<?php

namespace Foodsharing\Helpers;

final class RouteHelper
{
	private $translationHelper;

	public function __construct(TranslationHelper $translationHelper)
	{
		$this->translationHelper = $translationHelper;
	}

	public function go(string $url): void
	{
		header('Location: ' . $url);
		exit();
	}

	public function goSelf(): void
	{
		$this->go($this->getSelf());
	}

	public function goLogin(): void
	{
		$this->go('/?page=login&ref=' . urlencode($_SERVER['REQUEST_URI']));
	}

	public function goPage($page = false): void
	{
		if (!$page) {
			$page = $this->getPage();
			if (isset($_GET['bid'])) {
				$page .= '&bid=' . (int)$_GET['bid'];
			}
		}
		$this->go('/?page=' . $page);
	}

	public function getSelf()
	{
		return $_SERVER['REQUEST_URI'];
	}

	public function getPage()
	{
		$page = $this->getGet('page');
		if (!$page) {
			$page = 'index';
		}

		return $page;
	}

	public function getSubPage()
	{
		$sub_page = $this->getGet('sub');
		if (!$sub_page) {
			$sub_page = 'index';
		}

		return $sub_page;
	}

	private function getGet(string $name)
	{
		return $_GET[$name] ?? false;
	}

	public function pageLink($page, $id, $action = '')
	{
		if (!empty($action)) {
			$action = '&a=' . $action;
		}

		return array('href' => '/?page=' . $page . $action, 'name' => $this->translationHelper->s($id));
	}

	public function autolink(string $str, array $attributes = array())
	{
		$attributes['target'] = '_blank';
		$attrs = '';
		foreach ($attributes as $attribute => $value) {
			$attrs .= " {$attribute}=\"{$value}\"";
		}
		$str = ' ' . $str;
		$str = preg_replace(
			'`([^"=\'>])(((http|https|ftp)://|www.)[^\s<]+[^\s<\.)])`i',
			'$1<a href="$2"' . $attrs . '>$2</a>',
			$str
		);
		$str = substr($str, 1);
		// adds http:// if not existing
		return preg_replace('`href=\"www`', 'href="http://www', $str);
	}
}
