<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Helpers\TimeHelper;
use Foodsharing\Services\ForumService;
use Foodsharing\Services\ImageService;

class RegionHelper
{
	private $forumService;
	private $timeHelper;
	private $imageService;

	public function __construct(ForumService $forumService, TimeHelper $timeHelper, ImageService $imageService)
	{
		$this->forumService = $forumService;
		$this->timeHelper = $timeHelper;
		$this->imageService = $imageService;
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
				'imageUrl' => $this->imageService->img($t['foodsaver_photo'], 'mini', 'q')
			];
			$t['post_time'] = $this->timeHelper->niceDate($t['post_time_ts']);
			$t['url'] = $this->forumService->url($regionId, $ambassadorForum, $t['id']);

			return $t;
		};

		return array_map($processThreads, $threads);
	}
}
