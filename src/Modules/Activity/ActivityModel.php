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
			$smTitle = '';
			$title = 'Termin: ' . $u['name'];

			$out[] = [
				'attr' => [
				'href' => '/profile/' . $u['fs_id']
				],
				'title' => '<a href="/profile/' . $u['fs_id'] . '">' . $u['fs_name'] . '</a> <i class="fa fa-angle-right"></i> <a href="?page=event&id=' . $u['event_id'] . '">' . $title . '</a><small>' . $smTitle . '</small>',
				'desc' => $this->textPrepare($u['body']),
				'time' => $u['time'],
				'icon' => $this->imageService->img($u['fs_photo'], 50),
				'time_ts' => $u['time_ts'],
				'quickreply' => '/xhrapp.php?app=wallpost&m=quickreply&table=event&id=' . (int)$u['event_id']
			];
		}

		return $out;
	}

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
				$smTitle = '';
				$title = 'Essenskorb #' . $u['basket_id'];

				$out[] = [
					'attr' => [
						'href' => '/profile/' . $u['fs_id']
					],
					'title' => '<a href="/profile/' . $u['fs_id'] . '">' . $u['fs_name'] . '</a> <i class="fas fa-angle-right"></i> <a href="/essenskoerbe/' . $u['basket_id'] . '">' . $title . '</a><small>' . $smTitle . '</small>',
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
				$smTitle = $u['fs_name'] . 's Status';

				if ($u['fs_id'] === $this->session->id()) {
					$smTitle = 'Deine Pinnwand';
				}

				$out[] = [
					'attr' => [
						'href' => '/profile/' . $u['fs_id']
					],
					'title' => '<a href="/profile/' . $u['poster_id'] . '">' . $u['poster_name'] . '</a> <small>' . $smTitle . '</small>',
					'desc' => $this->textPrepare($u['body']),
					'time' => $u['time'],
					'icon' => $this->imageService->img($u['fs_photo'], 50),
					'time_ts' => $u['time_ts']
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

					$from = 'E-Mail';

					if ($sender !== null) {
						if (isset($sender['from']) && !empty($sender['from'])) {
							$from = '<a title="' . $sender['mailbox'] . '@' . $sender['host'] . '" href="/?page=mailbox&mailto=' . urlencode($sender['mailbox'] . '@' . $sender['host']) . '">' . $this->ttt($sender['personal'], 22) . '</a>';
						} elseif (isset($sender['mailbox'])) {
							$from = '<a title="' . $sender['mailbox'] . '@' . $sender['host'] . '" href="/?page=mailbox&mailto=' . urlencode($sender['mailbox'] . '@' . $sender['host']) . '">' . $this->ttt($sender['mailbox'] . '@' . $sender['host'], 22) . '</a>';
						}
					}

					$out[] = [
						'attr' => [
							'href' => '/?page=mailbox&show=' . $u['id']
						],
						'title' => $from . ' <i class="fas fa-angle-right"></i> <a href="/?page=mailbox&show=' . $u['id'] . '">' . $this->ttt($u['subject'], 30) . '</a><small>' . $this->ttt($u['mb_name'] . '@' . PLATFORM_MAILBOX_HOST, 19) . '</small>',
						'desc' => $this->textPrepare($u['body']),
						'time' => $u['time'],
						'icon' => '/img/mailbox-50x50.png',
						'time_ts' => $u['time_ts'],
						'quickreply' => '/xhrapp.php?app=mailbox&m=quickreply&mid=' . (int)$u['id']
					];
				}

				return $out;
			}
		}

		return [];
	}

	private function ttt($str, $length = 160)
	{
		if (strlen($str) > $length) {
			$str = substr($str, 0, ($length - 4)) . '...';
		}

		return $str;
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
			$updates = array_merge($updates, $this->activityGateway->fetchAllForumUpdates($ambassadorIds, $page, true));
		}

		if (!empty($updates)) {
			$out = [];
			foreach ($updates as $u) {
				$forumTypeString = $u['bot_theme'] === 1 ? 'botforum' : 'forum';
				$ambPrefix = $u['bot_theme'] === 1 ? 'BOT' : '';
				$url = '/?page=bezirk&bid=' . (int)$u['bezirk_id'] . '&sub=' . $forumTypeString . '&tid=' . (int)$u['id'] . '&pid=' . (int)$u['last_post_id'] . '#tpost-' . (int)$u['last_post_id'];
				$out[] = [
					'attr' => [
						'href' => $url
					],
					'title' => '<a href="/profile/' . (int)$u['foodsaver_id'] . '">' . $u['foodsaver_name'] . '</a> <i class="fas fa-angle-right"></i> <a href="' . $url . '">' . $u['name'] . '</a> <small>' . $ambPrefix . ' ' . $u['bezirk_name'] . '</small>',
					'desc' => $this->textPrepare($u['post_body']),
					'time' => $u['update_time'],
					'icon' => $this->imageService->img($u['foodsaver_photo'], 50),
					'time_ts' => $u['update_time_ts'],
					'quickreply' => '/xhrapp.php?app=bezirk&m=quickreply&bid=' . (int)$u['bezirk_id'] . '&tid=' . (int)$u['id'] . '&pid=' . (int)$u['last_post_id'] . '&sub=' . $forumTypeString
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
					'attr' => [
						'href' => '/?page=fsbetrieb&id=' . $r['betrieb_id']
					],
					'title' => '<a href="/profile/' . $r['foodsaver_id'] . '">' . $r['foodsaver_name'] . '</a> <i class="fas fa-angle-right"></i> <a href="/?page=fsbetrieb&id=' . $r['betrieb_id'] . '">' . $r['betrieb_name'] . '</a>',
					'desc' => $this->textPrepare($r['text']),
					'time' => $r['update_time'],
					'icon' => $this->imageService->img($r['foodsaver_photo'], 50),
					'time_ts' => $r['update_time_ts']
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
