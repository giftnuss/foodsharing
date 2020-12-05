<?php

namespace Foodsharing\Modules\Store;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\BellUpdaterInterface;
use Foodsharing\Modules\Bell\BellUpdateTrigger;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;

class PickupGateway extends BaseGateway implements BellUpdaterInterface
{
	private BellGateway $bellGateway;

	public function __construct(
		Database $db,
		BellGateway $bellGateway,
		BellUpdateTrigger $bellUpdateTrigger
	) {
		parent::__construct($db);

		$this->bellGateway = $bellGateway;

		$bellUpdateTrigger->subscribe($this);
	}

	public function addFetcher(int $fsId, int $storeId, \DateTime $date, bool $confirmed = false): int
	{
		$result = $this->db->insertIgnore('fs_abholer', [
			'foodsaver_id' => $fsId,
			'betrieb_id' => $storeId,
			'date' => $this->db->date($date),
			'confirmed' => $confirmed,
		]);

		if (!$confirmed) {
			$this->updateBellNotificationForStoreManagers($storeId, true);
		}

		return $result;
	}

	public function deleteAllDatesFromAFoodsaver(int $fs_id)
	{
		$storeIdsThatWillBeDeleted = $this->db->fetchAllValuesByCriteria(
			'fs_abholer',
			'betrieb_id',
			[
				'foodsaver_id' => $fs_id,
				'date >' => $this->db->now(),
			],
		);

		$result = $this->db->delete('fs_abholer', [
			'foodsaver_id' => $fs_id,
			'date >' => $this->db->now(),
		]);

		foreach ($storeIdsThatWillBeDeleted as $storeIdDel) {
			$this->updateBellNotificationForStoreManagers($storeIdDel);
		}

		return $result;
	}

	public function removeFetcher(int $fsId, int $storeId, \DateTime $date)
	{
		$deletedRows = $this->db->delete('fs_abholer', [
			'foodsaver_id' => $fsId,
			'betrieb_id' => $storeId,
			'date' => $this->db->date($date),
		]);
		$this->updateBellNotificationForStoreManagers($storeId);

		return $deletedRows;
	}

	/**
	 * @param bool $markNotificationAsUnread:
	 * if an older notification exists, that has already been marked as read,
	 * it can be marked as unread again while updating it
	 */
	public function updateBellNotificationForStoreManagers(int $storeId, bool $markNotificationAsUnread = false): void
	{
		$storeName = $this->getStoreName($storeId);
		$messageIdentifier = 'store-fetch-unconfirmed-' . $storeId;
		$messageCount = $this->getUnconfirmedFetchesCount($storeId);
		$messageVars = ['betrieb' => $storeName, 'count' => $messageCount];
		$messageTimestamp = $this->getNextUnconfirmedFetchTime($storeId);
		$messageExpiration = $messageTimestamp;

		$oldBellExists = $this->bellGateway->bellWithIdentifierExists($messageIdentifier);

		if ($messageCount === 0 && $oldBellExists) {
			$this->bellGateway->delBellsByIdentifier($messageIdentifier);
		} elseif ($messageCount > 0 && $oldBellExists) {
			$oldBellId = $this->bellGateway->getOneByIdentifier($messageIdentifier);
			$data = [
				'vars' => $messageVars,
				'time' => $messageTimestamp,
				'expiration' => $messageExpiration,
			];
			$this->bellGateway->updateBell($oldBellId, $data, $markNotificationAsUnread);
		} elseif ($messageCount > 0 && !$oldBellExists) {
			$bellData = Bell::create(
				'betrieb_fetch_title',
				'betrieb_fetch',
				'fas fa-user-clock',
				['href' => '/?page=fsbetrieb&id=' . $storeId],
				$messageVars,
				$messageIdentifier,
				false,
				$messageExpiration,
				$messageTimestamp
			);
			$this->bellGateway->addBell($this->getResponsibleFoodsaverIds($storeId), $bellData);
		}
	}

	public function updateExpiredBells(): void
	{
		$expiredBells = $this->bellGateway->getExpiredByIdentifier('store-fetch-unconfirmed-%');

		foreach ($expiredBells as $bell) {
			$storeId = substr($bell->identifier, strlen('store-fetch-unconfirmed-'));
			$this->updateBellNotificationForStoreManagers(intval($storeId));
		}
	}

