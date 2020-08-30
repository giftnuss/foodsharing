<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;

final class ContentPermissions
{
	private $session;

	private $PR_PARTNER_AND_TEAM_CONTENT_IDS = ['id' => [10, 39, 54, 53]];
	private $QUIZ_PAGES = ['id' => [12, 13, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 33, 35, 36]];
	private $START_PAGE = ['id' => [38, 48]];

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function mayEditContent()
	{
		return $this->session->may('orga')
			|| $this->session->isAdminFor(RegionIDs::QUIZ_AND_REGISTRATION_WORK_GROUP)
			|| $this->session->isAdminFor(RegionIDs::PR_PARTNER_AND_TEAM_WORK_GROUP)
			|| $this->session->isAdminFor(RegionIDs::PR_START_PAGE);
	}

	public function mayEditContentListIDs()
	{
		if ($this->session->may('orga')) {
			return [];
		}

		if ($this->session->isAdminFor(RegionIDs::QUIZ_AND_REGISTRATION_WORK_GROUP)) {
			return $this->QUIZ_PAGES;
		}
		if ($this->session->isAdminFor(RegionIDs::PR_PARTNER_AND_TEAM_WORK_GROUP)) {
			return $this->PR_PARTNER_AND_TEAM_CONTENT_IDS;
		}
		if ($this->session->isAdminFor(RegionIDs::PR_START_PAGE)) {
			return $this->START_PAGE;
		}
	}

	public function mayEditContentId($id)
	{
		if ($this->session->may('orga')) {
			return true;
		}
		if ($this->session->isAdminFor(RegionIDs::QUIZ_AND_REGISTRATION_WORK_GROUP)) {
			return in_array($id, $this->QUIZ_PAGES['id']);
		}
	}

	public function mayCreateContent()
	{
		return $this->session->may('orga');
	}
}
