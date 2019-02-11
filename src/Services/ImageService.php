<?php

namespace Foodsharing\Services;

use Flourish\fImage;
use Flourish\fException;

class ImageService
{
	private $extensions = ['image/gif' => 'gif', 'image/jpeg' => 'jpg', 'image/pjpeg' => 'jpg', 'image/png' => 'png'];

	/**
	 * Guesses a filename extension for a file. Returns the extension or null if
	 * the file does not exist or does not contain a known format.
	 *
	 * @param string $file the file
	 */
	public function guessImageFileExtension($file): ?string
	{
		if (empty($file) || !file_exists($file)) {
			return null;
		}

		try {
			$finfo = finfo_open();
			$mime = finfo_file($finfo, $file, FILEINFO_MIME_TYPE);
			finfo_close($finfo);

			if (!is_null($mime) && isset($this->extensions[$mime])) {
				return $this->extensions[$mime];
			}
		} catch (fException $e) {
		}

		return null;
	}

	/**
	 * Creates a copy of the file in the destination directory with a unique
	 * name and creates rescaled versions of it. Returns the base name for the
	 * created files or null if the original file does not exist or rescaling
	 * failed.
	 *
	 * @param string $file the original file
	 * @param string $dstDir destination directory
	 * @param array $sizes key-value-pairs of size (int) and prefix (string)
	 */
	public function createResizedPictures($file, $dstDir, $sizes): ?string
	{
		$extension = $this->guessImageFileExtension($file);
		if (is_null($extension)) {
			return null;
		}
		$name = uniqid() . '.' . strtolower($extension);

		try {
			foreach ($sizes as $s => $p) {
				$dst = $dstDir . $p . $name;
				copy($file, $dst);
				$img = new fImage($dst);
				$img->resize($s, $s);
				$img->saveChanges();
			}

			return $name;
		} catch (fException $e) {
			// in case of an error remove all created files
			$this->removeResizedPictures($dstDir, $name, $sizes);
		}

		return null;
	}

	/**
	 * Removes all rescaled versions of the picture with the given name
	 * and prefixes in the directory.
	 *
	 * @param string $dir the directory
	 * @param string $name the base name
	 * @param array $sizes key-value-pairs of size (int) and prefix (string)
	 */
	public function removeResizedPictures($dir, $name, $sizes): void
	{
		foreach ($sizes as $s => $p) {
			if (file_exists($dir . $p . $name)) {
				unlink($dir . $p . $name);
			}
		}
	}
}
