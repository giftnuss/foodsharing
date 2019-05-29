<?php

namespace Foodsharing\Controller;

use Foodsharing\Annotation\DisableCsrfProtection;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Uploads\UploadsGateway;
use Foodsharing\Services\UploadsService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
	public function getFileAction(string $uuid, string $filename, ParamFetcher $paramFetcher): void
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

		$mime = explode('/', $file['mimeType']);
		switch ($mime[0]) {
			case 'video':
			case 'audio':
			case 'image':
				header('Content-Type: ' . $file['mimeType']);
				break;
			case 'text':
				header('Content-Type: text/plain');
				break;
			default:
				header('Content-Type: application/octet-stream');
		}
		readfile($filename);
		die();
	}

	/**
	 * @DisableCsrfProtection
	 * @Rest\Post("uploads")
	 * @Rest\RequestParam(name="filename")
	 * @Rest\RequestParam(name="body")
	 *
	 * @return Response
	 */
	public function uploadFileAction(ParamFetcher $paramFetcher): Response
	{
		if ($this->session->id()) {
			$MAX_UPLOAD_FILE_SIZE = 1.5 * 1024 * 1024; // max 1.5 MB
		} else {
			$MAX_UPLOAD_FILE_SIZE = 0.3 * 1024 * 1024; // max 300 kB for non logged-in users
		}

		$MAX_BASE64_SIZE = 4 * ($MAX_UPLOAD_FILE_SIZE / 3);

		$filename = $paramFetcher->get('filename');
		$body_encoded = $paramFetcher->get('body');

		if (!$filename) {
			throw new HttpException(400, 'no filename provided');
		}
		if (!$body_encoded) {
			throw new HttpException(400, 'no body provided');
		}

		if (strlen($body_encoded) > $MAX_BASE64_SIZE) {
			throw new HttpException(413, 'file is bigger than ' . round($MAX_UPLOAD_FILE_SIZE / 1024 / 1024, 1) . ' MB');
		}

		$body = base64_decode($body_encoded, true);
		if (!$body) {
			throw new HttpException(400, 'invalid body');
		}

		$tempfile = tempnam(sys_get_temp_dir(), 'fs_upload');
		file_put_contents($tempfile, $body);

		$hash = hash_file('sha256', $tempfile);
		$size = filesize($tempfile);
		$mimeType = mime_content_type($tempfile);

		if (!$this->session->id() && strpos($mimeType, 'image/') !== 0) {
			unlink($tempfile);
			throw new HttpException(400, 'only images allowed for non loggedin users');
		}

		// image? check whether its valid
		if ((strpos($mimeType, 'image/') === 0) && !$this->uploadsService->isValidImage($tempfile)) {
			unlink($tempfile);
			throw new HttpException(400, 'invalid image provided');
		}

		$file = $this->uploadsGateway->addFile($this->session->id(), $hash, $size, $mimeType);

		if (!$file['isReuploaded']) {
			$path = $this->uploadsService->getFileLocation($file['uuid']);
			$dir = dirname($path);

			// create parent directories if they don't exist yet
			if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
				throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
			}

			// JPEG? strip exif data!
			// https://gitlab.com/foodsharing-dev/foodsharing/issues/375
			if ($mimeType === 'image/jpeg') {
				$this->uploadsService->stripImageExifData($tempfile, $path);
			} else {
				// otherwise just move it
				rename($tempfile, $path);
			}
		}
		$view = $this->view([
			'url' => '/api/uploads/' . $file['uuid'] . '/' . $filename,
			'uuid' => $file['uuid'],
			'filename' => $filename,
			'mimeType' => $mimeType,
			'filesize' => $size
		], 200);

		return $this->handleView($view);
	}
}
