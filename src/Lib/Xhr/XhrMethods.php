<?php

namespace Foodsharing\Lib\Xhr;

use Exception;
use Flourish\fImage;
use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\DBConstants\Email\EmailStatus;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Core\DBConstants\Store\Milestone;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
use Foodsharing\Modules\Core\DBConstants\Store\TeamStatus;
use Foodsharing\Modules\Email\EmailGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Modules\Message\MessageGateway;
use Foodsharing\Modules\Region\ForumGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\StoreModel;
use Foodsharing\Permissions\NewsletterEmailPermissions;
use Foodsharing\Permissions\RegionPermissions;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\EmailHelper;
use Foodsharing\Utility\IdentificationHelper;
use Foodsharing\Utility\ImageHelper;
use Foodsharing\Utility\Sanitizer;
use Foodsharing\Utility\TranslationHelper;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class XhrMethods
{
	private Db $model;
	private Mem $mem;
	private Session $session;
	private Utils $v_utils;
	private ViewUtils $xhrViewUtils;
	private StoreModel $storeModel;
	private MessageGateway $messageGateway;
	private RegionGateway $regionGateway;
	private StorePermissions $storePermissions;
	private ForumGateway $forumGateway;
	private BellGateway $bellGateway;
	private StoreGateway $storeGateway;
	private FoodsaverGateway $foodsaverGateway;
	private EmailGateway $emailGateway;
	private MailboxGateway $mailboxGateway;
	private ImageManager $imageManager;
	private Sanitizer $sanitizerService;
	private EmailHelper $emailHelper;
	private ImageHelper $imageService;
	private IdentificationHelper $identificationHelper;
	private DataHelper $dataHelper;
	private TranslationHelper $translationHelper;
	private NewsletterEmailPermissions $newsletterEmailPermissions;
	private RegionPermissions $regionPermissions;
	private TranslatorInterface $translator;

	public function __construct(
		Mem $mem,
		Session $session,
		Db $model,
		Utils $viewUtils,
		ViewUtils $xhrViewUtils,
		StoreModel $storeModel,
		MessageGateway $messageGateway,
		RegionGateway $regionGateway,
		ForumGateway $forumGateway,
		BellGateway $bellGateway,
		StoreGateway $storeGateway,
		StorePermissions $storePermissions,
		FoodsaverGateway $foodsaverGateway,
		EmailGateway $emailGateway,
		MailboxGateway $mailboxGateway,
		ImageManager $imageManager,
		Sanitizer $sanitizerService,
		EmailHelper $emailHelper,
		ImageHelper $imageService,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper,
		TranslationHelper $translationHelper,
		NewsletterEmailPermissions $newsletterEmailPermissions,
		RegionPermissions $regionPermission,
		TranslatorInterface $translator
	) {
		$this->mem = $mem;
		$this->session = $session;
		$this->model = $model;
		$this->v_utils = $viewUtils;
		$this->xhrViewUtils = $xhrViewUtils;
		$this->storeModel = $storeModel;
		$this->messageGateway = $messageGateway;
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
		$this->translator = $translator;
	}

	public function xhr_verify($data)
	{
		$regions = $this->regionGateway->getFsRegionIds((int)$data['fid']);

		if (!$this->session->isAmbassadorForRegion($regions, false, true) && !$this->session->isOrgaTeam()) {
			return json_encode(['status' => 0]);
		}

		$countver = $this->model->qOne('
			SELECT COUNT(*) FROM fs_verify_history
			WHERE date BETWEEN NOW()- INTERVAL 20 SECOND AND now()
			AND bot_id = ' . $this->session->id()
		);
		if ($countver > 10) {
			return json_encode(['status' => 0]);
		}

		$countFetch = $this->model->qOne('
			SELECT 	count(a.`date`)
			FROM   `fs_abholer` a

			WHERE a.foodsaver_id = ' . (int)$data['fid'] . '
			AND   a.`date` > NOW()
		');

		if ($countFetch > 0) {
			return json_encode(['status' => 0]);
		}

		if ($this->model->update('UPDATE `fs_foodsaver` SET `verified` = ' . (int)$data['v'] . ' WHERE `id` = ' . (int)$data['fid'])) {
			$this->model->insert('
				INSERT INTO `fs_verify_history`
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

			return json_encode(['status' => 1]);
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
		$storeId = (int)$data['bid'];

		if (!$this->storePermissions->mayReadStoreWall($storeId)) {
			return json_encode(['status' => 0]);
		}

		$allWallposts = $this->model->q('
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

			LIMIT 50'
		);
		if (!$allWallposts) {
			return json_encode(['status' => 0]);
		}

		$html = '<table class="pintable">';
		foreach ($allWallposts as $wallpost) {
			$classes = 'odd';
			$pic = $this->imageService->img($wallpost['photo']);
			$userId = intval($wallpost['fsid']);
			$mile = intval($wallpost['milestone']);

			$delete = '';
			if ($this->session->id() == $userId || $this->session->isOrgaTeam()) {
				$delete = '<span class="dot">·</span>'
					. '<a class="pdelete light" href="#p' . $wallpost['id'] . '"'
					. ' onclick="u_delPost(' . (int)$wallpost['id'] . '); return false;">'
					. $this->translator->trans('button.delete')
				. '</a>';
			}

			$time = date('d.m.Y H:i', $wallpost['zeit']);
			$day = date('d.m.Y', $wallpost['zeit']);

			$msg = '<span class="msg">' . nl2br($wallpost['text']) . '</span>
				<div class="foot">
					<span class="time">'
					. $this->translator->trans('wall.by', [
						'{when}' => $this->translator->trans('date.time', ['{time}' => $time]),
						'{name}' => $wallpost['name'],
					])
					. '</span>' . $delete . '
				</div>';

			if ($mile == Milestone::ACCEPTED || $mile == Milestone::DROPPED) {
				$fsName = $this->model->getVal('name', 'foodsaver', $userId);
				$link = '<a href="/profile/' . $userId . '">' . $fsName . '</a>';
				$msg = '<span class="msg">' . $this->translator->trans(
					($mile == Milestone::ACCEPTED) ? 'storeedit.milestone.accepted' : 'storeedit.milestone.dropped',
					['{user}' => $link]
				) . '</span>';
			} elseif ($mile == Milestone::CREATED) {
				$msg = '<div class="milestone">'
					. $this->translator->trans('storeedit.milestone.created', [
						'{user}' => '<a href="/profile/' . $userId . '">' . $wallpost['name'] . '</a>',
						'{date}' => $day,
					]) . '</div>';
			} elseif ($mile == Milestone::STATUS_CHANGED) {
				$msg = '<span class="msg">'
					. '<strong>'
					. $this->translator->trans('storeedit.milestone.statuschanged', ['{date}' => $day])
					. '</strong> '
					// the old messages are `status_msg_{1,2,3,4,5,6}`
					. $this->translator->trans($wallpost['text'])
				. '</span>';
			}

			if (Milestone::isStoreMilestone($mile) || Milestone::isTeamMilestone($mile)) {
				$classes .= ' milestone';
			}
			if (Milestone::isStoreMilestone($mile)) {
				$pic = 'img/milestone.png';
			}

			$html .= '
			<tr class="' . $classes . ' bpost bpost-' . $wallpost['id'] . '">
				<td class="img">
					<a href="/profile/' . $userId . '"><img src="' . $pic . '" /></a>
				</td>
				<td>' . $msg . '</td>
			</tr>';
		}

		return json_encode([
			'status' => 1,
			'html' => $html . '</table>',
		]);
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
					$hide_some = ' AND betrieb_status_id <> 7'; // CooperationStatus::PERMANENTLY_CLOSED
					if (isset($data['options']) && is_array($data['options'])) {
						foreach ($data['options'] as $opt) {
							if ($opt == 'needhelpinstant') {
								$team_status[] = 'team_status = 2'; // TeamStatus::OPEN_SEARCHING
							} elseif ($opt == 'needhelp') {
								$team_status[] = 'team_status = 1'; // TeamStatus::OPEN
							} elseif ($opt == 'nkoorp') {
								// CooperationStatus::COOPERATION_STARTING
								// CooperationStatus::COOPERATION_ESTABLISHED
								$hide_some .= ' AND betrieb_status_id NOT IN(3,5)';
							}
						}
					}

					if (!empty($team_status)) {
						$team_status = ' AND (' . implode(' OR ', $team_status) . ')';
					} else {
						$team_status = '';
					}

					$out['betriebe'] = $this->model->q('
						SELECT `id`, lat, lon
						FROM fs_betrieb
						WHERE lat != ""
						' . $team_status . $hide_some
					);
				} elseif ($t == 'fairteiler') {
					$out['fairteiler'] = $this->model->q('
						SELECT `id`, lat, lon, bezirk_id AS bid
						FROM fs_fairteiler
						WHERE lat != ""
						AND status = 1'
					);
				} elseif ($t == 'baskets') {
					if ($baskets = $this->model->q('
						SELECT id, lat, lon, location_type
						FROM fs_basket
						WHERE `status` = 1')
					) {
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
					// for now, we just check if the frontend did not want to betray us.
					// later, we might want to have better resize / error handling
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
					$fullPath = sprintf('images/%s/%s.%s', $namespace, sha1_file($file->getPathname()), $ext);
					$internalName = sprintf('%s/%s.%s', $namespace, sha1_file($file->getPathname()), $ext);

					$img->save($fullPath);
					$response->setStatusCode(200);
					$response->setData([
						'fullPath' => $fullPath,
						'internalName' => $internalName,
					]);
				} else {
					$response->setData([
						'width' => $img->width(),
						'height' => $img->height(),
						'max_width' => $width,
						'max_height' => $height,
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
				$filename = str_replace('.jpeg', '.jpg', strtolower($_FILES['uploadpic']['name']));
				$extension = strtolower(substr($filename, strlen($filename) - 4, 4));

				$path = ROOT_DIR . 'images/' . $id;

				if (!is_dir($path)) {
					mkdir($path);
				}

				$newname = uniqid() . $extension;

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
		 * [resize] => [250,528]
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

			if (empty($recip)) {
				return json_encode([
					'status' => 2,
					'comment' => $this->translator->trans('recipients.done'),
				]);
			}

			$mailbox = $this->mailboxGateway->getMailbox((int)$mail['mailbox_id']);
			$mailbox['email'] = $mailbox['name'] . '@' . NOREPLY_EMAIL_HOST;

			$sender = $this->model->getValues(['geschlecht', 'name'], 'foodsaver', $this->session->id());

			$this->emailGateway->setEmailStatus($mail['id'], $recip, EmailStatus::STATUS_INITIALISED);

			foreach ($recip as $fs) {
				$anrede = $this->translator->trans('salutation.' . $fs['geschlecht']);

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
			$current = $fs['email'] ?? $this->translator->trans('recipients.unknown');

			return json_encode([
				'left' => $mails_left,
				'status' => 1,
				'comment' => $this->translator->trans('recipients.status', ['{current}' => $current]),
			]);
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
				$uploaded = $_FILES['uploadpic']['tmp_name'];
				// prevent path traversal
				$uploaded = preg_replace('/%/', '', $uploaded);
				$uploaded = preg_replace('/\.+/', '.', $uploaded);

				$filename = $_FILES['uploadpic']['name'];
				$filename = strtolower($filename);
				$filename = str_replace('.jpeg', '.jpg', $filename);
				$extension = strtolower(substr($filename, strlen($filename) - 4, 4));
				if ($this->is_allowed($_FILES['uploadpic'])) {
					try {
						$file = $this->makeUnique() . $extension;
						move_uploaded_file($uploaded, './tmp/' . $file);
						$image = new fImage('./tmp/' . $file);
						$image->resize(550, 0);
						$image->saveChanges();
						$func = 'parent.fotoupload(\'' . $file . '\',\'' . $id . '\');';
					} catch (Exception $e) {
						$func = 'parent.pic_error(\'' . $this->translator->trans('upload.image-problem') . '\',\'' . $id . '\');';
					}
				} else {
					$func = 'parent.pic_error(\'' . $this->translator->trans('error_unexpected') . '\',\'' . $id . '\');';
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
				$func = 'alert(\'' . $this->translator->trans('error_unexpected') . '\');';
			}
		}

		echo '<html>
		<head><title>' . $this->translator->trans('picture_upload_widget.picture_upload') . '</title></head>
		<body onload="' . $func . '"></body>
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
		if (!$this->regionPermissions->mayAdministrateRegions()) {
			return;
		}

		$data['name'] = strip_tags($data['name']);
		$data['name'] = str_replace(['/', '"', "'", '.', ';'], '', $data['name']);
		$data['has_children'] = 0;
		$data['email_pass'] = '';
		$data['email_name'] = 'foodsharing ' . $data['name'];

		if (empty($data['name'])) {
			return;
		}

		$out = $this->regionGateway->addRegion($data);

		if (!$out) {
			return;
		}

		$parentId = intval($data['parent_id']);
		$this->model->update('UPDATE fs_bezirk SET has_children = 1 WHERE `id` = ' . $parentId);

		return json_encode([
			'status' => 1,
			'script' => '$("#tree").dynatree("getTree").reload(); pulseInfo("'
				. $this->translator->trans('region.created', ['{region}' => $data['name']]) .
			'");',
		]);
	}

	public function xhr_update_abholen($data)
	{
		if (!$this->storePermissions->mayEditPickups($data['bid'])) {
			return XhrResponses::PERMISSION_DENIED;
		}

		$this->model->del('DELETE FROM `fs_abholzeiten` WHERE `betrieb_id` = ' . (int)$data['bid']);

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
				' . $this->model->strval(
					sprintf('%02d', $data['nfttime']['hour'][$i])
					. ':' .
					sprintf('%02d', $data['nfttime']['min'][$i]) . ':00'
				) . ',
				' . (int)$data['nft-count'][$i] . '
			)
		');
			}
		}
		$storeName = $this->model->getVal('name', 'betrieb', $data['bid']);
		$team = $this->storeGateway->getStoreTeam($data['bid']);
		$team = array_map(function ($foodsaver) { return $foodsaver['id']; }, $team);
		$bellData = Bell::create('store_cr_times_title', 'store_cr_times', 'fas fa-user-clock', [
			'href' => '/?page=fsbetrieb&id=' . (int)$data['bid'],
		], [
			'user' => $this->session->user('name'),
			'name' => $storeName,
		], 'store-time-' . (int)$data['bid']);
		$this->bellGateway->addBell($team, $bellData);

		return json_encode(['status' => 1]);
	}

	public function xhr_bezirkTree($data)
	{
		$region = $this->regionGateway->getBezirkByParent($data['p'], $this->session->isOrgaTeam());
		if (!$region) {
			$out = ['status' => 0];
		} else {
			$out = [];
			foreach ($region as $r) {
				$hasChildren = false;
				if ($r['has_children'] == 1) {
					$hasChildren = true;
				}
				$out[] = [
					'title' => $r['name'],
					'isLazy' => $hasChildren,
					'isFolder' => $hasChildren,
					'ident' => $r['id'],
					'type' => $r['type'],
				];
			}
		}

		return json_encode($out);
	}

	public function xhr_bteamstatus($data)
	{
		$teamStatus = (int)$_GET['status'];
		$storeId = (int)$_GET['bid'];
		if ($this->storePermissions->mayEditStore($storeId) && TeamStatus::isValidStatus($teamStatus)) {
			$this->storeGateway->setStoreTeamStatus($storeId, $teamStatus);
		}
	}

	public function xhr_getBezirk($data)
	{
		global $g_data;

		if (!$this->regionPermissions->mayAdministrateRegions()) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$g_data = $this->regionGateway->getOne_bezirk($data['id']);

		$g_data['mailbox_name'] = '';
		if ($mbname = $this->mailboxGateway->getMailboxname($g_data['mailbox_id'])) {
			$g_data['mailbox_name'] = $mbname;
		}

		$out = [];
		$out['status'] = 1;

		$inputs = '<input type="text" name="botschafter[]" value="" class="tag input text value" />';
		if (!empty($g_data['foodsaver'])) {
			$inputs = '';
			if (isset($g_data['botschafter']) && is_array($g_data['botschafter'])) {
				foreach ($g_data['botschafter'] as $fs) {
					$inputs .= '<input type="text" name="botschafter[' . $fs['id'] . '-a]" value="' . $fs['name'] . '" class="tag input text value" />';
				}
			}
		}

		$inputs = '<div id="botschafter">' . $inputs . '</div>';

		$regions = $this->regionGateway->getBasics_bezirk();
		$out['html'] = $this->v_utils->v_form('bezirkForm', [
			$this->v_utils->v_form_hidden('bezirk_id', (int)$data['id']),
			$this->v_utils->v_form_select('parent_id', ['values' => $regions]),
			$this->v_utils->v_form_select('master', [
				'label' => $this->translator->trans('region.hull.parent'),
				'desc' => $this->translator->trans('region.hull.parent-info'),
				'values' => $regions,
			]),
			$this->v_utils->v_form_text('name'),
			$this->v_utils->v_form_text('mailbox_name', [
				'desc' => $this->translator->trans('region.mail.name-info'),
			]),
			$this->v_utils->v_form_text('email_name', [
				'label' => $this->translator->trans('region.mail.sender'),
			]),
			$this->v_utils->v_form_select('type', [
				'label' => $this->translator->trans('region.type.title'),
				'values' => [
					['id' => Type::CITY, 'name' => $this->translator->trans('region.type.city')],
					['id' => Type::BIG_CITY, 'name' => $this->translator->trans('region.type.bigcity')],
					['id' => Type::PART_OF_TOWN, 'name' => $this->translator->trans('region.type.townpart')],
					['id' => Type::DISTRICT, 'name' => $this->translator->trans('region.type.district')],
					['id' => Type::REGION, 'name' => $this->translator->trans('region.type.region')],
					['id' => Type::FEDERAL_STATE, 'name' => $this->translator->trans('region.type.state')],
					['id' => Type::COUNTRY, 'name' => $this->translator->trans('region.type.country')],
					['id' => Type::WORKING_GROUP, 'name' => $this->translator->trans('region.type.workgroup')],
				],
			]),
			$this->v_utils->v_form_select('workgroup_function', [
				'label' => $this->translator->trans('group.function.title'),
				'values' => [
					[
						'id' => WorkgroupFunction::WELCOME,
						'name' => $this->translator->trans('group.function.welcome'),
					],
					[
						'id' => WorkgroupFunction::VOTING,
						'name' => $this->translator->trans('group.function.voting'),
					],
					[
						'id' => WorkgroupFunction::FSP,
						'name' => $this->translator->trans('group.function.fsp'),
					],
				],
			]),
			$this->v_utils->v_input_wrapper(
				$this->translator->trans('terminology.ambassadors'),
				$inputs,
				'botschafter'
			)
		], ['submit' => $this->translator->trans('button.save')])
		.
		$this->v_utils->v_input_wrapper($this->translator->trans('region.hull.title'),
			'<a class="button" href="#" onclick="'
				. 'if (confirm(\'' . $this->translator->trans('region.hull.confirm') . '\')) {'
				. 'tryMasterUpdate(' . (int)$data['id'] . ');} return false;'
			. '">'
			. $this->translator->trans('region.hull.start')
			. '</a>', 'masterupdate', [
				'desc' => $this->translator->trans('region.hull.closure', [
					'{region}' => $g_data['name'],
				]),
			]
		);

		$out['script'] = '
		$("#bezirkform-form").off("submit");
		$("#bezirkform-form").on("submit", function (ev) {
			ev.preventDefault();

			$("#dialog-confirm-msg").html("' . $this->translator->trans('region.confirm') . '");

			$("#dialog-confirm").dialog("option", "buttons", {
				"' . $this->translator->trans('region.save') . '": function () {
					showLoader();
					$.ajax({
						url: "/xhr.php?f=saveBezirk",
						data: $("#bezirkform-form").serialize(),
						dataType: "json",
						success: function (data) {
							$("#info-msg").html("");
							$.globalEval(data.script);
							$("#dialog-confirm").dialog("close");
							$("#tree").dynatree("getTree").reload();
						},
						complete: function () {
							hideLoader();
						}
					});
				},
				"' . $this->translator->trans('region.cancel') . '": function () {
					$("#dialog-confirm").dialog("close");
				}
			});

			$("#dialog-confirm").dialog("open");
		});

		$("input[type=\'submit\']").button();

		$("#botschafter input").tagedit({
			autocompleteURL: async function (request, response) {
			  let data = null
			  try {
			    data = await searchUser(request.term)
			  } catch (e) {
			  }
			  response(data)
			},
			allowEdit: false,
			allowAdd: false
		});

		$(window).on("keydown", function (event) {
			if (event.keyCode == 13) {
				event.preventDefault();
				return false;
			}
		});';

		if ($foodsaver = $this->foodsaverGateway->getFsMap($data['id'])) {
			$out['foodsaver'] = $foodsaver;
		}

		if ($betriebe = $this->storeGateway->getMapsStores($data['id'])) {
			$out['betriebe'] = $betriebe;
			foreach ($out['betriebe'] as $i => $b) {
				$img = ($b['kette_id'] == 0) ? '' : $b['logo'];
				if ($img) {
					$img = '<a href="/?page=fsbetrieb&id=' . (int)$b['id'] . '">'
						. '<img style="float: right; margin-left: 10px;" src="' . $this->idimg($img, 100) . '" />'
					. '</a>';
				}
				$out['betriebe'][$i]['bubble'] = '<div style="height: 110px; overflow: hidden; width: 270px; ">'
					. '<div style="margin-right: 5px; float: right;">' . $img . '</div>'
					. '<h1 style="font-size: 13px; font-weight: bold; margin-bottom: 8px;">'
						. '<a href="/?page=fsbetrieb&id=' . (int)$b['id'] . '">'
						. $this->sanitizerService->jsSafe($b['name'])
						. '</a>'
					. '</h1>'
					. '<p>' . $this->sanitizerService->jsSafe($b['str'] . ' ' . $b['hsnr']) . '</p>'
					. '<p>' . $this->sanitizerService->jsSafe($b['plz'] . ' ' . $b['stadt']) . '</p>'
				. '</div><div style="clear: both;"></div>';
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
			'milestone' => Milestone::ACCEPTED,
		]);

		$regionId = $this->model->getVal('bezirk_id', 'betrieb', $data['bid']);
		$this->regionGateway->linkBezirk($data['fsid'], $regionId);

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
		$storeId = intval($data['id']);
		if (!$this->storePermissions->mayJoinStoreRequest($storeId)) {
			return XhrResponses::PERMISSION_DENIED;
		}

		$storeName = $this->model->getVal('name', 'betrieb', $storeId);

		if ($bellRecipients = $this->storeGateway->getBiebsForStore($storeId)) {
			$msg = $this->translator->trans('store.request.got-it');
		} else {
			$msg = $this->translator->trans('store.request.no-sm');

			$regionId = $this->model->getVal('bezirk_id', 'betrieb', $storeId);
			$bellRecipients = [];
			if ($inform = $this->foodsaverGateway->getAdminsOrAmbassadors($regionId)) {
				foreach ($inform as $fs) {
					$bellRecipients[] = $fs['id'];
				}
				$msg .= ' ' . $this->translator->trans('store.request.amb-instead');
			} else {
				$bellRecipients = $this->foodsaverGateway->getOrgateam();
				$msg .= ' ' . $this->translator->trans('store.request.orga-instead');
			}
		}

		$bellData = Bell::create('store_new_request_title', 'store_new_request', 'fas fa-user-plus', [
			'href' => '/?page=fsbetrieb&id=' . $storeId,
		], [
			'user' => $this->session->user('name'),
			'name' => $storeName,
		], 'store-request-' . $storeId);
		$this->bellGateway->addBell($bellRecipients, $bellData);

		$this->storeModel->teamRequest($this->session->id(), $storeId);

		return json_encode(['status' => 1, 'msg' => $msg]);
	}

	public function xhr_saveBezirk($data)
	{
		if (!$this->regionPermissions->mayAdministrateRegions()) {
			return;
		}

		global $g_data;
		$g_data = $data;
		$regionId = intval($data['bezirk_id']);
		$parentId = intval($data['parent_id']);

		// Check for: Only a workgroup can have a function.
		// If the workgroup is set to welcome Team - make sure there can be only one Welcome Team in a region.
		if ($data['type'] != Type::WORKING_GROUP && $data['workgroup_function']) {
			return json_encode([
				'status' => 1,
				'script' => 'pulseError("' . $this->translator->trans('group.function.invalid') . '");',
			]);
		} elseif ($data['workgroup_function'] == WorkgroupFunction::WELCOME) {
			$welcomeGroupId = $this->regionGateway->getRegionFunctionGroupId($parentId, WorkgroupFunction::WELCOME);
			if ($welcomeGroupId && ($welcomeGroupId != $regionId)) {
				return json_encode([
					'status' => 1,
					'script' => 'pulseError("' . $this->translator->trans('group.function.duplicate_welcome_team') . '");',
				]);
			}
		} elseif ($data['workgroup_function'] == WorkgroupFunction::VOTING) {
			$votingGroupId = $this->regionGateway->getRegionFunctionGroupId($data['parent_id'], WorkgroupFunction::VOTING);
			if ($votingGroupId !== null && $votingGroupId !== (int)$data['bezirk_id']) {
				return json_encode([
					'status' => 1,
					'script' => 'pulseError("' . $this->translator->trans('group.function.duplicate_voting_team') . '");',
				]);
			}
		} elseif ($data['workgroup_function'] == WorkgroupFunction::FSP) {
			$fspGroupId = $this->regionGateway->getRegionFunctionGroupId($data['parent_id'], WorkgroupFunction::FSP);
			if ($fspGroupId !== null && $fspGroupId !== (int)$data['bezirk_id']) {
				return json_encode([
					'status' => 1,
					'script' => 'pulseError("' . $this->translator->trans('group.function.duplicate_fsp_team') . '");',
				]);
			}
		}

		$oldRegionData = $this->regionGateway->getOne_bezirk($regionId);

		$mbid = (int)$this->model->qOne('SELECT mailbox_id FROM fs_bezirk WHERE id = ' . $regionId);

		if (strlen($g_data['mailbox_name']) > 1) {
			if ($mbid > 0) {
				$this->model->update('UPDATE fs_mailbox SET name = ' . $this->model->strval($g_data['mailbox_name']) . ' WHERE id = ' . (int)$mbid);
			} else {
				$mbid = $this->model->insert('INSERT INTO fs_mailbox(`name`)VALUES(' . $this->model->strval($g_data['mailbox_name']) . ')');
				$this->model->update('UPDATE fs_bezirk SET mailbox_id = ' . (int)$mbid . ' WHERE id = ' . $regionId);
			}
		}

		$this->sanitizerService->handleTagSelect('botschafter');

		// If the workgroup is moved it loses the old functions.
		// else a region is moved, all workgroups loose their related targets
		if ($oldRegionData['parent_id'] != $parentId) {
			if ($oldRegionData['type'] == Type::WORKING_GROUP) {
				if ($oldRegionData['workgroup_function']) {
					$this->regionGateway->deleteRegionFunction($regionId, $oldRegionData['workgroup_function']);
				}
			} else {
				$this->regionGateway->deleteTargetFunctions($regionId);
			}
			$oldRegionData = $this->regionGateway->getOne_bezirk($regionId);
		}

		$this->regionGateway->update_bezirkNew($regionId, $g_data);

		if (!$oldRegionData['workgroup_function'] && $g_data['workgroup_function']) {
			if ($g_data['workgroup_function'] > 0) {
				$this->regionGateway->addRegionFunction($regionId, $g_data['workgroup_function'], $parentId);
			}
		} elseif ($oldRegionData['workgroup_function'] != $g_data['workgroup_function']) {
			$this->regionGateway->deleteRegionFunction($regionId, $oldRegionData['workgroup_function']);
		}

		return json_encode([
			'status' => 1,
			'script' => 'pulseInfo("' . $this->translator->trans('region.edit_success') . '");',
		]);
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
			$foodsaverId = (int)$data['fsid'];
			$teamChatId = $this->storeGateway->getBetriebConversation($storeId);
			$standbyTeamChatId = $this->storeGateway->getBetriebConversation($storeId, true);
			if ($data['action'] == 'toteam') {
				$check = true;
				$this->model->update('UPDATE `fs_betrieb_team` SET `active` = 1 WHERE foodsaver_id = ' . $foodsaverId . ' AND betrieb_id = ' . $storeId);
				$this->messageGateway->addUserToConversation($teamChatId, $foodsaverId);
				$this->messageGateway->deleteUserFromConversation($standbyTeamChatId, $foodsaverId);
				$this->storeGateway->addStoreLog($data['bid'], $this->session->id(), $data['fsid'], null, StoreLogAction::MOVED_TO_TEAM);
			} elseif ($data['action'] == 'tojumper') {
				$check = true;
				$this->model->update('UPDATE `fs_betrieb_team` SET `active` = 2 WHERE foodsaver_id = ' . $foodsaverId . ' AND betrieb_id = ' . $storeId);
				$this->messageGateway->addUserToConversation($standbyTeamChatId, $foodsaverId);
				$this->messageGateway->deleteUserFromConversation($teamChatId, $foodsaverId);
				$this->storeGateway->addStoreLog($data['bid'], $this->session->id(), $data['fsid'], null, StoreLogAction::MOVED_TO_JUMPER);
			} elseif ($data['action'] == 'delete') {
				$check = true;
				$this->model->del('DELETE FROM `fs_betrieb_team` WHERE foodsaver_id = ' . $foodsaverId . ' AND betrieb_id = ' . $storeId);
				$this->model->del('DELETE FROM `fs_abholer` WHERE `betrieb_id` = ' . $storeId . ' AND `foodsaver_id` = ' . $foodsaverId . ' AND `date` > NOW()');
				$this->messageGateway->deleteUserFromConversation($teamChatId, $foodsaverId);
				$this->messageGateway->deleteUserFromConversation($standbyTeamChatId, $foodsaverId);
				$this->storeGateway->addStoreLog($data['bid'], $this->session->id(), $data['fsid'], null, StoreLogAction::REMOVED_FROM_STORE);
			}

			if ($check) {
				return json_encode(['status' => 1]);
			}
		}
	}
}
