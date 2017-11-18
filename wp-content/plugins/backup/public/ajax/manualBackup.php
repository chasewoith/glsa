<?php
    require_once(dirname(__FILE__) . '/../boot.php');
    require_once(SG_BACKUP_PATH . 'SGBackup.php');
    require_once(SG_LIB_PATH . 'SGReloadHandler.php');
    require_once(SG_LIB_PATH . 'SGPauseHandler.php');

    try {
        $success = array('success' => 1);
        if (isAjax() && count($_POST)) {
            $options = $_POST;
            $error = array();

			if(@in_array('wp-content', $options['directory'])){
				$options['directory'] = array('wp-content');
			}

			if(@$options['backupType'] == SG_BACKUP_TYPE_FULL){

			}

            setManualBackupOptions($options);
        }
        else {
            $options = json_decode(SGConfig::get('SG_ACTIVE_BACKUP_OPTIONS'), true);
            setManualBackupOptions($options);
        }

        $wpContent = basename(WP_CONTENT_DIR);
        $upload_dir = wp_upload_dir();
        $wpUploads = basename($upload_dir['basedir']);

        $reloadHandler = new SGReloadHandler();

        if ($reloadHandler->canReload()) {
            $pauseHandler = new SGPauseHandler();

            $notificationCenter = SGNotificationCenter::getInstance();
            $notificationCenter->addObserver(SG_NOTIFICATION_SHOULD_CONTINUE_ACTION, $pauseHandler, 'shouldPause', true);
            $notificationCenter->addObserver(SG_NOTIFICATION_DID_FINISH_BACKUP, $pauseHandler, 'didFinishBackup');
        }

        $b = new SGBackup();
        $b->backup();

        if ($reloadHandler->canReload()) {
            $b->setReloading(true);
            $reloadHandler->reload();
        }

        die(json_encode($success));

    }
    catch (SGException $exception)
    {
        array_push($error, $exception->getMessage());
        die(json_encode($error));
    }

    function setManualBackupOptions($options)
    {
        $activeOptions = array('backupDatabase' => 0, 'backupFiles' => 0, 'ftp' => 0, 'gdrive' => 0, 'dropbox' => 0, 'background' => 0);

        //If background mode
        $isBackgroundMode = !empty($options['backgroundMode']) ? 1 : 0;
        SGConfig::set('SG_BACKUP_IN_BACKGROUND_MODE', $isBackgroundMode, false);
        $activeOptions['background'] = $isBackgroundMode;

        //If cloud backup
        if (!empty($options['backupCloud']) && count($options['backupStorages'])) {
            $clouds = $activeOptions['backupStorages'] = $options['backupStorages'];
            SGConfig::set('SG_BACKUP_UPLOAD_TO_STORAGES', implode(',', $clouds), false);
            $activeOptions['backupCloud'] = $options['backupCloud'];
            $activeOptions['gdrive'] = in_array(SG_STORAGE_GOOGLE_DRIVE, $options['backupStorages']) ? 1 : 0;
            $activeOptions['ftp'] = in_array(SG_STORAGE_FTP, $options['backupStorages']) ? 1 : 0;
            $activeOptions['dropbox'] = in_array(SG_STORAGE_DROPBOX, $options['backupStorages']) ? 1 : 0;
        }

        $activeOptions['backupType'] = $options['backupType'];
        if ($options['backupType'] == SG_BACKUP_TYPE_FULL) {
            SGConfig::set('SG_ACTION_BACKUP_DATABASE_AVAILABLE', 1, false);
            SGConfig::set('SG_ACTION_BACKUP_FILES_AVAILABLE', 1, false);
			SGConfig::set('SG_BACKUP_FILE_PATHS', 'wp-content', false);
            $activeOptions['backupDatabase'] = 1;
            $activeOptions['backupFiles'] = 1;
        }
        else if ($options['backupType'] == SG_BACKUP_TYPE_CUSTOM) {
            //If database backup
            $isDatabaseBackup = !empty($options['backupDatabase']) ? 1 : 0;
            SGConfig::set('SG_ACTION_BACKUP_DATABASE_AVAILABLE', $isDatabaseBackup, false);
            $activeOptions['backupDatabase'] = $isDatabaseBackup;

            //If files backup
			if(@in_array('wp-content', $options['directory'])) {
				SGConfig::set('SG_ACTION_BACKUP_FILES_AVAILABLE', 1, false);
				SGConfig::set('SG_BACKUP_FILE_PATHS', 'wp-content', false);
                $activeOptions['backupFiles'] = 1;
			}
            else if (!empty($options['backupFiles']) && count($options['directory'])) {
                $directories = $options['directory'];
                SGConfig::set('SG_ACTION_BACKUP_FILES_AVAILABLE', 1, false);
                SGConfig::set('SG_BACKUP_FILE_PATHS', implode(',', $directories), false);
                $activeOptions['backupFiles'] = 1;
                $activeOptions['directory'] = $directories;
            }
            else {
                SGConfig::set('SG_ACTION_BACKUP_FILES_AVAILABLE', 0, false);
                SGConfig::set('SG_BACKUP_FILE_PATHS', 0, false);
            }
        }
        SGConfig::set('SG_ACTIVE_BACKUP_OPTIONS', json_encode($activeOptions));
    }
