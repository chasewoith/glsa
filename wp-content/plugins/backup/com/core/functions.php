<?php
function realFilesize($filename)
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

function formattedDuration($startTs, $endTs)
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

function deleteDirectory($dirName)
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
                deleteDirectory($dirName.'/'.$file);
            }
        }
    }

    closedir($dirHandle);
    return @rmdir($dirName);
}

function downloadFile($file, $type = 'application/octet-stream')
{
    if (file_exists($file))
    {
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

function downloadFileSymlink($safedir, $filename)
{
    $downloaddir = SG_SYMLINK_PATH;
    $downloadURL = SG_SYMLINK_URL;

    if (!file_exists($downloaddir))
    {
        mkdir($downloaddir, 0777);
    }

    $letters = 'abcdefghijklmnopqrstuvwxyz';
    srand((double) microtime() * 1000000);
    $string = '';

    for ($i = 1; $i <= rand(4,12); $i++)
    {
       $q = rand(1,24);
       $string = $string.$letters[$q];
    }

    $handle = opendir($downloaddir);
    while ($dir = readdir($handle))
    {
        if ($dir == "." || $dir == "..")
        {
            continue;
        }

        if (is_dir($downloaddir.$dir))
        {
            @unlink($downloaddir . $dir . "/" . $filename);
            @rmdir($downloaddir . $dir);
        }
    }

    closedir($handle);

    mkdir($downloaddir . $string, 0777);
    symlink($safedir . $filename, $downloaddir . $string . "/" . $filename);
    header("Location: " . $downloadURL . $string . "/" . $filename);
    exit;
}

function shutdownAction($actionId, $actionType, $filePath, $dbFilePath, $backupObj)
{
    if ($backupObj->getReloading())
    {
        return;
    }

    $action = SGBackup::getAction($actionId);
    if ($action && ($action['status']==SG_ACTION_STATUS_IN_PROGRESS_DB || $action['status']==SG_ACTION_STATUS_IN_PROGRESS_FILES))
    {
        SGBackupLog::writeExceptionObject(new SGExceptionServerError('Execution time abort'));

        SGBackup::changeActionStatus($actionId, SG_ACTION_STATUS_ERROR);

        if ($actionType==SG_ACTION_TYPE_BACKUP)
        {
            @unlink($filePath);
            @unlink($dbFilePath);
        }
    }
}
