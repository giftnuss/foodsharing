<?php

namespace Foodsharing\Modules\Event;

use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Permissions\EventPermissions;

class EventXhr extends Control
{
	private $stats;
	private $event;
	private $gateway;
	private $responses;
	private $eventPermissions;

	public function __construct(EventGateway $gateway, EventPermissions $eventPermissions)
	{
		$this->gateway = $gateway;
		$this->responses = new XhrResponses();
		$this->eventPermissions = $eventPermissions;

		parent::__construct();

		if (isset($_GET['id'])) {
			$this->event = $this->gateway->getEventWithInvites($_GET['id']);
		}

		$this->stats = [
			InvitationStatus::INVITED => true, // invited
			InvitationStatus::ACCEPTED => true, // will join
			InvitationStatus::MAYBE => true, // might join
			InvitationStatus::WONT_JOIN => true  // will not join (but has been invited)
		];
	}

	public function accept()
	{
		if (!$this->eventPermissions->mayJoinEvent($this->event)) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if ($this->gateway->setInviteStatus($_GET['id'], $this->session->id(), InvitationStatus::ACCEPTED)) {
			$dialog = new XhrDialog();
			$dialog->setTitle('Einladung');
			$dialog->addContent($this->v_utils->v_info('Lieben Dank! Du hast die Einladung angenommen.'));
			$dialog->addButton('Zum Event', 'goTo(\'/?page=event&id=' . (int)$_GET['id'] . '\');');
			$dialog->addAbortButton();

			return $dialog->xhrout();
		}

		return $this->responses->fail_generic();
	}

	public function maybe()
	{
		if (!$this->eventPermissions->mayJoinEvent($this->event)) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if ($this->gateway->setInviteStatus($_GET['id'], $this->session->id(), InvitationStatus::MAYBE)) {
			$dialog = new XhrDialog();
			$dialog->setTitle('Einladung');
			$dialog->addContent($this->v_utils->v_info('Lieben Dank! Schön, dass Du vielleicht dabei bist.'));
			$dialog->addButton('Zum Event', 'goTo(\'/?page=event&id=' . (int)$_GET['id'] . '\');');
			$dialog->addAbortButton();

			return $dialog->xhrout();
		}

		return $this->responses->fail_generic();
	}

	public function noaccept()
	{
		if (!$this->eventPermissions->mayJoinEvent($this->event)) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if ($this->gateway->setInviteStatus($_GET['id'], $this->session->id(), InvitationStatus::WONT_JOIN)) {
			return [
				'status' => 1,
				'script' => 'pulseInfo("Einladung gelöscht.");'
			];
		}

		return $this->responses->fail_generic();
	}

	public function ustat()
	{
		if (!$this->eventPermissions->mayJoinEvent($this->event)) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if (isset($this->stats[(int)$_GET['s']]) && $this->gateway->setInviteStatus(
				$_GET['id'],
				$this->session->id(),
				$_GET['s']
			)) {
			return [
					'status' => 1,
					'script' => 'pulseInfo("Einladungsstatus geändert!");'
				];
		}

		return $this->responses->fail_generic();
	}

	public function ustatadd()
	{
		if (!$this->eventPermissions->mayJoinEvent($this->event)) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if (isset($this->stats[(int)$_GET['s']]) && $this->gateway->addInviteStatus(
				$_GET['id'],
				[$this->session->id()],
				$_GET['s']
			)) {
			return [
					'status' => 1,
					'script' => 'pulseInfo("Status geändert!");'
				];
		}

		return $this->responses->fail_generic();
	}
}
