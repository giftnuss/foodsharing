<?php

namespace Foodsharing\Modules\Index;

use Foodsharing\Modules\Core\View;

class IndexView extends View
{
	public function index($content_block1, $content_block2, $content_block3)
	{
		$params = [
				'contentBlock1' => $content_block1,
				'contentBlock2' => $content_block2,
				'contentBlock3' => $content_block3
			];

		return $this->vueComponent('index', 'Index', $params);
	}
}
