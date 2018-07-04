<?php

namespace Foodsharing\Modules\Basket;

use Flourish\fImage;
use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\BasketRequests\Status;
use Foodsharing\Modules\Core\Model;
use Foodsharing\Modules\Message\MessageModel;

class BasketXhr extends Control
{
	private $status;
	private $basketGateway;
	private $messageModel;

	public function __construct(Model $model, BasketView $view, BasketGateway $basketGateway, MessageModel $messageModel
	){
		$this->model = $model;
		$this->messageModel = $messageModel;
		$this->view = $view;
		$this->basketGateway = $basketGateway;

		$this->status = array(
			'ungelesen' => Status::REQUESTED_MESSAGE_UNREAD,
			'gelesen' => Status::REQUESTED_MESSAGE_READ,
			'abgeholt' => Status::DELETED_PICKED_UP,
			'abgelehnt' => Status::DENIED,
			'nicht_gekommen' => Status::NOT_PICKED_UP,
			'wall_follow' => Status::FOLLOWED,
			'angeklickt' => Status::REQESTED
		);

		parent::__construct();

		/*
		 * allowed method for not logged in users
		 */
		$allowed = array(
			'bubble' => true,
			'login' => true,
			'basketchoords' => true,
			'closebaskets' => true
		);

		if (!$this->session->may() && !isset($allowed[$_GET['m']])) {
			echo json_encode(array(
				'status' => 1,
				'script' => 'pulseError("Du bist nicht eingeloggt, vielleicht ist Deine Session abgelaufen, bitte logge Dich ein und sende Deine Anfrage erneut ab.");'
			));
			exit();
		}
	}

	public function basketchoords()
	{
		$xhr = new Xhr();
		if ($baskets = $this->basketGateway->getBasketCoordinates()) {
			$xhr->addData('baskets', $baskets);
		}

		$xhr->send();
	}

