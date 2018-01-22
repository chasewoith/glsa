<?php

function backupGuardgetSealPopup()
{
	$currentDate = time();
	$sgShouldShowPopup = SGConfig::get('SG_SHOULD_SHOW_POPUP') == null ? true : SGConfig::get('SG_SHOULD_SHOW_POPUP');
	$sgPluginInstallUpdateDate = SGConfig::get('SG_PLUGIN_INSTALL_UPDATE_DATE') == null ? time() : SGConfig::get('SG_PLUGIN_INSTALL_UPDATE_DATE');

	// check ig plugin is active for free days show poup
	if (($currentDate - $sgPluginInstallUpdateDate >= SG_PLUGIN_ACTIVE_INTERVAL) && $sgShouldShowPopup) {
		?>
		<script>
			window.SGPMPopupLoader=window.SGPMPopupLoader||{ids:[],popups:{},call:function(w,d,s,l,id){
				w['sgp']=w['sgp']||function(){(w['sgp'].q=w['sgp'].q||[]).push(arguments[0]);};
				var sg1=d.createElement(s),sg0=d.getElementsByTagName(s)[0];
				if(SGPMPopupLoader && SGPMPopupLoader.ids && SGPMPopupLoader.ids.length > 0){SGPMPopupLoader.ids.push(id); return;}
				SGPMPopupLoader.ids.push(id);
				sg1.onload = function(){SGPMPopup.openSGPMPopup();}; sg1.async=true; sg1.src=l;
				sg0.parentNode.insertBefore(sg1,sg0);
				return {};
			}};
			SGPMPopupLoader.call(window,document,'script','https://popupmaker.com/assets/lib/SGPMPopup.min.js','7c685e17');
		</script>
		<?php
		SGConfig::set('SG_SHOULD_SHOW_POPUP', 0);
	}

	return;
}

function backupGuardConvertDateTimezone($date, $dateFormat = "Y-m-d H:i:s", $timezone = "UTC")
{
	if (in_array($timezone, timezone_identifiers_list())) {
		$date = date_create($date);
		$timezone = timezone_open($timezone);
		date_timezone_set($date, $timezone);

		if (!$dateFormat) {
			$dateFormat = "Y-m-d H:i:s";
		}

		return date_format($date, $dateFormat);
	}

	return $date;
}

function backupGuardRemoveSlashes($value)
{
	if (SG_ENV_ADAPTER == SG_ENV_WORDPRESS) {
		return wp_unslash($value);
	}
	else {
		if (is_array($value)) {
			return array_map('stripslashes', $value);
		}

		return stripslashes($value);
	}
}

function backupGuardSanitizeTextField($value)
{
	if (SG_ENV_ADAPTER == SG_ENV_WORDPRESS) {
		if (is_array($value)) {
			return array_map('sanitize_text_field', $value);
		}

		return sanitize_text_field($value);
	}
	else {
		if (is_array($value)) {
			return array_map('strip_tags', $value);
		}

		return strip_tags($value);
	}
}

function backupGuardIsMultisite()
{
	if (SG_ENV_ADAPTER == SG_ENV_WORDPRESS) {
		return defined('BG_IS_MULTISITE')?BG_IS_MULTISITE:is_multisite();
	}
	else {
		return false;
	}
}

function backupGuardGetBanner($env, $type="plugin", $userType = null)
{
	require_once(SG_LIB_PATH.'BackupGuard/Client.php');
	$client = new BackupGuard\Client();
	return $client->getBanner(strtolower($env), $type, $userType);
}

function backupGuardGetFilenameOptions($options)
{
	$selectedPaths = explode(',', $options['SG_BACKUP_FILE_PATHS']);
	$pathsToExclude = explode(',', $options['SG_BACKUP_FILE_PATHS_EXCLUDE']);

	$opt = '';

	if (SG_ENV_ADAPTER == SG_ENV_WORDPRESS) {
		$opt .= 'opt(';

		if ($options['SG_BACKUP_TYPE'] == SG_BACKUP_TYPE_CUSTOM) {
			if ($options['SG_ACTION_BACKUP_DATABASE_AVAILABLE']) {
				$opt .= 'db_';
			}

			if ($options['SG_ACTION_BACKUP_FILES_AVAILABLE']) {
				if (in_array('wp-content', $selectedPaths)) {
					$opt .= 'wpc_';
				}
				if (!in_array('wp-content/plugins', $pathsToExclude)) {
					$opt .= 'plg_';
				}
				if (!in_array('wp-content/themes', $pathsToExclude)) {
					$opt .= 'thm_';
				}
				if (!in_array('wp-content/uploads', $pathsToExclude)) {
					$opt .= 'upl_';
				}
			}


		}
		else {
			$opt .= 'full';
		}

		$opt = trim($opt, "_");
		$opt .= ')_';
	}

	return $opt;
}

