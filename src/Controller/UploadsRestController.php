<?php

namespace Foodsharing\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Foodsharing\Annotation\DisableCsrfProtection;
use Foodsharing\Modules\Uploads\UploadsGateway;
use Foodsharing\Services\UploadsService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Foodsharing\Lib\Session;

class UploadsRestController extends AbstractFOSRestController
{
	/**
	 * @var UploadsGateway
	 */
	private $uploadsGateway;

	/**
	 * @var UploadsService
	 */
	private $uploadsService;

	/**
	 * @var Session
	 */
	private $session;

	public function __construct(UploadsGateway $uploadsGateway, UploadsService $uploadsService, Session $session)
	{
		$this->uploadsGateway = $uploadsGateway;
		$this->uploadsService = $uploadsService;
		$this->session = $session;
	}

	/**
	 * @DisableCsrfProtection
	 * @Rest\Get("uploads/{uuid}/{filename}", requirements={"uuid"="[0-9a-f\-]+", "filename"=".+"})
	 * @Rest\QueryParam(name="w", requirements="\d+", default=0, description="Max image width")
	 * @Rest\QueryParam(name="h", requirements="\d+", default=0, description="Max image height")
	 * @Rest\QueryParam(name="q", requirements="\d+", default=0, description="Image quality (between 1 and 100")
	 * resize behavior: fill
	 */
	public function getFileAction(string $uuid, string $filename, ParamFetcher $paramFetcher)
	{
		$width = $paramFetcher->get('w');
		$height = $paramFetcher->get('h');
		$quality = $paramFetcher->get('q');
		$doResize = $height || $width;

		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			http_response_code(304);
			die();
		}

		if ($height && $height < 16) {
			throw new HttpException(400, 'minium height is 16 pixel');
		}
		if ($height && $height > 500) {
			throw new HttpException(400, 'maximum height is 500 pixel');
		}
		if ($width && $width < 16) {
			throw new HttpException(400, 'minium width is 16 pixel');
		}
		if ($width && $width > 800) {
			throw new HttpException(400, 'maximum width is 800 pixel');
		}

		if (($height && !$width) || ($width && !$height)) {
			throw new HttpException(400, 'resizing requires both, height and width');
		}

		if ($quality && !$doResize) {
			throw new HttpException(400, 'quality parameter only allowed while resizing');
		}
		if ($quality && ($quality < 1 || $quality > 100)) {
			throw new HttpException(400, 'quality needs to be between 1 and 100');
		}

		$file = $this->uploadsGateway->getFile($uuid);
		if (!$file) {
			throw new HttpException(404, 'file not found');
		}

		// update lastAccess timestamp
		$this->uploadsGateway->touchFile($uuid);

		$filename = $this->uploadsService->getFileLocation($uuid);

		// resizing of images
		if ($doResize) {
			if (strpos($file['mimeType'], 'image/') !== 0) {
				throw new HttpException(400, 'resizing only possible with images');
			}

			if (!$quality) {
				$quality = 80;
			}

			$originalFilename = $filename;
			$filename = $this->uploadsService->getFileLocation($uuid, $width, $height, $quality);

			if (!file_exists($filename)) {
				$originalFilename = $this->uploadsService->getFileLocation($uuid);
				$this->uploadsService->resizeImage($originalFilename, $filename, $width, $height, $quality);
			}
		}

		header('Pragma: public');
		header('Cache-Control: max-age=' . (86400 * 7));
		header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400 * 7));
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Content-Type: ' . $file['mimeType']);

		readfile($filename);
		die();
	}

	/**
	 * @DisableCsrfProtection
	 * @Rest\Post("uploads")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function uploadFileAction()
	{
		$MAX_UPLOAD_FILE_SIZE = 1.5 * 1024 * 1024; // max 1.5MB

		// if (!$this->session->id()) {
		//     throw new HttpException(403, "not loggedin");
		// }

		if (!$_FILES['file']) {
			throw new HttpException(400, 'not file specified');
		}

		if ($_FILES['file']['size'] > $MAX_UPLOAD_FILE_SIZE) {
			// throw new HttpException(413, "file is bigger than ".round($MAX_UPLOAD_FILE_SIZE/1024/1024, 1)." MB");
		}

		// image? check whether its valid
		if (strpos($_FILES['file']['type'], 'image/') == 0) {
			if (!$this->uploadsService->isValidImage($_FILES['file']['tmp_name'])) {
				throw new HttpException(400, 'invalid image provided');
			}
		}

		$file = $this->uploadsGateway->addFile($_FILES['file']['tmp_name']);

		if (!$file['isReuploaded']) {
			$path = $this->uploadsService->getFileLocation($file['uuid']);
			$dir = dirname($path);

			// create parent directories if they don't exist yet
			@mkdir($dir, 0775, true);

			// JPEG? strip exif data!
			// https://gitlab.com/foodsharing-dev/foodsharing/issues/375
			if ($file['mimeType'] === 'image/jpeg') {
				$this->uploadsService->stripImageExifData($_FILES['file']['tmp_name'], $path);
			} else {
				// otherweise just move it
				move_uploaded_file($_FILES['file']['tmp_name'], $path);
			}
		}

		$view = $this->view([
			'url' => '/api/uploads/' . $file['uuid'] . '/' . $_FILES['file']['name'],
			'uuid' => $file['uuid'],
			'filename' => $_FILES['file']['name'],
			'mimeType' => $file['mimeType'],
			'filesize' => $file['filesize']
		], 200);

		return $this->handleView($view);
	}
}
