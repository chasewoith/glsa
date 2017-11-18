<?php
require_once(dirname(__FILE__).'/../boot.php');

$sgFeature = $_POST['sgFeature'];
if (!SGBoot::isFeatureAvailable($sgFeature)) {
	die('{"error":"This feature is not available in your package."}');
}

die('{"success":1}');