function backupGuardGenerateToken()
{
	return md5(time());
}

// Parse a URL and return its components
function backupGuardParseUrl($url)
{
	$urlComponents = parse_url($url);
	$domain = $urlComponents['host'];
	$port = '';

	if (isset($urlComponents['port']) && strlen($urlComponents['port'])) {
		$port = ":".$urlComponents['port'];
	}

	$domain = preg_replace("/(www|\dww|w\dw|ww\d)\./", "", $domain);

	$path = "";
	if (isset($urlComponents['path'])) {
	    $path = $urlComponents['path'];
	}

	return $domain.$port.$path;
}

function backupGuardIsReloadEnabled()
{
	// Check if reloads option is turned on
	return SGConfig::get('SG_BACKUP_WITH_RELOADINGS')?true:false;
}

function backupGuardGetBackupOptions($options)
{
	$backupOptions = array(
		'SG_BACKUP_UPLOAD_TO_STORAGES' => '',
		'SG_BACKUP_FILE_PATHS_EXCLUDE' => '',
		'SG_BACKUP_FILE_PATHS' => ''
	);

	//If background mode
	$isBackgroundMode = !empty($options['backgroundMode']) ? 1 : 0;

	if ($isBackgroundMode) {
		$backupOptions['SG_BACKUP_IN_BACKGROUND_MODE'] = $isBackgroundMode;
	}

	//If cloud backup
	if (!empty($options['backupCloud']) && count($options['backupStorages'])) {
		$clouds = $options['backupStorages'];
		$backupOptions['SG_BACKUP_UPLOAD_TO_STORAGES'] = implode(',', $clouds);
	}

	$backupOptions['SG_BACKUP_TYPE'] = $options['backupType'];

	if ($options['backupType'] == SG_BACKUP_TYPE_FULL) {
		$backupOptions['SG_ACTION_BACKUP_DATABASE_AVAILABLE']= 1;
		$backupOptions['SG_ACTION_BACKUP_FILES_AVAILABLE'] = 1;
		$backupOptions['SG_BACKUP_FILE_PATHS_EXCLUDE'] = SG_BACKUP_FILE_PATHS_EXCLUDE;
		$backupOptions['SG_BACKUP_FILE_PATHS'] = 'wp-content';
	}
	else if ($options['backupType'] == SG_BACKUP_TYPE_CUSTOM) {
		//If database backup
		$isDatabaseBackup = !empty($options['backupDatabase']) ? 1 : 0;
		$backupOptions['SG_ACTION_BACKUP_DATABASE_AVAILABLE'] = $isDatabaseBackup;

		//If db backup
		if($options['backupDBType']){
			$tablesToBackup = implode(',', $options['table']);
			$backupOptions['SG_BACKUP_TABLES_TO_BACKUP'] = $tablesToBackup;
		}

		//If files backup
		if (!empty($options['backupFiles']) && count($options['directory'])) {
			$backupFiles = explode(',', SG_BACKUP_FILE_PATHS);
			$filesToExclude = @array_diff($backupFiles, $options['directory']);

			if (in_array('wp-content', $options['directory'])) {
				$options['directory'] = array('wp-content');
			}
			else {
				$filesToExclude = array_diff($filesToExclude, array('wp-content'));
			}

			$filesToExclude = implode(',', $filesToExclude);
			if (strlen($filesToExclude)) {
				$filesToExclude = ','.$filesToExclude;
			}

			$backupOptions['SG_BACKUP_FILE_PATHS_EXCLUDE'] = SG_BACKUP_FILE_PATHS_EXCLUDE.$filesToExclude;
			$options['directory'] = backupGuardSanitizeTextField($options['directory']);
			$backupOptions['SG_BACKUP_FILE_PATHS'] = implode(',', $options['directory']);
			$backupOptions['SG_ACTION_BACKUP_FILES_AVAILABLE'] = 1;
		}
		else {
			$backupOptions['SG_ACTION_BACKUP_FILES_AVAILABLE'] = 0;
			$backupOptions['SG_BACKUP_FILE_PATHS'] = 0;
		}
	}
	return $backupOptions;
}

