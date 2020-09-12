<?php

namespace Foodsharing\Modules\Basket;

use Flourish\fImage;
use Foodsharing\Lib\WebSocketConnection;
use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\BasketRequests\Status as RequestStatus;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Utility\ImageHelper;
use Foodsharing\Utility\TimeHelper;

class BasketXhr extends Control
{
	private BasketGateway $basketGateway;
	private FoodsaverGateway $foodsaverGateway;
	private TimeHelper $timeHelper;
	private ImageHelper $imageService;
	private WebSocketConnection $webSocketConnection;

	public function __construct(
		BasketView $view,
		BasketGateway $basketGateway,
		FoodsaverGateway $foodsaverGateway,
		TimeHelper $timeHelper,
		ImageHelper $imageService,
		WebSocketConnection $webSocketConnection
	) {
		$this->view = $view;
		$this->basketGateway = $basketGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->timeHelper = $timeHelper;
		$this->imageService = $imageService;
		$this->webSocketConnection = $webSocketConnection;

		parent::__construct();

		// allowed methods for users who are not logged in
		$allowed = [
			'bubble',
			'login',
			'nearbyBaskets',
		];

		if (!$this->session->may() && !in_array($_GET['m'], $allowed)) {
			echo json_encode(
				[
					'status' => 1,
					'script' => 'pulseError("' . $this->translator->trans('basket.no-login') . '");',
				],
				JSON_THROW_ON_ERROR
			);
			exit();
		}
	}

