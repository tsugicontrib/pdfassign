<?php
require __DIR__ . '/vendor/autoload.php';
require_once "../config.php";

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

$job_id = U::get($_SESSION, 'job_id');
$job_start = U::get($_SESSION, 'job_start');
$retval = array();
header('Content-Type: application/json');

if ( ! $job_id ) {
    $retval['status'] = 'There is no active conversion';
    echo(json_encode($retval, JSON_PRETTY_PRINT));
    return;
}
$retval['job_id'] = $job_id;
if ( $job_start ) {
    $retval['ellapsed'] = time() - $job_start;
}

$context_settings = $TSUGI_LAUNCH->context->settingsGetAll();

// https://github.com/cloudconvert/cloudconvert-php
$api_key = U::get($context_settings, 'api_key');
$sandbox = U::get($context_settings, 'sandbox');

if ( ! $api_key ) {
    $retval['status'] = 'API Key not configured.';
    echo(json_encode($retval, JSON_PRETTY_PRINT));
    return;
}

$cloudconvert = new CloudConvert([
    'api_key' => $api_key,
    'sandbox' => $sandbox == 'true'
]);

$tag = "context_". $LAUNCH->context->id . '::result_' . $LAUNCH->result->id;


$job = $cloudconvert->jobs()->get($job_id);

/* 
    public const STATUS_WATING = 'waiting';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_ERROR = 'error';
    public const STATUS_FINISHED = 'finished';
*/
$status = $job->getStatus();
$retval['status'] = $status;

// Grab extra bits.
$debug = array();
$debug['tag'] = $job->getTag();
$debug['created_at'] = $job->getCreatedAt();
$debug['ended_at'] = $job->getEndedAt();
$retval['job'] = $debug;

if ( $status != Job::STATUS_FINISHED ) {
    echo(json_encode($retval, JSON_PRETTY_PRINT));
    return;
}

// TODO: Try / except here
$results = array();
foreach ($job->getExportUrls() as $file) {

    error_log('Started Copying '.$file->filename);
    $source = $cloudconvert->getHttpTransport()->download($file->url)->detach();
    $dest = fopen('output/' . $file->filename, 'w');

    $results[] = "output/".$file->filename;
    stream_copy_to_stream($source, $dest);
    error_log('Finished Copying '.$file->filename);

}
$retval['results'] = $results;

echo(json_encode($retval, JSON_PRETTY_PRINT));
