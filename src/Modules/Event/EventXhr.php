<?php

namespace Foodsharing\Modules\Event;

use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Lib\Xhr\XhrResponses;

class EventXhr extends Control
{
	private $stats;
	private $event;
	private $gateway;
	private $responses;

	public function __construct(EventGateway $gateway)
	{
		$this->gateway = $gateway;
		$this->responses = new XhrResponses();

		parent::__construct();

		if (isset($_GET['id'])) {
			$this->event = $this->gateway->getEventWithInvites($_GET['id']);
		}

		$this->stats = [
			0 => true, // invited
			1 => true, // will join
			2 => true, // might join
			3 => true  // will not join (but has been invited)
		];
	}

	public function accept()
	{
		if (!$this->maySeeEvent()) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if ($this->gateway->setInviteStatus($_GET['id'], $this->session->id(), 1)) {
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
		if (!$this->maySeeEvent()) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if ($this->gateway->setInviteStatus($_GET['id'], $this->session->id(), 2)) {
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
		if (!$this->maySeeEvent()) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if ($this->gateway->setInviteStatus($_GET['id'], $this->session->id(), 3)) {
			return array(
				'status' => 1,
				'script' => 'pulseInfo("Einladung gelöscht.");'
			);
		}

		return $this->responses->fail_generic();
	}

	public function ustat()
	{
		if (!$this->maySeeEvent()) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if (isset($this->stats[(int)$_GET['s']]) && $this->gateway->setInviteStatus(
				$_GET['id'],
				$this->session->id(),
				$_GET['s']
			)) {
			return array(
					'status' => 1,
					'script' => 'pulseInfo("Einladungsstatus geändert!");'
				);
		}

		return $this->responses->fail_generic();
	}

	public function ustatadd()
	{
		if (!$this->maySeeEvent()) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if (isset($this->stats[(int)$_GET['s']]) && $this->gateway->addInviteStatus(
				$_GET['id'],
				$this->session->id(),
				$_GET['s']
			)) {
			return array(
					'status' => 1,
					'script' => 'pulseInfo("Status geändert!");'
				);
		}

		return $this->responses->fail_generic();
	}

	private function maySeeEvent(): bool
	{
		if (!$this->event) {
			return false;
		}

		return $this->event['public'] == 1 || $this->session->may('orga') || $this->session->isAdminFor(
				$this->event['bezirk_id']
			) || isset($this->event['invites']['may'][$this->session->id()]);
	}
}
