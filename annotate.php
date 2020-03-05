<?php
require_once "../config.php";

// The Tsugi PHP API Documentation is available at:
// http://do1.dr-chuck.com/tsugi/phpdoc/

use \Tsugi\Util\U;
use \Tsugi\Util\Net;
use \Tsugi\Core\LTIX;
use \Tsugi\Core\Settings;
use \Tsugi\UI\SettingsForm;

// No parameter means we require CONTEXT, USER, and LINK
$LAUNCH = LTIX::requireData();

// Render view
$OUTPUT->header();
?>
<link rel="stylesheet" href="<?= $CFG->staticroot ?>/js/annotator-full.1.2.10/annotator.min.css" />
<?php
$OUTPUT->bodyStart();
$OUTPUT->topNav();
$OUTPUT->flashMessages();
?>
<div style="float:right;">
<a href="index.php" class="btn btn-primary">Edit</a>
</div>

    <div id="spinner"><img src="<?= $OUTPUT->getSpinnerUrl() ?>"/></div>
    <div id="output_div" style="display: none;">
    </div>
<?php
$OUTPUT->footerStart();
// https://github.com/jitbit/HtmlSanitizer
?>
<script src="https://cdn.jsdelivr.net/gh/jitbit/HtmlSanitizer@master/HtmlSanitizer.js"></script>
<script src="<?= $CFG->staticroot ?>/js/annotator-full.1.2.10/annotator-full.min.js"></script>
<script type="text/javascript">
$(document).ready( function () {
    $.get('<?= addSession('load_text.php') ?>', function(data) {
      var html = HtmlSanitizer.SanitizeHtml(data);
      $('#output_div').html(html);
       $('#spinner').hide();
       $('#output_div').show();
       var ann = new Annotator(jQuery('#output_div'));
       ann.startPlugins();
       console.log('Annotator started');
    })
  }
);
</script>
<?php
$OUTPUT->footerEnd();

