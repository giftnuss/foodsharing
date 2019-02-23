<?php

namespace Foodsharing\Lib\View;

use Foodsharing\Helpers\PageCompositionHelper;

class vAccordion
{
	private $panels;
	private $id;
	private $options;
	/**
	 * @var PageCompositionHelper
	 */
	private $pageCompositionHelper;

	public function __construct($option = array())
	{
		global $container;
		$this->pageCompositionHelper = $container->get(PageCompositionHelper::class);
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
		$this->pageCompositionHelper->addJs('$("#' . $this->id . '").accordion(' . json_encode($this->options) . ');');

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
