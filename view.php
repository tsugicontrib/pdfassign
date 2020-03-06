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

$head_content = "<script>alert('in the head');</script></head>";
$body_content = "<script>alert('in the body');</script></body>";

if ( is_string($blob) ) {
    $new_blob = str_replace('</head>', $head_content, $blob);
    $blob = str_replace('</body>', $body_content, $new_blob);
    echo($blob);
} else {
    /*
    $blob = stream_get_contents($blob);
    $new_blob = str_replace('</head>', $head_content, $blob);
    $blob = str_replace('</body>', $body_content, $new_blob);
    echo($blob);
     */
    $lastcontent = false;
    $headfound = false;
    $bodyfound = false;
    $buffer_size = 100000;
    $keep_length = strlen('</head>')-1;
    while( $content = fread($blob, $buffer_size) ) {
        if ( $lastcontent == false ) {
            $lastcontent = $content;
            continue;
        }
        $str_blob = $lastcontent . $content;
        $len = strlen($str_blob);
        if ( ! $headfound ) {
            $str_blob = str_replace('</head>', $head_content, $str_blob);
            $headfound = strlen($str_blob) > $len;
        }
        $len = strlen($str_blob);
        if ( ! $bodyfound ) {
            $str_blob = str_replace('</body>', $body_content, $str_blob);
            $bodyfound = strlen($str_blob) > $len;
        }

        // Retain the last 6 characters (/head> and no more)
        $len = strlen($str_blob);
        echo(substr($str_blob, 0, $len-$keep_length));
        $lastcontent = substr($str_blob, $len-$keep_length, $keep_length);
    }
    if ( $lastcontent ) {
        $str_blob = $lastcontent;
        if ( ! $headfound ) {
            $str_blob = str_replace('</head>', $head_content, $str_blob);
        }
        if ( ! $bodyfound ) {
            $str_blob = str_replace('</body>', $body_content, $str_blob);
        }
        echo($str_blob);
    }
}


