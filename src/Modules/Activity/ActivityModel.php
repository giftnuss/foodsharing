<?php

namespace Foodsharing\Modules\Activity;

use Foodsharing\Modules\Core\Model;
use Foodsharing\Modules\Mailbox\MailboxModel;
use Foodsharing\Services\OutputSanitizerService;

class ActivityModel extends Model
{
	private $mailboxModel;
	private $activityGateway;
	private $outputSanitizerService;

	public function __construct(MailboxModel $mailboxModel, ActivityGateway $activityGateway, OutputSanitizerService $outputSanitzierService)
	{
		parent::__construct();
		$this->mailboxModel = $mailboxModel;
		$this->activityGateway = $activityGateway;
		$this->outputSanitizerService = $outputSanitzierService;
	}
	public function loadEventWallUpdates($page = 0)
	{
		$updates = array();
		if ($up = $this->activityGateway->fetchAllEventUpdates(S::id(), $page)) {
			$updates = $up;
		}

		if (!empty($updates))
			$out = array();

			foreach ($updates as $u) {
				/*
				 * quick fix later list all comments in a package
				*/
				if (isset($hb[$u['event_id']])) {
					continue;
				}
				$hb[$u['event_id']] = true;

				$smTitle = '';
				$title = 'Veranstaltung : ' . $u['name'];

				$out[] = [
					'attr' => [
						'href' => '/profile/' . $u['fs_id']
					],
					'title' => '<a href="/profile/' . $u['fs_id'] . '">' . $u['fs_name'] . '</a> <i class="fa fa-angle-right"></i> <a href="?page=event&id=' . $u['event_id'] . '">' . $title . '</a><small>' . $smTitle . '</small>',
					'desc' => $this->textPrepare(nl2br($u['body'])),
					'time' => $u['time'],
					'icon' => $this->func->img($u['fs_photo'], 50),
					'time_ts' => $u['time_ts'],
				];
			}

			return $out;
	}

	public function loadBasketWallUpdates($page = 0)
	{
		$updates = array();
		if ($up = $this->activityGateway->fetchAllBasketWallUpdates($this->session->id(), $page)) {
			$updates = $up;
		}

		if ($up = $this->activityGateway->fetchAllWallpostsFromFoodBasekts($this->session->id(), $page)) {
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
					'attr' => [
						'href' => '/profile/' . $u['fs_id']
					],
					'title' => '<a href="/profile/' . $u['fs_id'] . '">' . $u['fs_name'] . '</a> <i class="fa fa-angle-right"></i> <a href="/essenskoerbe/' . $u['basket_id'] . '">' . $title . '</a><small>' . $smTitle . '</small>',
					'desc' => $this->textPrepare(nl2br($u['body'])),
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
		$sanitized = $this->outputSanitizerService->sanitizeForHtml($txt);

		if (strlen($txt) > 100) {
			return '<span class="txt">' . $this->outputSanitizerService->sanitizeForHtml($this->func->tt($txt, 90)) . ' <a href="#" onclick="$(this).parent().hide().next().show();return false;">alles zeigen <i class="fa fa-angle-down"></i></a></span><span class="txt" style="display:none;">' . $sanitized . ' <a href="#" onclick="$(this).parent().hide().prev().show();return false;">weniger <i class="fa fa-angle-up"></i></a></span>';
		}

		return '<span class="txt">' . $sanitized . '</span>';
	}

	public function loadFriendWallUpdates($hidden_ids, $page = 0)
	{
		$buddy_ids = array();

		if ($b = $this->session->get('buddy-ids')) {
			$buddy_ids = $b;
		}

		$buddy_ids[$this->func->fsId()] = $this->func->fsId();

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

				if ($u['fs_id'] === $this->func->fsId()) {
					$smTitle = 'Deine Pinnwand';
				}

				$out[] = [
					'attr' => [
						'href' => '/profile/' . $u['fs_id']
					],
					'title' => '<a href="/profile/' . $u['poster_id'] . '">' . $u['poster_name'] . '</a> <small>' . $smTitle . '</small>',
					'desc' => $this->textPrepare(nl2br($u['body'])),
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
						'attr' => [
							'href' => '/?page=mailbox&show=' . $u['id']
						],
						'title' => $from . ' <i class="fa fa-angle-right"></i> <a href="/?page=mailbox&show=' . $u['id'] . '">' . $this->func->ttt($u['subject'], 30) . '</a><small>' . $this->func->ttt($u['mb_name'] . '@' . DEFAULT_EMAIL_HOST, 19) . '</small>',
						'desc' => $this->textPrepare(nl2br($u['body'])),
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
		$tmp = $this->session->getRegionIds();
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
					if (!$this->func->isBotFor($u['bezirk_id'])) {
						$check = false;
					}
				}

				$url = '/?page=bezirk&bid=' . (int)$u['bezirk_id'] . '&sub=' . $sub . '&tid=' . (int)$u['id'] . '&pid=' . (int)$u['last_post_id'] . '#tpost-' . (int)$u['last_post_id'];

				if ($check) {
					$out[] = [
						'attr' => [
							'href' => $url
						],
						'title' => '<a href="/profile/' . (int)$u['foodsaver_id'] . '">' . $u['foodsaver_name'] . '</a> <i class="fa fa-angle-right"></i> <a href="' . $url . '">' . $u['name'] . '</a> <small>' . $u['bezirk_name'] . '</small>',
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
					'attr' => [
						'href' => '/?page=fsbetrieb&id=' . $r['betrieb_id']
					],
					'title' => '<a href="/profile/' . $r['foodsaver_id'] . '">' . $r['foodsaver_name'] . '</a> <i class="fa fa-angle-right"></i> <a href="/?page=fsbetrieb&id=' . $r['betrieb_id'] . '">' . $r['betrieb_name'] . '</a>',
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
