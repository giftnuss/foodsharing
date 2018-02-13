<?php

namespace Foodsharing\Modules\Event;

use Foodsharing\Lib\Session\S;
use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Core\Control;

class EventXhr extends Control
{
	private $stats;
	private $event;

	public function __construct(EventModel $model)
	{
		$this->model = $model;

		parent::__construct();

		if (isset($_GET['id'])) {
			$this->event = $this->model->getEvent($_GET['id']);
			if (!$this->mayEvent()) {
				return false;
			}
		}

		$this->stats = array(
			0 => true, // eingeladen
			1 => true, // dabei
			2 => true, // kann vielleciht
			3 => true  // eingeladen aber abgesagt
		);
	}

	public function accept()
	{
		if ($this->model->setInviteStatus($_GET['id'], 1)) {
			$dialog = new XhrDialog();
			$dialog->setTitle('Einladung');
			$dialog->addContent($this->v_utils->v_info('Lieben Dank! Du hast die Einladung angenommen.'));
			$dialog->addButton('Zum Event', 'goTo(\'/?page=event&id=' . (int)$_GET['id'] . '\');');
			$dialog->addAbortButton();

			return $dialog->xhrout();
		}
	}

	public function maybe()
	{
		if ($this->model->setInviteStatus($_GET['id'], 2)) {
			$dialog = new XhrDialog();
			$dialog->setTitle('Einladung');
			$dialog->addContent($this->v_utils->v_info('Lieben Dank! Schön, dass Du vielleicht dabei bist.'));
			$dialog->addButton('Zum Event', 'goTo(\'/?page=event&id=' . (int)$_GET['id'] . '\');');
			$dialog->addAbortButton();

			return $dialog->xhrout();
		}
	}

	public function noaccept()
	{
		if ($this->model->setInviteStatus($_GET['id'], 3)) {
			return array(
				'status' => 1,
				'script' => 'pulseInfo("Einladung gelöscht.");'
			);
		}
	}

	public function ustat()
	{
		if (isset($this->stats[(int)$_GET['s']])) {
			if ($this->model->setInviteStatus($_GET['id'], $_GET['s'])) {
				return array(
					'status' => 1,
					'script' => 'pulseInfo("Einladungsstatus geändert!");'
				);
			}
		}
	}

	public function ustatadd()
	{
		if (isset($this->stats[(int)$_GET['s']])) {
			if ($this->model->addInviteStatus($_GET['id'], $_GET['s'])) {
				return array(
					'status' => 1,
					'script' => 'pulseInfo("Status geändert!");'
				);
			}
		}
	}

	private function mayEvent()
	{
		if ($this->event['public'] == 1 || S::may('orga') || $this->func->isBotFor($this->event['bezirk_id']) || isset($this->event['invites']['may'][$this->func->fsId()])) {
			return true;
		}

		return false;
	}
}
