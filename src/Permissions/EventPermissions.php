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

	public function mayEditEvent(array $event): bool
	{
		if ($this->session->isOrgaTeam()) {
			return true;
		}
		if ($this->session->isAdminFor($event['bezirk_id'])) {
			return true;
		}

		return $event['fs_id'] == $this->session->id();
	}

	public function maySeeEvent(array $event): bool
	{
		return $this->session->mayBezirk($event['bezirk_id']) || isset($event['invites']['may'][$this->session->id()]) || $event['public'] == 1;
	}

	public function mayJoinEvent(array $event): bool
	{
		return $this->maySeeEvent($event);
	}

	public function mayCommentInEvent(array $event): bool
	{
		return $this->maySeeEvent($event);
	}
}
