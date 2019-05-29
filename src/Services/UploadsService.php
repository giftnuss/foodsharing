<?php

namespace Foodsharing\Services;

use Imagick;

class UploadsService
{
	public function getFileLocation(string $uuid, int $width = 0, int $height = 0, int $quality = 0): string
	{
		$filename = $uuid;

		if ($height && $width) {
			$filename .= '-' . $width . 'x' . $height;
		}
		if ($quality) {
			$filename .= '-q' . $quality;
		}

		return implode('/', [
			ROOT_DIR,
			'data/uploads',
			$uuid[0],
			$uuid[1] . $uuid[2],
			$filename
		]);
	}

	public function isValidImage(string $file): bool
	{
		$img = new Imagick($file);

		return $img->valid();
	}

	public function stripImageExifData(string $input, string $output): void
	{
		$img = new Imagick($input);

		// rotate images according the EXIF rotation
		switch ($img->getImageOrientation()) {
			case Imagick::ORIENTATION_TOPLEFT:
				break;
			case Imagick::ORIENTATION_TOPRIGHT:
				$img->flopImage();
				break;
			case Imagick::ORIENTATION_BOTTOMRIGHT:
				$img->rotateImage('#000', 180);
				break;
			case Imagick::ORIENTATION_BOTTOMLEFT:
				$img->flopImage();
				$img->rotateImage('#000', 180);
				break;
			case Imagick::ORIENTATION_LEFTTOP:
				$img->flopImage();
				$img->rotateImage('#000', -90);
				break;
			case Imagick::ORIENTATION_RIGHTTOP:
				$img->rotateImage('#000', 90);
				break;
			case Imagick::ORIENTATION_RIGHTBOTTOM:
				$img->flopImage();
				$img->rotateImage('#000', 90);
				break;
			case Imagick::ORIENTATION_LEFTBOTTOM:
				$img->rotateImage('#000', -90);
				break;
			default: // Invalid orientation
				break;
		}
		$img->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);

		// store ICC Profiles
		$profiles = $img->getImageProfiles('icc', true);

		// remove all EXIF DATA
		$img->stripImage();

		// restore ICC Profiles
		if (!empty($profiles)) {
			$img->profileImage('icc', $profiles['icc']);
		}

		// write image
		$img->writeImage($output);
	}

	/**
	 * Resizes and crops $image to fit provided $width and $height.
	 */
	public function resizeImage(string $input, string $output, int $width, int $height, int $quality): void
	{
		$img = new Imagick($input);

		$ratio = $width / $height;

		// Original image dimensions.
		$old_width = $img->getImageWidth();
		$old_height = $img->getImageHeight();
		$old_ratio = $old_width / $old_height;

		// Determine new image dimensions to scale to.
		// Also determine cropping coordinates.
		if ($ratio > $old_ratio) {
			$new_width = $width;
			$new_height = $width / $old_width * $old_height;
			$crop_x = 0;
			$crop_y = (int)(($new_height - $height) / 2);
		} else {
			$new_width = $height / $old_height * $old_width;
			$new_height = $height;
			$crop_x = (int)(($new_width - $width) / 2);
			$crop_y = 0;
		}
		$img->resizeImage($new_width, $new_height, imagick::FILTER_LANCZOS, 0.9, true);
		$img->cropImage($width, $height, $crop_x, $crop_y);
		if ($quality) {
			$img->setImageCompressionQuality($quality);
		}
		// $img->resizeImage($width, $height, Imagick::FILTER_POINT, 1);
		$img->writeImage($output);
	}
}
