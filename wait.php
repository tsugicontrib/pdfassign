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

$size = U::get($_GET, 'size');
$seconds = (int) (15 + ($size/37000));

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
<div class="progress">
  <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" id="progress"
  aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
    
  </div>
</div>
</center>
<!--
<p><a href="upload_status.php" target="_blank">Status (Debug only)</a></p>
-->
<?php
$OUTPUT->footerStart();
?>
<script>
var size = <?= $size ?>;
var seconds = <?= $seconds ?>;

function checkStatus() {
    console.log('checkStatus()');
    $.getJSON( '<?= addSession("upload_status.php") ?>', function( data ) {
        console.log(data);
        $('#status').html(data.status);
        $('#job_id').html(data.job_id);
        $('#ellapsed').html(data.ellapsed);
        var progress = Math.trunc(data.ellapsed * 100 / seconds);
        if ( progress > 90 ) progress = 90;
        $('#progress').attr("aria-valuenow", progress);
        $('#progress').css("width", progress+'%');
        if ( data.status == 'error') {
			alert('Something went wrong with the conversion, detail: '+data.code);
			return;
		}
        if ( data.status == 'downloading') {
            $('#progress').removeClass("progress-bar-info", progress);
            $('#progress').addClass("progress-bar-success", progress);
            setTimeout(checkStatus, 1000);
            return;
        }
        if ( data.status != 'finished') {
            setTimeout(checkStatus, 3000);
            return;
        }
        window.location.replace('<?= addSession("index.php".'?size='.$size) ?>'+'&ellapsed='+data.ellapsed);
    });
}
setTimeout(checkStatus, 1000);
</script>
<?php
$OUTPUT->footerEnd();

