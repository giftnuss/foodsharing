<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Event\EventGateway;
use Foodsharing\Modules\Region\RegionGateway;

class WallPostPermissions
{
	private $regionGateway;
	private $eventGateway;
	private $eventPermission;
	private $session;

	public function __construct(
		RegionGateway $regionGateway,
		EventGateway $eventGateway,
		EventPermissions $eventPermissions,
		Session $session
	) {
		$this->regionGateway = $regionGateway;
		$this->eventGateway = $eventGateway;
		$this->eventPermission = $eventPermissions;
		$this->session = $session;
	}

	public function mayReadWall(int $fsId, string $target, int $targetId): bool
	{
		switch ($target) {
			case 'foodsaver':
				$result = $fsId > 0;
				break;
			case 'bezirk':
				$result = $fsId && $this->regionGateway->hasMember($fsId, $targetId);
				break;
			case 'event':
				$event = $this->eventGateway->getEventWithInvites($targetId);

				$result = $this->eventPermission->mayCommentInEvent($event);
				break;
			case 'fairteiler':
				$result = true;
				break;
			case 'question':
				$result = $fsId && $this->regionGateway->hasMember($fsId, RegionIDs::QUIZ_AND_REGISTRATION_WORK_GROUP);
				break;
			case 'usernotes':
			case 'fsreport':
				$result = $fsId && ($this->regionGateway->hasMember($fsId, RegionIDs::EUROPE_REPORT_TEAM) || $this->session->isOrgaTeam());
				break;
			case 'application':
				// Uses Session::isAdminForAWorkGroup() instead of the more appropriate and specific Session::isAdminFor() since
				// there's no good way to pass the required region id at the moment
				$result = $fsId && $this->session->isAdminForAWorkGroup();
				break;
			default:
				$result = false;
				break;
		}

		return $result;
	}

	public function mayWriteWall(int $fsId, string $target, int $targetId): bool
	{
		switch ($target) {
			case 'foodsaver':
				return $fsId === $targetId;
			case 'question':
				return $fsId > 0;
			default:
				return $fsId > 0 && $this->mayReadWall($fsId, $target, $targetId);
		}
	}

	/**
	 * method describing _global_ deletion access to walls. Every author is always allowed to remove their own posts.
	 */
	public function mayDeleteFromWall(int $fsId, string $target, int $targetId): bool
	{
		switch ($target) {
			case 'bezirk':
				$result = $this->regionGateway->isAdmin($fsId, $targetId);
				break;
			case 'question':
			case 'usernotes':
			case 'fsreport':
				$result = $this->mayReadWall($fsId, $target, $targetId);
				break;
			case 'fairteiler':
				$result = $this->session->may('orga');
				break;
			default:
				$result = false;
				break;
		}

		return $result;
	}
}
