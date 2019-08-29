<?php

namespace Foodsharing\Modules\Event;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Permissions\EventPermissions;

class EventControl extends Control
{
	private $gateway;
	private $dataHelper;
	private $eventPermissions;

	public function __construct(EventView $view, EventGateway $gateway, DataHelper $dataHelper, EventPermissions $eventPermissions)
	{
		$this->view = $view;
		$this->gateway = $gateway;
		$this->dataHelper = $dataHelper;
		$this->eventPermissions = $eventPermissions;

		parent::__construct();
	}

	public function index()
	{
		if (!isset($_GET['sub']) && isset($_GET['id']) && ($event = $this->gateway->getEventWithInvites($_GET['id'])) && $this->eventPermissions->maySeeEvent($event)) {
			$this->pageHelper->addBread('Termine', '/?page=event');
			$this->pageHelper->addBread($event['name']);

			$status = $this->gateway->getInviteStatus($event['id'], $this->session->id());

			$this->pageHelper->addContent($this->view->eventTop($event), CNT_TOP);
			$this->pageHelper->addContent($this->view->statusMenu($event, $status), CNT_LEFT);
			$this->pageHelper->addContent($this->view->event($event));

			if ($event['online'] == 0 && $event['location'] != false) {
				$this->pageHelper->addContent($this->view->location($event['location']), CNT_RIGHT);
			} elseif ($event['online'] == 1) {
				$this->pageHelper->addContent($this->view->locationMumble(), CNT_RIGHT);
			}

			if ($event['invites']) {
				$this->pageHelper->addContent($this->view->invites($event['invites']), CNT_RIGHT);
			}
			$this->pageHelper->addContent($this->v_utils->v_field($this->wallposts('event', $event['id']), 'Pinnwand'));
		} elseif (!isset($_GET['sub'])) {
			$this->flashMessageHelper->info($this->translationHelper->s('event_not_available'));
			$this->routeHelper->go('/?page=dashboard');
		}
	}

	public function edit()
	{
		if ($event = $this->gateway->getEventWithInvites($_GET['id'])) {
			if (!$this->eventPermissions->mayEditEvent($event)) {
				return false;
			}

			if ($this->eventPermissions->mayEditEvent($event)) {
				$this->pageHelper->addBread('Termine', '/?page=event');
				$this->pageHelper->addBread('Neuer Termin');

				if ($this->isSubmitted() && $data = $this->validateEvent()) {
					if ($this->gateway->updateEvent($_GET['id'], $data)) {
						if (isset($_POST['delinvites']) && $_POST['delinvites'] == 1) {
							$this->gateway->deleteInvites($_GET['id']);
						}
						if ($data['invite']) {
							$this->gateway->inviteFullRegion($data['bezirk_id'], $_GET['id'], $data['invitesubs']);
						}
						$this->flashMessageHelper->info('Event wurde erfolgreich geändert!');
						$this->routeHelper->go('/?page=event&id=' . (int)$_GET['id']);
					}
				}

				$regions = $this->session->getRegions();

				if (($event['location_id'] !== null) && $loc = $this->gateway->getLocation($event['location_id'])) {
					$event['location_name'] = $loc['name'];
					$event['lat'] = $loc['lat'];
					$event['lon'] = $loc['lon'];
					$event['plz'] = $loc['zip'];
					$event['ort'] = $loc['city'];
					$event['anschrift'] = $loc['street'];
				}

				$this->dataHelper->setEditData($event);

				$this->pageHelper->addContent($this->view->eventForm($regions));
			} else {
				$this->routeHelper->go('/?page=event');
			}
		}
	}

	public function add(): void
	{
		$this->pageHelper->addBread('Termine', '/?page=event');
		$this->pageHelper->addBread('Neuer Termin');

		if ($this->isSubmitted()) {
			if (($data = $this->validateEvent()) && $id = $this->gateway->addEvent($this->session->id(), $data)) {
				if ($data['invite']) {
					$this->gateway->inviteFullRegion($data['bezirk_id'], $id, $data['invitesubs']);
				}
				$this->flashMessageHelper->info('Event wurde erfolgreich eingetragen!');
				$this->routeHelper->go('/?page=event&id=' . $id);
			}
		} else {
			$regions = $this->session->getRegions();

			$this->pageHelper->addContent($this->view->eventForm($regions));
		}
	}

	private function validateEvent(): array
	{
		$out = [
			'name' => '',
			'description' => '',
			'online_type' => 0,
			'location_id' => null,
			'start' => date('Y-m-d') . ' 15:00:00',
			'end' => date('Y-m-d') . ' 16:00:00',
			'public' => 0,
			'bezirk_id' => 0,
			'invite' => false,
			'online' => 0,
			'invitesubs' => false
		];

		if (isset($_POST['public']) && $_POST['public'] == 1) {
			$out['public'] = 1;
		} elseif ($regionId = $this->getPostInt('bezirk_id')) {
			$out['bezirk_id'] = (int)$regionId;
			if (isset($_POST['invite']) && $_POST['invite'] == InvitationStatus::ACCEPTED) {
				$out['invite'] = true;
				if (isset($_POST['invitesubs']) && $_POST['invitesubs'] == 1) {
					$out['invitesubs'] = true;
				}
			}
		}

		if (($start_date = $this->getPostDate('date')) && $start_time = $this->getPostTime('time_start')) {
			if ($end_time = $this->getPostTime('time_end')) {
				$out['start'] = date('Y-m-d', $start_date) . ' ' . sprintf('%02d', $start_time['hour']) . ':' . sprintf(
						'%02d',
						$start_time['min']
					) . ':00';
				$out['end'] = date('Y-m-d', $start_date) . ' ' . sprintf('%02d', $end_time['hour']) . ':' . sprintf(
						'%02d',
						$end_time['min']
					) . ':00';

				if ((int)$this->getPostInt('addend') == 1 && ($ed = $this->getPostDate('dateend'))) {
					$out['end'] = date('Y-m-d', $ed) . ' ' . sprintf('%02d', $end_time['hour']) . ':' . sprintf(
							'%02d',
							$end_time['min']
						) . ':00';
				}
			}
		}

		if ($name = $this->getPostString('name')) {
			$out['name'] = $name;
		}

		if ($description = $this->getPostString('description')) {
			$out['description'] = $description;
		}

		$out['online_type'] = $this->getPostInt('online_type');

		if ($out['online_type'] == 1) {
			$out['online'] = 0;

			$lat = $this->getPost('lat');
			$lon = $this->getPost('lon');

			$id = $this->gateway->addLocation(
				$this->getPostString('location_name'),
				$lat,
				$lon,
				$this->getPostString('anschrift'),
				$this->getPostString('plz'),
				$this->getPostString('ort')
			);

			$out['location_id'] = $id;
		} else {
			$out['online'] = 1;
			$out['location_id'] = null;
		}

		return $out;
	}
}
