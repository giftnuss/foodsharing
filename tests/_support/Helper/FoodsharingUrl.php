<?php

namespace Helper;

use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class FoodsharingUrl extends \Codeception\Module\Db
{
	public function storeUrl($storeId)
	{
		return '/?page=fsbetrieb&id=' . (int)$storeId;
	}

	public function storeEditUrl($storeId)
	{
		return '/?page=betrieb&id=' . (int)$storeId . '&a=edit';
	}

	public function storeListUrl($storeId)
	{
		return '/?page=betrieb&bid=' . (int)$storeId;
	}

	public function storeNewUrl()
	{
		return '/?page=betrieb&&a=new';
	}

	public function groupEditUrl($groupId)
	{
		return '/?page=groups&sub=edit&id=' . (int)$groupId;
	}

	public function groupListUrl()
	{
		return '/?page=groups';
	}

	public function forumThemeUrl($id, $regionId = null)
	{
		if (!isset($regionId)) {
			$regionId = $this->grabFromDatabase('fs_bezirk_has_theme', 'bezirk_id', ['theme_id' => $id]);
		}

		return '/?page=bezirk&bid=' . (int)$regionId . '&sub=forum&tid=' . (int)$id;
	}

	public function forumUrl($id, $botforum = false)
	{
		$sub = $botforum ? 'botforum' : 'forum';

		return '/?page=bezirk&bid=' . (int)$id . '&sub=' . $sub;
	}

	public function regionWallUrl($id)
	{
		return '/?page=bezirk&bid=' . (int)$id . '&sub=wall';
	}

	public function fairTeilerRegionListUrl($region_id)
	{
		return '/?page=fairteiler&bid=' . (int)$region_id;
	}

	public function fairTeilerGetUrlShort($fairteiler_id)
	{
		return '/fairteiler/' . (int)$fairteiler_id;
	}

	public function fairTeilerGetUrl($fairteiler_id)
	{
		return '/?page=fairteiler&sub=ft&id=' . (int)$fairteiler_id;
	}

	public function fairTeilerEditUrl($fairteiler_id)
	{
		return '/?page=fairteiler&sub=ft&id=' . (int)$fairteiler_id . '&sub=edit';
	}

	public function foodBasketInfoUrl($basket_id)
	{
		return '/essenskoerbe/' . (int)$basket_id;
	}

	public function settingsUrl()
	{
		return '/?page=settings&sub=general';
	}

	public function eventAddUrl($regionId)
	{
		return '/?page=event&sub=add&bid=' . (int)$regionId;
	}

	public function apiReportListForRegion($regionId)
	{
		return 'api/report/region/' . (int)$regionId;
	}

	public function upgradeQuizUrl(int $quizRole): string
	{
		$result = '/?page=settings&sub=upgrade/up_';
		switch ($quizRole) {
			case Role::STORE_MANAGER:
				return $result . 'bip';
			case Role::AMBASSADOR:
				return $result . 'bot';
			default:
				return $result . 'fs';
		}
	}
}
