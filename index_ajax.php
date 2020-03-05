<?php
require __DIR__ . '/vendor/autoload.php';
require_once "../config.php";
require_once "upload_util.php";
// The Tsugi PHP API Documentation is available at:
// http://do1.dr-chuck.com/tsugi/phpdoc/

use \Tsugi\Util\U;
use \Tsugi\Util\Net;
use \Tsugi\Core\LTIX;
use \Tsugi\Core\Settings;
use \Tsugi\UI\SettingsForm;

use \CloudConvert\CloudConvert;
use \CloudConvert\Models\Job;
use \CloudConvert\Models\Task;

// No parameter means we require CONTEXT, USER, and LINK
$LAUNCH = LTIX::requireData();

// If settings were updated
if ( SettingsForm::handleSettingsPost() ) {
    header( 'Location: '.addSession('index.php') ) ;
    return;
}

// Handle Post Data
$p = $CFG->dbprefix;
$old_content = $LAUNCH->result->getJsonKey('content', '');

if ( U::get($_POST, 'content') ) {
    $LAUNCH->result->setJsonKey('content', U::get($_POST, 'content') );
    $PDOX->queryDie("DELETE FROM {$p}attend WHERE link_id = :LI",
            array(':LI' => $LINK->id)
    );
    $_SESSION['success'] = 'Updated';
    header( 'Location: '.addSession('index.php') ) ;
    return;
} 

// Render view
$OUTPUT->header();
// https://github.com/jitbit/HtmlSanitizer

$OUTPUT->bodyStart();
$OUTPUT->topNav();

if ( $USER->instructor ) {
    echo('<div style="float:right;">');
    echo('<a href="annotate.php" class="btn btn-primary">Annotate</a>');
    SettingsForm::button(false);
    echo('</div>');

    $OUTPUT->welcomeUserCourse();
    echo('<br clear="all">');
    SettingsForm::start();
    echo("<p>Configure the LTI Tool<p>\n");
    SettingsForm::text('code',__('Code'));
    SettingsForm::checkbox('grade',__('Send a grade'));
    SettingsForm::done();
    SettingsForm::end();
}

$OUTPUT->flashMessages();

echo("<!-- Classic single-file version of the tool -->\n");

$upload_max_size = ini_get('upload_max_filesize');
$upload_max_size_bytes = return_bytes($upload_max_size);
?>
<p>
<a href="upload_info.php" target="_blank">Get job info</a>
</p>
<p>
  <form action="" method="post" id="upload_form" enctype="multipart/form-data">
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
  e.preventDefault();
  if (window.File && window.FileReader && window.FileList && window.Blob) {
      var file = $('#thepdf')[0].files[0];
      if ( typeof file == 'undefined' ) {
        alert("Please select a file");
        return;
      }
      if ( file.type != 'application/pdf') {
          console.log('Type', file.type);
          alert("File " + file.name + " expecting PDF, found" + file.type );
          return;
      }
      if (file && file.size > <?= $upload_max_size_bytes ?> ) {
        alert("File " + file.name + " of type " + file.type + " must be < <?= $upload_max_size ?>");
        return;
      }
      $.getJSON( '<?= addSession("upload_info.php") ?>', function( data ) {
        console.log(data);
        $("#upload_form").attr("action", data.url);
        $.each( data.hidden, function( key, val ) {
          console.log(key, val); 
          $("<input>").attr({
                name: key,
                type: "hidden",
                value: val
            }).appendTo("#upload_form");
        });
      });
  }
});
</script>
<?php
$OUTPUT->footerEnd();

