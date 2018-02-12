<?php

namespace Foodsharing\Lib\View;

class vAccordion
{
	private $panels;
	private $id;
	private $options;
	private $g_func;

	public function __construct($option = array())
	{
		global $g_func;
		$this->func = $g_func;
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