	public function newbasket()
	{
		$dia = new XhrDialog();
		$dia->setTitle('Essenskorb anbieten');

		$dia->addPictureField('picture');

		$foodsaver = $this->model->getValues(array('telefon', 'handy'), 'foodsaver', $this->func->fsId());

		$dia->addContent($this->view->basketForm($foodsaver));

		$dia->addJs('
				
		$("#tel-wrapper").hide();
		$("#handy-wrapper").hide();
		
		$("input.input.cb-contact_type[value=\'2\']").change(function(){
			if(this.checked)
			{
				$("#tel-wrapper").show();
				$("#handy-wrapper").show();	
			}
			else
			{
				$("#tel-wrapper").hide();
				$("#handy-wrapper").hide();
			}
		});
				
		$(".cb-food_art[value=3]").click(function(){
			if(this.checked)
			{
				$(".cb-food_art[value=2]")[0].checked = true;
			}
		});
		');

		$dia->noOverflow();

		$dia->addOpt('width', 550);

		$dia->addButton('Essenskorb veröffentlichen', 'ajreq(\'publish\',{appost:0,app:\'basket\',data:$(\'#' . $dia->getId() . ' .input\').serialize(),description:$(\'#description\').val(),picture:$(\'#' . $dia->getId() . '-picture-filename\').val(),weight:$(\'#weight\').val()});');

		return $dia->xhrout();
	}

	public function publish()
	{
		$data = false;

		parse_str($_GET['data'], $data);

		$desc = strip_tags($data['description']);

		$desc = trim($desc);

		if (empty($desc)) {
			return array(
				'status' => 1,
				'script' => 'pulseInfo("Bitte gib eine Beschreibung ein!");'
			);
		}

		$pic = '';
		$weight = floatval($data['weight']);

		if (isset($data['filename'])) {
			$pic = preg_replace('/[^a-z0-9\.]/', '', $data['filename']);
			if (!empty($pic) && file_exists('tmp/' . $pic)) {
				$this->resizePic($pic);
			}
		}

		$lat = 0;
		$lon = 0;

		$location_type = 0;

		if ($location_type == 0) {
			$fs = $this->model->getValues(array('lat', 'lon'), 'foodsaver', $this->func->fsId());
			$lat = $fs['lat'];
			$lon = $fs['lon'];
		}

		if ($lat == 0 && $lon == 0) {
			return array(
				'status' => 1,
				'script' => 'pulseInfo("Bitte gib in Deinem Profil eine Adresse ein!");'
			);
		}

		$contact_type = 1;
		$tel = array(
			'tel' => '',
			'handy' => ''
		);

		if (isset($data['contact_type']) && is_array($data['contact_type'])) {
			$contact_type = implode(':', $data['contact_type']);
			if (in_array(2, $data['contact_type'])) {
				$tel = array(
					'tel' => preg_replace('/[^0-9\-\/]/', '', $data['tel']),
					'handy' => preg_replace('/[^0-9\-\/]/', '', $data['handy'])
				);
			}
		}

		if (!empty($desc) && ($id = $this->basketGateway->addBasket($desc, $pic, $tel, $contact_type, $weight, $location_type, $lat, $lon, $this->session->user('bezirk_id'), S::id()))) {
			if (isset($data['food_type']) && is_array($data['food_type'])) {
				$types = array();
				foreach ($data['food_type'] as $ft) {
					if ((int)$ft > 0) {
						$types[] = (int)$ft;
					}
				}

				$this->basketGateway->addTypes($id, $types);
			}

			if (isset($data['food_art']) && is_array($data['food_art'])) {
				$arts = array();
				foreach ($data['food_art'] as $ft) {
					if ((int)$ft > 0) {
						$arts[] = (int)$ft;
					}
				}

				$this->basketGateway->addArt($id, $arts);
			}

			return array(
				'status' => 1,
				'script' => '
					$("#msgbar-basket").hide();
					pulseInfo("Danke Dir! Der Essenskorb wurde veröffentlicht!");
					$(".xhrDialog").dialog("close");
					$(".xhrDialog").dialog("destroy");
					$(".xhrDialog").remove();'
			);
		}

		return array(
			'status' => 1,
			'script' => 'pulseError("Es gab einen Fehler, der Essenskorb konnte nicht veröffentlicht werden.");'
		);
	}

	public function resizePic($pic)
	{
		copy('tmp/' . $pic, 'images/basket/' . $pic);

		$img = new fImage('images/basket/' . $pic);
		$img->resize(800, 800);
		$img->saveChanges();

		copy('images/basket/' . $pic, 'images/basket/medium-' . $pic);

		$img = new fImage('images/basket/medium-' . $pic);
		$img->resize(450, 450);
		$img->saveChanges();

		copy('images/basket/medium-' . $pic, 'images/basket/thumb-' . $pic);

		$img = new fImage('images/basket/thumb-' . $pic);
		$img->cropToRatio(1, 1);
		$img->resize(200, 200);
		$img->saveChanges();

		copy('images/basket/thumb-' . $pic, 'images/basket/75x75-' . $pic);

		$img = new fImage('images/basket/75x75-' . $pic);
		$img->cropToRatio(1, 1);
		$img->resize(75, 75);
		$img->saveChanges();

		copy('images/basket/75x75-' . $pic, 'images/basket/50x50-' . $pic);

		$img = new fImage('images/basket/50x50-' . $pic);
		$img->cropToRatio(1, 1);
		$img->resize(50, 50);
		$img->saveChanges();
	}

	public function closebaskets()
	{
		$xhr = new Xhr();

		if (isset($_GET['choords'])) {
			if ($basket = $this->basketGateway->listCloseBaskets($this->session->id(), array(
				'lat' => $_GET['choords'][0],
				'lon' => $_GET['choords'][1]
			))
			) {
				$xhr->addData('baskets', $basket);
			}
		}

		$xhr->send();
	}

	public function bubble()
	{
		if ($basket = $this->basketGateway->getBasket($_GET['id'])) {
			if ($basket['fsf_id'] == 0) {
				$dia = new XhrDialog();

				/*
				 * What see the user if not logged in?
				 */
				if (!$this->session->may()) {
					$dia->setTitle('Essenskorb');
					$dia->addContent($this->view->bubbleNoUser($basket));
				} else {
					$dia->setTitle('Essenskorb von ' . $basket['fs_name']);
					$dia->addContent($this->view->bubble($basket));
				}

				$dia->addButton('zum Essenskorb', 'goTo(\'/essenskoerbe/' . (int)$basket['id'] . '\');');

				$modal = false;
				if (isset($_GET['modal'])) {
					$modal = true;
				}
				$dia->addOpt('modal', 'false', $modal);
				$dia->addOpt('resizeable', 'false', false);

				$dia->addOpt('width', 400);
				$dia->noOverflow();

				$return = $dia->xhrout();

				return $return;
			} else {
				return $this->fsBubble($basket);
			}
		} else {
			return array(
				'status' => 1,
				'script' => 'pulseError("Essenskorb konnte nicht geladen werden");'
			);
		}
	}

	public function fsBubble($basket)
	{
		$dia = new XhrDialog();

		$dia->setTitle('Essenskorb von ' . BASE_URL);

		$dia->addContent($this->view->fsBubble($basket));
		$modal = false;
		if (isset($_GET['modal'])) {
			$modal = true;
		}
		$dia->addOpt('modal', 'false', $modal);
		$dia->addOpt('resizeable', 'false', false);

		$dia->addOpt('width', 400);
		$dia->noOverflow();

		$dia->addJs('$(".fsbutton").button();');

		$return = $dia->xhrout();

		return $return;
	}

	public function request()
	{
		if ($basket = $this->basketGateway->getBasket($_GET['id'])) {
			$this->basketGateway->setStatus($_GET['id'], Status::REQESTED, S::id());
			$dia = new XhrDialog();
			$dia->setTitle('Essenskorb von ' . $basket['fs_name'] . '');
			$dia->addOpt('width', 300);
			$dia->noOverflow();
			$dia->addContent($this->view->contactTitle($basket));

			$contact_type = array(1);

			if (!empty($basket['contact_type'])) {
				$contact_type = explode(':', $basket['contact_type']);
			}

			if (in_array(2, $contact_type)) {
				$dia->addContent($this->view->contactNumber($basket));
			}
			if (in_array(1, $contact_type)) {
				$dia->addContent($this->view->contactMsg($basket));
				$dia->addButton('Anfrage absenden', 'ajreq(\'sendreqmessage\',{appost:0,app:\'basket\',id:' . (int)$_GET['id'] . ',msg:$(\'#contactmessage\').val()});');
			}

			return $dia->xhrout();
		}
	}

	public function sendreqmessage()
	{
		if ($fs_id = $this->model->getVal('foodsaver_id', 'basket', $_GET['id'])) {
			$msg = strip_tags($_GET['msg']);
			$msg = trim($msg);
			if (!empty($msg)) {
				$this->messageModel->message($fs_id, $this->func->fsId(), $msg, 0);
				$this->mailMessage($this->func->fsId(), $fs_id, $msg, 22);
				$this->basketGateway->setStatus($_GET['id'], Status::REQUESTED_MESSAGE_UNREAD, S::id());

				return array(
					'status' => 1,
					'script' => 'if($(".xhrDialog").length > 0){$(".xhrDialog").dialog("close");}pulseInfo("Anfrage wurde versendet.");'
				);
			} else {
				return array(
					'status' => 1,
					'script' => 'pulseError("Du hast keine Nachricht eingegeben");'
				);
			}
		}

		return array(
			'status' => 1,
			'script' => 'pulseError("Es ist ein Fehler aufgetreten");'
		);
	}

	public function infobar()
	{
		$this->session->noWrite();

		$xhr = new Xhr();

		$out = '';
		if ($updates = $this->basketGateway->listUpdates(S::id())) {
			$out = $this->view->listUpdates($updates);
		}

		if ($baskets = $this->basketGateway->listMyBaskets(S::id())) {
			$out .= $this->view->listMyBaskets($baskets);
		}

		$xhr->addData('html', $out);

		$xhr->send();
	}

	public function update()
	{
		$count = $this->basketGateway->getUpdateCount(S::id());
		if ((int)$count > 0) {
			return array(
				'status' => 1,
				'script' => '
					$("#msgBar-badge .bar-basket").text("' . $count . '").css({ opacity: 1 });
					$("#msgbar-basket ul li.loading").remove();
					$("#msgbar-basket ul").prepend(\'<li class="loading">&nbsp;</li>\');
				'
			);
		} else {
			return array(
				'status' => 1,
				'script' => '$("#msgBar-badge .bar-basket").text("0").css({ opacity: 0 });'
			);
		}
	}

	public function answer()
	{
		if ($id = $this->model->getVal('foodsaver_id', 'basket', $_GET['id'])) {
			if ($id == $this->func->fsId()) {
				$this->basketGateway->setStatus($_GET['id'], Status::REQUESTED_MESSAGE_READ, $_GET['fid']);

				return array(
					'status' => 1,
					'script' => 'chat(' . $_GET['fid'] . ');$("#msgbar-basket").hide();ajreq("update",{app:"basket"});'
				);
			}
		}
	}

	public function removeRequest()
	{
		if ($request = $this->basketGateway->getRequest($_GET['id'], $_GET['fid'], S::id())) {
			$dia = new XhrDialog();
			$dia->addOpt('width', '400');
			$dia->noOverflow();
			$dia->setTitle('Essenskorbanfrage von ' . $request['fs_name'] . ' abschließen');
			$dia->addContent(
				'<div>
					<img src="' . $this->func->img($request['fs_photo']) . '" style="float:left;margin-right:10px;">
					<p>Anfragezeitpunkt: ' . $this->func->niceDate($request['time_ts']) . '</p>
					<div style="clear:both;"></div>
				</div>'
				. $this->v_utils->v_form_radio('fetchstate', array(
					'values' => array(
						array('id' => Status::DELETED_PICKED_UP, 'name' => 'Ja, ' . $this->func->genderWord($request['fs_gender'], 'er', 'sie', 'er/sie') . ' hat den Korb abgeholt.'),
						array('id' => Status::NOT_PICKED_UP, 'name' => 'Nein, ' . $this->func->genderWord($request['fs_gender'], 'er', 'sie', 'er/sie') . ' ist leider nicht wie verabredet erschienen.'),
						array('id' => Status::DELETED_OTHER_REASON, 'name' => 'Die Lebensmittel wurden von jemand anderem abgeholt.')),
					'selected' => 3
				))
			);
			$dia->addAbortButton();
			$dia->addButton('Weiter', 'ajreq(\'finishRequest\',{app:\'basket\',id:' . (int)$_GET['id'] . ',fid:' . (int)$_GET['fid'] . ',sk:$(\'#fetchstate-wrapper input:checked\').val()});');

			return $dia->xhrout();
		}
	}

	public function removeBasket()
	{
		$this->basketGateway->removeBasket($_GET['id'], S::id());

		return array(
			'status' => 1,
			'script' => '$(".basket-' . (int)$_GET['id'] . '").remove();pulseInfo("Essenskorb ist jetzt nicht mehr aktiv!");'
		);
	}

	public function follow()
	{
		if (isset($_GET['bid']) && (int)$_GET['bid'] > 0) {
			$this->basketGateway->follow($_GET['bid'], S::id());
		}
	}

	public function finishRequest()
	{
		if ($request = $this->basketGateway->getRequest($_GET['id'], $_GET['fid'], S::id())) {
			if (isset($_GET['sk']) && (int)$_GET['sk'] > 0) {
				$this->basketGateway->setStatus($_GET['id'], $_GET['sk'], $_GET['fid']);

				return array(
					'status' => 1,
					'script' => '
						$(".msg-' . (int)$_GET['id'] . '-' . (int)$_GET['fid'] . '").remove();
						pulseInfo("Danke Dir! Der Vorgang ist abgeschlossen.");
						$(".xhrDialog").dialog("close");
						$(".xhrDialog").dialog("destroy");
						$(".xhrDialog").remove();
						'
				);
			}
		}

		return array(
			'status' => 1,
			'script' => 'pulseError("Es ist ein Fehler aufgetreten");'
		);
	}
}
