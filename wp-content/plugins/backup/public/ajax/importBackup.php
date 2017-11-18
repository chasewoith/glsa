<?php
require_once(dirname(__FILE__).'/../boot.php');
require_once(SG_BACKUP_PATH.'SGBackup.php');
require_once(SG_LIB_PATH.'SGUploadHandler.php');

$error = array();
$success = array('success'=>1);

try {
	$sgUploadHandler = new SGUploadHandler();
}
catch (SGException $exception) {
    array_push($error, $exception->getMessage());
    die(json_encode($error));
}

wp_die();
