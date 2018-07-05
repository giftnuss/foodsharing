<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Lib\Func;
use Foodsharing\Services\ForumService;

class RegionHelper
{
	private $func;
	private $forumService;

	public function __construct(ForumService $forumService, Func $func)
	{
		$this->func = $func;
		$this->forumService = $forumService;
	}

	public function transformThreadViewData($threads, $regionId, $ambassadorForum)
	{
		$processThreads = function ($t) use ($regionId, $ambassadorForum) {
			$t['avatar'] = [
				'user' => ['id' => $t['foodsaver_id'],
					'name' => $t['foodsaver_name'],
					'sleep_status' => $t['sleep_status'],
				],
				'size' => 'mini',
				'imageUrl' => $this->func->img($t['foodsaver_photo'], 'mini', 'q')
			];
			$t['post_time'] = $this->func->niceDate($t['post_time_ts']);
			$t['url'] = $this->forumService->url($regionId, $ambassadorForum, $t['id']);

			return $t;
		};

		return array_map($processThreads, $threads);
	}
}
