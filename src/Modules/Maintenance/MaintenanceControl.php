<?php

namespace Foodsharing\Modules\Maintenance;

use Foodsharing\Helpers\EmailHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\BellUpdateTrigger;
use Foodsharing\Modules\Console\ConsoleControl;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Quiz\QuizHelper;
use Foodsharing\Modules\Store\StoreGateway;

class MaintenanceControl extends ConsoleControl
{
	private $model;
	private $bellGateway;
	private $storeGateway;
	private $foodsaverGateway;
	private $emailHelper;
	private $translationHelper;
	private $maintenanceGateway;
	private $quizHelper;
	private $bellUpdateTrigger;

	public function __construct(
		MaintenanceModel $model,
		BellGateway $bellGateway,
		StoreGateway $storeGateway,
		FoodsaverGateway $foodsaverGateway,
		EmailHelper $emailHelper,
		TranslationHelper $translationHelper,
		MaintenanceGateway $maintenanceGateway,
		QuizHelper $quizHelper,
		BellUpdateTrigger $bellUpdateTrigger
	) {
		$this->model = $model;
		$this->bellGateway = $bellGateway;
		$this->storeGateway = $storeGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->emailHelper = $emailHelper;
		$this->translationHelper = $translationHelper;
		$this->maintenanceGateway = $maintenanceGateway;
		$this->quizHelper = $quizHelper;
		$this->bellUpdateTrigger = $bellUpdateTrigger;

		parent::__construct();
	}

	public function warnings()
	{
		$this->betriebFetchWarning();
	}

	public function daily()
	{
		/*
		 * warn store manager if there are no fetching people
		 */
		$this->betriebFetchWarning();

		/*
		 * fill memcache with info about users if they want information mails etc.
		 */
		$this->memcacheUserInfo();

		/*
		 * delete unused images
		 */
		$this->deleteImages();

		/*
		 * delete unconfirmed store dates in the past
		 */
		$this->deleteUnconfirmedFetchDates();

		/*
		 * deactivate too old food baskets
		 */
		$this->deactivateBaskets();

		/*
		 * Update Bezirk closure table
		 *
		 * it gets crashed by some updates sometimes, workaround: Rebuild every day
		 */
		$this->rebuildBezirkClosure();

		/*
		 * Master Bezirk Update
		 *
		 * we have master bezirk that mean any user hierarchical under this bezirk have to be also in master self
		 */
		$this->masterBezirkUpdate();

		/*
		 * Delete old blocked ips
		 */
		$this->model->deleteOldIpBlocks();

		/*
		 * There may be some groups where people should automatically be added
		 * (e.g. Hamburgs BIEB group)
		 */
		$this->updateSpecialGroupMemberships();

		/*
		 * sleeping users, where the time period of sleepiness ended
		 */
		$this->wakeupSleepingUsers();

		/*
		 * updates outdated bells with passed expiration date
		 */
		$this->bellUpdateTrigger->triggerUpdate();
	}

	public function rebuildBezirkClosure()
	{
		$this->model->sql('DELETE FROM fs_bezirk_closure');
		$this->model->sql('INSERT INTO fs_bezirk_closure (bezirk_id, ancestor_id, depth) SELECT a.id, a.id, 0 FROM fs_bezirk AS a WHERE a.parent_id > 0');
		$this->model->sql('INSERT INTO fs_bezirk_closure (bezirk_id, ancestor_id, depth) SELECT a.bezirk_id, b.parent_id, a.depth+1 FROM fs_bezirk_closure AS a JOIN fs_bezirk AS b ON b.id = a.ancestor_id WHERE b.parent_id IS NOT NULL AND a.depth = 0');
		$this->model->sql('INSERT INTO fs_bezirk_closure (bezirk_id, ancestor_id, depth) SELECT a.bezirk_id, b.parent_id, a.depth+1 FROM fs_bezirk_closure AS a JOIN fs_bezirk AS b ON b.id = a.ancestor_id WHERE b.parent_id IS NOT NULL AND a.depth = 1');
		$this->model->sql('INSERT INTO fs_bezirk_closure (bezirk_id, ancestor_id, depth) SELECT a.bezirk_id, b.parent_id, a.depth+1 FROM fs_bezirk_closure AS a JOIN fs_bezirk AS b ON b.id = a.ancestor_id WHERE b.parent_id IS NOT NULL AND a.depth = 2');
		$this->model->sql('INSERT INTO fs_bezirk_closure (bezirk_id, ancestor_id, depth) SELECT a.bezirk_id, b.parent_id, a.depth+1 FROM fs_bezirk_closure AS a JOIN fs_bezirk AS b ON b.id = a.ancestor_id WHERE b.parent_id IS NOT NULL AND a.depth = 3');
		$this->model->sql('INSERT INTO fs_bezirk_closure (bezirk_id, ancestor_id, depth) SELECT a.bezirk_id, b.parent_id, a.depth+1 FROM fs_bezirk_closure AS a JOIN fs_bezirk AS b ON b.id = a.ancestor_id WHERE b.parent_id IS NOT NULL AND a.depth = 4');
		$this->model->sql('INSERT INTO fs_bezirk_closure (bezirk_id, ancestor_id, depth) SELECT a.bezirk_id, b.parent_id, a.depth+1 FROM fs_bezirk_closure AS a JOIN fs_bezirk AS b ON b.id = a.ancestor_id WHERE b.parent_id IS NOT NULL AND a.depth = 5');
	}

