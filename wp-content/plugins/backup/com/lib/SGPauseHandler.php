<?php

class SGPauseHandler
{
	private $startTs = 0;
	private $timeout = 15;

	public function __construct()
	{
		$this->startTs = time();
		$this->timeout = SGConfig::get('SG_RELOAD_INTERVAL');
	}

	public function shouldPause($data)
	{
		$secs = time()-$this->startTs;

		if ($this->timeout - $secs <= 0)
		{
			return false;
		}

		return true;
	}

	public function didFinishBackup()
	{
		exit;
	}
}
