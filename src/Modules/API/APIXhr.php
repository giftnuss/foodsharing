<?php

namespace Foodsharing\Modules\API;

use Flourish\fImage;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Basket\BasketModel;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Message\MessageModel;

class APIXhr extends Control
{
	private $messageModel;
	private $basketModel;
	private $gateway;

	public function __construct(APIGateway $gateway, MessageModel $messageModel, BasketModel $basketModel)
	{
		$this->gateway = $gateway;
		$this->messageModel = $messageModel;
		$this->basketModel = $basketModel;
		parent::__construct();

		if ($_GET['m'] != 'login' && !S::may()) {
			$this->appout([
				'status' => 2
			]);
		}
	}

	public function udata(): void
	{
		if ($user = $this->gateway->getValues(['id', 'name', 'photo'], 'foodsaver', $_GET['i'])) {
			$this->appout([
				'status' => 1,
				'user' => $user
			]);
		}

		$this->appout([
			'status' => 0
		]);
	}

	public function sendmsg(): void
	{
		$message = strip_tags($_GET['ms']);
		$message = trim($message);

		if ((int)$_GET['id'] > 0 && $message != '') {
			$conversation_id = (int)$_GET['id'];

			if ($this->messageModel->mayConversation($conversation_id)) {
				$id = $this->messageModel->sendMessage($conversation_id, $message);

				if ($member = $this->messageModel->listConversationMembers($conversation_id)) {
					foreach ($member as $m) {
						if ($m['id'] != $this->func->fsId()) {
							Mem::userAppend($m['id'], 'msg-update', $conversation_id);

							$this->func->sendSock($m['id'], 'conv', 'push', [
								'id' => $id,
								'cid' => $conversation_id,
								'fs_id' => $this->func->fsId(),
								'fs_name' => S::user('name'),
								'fs_photo' => S::user('photo'),
								'body' => $message,
								'time' => date('Y-m-d H:i:s')
							]);

							$this->sendEmailIfUserNotOnline($m, $conversation_id, $message);
						}
					}
				}

				$this->appout([
					'status' => 1,
					'time' => time(),
					'msg' => $message,
					'id' => (int)$_GET['id']
				]);
			}
		}

		$this->appout([
			'status' => 0
		]);
	}

	public function chathistory(): void
	{
		$cid = (int)$_GET['id'];

		if ($this->messageModel->mayConversation($cid) && $history = $this->messageModel->chatHistory($cid)) {
			$this->appout([
				'status' => 1,
				'id' => $cid,
				'history' => $history,
				'user' => $this->messageModel->listConversationMembers($cid)
			]);
		}

		$this->appout([
			'status' => 0
		]);
	}

	public function upload(): void
	{
		$ext = explode('.', $_FILES['file']['name']);
		$ext = end($ext);

		$pic = uniqid() . '.' . $ext;
		move_uploaded_file($_FILES['file']['tmp_name'], 'tmp/' . $pic);

		echo $pic;
		exit();
	}

	public function logout(): void
	{
		$this->gateway->logout();
		$_SESSION['login'] = false;
		$_SESSION = array();

		S::destroy();

		$this->appout([
			'status' => 1
		]);
	}

	public function login(): void
	{
		if (isset($_GET['e']) && $this->gateway->login($_GET['e'], $_GET['p'])) {
			$fs = $this->gateway->getValues(['telefon', 'handy', 'geschlecht', 'name', 'lat', 'lon', 'photo'], 'foodsaver', $this->func->fsId());

			$this->appout([
				'status' => 1,
				'token' => session_id(),
				'gender' => $fs['geschlecht'],
				'phone' => $fs['telefon'],
				'phone_mobile' => $fs['handy'],
				'id' => $this->func->fsId(),
				'name' => $fs['name'],
				'lat' => $fs['lat'],
				'lon' => $fs['lon'],
				'photo' => $fs['photo']
			]);
		}

		$this->appout([
			'status' => 0
		]);
	}

	public function initRelogin(): void
	{
		$this->appout([
			'status' => 0
		]);
	}

	public function basket_submit(): void
	{
		if (S::may()) {
			$desc = strip_tags($_GET['desc']);
			$tmp = array();

			if (isset($_GET['art'])) {
				$art = $_GET['art'];
				foreach ($art as $a) {
					if ((int)$a > 0) {
						$tmp[] = (int)$a;
					}
				}
			}
			$art = $tmp;

			$tmp = array();

			if (isset($_GET['types'])) {
				$types = $_GET['types'];
				foreach ($types as $t) {
					if ((int)$t > 0) {
						$tmp[] = (int)$t;
					}
				}
			}
			$types = $tmp;

			$tmp = array();

			$cTypes = $this->contactTypes($tmp);

			if (empty($cTypes)) {
				$cTypes = [1];
			}

			if (!empty($desc)) {
				$weight = (float)$_GET['weight'];
				if ($weight <= 0) {
					$weight = 3;
				}

				$tel = [
					'tel' => preg_replace('[^0-9\ \+]', '', $_GET['phone']),
					'handy' => preg_replace('[^0-9\ \+]', '', $_GET['phone_mobile'])
				];

				$photo = '';
				if (isset($_GET['photo']) && !empty($_GET['photo']) && $this->resizePic($_GET['photo'])) {
					$photo = strip_tags($_GET['photo']);
				}

				$fs = $this->gateway->getValues(['lat', 'lon'], 'foodsaver', $this->func->fsId());

				$lat = $fs['lat'];
				$lon = $fs['lon'];

				if ($_GET['fp'] == 'loc') {
					$llat = (float)$_GET['lat'];
					$llon = (float)$_GET['lon'];

					if (strlen($lat . '') > 2 && strlen($lon . '') > 2) {
						$lat = $llat;
						$lon = $llon;
					}
				}

				if ($id = $this->basketModel->addBasket(
					$desc,
					$photo, // pic
					$tel, // phone
					implode(':', $cTypes),
					$weight, // weight
					(int)$_GET['fetchart'], // location type
					$lat, // lat
					$lon, // lon
					S::user('bezirk_id')
				)
				) {
					if (!empty($art)) {
						$this->basketModel->addArt($id, $art);
					}
					if (!empty($types)) {
						$this->basketModel->addTypes($id, $types);
					}

					$this->appout([
						'status' => 1
					]);
				}
			}
		}

		$this->initRelogin();
	}

