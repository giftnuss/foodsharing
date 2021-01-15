<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Group\GroupFunctionGateway;
use Foodsharing\Modules\Region\ForumGateway;
use Foodsharing\Modules\Region\RegionGateway;

class ForumPermissions
{
	private ForumGateway $forumGateway;
	private Session $session;
	private RegionGateway $regionGateway;
	private GroupFunctionGateway $groupFunctionGateway;

	public function __construct(
		ForumGateway $forumGateway,
		RegionGateway $regionGateway,
		Session $session,
		GroupFunctionGateway $groupFunctionGateway
	) {
		$this->forumGateway = $forumGateway;
		$this->regionGateway = $regionGateway;
		$this->session = $session;
		$this->groupFunctionGateway = $groupFunctionGateway;
	}

	public function mayStartUnmoderatedThread(array $region, $ambassadorForum): bool
	{
		if (!$this->session->user('verified')) {
			return false;
		}
		$regionId = $region['id'];

		if ($ambassadorForum) {
			return $this->mayPostToRegion($regionId, $ambassadorForum);
		}

		$moderationGroup = $this->groupFunctionGateway->getRegionFunctionGroupId($regionId, WorkgroupFunction::MODERATION);

		if (empty($moderationGroup)) {
			if ($this->session->isAmbassadorForRegion($regionId)) {
				return true;
			}
		} elseif ($this->session->isAdminFor($moderationGroup)) {
			return true;
		}

		return !$region['moderated'];
	}

	public function mayPostToRegion(int $regionId, $ambassadorForum): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}

		if ($ambassadorForum && !$this->session->isAdminFor($regionId)) {
			return false;
		}
		if (!in_array($regionId, $this->session->listRegionIDs())) {
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

	public function mayPostToThread(int $threadId): bool
	{
		if ($this->session->may('orga')) {
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

	public function mayModerate(int $threadId): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}
		$forums = $this->forumGateway->getForumsForThread($threadId);

		foreach ($forums as $forum) {
			$moderationGroup = $this->groupFunctionGateway->getRegionFunctionGroupId($forum['forumId'], WorkgroupFunction::MODERATION);
			if (empty($moderationGroup)) {
				if ($this->session->isAdminFor($forum['forumId'])) {
					return true;
				}
			} elseif ($this->session->isAdminFor($moderationGroup)) {
				return true;
			}
		}

		return false;
	}

	public function mayAccessThread(int $threadId): bool
	{
		return $this->mayPostToThread($threadId);
	}

	public function mayAccessAmbassadorBoard(int $regionId): bool
	{
		return $this->mayPostToRegion($regionId, true);
	}

	public function mayChangeStickiness(int $regionId): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}

		$moderationGroup = $this->groupFunctionGateway->getRegionFunctionGroupId($regionId, WorkgroupFunction::MODERATION);

		if (empty($moderationGroup)) {
			if ($this->session->isAdminFor($regionId)) {
				return true;
			}
		} elseif ($this->session->isAdminFor($moderationGroup)) {
			return true;
		}

		return false;
	}

	public function mayDeletePost(array $post): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}
		if ($post['author_id'] == $this->session->id()) {
			return true;
		}

		return false;
	}

	public function mayDeleteThread(array $thread): bool
	{
		return !$thread['active'] && $this->mayModerate($thread['id']);
	}
}
