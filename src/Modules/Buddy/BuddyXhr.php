<?php

namespace Foodsharing\Modules\Buddy;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Control;

class BuddyXhr extends Control
{
	private $bellGateway;
	private $gateway;

	public function __construct(BuddyGateway $gateway, BellGateway $bellGateway, Db $model)
	{
		$this->gateway = $gateway;
		$this->bellGateway = $bellGateway;
		$this->model = $model;

		parent::__construct();
	}

	public function request()
	{
		if ($this->gateway->buddyRequestedMe($_GET['id'], $this->session->id())) {
			$this->gateway->confirmBuddy($_GET['id'], $this->session->id());

			$this->bellGateway->delBellsByIdentifier('buddy-' . $this->session->id() . '-' . (int)$_GET['id']);
			$this->bellGateway->delBellsByIdentifier('buddy-' . (int)$_GET['id'] . $this->session->id());

			$buddy_ids = array();
			if ($b = $this->session->get('buddy-ids')) {
				$buddy_ids = $b;
			}

			$buddy_ids[(int)$_GET['id']] = (int)$_GET['id'];

			$this->session->set('buddy-ids', $buddy_ids);

			return array(
				'status' => 1,
				'script' => '$(".buddyRequest").remove();pulseInfo("Jetzt kennt Ihr Euch!");'
			);
		}

		if ($this->gateway->buddyRequest($_GET['id'], $this->session->id())) {
			// language string for title
			$title = 'buddy_request_title';

			// language string for body too
			$body = 'buddy_request';

			// icon css class
			$icon = $this->func->img($this->session->user('photo'));

			// whats happen when click on the bell content
			$link_attributes = array('href' => '/profile/' . (int)$this->session->id() . '');

			// variables for the language strings
			$vars = array('name' => $this->session->user('name'));

			$identifier = 'buddy-' . $this->session->id() . '-' . (int)$_GET['id'];

			$this->bellGateway->addBell($_GET['id'], $title, $body, $icon, $link_attributes, $vars, $identifier);

			return array(
				'status' => 1,
				'script' => '$(".buddyRequest").remove();pulseInfo("Anfrage versendet!");'
			);
		}
	}

	public function removeRequest(): array
	{
		$this->gateway->removeRequest($_GET['id'], $this->session->id());

		return array(
			'status' => 1,
			'script' => 'pulseInfo("Anfrage gelöscht");$(".buddyreq-' . (int)$_GET['id'] . '").remove();'
		);
	}
}
