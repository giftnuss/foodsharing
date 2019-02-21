<?php

namespace Foodsharing\Modules\Activity;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Mailbox\MailboxModel;
use Foodsharing\Services\SanitizerService;

class ActivityModel extends Db
{
	private $mailboxModel;
	private $activityGateway;
	private $sanitizerService;

	public function __construct(MailboxModel $mailboxModel, ActivityGateway $activityGateway, SanitizerService $sanitizerService)
	{
		parent::__construct();
		$this->mailboxModel = $mailboxModel;
		$this->activityGateway = $activityGateway;
		$this->sanitizerService = $sanitizerService;
	}

	public function loadBasketWallUpdates($page = 0)
	{
		$updates = array();
		if ($up = $this->activityGateway->fetchAllBasketWallUpdates($this->session->id(), $page)) {
			$updates = $up;
		}

		if ($up = $this->activityGateway->fetchAllWallpostsFromFoodBaskets($this->session->id(), $page)) {
			$updates = array_merge($updates, $up);
		}

		if (!empty($updates)) {
			$out = array();

			foreach ($updates as $u) {
				/*
				 * quick fix later list all comments in a package
				*/
				if (isset($hb[$u['basket_id']])) {
					continue;
				}
				$hb[$u['basket_id']] = true;

				$smTitle = '';
				$title = 'Essenskorb #' . $u['basket_id'];

				$out[] = [
					'type' => 'foodbasket',
					'data' => [
						'fs_id' => $u['fs_id'],
						'fs_name' => $u['fs_name'],
						'basked_id' => $u['basket_id'],
						'desc' => $u['body'],
						'time' => $u['time'],
						'icon' => $this->func->img($u['fs_photo'], 50),
						'time_ts' => $u['time_ts'],
						'quickreply' => '/xhrapp.php?app=wallpost&m=quickreply&table=basket&id=' . (int)$u['basket_id']
					],
					'attr' => [
						'href' => '/profile/' . $u['fs_id']
					],
					'title' => '<a href="/profile/' . $u['fs_id'] . '">' . $u['fs_name'] . '</a> <i class="fas fa-angle-right"></i> <a href="/essenskoerbe/' . $u['basket_id'] . '">' . $title . '</a><small>' . $smTitle . '</small>',
					'desc' => $this->textPrepare($u['body']),
					'time' => $u['time'],
					'icon' => $this->func->img($u['fs_photo'], 50),
					'time_ts' => $u['time_ts'],
					'quickreply' => '/xhrapp.php?app=wallpost&m=quickreply&table=basket&id=' . (int)$u['basket_id']
				];
			}

			return $out;
		}

		return false;
	}

	private function textPrepare($txt): ?string
	{
		$txt = trim($txt);
		$sanitized = $this->sanitizerService->markdownToHtml($txt);

		if (strlen($txt) > 100) {
			return '<span class="txt">' . $this->sanitizerService->markdownToHtml($this->func->tt($txt, 90)) . ' <a href="#" onclick="$(this).parent().hide().next().show();return false;">alles zeigen <i class="fas fa-angle-down"></i></a></span><span class="txt" style="display:none;">' . $sanitized . ' <a href="#" onclick="$(this).parent().hide().prev().show();return false;">weniger <i class="fas fa-angle-up"></i></a></span>';
		}

		return '<span class="txt">' . $sanitized . '</span>';
	}

	public function loadFriendWallUpdates($hidden_ids, $page = 0)
	{
		$buddy_ids = array();

		if ($b = $this->session->get('buddy-ids')) {
			$buddy_ids = $b;
		}

		$buddy_ids[$this->session->id()] = $this->session->id();

		$bids = array();
		foreach ($buddy_ids as $id) {
			if (!isset($hidden_ids[$id])) {
				$bids[] = $id;
			}
		}

		if ($updates = $this->activityGateway->fetchAllFriendWallUpdates($bids, $page)) {
			$out = array();
			$hb = array();
			foreach ($updates as $u) {
				/*
				 * quick fix later list all comments in a package
				*/
				if (isset($hb[$u['fs_id']])) {
					continue;
				}
				$hb[$u['fs_id']] = true;

				$smTitle = $u['fs_name'] . 's Status';

				if ($u['fs_id'] === $this->session->id()) {
					$smTitle = 'Deine Pinnwand';
				}

				if (empty($u['gallery'])) {
					$u['gallery'] = '[]';
				}

				$out[] = [
					'type' => 'friendWall',
					'data' => [
						'fs_id' => $u['fs_id'],
						'fs_name' => $u['fs_name'],
						'poster_id' => $u['poster_id'],
						'poster_name' => $u['poster_name'],
						'desc' => $u['body'],
						'time' => $u['time'],
						'icon' => $this->func->img($u['fs_photo'], 50),
						'time_ts' => $u['time_ts'],
						'gallery' => $u['gallery']
					],
					'attr' => [
						'href' => '/profile/' . $u['fs_id']
					],
					'title' => '<a href="/profile/' . $u['poster_id'] . '">' . $u['poster_name'] . '</a> <small>' . $smTitle . '</small>',
					'desc' => $this->textPrepare($u['body']),
					'time' => $u['time'],
					'icon' => $this->func->img($u['fs_photo'], 50),
					'time_ts' => $u['time_ts']
				];
			}

			return $out;
		}

		return false;
	}

