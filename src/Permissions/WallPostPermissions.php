<?php

namespace Foodsharing\Permissions;

use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Event\EventGateway;
use Foodsharing\Modules\Region\RegionGateway;

class WallPostPermissions
{
	private $regionGateway;
	private $eventGateway;
	private $eventPermission;

	public function __construct(
		RegionGateway $regionGateway,
		EventGateway $eventGateway,
		EventPermissions $eventPermissions
	) {
		$this->regionGateway = $regionGateway;
		$this->eventGateway = $eventGateway;
		$this->eventPermission = $eventPermissions;
	}

	public function mayReadWall(int $fsId, string $target, int $targetId): bool
	{
		switch ($target) {
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
				$result = $fsId && $this->regionGateway->hasMember($fsId, RegionIDs::EUROPE_REPORT_TEAM);
				break;
			default:
				$result = $fsId > 0;
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
	 *
	 * @param int $fsId
	 * @param string $target
	 * @param int $targetId
	 *
	 * @return bool
	 */
	public function mayDeleteFromWall(int $fsId, string $target, int $targetId): bool
	{
		switch ($target) {
			case 'foodsaver':
				$result = $fsId === $targetId;
				break;
			case 'bezirk':
				$result = $this->regionGateway->isAdmin($fsId, $targetId);
				break;
			case 'usernotes':
			case 'question':
			$result = $this->mayReadWall($fsId, $target, $targetId);
			break;
			default:
				$result = $fsId > 0;
				break;
		}

		return $result;
	}
}
