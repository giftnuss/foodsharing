<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Region\ForumGateway;
use Foodsharing\Modules\Region\RegionGateway;

class ForumPermissions
{
	private $forumGateway;
	private $regionGateway;
	private $session;

	public function __construct(ForumGateway $forumGateway, RegionGateway $regionGateway, Session $session)
	{
		$this->forumGateway = $forumGateway;
		$this->regionGateway = $regionGateway;
		$this->session = $session;
	}

	public function mayPostToRegion($regionId, $ambassadorForum)
	{
		if ($this->session->isOrgaTeam()) {
			return true;
		}
		if (!in_array($regionId, $this->session->getRegionIds())) {
			return false;
		}
		if ($ambassadorForum && !$this->session->isAdminFor($regionId)) {
			return false;
		}

		return true;
	}

	public function mayPostToThread($threadId)
	{
		$threadStatus = $this->forumGateway->getBotThreadStatus($threadId);

		return $this->mayPostToRegion($threadStatus['bezirk_id'], $threadStatus['bot_theme']);
	}

	public function mayAccessThread($threadId)
	{
		return $this->mayPostToThread($threadId);
	}

	public function mayAccessAmbassadorBoard($regionId)
	{
		return $this->mayPostToRegion($regionId, 1);
	}

	public function mayAccessForum($forumId, $subForumId)
	{
		if ($subForumId !== 0 && $subForumId !== 1) {
			return false;
		}

		return $this->mayPostToRegion($forumId, $subForumId);
	}

	public function mayActivateThreads($regionId)
	{
		return $this->mayPostToRegion($regionId, 1);
	}

	public function mayChangeStickyness($regionId)
	{
		return $this->mayPostToRegion($regionId, 1);
	}

	public function mayDeletePost($region, $post)
	{
		if ($this->session->isOrgaTeam()) {
			return true;
		}
		if ($post['author_id'] == $this->session->id()) {
			return true;
		}
		if ($region['type'] == Type::WORKING_GROUP && ($this->session->isAdminFor($region['id']))) {
			return true;
		}

		return false;
	}
}
