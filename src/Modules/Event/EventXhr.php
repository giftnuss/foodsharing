<?php

namespace Foodsharing\Modules\Event;

use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Permissions\EventPermissions;

class EventXhr extends Control
{
	private ?array $event;
	private XhrResponses $responses;
	private EventGateway $gateway;
	private EventPermissions $eventPermissions;
	private array $responseOptions;

	public function __construct(
		EventGateway $gateway,
		EventPermissions $eventPermissions
	) {
		$this->gateway = $gateway;
		$this->responses = new XhrResponses();
		$this->eventPermissions = $eventPermissions;

		parent::__construct();

		$this->responseOptions = [
			InvitationStatus::ACCEPTED => 'pulseSuccess("' . $this->translator->trans('events.rsvp.yes') . '");',
			InvitationStatus::MAYBE => 'pulseSuccess("' . $this->translator->trans('events.rsvp.maybe') . '");',
			InvitationStatus::WONT_JOIN => 'pulseInfo("' . $this->translator->trans('events.rsvp.no') . '");',
		];

		if (isset($_GET['id'])) {
			$this->event = $this->gateway->getEvent($_GET['id'], true);
		}
	}

	public function eventresponse()
	{
		if ($this->event === null) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if (!$this->eventPermissions->mayJoinEvent($this->event)) {
			return XhrResponses::PERMISSION_DENIED;
		}

		$newStatus = (int)$_GET['s'];
		if (!InvitationStatus::isValidStatus($newStatus)) {
			return $this->responses->fail_generic();
		}
		if (!$this->gateway->setInviteStatus($_GET['id'], [$this->session->id()], $_GET['s'])) {
			return $this->responses->fail_generic();
		}

		$responseScript = $this->responseOptions[$newStatus];

		return [
			'status' => 1,
			'script' => $responseScript,
		];
	}
}