function backupGuardLoadStateData()
{
	if (file_exists(SG_BACKUP_DIRECTORY.SG_STATE_FILE_NAME)) {
		$sgState = new SGState();
		$stateFile = file_get_contents(SG_BACKUP_DIRECTORY.SG_STATE_FILE_NAME);
		$sgState = $sgState->factory($stateFile);
		return $sgState;
	}

	return false;
}

function backupGuardValidateApiCall($token)
{
	if (!strlen($token)) {
		exit();
	}

	$statePath = SG_BACKUP_DIRECTORY.SG_STATE_FILE_NAME;

	if (!file_exists($statePath)) {
		exit();
	}

	$state = file_get_contents($statePath);
	$state = json_decode($state, true);
	$stateToken = $state['token'];

	if ($stateToken != $token) {
		exit();
	}

	return true;
}

function backupGuardScanBackupsDirectory($path)
{
	$backups = scandir($path);
	$backupFolders = array();
	foreach ($backups as $key => $backup) {
		if ($backup == "." || $backup == "..") {
			continue;
		}

		if (is_dir($path.$backup)) {
			$backupFolders[$backup] = filemtime($path.$backup);
		}
	}
	// Sort(from low to high) backups by creation date
	asort($backupFolders);
	return $backupFolders;
}

function backupGuardSymlinksCleanup($dir)
{
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object == "." || $object == "..") {
				continue;
			}

			if (filetype($dir.$object) != "dir") {
				@unlink($dir.$object);
			}
			else {
				backupGuardSymlinksCleanup($dir.$object.'/');
				@rmdir($dir.$object);
			}
		}
	}
	else {
		@unlink($dir);
	}
	return;
}

function backupGuardRealFilesize($filename)
{
	$fp = fopen($filename, 'r');
	$return = false;
	if (is_resource($fp))
	{
		if (PHP_INT_SIZE < 8) // 32 bit
		{
			if (0 === fseek($fp, 0, SEEK_END))
			{
				$return = 0.0;
				$step = 0x7FFFFFFF;
				while ($step > 0)
				{
					if (0 === fseek($fp, - $step, SEEK_CUR))
					{
						$return += floatval($step);
					}
					else
					{
						$step >>= 1;
					}
				}
			}
		}
		else if (0 === fseek($fp, 0, SEEK_END)) // 64 bit
		{
			$return = ftell($fp);
		}
	}

	return $return;
}

function backupGuardFormattedDuration($startTs, $endTs)
{
	$unit = 'seconds';
	$duration = $endTs-$startTs;
	if ($duration>=60 && $duration<3600)
	{
		$duration /= 60.0;
		$unit = 'minutes';
	}
	else if ($duration>=3600)
	{
		$duration /= 3600.0;
		$unit = 'hours';
	}
	$duration = number_format($duration, 2, '.', '');

	return $duration.' '.$unit;
}

function backupGuardDeleteDirectory($dirName)
{
	$dirHandle = null;
	if (is_dir($dirName))
	{
		$dirHandle = opendir($dirName);
	}

	if (!$dirHandle)
	{
		return false;
	}

	while ($file = readdir($dirHandle))
	{
		if ($file != "." && $file != "..")
		{
			if (!is_dir($dirName."/".$file))
			{
				@unlink($dirName."/".$file);
			}
			else
			{
				backupGuardDeleteDirectory($dirName.'/'.$file);
			}
		}
	}

	closedir($dirHandle);
	return @rmdir($dirName);
}

function backupGuardDownloadFile($file, $type = 'application/octet-stream')
{
	$file = backupGuardRemoveSlashes($file);
	if (file_exists($file)) {
		header('Content-Description: File Transfer');
		header('Content-Type: '.$type);
		header('Content-Disposition: attachment; filename="'.basename($file).'";');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		readfile($file);
	}

	exit;
}

