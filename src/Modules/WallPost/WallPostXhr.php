<?php

namespace Foodsharing\Modules\WallPost;

use Flourish\fImage;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\Control;

class WallPostXhr extends Control
{
	private $wallPostGateway;
	private $table;
	private $id;

	public function __construct(WallPostGateway $wallPostGateway, WallPostView $view, Session $session)
	{
		$this->wallPostGateway = $wallPostGateway;
		$this->view = $view;
		$this->session = $session;

		parent::__construct();

		if ($this->wallPostGateway->isValidTarget($_GET['table']) && (int)$_GET['id'] > 0) {
			$this->table = $_GET['table'];
			$this->id = (int)$_GET['id'];
			$this->view->setTable($this->table, $this->id);
		} else {
			echo '{status:0}';
			exit();
		}
	}

	public function delpost()
	{
		if ((int)$_GET['post'] > 0) {
			$postId = (int)$_GET['post'];
			$fs = $this->wallPostGateway->getFsByPost($postId);
			if ($fs == $this->func->fsId()
				|| (!in_array($this->table, array('fairteiler', 'foodsaver')) && ($this->func->isBotschafter() || $this->session->isOrgaTeam()))
			) {
				if ($this->wallPostGateway->deletePost($postId)) {
					$this->wallPostGateway->unlinkPost($postId, $this->table);

					return array(
						'status' => 1
					);
				}
			}
		}

		return array(
			'status' => 0
		);
	}

	public function update()
	{
		if ((int)$this->wallPostGateway->getLastPostId($this->table, $this->id) != (int)$_GET['last']) {
			if ($posts = $this->wallPostGateway->getPosts($this->table, $this->id)) {
				return array(
					'status' => 1,
					'html' => $this->view->posts($posts)
				);
			}
		} else {
			return array(
				'status' => 0
			);
		}
	}

	public function quickreply()
	{
		$message = trim(strip_tags(isset($_POST['msg']) ?? ''));

		if (!empty($message)) {
			if ($post_id = $this->wallPostGateway->addPost($message, $this->session->id(), $this->table, $this->id)) {
				echo json_encode(array(
					'status' => 1,
					'message' => 'Klasse! Dein Pinnwandeintrag wurde gespeichert.'
				));
				exit();
			}
		}

		echo json_encode(array(
			'status' => 0,
			'message' => 'Upps! Dein Pinnwandeintrag konnte nicht gespeichert werden.'
		));
		exit();
	}

	public function post()
	{
		$message = strip_tags($_POST['text']);
		if (!(empty($message) && empty($_POST['attach']))) {
			$attach = '';
			if (!empty($_POST['attach'])) {
				$parts = explode(':', $_POST['attach']);
				if (count($parts) > 0) {
					$attach = array();
					foreach ($parts as $p) {
						$file = explode('-', $p);
						if (count($file) > 0) {
							if (!isset($attach[$file[0]])) {
								$attach[$file[0]] = array();
							}
							$attach[$file[0]][] = array(
								'file' => $file[1]
							);
						}
					}
					$attach = json_encode($attach);
				}
			}
			if ($post_id = $this->wallPostGateway->addPost($message, $this->session->id(), $this->table, $this->id, $attach)) {
				return array(
					'status' => 1,
					'html' => $this->view->posts($this->wallPostGateway->getPosts($this->table, $this->id)),
					'script' => '
					if(typeof u_wallpostReady !== \'undefined\' && $.isFunction(u_wallpostReady))
					{
						u_wallpostReady(' . (int)$post_id . ');
					}'
				);
			}
		}
	}

	private function isAllowed($table)
	{
		return $this->wallPostGateway->isValidTarget($table);
	}

	public function attachimage()
	{
		$init = '';
		if (isset($_FILES['etattach']['size']) && $_FILES['etattach']['size'] < 9136365 && $this->attach_allow($_FILES['etattach']['name'], $_FILES['etattach']['type'])) {
			$new_filename = uniqid();

			$ext = strtolower($_FILES['etattach']['name']);
			$ext = explode('.', $ext);
			if (count($ext) > 1) {
				$ext = end($ext);
				$ext = trim($ext);
				$ext = '.' . preg_replace('/[^a-z0-9]/', '', $ext);
			} else {
				$ext = '';
			}

			$new_filename = $new_filename . $ext;

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
		} elseif (!$this->attach_allow($_FILES['etattach']['name'])) {
			$init = 'window.parent.pulseInfo(\'' . $this->func->jsSafe($this->func->s('wrong_file')) . '\');window.parent.mb_clear();';
		} else {
			$init = 'window.parent.pulseInfo(\'' . $this->func->jsSafe($this->func->s('file_to_big')) . '\');window.parent.mb_clear();';
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

	public function attach_allow($filename, $mime = '')
	{
		if (strlen($filename) < 300) {
			$ext = explode('.', $filename);
			$ext = end($ext);
			$ext = strtolower($ext);
			$allowed = array(
				'jpg' => true,
				'jpeg' => true,
				'png' => true,
				'gif' => true
			);
			$notallowed_mime = array();

			if (isset($allowed[$ext])) {
				return true;
			}
		}

		return false;
	}
}
