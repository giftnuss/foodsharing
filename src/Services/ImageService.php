<?php

namespace Foodsharing\Services;

use Flourish\fImage;
use Flourish\fException;

final class ImageService
{
	private $extensions = ['image/gif' => 'gif', 'image/jpeg' => 'jpg', 'image/pjpeg' => 'jpg', 'image/png' => 'png'];

	/**
	 * Guesses a filename extension for a file.
	 *
	 * @param string $file the file
	 *
	 * @return string|null the extension or null if the file does not exist or
	 *                     does not contain a known format
	 */
	public function guessImageFileExtension(string $file): ?string
	{
		if (empty($file) || !file_exists($file)) {
			return null;
		}

		$fileInfo = finfo_open();
		$mime = finfo_file($fileInfo, $file, FILEINFO_MIME_TYPE);
		finfo_close($fileInfo);

		if ($mime !== null && isset($this->extensions[$mime])) {
			return $this->extensions[$mime];
		}

		return null;
	}

	/**
	 * Creates a copy of the file in the destination directory with a unique
	 * name and creates rescaled versions of it.
	 *
	 * @param string $file the original file
	 * @param string $dstDir destination directory
	 * @param array $sizes key-value-pairs of size (int) and prefix (string)
	 *
	 * @return string|null the base name for the created files or null if the
	 *                     original file does not exist or rescaling failed
	 */
	public function createResizedPictures(string $file, string $dstDir, array $sizes): ?string
	{
		$extension = $this->guessImageFileExtension($file);
		if ($extension === null) {
			return null;
		}
		$name = uniqid('', true) . '.' . strtolower($extension);

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
	public function removeResizedPictures(string $dir, string $name, array $sizes): void
	{
		foreach (array_values($sizes) as $p) {
			if (file_exists($dir . $p . $name)) {
				unlink($dir . $p . $name);
			}
		}
	}
}
