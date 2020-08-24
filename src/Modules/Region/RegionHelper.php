<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Utility\ImageHelper;
use Foodsharing\Utility\TimeHelper;

class RegionHelper
{
	private $forumService;
	private $timeHelper;
	private $imageService;

	public function __construct(
		ForumTransactions $forumTransactions,
		TimeHelper $timeHelper,
		ImageHelper $imageService
	) {
		$this->forumService = $forumTransactions;
		$this->timeHelper = $timeHelper;
		$this->imageService = $imageService;
	}

	public function transformThreadViewData($threads, $regionId, $ambassadorForum)
	{
		$processThreads = function ($t) use ($regionId, $ambassadorForum) {
			$t['userId'] = $t['foodsaver_id'];
			$t['avatar'] = [
				'sleepStatus' => $t['sleep_status'],
				'imageUrl' => $this->imageService->img($t['foodsaver_photo'], 'mini', 'q'),
			];
			$t['post_time'] = $this->timeHelper->niceDate($t['post_time_ts']);
			$t['url'] = $this->forumService->url($regionId, $ambassadorForum, $t['id']);

			return $t;
		};

		return array_map($processThreads, $threads);
	}
}