function backupGuardDownloadFileSymlink($safedir, $filename)
{
	$safedir  = backupGuardRemoveSlashes($safedir);
	$filename = backupGuardRemoveSlashes($filename);

	$downloaddir = SG_SYMLINK_PATH;
	$downloadURL = SG_SYMLINK_URL;

	if (!file_exists($downloaddir)) {
		mkdir($downloaddir, 0777);
	}

	$letters = 'abcdefghijklmnopqrstuvwxyz';
	srand((double) microtime() * 1000000);
	$string = '';

	for ($i = 1; $i <= rand(4,12); $i++) {
	   $q = rand(1,24);
	   $string = $string.$letters[$q];
	}

	$handle = opendir($downloaddir);
	while ($dir = readdir($handle)) {
		if ($dir == "." || $dir == "..") {
			continue;
		}

		if (is_dir($downloaddir.$dir)) {
			@unlink($downloaddir . $dir . "/" . $filename);
			@rmdir($downloaddir . $dir);
		}
	}

	closedir($handle);

	mkdir($downloaddir . $string, 0777);
	$res = @symlink($safedir . $filename, $downloaddir . $string . "/" . $filename);
	if ($res) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment;filename="'.$filename.'"');
		header('Content-Transfer-Encoding: binary');
		header("Location: " . $downloadURL . $string . "/" . $filename);
	}
	else{
		wp_die('Symlink / shortcut creation failed! Seems your server configurations don’t allow symlink creation, so we’re unable to provide you the direct download url. You can download your backup using any FTP client. All backups and related stuff we locate “/wp-content/uploads/backup-guard” directory. If you need this functionality, you should check out your server configurations and make sure you don’t have any limitation related to symlink creation.');
	}
	exit;
}

function backupGuardGetCurrentUrlScheme()
{
	return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')?'https':'http';
}

function backupGuardValidateLicense()
{
	if (!SGBoot::isFeatureAvailable('SCHEDULE')) {
		return true;
	}

	//only check once per day
	$ts = (int)SGConfig::get('SG_LICENSE_CHECK_TS');
	if (time() - $ts < SG_LICENSE_CHECK_TIMEOUT) {
		return true;
	}

	require_once(SG_LIB_PATH.'SGAuthClient.php');

	$url = site_url();

	$auth = SGAuthClient::getInstance();
	$res = $auth->validateUrl($url);

	if ($res === -1) { //login is required
		backup_guard_login_page();
		return false;
	}
	else if ($res === false) { //invalid license
		backup_guard_link_license_page();
		return false;
	}
	else {
		SGConfig::set('SG_LICENSE_CHECK_TS', time(), true);
		SGConfig::set('SG_LICENSE_KEY', $res, true);
	}

	return true;
}

//returns true if string $haystack ends with string $needle or $needle is an empty string
function backupGuardStringEndsWith($haystack, $needle)
{
	$length = strlen($needle);

	return $length === 0 ||
		(substr($haystack, -$length) === $needle);
}
//returns true if string $haystack starts with string $needle
function backupGuardStringStartsWith($haystack, $needle)
{
	$length = strlen($needle);
	return (substr($haystack, 0, $length) === $needle);
}

function backupGuardGetDbTables(){
	$sgdb = SGDatabase::getInstance();
	$tables = $sgdb->query("SHOW TABLES");
	$tablesKey = 'Tables_in_'.SG_DB_NAME;
	$tableNames = array();
	$customTablesToExclude = str_replace(' ', '', SGConfig::get('SG_TABLES_TO_EXCLUDE'));
	$tablesToExclude = explode(',', $customTablesToExclude);
	foreach ($tables as $table):
		$tableName = $table[$tablesKey];
		if($tableName != SG_ACTION_TABLE_NAME && $tableName != SG_CONFIG_TABLE_NAME && $tableName != SG_SCHEDULE_TABLE_NAME){
			array_push($tableNames, array('name'=>$tableName,
				'current'=> backupGuardStringStartsWith($tableName, SG_ENV_DB_PREFIX)? 'true':'false',
				'disabled'=>in_array($tableName,$tablesToExclude)? 'disabled':''
			));
		}
	endforeach;
	usort($tableNames, function ($name1, $name2){
		if(backupGuardStringStartsWith($name1['name'], SG_ENV_DB_PREFIX)){
			if(backupGuardStringStartsWith($name2['name'], SG_ENV_DB_PREFIX)){
				return 0;
			}
			return -1;
		}
		return 1;
	});
	return $tableNames;
}

