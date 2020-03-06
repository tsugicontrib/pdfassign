<?php
require __DIR__ . '/vendor/autoload.php';
require_once "../config.php";

use \Tsugi\Util\U;
use \Tsugi\Util\Net;
use \Tsugi\Core\LTIX;
use \Tsugi\Core\Settings;
use \Tsugi\UI\SettingsForm;
use \Tsugi\Blob\BlobUtil;

use \CloudConvert\CloudConvert;
use \CloudConvert\Models\Job;
use \CloudConvert\Models\Task;

// No parameter means we require CONTEXT, USER, and LINK
$LAUNCH = LTIX::requireData();

$upload_max_size_bytes = BlobUtil::maxUploadBytes();
$upload_max_size = U::displaySize($upload_max_size_bytes);

// If settings were updated
if ( SettingsForm::handleSettingsPost() ) {
    header( 'Location: '.addSession('index.php') ) ;
    return;
}

// Sanity check input
if ($_SERVER['REQUEST_METHOD'] == 'POST' && count($_POST) < 1 ) {
    $_SESSION['error'] = 'File upload size exceeded, please re-upload a smaller file';
    error_log("Upload size exceeded");
    header('Location: '.addSession('index.php'));
    return;
}

if ( count($_FILES) > 1 ) {
    $_SESSION['error'] = 'Only one file allowed';
    header( 'Location: '.addSession('index.php') ) ;
    return;
}

// Check all files to be within our size limit
$thefdes = null;
foreach($_FILES as $fdes) {
    if ( $fdes['size'] > $upload_max_size_bytes ) {
        $_SESSION['error'] = 'Error - '.$fdes['name'].' has a size of '.$fdes['size'].' (' . $upload_max_size . ' max size per file)';
        header( 'Location: '.addSession('index.php') ) ;
        return;
    }
    $thefdes = $fdes;
}

// Handle Post Data
$p = $CFG->dbprefix;
$context_settings = $TSUGI_LAUNCH->context->settingsGetAll();
$api_key = U::get($context_settings, 'api_key');
$sandbox = U::get($context_settings, 'sandbox');

// https://github.com/cloudconvert/cloudconvert-php
if ( $api_key && $thefdes ) {
    // echo("<pre>\n");var_dump($api_key); die();
    $cloudconvert = new CloudConvert([
        'api_key' => $api_key,
        'sandbox' => $sandbox == 'true'
    ]);


$tag = "context_". $LAUNCH->context->id . '::result_' . $LAUNCH->result->id;
$job = (new Job())
    ->setTag($tag)
    ->addTask(
        (new Task('import/upload', 'upload-my-file'))
    )
    ->addTask(
        (new Task('convert', 'convert-my-file'))
            ->set('input', 'upload-my-file')
            ->set('input_format', 'pdf')
            ->set('output_format', 'html')
    )
    ->addTask(
        (new Task('export/url', 'export-my-file'))
            ->set('input', 'convert-my-file')
    );


    $cloudconvert->jobs()->create($job);

    $job_id = $job->getId();
    $_SESSION['job_id'] = $job_id;
    $_SESSION['job_start'] = time();

    error_log("Job created ".$job_id);

    $uploadTask = $job->getTasks()->name('upload-my-file')[0];

    $cloudconvert->tasks()->upload($uploadTask, fopen($thefdes['tmp_name'], 'r'));

    $_SESSION['success'] = "Data uploaded ".$job_id;
    header('Location: '.addSession('wait.php'));
    return;
} 

$blob_id = $LAUNCH->result->getJsonKey('blob_id');

// Render view
$OUTPUT->header();
// https://github.com/jitbit/HtmlSanitizer

$OUTPUT->bodyStart();
$OUTPUT->topNav();
$OUTPUT->welcomeUserCourse();

if ( $USER->instructor ) {
    echo('<div style="float:right;">');
    echo('<a href="view.php"><button class="btn btn-info">View</button></a> '."\n");
    echo('<a href="config.php"><button class="btn btn-info">Configure</button></a> '."\n");
    echo('<a href="annotate.php" class="btn btn-primary">Annotate</a>');
    SettingsForm::button(false);
    echo('</div>');

    echo('<br clear="all">');
    SettingsForm::start();
    echo("<p>Configure the LTI Tool<p>\n");
    SettingsForm::text('code',__('Code'));
    SettingsForm::checkbox('grade',__('Send a grade'));
?>
<p>
This program makes use of the following technologies:
<ul>
<li> Online conversion from PDF to HTML
<a href="https://cloudconvert.com" target="_blank">CloudConvert API</a> </li>
<li>Open Source JavaScript annotation software from 
<a href="https://annotatorjs.org/" target="_blank">Annotator JS</a></li>
</ul>
The CloudConvert API requires a license.  There are free licenses available
for a few conversions per day.  Use the <b>Configure</b> option to set your
license key across all the links for a particular class.
</p>
<?php
    SettingsForm::done();
    SettingsForm::end();
}

$OUTPUT->flashMessages();

if ( ! $api_key ) {
    echo("<p>".__('Not configured')."</p>");
    $OUTPUT->footer();
    return;
}

if ( $blob_id ) {
    echo("<p>Blob_id: $blob_id</p>\n");
}
?>
<p>
<form action="<?= addSession('index.php') ?>" method="post" id="upload_form" enctype="multipart/form-data">
    <input type="file" id="thepdf" name="result_<?= $LAUNCH->result->id ?>"> (Max size <?= $upload_max_size ?>) <br/>
    <input type="submit" value="Submit">
  </form>
</p>
<?php
$OUTPUT->footerStart();
?>
<!-- https://stackoverflow.com/questions/2472422/django-file-upload-size-limit -->
<script>
$("#upload_form").submit(function(e) {
    console.log('Checking file size');
    if (window.File && window.FileReader && window.FileList && window.Blob) {
        var file = $('#thepdf')[0].files[0];
        if ( typeof file == 'undefined' ) {
            alert("Please select a file");
            e.preventDefault();
            return;
        }
        if ( file.type != 'application/pdf') {
            console.log('Type', file.type);
             alert("File " + file.name + " expecting PDF, found" + file.type );
            e.preventDefault();
            return;
        }
        if (file && file.size > <?= $upload_max_size_bytes ?> ) {
            alert("File " + file.name + " of type " + file.type + " must be < <?= $upload_max_size ?>");
            e.preventDefault();
            return;
        }
        return;  // Allow POST to happen
    }
    e.preventDefault();
});
</script>
<?php
$OUTPUT->footerEnd();

