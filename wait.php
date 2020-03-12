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

// https://www.w3schools.com/howto/howto_css_loader.asp
?>
<style>
.loader {
  border: 16px solid #f3f3f3; /* Light grey */
  border-top: 16px solid #3498db; /* Blue */
  border-radius: 50%;
  width: 120px;
  height: 120px;
  animation: spin 2s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
<?php

$OUTPUT->bodyStart();
$OUTPUT->topNav();

?>
<center>
<p>Converting your PDF using CloudConvert - This can take 1-3 minutes...</p>
<div class="loader"></div>
<p>Status: <span id="status"></span> (<span id="ellapsed">1</span> seconds)</p>
</center>
<!--
<p><a href="upload_status.php" target="_blank">Status (Debug only)</a></p>
-->
<?php
$OUTPUT->footerStart();
?>
<script>
function checkStatus() {
    console.log('checkStatus()');
    $('#spinner').show();
    $.getJSON( '<?= addSession("upload_status.php") ?>', function( data ) {
        console.log(data);
        $('#spinner').hide();
        $('#status').html(data.status);
        $('#job_id').html(data.job_id);
        $('#ellapsed').html(data.ellapsed);
        if ( data.status == 'error') {
			alert('Something went wrong with the conversion, detail: '+data.code);
			return;
		}
        if ( data.status == 'downloading') {
            setTimeout(checkStatus, 1000);
            return;
        }
        if ( data.status != 'finished') {
            setTimeout(checkStatus, 5000);
            return;
        }
        window.location.replace('<?= addSession("index.php") ?>');
    });
}
setTimeout(checkStatus, 2000);
</script>
<?php
$OUTPUT->footerEnd();

