<?php

namespace GPUG\Examples\Persister;

use GPUG\Examples\Persister;

class File extends Persister
{
	protected function _persist()
	{
		$this->_location = tempnam(sys_get_temp_dir(), 'UL-');

		if (!$this->_moveUploadedFile()) {
			unlink($this->_location);
			$this->_location = null;
			throw new \Exception('error moving uploaded file');
		}
	}

	protected function _moveUploadedFile()
	{
		// for the purposes of this example I'm using the rename function instead of
		// move_uploaded_file. The move_uploaded_file functionfirst verifies that the file
		// being moved was actually uploaded and not just created by some fancy test
		// framework.
		// For tips on actually testing uploaded files checkout out:
		// http://qafoo.com/blog/013_testing_file_uploads_with_php.html
		return rename($this->_uploadInfo['upload']['tmp_name'], $this->_location);
	}
}
