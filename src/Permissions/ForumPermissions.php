<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Region\ForumGateway;

class ForumPermissions
{
	private ForumGateway $forumGateway;
	private Session $session;

	public function __construct(ForumGateway $forumGateway, Session $session)
	{
		$this->forumGateway = $forumGateway;
		$this->session = $session;
	}

	public function mayStartUnmoderatedThread(array $region, $ambassadorForum): bool
	{
		if (!$this->session->user('verified')) {
			return false;
		}
		$regionId = $region['id'];
		if (!$this->mayPostToRegion($regionId, $ambassadorForum)) {
			return false;
		}
		if ($this->session->isAmbassadorForRegion([$regionId])) {
			return true;
		}

		return !$region['moderated'];
	}

	public function mayPostToRegion(int $regionId, $ambassadorForum): bool
	{
		if ($this->session->isOrgaTeam()) {
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

	public function mayModerate(int $threadId): bool
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

	public function mayAccessThread(int $threadId): bool
	{
		return $this->mayPostToThread($threadId);
	}

	public function mayAccessAmbassadorBoard(int $regionId): bool
	{
		return $this->mayPostToRegion($regionId, true);
	}

	public function mayActivateThreads(int $regionId): bool
	{
		return $this->mayPostToRegion($regionId, true);
	}

	public function mayChangeStickiness(int $regionId): bool
	{
		return $this->mayPostToRegion($regionId, true);
	}

	public function mayDeletePost(array $post): bool
	{
		if ($this->session->isOrgaTeam()) {
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
