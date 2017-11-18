<?php
require_once(SG_BACKUP_PATH.'SGIBackupDelegate.php');
require_once(SG_LIB_PATH.'SGArchive.php');

class SGBackupFiles implements SGArchiveDelegate
{
    private $rootDirectory = '';
    private $excludeFilePaths = array();
    private $filePath = '';
    private $stateFileHandle = null;
    private $sgbp = null;
    private $delegate = null;
    private $nextProgressUpdate = 0;
    private $totalBackupFilesCount = 0;
    private $currentBackupFileCount = 0;
    private $progressUpdateInterval = 0;
    private $warningsFound = false;
    private $dontExclude = array();

    public function __construct()
    {
        $this->rootDirectory = realpath(SGConfig::get('SG_APP_ROOT_DIRECTORY')).'/';

        $notificationCenter = SGNotificationCenter::getInstance();
        $notificationCenter->addObserver(SG_NOTIFICATION_SAVE_FILES_ACTION_STATE, $this, 'saveActionState', true);
    }

    public function setDelegate(SGIBackupDelegate $delegate)
    {
        $this->delegate = $delegate;
    }

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    public function addDontExclude($ex)
    {
        $this->dontExclude[] = $ex;
    }

    public function didFindWarnings()
    {
        return $this->warningsFound;
    }

    public function backup($filePath, $stateData)
    {
        $stateData = isset($stateData['filesBackup'])?$stateData['filesBackup']:false;

        if (!$stateData)
        {
            SGBackupLog::writeAction('backup files', SG_BACKUP_LOG_POS_START);
        }

        $excludeFilePaths = SGConfig::get('SG_BACKUP_FILE_PATHS_EXCLUDE');
        if (!$excludeFilePaths)
        {
            $this->excludeFilePaths = array();
        }
        else
        {
            $this->excludeFilePaths = explode(',', $excludeFilePaths);
        }

        $this->filePath = $filePath;
        $backupItems = SGConfig::get('SG_BACKUP_FILE_PATHS');
        $allItems = explode(',', $backupItems);

        $this->sgbp = new SGArchive($filePath, 'w', $stateData);
        $this->sgbp->setDelegate($this);

        if (!is_writable($filePath))
        {
            throw new SGExceptionForbidden('Could not create backup file: '.$filePath);
        }

        if (!$stateData)
        {
            SGBackupLog::write('Backup files: '.$backupItems);

            $this->resetBackupProgress($allItems);
            $this->warningsFound = false;

            SGBackupLog::write('Number of files to backup: '.$this->totalBackupFilesCount);
        }
        else
        {
            $this->nextProgressUpdate = $stateData['nextProgressUpdate'];
            $this->totalBackupFilesCount = $stateData['totalBackupFilesCount'];
            $this->currentBackupFileCount = $stateData['currentBackupFileCount'];
            $this->warningsFound = $stateData['warningsFound'];
        }

        $this->startFileArchivation($stateData);

        $this->sgbp->finalize();

        SGBackupLog::writeAction('backup files', SG_BACKUP_LOG_POS_END);
    }

    private function startFileArchivation($stateData)
    {
        $this->stateFileHandle = @fopen($this->filePath.'.state', 'r');

        if (isset($stateData['stateOffset']))
        {
            $len = strlen($stateData['filename'])+1;
            fseek($this->stateFileHandle, (int)$stateData['stateOffset']-$len);
        }

        while (($buffer = fgets($this->stateFileHandle)) !== false)
        {
            $buffer = trim($buffer);

            if (strlen($buffer) < 2)
            {
                continue;
            }

            if ($buffer[0]=='>')
            {
                $item = substr($buffer, 1);
                SGBackupLog::writeAction('backup file: '.$item, SG_BACKUP_LOG_POS_START);
            }
            else if ($buffer[0]=='<')
            {
                $item = substr($buffer, 1);
                SGBackupLog::writeAction('backup file: '.$item, SG_BACKUP_LOG_POS_END);
            }
            else
            {
                if (!$this->addFileToArchive($buffer, $stateData)) break;
                $stateData = array();
            }
        }

        //remove state file
        fclose($this->stateFileHandle);
        @unlink($this->filePath.'.state');
    }

    public function restore($filePath)
    {
        SGBackupLog::writeAction('restore files', SG_BACKUP_LOG_POS_START);

        $this->filePath = $filePath;

        $this->resetRestoreProgress(dirname($filePath));
        $this->warningsFound = false;

        $this->extractArchive($filePath);

        SGBackupLog::writeAction('restore files', SG_BACKUP_LOG_POS_END);
    }

    private function extractArchive($filePath)
    {
        $restorePath = $this->rootDirectory;

        $sgbp = new SGArchive($filePath, 'r');
        $sgbp->setDelegate($this);
        $sgbp->extractTo($restorePath);
    }

    public function getCorrectCdrFilename($filename)
    {
        $backupsPath = $this->pathWithoutRootDirectory(realpath(SG_BACKUP_DIRECTORY));

        if (strpos($filename, $backupsPath)===0)
        {
            $newPath = dirname($this->pathWithoutRootDirectory(realpath($this->filePath)));
            $filename = substr(basename(trim($this->filePath)), 0, -4); //remove sgbp extension
            return $newPath.'/'.$filename.'sql';
        }

        return $filename;
    }

    public function didExtractFile($filePath)
    {
        //update progress
        $this->currentBackupFileCount++;
        $this->updateProgress();
    }

