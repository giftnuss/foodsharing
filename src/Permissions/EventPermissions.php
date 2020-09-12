<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;

final class EventPermissions
{
	private Session $session;

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
		if ($event['public'] == 1) {
			return true;
		}
		if (isset($event['invites']['may'][$this->session->id()])) {
			return true;
		}

		return $this->session->mayBezirk($event['bezirk_id']);
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
