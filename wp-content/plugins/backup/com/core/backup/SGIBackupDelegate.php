<?php

interface SGIBackupDelegate
{
    public function isCancelled();
    public function didUpdateProgress($progress);
    public function isBackgroundMode();
    public function saveStateData($data);
}