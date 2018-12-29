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
						<legend><?php _backupGuardT('Cloud settings')?><?php echo backupGuardLoggedMessage(); ?></legend>
						<?php if (SGBoot::isFeatureAvailable('SUBDIRECTORIES')): ?>
							<div class="form-group form-inline">
								<label class="col-md-5 sg-control-label">
									<?php _backupGuardT('Destination folder')?>
								</label>

								<div class="col-md-7 pull-right text-right">
									<input id="cloudFolder" name="cloudFolder" type="text" class="form-control input-md" value="<?php echo esc_html(SGConfig::get('SG_STORAGE_BACKUPS_FOLDER_NAME'))?>">
									<button type="button" id="sg-save-cloud-folder" class="btn btn-success pull-right"><?php _backupGuardT('Save');?></button>
								</div>
							</div>
							<hr/>
						<?php endif; ?>
						<!-- Dropbox -->
						<?php if (SGBoot::isFeatureAvailable('DROPBOX')): ?>
							<div class="form-group">
								<label class="col-md-8 sg-control-label">
									<?php echo 'Dropbox' ?>
									<?php if(!empty($dropboxUsername)): ?>
										<br/>
										<span class="text-muted sg-dropbox-user sg-helper-block"><?php echo $dropboxUsername;?></span>
									<?php endif;?>
								</label>
								<div class="col-md-3 pull-right text-right">
									<label class="sg-switch-container">
										<input data-on-text="<?php _backupGuardT('ON')?>" data-off-text="<?php _backupGuardT('OFF')?>" data-storage="DROPBOX" data-remote="cloudDropbox" type="checkbox" class="sg-switch" <?php echo !empty($dropbox)?'checked="checked"':''?>>
									</label>
								</div>
							</div>
						<?php endif; ?>
						<!-- Google Drive -->
						<?php if (SGBoot::isFeatureAvailable('GOOGLE_DRIVE')): ?>
							<div class="form-group">
								<label class="col-md-8 sg-control-label">
									<?php echo 'Google Drive' ?>
									<?php if(!empty($gdriveUsername)): ?>
										<br/>
										<span class="text-muted sg-gdrive-user sg-helper-block"><?php echo $gdriveUsername;?></span>
									<?php endif;?>
								</label>
								<div class="col-md-3 pull-right text-right">
									<label class="sg-switch-container">
										<input data-on-text="<?php _backupGuardT('ON')?>" data-off-text="<?php _backupGuardT('OFF')?>" data-storage="GOOGLE_DRIVE" data-remote="cloudGdrive" type="checkbox" class="sg-switch" <?php echo !empty($gdrive)?'checked="checked"':''?>>
									</label>
								</div>
							</div>
						<?php endif; ?>
						<!-- FTP -->
						<?php if (SGBoot::isFeatureAvailable('FTP')): ?>
							<div class="form-group">
								<label class="col-md-8 sg-control-label sg-user-info">
									<?php echo 'FTP / SFTP' ?>
									<?php if(!empty($ftpUsername)): ?>
										<br/>
										<span class="text-muted sg-ftp-user sg-helper-block"><?php echo $ftpUsername;?></span>
									<?php endif;?>
								</label>
								<div class="col-md-3 pull-right text-right">
									<label class="sg-switch-container">
										<input type="checkbox" data-on-text="<?php _backupGuardT('ON')?>" data-off-text="<?php _backupGuardT('OFF')?>" data-storage="FTP" data-remote="cloudFtp" class="sg-switch" <?php echo !empty($ftp)?'checked="checked"':''?>>
										<a id="ftp-settings" href="javascript:void(0)" class="hide" data-toggle="modal" data-modal-name="ftp-settings" data-remote="modalFtpSettings"><?php echo 'FTP '._backupGuardT('Settings', true) ?></a>
									</label>
								</div>
							</div>
						<?php endif; ?>
						<!-- Amazon S3 -->
						<?php if (SGBoot::isFeatureAvailable('AMAZON')): ?>
							<div class="form-group">
								<label class="col-md-8 sg-control-label">
									<?php echo (backupGuardIsAccountGold()? 'Amazon ':'').'S3'?>
									<?php if (!empty($amazonInfo)):?>
										<br/>
										<span class="text-muted sg-ftp-user sg-helper-block"><?php echo $amazonInfo;?></span>
									<?php endif;?>
								</label>
								<div class="col-md-3 pull-right text-right">
									<label class="sg-switch-container">
										<input type="checkbox" data-on-text="<?php _backupGuardT('ON')?>" data-off-text="<?php _backupGuardT('OFF')?>" data-storage="AMAZON" data-remote="cloudAmazon" class="sg-switch" <?php echo !empty($amazon)?'checked="checked"':''?>>
										<a id="amazon-settings" href="javascript:void(0)" class="hide" data-toggle="modal" data-modal-name="amazon-settings" data-remote="modalAmazonSettings"><?php echo 'Amazon'._backupGuardT('Settings', true)?></a>
									</label>
								</div>
							</div>
						<?php endif; ?>
						<!-- One Drive -->
						<?php if (SGBoot::isFeatureAvailable('ONE_DRIVE')): ?>
							<div class="form-group">
								<label class="col-md-8 sg-control-label">
									<?php echo 'One Drive' ?>
									<?php if(!empty($oneDriveInfo)): ?>
										<br/>
										<span class="text-muted sg-gdrive-user sg-helper-block"><?php echo $oneDriveInfo;?></span>
									<?php endif;?>
								</label>
								<div class="col-md-3 pull-right text-right">
									<label class="sg-switch-container">
										<input data-on-text="<?php _backupGuardT('ON')?>" data-off-text="<?php _backupGuardT('OFF')?>" data-storage="ONE_DRIVE" data-remote="cloudOneDrive" type="checkbox" class="sg-switch" <?php echo !empty($oneDrive)?'checked="checked"':''?>>
									</label>
								</div>
							</div>
						<?php endif; ?>
					</fieldset>
				</form>
			</div>
		</div>
	</div>
	<?php require_once(SG_PUBLIC_INCLUDE_PATH.'/footer.php'); ?>
</div>
