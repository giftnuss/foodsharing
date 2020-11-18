<?php

namespace Foodsharing\Modules\Activity;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Activity\DTO\ActivityFilter;
use Foodsharing\Modules\Activity\DTO\ActivityFilterCategory;
use Foodsharing\Modules\Activity\DTO\ActivityUpdateBuddy as BuddyUpdate;
use Foodsharing\Modules\Activity\DTO\ActivityUpdateEvent as EventUpdate;
use Foodsharing\Modules\Activity\DTO\ActivityUpdateFoodsharepoint as FspUpdate;
use Foodsharing\Modules\Activity\DTO\ActivityUpdateForum as ForumUpdate;
use Foodsharing\Modules\Activity\DTO\ActivityUpdateMailbox as MailboxUpdate;
use Foodsharing\Modules\Activity\DTO\ActivityUpdateStore as StoreUpdate;
use Foodsharing\Modules\Activity\DTO\ImageActivityFilter;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Utility\ImageHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class ActivityTransactions
{
	private ActivityGateway $activityGateway;
	private MailboxGateway $mailboxGateway;
	private ImageHelper $imageHelper;
	private TranslatorInterface $translator;
	private Session $session;

	public function __construct(
		ActivityGateway $activityGateway,
		MailboxGateway $mailboxGateway,
		ImageHelper $imageHelper,
		TranslatorInterface $translator,
		Session $session
	) {
		$this->activityGateway = $activityGateway;
		$this->mailboxGateway = $mailboxGateway;
		$this->imageHelper = $imageHelper;
		$this->translator = $translator;
		$this->session = $session;
	}

	/**
	 * Returns all activity filters for the logged in user sorted into categories.
	 *
	 * @return array ActivityFilterCategory[]
	 */
	public function getFilters(): array
	{
		// list of currently excluded activities
		$excluded = $this->session->getOption('activity-listings') ?: [];

		// regions and groups
		$regionOptions = [];
		$groupOptions = [];
		if ($bezirke = $this->session->getRegions()) {
			foreach ($bezirke as $b) {
				$option = ActivityFilter::create($b['name'], $b['id'],
					!isset($excluded['bezirk-' . $b['id']])
				);
				if ($b['type'] == Type::WORKING_GROUP) {
					$groupOptions[] = $option;
				} else {
					$regionOptions[] = $option;
				}
			}
		}

		// mailboxes
		$mailboxOptions = [];
		if ($boxes = $this->mailboxGateway->getBoxes(
			$this->session->isAmbassador(),
			$this->session->id(),
			$this->session->may('bieb'))
		) {
			$mailboxOptions = array_map(function ($b) use ($excluded) {
				return ActivityFilter::create(
					$b['name'] . '@' . PLATFORM_MAILBOX_HOST, $b['id'],
					!isset($excluded['mailbox-' . $b['id']])
				);
			}, $boxes);
		}

		// buddy walls
		$buddyOptions = [];
		if ($buddyIds = $this->session->get('buddy-ids')) {
			$buddies = $this->activityGateway->fetchAllBuddies((array)$buddyIds);
			$buddyOptions = array_map(function ($b) use ($excluded) {
				return ImageActivityFilter::create(
					$b['name'], $b['id'], !isset($excluded['buddywall-' . $b['id']]),
					$this->imageHelper->img($b['photo'])
				);
			}, $buddies);
		}

		return [
			ActivityFilterCategory::create('bezirk', $this->translator->trans('search.mygroups'),
				$this->translator->trans('terminology.groups'), $groupOptions),
			ActivityFilterCategory::create('bezirk', $this->translator->trans('search.myregions'),
				$this->translator->trans('terminology.regions'), $regionOptions),
			ActivityFilterCategory::create('mailbox', $this->translator->trans('terminology.mailboxes'),
				$this->translator->trans('terminology.mailboxes'), $mailboxOptions),
			ActivityFilterCategory::create('buddywall', $this->translator->trans('search.mybuddies'),
				$this->translator->trans('terminology.buddies'), $buddyOptions)
		];
	}

	/**
	 * Sets the deactivated activities for the logged in user.
	 *
	 * @param array $excluded a list of activities to be deactivated. List entries should be objects with
	 * 'index' and 'id' entries
	 */
	public function setExcludedFilters(array $excluded): void
	{
		$list = [];
		foreach ($excluded as $o) {
			if (isset($o['index'], $o['id']) && (int)$o['id'] > 0) {
				$list[$o['index'] . '-' . $o['id']] = [
					'index' => $o['index'],
					'id' => $o['id']
				];
			}
		}

		$this->session->setOption('activity-listings', $list);
	}

	/**
	 * Returns a paginated list of dashboard update objects, filtered by excluding all undesired update sources.
	 *
	 * @param int $page Which page / chunk of updates to return
	 */
	public function getUpdateData(int $page): array
	{
		$hidden_ids = [
			'bezirk' => [],
			'mailbox' => [],
			'buddywall' => [],
		];

		// Store which update sources to skip, keyed by update type and entity ID
		if ($sesOptions = $this->session->getOption('activity-listings')) {
			foreach ($sesOptions as $o) {
				if (isset($hidden_ids[$o['index']])) {
					$hidden_ids[$o['index']][$o['id']] = $o['id'];
				}
			}
		}

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

			$out[] = EventUpdate::create(
				$u['time'], // TODO DateTime
				$u['time_ts'],
				$u['body'] ?? '',
				$replyUrl,
				$u['fs_id'],
				$u['fs_name'],
				$this->imageHelper->img($u['fs_photo'], 50),
				$u['event_region'],
				$u['gallery'] ?? [],
				$u['event_id'],
				$u['name']
			);
		}

		return $out;
	}

	private function loadFoodSharePointWallUpdates(int $page): array
	{
		$updates = $this->activityGateway->fetchAllFoodSharePointWallUpdates($this->session->id(), $page);
		$out = [];

		foreach ($updates as $u) {
			$out[] = FspUpdate::create(
				$u['time'], // TODO DateTime
				$u['time_ts'],
				$u['body'] ?? '',
				$u['fs_id'],
				$u['fs_name'],
				$u['region_id'],
				$this->imageHelper->img($u['fs_photo'], 50),
				$u['fsp_location'],
				$u['gallery'] ?? [],
				$u['fsp_id'],
				$u['name']
			);
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

		$updates = $this->activityGateway->fetchAllFriendWallUpdates($bids, $page);
		if (empty($updates)) {
			return [];
		}

		$out = [];
		foreach ($updates as $u) {
			$is_own = $u['fs_id'] === $this->session->id();

			$out[] = BuddyUpdate::create(
				$u['time'], // TODO DateTime
				$u['time_ts'],
				$u['body'] ?? '',
				$u['fs_id'],
				$u['fs_name'],
				$this->imageHelper->img($u['fs_photo'], 50),
				$u['gallery'] ?? [],
				$is_own
			);
		}

		return $out;
	}

	private function loadMailboxUpdates(int $page, array $hidden_ids): array
	{
		$boxes = $this->mailboxGateway->getBoxes(
			$this->session->isAmbassador(),
			$this->session->id(),
			$this->session->may('bieb')
		);

		if (empty($boxes)) {
			return [];
		}

		$mb_ids = [];
		foreach ($boxes as $b) {
			if (!isset($hidden_ids[$b['id']])) {
				$mb_ids[] = $b['id'];
			}
		}

		if (count($mb_ids) === 0) {
			return [];
		}

		$updates = $this->activityGateway->fetchAllMailboxUpdates($mb_ids, $page);

		if (empty($updates)) {
			return [];
		}

		$out = [];
		foreach ($updates as $u) {
			$sender = json_decode($u['sender'], true, 512, JSON_THROW_ON_ERROR + JSON_INVALID_UTF8_IGNORE);
			$replyUrl = '/xhrapp.php?app=mailbox&m=quickreply&mid=' . (int)$u['id'];

			$out[] = MailboxUpdate::create(
				$u['time'], // TODO DateTime
				$u['time_ts'],
				$u['body'] ?? '',
				$replyUrl,
				$u['mb_name'] . '@' . PLATFORM_MAILBOX_HOST,
				$u['id'],
				$u['subject'],
				$sender['mailbox'] . '@' . $sender['host']
			);
		}

		return $out;
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

		if (empty($updates)) {
			return [];
		}

		$out = [];
		foreach ($updates as $u) {
			$is_bot = $u['bot_theme'] === 1;

			$forumTypeString = $is_bot ? 'botforum' : 'forum';

			$replyUrl = '/xhrapp.php?app=bezirk&m=quickreply&bid=' . (int)$u['bezirk_id']
				. '&tid=' . (int)$u['id']
				. '&pid=' . (int)$u['last_post_id']
				. '&sub=' . $forumTypeString;

			$out[] = [
				'data' => ForumUpdate::create(
					$u['update_time'], // TODO DateTime
					$u['update_time_ts'],
					$u['post_body'] ?? '',
					$replyUrl,
					(int)$u['foodsaver_id'],
					$u['foodsaver_name'],
					$this->imageHelper->img($u['foodsaver_photo'], 50),
					$u['bezirk_name'],
					(int)$u['bezirk_id'],
					(int)$u['id'],
					(int)$u['last_post_id'],
					$u['name'],
					$forumTypeString,
					$is_bot
				),
			];
		}

		return $out;
	}

	private function loadStoreUpdates(int $page): array
	{
		$updates = $this->activityGateway->fetchAllStoreUpdates($this->session->id(), $page);
		if (empty($updates)) {
			return [];
		}

		$out = [];
		foreach ($updates as $u) {
			$out[] = StoreUpdate::create(
				$u['update_time'], // TODO DateTime
				$u['update_time_ts'],
				$u['text'] ?? '',
				$u['foodsaver_id'],
				$u['foodsaver_name'],
				$this->imageHelper->img($u['foodsaver_photo'], 50),
				$u['region_name'],
				$u['betrieb_id'],
				$u['betrieb_name']
			);
		}

		return $out;
	}
}
