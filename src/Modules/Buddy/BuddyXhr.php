<?php

namespace Foodsharing\Modules\Buddy;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Services\ImageService;

class BuddyXhr extends Control
{
	private $bellGateway;
	private $gateway;
	private $imageService;

	public function __construct(BuddyGateway $gateway, BellGateway $bellGateway, ImageService $imageService)
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
			$this->bellGateway->delBellsByIdentifier('buddy-' . (int)$_GET['id'] . $this->session->id());

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
			// language string for title
			$title = 'buddy_request_title';

			// language string for body too
			$body = 'buddy_request';

			// icon css class
			$icon = $this->imageService->img($this->session->user('photo'));

			// whats happen when click on the bell content
			$link_attributes = ['href' => '/profile/' . (int)$this->session->id() . ''];

			// variables for the language strings
			$vars = ['name' => $this->session->user('name')];

			$identifier = 'buddy-' . $this->session->id() . '-' . (int)$_GET['id'];

			$this->bellGateway->addBell($_GET['id'], $title, $body, $icon, $link_attributes, $vars, $identifier);

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
