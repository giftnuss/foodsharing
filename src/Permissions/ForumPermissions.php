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

	public function mayPostToRegion($regionId, $ambassadorForum): bool
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

	public function mayAccessForum($forumId, $forumSubId): bool
	{
		if ($forumSubId !== 0 && $forumSubId !== 1) {
			return false;
		}

		return $this->mayPostToRegion($forumId, $forumSubId);
	}

	public function mayPostToThread($threadId): bool
	{
		if ($this->session->isOrgaTeam()) {
			return true;
		}
		$forums = $this->forumGateway->getForumsForThread($threadId);
		foreach ($forums as $forum) {
			if ($this->mayAccessForum($forum['forumId'], $forum['forumSubId'])) {
				return true;
			}
		}

		return false;
	}

	public function mayAdministrateThread($threadId): bool
	{
		if ($this->session->isOrgaTeam()) {
			return true;
		}
		$forums = $this->forumGateway->getForumsForThread($threadId);
		foreach ($forums as $forum) {
			if ($this->mayAccessForum($forum['forumId'], 1)) {
				return true;
			}
		}

		return false;
	}

	public function mayAccessThread($threadId): bool
	{
		return $this->mayPostToThread($threadId);
	}

	public function mayAccessAmbassadorBoard($regionId): bool
	{
		return $this->mayPostToRegion($regionId, 1);
	}

	public function mayActivateThreads($regionId): bool
	{
		return $this->mayPostToRegion($regionId, 1);
	}

	public function mayChangeStickyness($regionId): bool
	{
		return $this->mayPostToRegion($regionId, 1);
	}

	public function mayDeletePost($region, $post): bool
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
