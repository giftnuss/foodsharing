<?php

namespace Foodsharing\Modules\Buddy;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Control;

class BuddyXhr extends Control
{
	private $bellGateway;

	public function __construct(BuddyModel $model, BellGateway $bellGateway)
	{
		$this->model = $model;
		$this->bellGateway = $bellGateway;

		parent::__construct();
	}

	public function request()
	{
		if ($this->model->buddyRequestedMe($_GET['id'])) {
			$this->model->confirmBuddy($_GET['id']);

			$this->model->delBells('buddy-' . $this->func->fsId() . '-' . (int)$_GET['id']);
			$this->model->delBells('buddy-' . (int)$_GET['id'] . $this->func->fsId());

			$buddy_ids = array();
			if ($b = S::get('buddy-ids')) {
				$buddy_ids = $b;
			}

			$buddy_ids[(int)$_GET['id']] = (int)$_GET['id'];

			S::set('buddy-ids', $buddy_ids);

			return array(
				'status' => 1,
				'script' => '$(".buddyRequest").remove();pulseInfo("Jetzt kennt Ihr Euch!");'
			);
		} elseif ($this->model->buddyRequest($_GET['id'])) {
			// language string for title
			$title = 'buddy_request_title';

			// language string for body too
			$body = 'buddy_request';

			// icon css class
			$icon = $this->func->img(S::user('photo'));

			// whats happen when click on the bell content
			$link_attributes = array('href' => '#', 'onclick' => 'profile(' . (int)$this->func->fsId() . ');return false;');

			// variables for the language strings
			$vars = array('name' => S::user('name'));

			$identifier = 'buddy-' . $this->func->fsId() . '-' . (int)$_GET['id'];

			$this->bellGateway->addBell($_GET['id'], $title, $body, $icon, $link_attributes, $vars, $identifier);

			return array(
				'status' => 1,
				'script' => '$(".buddyRequest").remove();pulseInfo("Anfrage versendet!");'
			);
		}
	}

	public function removeRequest()
	{
		$this->model->removeRequest($_GET['id']);

		return array(
			'status' => 1,
			'script' => 'pulseInfo("Anfrage gelöscht");$(".buddyreq-' . (int)$_GET['id'] . '").remove();'
		);
	}
}
