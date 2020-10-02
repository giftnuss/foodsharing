<?php

namespace Foodsharing\Modules\Core;

use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Foodsharing\Utility\ImageHelper;
use Foodsharing\Utility\PageHelper;
use Foodsharing\Utility\RouteHelper;
use Foodsharing\Utility\Sanitizer;
use Foodsharing\Utility\TimeHelper;
use Foodsharing\Utility\TranslationHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class View
{
	private $sub;

	/* @var \Foodsharing\Lib\Session */
	protected $session;
	/* @var \Foodsharing\Lib\View\Utils */
	protected $v_utils;

	/* @var \Twig\Environment */
	public $twig;

	protected $dataHelper;
	protected $identificationHelper;
	protected $imageService;
	protected $pageHelper;
	protected $routeHelper;
	protected $sanitizerService;
	protected $timeHelper;
	protected $translationHelper;
	protected $translator;

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
		TranslatorInterface $translator
	) {
		$this->twig = $twig;
		$this->session = $session;
		$this->v_utils = $viewUtils;
		$this->dataHelper = $dataHelper;
		$this->identificationHelper = $identificationHelper;
		$this->imageService = $imageService;
		$this->pageHelper = $pageHelper;
		$this->routeHelper = $routeHelper;
		$this->sanitizerService = $sanitizerService;
		$this->timeHelper = $timeHelper;
		$this->translationHelper = $translationHelper;
		$this->translator = $translator;
	}

	public function setSub($sub)
	{
		$this->sub = $sub;
	}

	public function topbar($title, $subtitle = '', $icon = '')
	{
		if ($icon != '') {
			$icon = '<div class="img">' . $icon . '</div>';
		}

		if ($subtitle != '') {
			$subtitle = '<p>' . $subtitle . '</p>';
		}

		return '
		<div class="content-top corner-all">
			' . $icon . '
			<h3>' . $title . '</h3>
			' . $subtitle . '
			<div style="clear:both;"></div>
		</div>';
	}

	public function distance($distance)
	{
		$distance = round($distance, 1);

		if ($distance == 1.0) {
			$distance = '1 km';
		} elseif ($distance < 1) {
			$distance = ($distance * 1000) . ' m';
		} else {
			$distance = number_format($distance, 1, ',', '.') . ' km';
		}

		return $distance;
	}

	public function location($location)
	{
		$out = $this->v_utils->v_field('
		<p>' . $location['name'] . '</p>
		<p>
			' . $location['street'] . '<br />
			' . $location['zip'] . ' ' . $location['city'] . '
		</p>

		', 'Ort', ['class' => 'ui-padding']);

		return $out;
	}

	public function headline($title, $img = '', $click = false)
	{
		if ($click !== false) {
			$click = ' onclick="' . $click . 'return false;"';
		}
		if (!empty($img)) {
			$img = '
			<div class="welcome_profile_image">
				<a href="#"' . $click . '>
					<img width="50" height="50" src="' . $img . '" alt="Raphael" class="image_online">
				</a>
			</div>';
		}

		return '

		<div class="welcome ui-padding margin-bottom ui-corner-all">
			' . $img . '
			<div class="welcome_profile_name">
				<div class="user_display_name">
					' . $title . '
				</div>
			</div>
		</div>';
	}

	public function fsAvatarList($foodsaver, $option = [])
	{
		if (!is_array($foodsaver)) {
			return '';
		}
		$useScroller = $option['scroller'] ?? true;
		$maxHeight = $option['height'] ?? 185;

		$id = $this->identificationHelper->id('team');
		if (isset($option['id'])) {
			$id = $option['id'];
		}

		$out = '
		<div>
			<ul id="' . $id . '" class="linklist">';
		if (!isset($option['noshuffle'])) {
			shuffle($foodsaver);
		}
		foreach ($foodsaver as $fs) {
			$photo = $this->imageService->avatar($fs);

			$click = ' onclick="profile(' . (int)$fs['id'] . ');return false;"';

			$href = '#';
			if (isset($fs['href'])) {
				$click = '';
				$href = $fs['href'];
			}

			$out .= '
				<li>
					<a href="' . $href . '"' . $click . ' class="ui-corner-all">
						<span style="float:left;margin-right:7px;">' . $photo . '</span>
						<span class="title">' . $fs['name'] . '</span>
						<span style="clear:both;"></span>
					</a>
				</li>';
		}
		$out .= '
			</ul>
			<div style="clear:both"></div>
		</div>';

		if ($useScroller) {
			$out = $this->v_scroller($out, $maxHeight);
		}

		return $out;
	}

	public function menu($items, $option = [])
	{
		$title = false;
		if (isset($option['title'])) {
			$title = $option['title'];
		}

		$active = false;
		if (isset($option['active'])) {
			$active = $option['active'];
		}

		$id = $this->identificationHelper->id('vmenu');

		$out = '';

		foreach ($items as $item) {
			if (!isset($item['href'])) {
				$item['href'] = '#';
			}

			$click = '';
			if (isset($item['click'])) {
				$click = ' onclick="' . $item['click'] . '"';
			}
			$class = '';
			if ($active !== false && (strpos($item['href'], '=' . $active) !== false)) {
				$class = 'active ';
			}

			$out .= '<li>
					<a class="' . $class . 'ui-corner-all" href="' . $item['href'] . '"' . $click . '>
						<span>' . $item['name'] . '</span>
					</a>
				</li>';
		}

		if (!$title) {
			return '
		<div class="ui-widget ui-widget-content ui-corner-all ui-padding margin-bottom">
			<ul class="linklist">
				' . $out . '
			</ul>
		</div>';
		}

		return '
		<h3 class="head ui-widget-header ui-corner-top">' . $title . '</h3>
		<div class="ui-widget ui-widget-content ui-corner-bottom ui-padding margin-bottom">
			<ul class="linklist">
				' . $out . '
			</ul>
		</div>';
	}

	public function latLonPicker($id, $options = [], $context = '')
	{
		if (!isset($options['location'])) {
			$data = $this->session->getLocation() ?? ['lat' => 0, 'lon' => 0];
		} else {
			$data['lat'] = $options['location']['lat'];
			$data['lon'] = $options['location']['lon'];
		}

		if (empty($data['lat']) || empty($data['lon'])) {
			/* set empty coordinates, javascript will take over default location */
			$data['lat'] = 0;
			$data['lon'] = 0;
		}

		// Default to blank values for these keys
		foreach (['anschrift', 'plz', 'ort'] as $key) {
			if (!isset($options[$key])) {
				$options[$key] = '';
			}
		}

		$out = $this->v_utils->v_input_wrapper(
			$this->translator->trans('addresspicker.label'),
	'<div class="lat-lon-picker">' .
			$this->v_utils->v_info(
				$this->translator->trans('addresspicker.infobox')
				. ($context ? '<hr>' . $this->translator->trans('addresspicker.infobox' . $context) : '')
			) .
		'<input placeholder="' . $this->translator->trans('addresspicker.placeholder') . '" '
			. 'type="text" value="" id="addresspicker" type="text" class="input text value ui-corner-top" />
		<div id="map" class="pickermap"></div>
	</div>');
		$out .=
			$this->v_utils->v_form_text('anschrift', ['value' => $options['anschrift'], 'required' => '1']) .
			$this->v_utils->v_form_text('plz', ['value' => $options['plz'], 'disabled' => '1', 'required' => '1']) .
			$this->v_utils->v_form_text('ort', ['value' => $options['ort'], 'disabled' => '1', 'required' => '1']) .
			$this->v_utils->v_form_text('lat', ['value' => $data['lat']]) .
			$this->v_utils->v_form_text('lon', ['value' => $data['lon']]) .
			'';

		return $out;
	}

	public function simpleContent($content)
	{
		$out = $this->v_utils->v_field($content['body'], $content['title'], ['class' => 'ui-padding']);

		return $out;
	}

	public function vueComponent($id, $component, $props = [], $data = [])
	{
		return $this->twig->render('partials/vue-wrapper.twig', [
			'id' => $id,
			'component' => $component,
			'props' => $props,
			'initialData' => $data,
		]);
	}

	/**
	 * @deprecated Use modern frontend code instead
	 */
	private function v_scroller(string $content, int $maxHeight): string
	{
		if ($this->session->isMob()) {
			return $content;
		}
		$id = $this->identificationHelper->id('scroller');
		$this->pageHelper->addJs('$("#' . $id . '").slimScroll({height: "auto"});');
		$this->pageHelper->addStyle('
			.scroller { margin: 0; max-height:' . $maxHeight . 'px; }
			.scroller .overview { left: 0; }'
		);

		return '<div id="' . $id . '" class="scroller">' . $content . '</div>';
	}
}
