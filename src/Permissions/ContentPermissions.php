<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Content\ContentId;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;

final class ContentPermissions
{
	private $session;

	private $PR_PARTNER_AND_TEAM_CONTENT_IDS = ['id' => [ContentId::PARTNER_PAGE_10,
														 ContentId::TEAM_HEADER_PAGE_39,
														 ContentId::TEAM_ACTIVE_PAGE_53,
														 ContentId::TEAM_FORMER_ACTIVE_PAGE_54
														]];

	private $QUIZ_CONTENT_IDS = ['id' => [ContentId::QUIZ_DESCRIPTION_PAGE_12,
											ContentId::QUIZ_FAILED_PAGE_13,
											ContentId::QUIZ_CONFIRM_FS_PAGE_14,
											ContentId::QUIZ_CONFIRM_SM_PAGE_15,
											ContentId::QUIZ_CONFIRM_AMB_PAGE_16,
											ContentId::QUIZ_START_PAGE_17,
											ContentId::QUIZ_POPUP_PAGE_18,
											ContentId::QUIZ_FAILED_FS_TRY_1_PAGE_19,
											ContentId::QUIZ_FAILED_FS_TRY_2_PAGE_20,
											ContentId::QUIZ_FAILED_FS_TRY_3_PAGE_21,
											ContentId::QUIZ_FAILED_SM_TRY_1_PAGE_22,
											ContentId::QUIZ_FAILED_SM_TRY_2_PAGE_23,
											ContentId::QUIZ_FAILED_SM_TRY_3_PAGE_24,
											ContentId::QUIZ_FAILED_AMB_TRY_1_PAGE_25,
											ContentId::QUIZ_FAILED_AMB_TRY_2_PAGE_26,
											ContentId::QUIZ_FAILED_AMB_TRY_3_PAGE_27,
											ContentId::QUIZ_REMARK_PAGE_33,
											ContentId::QUIZ_POPUP_SM_PAGE_34,
											ContentId::QUIZ_POPUP_AMB_PAGE_35,
											ContentId::QUIZ_POPUP_AMB_LAST_PAGE_36]];

	private $START_CONTENT_IDS = ['id' => [ContentId::START_MAIN_AU_PAGE_37,
										   ContentId::START_MAIN_DE_PAGE_38,
										   ContentId::START_BETA_PAGE_48]];

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
			return $this->QUIZ_CONTENT_IDS;
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
			return in_array($id, $this->QUIZ_CONTENT_IDS['id']);
		}
		if ($this->session->isAdminFor(RegionIDs::PR_PARTNER_AND_TEAM_WORK_GROUP)) {
			return in_array($id, $this->PR_PARTNER_AND_TEAM_CONTENT_IDS['id']);
		}
		if ($this->session->isAdminFor(RegionIDs::PR_START_PAGE)) {
			return in_array($id, $this->START_CONTENT_IDS['id']);
		}
	}

	public function mayCreateContent()
	{
		return $this->session->may('orga');
	}
}
