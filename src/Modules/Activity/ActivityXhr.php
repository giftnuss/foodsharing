<?php

namespace Foodsharing\Modules\Activity;

use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Mailbox\MailboxModel;

class ActivityXhr extends Control
{
	private $mailboxModel;

	public function __construct(ActivityModel $model, MailboxModel $mailboxModel)
	{
		$this->model = $model;
		$this->mailboxModel = $mailboxModel;
		parent::__construct();
	}

	public function loadMore(): void
	{
		/*
		 * get ids to not display from options
		 */
		$hidden_ids = array(
			'bezirk' => array(),
			'mailbox' => array(),
			'buddywall' => array()
		);

		if ($sesOptions = $this->session->option('activity-listings')) {
			foreach ($sesOptions as $o) {
				if (isset($hidden_ids[$o['index']])) {
					$hidden_ids[$o['index']][$o['id']] = true;
				}
			}
		}

		$xhr = new Xhr();

		/*
		 * get forum updates
		*/

		$updates = array();

		$updates = $this->model->loadForumUpdates($_GET['page'], $hidden_ids['bezirk']);

		if ($up = $this->model->loadStoreUpdates($_GET['page'])) {
			$updates = array_merge($updates, $up);
		}
		if ($up = $this->model->loadMailboxUpdates($_GET['page'], $hidden_ids['mailbox'])) {
			$updates = array_merge($updates, $up);
		}
		if ($up = $this->model->loadFriendWallUpdates($hidden_ids['buddywall'], $_GET['page'])) {
			$updates = array_merge($updates, $up);
		}

		$updates = array_merge($updates, $this->model->loadBasketWallUpdates($_GET['page']));

		$updates = array_merge($updates, $this->model->loadEventWallUpdates($_GET['page']));

		$xhr->addData('updates', $updates);

		$xhr->send();
	}

	public function load(): void
	{
		/*
		 * get forum updates
		 */
		if (isset($_GET['options'])) {
			$options = array();
			foreach ($_GET['options'] as $o) {
				if ((int)$o['id'] > 0 && isset($o['index'], $o['id'])) {
					$options[$o['index'] . '-' . $o['id']] = [
						'index' => $o['index'],
						'id' => $o['id']
					];
				}
			}

			if (empty($options)) {
				$options = false;
			}

			$this->session->setOption('activity-listings', $options, $this->model);
		}

		$page = 0;
		$hidden_ids = array(
			'bezirk' => array(),
			'mailbox' => array(),
			'buddywall' => array()
		);

		if ($sesOptions = $this->session->option('activity-listings')) {
			foreach ($sesOptions as $o) {
				if (isset($hidden_ids[$o['index']])) {
					$hidden_ids[$o['index']][$o['id']] = $o['id'];
				}
			}
		}

		$xhr = new Xhr();
		$updates = array();
		$updates = $this->model->loadForumUpdates($page, $hidden_ids['bezirk']);

		if ($up = $this->model->loadStoreUpdates()) {
			$updates = array_merge($updates, $up);
		}

		if ($up = $this->model->loadMailboxUpdates($page, $hidden_ids['mailbox'])) {
			$updates = array_merge($updates, $up);
		}
		if ($up = $this->model->loadFriendWallUpdates($hidden_ids['buddywall'], $page)) {
			$updates = array_merge($updates, $up);
		}

		$updates = array_merge($updates, $this->model->loadBasketWallUpdates($page));

		$updates = array_merge($updates, $this->model->loadEventWallUpdates($page));

		$xhr->addData('updates', $updates);

		$xhr->addData('user', [
			'id' => $this->func->fsId(),
			'name' => $this->session->user('name'),
			'avatar' => $this->func->img($this->session->user('photo'))
		]);

		if (isset($_GET['listings'])) {
			$listings = array(
				'groups' => array(),
				'regions' => array(),
				'mailboxes' => array(),
				'stores' => array(),
				'buddywalls' => array()
			);

			$option = array();

			if ($list = $this->session->option('activity-listings')) {
				$option = $list;
			}

			/*
			 * listings regions
			*/
			if ($bezirke = $this->session->getRegions()) {
				foreach ($bezirke as $b) {
					$checked = true;
					$regionId = 'bezirk-' . $b['id'];
					if (isset($option[$regionId])) {
						$checked = false;
					}
					$dat = [
						'id' => $b['id'],
						'name' => $b['name'],
						'checked' => $checked
					];
					if ($b['type'] == Type::WORKING_GROUP) {
						$listings['groups'][] = $dat;
					} else {
						$listings['regions'][] = $dat;
					}
				}
			}

			/*
			 * listings buddy walls
			 */
			if ($buddies = $this->model->getBuddies()) {
				foreach ($buddies as $b) {
					$checked = true;
					$buddyWallId = 'buddywall-' . $b['id'];
					if (isset($option[$buddyWallId])) {
						$checked = false;
					}
					$listings['buddywalls'][] = [
						'id' => $b['id'],
						'name' => '<img style="border-radius:4px;position:relative;top:5px;" src="' . $this->func->img($b['photo']) . '" height="24" /> ' . $b['name'],
						'checked' => $checked
					];
				}
			}

			/*
			 * listings mailboxes
			*/
			if ($boxes = $this->mailboxModel->getBoxes()) {
				foreach ($boxes as $b) {
					$checked = true;
					$mailboxId = 'mailbox-' . $b['id'];
					if (isset($option[$mailboxId])) {
						$checked = false;
					}
					$listings['mailboxes'][] = [
						'id' => $b['id'],
						'name' => $b['name'] . '@' . DEFAULT_EMAIL_HOST,
						'checked' => $checked
					];
				}
			}

			$xhr->addData('listings', [
				0 => [
					'name' => $this->func->s('groups'),
					'index' => 'bezirk',
					'items' => $listings['groups']
				],
				1 => [
					'name' => $this->func->s('regions'),
					'index' => 'bezirk',
					'items' => $listings['regions']
				],
				2 => [
					'name' => $this->func->s('mailboxes'),
					'index' => 'mailbox',
					'items' => $listings['mailboxes']
				],
				3 => [
					'name' => $this->func->s('buddywalls'),
					'index' => 'buddywall',
					'items' => $listings['buddywalls']
				],
			]);
		}

		$xhr->send();
	}
}
