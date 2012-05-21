<?php

namespace GPUG\Examples\Persister;

use GPUG\Examples\Persister;

class File extends Persister
{
	protected function _persist()
	{
		$this->_location = tempnam(system_get_temp_dir(), 'Upload-');

		if (!$this->_moveUploadedFile()) {
			throw new \Exception('error moving uploaded file');
		}
	}

	protected function _moveUploadedFile()
	{
		return move_uploaded_file('upload', $this->_location);
	}
}
