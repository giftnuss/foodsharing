<?php

namespace Foodsharing\Lib\View;

use Foodsharing\Helpers\PageHelper;

class vPage
{
	private $title;
	private $content;
	private $sections;
	private $sections_left;
	private $sections_right;
	private $subtitle;
	private $bread;
	/**
	 * @var PageHelper
	 */
	private $pageHelper;

	public function __construct($title, $content)
	{
		global $container;
		$this->setTitle($title);
		$this->setContent($content);

		$this->sections = array();
		$this->sections_left = array();
		$this->sections_right = array();
		$this->subtitle = false;
		$this->bread = array($title, false);
		$this->pageHelper = $container->get(PageHelper::class);
	}

	public function setTitle($title)
	{
		$this->title = $title;
	}

	public function setBread($name, $url = false)
	{
		$this->bread = array($name, $url);
	}

	public function setSubTitle($subtitle)
	{
		$this->subtitle = $subtitle;
	}

	public function setContent($html)
	{
		$this->content = $html;
	}

	public function addSection($html, $title = false, $option = array())
	{
		$defOpt = array(
			'wrapper' => true
		);
		$option = array_merge($defOpt, $option);

		$this->sections[] = array(
			'cnt' => $html,
			'title' => $title,
			'option' => $option
		);
	}

	public function addSectionRight($html, $title = false, $option = array())
	{
		$defOpt = array(
			'wrapper' => true
		);
		$option = array_merge($defOpt, $option);

		$this->sections_right[] = array(
			'cnt' => $html,
			'title' => $title,
			'option' => $option
		);
	}

	public function addSectionLeft($html, $title = false, $option = array())
	{
		$defOpt = array(
			'wrapper' => true
		);
		$option = array_merge($defOpt, $option);

		$this->sections_left[] = array(
			'cnt' => $html,
			'title' => $title,
			'option' => $option
		);
	}

	public function render()
	{
		$this->pageHelper->addBread($this->bread[0], $this->bread[1]);
		$this->pageHelper->addTitle($this->title);

		$subtitle = '';
		if ($this->subtitle !== false) {
			$subtitle = '<small>' . $this->subtitle . '</small>';
		}

		$this->pageHelper->addContent('
		<div class="page ui-padding ui-widget-content corner-all">
			<div class="h1">
				<h1>' . $this->title . '</h1>
				' . $subtitle . '
			</div>
			' . $this->content . '
		</div>');

		foreach ($this->sections as $s) {
			$title = '';
			if ($s['title'] !== false) {
				$title = '<h2>' . $s['title'] . '</h2>
				';
			}
			$this->pageHelper->addContent('
		<div class="page page-section ui-padding ui-widget-content corner-all">
			' . $title . $s['cnt'] . '
		</div>');
		}

		foreach ($this->sections_left as $key => $s) {
			$title = '';
			if ($s['title'] !== false) {
				$title = '<h3>' . $s['title'] . '</h3>
				';
			}

			$class = ' page-section';

			if ($key == 0) {
				$class = '';
			}

			if ($s['option']['wrapper']) {
				$s['cnt'] = '
			<div class="page' . $class . ' ui-padding ui-widget-content corner-all">
				' . $title . $s['cnt'] . '
			</div>';
			} else {
				$s['cnt'] = $title . $s['cnt'];
			}

			$this->pageHelper->addContent($s['cnt'], CNT_LEFT);
		}

		foreach ($this->sections_right as $i => $s) {
			$title = '';
			$class = '';

			if ($s['title'] !== false) {
				$title = '<h3>' . $s['title'] . '</h3>
				';
			}

			if ($i > 0) {
				$class = ' page-section';
			}

			if ($s['option']['wrapper']) {
				$s['cnt'] = '
			<div class="page' . $class . ' ui-padding ui-widget-content corner-all">
				' . $title . $s['cnt'] . '
			</div>';
			} else {
				$s['cnt'] = $title . $s['cnt'];
			}

			$this->pageHelper->addContent($s['cnt'], CNT_RIGHT);
		}
	}
}
