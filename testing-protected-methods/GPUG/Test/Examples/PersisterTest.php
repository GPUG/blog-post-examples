<?php

namespace GPUG\Test\Examples;

$exampleDir = $GLOBALS['ROOT_DIR'] . '/testing-protected-methods/GPUG/Examples';
require_once $exampleDir . '/Logger.php';
require_once $exampleDir . '/Persister.php';

use GPUG\Examples\Logger;
use GPUG\Examples\Persister;

/**
 * @group testing-protected-methods
 */
class PersisterTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$logPath = '/tmp/test-log.log';
		if (file_exists($logPath)) {
			unlink($logPath);
		}

		$this->_logger = new Logger($logPath);
	}

	public function test_successful_persist()
	{
		$persisterMock = $this->getMockBuilder('GPUG\Examples\Persister')
			->setConstructorArgs(array(array(), $this->_logger))
			->setMethods(array('_persist', 'getLocation'))
			->getMock();

		$persisterMock->expects($this->any())
			->method('getLocation')
			->will($this->returnValue('/some/path'));

		$this->assertTrue($persisterMock->persist());

		$logContents = file_get_contents('/tmp/test-log.log');
		$logRegExp = '#^SEYZ \[\d+\] \d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[-+]\d{2}:\d{2}: Successfully stored uploaded file in: /some/path$#'; 
		$this->assertRegExp($logRegExp, $logContents);
	}

	public function test_failed_persist()
	{
		$persisterMock = $this->getMockBuilder('GPUG\Examples\Persister')
			->setConstructorArgs(array(array(), $this->_logger))
			->getMockForAbstractClass();

		$persisterMock->expects($this->any())
			->method('_persist')
			->will($this->throwException(new \Exception('the test denies it!')));

		try {
			$persisterMock->persist();
		} catch (\Exception $e) {
			$logContents = file_get_contents('/tmp/test-log.log');
			$logRegExp = '#^OHNOES \[\d+\] \d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[-+]\d{2}:\d{2}: Unable to persist uploaded file: the test denies it!$#'; 
			$this->assertRegExp($logRegExp, $logContents);
			return;
		}

		$this->fail('Expected execption was not thrown in test test_failed_persist');
	}
}
