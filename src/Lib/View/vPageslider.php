<?php

namespace Foodsharing\Lib\View;

use Foodsharing\DI;
use Foodsharing\Lib\Func;

class vPageslider
{
	private $sections;
	private $id;
	private $defaultBgColor;

	/**
	 * @var Func
	 */
	private $func;

	public static $pageslider_count = 0;

	public function __construct()
	{
		$this->func = DI::$shared->get(Func::class);
		$this->sections = array();
		$this->defaultBgColor = '#F1E7C9';

		$this->id = 'fullpage-' . self::$pageslider_count;
		++self::$pageslider_count;

		// TODO: work out best way to handle these
		$this->func->addScript('/js/jquery.fullPage.min.js');
		$this->func->addStylesheet('/css/jquery.fullPage.css');
	}

	public function addSection($html, $option = array())
	{
		$this->sections[] = array(
			'html' => $html,
			'option' => $option
		);
	}

	public function render()
	{
		$out = '';

		$colors = [];
		$anchors = [];
		$tooltips = [];

		foreach ($this->sections as $i => $s) {
			if (isset($s['option']['color'])) {
				$colors[] = $s['option']['color'];
			} else {
				$colors[] = $this->defaultBgColor;
			}

			if (isset($s['option']['tooltip'])) {
				$tooltips[] = $s['option']['tooltip'];
			}

			if (isset($s['option']['anchor'])) {
				$anchors[] = $s['option']['anchor'];
			} else {
				$anchors[] = 'anchor-' . $i;
			}

			if (!isset($s['option']['wrapper']) || $s['option']['wrapper'] === true) {
				$s['html'] = '<div class="inner">' . $s['html'] . '</div>';
			}

			$out .= '
			<div style="visibility:hidden;" class="section " id="section' . $i . '">
				' . $s['html'] . '
			</div>';
		}

		$slider = [
			'id' => $this->id,
			'anchors' => $anchors,
			'colors' => $colors,
			'tooltips' => $tooltips,
			'sections' => $this->sections,
		];

		if (!isset($this->func->jsData['sliders'])) {
			$this->func->jsData['sliders'] = [];
		}

		$this->func->jsData['sliders'][] = $slider;

		return '
		<div id="' . $this->id . '">
			' . $out . '
		</div>';
	}
}
