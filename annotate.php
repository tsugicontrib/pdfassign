<?php
require __DIR__ . '/vendor/autoload.php';
require_once "../config.php";

use \Tsugi\Util\U;
use \Tsugi\Util\Net;
use \Tsugi\Core\LTIX;
use \Tsugi\Core\Settings;
use \Tsugi\UI\SettingsForm;
use \Tsugi\Blob\BlobUtil;
use \Tsugi\Blob\Access;

use \CloudConvert\CloudConvert;
use \CloudConvert\Models\Job;
use \CloudConvert\Models\Task;

// No parameter means we require CONTEXT, USER, and LINK
$LAUNCH = LTIX::requireData();

$blob_id = $LAUNCH->result->getJsonKey('blob_id');

// Might be a string or opened handle
$retval = Access::openContent($LAUNCH, $blob_id);
if ( ! is_array($retval) ) die('Cannot load blob');
$blob = $retval[0];
$type = $retval[1];

header("Content-Type: ".$type);

$api_endpoint = $CFG->wwwroot . '/api/annotate/' . session_id() . ':' . $LAUNCH->result->id;

$matches = array(
    array(false, '</head>','
<link href="http://localhost:8888/tsugi-static/js/jquery-ui-1.11.4/jquery-ui.min.css" rel="stylesheet">
<link rel="stylesheet" href="'.$CFG->staticroot.'/js/annotator-full.1.2.10/annotator.min.css" />
</head>'),
    array(false, '<div id="page-container">', '
<div id="page-container">
<button onclick="window.location.href = \''.addSession('index.php').'\';"
style="position: fixed; border-radius: 4px; border: 4px solid darkblue; z-index: 10000; color: white; background-color: #008CBA;
 font-size: 16px; right:20px;  padding: 10px 16px;">'.__('Done').'</button>
'),
    array(false, '</body>', '
<script src="'.$CFG->staticroot.'/js/jquery-1.11.3.js"></script>
<script src="http://localhost:8888/tsugi-static/js/jquery-ui-1.11.4/jquery-ui.min.js"></script>
<script src="'.$CFG->staticroot.'/js/annotator-full.1.2.10/annotator-full.min.js"></script>
<script type="text/javascript">
function startAnnotate() {
  // var ann = new Annotator(jQuery(\'#page-container\'));
  // console.log(ann);
  // console.log("Annotator started");
      $("#page-container").annotator()
      .annotator("setupPlugins", {} , {
         Auth: false,
         Tags: false,
         Filter: false,
         Store: {
            prefix: "'.$api_endpoint.'",
            loadFromSearch: false
         }
      } );
      console.log("Annotator started");
}
$(document).ready( function () {
    setTimeout(startAnnotate, 10);
    console.log("timer started...");
});
</script>
</body>'),
);

function augmentHTML($text, &$matches) {
    for($i=0; $i<count($matches); $i++) {
        $match = $matches[$i];
        if ( $match[0] ) continue;  // Only match once
        if ( strpos($text, $match[1]) === false ) continue;
        $text = str_replace($match[1], $match[2], $text);
        $matches[$i][0] = true;
    }
    return $text;
}

if ( is_string($blob) ) {
    $blob = augmentHTML($blob, $matches);
    echo($blob);
} else {
    $lastcontent = false;
    $buffer_size = 100000;
    $keep_length = 100;
    while( $content = fread($blob, $buffer_size) ) {
        if ( $lastcontent == false ) {
            $lastcontent = $content;
            continue;
        }
        $content = $lastcontent . $content;
        $content = augmentHTML($content, $matches);

        // Retain the last 6 characters (/head> and no more)
        $len = strlen($content);
        if ( $keep_length > $len ) {
            echo($content);
            $lastcontent = false;
            continue;
        }
        echo(substr($content, 0, $len-$keep_length));
        $lastcontent = substr($content, $len-$keep_length, $keep_length);
    }
    if ( $lastcontent ) {
        $blob = $lastcontent;
        $blob = augmentHTML($blob, $matches);
        echo($blob);
    }
}