	public function confirmFetcher(int $fsid, int $storeId, \DateTime $date): int
	{
		$result = $this->db->update(
			'fs_abholer',
			['confirmed' => 1],
			['foodsaver_id' => $fsid, 'betrieb_id' => $storeId, 'date' => $this->db->date($date)]
		);

		$this->updateBellNotificationForStoreManagers($storeId);

		return $result;
	}

	public function getAbholzeiten(int $storeId): array
	{
		$times = $this->db->fetchAll('
			SELECT `time`, `dow`, `fetcher`
			FROM `fs_abholzeiten`
			WHERE `betrieb_id` = :storeId
		', [':storeId' => $storeId]);

		if (!$times) {
			return [];
		}

		$result = [];
		foreach ($times as $r) {
			$result[$r['dow'] . '-' . $r['time']] = [
				'dow' => $r['dow'],
				'time' => $r['time'],
				'fetcher' => $r['fetcher'],
			];
		}

		ksort($result);

		return $result;
	}

	public function getPickupSignupsForDate(int $storeId, \DateTime $date)
	{
		return $this->getPickupSignupsForDateRange($storeId, $date, $date);
	}

	public function getPickupSignupsForDateRange(int $storeId, \DateTime $from, ?\DateTime $to = null)
	{
		$condition = ['date >=' => $this->db->date($from), 'betrieb_id' => $storeId];
		if (!is_null($to)) {
			$condition['date <='] = $this->db->date($to);
		}
		$result = $this->db->fetchAllByCriteria(
			'fs_abholer',
			['foodsaver_id', 'date', 'confirmed'],
			$condition
		);

		return array_map(function ($e) {
			$e['date'] = $this->db->parseDate($e['date']);

			return $e;
		}, $result);
	}

	public function getPickupHistory(int $storeId, \DateTime $from, \DateTime $to): array
	{
		return $this->db->fetchAll('
			SELECT	a.foodsaver_id AS foodsaverId,
					a.confirmed,
					a.date,
					UNIX_TIMESTAMP(a.date) AS date_ts

			FROM	fs_abholer a

			WHERE	a.betrieb_id = :storeId
			AND     a.date >= :from
			AND     a.date <= :to

			ORDER BY a.date
		', [
			':storeId' => $storeId,
			':from' => $this->db->date($from),
			':to' => $this->db->date($to),
		]);
	}

	public function getRegularPickups(int $storeId)
	{
		return $this->db->fetchAllByCriteria('fs_abholzeiten',
			['time', 'dow', 'fetcher'],
			['betrieb_id' => $storeId]
		);
	}

	public function getOnetimePickups(int $storeId, \DateTime $date)
	{
		return $this->getOnetimePickupsForRange($storeId, $date, $date);
	}

	public function getOnetimePickupsForRange(int $storeId, \DateTime $from, ?\DateTime $to)
	{
		$condition = [
			'betrieb_id' => $storeId,
			'time >=' => $this->db->date($from),
		];
		if ($to) {
			$condition = array_merge($condition, ['time <=' => $this->db->date($to)]);
		}
		$result = $this->db->fetchAllByCriteria('fs_fetchdate', ['time', 'fetchercount'], $condition);

		return array_map(function ($e) {
			return [
				'date' => $this->db->parseDate($e['time']),
				'fetcher' => $e['fetchercount'],
			];
		}, $result);
	}

	public function addOnetimePickup(int $storeId, \DateTime $date, int $slots)
	{
		$this->db->insert('fs_fetchdate', [
			'betrieb_id' => $storeId,
			'time' => $this->db->date($date),
			'fetchercount' => $slots,
		]);
	}

	public function updateOnetimePickupTotalSlots(int $storeId, \DateTime $date, int $slots): bool
	{
		return $this->db->update('fs_fetchdate',
			['fetchercount' => $slots],
			['betrieb_id' => $storeId, 'time' => $this->db->date($date)]
		) === 1;
	}

	private function getFutureRegularPickupInterval(int $storeId): CarbonInterval
	{
		$result = $this->db->fetchValueByCriteria('fs_betrieb', 'prefetchtime', ['id' => $storeId]);

		return CarbonInterval::seconds($result);
	}

	private function getNextUnconfirmedFetchTime(int $storeId): \DateTime
	{
		$date = $this->db->fetchValue('
			SELECT  MIN(`date`)
		
			FROM    `fs_abholer`
		
			WHERE   `betrieb_id` = :storeId
			AND     `confirmed` = 0
			AND     `date` > :date
		', [
			':storeId' => $storeId,
			':date' => $this->db->now(),
		]);

