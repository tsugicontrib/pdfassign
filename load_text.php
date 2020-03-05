<?php
require_once "../config.php";

use \Tsugi\Util\U;
use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();

header('Content-Type:text/plain');

$old_content = $LAUNCH->result->getJsonKey('content', '');
echo($old_content);
