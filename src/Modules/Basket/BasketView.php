<?php

namespace Foodsharing\Modules\Basket;

use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Lib\View\vMap;
use Foodsharing\Lib\View\vPage;
use Foodsharing\Modules\Core\DBConstants\Map\MapConstants;
use Foodsharing\Modules\Core\View;
use Foodsharing\Permissions\BasketPermissions;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Foodsharing\Utility\ImageHelper;
use Foodsharing\Utility\PageHelper;
use Foodsharing\Utility\RouteHelper;
use Foodsharing\Utility\Sanitizer;
use Foodsharing\Utility\TimeHelper;
use Foodsharing\Utility\TranslationHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class BasketView extends View
{
	private $basketPermissions;

	public function __construct(
		\Twig\Environment $twig,
		Session $session,
		Utils $viewUtils,
		DataHelper $dataHelper,
		IdentificationHelper $identificationHelper,
		ImageHelper $imageService,
		PageHelper $pageHelper,
		RouteHelper $routeHelper,
		Sanitizer $sanitizerService,
		TimeHelper $timeHelper,
		TranslationHelper $translationHelper,
		TranslatorInterface $translator,
		BasketPermissions $basketPermissions
	) {
		$this->basketPermissions = $basketPermissions;
		parent::__construct(
			$twig,
			$session,
			$viewUtils,
			$dataHelper,
			$identificationHelper,
			$imageService,
			$pageHelper,
			$routeHelper,
			$sanitizerService,
			$timeHelper,
			$translationHelper,
			$translator
		);
	}

	public function find(array $baskets, $location): void
	{
		$page = new vPage($this->translator->trans('terminology.baskets'), $this->findMap($location));

		if ($baskets) {
			$label = $this->translator->trans('basket.nearby-short');
			$page->addSectionRight($this->nearbyBaskets($baskets), $label);
		}

		$page->render();
	}

	private function findMap($location): string
	{
		$map = new vMap($location);

		if (is_array($location)) {
			$map->setCenter($location['lat'], $location['lon']);
		} else {
			$map->setCenter(MapConstants::CENTER_GERMANY_LAT, MapConstants::CENTER_GERMANY_LON);
			$map->setZoom(MapConstants::ZOOM_COUNTRY);
		}

		$map->setSearchPanel('mapsearch');
		$map->setMarkerCluster();
		$map->setDefaultMarkerOptions('shopping-basket', 'green');

		return '<div class="ui-widget">
			<input id="mapsearch" type="text" name="mapsearch" value="" placeholder="'
			. $this->translator->trans('basket.mapsearch')
			. '" class="input text value ui-corner-top" />
			<div class="findmap">' . $map->render() . '</div>
		</div>';
	}

	public function nearbyBaskets(array $baskets): string
	{
		$out = '
		<ul class="linklist" id="cbasketlist">';
		foreach ($baskets as $b) {
			$img = '/img/basket.png';
			if (!empty($b['picture'])) {
				$img = '/images/basket/thumb-' . $b['picture'];
			}

			$distance = $this->distance($b['distance']);

			$out .= '<li>
				<a class="ui-corner-all" onclick="ajreq(\'bubble\','
				. '{app: \'basket\''
				. ',id:' . (int)$b['id']
				. ',modal: 1'
				. '}); return false;" href="#">
					<span style="float: left; margin-right: 7px;">
						<img width="35px" src="' . $img . '" class="ui-corner-all">
					</span>
					<span style="height: 35px; overflow: hidden; font-size: 11px; line-height: 16px;">
						<strong style="float: right; margin: 0 0 0 3px;">(' . $distance . ')</strong>'
						. $this->sanitizerService->tt($b['description'], 50) . '
					</span>
					<span style="clear: both;"></span>
				</a>
			</li>';
		}

		return $out . '
		</ul>
		<div style="text-align: center;">
			<a class="button" href="/karte?load=baskets">' . $this->translator->trans('basket.all_map') . '</a>
		</div>';
	}

	public function basket(array $basket, $requests): void
	{
		$label = $this->translator->trans('terminology.basket') . ' #' . $basket['id'];
		$page = new vPage($label,
			'<div class="fbasket-wrap">
				<div class="fbasket-pic">
					' . $this->pageImg($basket['picture'] ?? '') . '
				</div>
				<div class="fbasket-desc">
					<p>' . nl2br($basket['description']) . '</p>
				</div>
			</div>');

		$page->setSubTitle($this->getSubtitle($basket));

		if ($this->session->may()) {
			$page->addSection($this->v_utils->v_info($this->translator->trans('basket.howto')));

			$label = $this->translator->trans('basket.provider');
			$page->addSectionRight($this->userBox($basket, $requests), $label);

			if ($basket['fs_id'] == $this->session->id() && $requests) {
				$label = $this->translator->trans('basket.requests', ['{count}' => count($requests)]);
				$page->addSectionRight($this->requests($requests), $label);
			}

			if ($basket['lat'] != 0 || $basket['lon'] != 0) {
				$map = new vMap([$basket['lat'], $basket['lon']]);
				$map->addMarker($basket['lat'], $basket['lon']);

				$map->setDefaultMarkerOptions('shopping-basket', 'green');

				$map->setCenter($basket['lat'], $basket['lon']);

				$page->addSectionRight($map->render(), $this->translator->trans('basket.where'));
			}
		} else {
			$page->addSection(
				$this->v_utils->v_info(
					$this->translator->trans('basket.login'),
					$this->translator->trans('notice')
				),
				false,
				['wrapper' => false]
			);
		}

		$page->render();
	}

	public function basketTaken(array $basket): void
	{
		$label = $this->translator->trans('terminology.basket') . ' #' . $basket['id'];
		$page = new vPage($label,
			'<div>
				<p>' . $this->translator->trans('basket.taken') . '</p>
			</div>');
		$page->render();
	}

	public function requests(array $requests): string
	{
		$out = '<ul class="linklist conversation-list">';

		foreach ($requests as $r) {
			$img = $this->imageService->img($r['fs_photo']);
			$out .= '<li><a onclick="chat(' . (int)$r['fs_id'] . '); return false;" href="#">'
				. '<span class="pics"><img width="50" alt="avatar" src="' . $img . '"></span>'
				. '<span class="names">' . $r['fs_name'] . '</span>'
				. '<span class="msg"></span>'
				. '<span class="time">' . $this->timeHelper->niceDate($r['time_ts']) . '</span>'
				. '<span class="clear"></span>
			</a></li>';
		}

		return $out . '</ul>';
	}

	private function getSubtitle(array $basket): string
	{
		$created = $this->timeHelper->niceDate($basket['time_ts']);
		$expires = $this->timeHelper->niceDate($basket['until_ts']);

		$subtitle = '<p>' . $this->translator->trans('basket.created', ['{date}' => $created]) . '</p>';
		$subtitle .= '<p>' . $this->translator->trans('basket.expires', ['{date}' => $expires]) . '</p>';

		if ($basket['update_ts']) {
			$updated = $this->timeHelper->niceDate($basket['update_ts']);
			$subtitle .= '<p>' . $this->translator->trans('basket.updated', ['{date}' => $updated]) . '</p>';
		}

		return $subtitle;
	}

	private function userBox(array $basket, array $requests): string
	{
		$request = '';

		if ($this->basketPermissions->mayRequest($basket['fs_id'])) {
			$hasRequested = $requests && count($requests) > 0;

			if (!empty($basket['contact_type'])) {
				$contact_type = explode(':', $basket['contact_type']);
			} else {
				$contact_type = [];
			}
			$allowContactByMessage = in_array(1, $contact_type);
			$allowContactByPhone = in_array(2, $contact_type);

			$request = $this->vueComponent('vue-BasketRequestForm', 'request-form', [
				'basketId' => $basket['id'],
				'basketCreatorId' => $basket['foodsaver_id'],
				'initialHasRequested' => $hasRequested,
				'initialRequestCount' => $basket['request_count'],
				'mobileNumber' => ($allowContactByPhone && !empty($basket['handy'])) ? $basket['handy'] : null,
				'landlineNumber' => ($allowContactByPhone && !empty($basket['tel'])) ? $basket['tel'] : null,
				'allowRequestByMessage' => $allowContactByMessage
			]);
		}
		if ($this->basketPermissions->mayEdit($basket['fs_id'])) {
			$request = '
				<div class="ui-padding-bottom">
					<a class="button button-big" href="#" onclick="ajreq(\'editBasket\','
					. '{app:\'basket\''
					. ',id:' . (int)$basket['id']
					. '});">' . $this->translator->trans('basket.edit') . '
					</a>
				</div>';
		}
		if ($this->basketPermissions->mayDelete($basket)) {
			$request = $request . '

				<div>
					<a class="button button-big" href="#" onclick="tryRemoveBasket(' . (int)$basket['id'] . ');">'
					. $this->translator->trans('basket.delete') . '
					</a>
				</div>';
		}

		$basketUser = [
			'id' => $basket['fs_id'],
			'name' => $basket['fs_name'],
			'photo' => $basket['fs_photo'],
			'sleep_status' => $basket['sleep_status'],
		];

		return $this->fsAvatarList([$basketUser], 600) . $request;
	}

	private function pageImg(string $img): string
	{
		$img = ($img == '') ? '/img/foodloob.gif' : '/images/basket/medium-' . $img;

		return '<img class="basket-img" src="' . $img . '" />';
	}

	public function basketForm(array $foodsaver): string
	{
		$out = '';

		$out .= $this->v_utils->v_form_textarea('description', ['maxlength' => 1705]);

		$values = [
			['id' => 0.25, 'name' => '250 g'],
			['id' => 0.5, 'name' => '500 g'],
			['id' => 0.75, 'name' => '750 g'],
		];

		$kgValues = array_merge(range(1, 9), range(10, 100, 10));
		foreach ($kgValues as $i) {
			$values[] = [
				'id' => $i,
				'name' => $i . '<span style="white-space:nowrap">&thinsp;</span>kg',
			];
		}

		$out .= $this->v_utils->v_form_select('weight', [
			'values' => $values,
			'selected' => 3,
		]);

		$out .= $this->v_utils->v_form_checkbox('contact_type', [
			'values' => [
				['id' => 1, 'name' => $this->translator->trans('basket.contact.write')],
				['id' => 2, 'name' => $this->translator->trans('basket.contact.call')],
			],
			'checked' => [1],
		]);

		$out .= $this->v_utils->v_form_text('tel', ['value' => $foodsaver['telefon']]);
		$out .= $this->v_utils->v_form_text('handy', ['value' => $foodsaver['handy']]);

		$out .= $this->v_utils->v_form_select('lifetime', [
			'values' => [
				['id' => 1, 'name' => $this->translator->trans('basket.valid.1')],
				['id' => 2, 'name' => $this->translator->trans('basket.valid.2')],
				['id' => 3, 'name' => $this->translator->trans('basket.valid.3')],
				['id' => 7, 'name' => $this->translator->trans('basket.valid.7')],
				['id' => 14, 'name' => $this->translator->trans('basket.valid.14')],
				['id' => 21, 'name' => $this->translator->trans('basket.valid.21')],
			],
			'selected' => 7,
		]);

		$out .= $this->v_utils->v_form_checkbox('food_type', [
			'values' => [
				['id' => 1, 'name' => $this->translator->trans('basket.has.bread')],
				['id' => 2, 'name' => $this->translator->trans('basket.has.greens')],
				['id' => 3, 'name' => $this->translator->trans('basket.has.dairy')],
				['id' => 4, 'name' => $this->translator->trans('basket.has.dry')],
				['id' => 5, 'name' => $this->translator->trans('basket.has.frozen')],
				['id' => 6, 'name' => $this->translator->trans('basket.has.prepared')],
				['id' => 7, 'name' => $this->translator->trans('basket.has.pet')],
			],
		]);

		return $out . $this->v_utils->v_form_checkbox('food_art', [
			'values' => [
				['id' => 1, 'name' => $this->translator->trans('basket.is.organic')],
				['id' => 2, 'name' => $this->translator->trans('basket.is.veggie')],
				['id' => 3, 'name' => $this->translator->trans('basket.is.vegan')],
				['id' => 4, 'name' => $this->translator->trans('basket.is.gf')],
			],
		]);
	}

	public function basketEditForm(array $basket): string
	{
		$out = '';

		$out .= $this->v_utils->v_form_textarea('description', ['maxlength' => 1705, 'value' => $basket['description']]);

		return $out . $this->v_utils->v_form_hidden('basket_id', $basket['id']);
	}

	public function fsBubble(array $basket): string
	{
		$img = '';
		if (!empty($basket['picture'])) {
			$img = '<div style="width: 100%; max-height: 200px; overflow: hidden;">
				<img src="http://media.myfoodsharing.org/de/items/200/' . $basket['picture'] . '" />
			</div>';
		}

		return $img . $this->v_utils->v_input_wrapper(
			$this->translator->trans('basket.description'),
			nl2br($this->routeHelper->autolink($basket['description']))
		) . '
		<div style="text-align: center;">
			<a class="fsbutton" href="' . BASE_URL . '/essenskoerbe/' . $basket['fsf_id'] . '" target="_blank">'
			. $this->translator->trans('basket.request-fs') .
			'</a>
		</div>';
	}

	public function bubbleNoUser(array $basket): string
	{
		$img = '';
		if (!empty($basket['picture'])) {
			$img = '<div style="width: 100%; overflow: hidden;">
				<img src="/images/basket/medium-' . $basket['picture'] . '" width="100%" />
			</div>';
		}

		return $img . $this->v_utils->v_input_wrapper(
			$this->translator->trans('basket.description'),
			nl2br($this->routeHelper->autolink($basket['description']))
		);
	}

	public function bubble(array $basket): string
	{
		$img = '';
		if (!empty($basket['picture'])) {
			$img = '<div style="width: 100%; overflow: hidden;">
				<img src="/images/basket/medium-' . $basket['picture'] . '" width="100%" />
			</div>';
		}

		return $img . $this->v_utils->v_input_wrapper(
			$this->translator->trans('basket.date'),
			$this->timeHelper->niceDate($basket['time_ts'])
		) . $this->v_utils->v_input_wrapper(
			$this->translator->trans('basket.description'),
			nl2br($this->routeHelper->autolink($basket['description']))
		);
	}
}
