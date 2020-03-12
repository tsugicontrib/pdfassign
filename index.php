<?php
require __DIR__ . '/vendor/autoload.php';
require_once "../config.php";

use \Tsugi\Util\U;
use \Tsugi\Core\LTIX;
use \Tsugi\UI\Annotate;
use \Tsugi\Blob\Access;

// No parameter means we require CONTEXT, USER, and LINK
$LAUNCH = LTIX::requireData();

$next = U::safe_href(U::get($_GET, 'next', 'edit.php'));
$next_text = __('Info');
if ( $next != 'edit.php' ) $next_text = __('Back');

$user_id = U::safe_href(U::get($_GET, 'user_id'));
if ( $user_id && ! $LAUNCH->user->instructor ) {
    http_response_code(403);
    die('Not authorized');
}
if ( ! $user_id ) $user_id = $LAUNCH->user->id;

$file_id = $LAUNCH->result->getJsonKeyForUser('file_id', false, $user_id);
if ( ! $file_id ) {
    header("Location: ".addSession($next));
    return;
}


// Might be a string or opened handle
$retval = Access::openContent($LAUNCH, $file_id);

// If we get a string - some how the file_id link is broken
if ( ! is_array($retval) ) {
    $LAUNCH->result->setJsonKeyForUser('file_id', false, $user_id);
    if (is_string($retval) ) $_SESSION['error'] = 'Error retrieving file='.$file_id.' ('.$retval.')';
    error_log("Could not load blob for user=$user_id b=$file_id error=".$retval);
    header("Location: ".addSession($next));
    return;
}

$blob = $retval[0];
$type = $retval[1];

header("Content-Type: ".$type);

$api_endpoint = $CFG->wwwroot . '/api/annotate/' . session_id() . ':' . $LAUNCH->result->id;

$matches = array(
    array(false, '</head>','
<link href="'.$CFG->staticroot.'/js/jquery-ui-1.11.4/jquery-ui.min.css" rel="stylesheet">
'.Annotate::header().'
<link rel="stylesheet" href="'.$CFG->staticroot.'/js/annotator-full.1.2.10/annotator.min.css" />
</head>'),
    array(false, '<div id="page-container">', '
<div id="page-container">
<div style="position: fixed; top:10px; right:20px; z-index: 10000;">
<button onclick="alert(\'To annotate text, highlight text and an annotation input box will pop up.\'+
\' To view, edit or delete an existing annotation, simply hover over the highlighted text.\');"
style="border-radius: 4px; border: 4px solid darkblue; color: white; background-color: #008CBA;
 font-size: 16px; padding: 10px 16px;">'.__('Help').'</button>
<button onclick="window.location.href = \''.addSession($next).'\';"
style="border-radius: 4px; border: 4px solid darkblue; color: white; background-color: #008CBA;
 font-size: 16px; padding: 10px 16px;">'.$next_text.'</button>
</div>
'),
    array(false, '</body>', '
<script src="'.$CFG->staticroot.'/js/jquery-1.11.3.js"></script>
<script src="'.$CFG->staticroot.'/js/jquery-ui-1.11.4/jquery-ui.min.js"></script>
'.Annotate::footer($user_id).'
<script>
$(document).ready( function () {
    tsugiStartAnnotation("#page-container");
});
</script>
</body>'),
);

function augmentHTML($text, &$matches, $pos) {
    for($i=0; $i<count($matches); $i++) {
        $match = $matches[$i];
        if ( $match[0] ) continue;  // Only match once
        if ( strpos($text, $match[1]) === false ) continue;
        // error_log("Processing match $i at postion $pos");
        $text = str_replace($match[1], $match[2], $text);
        $matches[$i][0] = true;
    }
    return $text;
}

if ( is_string($blob) ) {
    $blob = augmentHTML($blob, $matches, 0);
    echo($blob);
} else {
    $lastcontent = false;
    $buffer_size = 100000;
    $keep_length = 100;
    $pos = 0;
    while( $content = fread($blob, $buffer_size) ) {
        if ( $lastcontent == false ) {
            $lastcontent = $content;
            continue;
        }
        $content = $lastcontent . $content;
        $content = augmentHTML($content, $matches, $pos);

        // Retain the last 6 characters (/head> and no more)
        $len = strlen($content);
        if ( $keep_length > $len ) {
            echo($content);
            $lastcontent = false;
            continue;
        }
        $pos = $pos + ($len-$keep_length);
        echo(substr($content, 0, $len-$keep_length));
        $lastcontent = substr($content, $len-$keep_length, $keep_length);
    }
    if ( $lastcontent ) {
        $blob = $lastcontent;
        $blob = augmentHTML($blob, $matches, -1);
        echo($blob);
    }
}


