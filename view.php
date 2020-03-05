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

$blob = Access::readContent($blob_id);
header("Content-Type: text/html; charset=utf-8;");

if ( is_string($blob) ) {
    echo($blob);
} else {
    fpassthru($blob);
}


