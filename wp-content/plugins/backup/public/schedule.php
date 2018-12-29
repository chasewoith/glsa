<?php

require_once(dirname(__FILE__).'/boot.php');
require_once(SG_PUBLIC_INCLUDE_PATH.'header.php');
require_once(SG_BACKUP_PATH.'SGBackup.php');

$id = '';
$directories = SG_BACKUP_FILE_PATHS;
$directories = explode(',', $directories);
$dropbox = SGConfig::get('SG_DROPBOX_ACCESS_TOKEN');

$intervalSelectElement = array(
	BG_SCHEDULE_INTERVAL_HOURLY => 'Hour',
	BG_SCHEDULE_INTERVAL_DAILY => 'Day',
	BG_SCHEDULE_INTERVAL_WEEKLY => 'Week',
	BG_SCHEDULE_INTERVAL_MONTHLY => 'Month'
);

$sgb = new SGBackup();
$scheduleParams = $sgb->getScheduleParamsById(SG_SCHEDULER_DEFAULT_ID);
$scheduleParams = backupGuardParseBackupOptions($scheduleParams);
?>
<?php require_once(SG_PUBLIC_INCLUDE_PATH.'sidebar.php'); ?>
<div id="sg-content-wrapper">
	<div class="container-fluid">
		<div class="row sg-schedule-container">
			<div class="col-md-12">
				<form class="form-horizontal" method="post" data-sgform="ajax" data-type="schedule">
					<fieldset>
						<legend><?php _backupGuardT('Schedule settings')?><?php echo backupGuardLoggedMessage(); ?></legend>
						<?php if (!SGBoot::isFeatureAvailable('MULTI_SCHEDULE')): ?>
							<div class="form-group">
								<div class="col-md-12 sg-feature-alert-text">
									<?php _backupGuardT('*Multiple schedule profiles are available only in')?> <a href="<?php echo SG_BACKUP_SITE_PRICING_URL?>" target="_blank"><?php _backupGuardT('Platinum')?></a> <?php _backupGuardT('version.')?>
								</div>
							</div>
						<?php endif; ?>
						<div class="form-group">
							<label class="col-md-8 sg-control-label">
								<?php _backupGuardT('Scheduled backup')?>
							</label>
							<div class="col-md-3 pull-right text-right">
								<label class="sg-switch-container">
									<input type="checkbox" class="sg-switch" <?php echo strlen($scheduleParams['label'])?'checked':''?> data-remote="schedule">
								</label>
							</div>
						</div>
						<div class="sg-schedule-settings sg-schedule-settings-<?php echo strlen($scheduleParams['label'])?'opened':'closed'; ?>">
							<div class="form-group">
								<label class="col-md-4 sg-control-label" for="sg-schedule-label"><?php _backupGuardT('Schedule label')?></label>
								<div class="col-md-8">
									<input class="form-control" name="sg-schedule-label" id="sg-schedule-label" value="<?php echo esc_html($scheduleParams['label'])?>">
								</div>
							</div>
							<!-- Schedule interval -->
							<div class="form-group">
								<label class="col-md-4 sg-control-label" for="sg-schedule-interval"><?php _backupGuardT('Perform backup every')?></label>
								<div class="col-md-8">
									<?php echo selectElement($intervalSelectElement, array('id'=>'sg-schedule-interval', 'name'=>'scheduleInterval', 'class'=>'form-control'), '', esc_html($scheduleParams['interval']));?>
								</div>
							</div>
							<!-- Schedule options -->
							<div class="form-group sg-custom-backup-schedule">
								<div class="col-md-8 col-md-offset-4">
									<div class="radio sg-no-padding-top">
										<label for="fullbackup-radio">
											<input type="radio" name="backupType" id="fullbackup-radio" value="1" checked>
											<?php _backupGuardT('Full backup'); ?>
										</label>
									</div>
									<div class="radio sg-no-padding-top">
										<label for="custombackup-radio">
											<input type="radio" name="backupType" id="custombackup-radio" value="2" <?php echo $scheduleParams['isCustomBackup']?'checked':'' ?>>
											<?php _backupGuardT('Custom backup'); ?>
										</label>
									</div>
									<div class="col-md-12 sg-custom-backup <?php echo $scheduleParams['isCustomBackup']?'sg-open':'' ?>">
										<div class="checkbox">
											<label for="custombackupdb-chbx">
												<input type="checkbox" name="backupDatabase" class="sg-custom-option" id="custombackupdb-chbx" <?php echo $scheduleParams['isDatabaseSelected']?'checked':'' ?>>
												<?php _backupGuardT('Backup database'); ?>
											</label>
										</div>
										<div class="checkbox">
											<label for="custombackupfiles-chbx">
												<input type="checkbox" name="backupFiles" class="sg-custom-option" id="custombackupfiles-chbx" <?php echo $scheduleParams['isFilesSelected']?'checked':'' ?>>
												<?php _backupGuardT('Backup files'); ?>
											</label>
											<!--Files-->
											<div class="col-md-12 sg-checkbox sg-custom-backup-files <?php echo $scheduleParams['isFilesSelected']?'sg-open':'' ?>">
												<?php foreach ($directories as $directory): ?>
													<div class="checkbox">
														<label for="<?php echo 'sg'.$directory?>">
															<input type="checkbox" name="directory[]" id="<?php echo 'sg'.$directory;?>" value="<?php echo $directory;?>" <?php if($directory == 'wp-content' && in_array($directory, $scheduleParams['selectedDirectories'])){ echo 'checked=checked'; } elseif ($directory != 'wp-content' && !in_array($directory, $scheduleParams['excludeDirectories'])){ echo 'checked=checked'; } ?> >
															<?php echo basename($directory);?>
														</label>
													</div>
												<?php endforeach;?>
											</div>
										</div>
									</div>
									<div class="clearfix"></div>
									<!--Cloud-->
									<?php if(SGBoot::isFeatureAvailable('STORAGE')): ?>
										<div class="checkbox">
											<label for="custombackupcloud-chbx">
												<input type="checkbox" name="backupCloud" id="custombackupcloud-chbx" <?php echo count($scheduleParams['selectedClouds'])?'checked':''?>>
												<?php _backupGuardT('Upload to cloud'); ?>
											</label>
											<!--Storages-->
											<div class="col-md-12 sg-checkbox sg-custom-backup-cloud <?php echo count($scheduleParams['selectedClouds'])?'sg-open':'';?>">
												<?php if(SGBoot::isFeatureAvailable('FTP')): ?>
													<div class="checkbox">
														<label for="cloud-ftp" <?php echo empty($ftp)?'data-toggle="tooltip" data-placement="right" title="'._backupGuardT('FTP is not active.',true).'"':''?>>
															<input type="checkbox" name="backupStorages[]" id="cloud-ftp" value="<?php echo SG_STORAGE_FTP ?>" <?php echo in_array(SG_STORAGE_FTP, $scheduleParams['selectedClouds'])?'checked="checked"':''?> <?php echo empty($ftp)?'disabled="disabled"':''?>>
															<?php echo 'FTP' ?>
														</label>
													</div>
												<?php endif; ?>
												<?php if(SGBoot::isFeatureAvailable('DROPBOX')): ?>
													<div class="checkbox">
														<label for="cloud-dropbox" <?php echo empty($dropbox)?'data-toggle="tooltip" data-placement="right" title="'._backupGuardT('Dropbox is not active.',true).'"':''?>>
															<input type="checkbox" name="backupStorages[]" id="cloud-dropbox" value="<?php echo SG_STORAGE_DROPBOX ?>" <?php echo in_array(SG_STORAGE_DROPBOX, $scheduleParams['selectedClouds'])?'checked="checked"':''?> <?php echo empty($dropbox)?'disabled="disabled"':''?>>
															<?php echo 'Dropbox' ?>
														</label>
													</div>
												<?php endif; ?>
												<?php if(SGBoot::isFeatureAvailable('GOOGLE_DRIVE')): ?>
													<div class="checkbox">
														<label for="cloud-gdrive" <?php echo empty($gdrive)?'data-toggle="tooltip" data-placement="right" title="'._backupGuardT('Google Drive is not active.',true).'"':''?>>
															<input type="checkbox" name="backupStorages[]" id="cloud-gdrive" value="<?php echo SG_STORAGE_GOOGLE_DRIVE?>" <?php echo in_array(SG_STORAGE_GOOGLE_DRIVE, $scheduleParams['selectedClouds'])?'checked="checked"':''?> <?php echo empty($gdrive)?'disabled="disabled"':''?>>
															<?php echo 'Google Drive' ?>
														</label>
													</div>
												<?php endif; ?>
												<?php if(SGBoot::isFeatureAvailable('AMAZON')): ?>
													<div class="checkbox">
														<label for="cloud-amazon" <?php echo empty($amazon)?'data-toggle="tooltip" data-placement="right" title="'._backupGuardT('Amazon S3 Drive is not active.',true).'"':''?>>
															<input type="checkbox" name="backupStorages[]" id="cloud-amazon" value="<?php echo SG_STORAGE_AMAZON?>" <?php echo in_array(SG_STORAGE_AMAZON, $scheduleParams['selectedClouds'])?'checked="checked"':''?> <?php echo empty($amazon)?'disabled="disabled"':''?>>
															<?php echo 'Amazon S3' ?>
														</label>
													</div>
												<?php endif; ?>
												<?php if(SGBoot::isFeatureAvailable('ONE_DRIVE')): ?>
													<div class="checkbox">
														<label for="cloud-one-drive" <?php echo empty($oneDrive)?'data-toggle="tooltip" data-placement="right" title="'._backupGuardT('One Drive is not active.', true).'"':''?>>
															<input type="checkbox" name="backupStorages[]" id="cloud-one-drive" value="<?php echo SG_STORAGE_ONE_DRIVE?>" <?php echo in_array(SG_STORAGE_ONE_DRIVE, $scheduleParams['selectedClouds'])?'checked="checked"':''?> <?php echo empty($oneDrive)?'disabled="disabled"':''?>>
															<?php echo 'One Drive' ?>
														</label>
													</div>
												<?php endif;?>
											</div>
											<div class="clearfix"></div>
										</div>
									<?php endif; ?>
									<!-- Background mode -->
									<?php if(SGBoot::isFeatureAvailable('BACKGROUND_MODE')): ?>
										<div class="checkbox">
											<label for="background-chbx">
												<input type="checkbox" name="backgroundMode" id="background-chbx" <?php echo $scheduleParams['isBackgroundMode']?'checked':''?>>
												<?php _backupGuardT('Background mode'); ?>
											</label>
										</div>
									<?php endif;?>
								</div>
							</div>
							<!-- Button (Double) -->
							<div class="form-group">
								<label class="col-md-4 sg-control-label" for="button1id"></label>
								<div class="col-md-8">
									<button type="button" id="sg-save-schedule" onclick="sgBackup.schedule()" class="btn btn-success pull-right"><?php _backupGuardT('Save');?></button>
								</div>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
	</div>
	<?php require_once(SG_PUBLIC_INCLUDE_PATH.'/footer.php'); ?>
</div>
