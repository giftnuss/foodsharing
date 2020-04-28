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
	private $activityGateway;

	public function __construct(
		ImageService $imageService,
		MailboxGateway $mailboxGateway,
		ActivityGateway $activityGateway
	) {
		$this->imageService = $imageService;
		$this->mailboxGateway = $mailboxGateway;
		$this->activityGateway = $activityGateway;
		parent::__construct();
	}

	public function load(): void
	{
		/*
		 * get forum updates
		 */
		if (isset($_GET['options'])) {
			$options = [];
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

		$page = $_GET['page'] ?? 0;

		$hidden_ids = [
			'bezirk' => [],
			'mailbox' => [],
			'buddywall' => []
		];

		if ($sesOptions = $this->session->option('activity-listings')) {
			foreach ($sesOptions as $o) {
				if (isset($hidden_ids[$o['index']])) {
					$hidden_ids[$o['index']][$o['id']] = $o['id'];
				}
			}
		}

		$xhr = new Xhr();
		$xhr->addData('updates', $this->buildUpdateData($hidden_ids, $page));
		$xhr->addData('user', [
			'id' => $this->session->id(),
			'name' => $this->session->user('name'),
			'avatar' => $this->imageService->img($this->session->user('photo'))
		]);
		$xhr->send();
	}

	public function setOptionList(): void
	{
		if (isset($_GET['options'])) {
			$options = [];
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

		if (isset($_GET['select_all_options'])) {
			$this->session->setOption('activity-listings', false, $this->model);
		}
	}

	public function getOptionList(): void
	{
		/*
		 * get forum updates
		 */

		$xhr = new Xhr();

		$listings = [
			'groups' => [],
			'regions' => [],
			'mailboxes' => [],
			'stores' => [],
			'buddywalls' => []
		];

		$option = [];

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
		$buddies = $this->getBuddies();
		foreach ($buddies as $b) {
			$checked = true;
			$buddyWallId = 'buddywall-' . $b['id'];
			if (isset($option[$buddyWallId])) {
				$checked = false;
			}
			$listings['buddywalls'][] = [
				'id' => $b['id'],
				'imgUrl' => $this->imageService->img($b['photo']),
				'name' => $b['name'],
				'checked' => $checked
			];
		}

		/*
			* listings mailboxes
		*/
		if ($boxes = $this->mailboxGateway->getBoxes(
				$this->session->isAmbassador(),
				$this->session->id(),
				$this->session->may('bieb'))
			) {
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

		$xhr->send();
	}

	private function buildUpdateData(array $hidden_ids, int $page): array
	{
		return array_merge(
			$this->loadForumUpdates($page, $hidden_ids['bezirk']),
			$this->loadStoreUpdates($page),
			$this->loadMailboxUpdates($page, $hidden_ids['mailbox']),
			$this->loadFoodSharePointWallUpdates($page),
			$this->loadFriendWallUpdates($page, $hidden_ids['buddywall']),
			$this->loadEventWallUpdates($page)
		);
	}

	private function loadEventWallUpdates(int $page): array
	{
		$updates = $this->activityGateway->fetchAllEventUpdates($this->session->id(), $page);
		$out = [];

		foreach ($updates as $u) {
			$replyUrl = '/xhrapp.php?app=wallpost&m=quickreply&table=event&id=' . (int)$u['event_id'];

			$out[] = [
				'type' => 'event',
				'data' => [
					'desc' => $u['body'] ?? '',
					'event_id' => $u['event_id'],
					'event_name' => $u['name'],
					'fs_id' => $u['fs_id'],
					'fs_name' => $u['fs_name'],
					'gallery' => $u['gallery'] ?? [],
					'icon' => $this->imageService->img($u['fs_photo'], 50),
					'source' => $u['event_region'],
					'time' => $u['time'],
					'time_ts' => $u['time_ts'],
					'quickreply' => $replyUrl
				]
			];
		}

		return $out;
	}

	private function loadFoodSharePointWallUpdates(int $page): array
	{
		$updates = $this->activityGateway->fetchAllFoodSharePointWallUpdates($this->session->id(), $page);
		$out = [];

		foreach ($updates as $u) {
			// This would send updates to all subscribers, is it really needed?
			$replyUrl = '/xhrapp.php?app=wallpost&m=quickreply&table=fairteiler&id=' . (int)$u['fsp_id'];

			$out[] = [
				'type' => 'foodsharepoint',
				'data' => [
					'desc' => $u['body'] ?? '',
					'fsp_id' => $u['fsp_id'],
					'fsp_name' => $u['name'],
					'fs_id' => $u['fs_id'],
					'fs_name' => $u['fs_name'],
					'gallery' => $u['gallery'] ?? [],
					'icon' => $this->imageService->img($u['fs_photo'], 50),
					'region_id' => $u['region_id'],
					'source' => $u['fsp_location'],
					'time' => $u['time'],
					'time_ts' => $u['time_ts']
				]
				// 'quickreply' => $replyUrl
			];
		}

		return $out;
	}

	private function loadFriendWallUpdates(int $page, array $hidden_ids): array
	{
		$buddy_ids = [];

		if ($b = $this->session->get('buddy-ids')) {
			$buddy_ids = $b;
		}

		$buddy_ids[$this->session->id()] = $this->session->id();

		$bids = [];
		foreach ($buddy_ids as $id) {
			if (!isset($hidden_ids[$id])) {
				$bids[] = $id;
			}
		}

		if ($updates = $this->activityGateway->fetchAllFriendWallUpdates($bids, $page)) {
			$out = [];
			foreach ($updates as $u) {
				$is_own = $u['fs_id'] === $this->session->id();

				$out[] = [
					'type' => 'friendWall',
					'data' => [
						'desc' => $u['body'] ?? '',
						'fs_id' => $u['fs_id'],
						'fs_name' => $u['fs_name'],
						'gallery' => $u['gallery'] ?? [],
						'icon' => $this->imageService->img($u['fs_photo'], 50),
						'is_own' => $is_own ? '_own' : null,
						'source' => $u['fs_name'],
						'time' => $u['time'],
						'time_ts' => $u['time_ts']
					]
				];
			}

			return $out;
		}

		return [];
	}

	private function loadMailboxUpdates(int $page, array $hidden_ids): array
	{
		if ($boxes = $this->mailboxGateway->getBoxes($this->session->isAmbassador(), $this->session->id(), $this->session->may('bieb'))) {
			$mb_ids = [];
			foreach ($boxes as $b) {
				if (!isset($hidden_ids[$b['id']])) {
					$mb_ids[] = $b['id'];
				}
			}

			if (count($mb_ids) === 0) {
				return [];
			}

			if ($updates = $this->activityGateway->fetchAllMailboxUpdates($mb_ids, $page)) {
				$out = [];
				foreach ($updates as $u) {
					$sender = @json_decode($u['sender'], true);

					$out[] = [
						'type' => 'mailbox',
						'data' => [
							'sender_email' => $sender['mailbox'] . '@' . $sender['host'],
							'mailbox_id' => $u['id'],
							'subject' => $u['subject'],
							'mailbox_name' => $u['mb_name'] . '@' . PLATFORM_MAILBOX_HOST,
							'desc' => $u['body'] ?? '',
							'time' => $u['time'],
							'icon' => '/img/mailbox-50x50.png',
							'time_ts' => $u['time_ts'],
							'quickreply' => '/xhrapp.php?app=mailbox&m=quickreply&mid=' . (int)$u['id']
						]
					];
				}

				return $out;
			}
		}

		return [];
	}

	private function loadForumUpdates(int $page, array $hidden_ids): array
	{
		$myRegionIds = $this->session->listRegionIDs();
		$region_ids = [];
		if ($myRegionIds === [] || count($myRegionIds) === 0) {
			return [];
		}

		foreach ($myRegionIds as $regionId) {
			if ($regionId > 0 && !isset($hidden_ids[$regionId])) {
				$region_ids[] = $regionId;
			}
		}

		if (count($region_ids) === 0) {
			return [];
		}

		$updates = $this->activityGateway->fetchAllForumUpdates($region_ids, $page, false);
		if ($ambassadorIds = $this->session->getMyAmbassadorRegionIds()) {
			$botPosts = $this->activityGateway->fetchAllForumUpdates($ambassadorIds, $page, true);
			$updates = array_merge($updates, $botPosts);
		}

		if (!empty($updates)) {
			$out = [];
			foreach ($updates as $u) {
				$is_bot = $u['bot_theme'] === 1;

				$forumTypeString = $is_bot ? 'botforum' : 'forum';

				$replyUrl = '/xhrapp.php?app=bezirk&m=quickreply&bid=' . (int)$u['bezirk_id']
					. '&tid=' . (int)$u['id']
					. '&pid=' . (int)$u['last_post_id']
					. '&sub=' . $forumTypeString;

				$out[] = [
					'type' => 'forum',
					'data' => [
						'desc' => $u['post_body'] ?? '',
						'fs_id' => (int)$u['foodsaver_id'],
						'fs_name' => $u['foodsaver_name'],
						'forum_name' => $u['name'],
						'forum_post' => (int)$u['last_post_id'],
						'forum_topic' => (int)$u['id'],
						'forum_type' => $forumTypeString,
						'icon' => $this->imageService->img($u['foodsaver_photo'], 50),
						'is_bot' => $is_bot ? '_bot' : null,
						'region_id' => (int)$u['bezirk_id'],
						'source' => $u['bezirk_name'],
						'time' => $u['update_time'],
						'time_ts' => $u['update_time_ts'],
						'quickreply' => $replyUrl
					]
				];
			}

			return $out;
		}

		return [];
	}

	private function loadStoreUpdates(int $page): array
	{
		if ($this->session->getMyBetriebIds() && $ret = $this->activityGateway->fetchAllStoreUpdates($this->session->id(), $page)) {
			$out = [];
			foreach ($ret as $r) {
				$out[] = [
					'type' => 'store',
					'data' => [
						'desc' => $r['text'] ?? '',
						'fs_id' => $r['foodsaver_id'],
						'fs_name' => $r['foodsaver_name'],
						'icon' => $this->imageService->img($r['foodsaver_photo'], 50),
						'source' => $r['region_name'],
						'store_id' => $r['betrieb_id'],
						'store_name' => $r['betrieb_name'],
						'time' => $r['update_time'],
						'time_ts' => $r['update_time_ts']
					]
				];
			}

			return $out;
		}

		return [];
	}

	/**
	 * Lists all of the user's buddies.
	 *
	 * @return array a list of buddies or an empty array
	 */
	private function getBuddies(): array
	{
		if ($buddyIds = $this->session->get('buddy-ids')) {
			return $this->activityGateway->fetchAllBuddies($buddyIds);
		}

		return [];
	}
}
