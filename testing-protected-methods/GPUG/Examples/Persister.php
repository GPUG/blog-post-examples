<?php

namespace GPUG\Examples;

use GPUG\Examples\Logger;

abstract class Persister
{
	protected $_location = null;

	protected $_uploadInfo;
	protected $_log;

	public function __construct(array $uploadInfo, Logger $logger)
	{
		$this->_uploadInfo = $uploadInfo;
		$this->_logger = $logger;
	}

	public function persist()
	{

		try {
			// maybe do some preprocessing of the file
			// make sure it's safe, etc

			// call out to a protected method to actually store the file somewhere
			$this->_persist();
		} catch (\Exception $e) {
			// this exception will have some nitty gritty details useful for logging
			$this->_logger->log(
				'Unable to persist uploaded file: ' . $e->getMessage(), 
				Logger::LEVEL_OHNOES
			);

			// throw a new friendly exception
			throw new \Exception(
				'We were unable to store your file, maybe retry a little later?',
				0, // we don't need no stinking error codes
				$e
			);
		}

		$this->_logger->log('Successfully stored uploaded file in: ' . $this->getLocation());

		return true;
	}

	public function getLocation()
	{
		return $this->_location;
	}

	abstract protected function _persist();
}