	private function updateSpecialGroupMemberships()
	{
		self::info('updating HH bieb austausch');
		$hh_biebs = $this->storeGateway->getStoreManagersOf(31);
		$hh_biebs[] = 3166;   // Gerard Roscoe
		$counts = $this->foodsaverGateway->updateGroupMembers(826, $hh_biebs, true);
		self::info('+' . $counts['inserts'] . ', -' . $counts['deletions']);

		self::info('updating Europe Bot group');
		$bots = $this->foodsaverGateway->getRegionAmbassadorIds(RegionIDs::EUROPE);
		$counts = $this->foodsaverGateway->updateGroupMembers(RegionIDs::EUROPE_BOT_GROUP, $bots, true);
		self::info('+' . $counts['inserts'] . ', -' . $counts['deletions']);

		self::info('updating berlin bieb austausch');
		$berlin_biebs = $this->storeGateway->getStoreManagersOf(47);
		$counts = $this->foodsaverGateway->updateGroupMembers(1057, $berlin_biebs, true);
		self::info('+' . $counts['inserts'] . ', -' . $counts['deletions']);

		self::info('updating Switzerland BOT group');
		$chBots = $this->foodsaverGateway->getRegionAmbassadorIds(RegionIDs::SWITZERLAND);
		$counts = $this->foodsaverGateway->updateGroupMembers(RegionIDs::SWITZERLAND_BOT_GROUP, $chBots, true);
		self::info('+' . $counts['inserts'] . ', -' . $counts['deletions']);

		self::info('updating Austria BOT group');
		$aBots = $this->foodsaverGateway->getRegionAmbassadorIds(RegionIDs::AUSTRIA);
		$counts = $this->foodsaverGateway->updateGroupMembers(RegionIDs::AUSTRIA_BOT_GROUP, $aBots, true);
		self::info('+' . $counts['inserts'] . ', -' . $counts['deletions']);

		self::info('updating Zürich BIEB group');
		$zuerich_biebs = $this->storeGateway->getStoreManagersOf(108);
		$counts = $this->foodsaverGateway->updateGroupMembers(1313, $zuerich_biebs, true);
		self::info('+' . $counts['inserts'] . ', -' . $counts['deletions']);

		self::info('updating Wien BIEB group');
		$wien_biebs = $this->storeGateway->getStoreManagersOf(13);
		$counts = $this->foodsaverGateway->updateGroupMembers(707, $wien_biebs, true);
		self::info('+' . $counts['inserts'] . ', -' . $counts['deletions']);

		self::info('updating Graz BIEB group');
		$graz_biebs = $this->storeGateway->getStoreManagersOf(149);
		$counts = $this->foodsaverGateway->updateGroupMembers(1655, $graz_biebs, true);
		self::info('+' . $counts['inserts'] . ', -' . $counts['deletions']);
	}

	private function deactivateBaskets()
	{
		$count = $this->maintenanceGateway->deactivateOldBaskets();
		self::info($count . ' old foodbaskets deactivated');
	}

	private function deleteUnconfirmedFetchDates()
	{
		self::info('delete unconfirmed fetchdates...');
		$count = $this->maintenanceGateway->deleteUnconfirmedFetchDates();
		self::success($count . ' deleted');
	}

