<?php
require_once(dirname(__FILE__).'/boot.php');
require_once(SG_PUBLIC_INCLUDE_PATH.'/header.php');
$dropbox = SGConfig::get('SG_DROPBOX_ACCESS_TOKEN');
$gdrive = SGConfig::get('SG_GOOGLE_DRIVE_REFRESH_TOKEN');
$ftp = SGConfig::get('SG_STORAGE_FTP_CONNECTED');
$amazon = SGConfig::get('SG_STORAGE_AMAZON_CONNECTED');
$oneDrive = SGConfig::get('SG_ONE_DRIVE_REFRESH_TOKEN');

$ftpUsername = SGConfig::get('SG_FTP_CONNECTION_STRING');
$gdriveUsername = SGConfig::get('SG_GOOGLE_DRIVE_CONNECTION_STRING');
$dropboxUsername = SGConfig::get('SG_DROPBOX_CONNECTION_STRING');
$amazonInfo = SGConfig::get('SG_AMAZON_BUCKET');

$oneDriveInfo = SGConfig::get('SG_ONE_DRIVE_CONNECTION_STRING');
?>
<?php require_once(SG_PUBLIC_INCLUDE_PATH.'sidebar.php'); ?>
<div id="sg-content-wrapper">
    <div class="container-fluid">
        <div class="row sg-cloud-container">
            <div class="col-md-12">
                <form class="form-horizontal">
                    <fieldset>
                        <legend><?php echo _backupGuardT('Cloud settings')?><?php echo backupGuardLoggedMessage(); ?></legend>
                        <div class="form-group form-inline">
                            <label class="col-md-5 sg-control-label">
                                <?php echo _backupGuardT('Destination folder')?>
                                <?php if (!SGBoot::isFeatureAvailable('SUBDIRECTORIES')): ?>
                                    &nbsp;&nbsp;<span data-toggle="tooltip" title="<?php echo SG_PLATINUM_TOOLTIP_TEXT?>" class="badge sg-badge-warning" class="badge sg-badge-warning" target-url="<?php echo SG_BACKUP_SITE_PRICING_URL?>"><?php echo SG_BADGE_PLATINUM_TEXT?></span>
                                <?php endif; ?>
                            </label>

                            <div class="col-md-7 pull-right text-right">
                                <input id="cloudFolder" name="cloudFolder" type="text" class="form-control input-md" value="<?php echo esc_html(SGConfig::get('SG_STORAGE_BACKUPS_FOLDER_NAME'))?>">
                                <button type="button" id="sg-save-cloud-folder" class="btn btn-success pull-right"><?php echo _backupGuardT('Save');?></button>
                            </div>
                        </div>
                        <hr/>
                        <!-- Dropbox -->
                        <div class="form-group">
                            <label class="col-md-8 sg-control-label">
                                <?php echo _backupGuardT('Dropbox')?>
                                <?php if (!SGBoot::isFeatureAvailable('DROPBOX')): ?>
                                    &nbsp;&nbsp;<span data-toggle="tooltip" title="<?php echo SG_SILVER_TOOLTIP_TEXT?>" class="badge sg-badge-warning" class="badge sg-badge-warning" target-url="<?php echo SG_BACKUP_SITE_PRICING_URL?>"><?php echo SG_BADGE_SILVER_PLUS_TEXT?></span>
                                <?php endif; ?>
                                <?php if(!empty($dropboxUsername)): ?>
                                    <br/><span class="text-muted sg-dropbox-user sg-helper-block"><?php echo $dropboxUsername;?></span>
                                <?php endif;?>
                            </label>
                            <div class="col-md-3 pull-right text-right">
                                <label class="sg-switch-container">
                                    <input data-on-text="<?php echo _backupGuardT('ON')?>" data-off-text="<?php echo _backupGuardT('OFF')?>" data-storage="DROPBOX" data-remote="cloudDropbox" type="checkbox" class="sg-switch" <?php echo !empty($dropbox)?'checked="checked"':''?>>
                                </label>
                            </div>
                        </div>
                        <!-- Google Drive -->
                        <div class="form-group">
                            <label class="col-md-8 sg-control-label">
                                <?php echo _backupGuardT('Google Drive')?>
                                <?php if (!SGBoot::isFeatureAvailable('GOOGLE_DRIVE')): ?>
                                    &nbsp;&nbsp;<span data-toggle="tooltip" title="<?php echo SG_GOLD_TOOLTIP_TEXT?>" class="badge sg-badge-warning" class="badge sg-badge-warning" target-url="<?php echo SG_BACKUP_SITE_PRICING_URL?>"><?php echo SG_BADGE_GOLD_PLUS_TEXT?></span>
                                <?php endif; ?>
                                <?php if(!empty($gdriveUsername)): ?>
                                    <br/><span class="text-muted sg-gdrive-user sg-helper-block"><?php echo $gdriveUsername;?></span>
                                <?php endif;?>
                            </label>
                            <div class="col-md-3 pull-right text-right">
                                <label class="sg-switch-container">
                                    <input data-on-text="<?php echo _backupGuardT('ON')?>" data-off-text="<?php echo _backupGuardT('OFF')?>" data-storage="GOOGLE_DRIVE" data-remote="cloudGdrive" type="checkbox" class="sg-switch" <?php echo !empty($gdrive)?'checked="checked"':''?>>
                                </label>
                            </div>
                        </div>
                        <!-- FTP -->
                        <div class="form-group">
                            <label class="col-md-8 sg-control-label sg-user-info">
                                <?php echo _backupGuardT('FTP / SFTP')?>
                                <?php if (!SGBoot::isFeatureAvailable('FTP')): ?>
                                    &nbsp;&nbsp;<span data-toggle="tooltip" title="<?php echo SG_SILVER_TOOLTIP_TEXT?>" class="badge sg-badge-warning" class="badge sg-badge-warning" target-url="<?php echo SG_BACKUP_SITE_PRICING_URL?>"><?php echo SG_BADGE_SILVER_PLUS_TEXT?></span>
                                <?php endif; ?>
                                <?php if(!empty($ftpUsername)): ?>
                                    <br/><span class="text-muted sg-ftp-user sg-helper-block"><?php echo $ftpUsername;?></span>
                                <?php endif;?>
                            </label>
                            <div class="col-md-3 pull-right text-right">
                                <label class="sg-switch-container">
                                    <input type="checkbox" data-on-text="<?php echo _backupGuardT('ON')?>" data-off-text="<?php echo _backupGuardT('OFF')?>" data-storage="FTP" data-remote="cloudFtp" class="sg-switch" <?php echo !empty($ftp)?'checked="checked"':''?>>
                                    <a id="ftp-settings" href="javascript:void(0)" class="hide" data-toggle="modal" data-modal-name="ftp-settings" data-remote="modalFtpSettings"><?php _backupGuardT('Ftp Settings')?></a>
                                </label>
                            </div>
                        </div>
                        <!-- Amazon S3 -->
                        <div class="form-group">
                            <label class="col-md-8 sg-control-label">
                                <?php echo _backupGuardT((backupGuardIsAccountGold()? 'Amazon ':'').'S3')?>
                                <?php if (!SGBoot::isFeatureAvailable('AMAZON')): ?>
                                    &nbsp;&nbsp;<span data-toggle="tooltip" title="<?php echo SG_GOLD_TOOLTIP_TEXT?>" class="badge sg-badge-warning" class="badge sg-badge-warning" target-url="<?php echo SG_BACKUP_SITE_PRICING_URL?>"><?php echo SG_BADGE_GOLD_PLUS_TEXT?></span>
                                <?php endif; ?>
                                <?php if (!empty($amazonInfo)):?>
                                    <br/><span class="text-muted sg-ftp-user sg-helper-block"><?php echo $amazonInfo;?></span>
                                <?php endif;?>
                            </label>
                            <div class="col-md-3 pull-right text-right">
                                <label class="sg-switch-container">
                                    <input type="checkbox" data-on-text="<?php echo _backupGuardT('ON')?>" data-off-text="<?php echo _backupGuardT('OFF')?>" data-storage="AMAZON" data-remote="cloudAmazon" class="sg-switch" <?php echo !empty($amazon)?'checked="checked"':''?>>
                                    <a id="amazon-settings" href="javascript:void(0)" class="hide" data-toggle="modal" data-modal-name="amazon-settings" data-remote="modalAmazonSettings"><?php _backupGuardT('Amazon Settings')?></a>
                                </label>
                            </div>
                        </div>
                        <!-- One Drive -->
                        <div class="form-group">
                            <label class="col-md-8 sg-control-label">
                                <?php echo _backupGuardT('One Drive')?>
                                <?php if (!SGBoot::isFeatureAvailable('ONE_DRIVE')): ?>
                                    &nbsp;&nbsp;<span data-toggle="tooltip" title="<?php echo SG_GOLD_TOOLTIP_TEXT?>" class="badge sg-badge-warning" class="badge sg-badge-warning" target-url="<?php echo SG_BACKUP_SITE_PRICING_URL?>"><?php echo SG_BADGE_GOLD_PLUS_TEXT?></span>
                                <?php endif; ?>
                                <?php if(!empty($oneDriveInfo)): ?>
                                    <br/><span class="text-muted sg-gdrive-user sg-helper-block"><?php echo $oneDriveInfo;?></span>
                                <?php endif;?>
                            </label>
                            <div class="col-md-3 pull-right text-right">
                                <label class="sg-switch-container">
                                    <input data-on-text="<?php echo _backupGuardT('ON')?>" data-off-text="<?php echo _backupGuardT('OFF')?>" data-storage="ONE_DRIVE" data-remote="cloudOneDrive" type="checkbox" class="sg-switch" <?php echo !empty($oneDrive)?'checked="checked"':''?>>
                                </label>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
    <?php require_once(SG_PUBLIC_INCLUDE_PATH.'/footer.php'); ?>
</div>