		return new \DateTime($date);
	}

	private function getUnconfirmedFetchesCount(int $storeId)
	{
		return $this->db->count('fs_abholer', ['betrieb_id' => $storeId, 'confirmed' => 0, 'date >' => $this->db->now()]);
	}

	/**
	 * @param Carbon $from DateRange start for all slots. Now if empty.
	 * @param Carbon $to DateRange for regular slots - future pickup interval if empty
	 * @param Carbon $oneTimeSlotTo DateRange for onetime slots to be taken into account
	 */
	public function getPickupSlots(int $storeId, ?Carbon $from = null, ?Carbon $to = null, ?Carbon $oneTimeSlotTo = null): array
	{
		$intervalFuturePickupSignup = $this->getFutureRegularPickupInterval($storeId);
		$from = $from ?? Carbon::now();
		$extendedToDate = Carbon::tomorrow()->add($intervalFuturePickupSignup);
		$to = $to ?? $extendedToDate;
		$regularSlots = $this->getRegularPickups($storeId);
		$onetimeSlots = $this->getOnetimePickupsForRange($storeId, $from, $oneTimeSlotTo);
		$signupsTo = is_null($oneTimeSlotTo) ? null : max($to, $oneTimeSlotTo);
		$signups = $this->getPickupSignupsForDateRange($storeId, $from, $signupsTo);

		$slots = [];
		foreach ($regularSlots as $slot) {
			$date = $from->copy();
			$date->addDays($this->realMod($slot['dow'] - $date->format('w'), 7));
			$date->setTimeFromTimeString($slot['time'])->shiftTimezone('Europe/Berlin');
			if ($date < $from) {
				/* setting time could shift it into past */
				$date->addDays(7);
			}
			while ($date <= $to) {
				if (empty(array_filter($onetimeSlots, function ($e) use ($date) {
					return $date == $e['date'];
				}))) {
					/* only take this regular slot into account when there is no manual slot for the same time */
					$occupiedSlots = array_map(
						function ($e) {
							return ['foodsaverId' => $e['foodsaver_id'], 'isConfirmed' => (bool)$e['confirmed']];
						},
						array_filter($signups,
							function ($e) use ($date) {
								return $date == $e['date'];
							}
						)
					);
					$isAvailable =
						$date > Carbon::now() &&
						$date <= $extendedToDate &&
						$slot['fetcher'] > count($occupiedSlots);
					$slots[] = [
						'date' => $date,
						'totalSlots' => $slot['fetcher'],
						'occupiedSlots' => array_values($occupiedSlots),
						'isAvailable' => $isAvailable,
					];
				}

				$date = $date->copy()->addDays(7);
			}
		}
		foreach ($onetimeSlots as $slot) {
			$occupiedSlots = array_map(
				function ($e) {
					return ['foodsaverId' => $e['foodsaver_id'], 'isConfirmed' => (bool)$e['confirmed']];
				},
				array_filter($signups,
					function ($e) use ($slot) {
						return $slot['date'] == $e['date'];
					}
				)
			);
			if ($slot['fetcher'] === 0 && count($occupiedSlots) === 0) {
				/* Do not display empty/cancelled pickups.
				Do show them, when somebody is signed up (although this should not happen) */
				continue;
			}
			/* Onetime slots are always in the future available for signups */
			$isInFuture = $slot['date'] > Carbon::now();
			$hasFree = $slot['fetcher'] > count($occupiedSlots);

			$slots[] = [
				'date' => $slot['date'],
				'totalSlots' => $slot['fetcher'],
				'occupiedSlots' => array_values($occupiedSlots),
				'isAvailable' => $isInFuture && $hasFree,
			];
		}

		return $slots;
	}

	private function realMod(int $a, int $b)
	{
		$res = $a % $b;
		if ($res < 0) {
			return $res += abs($b);
		}

		return $res;
	}

	private function getStoreName(int $storeId): string
	{
		return $this->db->fetchValueByCriteria('fs_betrieb', 'name', ['id' => $storeId]);
	}

	/**
	 * @return int[]
	 */
	private function getResponsibleFoodsaverIds(int $storeId): array
	{
		return $this->db->fetchAllValuesByCriteria('fs_betrieb_team', 'foodsaver_id', [
			'betrieb_id' => $storeId,
			'verantwortlich' => 1
		]);
	}
}
