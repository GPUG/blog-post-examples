<?php

namespace GPUG\Test\Examples\Persister;

$exampleDir = realpath($GLOBALS['ROOT_DIR'] . '/testing-protected-methods/GPUG/Examples');
require_once realpath($exampleDir . '/Logger.php');
require_once realpath($exampleDir . '/Persister.php');
require_once realpath($exampleDir . '/Persister/File.php');

use GPUG\Examples\Logger;
use GPUG\Examples\Persister\File;

/**
 * @group testing-protected-methods
 */
class FileTest extends \PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		$this->logger = $this->getMockBuilder('GPUG\Examples\Logger')
			->disableOriginalConstructor()
			->getMock();

		$uploadedFile = tempnam(sys_get_temp_dir(), 'UL-');
		file_put_contents($uploadedFile, "Hello World\n"); 

		$_FILES = array(
			'upload' => array(
				'name' => 'somefile.txt',
				'type' => 'text/plain',
				'size' => 13,
				'tmp_name' => $uploadedFile,
				'error' => UPLOAD_ERR_OK,
			),
		);

	}

	public function tearDown()
	{
		$tempDir = sys_get_temp_dir();
		$globPattern = $tempDir . DIRECTORY_SEPARATOR . 'UL-*';

		foreach (glob($globPattern) as $tempFile) {
			if (is_writable($tempFile)) {
				unlink($tempFile);
			}
		}
	}

	public function test_protected_persist_indirectly_via_public_persist_success()
	{
		$filePersister = new File($_FILES, $this->logger);
		$this->assertTrue($filePersister->persist());

		$persistedLocation = \PHPUnit_Framework_Assert::readAttribute($filePersister, '_location');
		$this->assertRegExp('/UL-/', $persistedLocation);
		$this->assertTrue(file_exists($persistedLocation));
		$this->assertEquals("Hello World\n", file_get_contents($persistedLocation));
	}

	public function test_protected_persist_indirectly_via_public_persist_failure()
	{
		$filePersisterMock = $this->getMockBuilder('GPUG\Examples\Persister\File')
			->setConstructorArgs(array($_FILES, $this->logger))
			->setMethods(array('_moveUploadedFile'))
			->getMock();

		$filePersisterMock->expects($this->once())
			->method('_moveUploadedFile')
			->will($this->returnValue(false));

		try {
			$filePersisterMock->persist();
		} catch (\Exception $e) {
			$this->assertNull(\PHPUnit_Framework_Assert::readAttribute($filePersisterMock, '_location'));
			$this->assertEquals('We were unable to store your file, maybe retry a little later?', $e->getMessage());

			$pe = $e->getPrevious();
			$this->assertNotNull($pe);
			$this->assertEquals('error moving uploaded file', $pe->getMessage());
			return;
		}

		$this->fail('Expected exception was not thrown in test_protected_persist_indirectly_via_public_persist_failure');

	}

	public function test_protected_persist_directly_with_reflection_success()
	{
		$filePersister = new File($_FILES, $this->logger);

		$reflectedPersist = new \ReflectionMethod($filePersister, '_persist');
		$reflectedPersist->setAccessible(true);
		$reflectedPersist->invoke($filePersister);

		$persistedLocation = \PHPUnit_Framework_Assert::readAttribute($filePersister, '_location');
		$this->assertRegExp('/UL-/', $persistedLocation);
		$this->assertTrue(file_exists($persistedLocation));
		$this->assertEquals("Hello World\n", file_get_contents($persistedLocation));
	}

	public function test_protected_persist_directly_with_refelection_failure()
	{

		$filePersisterMock = $this->getMockBuilder('GPUG\Examples\Persister\File')
			->setConstructorArgs(array($_FILES, $this->logger))
			->setMethods(array('_moveUploadedFile'))
			->getMock();

		$filePersisterMock->expects($this->once())
			->method('_moveUploadedFile')
			->will($this->returnValue(false));

		$reflectedPersist = new \ReflectionMethod($filePersisterMock, '_persist');
		$reflectedPersist->setAccessible(true);

		try {
			$reflectedPersist->invoke($filePersisterMock);
		} catch (\Exception $e) {
			$this->assertNull(\PHPUnit_Framework_Assert::readAttribute($filePersisterMock, '_location'));
			$this->assertEquals('error moving uploaded file', $e->getMessage());
			return;
		}

		$this->fail('Expected exception was not thrown in test_protected_persist_directly_with_reflection_failure');

	}

	public function test_protected_persist_with_testable_subclass_success()
	{
		$filePersister = new TestableFile($_FILES, $this->logger);
		$filePersister->_persistWrapper();

		$persistedLocation = \PHPUnit_Framework_Assert::readAttribute($filePersister, '_location');
		$this->assertRegExp('/UL-/', $persistedLocation);
		$this->assertTrue(file_exists($persistedLocation));
		$this->assertEquals("Hello World\n", file_get_contents($persistedLocation));
	}

	public function test_protected_persist_with_testable_subclass_failure()
	{

		$filePersisterMock = $this->getMockBuilder('GPUG\Test\Examples\Persister\TestableFile')
			->setConstructorArgs(array($_FILES, $this->logger))
			->setMethods(array('_moveUploadedFile'))
			->getMock();

		$filePersisterMock->expects($this->once())
			->method('_moveUploadedFile')
			->will($this->returnValue(false));

		try {
			$filePersisterMock->_persistWrapper();
		} catch (\Exception $e) {
			$this->assertNull(\PHPUnit_Framework_Assert::readAttribute($filePersisterMock, '_location'));
			$this->assertEquals('error moving uploaded file', $e->getMessage());
			return;
		}

		$this->fail('Expected exception was not thrown in test_protected_persist_directly_with_reflection_failure');

	}
}

class TestableFile extends File
{
	public function _persistWrapper()
	{
		$this->_persist();
	}
}