	public function resizePic($pic): bool
	{
		if (file_exists('tmp/' . $pic)) {
			copy('tmp/' . $pic, 'images/basket/' . $pic);
			$this->chmod('images/basket/' . $pic);

			$img = new fImage('images/basket/' . $pic);

			$img->resize(800, 800);
			$img->saveChanges();

			copy('images/basket/' . $pic, 'images/basket/thumb-' . $pic);
			copy('images/basket/' . $pic, 'images/basket/medium-' . $pic);

			$this->chmod('images/basket/thumb-' . $pic);
			$this->chmod('images/basket/medium-' . $pic);

			$img = new fImage('images/basket/thumb-' . $pic);
			$img->cropToRatio(1, 1);
			$img->resize(200, 200);
			$img->saveChanges();

			$img = new fImage('images/basket/medium-' . $pic);
			$img->resize(450, 450);
			$img->saveChanges();

			return true;
		}

		return false;
	}

	private function chmod($file): void
	{
		exec('chmod 777 /var/www/lmr-v1/freiwillige/' . $file);
	}

	public function checklogin(): void
	{
		if (S::may()) {
			$this->appout([
				'status' => 1
			]);
		}
		$this->appout([
			'status' => 0
		]);
	}

	public function orgagruppen(): void
	{
		if ($groups = $this->gateway->getOrgaGroups()) {
			$this->out($groups);
		}
	}

	public function auth(): void
	{
		if ($ret = $this->gateway->checkClient($_GET['user'], $_GET['pass'])) {
			$values = $this->gateway->getValues(['id', 'orgateam', 'name', 'email', 'photo', 'geschlecht', 'rolle'], 'foodsaver', $ret['id']);

			$values['bot'] = $values['rolle'] >= 3;

			$values['menu'] = false;

			$this->out([
				'status' => 1,
				'data' => $values
			]);
		} else {
			$this->out([
				'status' => 0
			]);
		}
	}

	private function out($data): void
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		echo json_encode($data);
		exit();
	}

	public function loadBasket(): void
	{
		if (S::may() && $basket = $this->gateway->getBasket($_GET['id'])) {
			$this->appout([
				'status' => 1,
				'basket' => $basket
			]);
		}

		$this->appout([
			'status' => 0
		]);
	}

	public function allbaskets(): void
	{
		if (S::may() && $baskets = $this->gateway->allBaskets()) {
			$this->appout([
				'status' => 1,
				'baskets' => $baskets
			]);
		}
		$this->appout([
			'status' => 0
		]);
	}

	public function basketsnear(): void
	{
		if (isset($_GET['lat'], $_GET['lon']) && S::may()) {
			$lat = (float)$_GET['lat'];
			$lon = (float)$_GET['lon'];

			if ($baskets = $this->gateway->nearBaskets($lat, $lon)) {
				$this->appout([
					'status' => 1,
					'baskets' => $baskets
				]);
			}
		}
		$this->appout([
			'status' => 0
		]);
	}

	public function loadrequests(): void
	{
		if ($convs = $this->messageModel->listConversations()) {
			$out = array();
			foreach ($convs as $c) {
				$out[] = [
					't' => $this->func->niceDateShort($c['last_ts']),
					'n' => $c['name'],
					'id' => $c['id'],
					'u' => $c['member'],
					'lu' => $c['last_foodsaver_id'],
					'm' => $c['last_message']
				];
			}

			$this->appout([
				'status' => 1,
				'requests' => $out
			]);
		}

		$this->appout([
			'status' => 0
		]);
	}

	/**
	 * @param $m
	 * @param $conversation_id
	 * @param $message
	 */
	private function sendEmailIfUserNotOnline($m, $conversation_id, $message): void
	{
		if ($this->messageModel->wantMsgEmailInfo($m['id'])) {
			$this->convMessage($m, $conversation_id, $message, $this->messageModel);
		}
	}

	/**
	 * @param $tmp
	 *
	 * @return mixed
	 */
	private function contactTypes($tmp)
	{
		if (isset($_GET['ctype'])) {
			$cTypes = $_GET['ctype'];

			foreach ($cTypes as $t) {
				if (in_array((int)$t, [1, 2])) {
					$tmp[(int)$t] = (int)$t;
				}
			}
		}

		return $tmp;
	}
}
