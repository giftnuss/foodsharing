<?php

namespace Foodsharing\Services;

use Exception;
use Flourish\fImage;
use Flourish\fException;
use UnexpectedValueException;

final class ImageService
{
	private $extensions = ['image/gif' => 'gif', 'image/jpeg' => 'jpg', 'image/png' => 'png'];

	/**
	 * Guesses a filename extension for a file.
	 *
	 * @param string $file the file
	 *
	 * @throws UnexpectedValueException if the file does not exist or has an
	 *                                  unknown image type
	 *
	 * @return string the guessed extension
	 */
	public function guessImageFileExtension(string $file): string
	{
		if (empty($file) || !file_exists($file)) {
			throw new UnexpectedValueException('File not found');
		}

		$fileInfo = finfo_open();
		$mime = finfo_file($fileInfo, $file, FILEINFO_MIME_TYPE);
		finfo_close($fileInfo);

		if ($mime !== null && isset($this->extensions[$mime])) {
			return $this->extensions[$mime];
		}

		throw new UnexpectedValueException('Unknown image type');
	}

	/**
	 * Creates a copy of the file in the destination directory with a unique
	 * name and creates rescaled versions of it.
	 *
	 * @param string $file the original file
	 * @param string $dstDir destination directory
	 * @param array $sizes key-value-pairs of size (int) and prefix (string)
	 *
	 * @throws UnexpectedValueException if the file does not exist or has an
	 *                                  unknown image type
	 * @throws fException if an error occures while resizing the image
	 *
	 * @return string the base name for the created files or null if the
	 *                     original file does not exist or rescaling failed
	 */
	public function createResizedPictures(string $file, string $dstDir, array $sizes): string
	{
		$extension = $this->guessImageFileExtension($file);
		$name = uniqid('', true) . '.' . strtolower($extension);

		try {
			foreach ($sizes as $s => $p) {
				$dst = $dstDir . $p . $name;
				copy($file, $dst);
				$img = new fImage($dst);
				$img->resize($s, $s);
				$img->saveChanges();
			}
		} catch (fException $e) {
			// in case of an error remove all created files
			$this->removeResizedPictures($dstDir, $name, $sizes);
			throw $e;
		}

		return $name;
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

	public function img($file = false, $size = 'mini', $format = 'q', $altimg = false)
	{
		if ($file === false) {
			$file = $_SESSION['client']['photo'];
		}

		// prevent path traversal
		$file = preg_replace('/%/', '', $file);
		$file = preg_replace('/\.+/', '.', $file);

		if (!empty($file) && file_exists('images/' . $file)) {
			if (!file_exists('images/' . $size . '_' . $format . '_' . $file)) {
				$this->resizeImg('images/' . $file, $size, $format);
			}

			return '/images/' . $size . '_' . $format . '_' . $file;
		}

		if ($altimg === false) {
			return '/img/' . $size . '_' . $format . '_avatar.png';
		}

		return $altimg;
	}

	private function resizeImg(string $img, string $width, string $format): bool
	{
		// prevent path traversal
		$img = preg_replace('/%/', '', $img);
		$img = preg_replace('/\.+/', '.', $img);
		if (file_exists($img)) {
			$opt = 'auto';
			if ($format == 'q') {
				$opt = 'crop';
			}

			try {
				$newimg = str_replace('/', '/' . $width . '_' . $format . '_', $img);
				copy($img, $newimg);
				$img = new fImage($newimg);

				if ($opt == 'crop') {
					$img->cropToRatio(1, 1);
					$img->resize($width, $width);
				} else {
					$img->resize($width, 0);
				}

				$img->saveChanges();

				return true;
			} catch (Exception $e) {
			}
		}

		return false;
	}

	public function avatar($foodsaver, $size = 'mini', $altimg = false): string
	{
		/*
		 * temporary for quiz
		 */
		$bg = '';
		if (isset($foodsaver['quiz_rolle'])) {
			switch ($foodsaver['quiz_rolle']) {
				case 1:
					$bg = 'box-sizing:border-box;border:3px solid #4A3520;';
					break;
				case 2:
					$bg = 'box-sizing:border-box;border:3px solid #599022;';
					break;
				case 3:
					$bg = 'box-sizing:border-box;border:3px solid #FFBB00;';
					break;
				case 4:
					$bg = 'box-sizing:border-box;border:3px solid #FF4800;';
					break;
				default:
					break;
			}
		}

		return '<span style="' . $bg . 'background-image:url(' . $this->img($foodsaver['photo'], $size, 'q', $altimg) . ');" class="avatar size-' . $size . ' sleepmode-' . $foodsaver['sleep_status'] . '"><i>' . $foodsaver['name'] . '</i></span>';
	}
}
