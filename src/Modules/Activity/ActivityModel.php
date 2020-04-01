<?php

namespace Foodsharing\Modules\Activity;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Services\ImageService;
use Foodsharing\Services\SanitizerService;

class ActivityModel extends Db
{
	private $activityGateway;
	private $sanitizerService;
	private $imageService;
	private $mailboxGateway;

	public function __construct(
		ActivityGateway $activityGateway,
		SanitizerService $sanitizerService,
		ImageService $imageService,
		MailboxGateway $mailboxGateway
	) {
		parent::__construct();
		$this->activityGateway = $activityGateway;
		$this->sanitizerService = $sanitizerService;
		$this->imageService = $imageService;
		$this->mailboxGateway = $mailboxGateway;
	}

	public function loadEventWallUpdates(int $page): array
	{
		$updates = $this->activityGateway->fetchAllEventUpdates($this->session->id(), $page);
		$out = [];

		foreach ($updates as $u) {
			$replyUrl = '/xhrapp.php?app=wallpost&m=quickreply&table=event&id=' . (int)$u['event_id'];

			$out[] = [
				'type' => 'event',
				'data' => [
					'desc' => $u['body'],
					'event_id' => $u['event_id'],
					'event_name' => 'Termin: ' . $u['name'],
					'fs_id' => $u['fs_id'],
					'fs_name' => $u['fs_name'],
					'icon' => $this->imageService->img($u['fs_photo'], 50),
					'source_name' => 'Termin',
					'time' => $u['time'],
					'time_ts' => $u['time_ts'],
					'quickreply' => $replyUrl
				]
			];
		}

		return $out;
	}

	// basket wall updates were removed and could be replaced by yet not used food share point updates
	public function loadBasketWallUpdates(int $page): array
	{
		$updates = [];
		if ($up = $this->activityGateway->fetchAllBasketWallUpdates($this->session->id(), $page)) {
			$updates = $up;
		}

		if ($up = $this->activityGateway->fetchAllWallpostsFromFoodBaskets($this->session->id(), $page)) {
			$updates = array_merge($updates, $up);
		}

		if (!empty($updates)) {
			$out = [];

			foreach ($updates as $u) {
				$title = 'Essenskorb #' . $u['basket_id'];

				$out[] = [
					'attr' => [
						'href' => '/profile/' . $u['fs_id']
					],
					'title' => '<a href="/profile/' . $u['fs_id'] . '">' . $u['fs_name'] . '</a> <i class="fas fa-angle-right"></i> <a href="/essenskoerbe/' . $u['basket_id'] . '">' . $title . '</a>',
					'desc' => $this->textPrepare($u['body']),
					'time' => $u['time'],
					'icon' => $this->imageService->img($u['fs_photo'], 50),
					'time_ts' => $u['time_ts'],
					'quickreply' => '/xhrapp.php?app=wallpost&m=quickreply&table=basket&id=' . (int)$u['basket_id']
				];
			}

			return $out;
		}

		return [];
	}

	private function textPrepare($txt): ?string
	{
		$txt = trim($txt);
		$sanitized = $this->sanitizerService->markdownToHtml($txt);

		if (strlen($txt) > 100) {
			return '<span class="txt">' . $this->sanitizerService->markdownToHtml($this->sanitizerService->tt($txt, 90)) . ' <a href="#" onclick="$(this).parent().hide().next().show();return false;">alles zeigen <i class="fas fa-angle-down"></i></a></span><span class="txt" style="display:none;">' . $sanitized . ' <a href="#" onclick="$(this).parent().hide().prev().show();return false;">weniger <i class="fas fa-angle-up"></i></a></span>';
		}

		return '<span class="txt">' . $sanitized . '</span>';
	}

	public function loadFriendWallUpdates(int $page, array $hidden_ids): array
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
				if ($u['fs_id'] === $this->session->id()) {
					$smTitle = 'Deine Pinnwand';
				} else {
					$smTitle = $u['fs_name'] . 's Status';
				}

				$out[] = [
					'type' => 'friendWall',
					'data' => [
						'desc' => $u['body'],
						'icon' => $this->imageService->img($u['fs_photo'], 50),
						'fs_id' => $u['fs_id'],
						'fs_name' => $u['fs_name'],
						'source_name' => $smTitle,
						'time' => $u['time'],
						'time_ts' => $u['time_ts']
					]
				];
			}

			return $out;
		}

		return [];
	}

	public function loadMailboxUpdates(int $page, array $hidden_ids): array
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
							'desc' => $u['body'],
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

	public function loadForumUpdates(int $page, array $hidden_ids): array
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
				$forumTypeString = $u['bot_theme'] === 1 ? 'botforum' : 'forum';
				$forumTypePostfix = $u['bot_theme'] === 1 ? 'BOT-Forum' : 'Forum';
				$url = '/?page=bezirk&bid=' . (int)$u['bezirk_id']
					. '&sub=' . $forumTypeString
					. '&tid=' . (int)$u['id']
					. '&pid=' . (int)$u['last_post_id']
					. '#tpost-' . (int)$u['last_post_id'];
				$replyUrl = '/xhrapp.php?app=bezirk&m=quickreply&bid=' . (int)$u['bezirk_id']
					. '&tid=' . (int)$u['id']
					. '&pid=' . (int)$u['last_post_id']
					. '&sub=' . $forumTypeString;

				$out[] = [
					'type' => 'forum',
					'data' => [
						'fs_id' => (int)$u['foodsaver_id'],
						'fs_name' => $u['foodsaver_name'],
						'forum_href' => $url,
						'forum_name' => $u['name'],
						'source_name' => $forumTypePostfix . ' ' . $u['bezirk_name'],
						'desc' => $u['post_body'],
						'time' => $u['update_time'],
						'icon' => $this->imageService->img($u['foodsaver_photo'], 50),
						'time_ts' => $u['update_time_ts'],
						'quickreply' => $replyUrl
					]
				];
			}

			return $out;
		}

		return [];
	}

	public function loadStoreUpdates(int $page): array
	{
		if ($this->session->getMyBetriebIds() && $ret = $this->activityGateway->fetchAllStoreUpdates($this->session->id(), $page)) {
			$out = [];
			foreach ($ret as $r) {
				$out[] = [
					'type' => 'store',
					'data' => [
						'fs_id' => $r['foodsaver_id'],
						'fs_name' => $r['foodsaver_name'],
						'store_id' => $r['betrieb_id'],
						'store_name' => $r['betrieb_name'],
						'desc' => $r['text'],
						'time' => $r['update_time'],
						'icon' => $this->imageService->img($r['foodsaver_photo'], 50),
						'time_ts' => $r['update_time_ts']
					]
				];
			}

			return $out;
		}

		return [];
	}

	public function getBuddies()
	{
		if ($buddyIds = $this->session->get('buddy-ids')) {
			return $this->activityGateway->fetchAllBuddies($buddyIds);
		}

		return false;
	}
}
