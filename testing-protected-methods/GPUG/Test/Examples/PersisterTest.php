<?php

namespace GPUG\Test\Examples;

$exampleDir = realpath($GLOBALS['ROOT_DIR'] . '/testing-protected-methods/GPUG/Examples');
require_once realpath($exampleDir . '/Logger.php');
require_once realpath($exampleDir . '/Persister.php');

use GPUG\Examples\Logger;
use GPUG\Examples\Persister;

/**
 * @group testing-protected-methods
 */
class PersisterTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->logPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test-log.log';
		$this->logger = new Logger($this->logPath);
	}

	public function tearDown()
	{
		if (file_exists($this->logPath)) {
			unlink($this->logPath);
		}
	}

	public function test_successful_persist()
	{
		$persisterMock = $this->getMockBuilder('GPUG\Examples\Persister')
			->setConstructorArgs(array(array(), $this->logger))
			->setMethods(array('_persist', 'getLocation'))
			->getMock();

		$persisterMock->expects($this->any())
			->method('getLocation')
			->will($this->returnValue('/some/path'));

		$this->assertTrue($persisterMock->persist());

		$logContents = file_get_contents($this->logPath);
		$logRegExp = '#^SEYZ \[\d+\] \d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[-+]\d{2}:\d{2}: Successfully stored uploaded file in: /some/path$#'; 
		$this->assertRegExp($logRegExp, $logContents);
	}

	public function test_failed_persist()
	{
		$persisterMock = $this->getMockBuilder('GPUG\Examples\Persister')
			->setConstructorArgs(array(array(), $this->logger))
			->getMockForAbstractClass();

		$persisterMock->expects($this->any())
			->method('_persist')
			->will($this->throwException(new \Exception('the test denies it!')));

		try {
			$persisterMock->persist();
		} catch (\Exception $e) {
			$logContents = file_get_contents($this->logPath);
			$logRegExp = '#^OHNOES \[\d+\] \d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[-+]\d{2}:\d{2}: Unable to persist uploaded file: the test denies it!$#'; 
			$this->assertRegExp($logRegExp, $logContents);
			return;
		}

		$this->fail('Expected execption was not thrown in test test_failed_persist');
	}
}
