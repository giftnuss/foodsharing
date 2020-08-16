<?php

namespace Foodsharing\Modules\Buddy;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Utility\ImageHelper;

class BuddyXhr extends Control
{
	private $bellGateway;
	private $gateway;
	private $imageService;

	public function __construct(BuddyGateway $gateway, BellGateway $bellGateway, ImageHelper $imageService)
	{
		$this->gateway = $gateway;
		$this->bellGateway = $bellGateway;
		$this->imageService = $imageService;

		parent::__construct();
	}

	public function request()
	{
		if ($this->gateway->buddyRequestedMe($_GET['id'], $this->session->id())) {
			$this->gateway->confirmBuddy($_GET['id'], $this->session->id());

			$this->bellGateway->delBellsByIdentifier('buddy-' . $this->session->id() . '-' . (int)$_GET['id']);
			$this->bellGateway->delBellsByIdentifier('buddy-' . (int)$_GET['id'] . '-' . $this->session->id());

			$buddy_ids = [];
			if ($b = $this->session->get('buddy-ids')) {
				$buddy_ids = $b;
			}

			$buddy_ids[(int)$_GET['id']] = (int)$_GET['id'];

			$this->session->set('buddy-ids', $buddy_ids);

			return [
				'status' => 1,
				'script' => '$(".buddyRequest").remove();pulseInfo("Jetzt kennt Ihr Euch!");'
			];
		}

		if ($this->gateway->buddyRequest($_GET['id'], $this->session->id())) {
			$this->bellGateway->addBell($_GET['id'], Bell::create(
				'buddy_request_title',
				'buddy_request',
				$this->imageService->img($this->session->user('photo')),
				['href' => '/profile/' . (int)$this->session->id() . ''],
				['name' => $this->session->user('name')],
				'buddy-' . $this->session->id() . '-' . (int)$_GET['id']
			));

			return [
				'status' => 1,
				'script' => '$(".buddyRequest").remove();pulseInfo("Anfrage versendet!");'
			];
		}
	}

	public function removeRequest(): array
	{
		$this->gateway->removeRequest($_GET['id'], $this->session->id());

		return [
			'status' => 1,
			'script' => 'pulseInfo("Anfrage gelöscht");$(".buddyreq-' . (int)$_GET['id'] . '").remove();'
		];
	}
}
