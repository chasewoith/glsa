<?php

	$banner = backupGuardGetBanner(SG_ENV_ADAPTER, "plugin", SG_PRODUCT_IDENTIFIER);
	if (!(SGBoot::isFeatureAvailable('MULTI_SCHEDULE') && !$banner)) {
		include_once(SG_NOTICE_TEMPLATES_PATH.'banner.php');
	}

	SGNotice::getInstance()->renderAll();
?>

<div class="sg-spinner"></div>
<div class="sg-wrapper-less">
	<div id="sg-wrapper">
