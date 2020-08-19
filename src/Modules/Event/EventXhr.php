<?php

namespace Foodsharing\Modules\Event;

use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Permissions\EventPermissions;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventXhr extends Control
{
	private $event;
	private $gateway;
	private $responses;
	private $eventPermissions;
	private $translator;
	private $responseOptions;

	public function __construct(
		EventGateway $gateway,
		EventPermissions $eventPermissions,
		TranslatorInterface $translator
	) {
		$this->gateway = $gateway;
		$this->responses = new XhrResponses();
		$this->eventPermissions = $eventPermissions;
		$this->translator = $translator;
		$this->responseOptions = [
			InvitationStatus::ACCEPTED => 'pulseSuccess("' . $this->translator->trans('events.rsvp.yes') . '");',
			InvitationStatus::MAYBE => 'pulseSuccess("' . $this->translator->trans('events.rsvp.maybe') . '");',
			InvitationStatus::WONT_JOIN => 'pulseInfo("' . $this->translator->trans('events.rsvp.no') . '");',
		];

		parent::__construct();

		if (isset($_GET['id'])) {
			$this->event = $this->gateway->getEvent($_GET['id'], true);
		}
	}

	public function eventresponse()
	{
		if (!$this->eventPermissions->mayJoinEvent($this->event)) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$newStatus = (int)$_GET['s'];
		if (!InvitationStatus::isValidStatus($newStatus)) {
			return $this->responses->fail_generic();
		}

		$responseScript = $this->responseOptions[$newStatus];

		if ($this->gateway->setInviteStatus($_GET['id'], [$this->session->id()], $_GET['s'])) {
			return [
				'status' => 1,
				'script' => $responseScript,
			];
		} else {
			return $this->responses->fail_generic();
		}
	}
}