    public function didFindExtractError($error)
    {
        $this->warn($error);
    }

    public function didCountFilesInsideArchive($count)
    {
        $this->totalBackupFilesCount = $count;
        SGBackupLog::write('Number of files to restore: '.$count);
    }

    public function saveActionState($data)
    {
        $data['nextProgressUpdate'] = $this->nextProgressUpdate;
        $data['totalBackupFilesCount'] = $this->totalBackupFilesCount;
        $data['currentBackupFileCount'] = $this->currentBackupFileCount;
        $data['warningsFound'] = $this->warningsFound;
        $data['stateOffset'] = ftell($this->stateFileHandle);

        $notificationCenter = SGNotificationCenter::getInstance();
        $notificationCenter->postDoNotification(SG_NOTIFICATION_SAVE_ACTION_STATE, array('filesBackup'=>$data));
    }

    private function resetBackupProgress($allItems)
    {
        $this->currentBackupFileCount = 0;
        $this->progressUpdateInterval = SGConfig::get('SG_ACTION_PROGRESS_UPDATE_INTERVAL');

        //create state file
        $statePath = $this->filePath.'.state';
        $this->stateFileHandle = @fopen($statePath, 'w');

        //get number of files to backup
        $this->totalBackupFilesCount = $this->getTotalCountOfBackupFiles($allItems);
        $this->nextProgressUpdate = $this->progressUpdateInterval;

        //close state file
        fclose($this->stateFileHandle);
    }

    private function resetRestoreProgress($restorePath)
    {
        $this->currentBackupFileCount = 0;
        $this->progressUpdateInterval = SGConfig::get('SG_ACTION_PROGRESS_UPDATE_INTERVAL');
        $this->nextProgressUpdate = $this->progressUpdateInterval;
    }

    private function getTotalCountOfBackupFiles($allItems)
    {
        $totalCount = 0;

        foreach ($allItems as $item)
        {
            $path = $this->rootDirectory.$item;

            $this->writeLineToStateFile('>'.$item);

            $count = 0;
            $this->numberOfFilesInDirectory($path, $count);

            $totalCount += $count;

            $this->writeLineToStateFile('<'.$item);
        }

        return $totalCount;
    }

    private function writeLineToStateFile($line)
    {
        fwrite($this->stateFileHandle, $line."\n");
        fflush($this->stateFileHandle);
    }

    private function pathWithoutRootDirectory($path)
    {
        return substr($path, strlen($this->rootDirectory));
    }

    private function shouldExcludeFile($path)
    {
        if (in_array($path, $this->dontExclude))
        {
            return false;
        }

        //get the name of the file/directory removing the root directory
        $file = $this->pathWithoutRootDirectory($path);

        //check if file/directory must be excluded
        foreach ($this->excludeFilePaths as $exPath)
        {
            if (strpos($file, $exPath)===0)
            {
                return true;
            }
        }

        return false;
    }

    private function numberOfFilesInDirectory($path, &$count = 0)
    {
        if ($this->shouldExcludeFile($path)) return;

        if (is_dir($path))
        {
            if ($handle = @opendir($path))
            {
                $filesFound = false;
                while (($file = readdir($handle)) !== false)
                {
                    if ($file === '.')
                    {
                        continue;
                    }
                    if ($file === '..')
                    {
                        continue;
                    }

                    $filesFound = true;
                    $this->numberOfFilesInDirectory($path.'/'.$file, $count);
                }

                if (!$filesFound)
                {
                    $file = substr($path, strlen($this->rootDirectory));
                    $file = str_replace('\\', '/', $file);
                    $this->writeLineToStateFile($file.'/');
                }

                closedir($handle);
            }
            else
            {
                $this->warn('Could not read directory (skipping): '.$path);
            }
        }
        else
        {
            if (is_readable($path))
            {
                $count++;

                $file = substr($path, strlen($this->rootDirectory));
                $file = str_replace('\\', '/', $file);
                $this->writeLineToStateFile($file);
            }
        }
    }

    public function cancel()
    {
        @unlink($this->filePath);
    }

    private function addFileToArchive($file, $stateData)
    {
        $path = $this->rootDirectory.$file;

        if ($this->shouldExcludeFile($path)) return true;

        //check if it is a directory
        if (is_dir($path))
        {
            $this->sgbp->addFile($file, ''); //create empty directory
            return true;
        }

        //it is a file, try to add it to archive
        if (is_readable($path))
        {
            $this->sgbp->addFileFromPath($file, $path, $stateData);
        }
        else
        {
            $this->warn('Could not read file (skipping): '.$path);
        }

        //update progress and check cancellation
        $this->currentBackupFileCount++;
        if ($this->updateProgress())
        {
            if ($this->delegate && $this->delegate->isCancelled())
            {
                return false;
            }
        }

        if (SGBoot::isFeatureAvailable('BACKGROUND_MODE') && $this->delegate->isBackgroundMode())
        {
            SGBackgroundMode::next();
        }

        return true;
    }

    private function warn($message)
    {
        $this->warningsFound = true;
        SGBackupLog::writeWarning($message);
    }

    private function updateProgress()
    {
        $progress = round($this->currentBackupFileCount*100.0/$this->totalBackupFilesCount);

        if ($progress>=$this->nextProgressUpdate)
        {
            $this->nextProgressUpdate += $this->progressUpdateInterval;

            if ($this->delegate)
            {
                $this->delegate->didUpdateProgress($progress);
            }

            return true;
        }

        return false;
    }
}
