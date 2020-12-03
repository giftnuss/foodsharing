<?php

namespace Flourish;

/**
 * Provides validation and movement of uploaded files.
 *
 * @copyright  Copyright (c) 2007-2012 Will Bond, others
 * @author     Will Bond [wb] <will@flourishlib.com>
 * @author     Will Bond, iMarc LLC [wb-imarc] <will@imarc.net>
 * @license    http://flourishlib.com/license
 *
 * @see       http://flourishlib.com/fUpload
 *
 * @version    1.0.0b15
 * @changes    1.0.0b15  Fixed an undefined variable error in ::setMaxSize() [wb, 2012-09-16]
 * @changes    1.0.0b14  Fixed some method signatures [wb, 2011-08-24]
 * @changes    1.0.0b13  Changed the class to throw fValidationException objects instead of fProgrammerException objects when the form is improperly configured - this is to prevent error logs when bad requests are sent by scanners/hackers [wb, 2011-08-24]
 * @changes    1.0.0b12  Fixed the ::filter() callback constant [wb, 2010-11-24]
 * @changes    1.0.0b11  Added ::setImageDimensions() and ::setImageRatio() [wb-imarc, 2010-11-11]
 * @changes    1.0.0b10  BackwardsCompatibilityBreak - renamed ::setMaxFilesize() to ::setMaxSize() to be consistent with fFile::getSize() [wb, 2010-05-30]
 * @changes    1.0.0b9   BackwardsCompatibilityBreak - the class no longer accepts uploaded files that start with a `.` unless ::allowDotFiles() is called - added ::setOptional() [wb, 2010-05-30]
 * @changes    1.0.0b8   BackwardsCompatibilityBreak - ::validate() no longer returns the `$_FILES` array for the file being validated - added `$return_message` parameter to ::validate(), fixed a bug with detection of mime type for text files [wb, 2010-05-26]
 * @changes    1.0.0b7   Added ::filter() to allow for ignoring array file upload field entries that did not have a file uploaded [wb, 2009-10-06]
 * @changes    1.0.0b6   Updated ::move() to use the new fFilesystem::createObject() method [wb, 2009-01-21]
 * @changes    1.0.0b5   Removed some unnecessary error suppression operators from ::move() [wb, 2009-01-05]
 * @changes    1.0.0b4   Updated ::validate() so it properly handles upload max filesize specified in human-readable notation [wb, 2009-01-05]
 * @changes    1.0.0b3   Removed the dependency on fRequest [wb, 2009-01-05]
 * @changes    1.0.0b2   Fixed a bug with validating filesizes [wb, 2008-11-25]
 * @changes    1.0.0b    The initial implementation [wb, 2007-06-14]
 */
class fUpload
{
	// The following constants allow for nice looking callbacks to static methods
	const check = 'fUpload::check';

