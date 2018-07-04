<?php

namespace Foodsharing\Lib\View;

use Foodsharing\DI;
use Foodsharing\Lib\Func;

class vAccordion
{
	private $panels;
	private $id;
	private $options;
	/**
	 * @var Func
	 */
	private $func;

	public function __construct($option = array())
	{
		$this->func = DI::$shared->get(Func::class);
		$this->panels = array();

		$this->id = 'acc-' . uniqid();
		$this->options = $option;
	}

	public function addPanel($title, $content)
	{
		$this->panels[] = array(
			'title' => $title,
			'content' => $content
		);
	}

	public function render()
	{
		$this->func->addJs('$("#' . $this->id . '").accordion(' . json_encode($this->options) . ');');

		$out = '
		<div id="' . $this->id . '">';

		foreach ($this->panels as $p) {
			$out .= '
			<h3>' . $p['title'] . '</h3>
			<div>
				' . $p['content'] . '
			</div>';
		}

		return $out . '</div>';
	}
}
