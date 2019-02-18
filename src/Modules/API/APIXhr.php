<?php

namespace Foodsharing\Modules\API;

use Flourish\fImage;
use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\WebSocketSender;
use Foodsharing\Modules\Basket\BasketGateway;
use Foodsharing\Services\BasketService;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Login\LoginGateway;
use Foodsharing\Modules\Message\MessageModel;
use Foodsharing\Modules\Message\MessageGateway;
use Foodsharing\Modules\Store\StoreGateway;

class APIXhr extends Control
{
	private $messageModel;
	private $messageGateway;
	private $basketGateway;
	private $apiGateway;
	private $storeGateway;
	private $loginGateway;
	private $webSocketSender;
	private $basketService;

	public function __construct(
		APIGateway $apiGateway,
		LoginGateway $loginGateway,
		MessageModel $messageModel,
		MessageGateway $messageGateway,
		StoreGateway $storeGateway,
		BasketGateway $basketGateway,
		Db $model,
		WebSocketSender $websocketSender,
		BasketService $basketService
	) {
		$this->apiGateway = $apiGateway;
		$this->loginGateway = $loginGateway;
		$this->messageModel = $messageModel;
		$this->messageGateway = $messageGateway;
		$this->storeGateway = $storeGateway;
		$this->basketGateway = $basketGateway;
		$this->model = $model;
		$this->webSocketSender = $websocketSender;
		$this->basketService = $basketService;
		parent::__construct();

		if ($_GET['m'] != 'login' && !$this->session->may()) {
			$this->appout([
				'status' => 2
			]);
		}
	}

	public function udata(): void
	{
		if ($user = $this->model->getValues(['id', 'name', 'photo'], 'foodsaver', $_GET['i'])) {
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
							$this->mem->userAppend($m['id'], 'msg-update', $conversation_id);

							$this->webSocketSender->sendSock($m['id'], 'conv', 'push', [
								'id' => $id,
								'cid' => $conversation_id,
								'fs_id' => $this->func->fsId(),
								'fs_name' => $this->session->user('name'),
								'fs_photo' => $this->session->user('photo'),
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
		$this->mem->logout($this->session->id());
		$_SESSION['login'] = false;
		$_SESSION = array();

		$this->session->destroy();

		$this->appout([
			'status' => 1
		]);
	}

	public function login(): void
	{
		if (!isset($_GET['e'])) {
			$this->appout([
				'status' => 0
			]);
		}

		$fs_id = $this->loginGateway->login($_GET['e'], $_GET['p']);

		if ($fs_id !== null) {
			$this->session->login($fs_id);

			$fs = $this->model->getValues(['telefon', 'handy', 'geschlecht', 'name', 'lat', 'lon', 'photo'], 'foodsaver', $this->session->id());

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
		} else {
			$this->appout([
				'status' => 0
			]);
		}
	}

	public function initRelogin(): void
	{
		$this->appout([
			'status' => 0
		]);
	}

	public function basket_submit(): void
	{
		if ($this->session->may()) {
			$desc = strip_tags($_GET['desc']);
			$tmp = array();

			$cTypes = $this->contactTypes($tmp);

			if (empty($cTypes)) {
				$cTypes = [1];
			}

			if (!empty($desc)) {
				$photo = '';
				if (isset($_GET['photo']) && !empty($_GET['photo']) && $this->resizePic($_GET['photo'])) {
					$photo = strip_tags($_GET['photo']);
				}

				$fs = $this->model->getValues(['lat', 'lon'], 'foodsaver', $this->func->fsId());

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

				if ($basket = $this->basketService->addBasket(
					$desc,
					$photo, // pic
					$_GET['phone'], // tel
					$_GET['phone_mobile'], //handy
					$cTypes,
					(float)$_GET['weight'], // weight
					(int)$_GET['fetchart'], // location type
					$lat, // lat
					$lon, // lon
					(int)$_GET['lifetime']
				)
				) {
					if (isset($_GET['art'])) {
						$this->basketService->addFoodKinds($basket['id'], $_GET['art']);
					}
					if (isset($_GET['types'])) {
						$this->basketService->addFoodTypes($basket['id'], $_GET['types']);
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
		if ($this->session->may()) {
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
		if ($groups = $this->apiGateway->getOrgaGroups()) {
			$this->out($groups);
		}
	}

	public function auth(): void
	{
		if ($ret = $this->loginGateway->checkClient($_GET['user'], $_GET['pass'])) {
			$values = $this->model->getValues(['id', 'orgateam', 'name', 'email', 'photo', 'geschlecht', 'rolle'], 'foodsaver', $ret['id']);

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
		if ($this->session->may() && $basket = $this->apiGateway->getBasket($_GET['id'])) {
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
		if ($this->session->may() && $baskets = $this->apiGateway->allBaskets()) {
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
		if (isset($_GET['lat'], $_GET['lon']) && $this->session->may()) {
			$lat = (float)$_GET['lat'];
			$lon = (float)$_GET['lon'];

			if ($baskets = $this->apiGateway->nearBaskets($lat, $lon)) {
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
			$this->convMessage($m, $conversation_id, $message, $this->messageGateway, $this->conversationGateway);
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