	/**
	 * Checks to see if the field specified is a valid file upload field.
	 *
	 * @throws fValidationException  If `$throw_exception` is `TRUE` and the request was not a POST or the content type is not multipart/form-data
	 *
	 * @param  string  $field            The field to check
	 * @param  bool $throw_exception  If an exception should be thrown when there are issues with the form
	 *
	 * @return bool  If the field is a valid file upload field
	 */
	public static function check($field, $throw_exception = true)
	{
		if (isset($_GET[$field]) && $_SERVER['REQUEST_METHOD'] != 'POST') {
			if ($throw_exception) {
				throw new fValidationException(
					'Missing method="post" attribute in form tag'
				);
			}

			return false;
		}

		if (isset($_POST[$field]) && (!isset($_SERVER['CONTENT_TYPE']) || stripos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') === false)) {
			if ($throw_exception) {
				throw new fValidationException(
					'Missing enctype="multipart/form-data" attribute in form tag'
				);
			}

			return false;
		}

		return isset($_FILES) && isset($_FILES[$field]) && is_array($_FILES[$field]);
	}

	/**
	 * Composes text using fText if loaded.
	 *
	 * @param  string  $message    The message to compose
	 * @param  mixed   $component  A string or number to insert into the message
	 * @param  mixed   ...
	 *
	 * @return string  The composed and possible translated message
	 */
	protected static function compose($message)
	{
		$args = array_slice(func_get_args(), 1);

		return vsprintf($message, $args);
	}

	/**
	 * The error message to display if the mime types do not match.
	 *
	 * @var string
	 */
	private $mime_type_message = null;

	/**
	 * The mime types of files accepted.
	 *
	 * @var array
	 */
	private $mime_types = array();

	/**
	 * All requests that hit this method should be requests for callbacks.
	 *
	 * @internal
	 *
	 * @param  string $method  The method to create a callback for
	 *
	 * @return callback  The callback for the method requested
	 */
	public function __get($method)
	{
		return array($this, $method);
	}

	/**
	 * Returns the `$_FILES` array for the field specified.
	 *
	 * @param  string $field  The field to get the file array for
	 * @param  mixed  $index  If the field is an array file upload field, use this to specify which array index to return
	 *
	 * @return array  The file info array from `$_FILES`
	 */
	private function extractFileUploadArray($field, $index = null)
	{
		if ($index === null) {
			return $_FILES[$field];
		}

		if (!is_array($_FILES[$field]['name'])) {
			throw new fValidationException(
				'The field specified, %s, does not appear to be an array file upload field',
				$field
			);
		}

		if (!isset($_FILES[$field]['name'][$index])) {
			throw new fValidationException(
				'The index specified, %1$s, is invalid for the field %2$s',
				$index,
				$field
			);
		}

		$file_array = array();
		$file_array['name'] = $_FILES[$field]['name'][$index];
		$file_array['type'] = $_FILES[$field]['type'][$index];
		$file_array['tmp_name'] = $_FILES[$field]['tmp_name'][$index];
		$file_array['error'] = $_FILES[$field]['error'][$index];
		$file_array['size'] = $_FILES[$field]['size'][$index];

		return $file_array;
	}

	/**
	 * Moves an uploaded file from the temp directory to a permanent location.
	 *
	 * @throws fValidationException  When the form is not setup for file uploads, the `$directory` is somehow invalid or ::validate() thows an exception
	 *
	 * @param  string|fDirectory $directory  The directory to upload the file to
	 * @param  string            $field      The file upload field to get the file from
	 * @param  mixed             $index      If the field was an array file upload field, upload the file corresponding to this index
	 *
	 * @return fFile|null  An fFile (or fImage) object, or `NULL` if no file was uploaded
	 */
	public function move($directory, $field, $index = null)
	{
		if (!is_object($directory)) {
			$directory = new fDirectory($directory);
		}

		if (!$directory->isWritable()) {
			throw new fEnvironmentException(
				'The directory specified, %s, is not writable',
				$directory->getPath()
			);
		}

		if (!self::check($field)) {
			throw new fValidationException(
				'The field specified, %s, does not appear to be a file upload field',
				$field
			);
		}

		$file_array = $this->extractFileUploadArray($field, $index);
		$error = $this->validateField($file_array);
		if ($error) {
			throw new fValidationException($error);
		}

		// This will only ever be true if the file is optional
		if ($file_array['name'] == '' || $file_array['tmp_name'] == '' || $file_array['size'] == 0) {
			return null;
		}

		$file_name = fFilesystem::makeURLSafe($file_array['name']);

		$file_name = $directory->getPath() . $file_name;
		$file_name = fFilesystem::makeUniqueName($file_name);

		if (!move_uploaded_file($file_array['tmp_name'], $file_name)) {
			throw new fEnvironmentException('There was an error moving the uploaded file');
		}

		if (!chmod($file_name, 0644)) {
			throw new fEnvironmentException('Unable to change permissions on the uploaded file');
		}

		return fFilesystem::createObject($file_name);
	}

	/**
	 * Sets the file mime types accepted.
	 *
	 * @param  array  $mime_types  The mime types to accept
	 * @param  string $message     The message to display if the uploaded file is not one of the mime type specified
	 */
	public function setMIMETypes($mime_types, $message)
	{
		$this->mime_types = $mime_types;
		$this->mime_type_message = $message;
	}

	/**
	 * Validates a $_FILES array against the upload configuration.
	 *
	 * @param array $file_array  The $_FILES array for a single file
	 *
	 * @return string  The validation error message
	 */
	private function validateField($file_array)
	{
		if (empty($file_array['name'])) {
			return self::compose('Please upload a file');
		}

		if ($file_array['error'] == UPLOAD_ERR_FORM_SIZE || $file_array['error'] == UPLOAD_ERR_INI_SIZE) {
			$max_size = (!empty($_POST['MAX_FILE_SIZE'])) ? $_POST['MAX_FILE_SIZE'] : ini_get('upload_max_filesize');
			$max_size = (!is_numeric($max_size)) ? fFilesystem::convertToBytes($max_size) : $max_size;

			return self::compose(
				'The file uploaded is over the limit of %s',
				fFilesystem::formatFilesize($max_size)
			);
		}

		if (empty($file_array['tmp_name']) || empty($file_array['size'])) {
			return self::compose('Please upload a file');
		}

		if (!empty($this->mime_types) && file_exists($file_array['tmp_name'])) {
			$contents = file_get_contents($file_array['tmp_name'], false, null, 0, 4096);
			if (!in_array(fFile::determineMimeType($file_array['name'], $contents), $this->mime_types)) {
				return self::compose($this->mime_type_message);
			}
		}

		$file_info = fFilesystem::getPathInfo($file_array['name']);
		if (in_array(strtolower($file_info['extension']), array('php', 'php4', 'php5'))) {
			return self::compose('The file uploaded is a PHP file, but those are not permitted');
		}

		if (substr($file_array['name'], 0, 1) == '.') {
			return self::compose('The name of the uploaded file may not being with a .');
		}
	}
}

/*
 * Copyright (c) 2007-2012 Will Bond <will@flourishlib.com>, others
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
