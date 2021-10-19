<?php

namespace Foodsharing\Modules\Event;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Event\EventType;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\EventPermissions;
use Foodsharing\Utility\DataHelper;

class EventControl extends Control
{
	private EventGateway $eventGateway;
	private RegionGateway $regionGateway;
	private DataHelper $dataHelper;
	private EventPermissions $eventPermissions;

	public function __construct(
		EventView $view,
		EventGateway $eventGateway,
		RegionGateway $regionGateway,
		DataHelper $dataHelper,
		EventPermissions $eventPermissions
	) {
		$this->view = $view;
		$this->eventGateway = $eventGateway;
		$this->regionGateway = $regionGateway;
		$this->dataHelper = $dataHelper;
		$this->eventPermissions = $eventPermissions;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->goLogin();
		}
	}

	public function index()
	{
		if (isset($_GET['sub'])) {
			// edit / creation are handled in the other EventControl functions
			return;
		}
		$eventId = $_GET['id'] ?? null;
		$regionId = $_GET['bid'] ?? null;

		if ($eventId === null) {
			if ($regionId === null) {
				// this never worked...
				$link = '/?page=dashboard';
			} else {
				// overview page: events in one region
				$link = '/?page=bezirk&sub=events&bid=' . $regionId;
			}

			return $this->routeHelper->go($link);
		}

		$eventId = intval($eventId);
		$event = $this->eventGateway->getEvent($eventId, true);

		if (!$event || !$this->eventPermissions->maySeeEvent($event)) {
			$this->flashMessageHelper->info($this->translator->trans('events.notFound'));

			return $this->routeHelper->go('/?page=dashboard');
		}

		$regionId = $event['bezirk_id'];
		$regionLink = '?page=bezirk&bid=' . $regionId;
		$regionEventsLink = $regionLink . '&sub=events';
		$regionName = $this->regionGateway->getRegionName($regionId) ?? '';

		$this->pageHelper->addBread($regionName, $regionLink);
		$this->pageHelper->addBread($this->translator->trans('events.bread'), $regionEventsLink);
		$this->pageHelper->addBread($event['name']);

		$status = $this->eventGateway->getInviteStatus($eventId, $this->session->id());
		$event['status'] = $status;
		$event['regionName'] = $regionName;

		$mayEdit = $this->eventPermissions->mayEditEvent($event);

		$this->pageHelper->addContent($this->view->eventPanel($event, $mayEdit), CNT_TOP);
		$this->pageHelper->addContent($this->view->event($event));
		$this->pageHelper->setContentWidth(6, 6);

		if ($event['online'] == 0 && $event['location'] != false) {
			$this->pageHelper->addContent($this->view->location($event['location']), CNT_LEFT);
		} elseif ($event['online'] == 1) {
			$this->pageHelper->addContent($this->view->locationMumble(), CNT_LEFT);
		}

		if ($event['invites']) {
			$this->pageHelper->addContent($this->view->invites($event['invites']), CNT_RIGHT);
		}
		$this->pageHelper->addContent($this->v_utils->v_field(
			$this->wallposts('event', $eventId),
			$this->translator->trans('wall.name')
		));
	}

	public function edit()
	{
		$eventId = $_GET['id'] ?? null;
		$event = $this->eventGateway->getEvent($eventId, true);

		if (!$event) {
			return $this->routeHelper->go('/?page=dashboard');
		}

		if (!$this->eventPermissions->mayEditEvent($event)) {
			return $this->routeHelper->go('/?page=event&id=' . $eventId);
		}

		$regionEventsLink = '?page=bezirk&sub=events&bid=' . $event['bezirk_id'];
		$this->pageHelper->addBread($this->translator->trans('events.bread'), $regionEventsLink);
		$this->pageHelper->addBread($event['name'], '/?page=event&id=' . $eventId);
		$this->pageHelper->addBread($this->translator->trans('events.edit'));

		if ($this->isSubmitted() && $data = $this->validateEvent()) {
			if ($this->eventGateway->updateEvent($_GET['id'], $data)) {
				if (isset($_POST['delinvites']) && $_POST['delinvites'] == 1) {
					$this->eventGateway->deleteInvites($_GET['id']);
				}
				if ($data['invite']) {
					$this->eventGateway->inviteFullRegion($data['bezirk_id'], $_GET['id'], $data['invitesubs']);
				}
				$this->flashMessageHelper->success($this->translator->trans('events.edited'));
				$this->routeHelper->go('/?page=event&id=' . (int)$_GET['id']);
			}
		}

		$regions = $this->session->getRegions();

		if (($event['location_id'] !== null) && $loc = $this->eventGateway->getLocation($event['location_id'])) {
			$event['location_name'] = $loc['name'];
			$event['lat'] = $loc['lat'];
			$event['lon'] = $loc['lon'];
			$event['plz'] = $loc['zip'];
			$event['ort'] = $loc['city'];
			$event['anschrift'] = $loc['street'];
		}

		$this->dataHelper->setEditData($event);

		$this->pageHelper->addContent($this->view->eventForm($regions));
	}

	public function add(): void
	{
		$this->pageHelper->addBread($this->translator->trans('events.bread'), '/?page=event');
		$this->pageHelper->addBread($this->translator->trans('events.create.title'));

		if ($this->isSubmitted()) {
			if (($data = $this->validateEvent()) && $id = $this->eventGateway->addEvent($this->session->id(), $data)) {
				if ($data['invite']) {
					$this->eventGateway->inviteFullRegion($data['bezirk_id'], $id, $data['invitesubs']);
				}
				$this->flashMessageHelper->success($this->translator->trans('events.created'));
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
			'location_id' => null,
			'start' => date('Y-m-d') . ' 15:00:00',
			'end' => date('Y-m-d') . ' 16:00:00',
			'public' => 0,
			'bezirk_id' => 0,
			'invite' => false,
			'online' => 0,
			'invitesubs' => false,
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

		$online_type = $this->getPostInt('online_type');

		if (EventType::isOnline($online_type)) {
			$out['online'] = 1;
			$out['location_id'] = null;
		} else {
			$out['online'] = 0;
			$id = $this->eventGateway->addLocation(
				$this->getPostString('location_name'),
				$this->getPost('lat'),
				$this->getPost('lon'),
				$this->getPostString('anschrift'),
				$this->getPostString('plz'),
				$this->getPostString('ort')
			);
			$out['location_id'] = $id;
		}

		return $out;
	}
}
