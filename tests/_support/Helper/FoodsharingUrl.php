<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class FoodsharingUrl extends \Codeception\Module\Db
{
	public function storeUrl($id)
	{
		return '/?page=fsbetrieb&id=' . (int)$id;
	}

	public function storeEditUrl($id)
	{
		return '/?page=betrieb&id=' . (int)$id . '&a=edit';
	}

	public function storeListUrl($bid)
	{
		return '/?page=betrieb&bid=' . (int)$bid;
	}

	public function storeNewUrl()
	{
		return '/?page=betrieb&&a=new';
	}

	public function groupEditUrl($id)
	{
		return '/?page=groups&sub=edit&id=' . (int)$id;
	}

	public function groupListUrl()
	{
		return '/?page=groups';
	}

	public function forumThemeUrl($id, $bezirk_id = null)
	{
		if (!isset($bezirk_id)) {
			$bezirk_id = $this->grabFromDatabase('fs_bezirk_has_theme', 'bezirk_id', ['theme_id' => $id]);
		}

		return '/?page=bezirk&bid=' . (int)$bezirk_id . '&sub=forum&tid=' . (int)$id;
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

	public function eventAddUrl($region)
	{
		return '/?page=event&sub=add&bid=' . (int)$region;
	}
}