	public function newBasket(): array
	{
		$dia = new XhrDialog();
		$dia->setTitle($this->translator->trans('basket.add'));

		$basketProvider = $this->foodsaverGateway->getFoodsaver($this->session->id());

		if (empty($basketProvider['lat']) || empty($basketProvider['lon'])) {
			$settingsLink = '<a href="/?page=settings&sub=general">' . $this->translator->trans('terminology.settings') . '</a>';
			$dia->addContent($this->v_utils->v_info(
				$this->translator->trans('basket.no-address'),
				'<br>' . $this->translator->trans('notice', ['{settings}' => $settingsLink])
			));
			$dia->addButton($this->translator->trans('basket.go-to-settings'),
				'goTo(\'/?page=settings&sub=general\');'
			);

			return $dia->xhrout();
		}

		$basketProvider = $this->foodsaverGateway->getFoodsaver($this->session->id());

		$dia->addContent($this->v_utils->v_info(
			$this->translator->trans('basket.public-info'),
			$this->translator->trans('notice')
		));

		$dia->addPictureField('picture', $this->translator->trans('basket.image'));

		$dia->addContent($this->view->basketForm($basketProvider));

		$dia->addJs('
		$("#tel-wrapper").hide();
		$("#handy-wrapper").hide();

		$("input.input.cb-contact_type[value=\'2\']").on("change", function () {
			if (this.checked) {
				$("#tel-wrapper").show();
				$("#handy-wrapper").show();
			} else {
				$("#tel-wrapper").hide();
				$("#handy-wrapper").hide();
			}
		});

		$(".cb-food_art[value=3]").on("click", function () {
			if (this.checked) {
				$(".cb-food_art[value=2]")[0].checked = true;
			}
		});');

		$dia->noOverflow();

		$dia->addOpt('width', '90%');

		$dia->addButton(
			$this->translator->trans('basket.publish'),
			'ajreq(\'publish\',{'
			. 'appost: 0,'
			. 'app: \'basket\','
			. 'data: $(\'#' . $dia->getId() . ' .input\').serialize(),'
			. 'description: $(\'#description\').val(),'
			. 'picture: $(\'#' . $dia->getId() . '-picture-filename\').val(),'
			. 'weight: $(\'#weight\').val()'
			. '});'
		);

		return $dia->xhrout();
	}

	public function publish(): array
	{
		$data = false;
		$basketProvider = $this->foodsaverGateway->getFoodsaver($this->session->id());

		parse_str($_GET['data'], $data);

		$desc = strip_tags($data['description']);

		$desc = trim($desc);

		if (empty($desc)) {
			return [
				'status' => 1,
				'script' => 'pulseInfo("' . $this->translator->trans('basket.no-desc') . '");',
			];
		}

		$pic = '';
		$weight = (float)$data['weight'];

		if (isset($data['filename'])) {
			$pic = $this->preparePicture($data['filename']);
		}

		$contact_type = 1;
		$tel = [
			'tel' => '',
			'handy' => '',
		];

		if (isset($data['contact_type']) && is_array($data['contact_type'])) {
			$contact_type = implode(':', $data['contact_type']);
			if (in_array(2, $data['contact_type'])) {
				$tel = [
					'tel' => preg_replace('/[^0-9\-\/]/', '', $data['tel']),
					'handy' => preg_replace('/[^0-9\-\/]/', '', $data['handy']),
				];
			}
		}

		// fix lifetime between 1 and 21 days and convert from days to seconds
		$lifetime = (int)$data['lifetime'];
		if ($lifetime < 1 || $lifetime > 21) {
			$lifetime = 7;
		}
		$lifetime *= 60 * 60 * 24;

		$id = $this->basketGateway->addBasket(
			$desc,
			$pic,
			$tel,
			$contact_type,
			$weight,
			0,
			(float)$basketProvider['lat'],
			(float)$basketProvider['lon'],
			$lifetime,
			$this->session->user('bezirk_id'),
			$this->session->id()
		);

		if (!$id) {
			return [
				'status' => 1,
				'script' => 'pulseInfo("' . $this->translator->trans('basket.publish_error') . '");',
			];
		}

		if (isset($data['food_type']) && is_array($data['food_type'])) {
			$types = [];
			foreach ($data['food_type'] as $foodType) {
				if ((int)$foodType > 0) {
					$types[] = (int)$foodType;
				}
			}

			$this->basketGateway->addTypes($id, $types);
		}

		if (isset($data['food_art']) && is_array($data['food_art'])) {
			$kinds = [];
			foreach ($data['food_art'] as $foodKind) {
				if ((int)$foodKind > 0) {
					$kinds[] = (int)$foodKind;
				}
			}

			$this->basketGateway->addKind($id, $kinds);
		}

		return [
			'status' => 1,
			'script' => '
				pulseInfo("' . $this->translator->trans('basket.published') . '");
				basketStore.loadBaskets();
				$(".xhrDialog").dialog("close");
				$(".xhrDialog").dialog("destroy");
				$(".xhrDialog").remove();',
		];
	}

	private function resizePic(string $pic): void
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

	public function nearbyBaskets(): void
	{
		$xhr = new Xhr();

		if (isset($_GET['coordinates']) && $basket = $this->basketGateway->listNearbyBasketsByDistance(
				$this->session->id(),
				[
					'lat' => $_GET['coordinates'][0],
					'lon' => $_GET['coordinates'][1],
				]
			)) {
			$xhr->addData('baskets', $basket);
		}

		$xhr->send();
	}

	public function bubble(): array
	{
		$basket = $this->basketGateway->getBasket($_GET['id']);
		if (!$basket) {
			return [
				'status' => 1,
				'script' => 'pulseError("' . $this->translator->trans('basket.error') . '");',
			];
		}

		if ($basket['fsf_id'] == 0) {
			$dia = new XhrDialog();

			// What does the user see if not logged in?
			if (!$this->session->may()) {
				$dia->setTitle($this->translator->trans('terminology.basket'));
				$dia->addContent($this->view->bubbleNoUser($basket));
			} else {
				$dia->setTitle($this->translator->trans('basket.by', ['{name}' => $basket['fs_name']]));
				$dia->addContent($this->view->bubble($basket));
			}

			$dia->addButton($this->translator->trans('basket.go'),
				'goTo(\'/essenskoerbe/' . (int)$basket['id'] . '\');'
			);

			$modal = false;
			if (isset($_GET['modal'])) {
				$modal = true;
			}
			$dia->addOpt('modal', 'false', $modal);
			$dia->addOpt('resizeable', 'false', false);

			$dia->noOverflow();

			return $dia->xhrout();
		}

		return $this->fsBubble($basket);
	}

	private function fsBubble(array $basket): array
	{
		$dia = new XhrDialog();

		$dia->setTitle($this->translator->trans('basket.on', ['{platform}' => BASE_URL]));

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

		return $dia->xhrout();
	}

	public function removeRequest(): ?array
	{
		$request = $this->basketGateway->getRequest($_GET['id'], $_GET['fid'], $this->session->id());
		if (!$request) {
			return null;
		}

		$dia = new XhrDialog();

		$dia->addOpt('width', '400');
		$dia->noOverflow();
		$dia->setTitle($this->translator->trans('basket.change-state', ['{name}' => $request['fs_name']]));

		$pronoun = $this->translator->trans('pronoun.' . $request['fs_gender']);
		$dia->addContent(
			'<div>
				<img src="' . $this->imageService->img($request['fs_photo']) . '" style="float: left; margin-right: 10px;">
				<p>' . $this->translator->trans('Anfragezeitpunkt:', [
					'{time}' => $this->timeHelper->niceDate($request['time_ts']),
				]) . '</p>
				<div style="clear: both;"></div>
			</div>'
			. $this->v_utils->v_form_radio('fetchstate', [
				'values' => [
					[
						'id' => RequestStatus::DELETED_PICKED_UP,
						'name' => $this->translator->trans('basket.state.okay', ['{pronoun}' => $pronoun]),
					],
					[
						'id' => RequestStatus::NOT_PICKED_UP,
						'name' => $this->translator->trans('basket.state.nope', ['{pronoun}' => $pronoun]),
					],
					[
						'id' => RequestStatus::DELETED_OTHER_REASON,
						'name' => $this->translator->trans('basket.state.gone'),
					],
					[
						'id' => RequestStatus::DENIED,
						'name' => $this->translator->trans('basket.state.deny'),
					],
				],
				'selected' => RequestStatus::DELETED_PICKED_UP,
			])
		);
		$dia->addAbortButton();
		$dia->addButton($this->translator->trans('button.next'),
			'ajreq(\'finishRequest\',{'
			. 'app: \'basket\','
			. 'id:' . (int)$_GET['id'] . ','
			. 'fid:' . (int)$_GET['fid'] . ','
			. 'sk: $(\'#fetchstate-wrapper input:checked\').val()'
			. '});'
		);

