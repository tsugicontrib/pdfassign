<?php
require_once "../config.php";

use \Tsugi\Util\U;
use \Tsugi\Core\Settings;
use \Tsugi\Core\LTIX;
use \Tsugi\UI\SettingsForm;
use \Tsugi\UI\Lessons;

$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;

if ( ! $USER->instructor ) die("Must be instructor");

$redirect = false;
$postkeys = array('api_key', 'sandbox');

if ( U::get($_POST, 'update') ) {
    foreach($postkeys as $key) {
        $LAUNCH->context->settingsSet($key, U::get($_POST, $key));
        $redirect = true;
    }
}

if ( $redirect ) {
    $_SESSION['success'] = 'Settings updated.';
    header("Location: ".addSession('edit.php'));
    return;
}

$settings = $LAUNCH->context->settingsGetAll();

$menu = new \Tsugi\UI\MenuSet();
$menu->addLeft(__('Back'), 'edit.php');
$menu->addRight(__('API Keys'), '#', /* push */ false, 'data-toggle="modal" data-target="#apiModal"');
$menu->addRight(__('Privacy'), '#', /* push */ false, 'data-toggle="modal" data-target="#privacyModal"');

// View
$OUTPUT->header();
$OUTPUT->bodyStart();
$OUTPUT->topNav($menu);
$OUTPUT->flashMessages();


echo($OUTPUT->modalString(__("Privacy details"), "
<p>
<b>Privacy Note:</b>
Once a PDF file has been converted by CloudConvert,
the HTML is pulled back into this system and and served from
this system for viewing and annotation.  CloudConvert
does <b>not</b> retain either the PDF or the converted
HTML longer than 24 hours.  It is merely a conversion
service.
</p>
", "privacyModal"));

echo($OUTPUT->modalString(__("API Key details"), "
<p>
To get an API key, go to 
<a href=\"https://cloudconvert.com/\" target=\"_blank\">
cloudconvert.com</a> and create an account.  Then create an
API V2 key from your dashboard.  Give the key the
<b>user.read</b>,
<b>user.write</b>,
<b>task.read</b>,
and
<b>task.write</b> permissions and
copy and retain the API key and paste it into this page.
</p>
<p>
CloudConvert provides limited use (about 25 conversions per day)
production API keys for free. They also provide a 'sandbox' environment
for testing.  You have to explicity list the files you will use
with the sandbox, but there is no limit on the number
of conversions.
</p>
<p>
The sandbox has 
different keys than production
so when you switch between sandbox an production in this screen you need to
switch keys as well.
</p>
<p>
CloudConvert has an excellent 'Jobs' dashboard that lets you monitor
your jobs in progress and completed and makes it easy to debug problems
with any conversions.  You can monitor this as students are uploading and
converting files to help diagnose issues they might be experiencing.
", "apiModal"));

?>
<p>
This tool requires API key from 
<a href="https://cloudconvert.com/" target="_blank">
CloudConvert</a> so that it can convert PDF files to
HTML files for annotation. 
</p>
<p><b>Note:</b> This is a per-course configuration, not a per-link
configuration so <b>changing this configuration</b>
affects all of the links in a course.  So be careful.
</p>
<form method="post">
<p>
Are you using the CloudConvert sandbox?
<select name="sandbox">
<option value="">-- Please select --</option>
<option value="false"
<?php if ( U::get($settings, "sandbox") == 'false' ) echo('selected'); ?>
>No</option>
<option value="true"
<?php if ( U::get($settings, "sandbox") == 'true' ) echo('selected'); ?>
>Yes</option>
</select>
</p>
<p>Cloudconvert v2 API_KEY<br/>
<textarea name="api_key" style="width:80%;" rows="10">
<?= htmlentities(U::get($settings, 'api_key')) ?>
</textarea></p>
<input type="submit" name="update">
</form>

<?php
$OUTPUT->footer();

