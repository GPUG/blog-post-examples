<?php

namespace GPUG\Examples;

class Logger
{
	const LEVEL_SEYZ = 'SEYZ';
	const LEVEL_OHNOES = 'OHNOES';
	const LEVEL_KTHXBAI = 'KTHXBAI';

	protected $_logPath;
	protected $_logHandle;
	protected $_pid;

	public function __construct($logPath)
	{
		if (!is_writable($logPath)) {
			throw new \Exception('Unable to write to log path: ' . $logPath);
		}

		$this->_logPath = $logPath;
		$this->_logHandle = fopen($this->_logPath, 'a');
		$this->_pid = getmypid();

	}

	public function log($message, $level = self::LEVEL_SEYZ)
	{
		$now = new \DateTime(null, new \DateTimeZone('UTC'));

		$message = sprintf("%s [%s] %s: %s", $level, $this->_pid, $now->format('c'), $message);
		fwrite($this->_logHandle, $message);
	}

	public function __destruct()
	{
		fflush($this->_logHandle);
		fclose($this->_logHandle);
	}
}
