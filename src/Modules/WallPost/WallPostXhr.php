<?php

namespace Foodsharing\Modules\WallPost;

use Flourish\fImage;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Permissions\WallPostPermissions;
use Foodsharing\Services\NotificationService;
use Foodsharing\Services\SanitizerService;

class WallPostXhr extends Control
{
	private $notificationService;
	private $wallPostGateway;
	private $wallPostPermissions;
	private $table;
	private $id;
	private $sanitizerService;

	public function __construct(
		NotificationService $notificationService,
		WallPostGateway $wallPostGateway,
		WallPostPermissions $wallPostPermissions,
		WallPostView $view,
		Session $session,
		SanitizerService $sanitizerService
	) {
		$this->notificationService = $notificationService;
		$this->wallPostGateway = $wallPostGateway;
		$this->wallPostPermissions = $wallPostPermissions;
		$this->view = $view;
		$this->session = $session;
		$this->sanitizerService = $sanitizerService;

		parent::__construct();

		if ((int)$_GET['id'] > 0 && $this->wallPostGateway->isValidTarget($_GET['table'])) {
			$this->table = $_GET['table'];
			$this->id = (int)$_GET['id'];
			$this->view->setTable($this->table, $this->id);
		} else {
			echo '{status:0}';
		}
	}

	public function delpost()
	{
		if ((int)$_GET['post'] > 0) {
			$postId = (int)$_GET['post'];

			if (!$this->wallPostGateway->isLinkedToTarget($postId, $this->table, $this->id)) {
				return [
					'status' => 0
				];
			}

			$fs = $this->wallPostGateway->getFsByPost($postId);
			if ($fs !== $this->session->id() && !$this->wallPostPermissions->mayDeleteFromWall($this->session->id(), $this->table, $this->id)) {
				return XhrResponses::PERMISSION_DENIED;
			}

			if ($this->wallPostGateway->deletePost($postId)) {
				$this->wallPostGateway->unlinkPost($postId, $this->table);

				return [
					'status' => 1
				];
			}
		}

		return [
			'status' => 0
		];
	}

	public function update()
	{
		if (!$this->wallPostPermissions->mayReadWall($this->session->id() ?? 0, $this->table, $this->id)) {
			return XhrResponses::PERMISSION_DENIED;
		}

		if ((int)$this->wallPostGateway->getLastPostId($this->table, $this->id) != (int)$_GET['last']) {
			if ($posts = $this->wallPostGateway->getPosts($this->table, $this->id)) {
				return [
					'status' => 1,
					'html' => $this->view->posts($posts, $this->wallPostPermissions->mayDeleteFromWall($this->session->id() ?? 0, $this->table, $this->id))
				];
			}
		} else {
			return [
				'status' => 0
			];
		}
	}

	public function quickreply()
	{
		if (!$this->wallPostPermissions->mayWriteWall($this->session->id(), $this->table, $this->id)) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$message = trim(strip_tags($_POST['msg'] ?? ''));

		if (!empty($message) && $post_id = $this->wallPostGateway->addPost(
				$message,
				$this->session->id(),
				$this->table,
				$this->id
			)) {
			echo json_encode(
				[
					'status' => 1,
					'message' => 'Klasse! Dein Pinnwandeintrag wurde gespeichert.'
				]
			);
			exit();
		}

		echo json_encode(
			[
			'status' => 0,
			'message' => 'Upps! Dein Pinnwandeintrag konnte nicht gespeichert werden.'
			]
		);
		exit();
	}

	public function post()
	{
		if (!$this->wallPostPermissions->mayWriteWall($this->session->id(), $this->table, $this->id)) {
			return XhrResponses::PERMISSION_DENIED;
		}

		$message = strip_tags($_POST['text']);
		if (!(empty($message) && empty($_POST['attach']))) {
			$attach = '';
			if (!empty($_POST['attach'])) {
				$parts = explode(':', $_POST['attach']);
				if (count($parts) > 0) {
					$attach = [];
					foreach ($parts as $p) {
						$file = explode('-', $p);
						if (count($file) > 0) {
							if (!isset($attach[$file[0]])) {
								$attach[$file[0]] = [];
							}
							$attach[$file[0]][] = [
								'file' => $file[1]
							];
						}
					}
					$attach = json_encode($attach);
				}
			}
			if ($this->wallPostGateway->addPost($message, $this->session->id(), $this->table, $this->id, $attach)) {
				if ($this->table === 'fairteiler') {
					$this->notificationService->newFairteilerPost($this->id);
				}

				return [
					'status' => 1,
					'html' => $this->view->posts($this->wallPostGateway->getPosts($this->table, $this->id), $this->wallPostPermissions->mayDeleteFromWall($this->session->id(), $this->table, $this->id))
				];
			}
		}
	}

	private function isAllowed(string $table)
	{
		return $this->wallPostGateway->isValidTarget($table);
	}

	public function attachimage()
	{
		if (!$this->wallPostPermissions->mayWriteWall($this->session->id(), $this->table, $this->id)) {
			return XhrResponses::PERMISSION_DENIED;
		}

		if (isset($_FILES['etattach']['size']) && $_FILES['etattach']['size'] < 9136365 && $this->attach_allow($_FILES['etattach']['name'])) {
			$new_filename = uniqid('', true);

			$ext = strtolower($_FILES['etattach']['name']);
			$ext = explode('.', $ext);
			if (count($ext) > 1) {
				$ext = end($ext);
				$ext = trim($ext);
				$ext = '.' . preg_replace('/[^a-z0-9]/', '', $ext);
			} else {
				$ext = '';
			}

			$new_filename .= $ext;

			move_uploaded_file($_FILES['etattach']['tmp_name'], 'images/wallpost/' . $new_filename);

			copy('images/wallpost/' . $new_filename, 'images/wallpost/thumb_' . $new_filename);
			copy('images/wallpost/' . $new_filename, 'images/wallpost/medium_' . $new_filename);
			$image = new fImage('images/wallpost/medium_' . $new_filename);
			$image->resize(530, 0);
			$image->saveChanges();

			$image = new fImage('images/wallpost/' . $new_filename);
			$image->resize(1000, 0);
			$image->saveChanges();

			$image = new fImage('images/wallpost/thumb_' . $new_filename);
			$image->cropToRatio(1, 1);
			$image->resize(75, 75);
			$image->saveChanges();

			$init = 'window.parent.mb_finishImage("' . $new_filename . '");';
		} else {
			$init = 'window.parent.pulseInfo(\'' . $this->sanitizerService->jsSafe($this->translationHelper->s('file_to_big')) . '\');window.parent.mb_clear();';
		}

		echo '<html><head>

		<script type="text/javascript">
			function init()
			{
				' . $init . '
			}
		</script>
				
		</head><body onload="init();"></body></html>';

		exit();
	}

	public function attach_allow(string $filename): bool
	{
		if (strlen($filename) < 300) {
			$ext = explode('.', $filename);
			$ext = end($ext);
			$ext = strtolower($ext);
			$allowed = [
				'jpg' => true,
				'jpeg' => true,
				'png' => true,
				'gif' => true
			];

			if (isset($allowed[$ext])) {
				return true;
			}
		}

		return false;
	}
}
