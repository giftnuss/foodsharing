<?php

namespace Foodsharing\Modules\Activity;

use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Services\ImageService;

class ActivityXhr extends Control
{
	private $imageService;
	private $mailboxGateway;

	public function __construct(
		ActivityModel $model,
		ImageService $imageService,
		MailboxGateway $mailboxGateway
	) {
		$this->model = $model;
		$this->imageService = $imageService;
		$this->mailboxGateway = $mailboxGateway;
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
		$xhr->addData('updates', $this->buildUpdateData($hidden_ids, $_GET['page']));
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
		$xhr->addData('updates', $this->buildUpdateData($hidden_ids, 0));
		$xhr->addData('user', [
			'id' => $this->session->id(),
			'name' => $this->session->user('name'),
			'avatar' => $this->imageService->img($this->session->user('photo'))
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
						'name' => '<img style="border-radius:4px;position:relative;top:5px;" src="' . $this->imageService->img($b['photo']) . '" height="24" /> ' . $b['name'],
						'checked' => $checked
					];
				}
			}

			/*
			 * listings mailboxes
			*/
			if ($boxes = $this->mailboxGateway->getBoxes($this->session->isAmbassador(), $this->session->id(), $this->session->may('bieb'))) {
				foreach ($boxes as $b) {
					$checked = true;
					$mailboxId = 'mailbox-' . $b['id'];
					if (isset($option[$mailboxId])) {
						$checked = false;
					}
					$listings['mailboxes'][] = [
						'id' => $b['id'],
						'name' => $b['name'] . '@' . PLATFORM_MAILBOX_HOST,
						'checked' => $checked
					];
				}
			}

			$xhr->addData('listings', [
				0 => [
					'name' => $this->translationHelper->s('groups'),
					'index' => 'bezirk',
					'items' => $listings['groups']
				],
				1 => [
					'name' => $this->translationHelper->s('regions'),
					'index' => 'bezirk',
					'items' => $listings['regions']
				],
				2 => [
					'name' => $this->translationHelper->s('mailboxes'),
					'index' => 'mailbox',
					'items' => $listings['mailboxes']
				],
				3 => [
					'name' => $this->translationHelper->s('buddywalls'),
					'index' => 'buddywall',
					'items' => $listings['buddywalls']
				],
			]);
		}

		$xhr->send();
	}

	private function buildUpdateData(array $hidden_ids, int $page): array
	{
		return array_merge(
			$this->model->loadForumUpdates($page, $hidden_ids['bezirk']),
			$this->model->loadStoreUpdates($page),
			$this->model->loadMailboxUpdates($page, $hidden_ids['mailbox']),
			$this->model->loadFriendWallUpdates($page, $hidden_ids['buddywall']),
			$this->model->loadBasketWallUpdates($page),
			$this->model->loadEventWallUpdates($page)
		);
	}
}
