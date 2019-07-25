<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;

final class EventPermissions
{
	private $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function maySeeEvent(array $event): bool
	{
		if (!isset($event)) {
			return false;
		}

		return $this->session->mayBezirk($event['bezirk_id']) || isset($event['invites']['may'][$this->session->id()]) || $event['public'] == 1;
	}

	public function mayJoinEvent(array $event): bool
	{
		return $this->maySeeEvent($event);
	}

	public function mayEditEvent(array $event): bool
	{
		return $event['fs_id'] == $this->session->id() || $this->session->isAdminFor(
				$event['bezirk_id']
			) || $this->session->isOrgaTeam();
	}

	public function mayCommentInEvent(array $event): bool
	{
		return $this->maySeeEvent($event);
	}
}
