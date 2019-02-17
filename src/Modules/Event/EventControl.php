<?php

namespace Foodsharing\Modules\Event;

use Foodsharing\Modules\Core\Control;

class EventControl extends Control
{
	private $gateway;

	public function __construct(EventView $view, EventGateway $gateway)
	{
		$this->view = $view;
		$this->gateway = $gateway;

		parent::__construct();
	}

	public function index()
	{
		if (!isset($_GET['sub']) && isset($_GET['id']) && ($event = $this->gateway->getEventWithInvites($_GET['id']))) {
			if (!$this->mayEvent($event)) {
				return false;
			}

			$this->func->addBread('Termine', '/?page=event');
			$this->func->addBread($event['name']);

			$status = $this->gateway->getInviteStatus($event['id'], $this->session->id());

			$this->func->addContent($this->view->eventTop($event), CNT_TOP);
			$this->func->addContent($this->view->statusMenu($event, $status), CNT_LEFT);
			$this->func->addContent($this->view->event($event));

			if ($event['online'] == 0 && $event['location'] != false) {
				$this->func->addContent($this->view->location($event['location']), CNT_RIGHT);
			} elseif ($event['online'] == 1) {
				$this->func->addContent($this->view->locationMumble(), CNT_RIGHT);
			}

			if ($event['invites']) {
				$this->func->addContent($this->view->invites($event['invites']), CNT_RIGHT);
			}
			$this->func->addContent($this->v_utils->v_field($this->wallposts('event', $event['id']), 'Pinnwand'));
		} elseif (!isset($_GET['sub'])) {
			$this->func->go('/?page=dashboard');
		}
	}

	private function isEventAdmin($event): bool
	{
		return $event['fs_id'] == $this->session->id() || $this->session->isAdminFor(
				$event['bezirk_id']
			) || $this->session->may('orga');
	}

	private function mayEvent($event): bool
	{
		return $event['public'] == 1 || $this->session->may('orga') || $this->session->isAdminFor(
				$event['bezirk_id']
			) || isset($event['invites']['may'][$this->session->id()]);
	}

	public function edit()
	{
		if ($event = $this->gateway->getEventWithInvites($_GET['id'])) {
			if (!$this->isEventAdmin($event)) {
				return false;
			}
			if ($event['fs_id'] == $this->session->id() || $this->session->isOrgaTeam() || $this->session->isAdminFor($event['bezirk_id'])) {
				$this->func->addBread('Termine', '/?page=event');
				$this->func->addBread('Neuer Termin');

				if ($this->isSubmitted()) {
					if ($data = $this->validateEvent()) {
						if ($this->gateway->updateEvent($_GET['id'], $data)) {
							if (isset($_POST['delinvites']) && $_POST['delinvites'] == 1) {
								$this->gateway->deleteInvites($_GET['id']);
							}
							if ($data['invite']) {
								$this->gateway->inviteFullRegion($data['bezirk_id'], $_GET['id'], $data['invitesubs']);
							}
							$this->func->info('Event wurde erfolgreich geändert!');
							$this->func->go('/?page=event&id=' . (int)$_GET['id']);
						}
					}
				}

				$bezirke = $this->session->getRegions();

				if ($event['location_id'] !== null) {
					if ($loc = $this->gateway->getLocation($event['location_id'])) {
						$event['location_name'] = $loc['name'];
						$event['lat'] = $loc['lat'];
						$event['lon'] = $loc['lon'];
						$event['plz'] = $loc['zip'];
						$event['ort'] = $loc['city'];
						$event['anschrift'] = $loc['street'];
					}
				}

				$this->func->setEditData($event);

				$this->func->addContent($this->view->eventForm($bezirke));
			} else {
				$this->func->go('/?page=event');
			}
		}
	}

	public function add()
	{
		$this->func->addBread('Termine', '/?page=event');
		$this->func->addBread('Neuer Termin');

		if ($this->isSubmitted()) {
			if ($data = $this->validateEvent()) {
				if ($id = $this->gateway->addEvent($this->session->id(), $data)) {
					if ($data['invite']) {
						$this->gateway->inviteFullRegion($data['bezirk_id'], $id, $data['invitesubs']);
					}
					$this->func->info('Event wurde erfolgreich eingetragen!');
					$this->func->go('/?page=event&id=' . (int)$id);
				}
			}
		} else {
			$bezirke = $this->session->getRegions();

			$this->func->addContent($this->view->eventForm($bezirke));
		}
	}

	private function validateEvent()
	{
		$out = array(
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
		);

		if (isset($_POST['public']) && $_POST['public'] == 1) {
			$out['public'] = 1;
		} elseif ($bid = $this->getPostInt('bezirk_id')) {
			$out['bezirk_id'] = (int)$bid;
			if (isset($_POST['invite']) && $_POST['invite'] == 1) {
				$out['invite'] = true;
				if (isset($_POST['invitesubs']) && $_POST['invitesubs'] == 1) {
					$out['invitesubs'] = true;
				}
			}
		}

		if ($start_date = $this->getPostDate('date')) {
			if ($start_time = $this->getPostTime('time_start')) {
				if ($end_time = $this->getPostTime('time_end')) {
					$out['start'] = date('Y-m-d', $start_date) . ' ' . $this->func->preZero($start_time['hour']) . ':' . $this->func->preZero($start_time['min']) . ':00';
					$out['end'] = date('Y-m-d', $start_date) . ' ' . $this->func->preZero($end_time['hour']) . ':' . $this->func->preZero($end_time['min']) . ':00';

					if ((int)$this->getPostInt('addend') == 1 && ($ed = $this->getPostDate('dateend'))) {
						$out['end'] = date('Y-m-d', $ed) . ' ' . $this->func->preZero($end_time['hour']) . ':' . $this->func->preZero($end_time['min']) . ':00';
					}
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
