<?php

namespace Foodsharing\Modules\Index;

use Foodsharing\Modules\Core\View;

class IndexView extends View
{
	public function index(string $content_block1, string $content_block2, string $content_block3, string $country)
	{
		$params = [
				'contentBlock1' => $content_block1,
				'contentBlock2' => $content_block2,
				'contentBlock3' => $content_block3,
				'country' => $country,
			];

		return $this->vueComponent('index', 'Index', $params);
	}
}