		return $dia->xhrout();
	}

	public function editBasket()
	{
		$basket = $this->basketGateway->getBasket($_GET['id']);

		if ($basket['fs_id'] !== $this->session->id()) {
			return XhrResponses::PERMISSION_DENIED;
		}

		$dia = new XhrDialog();
		$dia->setTitle($this->translator->trans('basket.edit'));

		$dia->addPictureField('picture', $this->translator->trans('basket.image'));

		$dia->addContent($this->view->basketEditForm($basket));

		$dia->addOpt('width', '90%');
		$dia->noOverflow();

		$dia->addButton($this->translator->trans('basket.publish'),
			'ajreq(\'publishEdit\',{'
			. 'appost: 0,'
			. 'app: \'basket\','
			. 'data: $(\'#' . $dia->getId() . ' .input\').serialize(),'
			. 'description: $(\'#description\').val(),'
			. 'picture: $(\'#' . $dia->getId() . '-picture-filename\').val(),'
			. 'basket_id: $(\'#basket_id\').val()'
			. '});'
		);

		return $dia->xhrout();
	}

	public function publishEdit(): array
	{
		$data = false;

		parse_str($_GET['data'], $data);

		$basketId = strip_tags($_GET['basket_id']);
		if (empty($basketId)) {
			return [
				'status' => 1,
				'script' => 'pulseInfo("' . $this->translator->trans('basket.publish_error') . '");',
			];
		}
		$basketId = intval($basketId);

		$basket = $this->basketGateway->getBasket($basketId);
		if ($basket['fs_id'] != $this->session->id()) {
			return [
				'status' => 1,
				'script' => 'pulseInfo("' . $this->translator->trans('basket.not-allowed') . '");',
			];
		}

		$desc = strip_tags($data['description']);
		$desc = trim($desc);
		if (empty($desc)) {
			return [
				'status' => 1,
				'script' => 'pulseInfo("' . $this->translator->trans('basket.no-desc') . '");',
			];
		}

		$pic = $basket['picture'];
		if (isset($data['filename']) && !empty($data['filename'])) {
			$pic = $this->preparePicture($data['filename']);
		}

		if ($this->basketGateway->editBasket($basketId, $desc, $pic, $basket['lat'], $basket['lon'], $this->session->id())) {
			return [
				'status' => 1,
				'script' => '
					pulseInfo("' . $this->translator->trans('basket.published') . '");
					basketStore.loadBaskets();
					$(".xhrDialog").dialog("close");
					$(".xhrDialog").dialog("destroy");
					$(".xhrDialog").remove();
					window.reload()',
			];
		} else {
			return [
				'status' => 1,
				'script' => 'pulseInfo("' . $this->translator->trans('basket.publish_error') . '");',
			];
		}
	}

	public function finishRequest(): array
	{
		if (!isset($_GET['sk']) || (int)$_GET['sk'] <= 0) {
			return [
				'status' => 1,
				'script' => 'pulseError("' . $this->translator->trans('error_unexpected') . '");',
			];
		}

		if ($this->basketGateway->getRequest($_GET['id'], $_GET['fid'], $this->session->id())) {
			$this->basketGateway->setStatus($_GET['id'], $_GET['sk'], $_GET['fid']);

			return [
				'status' => 1,
				'script' => '
					pulseInfo("' . $this->translator->trans('basket.state.finished') . '");
					$(".xhrDialog").dialog("close");
					$(".xhrDialog").dialog("destroy");
					$(".xhrDialog").remove();',
			];
		} else {
			return [
				'status' => 1,
				'script' => 'pulseError("' . $this->translator->trans('error_unexpected') . '");',
			];
		}
	}

	private function preparePicture(string $filename): string
	{
		$pic = preg_replace('/[^a-z0-9\.]/', '', $filename);
		if (!empty($pic) && file_exists('tmp/' . $pic)) {
			$this->resizePic($pic);
		}

		return $pic;
	}
}
