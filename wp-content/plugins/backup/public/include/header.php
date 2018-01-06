<?php

	$banner = backupGuardGetBanner(SG_ENV_ADAPTER, "plugin", SG_PRODUCT_IDENTIFIER);
	$isDisabelAdsEnabled = SGConfig::get('SG_DISABLE_ADS');
	if (!(SGBoot::isFeatureAvailable('MULTI_SCHEDULE') && !$banner) && !$isDisabelAdsEnabled) {
		include_once(SG_NOTICE_TEMPLATES_PATH.'banner.php');
	}

	SGNotice::getInstance()->renderAll();
?>

<div class="sg-spinner"></div>
<div class="sg-wrapper-less">
	<div id="sg-wrapper">