	private function deleteImages()
	{
		@unlink('images/.jpg');
		@unlink('images/.png');

		/* foodsaver photos */
		if ($foodsaver = $this->model->q('SELECT id, photo FROM fs_foodsaver WHERE photo != ""')) {
			$update = [];
			foreach ($foodsaver as $fs) {
				if (!file_exists('images/' . $fs['photo'])) {
					$update[] = $fs['id'];
				}
			}
			if (!empty($update)) {
				$this->model->update('UPDATE fs_foodsaver SET photo = "" WHERE id IN(' . implode(',', $update) . ')');
			}
		}
		$check = [];
		if ($foodsaver = $this->model->q('SELECT id, photo FROM fs_foodsaver WHERE photo != ""')) {
			foreach ($foodsaver as $fs) {
				$check[$fs['photo']] = $fs['id'];
			}
			$dir = opendir('./images');
			$count = 0;
			while (($file = readdir($dir)) !== false) {
				if (strlen($file) > 3 && !is_dir('./images/' . $file)) {
					$cfile = $file;
					if (strpos($file, '_') !== false) {
						$cfile = explode('_', $file);
						$cfile = end($cfile);
					}
					if (!isset($check[$cfile])) {
						++$count;
						@unlink('./images/' . $file);
						@unlink('./images/130_q_' . $file);
						@unlink('./images/50_q_' . $file);
						@unlink('./images/med_q_' . $file);
						@unlink('./images/mini_q_' . $file);
						@unlink('./images/thumb_' . $file);
						@unlink('./images/thumb_crop_' . $file);
						@unlink('./images/q_' . $file);
					}
				}
			}
		}
	}

	private function memcacheUserInfo()
	{
		$admins = $this->foodsaverGateway->getAllWorkGroupAmbassadorIds();
		if (!$admins) {
			$admins = [];
		}
		$this->mem->set('all_global_group_admins', serialize($admins));
	}

	private function masterBezirkUpdate()
	{
		self::info('master bezirk update');
		/* Master Bezirke */
		if ($foodsaver = $this->model->q('
				SELECT
				b.`id`,
				b.`name`,
				b.`type`,
				b.`master`,
				hb.foodsaver_id

				FROM 	`fs_bezirk` b,
				`fs_foodsaver_has_bezirk` hb

				WHERE 	hb.bezirk_id = b.id
				AND 	b.`master` != 0
				AND 	hb.active = 1

		')
		) {
			foreach ($foodsaver as $fs) {
				if (!$this->model->qRow('SELECT bezirk_id FROM `fs_foodsaver_has_bezirk` WHERE foodsaver_id = ' . (int)$fs['foodsaver_id'] . ' AND bezirk_id = ' . $fs['master'])) {
					if ((int)$fs['master'] > 0) {
						$this->model->insert('
						INSERT INTO `fs_foodsaver_has_bezirk`
						(
							`foodsaver_id`,
							`bezirk_id`,
							`active`,
							`added`
						)
						VALUES
						(
							' . (int)$fs['foodsaver_id'] . ',
							' . (int)$fs['master'] . ',
							1,
							NOW()
						)
						');
					}
				}
			}
		}

		self::success('OK');
	}

	public function flushcache()
	{
		self::info('flush Page Cache...');

		$this->mem->ensureConnected();

		if ($keys = $this->mem->cache->getAllKeys()) {
			foreach ($keys as $key) {
				if (substr($key, 0, 3) == 'pc-') {
					$this->mem->del($key);
				}
			}
		}

		self::success('OK');
	}

	public function betriebFetchWarning()
	{
		if ($foodsaver = $this->model->getStoreManagersWhichWillBeAlerted()) {
			self::info('send ' . count($foodsaver) . ' warnings...');
			foreach ($foodsaver as $fs) {
				$this->emailHelper->tplMail('chat/fetch_warning', $fs['fs_email'], [
					'anrede' => $this->translationHelper->s('anrede_' . $fs['geschlecht']),
					'name' => $fs['fs_name'],
					'betrieb' => $fs['betrieb_name'],
					'link' => BASE_URL . '/?page=fsbetrieb&id=' . $fs['betrieb_id']
				]);
			}
			self::success('OK');
		}
	}

	private function wakeupSleepingUsers()
	{
		self::info('wake up sleeping users...');
		$count = $this->maintenanceGateway->wakeupSleepingUsers();
		self::success($count . ' users woken up');
	}
}
