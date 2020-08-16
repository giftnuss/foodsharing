<?php

namespace Foodsharing\Controller;

use Exception;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Uploads\UploadsGateway;
use Foodsharing\Modules\Uploads\UploadsTransactions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UploadsRestController extends AbstractFOSRestController
{
	/**
	 * @var UploadsGateway
	 */
	private $uploadsGateway;

	/**
	 * @var UploadsTransactions
	 */
	private $uploadsTransactions;

	/**
	 * @var Session
	 */
	private $session;

	private const MIN_WIDTH = 16;
	private const MAX_WIDTH = 800;
	private const MIN_HEIGHT = 16;
	private const MAX_HEIGHT = 500;
	private const MIN_QUALITY = 1;
	private const MAX_QUALITY = 100;
	private const DEFAULT_QUALITY = 80;
	private const MAX_UPLOAD_FILE_SIZE_LOGGED_IN = 1.5 * 1024 * 1024;
	private const MAX_UPLOAD_FILE_SIZE = 0.3 * 1024 * 1024;
	private const EXPIRATION_TIME_SECONDS = 86400 * 7; // one week

	public function __construct(UploadsGateway $uploadsGateway, UploadsTransactions $uploadsService, Session $session)
	{
		$this->uploadsGateway = $uploadsGateway;
		$this->uploadsTransactions = $uploadsService;
		$this->session = $session;
	}

	/**
	 * Returns the file with the requested UUID. Width and height must both be given or can be set both to 0 to
	 * indicate no resizing.
	 *
	 * @Rest\Get("uploads/{uuid}", requirements={"uuid"="[0-9a-f\-]+"})
	 * @Rest\QueryParam(name="w", requirements="\d+", default=0, description="Max image width")
	 * @Rest\QueryParam(name="h", requirements="\d+", default=0, description="Max image height")
	 * @Rest\QueryParam(name="q", requirements="\d+", default=0, description="Image quality (between 1 and 100)")
	 */
	public function getFileAction(Request $request, string $uuid, ParamFetcher $paramFetcher): void
	{
		$width = $paramFetcher->get('w');
		$height = $paramFetcher->get('h');
		$quality = $paramFetcher->get('q');
		$doResize = $height || $width;

		if ($request->headers->get('if_modified_since')) {
			http_response_code(304);
			die();
		}

		// check parameters
		if ($height && $height < self::MIN_HEIGHT) {
			throw new HttpException(400, 'minium height is ' . self::MIN_HEIGHT . ' pixel');
		}
		if ($height && $height > self::MAX_HEIGHT) {
			throw new HttpException(400, 'maximum height is ' . self::MAX_HEIGHT . ' pixel');
		}
		if ($width && $width < self::MIN_WIDTH) {
			throw new HttpException(400, 'minium width is ' . self::MIN_WIDTH . ' pixel');
		}
		if ($width && $width > self::MAX_WIDTH) {
			throw new HttpException(400, 'maximum width is ' . self::MAX_WIDTH . ' pixel');
		}

		if (($height && !$width) || ($width && !$height)) {
			throw new HttpException(400, 'resizing requires both, height and width');
		}

		if ($quality && !$doResize) {
			throw new HttpException(400, 'quality parameter only allowed while resizing');
		}
		if ($quality && ($quality < self::MIN_QUALITY || $quality > self::MAX_QUALITY)) {
			throw new HttpException(400, 'quality needs to be between ' . self::MIN_QUALITY . ' and ' . self::MAX_QUALITY);
		}

		try {
			$mimetype = $this->uploadsGateway->getMimeType($uuid);
		} catch (Exception $e) {
			throw new HttpException(404, 'file not found');
		}

		// update lastAccess timestamp
		$this->uploadsGateway->touchFile($uuid);

		$filename = $this->uploadsTransactions->getFileLocation($uuid);

		// resizing of images
		if ($doResize) {
			if (strpos($mimetype, 'image/') !== 0) {
				throw new HttpException(400, 'resizing only possible with images');
			}

			if (!$quality) {
				$quality = self::DEFAULT_QUALITY;
			}

			$originalFilename = $filename;
			$filename = $this->uploadsTransactions->getFileLocation($uuid, $width, $height, $quality);

			if (!file_exists($filename)) {
				$this->uploadsTransactions->resizeImage($originalFilename, $filename, $width, $height, $quality);
			}
		}

		// write response
		header('Pragma: public');
		header('Cache-Control: max-age=' . self::EXPIRATION_TIME_SECONDS);
		header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + self::EXPIRATION_TIME_SECONDS));
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

		$mime = explode('/', $mimetype);
		switch ($mime[0]) {
			case 'video':
			case 'audio':
			case 'image':
				header('Content-Type: ' . $mimetype);
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
	 * @Rest\Post("uploads")
	 * @Rest\RequestParam(name="filename")
	 * @Rest\RequestParam(name="body")
	 */
	public function uploadFileAction(ParamFetcher $paramFetcher): Response
	{
		$filename = $paramFetcher->get('filename');
		$bodyEncoded = $paramFetcher->get('body');

		// check uploaded body
		if (!$filename) {
			throw new HttpException(400, 'no filename provided');
		}
		if (!$bodyEncoded) {
			throw new HttpException(400, 'no body provided');
		}

		$maxSize = $this->session->id() ? self::MAX_UPLOAD_FILE_SIZE_LOGGED_IN : self::MAX_UPLOAD_FILE_SIZE;
		$maxBase64Size = 4 * ($maxSize / 3);
		if (strlen($bodyEncoded) > $maxBase64Size) {
			throw new HttpException(413, 'file is bigger than ' . round($maxSize / 1024 / 1024, 1) . ' MB');
		}

		$body = base64_decode($bodyEncoded, true);
		if (!$body) {
			throw new HttpException(400, 'invalid body');
		}

		// save to temp file
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
		if ((strpos($mimeType, 'image/') === 0) && !$this->uploadsTransactions->isValidImage($tempfile)) {
			unlink($tempfile);
			throw new HttpException(400, 'invalid image provided');
		}

		$file = $this->uploadsGateway->addFile($this->session->id(), $hash, $size, $mimeType);

		if (!$file['isReuploaded']) {
			$path = $this->uploadsTransactions->getFileLocation($file['uuid']);
			$dir = dirname($path);

			// create parent directories if they don't exist yeted
			if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
				throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
			}

			// JPEG? strip exif data!
			if ($mimeType === 'image/jpeg') {
				$this->uploadsTransactions->stripImageExifData($tempfile, $path);
			} else {
				// otherwise just move it
				rename($tempfile, $path);
			}
		}
		$view = $this->view([
			'url' => '/api/uploads/' . $file['uuid'],
			'uuid' => $file['uuid'],
			'filename' => $filename,
			'mimeType' => $mimeType,
			'filesize' => $size
		], 200);

		return $this->handleView($view);
	}
}
