<?php

namespace Foodsharing\Helpers;

final class RouteHelper
{
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
}
