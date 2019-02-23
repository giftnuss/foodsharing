<?php

namespace Foodsharing\Modules\Relogin;

use Foodsharing\Modules\Core\Control;

class ReloginControl extends Control
{
	public function index()
	{
		$this->session->refreshFromDatabase();
		if (isset($_GET['url']) && !empty($_GET['url'])) {
			$url = urldecode($_GET['url']);
			if (substr($url, 0, 4) !== 'http') {
				$this->routeHelper->go($url);
			}
		}
		$this->routeHelper->go('/?page=dashboard');
	}
}
