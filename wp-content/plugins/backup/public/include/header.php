<?php

	$isAdsEnabled = SGConfig::get('SG_DISABLE_ADS');
	$isPlatinumPackage = SGBoot::isFeatureAvailable('MULTI_SCHEDULE');

	if (!$isPlatinumPackage && !$isAdsEnabled) {
		include_once(SG_NOTICE_TEMPLATES_PATH.'banner.php');
	}

	SGNotice::getInstance()->renderAll();
?>

<div class="sg-spinner"></div>
<div class="sg-wrapper-less">
	<div id="sg-wrapper">
