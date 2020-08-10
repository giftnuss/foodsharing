<?php

namespace Foodsharing\Modules\Login;

use Exception;
use Flourish\fImage;
use Flourish\fUpload;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;

class LoginXhr extends Control
{
	private $contentGateway;
	private $foodsaverGateway;
	private $loginGateway;

	public function __construct(
		LoginView $view,
		ContentGateway $contentGateway,
		FoodsaverGateway $foodsaverGateway,
		LoginGateway $loginGateway
	) {
		$this->view = $view;
		$this->contentGateway = $contentGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->loginGateway = $loginGateway;

		parent::__construct();
	}

	/**
	 * here arrives the photo what user cann upload in the quick join form.
	 */
	public function photoupload()
	{
		try {
			$uploader = new fUpload();
			$uploader->setMIMETypes(
				[
					'image/gif',
					'image/jpeg',
					'image/pjpeg',
					'image/png'
				],
				$this->translationHelper->s('upload_no_image')
			);
			$uploader->setMaxSize('5MB');

			if (($error = $uploader->validate('photo', true)) !== null) {
				$func = 'parent.join.photoUploadError(\'' . $error . '\');';
			} else {
				// move the uploaded file in a temp folder
				$image = $uploader->move(ROOT_DIR . 'tmp/', 'photo');

				// generate an unique name for the photo
				$name = uniqid() . '.' . strtolower($image->getExtension());

				$image->rename($name, true);

				$image = new fImage(ROOT_DIR . 'tmp/' . $name);

				$image->resize(800, 0);
				$image->saveChanges();

				$func = 'parent.join.readyUpload(\'' . $name . '\');';
			}
		} catch (Exception $e) {
			$func = 'parent.join.photoUploadError(\'' . $this->translationHelper->s('error_image') . '\');';
		}

		echo '<html>
<head><title>Upload</title></head><body onload="' . $func . '"></body>
</html>';
		exit();
	}
}
