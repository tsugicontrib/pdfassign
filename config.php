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
    header("Location: ".addSession('index.php'));
    return;
}

$settings = $LAUNCH->context->settingsGetAll();

// View
$OUTPUT->header();
$OUTPUT->bodyStart();
$OUTPUT->topNav();

// Settings button and dialog

echo('<div style="float: right;">');
echo('<a href="index.php"><button class="btn btn-info">Back</button></a> '."\n");
echo('</div>');

$OUTPUT->flashMessages();

$OUTPUT->welcomeUserCourse();

// echo("<pre>\n");var_dump($set);echo("</pre>\n");

?>
<p><b>Note:</b> This is a per-course configuration, not a per-link
configuration so <b>changing this configuration</b>
affects all of the links in a course.  So be careful.
</p>
<form method="post">
<p>
<select name="sandbox">
<option value="">-- Are you using the sandbox --</option>
<option value="false"
<?php if ( U::get($settings, "sandbox") == 'false' ) echo('selected'); ?>
>No</option>
<option value="true"
<?php if ( U::get($settings, "sandbox") == 'true' ) echo('selected'); ?>
>Yes</option>
</select>
</p>
<p>API_KEY<br/>
<textarea name="api_key" style="width:80%;" rows="10">
<?= htmlentities(U::get($settings, 'api_key')) ?>
</textarea></p>
<input type="submit" name="update">
</form>

<?php
$OUTPUT->footer();

