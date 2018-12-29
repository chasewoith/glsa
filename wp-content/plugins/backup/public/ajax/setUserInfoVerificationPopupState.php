<?php

require_once(dirname(__FILE__).'/../boot.php');

if(backupGuardIsAjax() && count($_POST)) {
	SGConfig::set('SG_HIDE_VERIFICATION_POPUP_STATE', 1);
	die('0');
}
