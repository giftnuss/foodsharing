<?php

namespace Foodsharing\Lib\Xhr;

use Exception;
use Flourish\fImage;
use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\EmailHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\DBConstants\Email\EmailStatus;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Email\EmailGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Modules\Message\MessageModel;
use Foodsharing\Modules\Region\ForumGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\StoreModel;
use Foodsharing\Modules\Store\TeamStatus;
use Foodsharing\Permissions\NewsletterEmailPermissions;
use Foodsharing\Permissions\RegionPermissions;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Services\ImageService;
use Foodsharing\Services\NotificationService;
use Foodsharing\Services\SanitizerService;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class XhrMethods
{
	private $model;
	private $mem;
	private $session;
	private $v_utils;
	private $xhrViewUtils;
	private $storeModel;
	private $messageModel;
	private $regionGateway;
	private $storePermissions;
	private $forumGateway;
	private $bellGateway;
	private $storeGateway;
	private $foodsaverGateway;
	private $emailGateway;
	private $mailboxGateway;
	private $imageManager;
	private $sanitizerService;
	private $emailHelper;
	private $imageService;
	private $identificationHelper;
	private $dataHelper;
	private $translationHelper;
	private $newsletterEmailPermissions;
	private $regionPermissions;
	private $notificationService;

	/**
	 * XhrMethods constructor.
	 *
	 * @param $model
	 */
	public function __construct(
		Mem $mem,
		Session $session,
		Db $model,
		Utils $viewUtils,
		ViewUtils $xhrViewUtils,
		StoreModel $storeModel,
		MessageModel $messageModel,
		RegionGateway $regionGateway,
		ForumGateway $forumGateway,
		BellGateway $bellGateway,
		StoreGateway $storeGateway,
		StorePermissions $storePermissions,
		FoodsaverGateway $foodsaverGateway,
		EmailGateway $emailGateway,
		MailboxGateway $mailboxGateway,
		ImageManager $imageManager,
		SanitizerService $sanitizerService,
		EmailHelper $emailHelper,
		ImageService $imageService,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper,
		TranslationHelper $translationHelper,
		NewsletterEmailPermissions $newsletterEmailPermissions,
		NotificationService $notificationService,
		RegionPermissions $regionPermission
	) {
		$this->mem = $mem;
		$this->session = $session;
		$this->model = $model;
		$this->v_utils = $viewUtils;
		$this->xhrViewUtils = $xhrViewUtils;
		$this->storeModel = $storeModel;
		$this->messageModel = $messageModel;
		$this->regionGateway = $regionGateway;
		$this->forumGateway = $forumGateway;
		$this->bellGateway = $bellGateway;
		$this->storeGateway = $storeGateway;
		$this->storePermissions = $storePermissions;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->emailGateway = $emailGateway;
		$this->mailboxGateway = $mailboxGateway;
		$this->imageManager = $imageManager;
		$this->sanitizerService = $sanitizerService;
		$this->emailHelper = $emailHelper;
		$this->imageService = $imageService;
		$this->identificationHelper = $identificationHelper;
		$this->dataHelper = $dataHelper;
		$this->translationHelper = $translationHelper;
		$this->newsletterEmailPermissions = $newsletterEmailPermissions;
		$this->regionPermissions = $regionPermission;
		$this->notificationService = $notificationService;
	}

	public function xhr_verify($data)
	{
		$regions = $this->regionGateway->getFsRegionIds((int)$data['fid']);

		if ($this->session->isAmbassadorForRegion($regions, false, true) || $this->session->isOrgaTeam()) {
			if ($countver = $this->model->qOne('SELECT COUNT(*) FROM fs_verify_history WHERE date BETWEEN NOW()- INTERVAL 20 SECOND AND now() AND bot_id = ' . $this->session->id())) {
				if ($countver > 10) {
					return json_encode([
						'status' => 0
					]);
				}
			}

			$countFetch = $this->model->qOne('
			SELECT 	count(a.`date`)
			FROM   `fs_abholer` a

			WHERE a.foodsaver_id = ' . (int)$data['fid'] . '
			AND   a.`date` > NOW()
		');

			if ($countFetch > 0) {
				return json_encode([
					'status' => 0
				]);
			}

			if ($this->model->update('UPDATE `fs_foodsaver` SET `verified` = ' . (int)$data['v'] . ' WHERE `id` = ' . (int)$data['fid'])) {
				$this->model->insert('
			INSERT INTO 	`fs_verify_history`
			(
				`fs_id`,
				`date`,
				`bot_id`,
				`change_status`
			)
			VALUES
			(
				' . (int)$data['fid'] . ',
				NOW(),
				' . $this->session->id() . ',
				' . (int)$data['v'] . '
			)
		');
				$this->bellGateway->delBellsByIdentifier('new-fs-' . (int)$data['fid']);

				return json_encode([
					'status' => 1
				]);
			}
		} else {
			return json_encode([
				'status' => 0
			]);
		}
	}

	/**
	 * Method for receiving store posts. Used on the store page to fetch the feed. Returns the store posts as
	 * prerendered HTML. As soon as the store feed frontend is redone in Vue.js, this method should be replaced by
	 * a GET action on the stores/{id}/posts REST resource (add an action method in StoreRestController).
	 *
	 * @param array $data: ['bid' => storeId] - an array only containing the storeId under the key 'bid'
	 *
	 * @return false|string - prerendered HTML of the store feed (if string)
	 */
	public function xhr_getPinPost($data)
	{
		$this->incLang('Store');
		$this->incLang('StoreUser');

		$storeId = (int)$data['bid'];

		if ($this->storePermissions->mayReadStoreWall($storeId)) {
			if ($allWallposts = $this->model->q('
				SELECT 	n.id,
						n.`text`,
						fs.name,
						fs.id AS fsid,
						UNIX_TIMESTAMP(n.zeit) AS zeit,
						fs.photo,
						n.milestone

				FROM  	fs_betrieb_notiz n,
						fs_foodsaver fs

				WHERE fs.id = n.foodsaver_id
				AND n.betrieb_id = ' . $storeId . '

				ORDER BY n.zeit DESC

				LIMIT 50')
			) {
				//$allWallposts = array_reverse($allWallposts);
				$html = '<table class="pintable">';
				$odd = 'odd';
				foreach ($allWallposts as $wallpost) {
					if ($odd == 'odd') {
						$odd = 'even';
					} else {
						$odd = 'odd';
					}
					$pic = $this->imageService->img($wallpost['photo']);

					$delete = '';
					if ($this->session->id() == $wallpost['fsid'] || $this->session->isOrgaTeam()) {
						$delete = '<span class="dot">·</span><a class="pdelete light" href="#p' . $wallpost['id'] . '" onclick="u_delPost(' . (int)$wallpost['id'] . ');return false;">' . $this->translationHelper->s('delete') . '</a>';
					}

					$msg = '<span class="msg">' . nl2br($wallpost['text']) . '</span>
						<div class="foot">
							<span class="time">' . $this->format_dt($wallpost['zeit']) . ' von ' . $wallpost['name'] . '</span>' . $delete . '
						</div>';

					if ($wallpost['milestone'] == 1) {
						$odd .= ' milestone';

						$msg = '
					<div class="milestone">
						<a href="/profile/' . (int)$wallpost['fsid'] . '">' . $wallpost['name'] . '</a> ' . $this->translationHelper->sv('betrieb_added', date('d.m.Y', $wallpost['zeit'])) . '
					</div>';

						$pic = 'img/milestone.png';
					} elseif ($wallpost['milestone'] == 2) {
						$odd .= ' milestone';
						$msg = '<span class="msg">' . $this->translationHelper->sv('accept_request', '<a href="/profile/' . (int)$wallpost['fsid'] . '">' . $this->model->getVal('name', 'foodsaver', $wallpost['fsid']) . '</a>') . '</span>';
					} elseif ($wallpost['milestone'] == 3) {
						$odd .= ' milestone';
						$pic = 'img/milestone.png';
						$msg = '<span class="msg"><strong>' . $this->translationHelper->sv('status_change_at', date('d.m.Y', $wallpost['zeit'])) . '</strong> ' . $this->translationHelper->s($wallpost['text']) . '</span>';
					} elseif ($wallpost['milestone'] == 5) {
						$odd .= ' milestone';
						$msg = '<span class="msg">' . $this->translationHelper->sv('quiz_dropped', '<a href="/profile/' . (int)$wallpost['fsid'] . '">' . $this->model->getVal('name', 'foodsaver', $wallpost['fsid']) . '</a>') . '</span>';
					}

					$html .= '
					<tr class="' . $odd . ' bpost bpost-' . $wallpost['id'] . '">
						<td class="img"><a href="/profile/' . (int)$wallpost['fsid'] . '"><img src="' . $pic . '" /></a></td>
						<td>' . $msg . '</td>
					</tr>';
				}

				return json_encode([
					'status' => 1,
					'html' => $html . '</table>'
				]);
			}
		}

		return json_encode([
			'status' => 0
		]);
	}

	private function format_dt($ts)
	{
		return date('d.m.Y H:i', $ts) . ' Uhr';
	}

	public function xhr_activeSwitch($data)
	{
		$allowed = [
			'blog_entry' => true
		];

		if ($this->session->may()) {
			if (isset($allowed[$data['t']])) {
				if ($this->model->update('UPDATE `fs_' . $data['t'] . '` SET `active` = ' . (int)$data['value'] . ' WHERE `id` = ' . (int)$data['id'])) {
					return 1;
				}
			}
		}

		return 0;
	}

	public function xhr_grabInfo(array $data)
	{
		if ($this->session->may()) {
			$this->mem->delPageCache('/?page=dashboard', $this->session->id());
			$fields = $this->dataHelper->unsetAll($data, ['lat', 'lon', 'stadt', 'plz', 'anschrift']);

			if ($this->foodsaverGateway->updateProfile($this->session->id(), $fields)) {
				return json_encode([
					'status' => 1,
					'script' => ''
				]);
			}
		}
	}

	public function xhr_childBezirke($data)
	{
		if (isset($data['parent'])) {
			if ($children = $this->regionGateway->getBezirkByParent((int)$data['parent'], $this->session->isOrgaTeam())) {
				return json_encode([
					'status' => 1,
					'html' => $this->xhrViewUtils->childBezirke($children, $data['parent'])
				]);
			}

			return json_encode([
				'status' => 0
			]);
		}
	}

	public function xhr_loadMarker($data)
	{
		$out = [];
		$out['status'] = 0;
		if (isset($data['types']) && is_array($data['types'])) {
			$out['status'] = 1;
			foreach ($data['types'] as $t) {
				if ($t == 'betriebe' && $this->session->may('fs')) {
					$team_status = [];
					$nkoorp = '';
					if (isset($data['options']) && is_array($data['options'])) {
						foreach ($data['options'] as $opt) {
							if ($opt == 'needhelpinstant') {
								$team_status[] = 'team_status = 2';
							} elseif ($opt == 'needhelp') {
								$team_status[] = 'team_status = 1';
							} elseif ($opt == 'nkoorp') {
								$nkoorp = ' AND betrieb_status_id NOT IN(3,5)';
							}
						}
					}

					if (!empty($team_status)) {
						$team_status = ' AND (' . implode(' OR ', $team_status) . ')';
					} else {
						$team_status = '';
					}

					$out['betriebe'] = $this->model->q(' SELECT `id`,lat,lon FROM fs_betrieb WHERE lat != "" ' . $team_status . $nkoorp);
				} elseif ($t == 'fairteiler') {
					$out['fairteiler'] = $this->model->q(' SELECT `id`,lat,lon,bezirk_id AS bid FROM fs_fairteiler WHERE lat != "" AND status = 1 ');
				} elseif ($t == 'baskets') {
					if ($baskets = $this->model->q('

					SELECT id, lat, lon, location_type
					FROM fs_basket
					WHERE `status` = 1

				')
					) {
						/*
						foreach ($baskets as $key => $b)
						{
							if($b['location_type'] !== 0)
							{
								//
							}
						}
						*/
						$out['baskets'] = $baskets;
					}
				}
			}
		}

		return json_encode($out);
	}

	public function xhr_uploadPictureRefactorMeSoon($data)
	{
		$request = Request::createFromGlobals();
		$response = JsonResponse::create([], 400);

		$namespace = 'workgroup';
		$width = 500;
		$height = 500;

		$file = $request->files->get('image');
		if ($file->isValid()) {
			try {
				$img = $this->imageManager->make($file->getPathname());
				if ($img->width() <= $width && $img->height() <= $height) {
					/*
					 * for now, we just check if the frontend did not want to betray us.
					 * later, we might want to have better resize / error handling
					 */
					switch ($img->mime()) {
						case 'image/jpeg':
							$ext = 'jpg';
							break;
						case 'image/png':
							$ext = 'png';
							break;
						case 'image/gif':
							$ext = 'gif';
							break;
						default:
							$ext = 'jpg';
					}
					$fullPath = sprintf('images/%s/%s.%s',
							$namespace,
							sha1_file($file->getPathname()),
							$ext);
					$internalName = sprintf('%s/%s.%s',
						$namespace,
						sha1_file($file->getPathname()),
						$ext);
					$img->save($fullPath);
					$response->setStatusCode(200);
					$response->setData([
						'fullPath' => $fullPath,
						'internalName' => $internalName
					]);
				} else {
					$response->setData([
						'width' => $img->width(),
						'height' => $img->height(),
						'max_width' => $width,
						'max_height' => $height
					]);
				}
			} catch (\Intervention\Image\Exception\NotReadableException $e) {
				throw $e;
			}
		}
		$response->send();
	}

	public function xhr_uploadPicture($data)
	{
		$func = '';
		$id = strtolower($data['id']);
		$id = preg_replace('/[^a-z0-9_]/', '', $id);
		if (isset($_FILES['uploadpic'])) {
			if ($this->is_allowed($_FILES['uploadpic'])) {
				$datein = str_replace('.jpeg', '.jpg', strtolower($_FILES['uploadpic']['name']));
				$ext = strtolower(substr($datein, strlen($datein) - 4, 4));

				$path = ROOT_DIR . 'images/' . $id;

				if (!is_dir($path)) {
					mkdir($path);
				}

				$newname = uniqid() . $ext;

				move_uploaded_file($_FILES['uploadpic']['tmp_name'], $path . '/orig_' . $newname);

				copy($path . '/orig_' . $newname, $path . '/' . $newname);
				$image = new fImage($path . '/' . $newname);
				$image->resize(600, 0);

				$image->saveChanges();

				if ($_GET['crop'] == 1) {
					$func = 'pictureCrop';
				} elseif (isset($_POST['resize'])) {
					return $this->pictureResize([
						'img' => $newname,
						'id' => $id,
						'resize' => $_POST['resize']
					]);
				}

				return '<html><head></head><body onload="parent.' . $func . '(\'' . $id . '\',\'' . $newname . '\');"></body></html>';

				echo uniqid();
			}
		}
	}

	public function xhr_cropagain($data)
	{
		$id = $this->session->id();
		if ($photo = $this->model->getVal('photo', 'foodsaver', $id)) {
			$path = ROOT_DIR . 'images';
			$img = $photo;

			$targ_w = $data['w'];
			$targ_h = $data['h'];
			$jpeg_quality = 100;

			$ext = explode('.', $img);
			$ext = end($ext);
			$ext = strtolower($ext);

			switch ($ext) {
				case 'gif':
					$img_r = imagecreatefromgif($path . '/' . $img);
					break;
				case 'jpg':
					$img_r = imagecreatefromjpeg($path . '/' . $img);
					break;
				case 'png':
					$img_r = imagecreatefrompng($path . '/' . $img);
					break;
				default:
					$img_r = null;
			}

			$dst_r = @imagecreatetruecolor($targ_w, $targ_h);

			if (!$dst_r) {
				return '0';
			}

			imagecopyresampled($dst_r, $img_r, 0, 0, $data['x'], $data['y'], $targ_w, $targ_h, $data['w'], $data['h']);

			$new_path = $path . '/crop_' . $img;

			@unlink($new_path);

			switch ($ext) {
				case 'gif':
					imagegif($dst_r, $new_path);
					break;
				case 'jpg':
					imagejpeg($dst_r, $new_path, $jpeg_quality);
					break;
				case 'png':
					imagepng($dst_r, $new_path, 0);
					break;
			}

			copy($path . '/' . $img, $path . '/thumb_' . $img);
			$image = new fImage($path . '/thumb_' . $img);
			$image->resize(150, 0);
			$image->saveChanges();

			copy($path . '/' . $img, $path . '/thumb_crop_' . $img);
			$image = new fImage($path . '/thumb_crop_' . $img);
			$image->resize(200, 0);
			$image->saveChanges();

			return '1';
		}

		return '0';
	}

	public function xhr_pictureCrop($data)
	{
		/*
		 * [ratio-val] => [{"x":37,"y":87,"w":500,"h":281},{"x":64,"y":0,"w":450,"h":450}]
		  [resize] => [250,528]
		 */

		$ratio = json_decode($_POST['ratio-val'], true);
		$resize = json_decode($_POST['resize']);

		// prevent path traversal
		$data['id'] = preg_replace('/[^a-z0-9\-_]/', '', $data['id']);
		$data['img'] = preg_replace('/[^a-z0-9\-_\.]/', '', $data['img']);

		if (is_array($ratio) && is_array($resize) && count($resize) < 5) {
			foreach ($ratio as $i => $r) {
				$i = preg_replace('/%/', '', $i);
				$i = preg_replace('/\.+/', '.', $i);
				$this->cropImg(ROOT_DIR . 'images/' . $data['id'], $data['img'], $i, $r['x'], $r['y'], $r['w'], $r['h']);
				foreach ($resize as $r) {
					if ($r < 1000) {
						copy(ROOT_DIR . 'images/' . $data['id'] . '/crop_' . $i . '_' . $data['img'], ROOT_DIR . 'images/' . $data['id'] . '/crop_' . $i . '_' . $r . '_' . $data['img']);
						$image = new fImage(ROOT_DIR . 'images/' . $data['id'] . '/crop_' . $i . '_' . $r . '_' . $data['img']);
						$image->resize($r, 0);
						$image->saveChanges();
					}
				}
			}

			copy(ROOT_DIR . 'images/' . $data['id'] . '/' . $data['img'], ROOT_DIR . 'images/' . $data['id'] . '/thumb_' . $data['img']);
			$image = new fImage(ROOT_DIR . 'images/' . $data['id'] . '/thumb_' . $data['img']);
			$image->resize(150, 0);
			$image->saveChanges();

			return '<html><head></head><body onload="parent.pictureReady(\'' . $data['id'] . '\',\'' . $data['img'] . '\');"></body></html>';
		}
	}

	private function cropImg($path, $img, int $i, int $x, int $y, int $w, int $h)
	{
		if ($w > 2000) {
			$w = 2000;
		}
		if ($h > 2000) {
			$h = 2000;
		}

		$targ_w = $w;
		$targ_h = $h;
		$jpeg_quality = 100;

		$ext = explode('.', $img);
		$ext = end($ext);
		$ext = strtolower($ext);

		switch ($ext) {
			case 'gif':
				$img_r = imagecreatefromgif($path . '/' . $img);
				break;
			case 'jpg':
				$img_r = imagecreatefromjpeg($path . '/' . $img);
				break;
			case 'png':
				$img_r = imagecreatefrompng($path . '/' . $img);
				break;
			default:
				$img_r = null;
		}

		$dst_r = imagecreatetruecolor($targ_w, $targ_h);

		imagecopyresampled($dst_r, $img_r, 0, 0, $x, $y, $targ_w, $targ_h, $w, $h);

		$new_path = $path . '/crop_' . $i . '_' . $img;

		@unlink($new_path);

		switch ($ext) {
			case 'gif':
				imagegif($dst_r, $new_path);
				break;
			case 'jpg':
				imagejpeg($dst_r, $new_path, $jpeg_quality);
				break;
			case 'png':
				imagepng($dst_r, $new_path, 0);
				break;
		}
	}

	private function pictureResize($data)
	{
		$id = preg_replace('/[^a-z0-9\-_]/', '', $data['id']);
		$img = preg_replace('/[^a-z0-9\-_\.]/', '', $data['img']);

		$resize = json_decode($data['resize'], true);

		if (is_array($resize) && count($resize) < 5) {
			foreach ($resize as $r) {
				if ($r < 1000) {
					$r = (int)$r;
					copy(ROOT_DIR . 'images/' . $id . '/' . $img, ROOT_DIR . 'images/' . $id . '/' . $r . '_' . $img);
					$image = new fImage(ROOT_DIR . 'images/' . $id . '/' . $r . '_' . $img);
					$image->resize($r, 0);
					$image->saveChanges();
				}
			}
		}

		copy(ROOT_DIR . 'images/' . $id . '/' . $img, ROOT_DIR . 'images/' . $id . '/thumb_' . $img);
		$image = new fImage(ROOT_DIR . 'images/' . $id . '/thumb_' . $img);
		$image->resize(150, 0);
		$image->saveChanges();

		return '<html><head></head><body onload="parent.pictureReady(\'' . $id . '\',\'' . $img . '\');"></body></html>';
	}

	public function xhr_getRecip($data)
	{
		if ($this->session->may()) {
			$fs = $this->foodsaverGateway->xhrGetFoodsaver($data);

			return json_encode($fs);
		}
	}

	public function xhr_addPhoto($data)
	{
		if (!$this->session->may()) {
			return XhrResponses::PERMISSION_DENIED;
		}

		$data = $this->dataHelper->getPostData();

		if (isset($data['fs_id'])) {
			$user_id = (int)$data['fs_id'];

			if (isset($_FILES['photo']) && (int)$_FILES['photo']['size'] > 0) {
				$ext = explode('.', $_FILES['photo']['name']);
				$ext = strtolower(end($ext));

				@unlink('./images/' . $user_id . '.' . $ext);

				$file = $this->makeUnique() . '.' . $ext;
				if (move_uploaded_file($_FILES['photo']['tmp_name'], './images/' . $file)) {
					$image = new fImage('./images/' . $file);
					$image->resize(800, 800);
					$image->saveChanges();

					copy('./images/' . $file, './images/thumb_crop_' . $file);
					copy('./images/' . $file, './images/crop_' . $file);

					$image = new fImage('./images/thumb_crop_' . $file);
					$image->cropToRatio(35, 45);
					$image->resize(200, 200);
					$image->saveChanges();

					$image = new fImage('./images/crop_' . $file);
					$image->cropToRatio(35, 45);
					$image->resize(600, 600);
					$image->saveChanges();

					copy('./images/thumb_crop_' . $file, './images/mini_q_' . $file);
					$image = new fImage('./images/mini_q_' . $file);
					$image->cropToRatio(1, 1);
					$image->resize(35, 35);
					$image->saveChanges();

					copy('./images/thumb_crop_' . $file, './images/130_q_' . $file);
					$image = new fImage('./images/130_q_' . $file);
					$image->cropToRatio(1, 1);
					$image->resize(130, 130);
					$image->saveChanges();

					@unlink('./tmp/tmp_' . $file);

					$this->foodsaverGateway->updatePhoto($user_id, str_replace('/', '', $file));
					$this->session->setPhoto($file);

					return '<html><head></head><body onload="parent.uploadPhotoReady(' . $user_id . ',\'./images/mini_q_' . $file . '\');"></body></html>';
				}
			}
		}
	}

	private function makeUnique()
	{
		return md5(date('Y-m-d H:i:s') . ':' . uniqid());
	}

	public function xhr_continueMail($data)
	{
		if ($this->newsletterEmailPermissions->mayAdministrateNewsletterEmail()) {
			$mail_id = (int)$data['id'];

			$mail = $this->emailGateway->getOne_send_email($mail_id);

			$bezirk = $this->regionGateway->getMailBezirk($this->session->getCurrentRegionId());
			$bezirk['email'] = EMAIL_PUBLIC;
			$bezirk['email_name'] = EMAIL_PUBLIC_NAME;
			$recip = $this->emailGateway->getMailNext($mail_id);

			$mailbox = $this->mailboxGateway->getMailbox((int)$mail['mailbox_id']);
			$mailbox['email'] = $mailbox['name'] . '@' . NOREPLY_EMAIL_HOST;

			$sender = $this->model->getValues(['geschlecht', 'name'], 'foodsaver', $this->session->id());

			if (empty($recip)) {
				return json_encode(['status' => 2, 'comment' => 'Es wurden alle E-Mails verschickt']);
				exit();
			}

			$this->emailGateway->setEmailStatus($mail['id'], $recip, EmailStatus::STATUS_INITIALISED);

			foreach ($recip as $fs) {
				$anrede = 'Liebe/r';
				if ($fs['geschlecht'] == 1) {
					$anrede = 'Lieber';
				} elseif ($fs['geschlecht'] == 2) {
					$anrede = 'Liebe';
				} else {
					$anrede = 'Liebe/r';
				}

				$search = ['{NAME}', '{ANREDE}', '{EMAIL}'];
				$replace = [$fs['name'], $anrede, $fs['email']];

				$attach = false;
				if (!empty($mail['attach'])) {
					$attach = json_decode($mail['attach'], true);
				}

				$message = str_replace($search, $replace, $mail['message']);
				$subject = str_replace($search, $replace, $mail['name']);

				$check = false;
				if ($this->emailHelper->libmail($mailbox, $fs['email'], $subject, $message, $attach, $fs['token'])) {
					$check = true;
				}

				if (!$check) {
					$this->emailGateway->setEmailStatus($mail['id'], [$fs['id']], EmailStatus::STATUS_INVALID_MAIL);
				} else {
					$this->emailGateway->setEmailStatus($mail['id'], [$fs['id']], EmailStatus::STATUS_SENT);
				}
			}

			$mails_left = $this->emailGateway->getMailsLeft($mail['id']);
			if ($mails_left) {
				// throttle to 5 mails per second here to avoid queue bloat
				sleep(2);
			}

			return json_encode(['left' => $mails_left, 'status' => 1, 'comment' => 'Versende E-Mails ... (aktuelle E-Mail-Adresse: ' . $fs['email'] . ')']);
		}

		return 0;
	}

	public function xhr_uploadPhoto($data)
	{
		$func = '';

		if (isset($_POST['action']) && $_POST['action'] == 'upload') {
			$id = strip_tags($_POST['pic_id']);
			if (isset($_FILES['uploadpic'])) {
				$error = 0;
				$datei = $_FILES['uploadpic']['tmp_name'];
				// prevent path traversal
				$datei = preg_replace('/%/', '', $datei);
				$datei = preg_replace('/\.+/', '.', $datei);

				$datein = $_FILES['uploadpic']['name'];
				$datein = strtolower($datein);
				$datein = str_replace('.jpeg', '.jpg', $datein);
				$dateiendung = strtolower(substr($datein, strlen($datein) - 4, 4));
				if ($this->is_allowed($_FILES['uploadpic'])) {
					try {
						$file = $this->makeUnique() . $dateiendung;
						move_uploaded_file($datei, './tmp/' . $file);
						$image = new fImage('./tmp/' . $file);
						$image->resize(550, 0);
						$image->saveChanges();
					} catch (Exception $e) {
						$func = 'parent.pic_error(\'Deine Datei schein nicht in Ordnung zu sein, nimm am besten ein normales jpg Bild\',\'' . $id . '\');';
					}

					$func = 'parent.fotoupload(\'' . $file . '\',\'' . $id . '\');';
				} else {
					$func = 'parent.pic_error(\'Deine Datei schein nicht in Ordnung zu sein, nimm am besten ein normales jpg Bild\',\'' . $id . '\');';
				}
			}
		} elseif (isset($_POST['action']) && $_POST['action'] == 'crop') {
			$file = str_replace('/', '', $_POST['file']);

			if ($img = $this->cropImage($file, $_POST['x'], $_POST['y'], $_POST['w'], $_POST['h'])) {
				$id = strip_tags($_POST['pic_id']);

				@unlink('images/' . $file);
				@unlink('images/crop_' . $file);
				@unlink('images/thumb_crop_' . $file);

				copy('tmp/' . $file, 'images/' . $file);
				copy('tmp/crop_' . $file, 'images/crop_' . $file);
				copy('tmp/thumb_crop_' . $file, 'images/thumb_crop_' . $file);

				@unlink('tmp/' . $file);
				@unlink('tmp/crop_' . $file);
				@unlink('tmp/thumb_crop_' . $file);

				$this->makeThumbs($file);

				$this->foodsaverGateway->updatePhoto($this->session->id(), $file);

				$func = 'parent.picFinish(\'' . $img . '\',\'' . $id . '\');';
			} else {
				$func = 'alert(\'Es ist ein Fehler aufgetreten, Sorry, probiers nochmal\');';
			}
		}

		echo '<html>
	<head><title>Upload</title></head><body onload="' . $func . '"></body>
	</html>';
	}

	private function is_allowed($img)
	{
		$img['name'] = strtolower($img['name']);
		$img['type'] = strtolower($img['type']);

		$allowed = ['jpg' => true, 'jpeg' => true, 'png' => true, 'gif' => true];

		$filename = $img['name'];
		$parts = explode('.', $filename);
		$ext = end($parts);

		$allowed_mime = ['image/gif' => true, 'image/jpeg' => true, 'image/png' => true];

		if (isset($allowed[$ext])) {
			return true;
		}

		return false;
	}

	private function cropImage($image, $x, $y, $w, $h)
	{
		if ($w > 2000 || $h > 2000) {
			return false;
		}

		$targ_w = 467;
		$targ_h = 600;
		$jpeg_quality = 100;

		$ext = explode('.', $image);
		$ext = end($ext);
		$ext = strtolower($ext);

		$img_r = null;

		switch ($ext) {
			case 'gif':
				$img_r = imagecreatefromgif('./tmp/' . $image);
				break;
			case 'jpg':
				$img_r = imagecreatefromjpeg('./tmp/' . $image);
				break;
			case 'png':
				$img_r = imagecreatefrompng('./tmp/' . $image);
				break;
		}

		if ($img_r === null) {
			return false;
		}

		$dst_r = imagecreatetruecolor($targ_w, $targ_h);

		imagecopyresampled($dst_r, $img_r, 0, 0, $x, $y, $targ_w, $targ_h, $w, $h);

		@unlink('../tmp/crop_' . $image);

		switch ($ext) {
			case 'gif':
				imagegif($dst_r, './tmp/crop_' . $image);
				break;
			case 'jpg':
				imagejpeg($dst_r, './tmp/crop_' . $image, $jpeg_quality);
				break;
			case 'png':
				imagepng($dst_r, './tmp/crop_' . $image, 0);
				break;
		}

		if (file_exists('./tmp/crop_' . $image)) {
			try {
				copy('./tmp/crop_' . $image, './tmp/thumb_crop_' . $image);
				$img = new fImage('./tmp/thumb_crop_' . $image);
				$img->resize(200, 0);
				$img->saveChanges();

				return 'thumb_crop_' . $image;
			} catch (Exception $e) {
				return false;
			}
		}

		return false;
	}

	private function makeThumbs($pic)
	{
		if (!file_exists(ROOT_DIR . 'images/mini_q_' . $pic) && file_exists(ROOT_DIR . 'images/' . $pic)) {
			copy(ROOT_DIR . 'images/' . $pic, ROOT_DIR . 'images/mini_q_' . $pic);
			copy(ROOT_DIR . 'images/' . $pic, ROOT_DIR . 'images/med_q_' . $pic);
			copy(ROOT_DIR . 'images/' . $pic, ROOT_DIR . 'images/q_' . $pic);

			$image = new fImage(ROOT_DIR . 'images/mini_q_' . $pic);
			$image->cropToRatio(1, 1);
			$image->resize(35, 35);
			$image->saveChanges();

			$image = new fImage(ROOT_DIR . 'images/med_q_' . $pic);
			$image->cropToRatio(1, 1);
			$image->resize(75, 75);
			$image->saveChanges();

			$image = new fImage(ROOT_DIR . 'images/q_' . $pic);
			$image->cropToRatio(1, 1);
			$image->resize(150, 150);
			$image->saveChanges();
		}
	}

	public function xhr_update_newbezirk($data)
	{
		if ($this->regionPermissions->mayAdministrateRegions()) {
			$data['name'] = strip_tags($data['name']);
			$data['name'] = str_replace(['/', '"', "'", '.', ';'], '', $data['name']);
			$data['has_children'] = 0;
			$data['email_pass'] = '';
			$data['email_name'] = 'foodsharing ' . $data['name'];

			if (!empty($data['name'])) {
				if ($out = $this->regionGateway->addRegion($data)) {
					$this->model->update('UPDATE fs_bezirk SET has_children = 1 WHERE `id` = ' . (int)$data['parent_id']);

					return json_encode([
						'status' => 1,
						'script' => '$("#tree").dynatree("getTree").reload();pulseInfo("' . $data['name'] . ' wurde angelegt");'
					]);
				}
			}
		}
	}

	public function xhr_update_abholen($data)
	{
		if (!$this->storePermissions->mayEditPickups($data['bid'])) {
			return XhrResponses::PERMISSION_DENIED;
		}

		$this->model->del('DELETE FROM 	`fs_abholzeiten` WHERE `betrieb_id` = ' . (int)$data['bid']);

		if (is_array($data['newfetchtime'])) {
			for ($i = 0; $i < (count($data['newfetchtime']) - 1); ++$i) {
				$this->model->sql('
			REPLACE INTO 	`fs_abholzeiten`
			(
					`betrieb_id`,
					`dow`,
					`time`,
					`fetcher`
			)
			VALUES
			(
				' . (int)$data['bid'] . ',
				' . (int)$data['newfetchtime'][$i] . ',
				' . $this->model->strval(sprintf('%02d', $data['nfttime']['hour'][$i]) . ':' . sprintf('%02d', $data['nfttime']['min'][$i]) . ':00') . ',
				' . (int)$data['nft-count'][$i] . '
			)
		');
			}
		}
		$betrieb = $this->model->getVal('name', 'betrieb', $data['bid']);
		$team = $this->storeGateway->getStoreTeam($data['bid']);
		$team = array_map(function ($foodsaver) {return $foodsaver['id']; }, $team);
		$bellData = Bell::create('store_cr_times_title', 'store_cr_times', 'img img-store brown', [
			'href' => '/?page=fsbetrieb&id=' . (int)$data['bid']
		], [
			'user' => $this->session->user('name'),
			'name' => $betrieb
		], 'store-time-' . (int)$data['bid']);
		$this->bellGateway->addBell($team, $bellData);

		return json_encode(['status' => 1]);
	}

	public function xhr_bezirkTree($data)
	{
		if ($bezirk = $this->regionGateway->getBezirkByParent($data['p'], $this->session->isOrgaTeam())) {
			$out = [];
			foreach ($bezirk as $b) {
				$hc = false;
				if ($b['has_children'] == 1) {
					$hc = true;
				}
				$out[] = [
					'title' => $b['name'],
					'isLazy' => $hc,
					'isFolder' => $hc,
					'ident' => $b['id'],
					'type' => $b['type']
				];
			}
		} else {
			$out = ['status' => 0];
		}

		return json_encode($out);
	}

	public function xhr_bteamstatus($data)
	{
		$teamStatus = (int)$_GET['status'];
		$storeId = (int)$_GET['bid'];
		if ($this->storePermissions->mayEditStore($storeId) && in_array($teamStatus,
				range(\Foodsharing\Modules\Core\DBConstants\Store\TeamStatus::CLOSED,
				\Foodsharing\Modules\Core\DBConstants\Store\TeamStatus::OPEN_SEARCHING))) {
			$this->storeGateway->setStoreTeamStatus($storeId, $teamStatus);
		}
	}

	public function xhr_getBezirk($data)
	{
		global $g_data;

		/*
		 * In some of these calls orga is still being checked against additionally, as this xhr methods are used with different modules but those modules don't have own permission classes yet.
		 * Even tho orga is yet the only condition of mayAdministrateRegions().
		 * This allows us to be flexible in case we want to remove this feature for most orgas. Which might be likely.
		 * */
		if (!($this->session->may('orga') || $this->regionPermissions->mayAdministrateRegions())) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$g_data = $this->regionGateway->getOne_bezirk($data['id']);

		$g_data['mailbox_name'] = '';
		if ($mbname = $this->mailboxGateway->getMailboxname($g_data['mailbox_id'])) {
			$g_data['mailbox_name'] = $mbname;
		}

		$out = [];
		$out['status'] = 1;
		$id = $this->identificationHelper->id('botschafter');

		$inputs = '<input type="text" name="' . $id . '[]" value="" class="tag input text value" />';
		if (!empty($g_data['foodsaver'])) {
			$inputs = '';
			if (isset($g_data['botschafter']) && is_array($g_data['botschafter'])) {
				foreach ($g_data['botschafter'] as $fs) {
					$inputs .= '<input type="text" name="' . $id . '[' . $fs['id'] . '-a]" value="' . $fs['name'] . '" class="tag input text value" />';
				}
			}
		}

		$inputs = '<div id="' . $id . '">' . $inputs . '</div>';

		$cats = $this->regionGateway->getBasics_bezirk();
		$out['html'] = $this->v_utils->v_form('bezirkForm', [
				$this->v_utils->v_form_hidden('bezirk_id', (int)$data['id']),
				$this->v_utils->v_form_select('parent_id', ['values' => $cats]),
				$this->v_utils->v_form_select('master', ['label' => 'Master-Bezirk', 'desc' => 'Alle Foodsaver sind automatisch mit im Master-Bezirk, sofern einer angegeben wurde', 'values' => $cats]),
				$this->v_utils->v_form_text('name'),
				$this->v_utils->v_form_text('mailbox_name', ['desc' => 'Achtung! Nicht willkürlich ändern! Auch darauf achten, dass diese Adresse unter Mailboxen verwalten noch nicht existiert.']),
				$this->v_utils->v_form_text('email_name', ['label' => 'Absendername']),
				$this->v_utils->v_form_select(
					'type',
					[
						'label' => 'Bezirkstyp',
						'values' => [
							['id' => Type::CITY, 'name' => 'Stadt'],
							['id' => Type::BIG_CITY, 'name' => 'Großstadt (ohne Anmeldemöglichkeit)'],
							['id' => Type::PART_OF_TOWN, 'name' => 'Stadtteil'],
							['id' => Type::DISTRICT, 'name' => 'Bezirk'],
							['id' => Type::REGION, 'name' => 'Region'],
							['id' => Type::FEDERAL_STATE, 'name' => 'Bundesland'],
							['id' => Type::COUNTRY, 'name' => 'Land'],
							['id' => Type::WORKING_GROUP, 'name' => 'Arbeitsgruppe'],
						],
					]
				),
				$this->v_utils->v_input_wrapper($this->translationHelper->s($id), $inputs, $id)
			], ['submit' => $this->translationHelper->s('save')]) .
			$this->v_utils->v_input_wrapper('Master-Update', '<a class="button" href="#" onclick="if(confirm(\'Master-Update wirklich starten?\')){ajreq(\'masterupdate\',{app:\'region\',id:' . (int)$data['id'] . '});}return false;">Master-Update starten</a>', 'masterupdate', ['desc' => 'Bei allen Kindbezirken ' . $g_data['name'] . ' als Master eintragen']);

		$out['script'] = '
		$("#bezirkform-form").off("submit");
		$("#bezirkform-form").on("submit", function(ev){
			ev.preventDefault();

			$("#dialog-confirm-msg").html("Sicher, dass Du die &Auml;nderungen am Bezirk speichern m&ouml;chtest?");

			$( "#dialog-confirm" ).dialog("option","buttons",{
					"Ja, Speichern": function()
					{
						showLoader();
						$.ajax({
							url: "/xhr.php?f=saveBezirk",
							data: $("#bezirkform-form").serialize(),
							dataType: "json",
							success: function(data) {
								$("#info-msg").html("");
								$.globalEval(data.script);
								$( "#dialog-confirm" ).dialog( "close" );
								$("#tree").dynatree("getTree").reload();
							},
							complete: function(){
								hideLoader();
							}
						});
					},
					"Nein, doch nicht": function()
					{
						$( "#dialog-confirm" ).dialog( "close" );
					}
				});

			$("#dialog-confirm").dialog("open");

		});

		$("input[type=\'submit\']").button();

		$("#' . $id . ' input").tagedit({
			autocompleteURL: "/xhr.php?f=getRecip",
			allowEdit: false,
			allowAdd: false
		});

		$(window).on("keydown", function(event){
		    if(event.keyCode == 13) {
		      event.preventDefault();
		      return false;
		    }
		  });
	';

		if ($foodsaver = $this->foodsaverGateway->getFsMap($data['id'])) {
			$out['foodsaver'] = $foodsaver;
		}
		if ($betriebe = $this->storeGateway->getMapsStores($data['id'])) {
			$out['betriebe'] = $betriebe;
			foreach ($out['betriebe'] as $i => $b) {
				$img = '';
				if ($b['kette_id'] != 0) {
					if ($img = $b['logo']) {
						$img = '<a href="/?page=betrieb&id=' . (int)$b['id'] . '"><img style="float:right;margin-left:10px;" src="' . $this->idimg($img, 100) . '" /></a>';
					}
				}
				$out['betriebe'][$i]['bubble'] = '<div style="height:110px;overflow:hidden;width:270px;"><div style="margin-right:5px;float:right;">' . $img . '</div><h1 style="font-size:13px;font-weight:bold;margin-bottom:8px;"><a onclick="betrieb(' . (int)$b['id'] . ');return false;" href="#">' . $this->sanitizerService->jsSafe($b['name']) . '</a></h1><p>' . $this->sanitizerService->jsSafe($b['str'] . ' ' . $b['hsnr']) . '</p><p>' . $this->sanitizerService->jsSafe($b['plz']) . ' ' . $this->sanitizerService->jsSafe($b['stadt']) . '</p></div><div style="clear:both;"></div>';
			}
		}

		return json_encode($out);
	}

	private function idimg($file, $size)
	{
		if (!empty($file)) {
			return 'images/' . str_replace('/', '/' . $size . '_', $file);
		}

		return false;
	}

	public function xhr_acceptBezirkRequest($data)
	{
		if ($this->session->isAdminFor($data['bid']) || $this->session->isOrgaTeam()) {
			$this->regionGateway->acceptBezirkRequest($data['fsid'], $data['bid']);

			return json_encode(['status' => 1]);
		}
	}

	public function xhr_denyBezirkRequest($data)
	{
		if ($this->session->isAdminFor($data['bid']) || $this->session->isOrgaTeam()) {
			$this->regionGateway->denyRegionRequest($data['fsid'], $data['bid']);

			return json_encode(['status' => 1]);
		}
	}

	public function xhr_denyRequest($data)
	{
		if ($this->session->id() == $data['fsid'] || $this->storeGateway->getUserTeamStatus($this->session->id(), $data['bid']) === TeamStatus::Coordinator || $this->session->isOrgaTeam()) {
			$this->storeModel->denyRequest($data['fsid'], $data['bid']);

			$msg = 'Deine Anfrage wurde erfolgreich zur&uuml;ckgezogen!';

			return json_encode(['status' => 1, 'msg' => $msg]);
		}

		$msg = 'Es ist ein Fehler aufgetreten!';

		return json_encode(['status' => 0, 'msg' => $msg]);
	}

	public function xhr_acceptRequest($data)
	{
		if (!$this->storePermissions->mayAcceptRequests($data['bid'])) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$this->storeModel->acceptRequest($data['fsid'], $data['bid']);

		$this->storeGateway->add_betrieb_notiz([
			'foodsaver_id' => $data['fsid'],
			'betrieb_id' => $data['bid'],
			'text' => '{ACCEPT_REQUEST}',
			'zeit' => date('Y-m-d H:i:s'),
			'milestone' => 2
		]);

		$bezirk_id = $this->model->getVal('bezirk_id', 'betrieb', $data['bid']);
		$this->regionGateway->linkBezirk($data['fsid'], $bezirk_id);

		return json_encode(['status' => 1]);
	}

	public function xhr_warteRequest($data)
	{
		if (!$this->storePermissions->mayAcceptRequests($data['bid'])) {
			return XhrResponses::PERMISSION_DENIED;
		}

		$this->storeModel->warteRequest($data['fsid'], $data['bid']);

		return json_encode(['status' => 1]);
	}

	public function xhr_betriebRequest($data)
	{
		if (!$this->storePermissions->mayJoinStoreRequest($data['id'])) {
			return XhrResponses::PERMISSION_DENIED;
		}

		$status = 1;
		$msg = 'Hallo Welt';
		$foodsaver = $this->model->getVal('name', 'foodsaver', $this->session->id());
		$betrieb = $this->model->getVal('name', 'betrieb', $data['id']);
		$bezirk_id = $this->model->getVal('bezirk_id', 'betrieb', $data['id']);
		if ($biebs = $this->storeGateway->getBiebsForStore($data['id'])) {
			$msg = 'Der Verantwortliche wurde über Deine Anfrage informiert und wird sich bei Dir melden.';

			$bellData = Bell::create('store_new_request_title', 'store_new_request', 'img img-store brown', [
				'href' => '/?page=fsbetrieb&id=' . (int)$data['id']
			], [
				'user' => $this->session->user('name'),
				'name' => $betrieb
			], 'store-request-' . (int)$data['id']);
			$this->bellGateway->addBell($biebs, $bellData);
		} else {
			$msg = 'Für diesen Betrieb gibt es noch keinen Verantwortlichen. Die Botschafter wurden informiert.';

			$botsch = [];
			$add = '';
			if ($b = $this->foodsaverGateway->getAdminsOrAmbassadors($bezirk_id)) {
				foreach ($b as $bb) {
					$botsch[] = $bb['id'];
				}
			} else {
				$botsch = $this->foodsaverGateway->getOrgateam();
				$add = ' Es gibt aber keinen Botschafter';
			}

			$bellData = Bell::create('store_new_request_title', 'store_new_request', 'img img-store brown', [
				'href' => '/?page=fsbetrieb&id=' . (int)$data['id']
			], [
				'user' => $this->session->user('name'),
				'name' => $betrieb
			], 'store-request-' . (int)$data['id']);
			$this->bellGateway->addBell($botsch, $bellData);
		}

		$this->storeModel->teamRequest($this->session->id(), $data['id']);

		return json_encode(['status' => $status, 'msg' => $msg]);
	}

	public function xhr_saveBezirk($data)
	{
		/*
		* In some of these calls orga is still being checked against additionally, as this xhr methods are used with different modules but those modules don't have own permission classes yet.
		* Even tho orga is yet the only condition of mayAdministrateRegions().
		* This allows us to be flexible in case we want to remove this feature for most orgas. Which might be likely.
		* */
		if ($this->session->may('orga') || $this->regionPermissions->mayAdministrateRegions()) {
			global $g_data;
			$g_data = $data;

			$mbid = (int)$this->model->qOne('SELECT mailbox_id FROM fs_bezirk WHERE id = ' . (int)$data['bezirk_id']);

			if (strlen($g_data['mailbox_name']) > 1) {
				if ($mbid > 0) {
					$this->model->update('UPDATE fs_mailbox SET name = ' . $this->model->strval($g_data['mailbox_name']) . ' WHERE id = ' . (int)$mbid);
				} else {
					$mbid = $this->model->insert('INSERT INTO fs_mailbox(`name`)VALUES(' . $this->model->strval($g_data['mailbox_name']) . ')');
					$this->model->update('UPDATE fs_bezirk SET mailbox_id = ' . (int)$mbid . ' WHERE id = ' . (int)$data['bezirk_id']);
				}
			}

			$this->sanitizerService->handleTagSelect('botschafter');

			$this->regionGateway->update_bezirkNew($data['bezirk_id'], $g_data);
			$this->notificationService->sendEmailIfGroupHasNoAdmin($data['bezirk_id']);

			return json_encode([
				'status' => 1,
				'script' => 'pulseInfo("' . $this->translationHelper->s('edit_success') . '");'
			]);
		}
	}

	private function incLang(string $moduleName): void
	{
		include ROOT_DIR . 'lang/DE/' . $moduleName . '.lang.php';
	}

	public function xhr_abortEmail($data)
	{
		$mailOwnerId = $this->emailGateway->getOne_send_email($data['id'])['foodsaver_id'];
		if ($this->session->id() == $mailOwnerId) {
			$this->emailGateway->setEmailStatus($data['id'], $mailOwnerId, EmailStatus::STATUS_CANCELED);
		}
	}

	public function xhr_bcontext($data)
	{
		$storeId = (int)$data['bid'];
		if ($this->storePermissions->mayEditStoreTeam($storeId)) {
			$check = false;
			if ($data['action'] == 'toteam') {
				$check = true;
				$this->model->update('UPDATE `fs_betrieb_team` SET `active` = 1 WHERE foodsaver_id = ' . (int)$data['fsid'] . ' AND betrieb_id = ' . $storeId);
			} elseif ($data['action'] == 'tojumper') {
				$check = true;
				$this->model->update('UPDATE `fs_betrieb_team` SET `active` = 2 WHERE foodsaver_id = ' . (int)$data['fsid'] . ' AND betrieb_id = ' . $storeId);
			} elseif ($data['action'] == 'delete') {
				$check = true;
				$this->model->del('DELETE FROM `fs_betrieb_team` WHERE foodsaver_id = ' . (int)$data['fsid'] . ' AND betrieb_id = ' . $storeId);
				$this->model->del('DELETE FROM `fs_abholer` WHERE `betrieb_id` = ' . $storeId . ' AND `foodsaver_id` = ' . (int)$data['fsid'] . ' AND `date` > NOW()');

				if ($tcid = $this->storeGateway->getBetriebConversation($storeId)) {
					$this->messageModel->deleteUserFromConversation($tcid, (int)$data['fsid'], true);
				}
				if ($scid = $this->storeGateway->getBetriebConversation($storeId, true)) {
					$this->messageModel->deleteUserFromConversation($scid, (int)$data['fsid'], true);
				}
			}

			if ($check) {
				return json_encode([
					'status' => 1
				]);
			}
		}
	}
}