function backupGuardGetBackupTablesHTML($defaultChecked = false){
	$tables = backupGuardGetDbTables();
	?>

	<div class="checkbox">
		<label for="custombackupdb-chbx">
			<input type="checkbox" class="sg-custom-option" name="backupDatabase" id="custombackupdb-chbx"  <?php echo $defaultChecked?'checked':'' ?>>
			<?php _backupGuardT('Backup database'); ?>
		</label>
		<div class="col-md-12 sg-checkbox sg-backup-db-options">
			<div class="checkbox">
				<label for="custombackupdbfull-radio" class="sg-backup-db-mode" title="Backup all tables found in the database">
					<input type="radio" name="backupDBType" id="custombackupdbfull-radio" value="0">
					<?php _backupGuardT('Full'); ?>
				</label>
				<label for="custombackupdbcurent-radio" class="sg-backup-db-mode" title="Backup tables related to the current WordPress installation. Only tables with <?php echo SG_ENV_DB_PREFIX ?> will be backed up">
					<input type="radio" name="backupDBType" id="custombackupdbcurent-radio" value="1">
					<?php _backupGuardT('Only WordPress'); ?>
				</label>
				<label for="custombackupdbcustom-radio" class="sg-backup-db-mode" title="Select tables you want to include in your backup">
					<input type="radio" name="backupDBType" id="custombackupdbcustom-radio" value="2">
					<?php _backupGuardT('Custom'); ?>
				</label>
				<!--Tables-->
				<div class="col-md-12 sg-custom-backup-tables">
					<?php foreach ($tables as $table): ?>
						<div class="checkbox">
							<label for="<?php echo $table['name']?>">
								<input type="checkbox" name="table[]" current="<?php echo $table['current'] ?>" <?php echo $table['disabled'] ?> id="<?php echo $table['name']?>" value="<?php echo $table['name'];?>">
								<?php echo basename($table['name']);?>
								<?php if($table['disabled']) {?>
									<span class="sg-disableText">(excluded from settings)</span>
								<?php } ?>
							</label>
						</div>
					<?php endforeach;?>
				</div>
			</div>
		</div>

	</div>

	<?php

}

function backupGuardIsAccountGold()
{
	return strpos("gold", SG_PRODUCT_IDENTIFIER)!== false;
}

function backupGuardGetProductName()
{
	$name = '';
	switch (SG_PRODUCT_IDENTIFIER) {
		case 'backup-guard-wp-silver':
			$name = 'Silver';
			break;
		case 'backup-guard-wp-platinum':
		case 'backup-guard-en-regular':
		case 'backup-guard-en':
		case 'backup-guard-en-extended':
			$name = 'Platinum';
			break;
		case 'backup-guard-wp-gold':
			$name = 'Gold';
			break;
		case 'backup-guard-wp-free':
			$name = 'Free';
			break;
	}

	return $name;
}

function backupGuardGetFileSelectiveRestore()
{
	?>
	<div class="col-md-12 sg-checkbox sg-restore-files-options">
		<div class="checkbox">
			<label for="restorefilesfull-radio" class="sg-restore-files-mode" >
				<input type="radio" name="restoreFilesType" checked id="restorefilesfull-radio" value="0">
				<?php _backupGuardT('Full'); ?>
			</label>

			<label for="restorefilescustom-radio" class="sg-restore-files-mode">
				<input type="radio" name="restoreFilesType" id="restorefilescustom-radio" value="1">
				<?php _backupGuardT('Custom'); ?>

			</label>
			<?php if (!SGBoot::isFeatureAvailable('SLECTIVE_RESTORE')): ?>
			<label class="sg-restore-files-mode">
				<a class="badge sg-badge-warning btn" href="<?php echo SG_BACKUP_SITE_PRICING_URL?>" target="_blank" data-toggle="tooltip" title="<?php echo SG_SILVER_TOOLTIP_TEXT ?>">
					<?php echo SG_BADGE_SILVER_PLUS_TEXT?>
				</a>
			</label>
			<?php endif; ?>

			<!--Files-->
			<div class="col-md-12 sg-file-selective-restore">
				<div id="fileSystemTreeContainer"></div>
			</div>
		</div>
	</div>
	<?php
}
