<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Helpers\TimeHelper;
use Foodsharing\Lib\Func;
use Foodsharing\Services\ForumService;

class RegionHelper
{
	private $func;
	private $forumService;
	private $timeHelper;

	public function __construct(ForumService $forumService, Func $func, TimeHelper $timeHelper)
	{
		$this->func = $func;
		$this->forumService = $forumService;
		$this->timeHelper = $timeHelper;
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
			$t['post_time'] = $this->timeHelper->niceDate($t['post_time_ts']);
			$t['url'] = $this->forumService->url($regionId, $ambassadorForum, $t['id']);

			return $t;
		};

		return array_map($processThreads, $threads);
	}
}
