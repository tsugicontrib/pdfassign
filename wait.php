<?php
require __DIR__ . '/vendor/autoload.php';
require_once "../config.php";

use \Tsugi\Util\U;
use \Tsugi\Util\Net;
use \Tsugi\Core\LTIX;
use \Tsugi\Blob\BlobUtil;

use \CloudConvert\CloudConvert;
use \CloudConvert\Models\Job;
use \CloudConvert\Models\Task;

// No parameter means we require CONTEXT, USER, and LINK
$LAUNCH = LTIX::requireData();

$context_settings = $TSUGI_LAUNCH->context->settingsGetAll();

$tag = "context_". $LAUNCH->context->id . '::result_' . $LAUNCH->result->id;

// Render view
$OUTPUT->header();

$OUTPUT->bodyStart();
$OUTPUT->topNav();
$OUTPUT->welcomeUserCourse();
$OUTPUT->flashMessages();

?>
<p>Waiting...</p>
<p><a href="upload_status.php" target="_blank">Status</a></p>
<p>
Job: <span id="job_id"></span>
Status: <span id="status"></span>
Ellapsed: <span id="ellapsed"></span>
</p>
<?php
$OUTPUT->footerStart();
?>
<script>
function checkStatus() {
    console.log('checkStatus()');
    $.getJSON( '<?= addSession("upload_status.php") ?>', function( data ) {
        console.log(data);
        $('#status').html(data.status);
        $('#job_id').html(data.job_id);
        $('#ellapsed').html(data.ellapsed);
        if ( data.status != 'finished') {
            setTimeout(checkStatus, 5000);
            return;
        }
        redirect = data.results[0];
        console.log('Redirect', redirect);
        window.location.replace(redirect);
    });
}
setTimeout(checkStatus, 2000);
</script>
<?php
$OUTPUT->footerEnd();

