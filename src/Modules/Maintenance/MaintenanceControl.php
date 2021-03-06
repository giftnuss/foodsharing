<?php

namespace Foodsharing\Modules\Maintenance;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\BellUpdateTrigger;
use Foodsharing\Modules\Console\ConsoleControl;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Group\GroupGateway;
use Foodsharing\Modules\Quiz\QuizHelper;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Utility\EmailHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class MaintenanceControl extends ConsoleControl
{
	private BellGateway $bellGateway;
	private StoreGateway $storeGateway;
	private FoodsaverGateway $foodsaverGateway;
	private EmailHelper $emailHelper;
	private MaintenanceGateway $maintenanceGateway;
	private QuizHelper $quizHelper;
	private BellUpdateTrigger $bellUpdateTrigger;
	private GroupGateway $groupGateway;
	private TranslatorInterface $translator;

	public function __construct(
		BellGateway $bellGateway,
		StoreGateway $storeGateway,
		FoodsaverGateway $foodsaverGateway,
		EmailHelper $emailHelper,
		MaintenanceGateway $maintenanceGateway,
		QuizHelper $quizHelper,
		BellUpdateTrigger $bellUpdateTrigger,
		GroupGateway $groupGateway,
		TranslatorInterface $translator
	) {
		$this->bellGateway = $bellGateway;
		$this->storeGateway = $storeGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->emailHelper = $emailHelper;
		$this->maintenanceGateway = $maintenanceGateway;
		$this->quizHelper = $quizHelper;
		$this->bellUpdateTrigger = $bellUpdateTrigger;
		$this->groupGateway = $groupGateway;
		$this->translator = $translator;

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
		$this->rebuildRegionClosure();

		/*
		 * Master Bezirk Update
		 *
		 * we have master bezirk that mean any user hierarchical under this bezirk have to be also in master self
		 */
		$this->masterBezirkUpdate();

		/*
		 * Delete old blocked ips
		 */
		$this->deleteOldIpBlocks();

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

	public function rebuildRegionClosure()
	{
		self::info('rebuilding region closure...');
		$this->groupGateway->recreateClosure();
		self::success('OK');
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

		self::info('updating Z??rich BIEB group');
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

		self::info('updating Voting Admin group');
		$votingAdmins = $this->foodsaverGateway->getWorkgroupFunctionAdminIds(WorkgroupFunction::VOTING);
		$counts = $this->foodsaverGateway->updateGroupMembers(RegionIDs::VOTING_ADMIN_GROUP, $votingAdmins, true);
		self::info('+' . $counts['inserts'] . ', -' . $counts['deletions']);

		self::info('updating orga Admin group');
		$orga = $this->foodsaverGateway->getOrgaTeamId();
		$counts = $this->foodsaverGateway->updateGroupMembers(RegionIDs::ORGA_COORDINATION_GROUP, array_column($orga, 'id'), true);
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
		if ($foodsaver = $this->maintenanceGateway->listUsersWithPhoto()) {
			$update = [];
			foreach ($foodsaver as $fs) {
				if (!str_starts_with($fs['photo'], '/api/uploads')) {
					if (!file_exists('images/' . $fs['photo'])) {
						$update[] = $fs['id'];
					}
				}
			}
			if (!empty($update)) {
				$this->maintenanceGateway->unsetUserPhotos($update);
			}
		}
		$check = [];
		if ($foodsaver = $this->maintenanceGateway->listUsersWithPhoto()) {
			foreach ($foodsaver as $fs) {
				if (!str_starts_with('/api/uploads', $fs['photo'])) {
					$check[$fs['photo']] = $fs['id'];
				}
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
		$this->maintenanceGateway->masterRegionUpdate();
		self::success('OK');
	}

	public function betriebFetchWarning()
	{
		if ($foodsaver = $this->maintenanceGateway->getStoreManagersWhichWillBeAlerted()) {
			self::info('send ' . count($foodsaver) . ' warnings...');
			foreach ($foodsaver as $fs) {
				$this->emailHelper->tplMail('chat/fetch_warning', $fs['fs_email'], [
					'anrede' => $this->translator->trans('salutation.' . $fs['geschlecht']),
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

	private function deleteOldIpBlocks()
	{
		self::info('deleting old blocked IPs...');
		$count = $this->maintenanceGateway->deleteOldIpBlocks();
		self::success($count . ' entries deleted');
	}
}