	public function loadMailboxUpdates($page = 0, $hidden_ids = false)
	{
		if ($boxes = $this->mailboxModel->getBoxes()) {
			$mb_ids = array();
			foreach ($boxes as $b) {
				if (!isset($hidden_ids[$b['id']])) {
					$mb_ids[] = $b['id'];
				}
			}

			if (count($mb_ids) === 0) {
				return false;
			}

			if ($updates = $this->activityGateway->fetchAllMailboxUpdates($mb_ids, $page)) {
				$out = array();
				foreach ($updates as $u) {
					$sender = @json_decode($u['sender'], true);

					$from = 'E-Mail';

					if ($sender !== null) {
						if (isset($sender['from']) && !empty($sender['from'])) {
							$from = '<a title="' . $sender['mailbox'] . '@' . $sender['host'] . '" href="/?page=mailbox&mailto=' . urlencode($sender['mailbox'] . '@' . $sender['host']) . '">' . $this->func->ttt($sender['personal'], 22) . '</a>';
						} elseif (isset($sender['mailbox'])) {
							$from = '<a title="' . $sender['mailbox'] . '@' . $sender['host'] . '" href="/?page=mailbox&mailto=' . urlencode($sender['mailbox'] . '@' . $sender['host']) . '">' . $this->func->ttt($sender['mailbox'] . '@' . $sender['host'], 22) . '</a>';
						}
					}

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
						],
						'attr' => [
							'href' => '/?page=mailbox&show=' . $u['id']
						],
						'title' => $from . ' <i class="fas fa-angle-right"></i> <a href="/?page=mailbox&show=' . $u['id'] . '">' . $this->func->ttt($u['subject'], 30) . '</a><small>' . $this->func->ttt($u['mb_name'] . '@' . PLATFORM_MAILBOX_HOST, 19) . '</small>',
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

		return false;
	}

	public function loadForumUpdates($page = 0, $bids_not_load = false)
	{
		$tmp = $this->session->listRegionIDs();
		$bids = array();
		if ($tmp === false || count($tmp) === 0) {
			return false;
		}

		foreach ($tmp as $t) {
			if ($t > 0 && !isset($bids_not_load[$t])) {
				$bids[] = $t;
			}
		}

		if (count($bids) === 0) {
			return false;
		}

		if ($updates = $this->activityGateway->fetchAllForumUpdates($bids, $page)
		) {
			$out = array();
			foreach ($updates as $u) {
				$check = true;
				$sub = 'forum';
				if ($u['bot_theme'] === 1) {
					$sub = 'botforum';
					if (!$this->session->isAdminFor($u['bezirk_id'])) {
						$check = false;
					}
				}

				$url = '/?page=bezirk&bid=' . (int)$u['bezirk_id'] . '&sub=' . $sub . '&tid=' . (int)$u['id'] . '&pid=' . (int)$u['last_post_id'] . '#tpost-' . (int)$u['last_post_id'];

				if ($check) {
					$out[] = [
						'type' => 'forum',
						'data' => [
							'fs_id' => (int)$u['foodsaver_id'],
							'fs_name' => $u['foodsaver_name'],
							'forum_href' => $url,
							'forum_name' => $u['name'],
							'region_name' => $u['bezirk_name'],
							'desc' => $u['post_body'],
							'time' => $u['update_time'],
							'icon' => $this->func->img($u['foodsaver_photo'], 50),
							'time_ts' => $u['update_time_ts'],
							'quickreply' => '/xhrapp.php?app=bezirk&m=quickreply&bid=' . (int)$u['bezirk_id'] . '&tid=' . (int)$u['id'] . '&pid=' . (int)$u['last_post_id'] . '&sub=' . $sub
						],
						'attr' => [
							'href' => $url
						],
						'title' => '<a href="/profile/' . (int)$u['foodsaver_id'] . '">' . $u['foodsaver_name'] . '</a> <i class="fas fa-angle-right"></i> <a href="' . $url . '">' . $u['name'] . '</a> <small>' . $u['bezirk_name'] . '</small>',
						'desc' => $this->textPrepare($u['post_body']),
						'time' => $u['update_time'],
						'icon' => $this->func->img($u['foodsaver_photo'], 50),
						'time_ts' => $u['update_time_ts'],
						'quickreply' => '/xhrapp.php?app=bezirk&m=quickreply&bid=' . (int)$u['bezirk_id'] . '&tid=' . (int)$u['id'] . '&pid=' . (int)$u['last_post_id'] . '&sub=' . $sub
					];
				}
			}

			return $out;
		}

		return false;
	}

	public function loadStoreUpdates($page = 0)
	{
		if ($this->session->getMyBetriebIds() && $ret = $this->activityGateway->fetchAllStoreUpdates($this->session->id(), $page)) {
			$out = array();
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
						'icon' => $this->func->img($r['foodsaver_photo'], 50),
						'time_ts' => $r['update_time_ts']
					],
					'attr' => [
						'href' => '/?page=fsbetrieb&id=' . $r['betrieb_id']
					],
					'title' => '<a href="/profile/' . $r['foodsaver_id'] . '">' . $r['foodsaver_name'] . '</a> <i class="fas fa-angle-right"></i> <a href="/?page=fsbetrieb&id=' . $r['betrieb_id'] . '">' . $r['betrieb_name'] . '</a>',
					'desc' => $this->textPrepare($r['text']),
					'time' => $r['update_time'],
					'icon' => $this->func->img($r['foodsaver_photo'], 50),
					'time_ts' => $r['update_time_ts']
				];
			}

			return $out;
		}

		return false;
	}

	public function getBuddies()
	{
		if ($bids = $this->session->get('buddy-ids')) {
			return $this->activityGateway->fetchAllBuddies($bids);
		}

		return false;
	}
}
