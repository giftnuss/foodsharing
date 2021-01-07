<?php

namespace Foodsharing\Modules\StoreUser;

use Carbon\Carbon;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\PickupGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\StoreModel;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\Sanitizer;
use Foodsharing\Utility\TimeHelper;
use Foodsharing\Utility\WeightHelper;

class StoreUserControl extends Control
{
	private $regionGateway;
	private $pickupGateway;
	private $storeGateway;
	private $storeModel;
	private $storePermissions;
	private $foodsaverGateway;
	private $dataHelper;
	private $sanitizerService;
	private $timeHelper;
	private $weightHelper;

	public function __construct(
		StoreUserView $view,
		RegionGateway $regionGateway,
		PickupGateway $pickupGateway,
		StoreGateway $storeGateway,
		StoreModel $model,
		StorePermissions $storePermissions,
		FoodsaverGateway $foodsaverGateway,
		DataHelper $dataHelper,
		Sanitizer $sanitizerService,
		TimeHelper $timeHelper,
		WeightHelper $weightHelper
	) {
		$this->view = $view;
		$this->regionGateway = $regionGateway;
		$this->pickupGateway = $pickupGateway;
		$this->storeGateway = $storeGateway;
		$this->storeModel = $model;
		$this->storePermissions = $storePermissions;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->dataHelper = $dataHelper;
		$this->sanitizerService = $sanitizerService;
		$this->timeHelper = $timeHelper;
		$this->weightHelper = $weightHelper;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->goLogin();
		}
	}

	public function index()
	{
		if (isset($_GET['id'])) {
			$storeId = intval($_GET['id']);
			$this->pageHelper->addBread($this->translator->trans('store.bread'), '/?page=fsbetrieb');
			global $g_data;

			$store = $this->storeGateway->getMyStore($this->session->id(), $storeId);

			if (!$store) {
				$this->routeHelper->goPage();
			}

			$this->pageHelper->jsData['store'] = [
				'id' => $storeId,
				'name' => $store['name'],
				'bezirk_id' => (int)$store['bezirk_id'],
				'verantwortlich' => $store['verantwortlich'],
				'prefetchtime' => $store['prefetchtime']
			];

			if (isset($_POST['form_submit']) && $_POST['form_submit'] == 'team' && $this->storePermissions->mayEditStore($storeId)) {
				$this->sanitizerService->handleTagSelect('storemanagers');
				if (!empty($g_data['storemanagers'])) {
					if (count($g_data['storemanagers']) > 3) {
						$this->flashMessageHelper->error($this->translator->trans('storeedit.team.max-sm'));
					} else {
						foreach ($g_data['storemanagers'] as $fsId) {
							$addedStoremanager = $this->storeGateway->addStoreManager($storeId, $fsId);
							$this->storeGateway->addStoreLog($storeId, $this->session->id(), $fsId, null, StoreLogAction::APPOINT_STORE_MANAGER);
						}
					}
				}

				$this->sanitizerService->handleTagSelect('foodsaver');
				if (!empty($g_data['foodsaver'])) {
					$addedTeam = $this->storeModel->addBetriebTeam($storeId, $g_data['foodsaver'], $g_data['verantwortlicher']);
				} elseif (empty($g_data['storemanagers'])) {
					$this->flashMessageHelper->info($this->translator->trans('storeedit.team.empty'));
				}
				if (isset($addedStoremanager) || isset($addedTeam)) {
					$this->flashMessageHelper->success($this->translator->trans('settings.saved'));
				}
				$this->routeHelper->goSelf();
			}

			$this->pageHelper->addTitle($store['name']);

			if ($this->storePermissions->mayAccessStore($storeId)) {
				if ((!$store['verantwortlich'] && $this->storePermissions->mayEditStore($storeId))) {
					$store['verantwortlich'] = true;

					$storeRegion = $this->storeGateway->getStoreRegionId($storeId);
					$storeGroup = $this->regionGateway->getRegionFunctionGroupId($storeRegion, WorkgroupFunction::STORES);
					if (empty($storeGroup)) {
						if ($this->session->isAdminFor($storeRegion)) {
							$this->flashMessageHelper->info(
								'<strong>' . $this->translator->trans('storeedit.team.note') . '</strong> '
								. $this->translator->trans('storeedit.team.amb'));
						}
					} elseif ($this->session->isAdminFor($storeGroup)) {
						$this->flashMessageHelper->info(
							'<strong>' . $this->translator->trans('storeedit.team.note') . '</strong> '
							. $this->translator->trans('storeedit.team.coordinator'));
					}
				} elseif (!$store['verantwortlich'] && $this->session->may('orga')) {
					$store['verantwortlich'] = true;
					$this->flashMessageHelper->info(
						'<strong>' . $this->translator->trans('storeedit.team.note') . '</strong> '
						. $this->translator->trans('storeedit.team.orga')
					);
				}

				$this->dataHelper->setEditData($store);

				$this->pageHelper->addBread($store['name']);

				$bibsaver = [];
				foreach ($store['foodsaver'] as $fs) {
					if ($fs['rolle'] >= Role::STORE_MANAGER) {
						$bibsaver[] = $fs;
					}
				}

				if ($store['verantwortlich']) {
					$checked = [];
					foreach ($store['foodsaver'] as $fs) {
						if ($fs['verantwortlich'] == 1) {
							$checked[] = $fs['id'];
						}
					}
					$verantwortlich_select = $this->v_utils->v_form_checkbox('verantwortlicher', ['values' => $bibsaver, 'checked' => $checked]);
					$valueOptions = $this->foodsaverGateway->xhrGetFoodsaversOfRegionsForTagSelect([$store['bezirk_id']]);

					$label = $this->translator->trans('storeedit.team.foodsaver');
					$elements = [
						$this->v_utils->v_form_tagselect('foodsaver', $label, $valueOptions),
						$verantwortlich_select,
					];

					if (empty($checked)) {
						$noStoreManagerWarning = $this->v_utils->v_error($this->translator->trans('storeedit.team.unmanaged'));
						$hiddenField = $this->v_utils->v_form_hidden('set_new_store_manager', 'true');
						$valueOptions = $this->foodsaverGateway->xhrGetStoremanagersOfRegionsForTagSelect([$store['bezirk_id']]);
						$elements = [
							$noStoreManagerWarning,
							$this->v_utils->v_form_tagselect('storemanagers', null, $valueOptions),
							$hiddenField,
						];
					}

					$edit_team = $this->v_utils->v_form(
						'team',
						$elements,
						['submit' => $this->translator->trans('button.save')]
					);

					$this->pageHelper->addHidden('<div id="teamEditor">' . $edit_team . '</div>');
				}

				/*Infos*/

				/* find yourself in the pickup list and show your last pickup date in store info */
				$lastFetchDate = null;
				foreach ($store['foodsaver'] as $fs) {
					if ($fs['id'] === $this->session->id() && $fs['last_fetch'] != null) {
						$lastFetchDate = Carbon::createFromTimestamp($fs['last_fetch']);
						break;
					}
				}

				$this->pageHelper->addContent($this->view->vueComponent('vue-storeinfos', 'store-infos', [
					'particularitiesDescription' => $store['besonderheiten'],
					'lastFetchDate' => $lastFetchDate,
					'street' => $store['str'],
					'housenumber' => $store['hsnr'],
					'postcode' => $store['plz'],
					'city' => $store['stadt'],
					'storeTitle' => $store['name'],
					'collectionQuantity' => $this->weightHelper->getFetchWeightName($store['abholmenge']),
					'press' => $store['presse'],
				]), CNT_RIGHT);

				/* options menu */
				$menu = [];

				if ($this->storePermissions->mayChatWithRegularTeam($store)) {
					$menu[] = [
						'name' => $this->translator->trans('store.chat.team'),
						'click' => 'conv.chat(' . $store['team_conversation_id'] . ');',
					];
				}

				if ($this->storePermissions->mayChatWithJumperWaitingTeam($store)) {
					$menu[] = [
						'name' => $this->translator->trans('store.chat.jumper'),
						'click' => 'conv.chat(' . $store['springer_conversation_id'] . ');',
					];
				}
				if ($this->storePermissions->mayEditStore($storeId)) {
					$menu[] = [
						'name' => $this->translator->trans('storeedit.bread'),
						'href' => '/?page=betrieb&a=edit&id=' . $storeId,
					];
					$menu[] = [
						'name' => $this->translator->trans('storeedit.team.bread'),
						'click' => '$(\'#teamEditor\').dialog({'
						. 'modal: true,'
						. 'width: $(window).width() * 0.95,'
						. 'title: \'' . $this->translator->trans('storeedit.team.bread') . '\''
						. '});',
					];
					$menu[] = [
						'name' => $this->translator->trans('pickup.edit.bread'),
						'click' => '$(\'#bid\').val(' . $storeId . ');'
							. '$(\'#editpickups\').dialog(\'open\');'
							. 'return false;',
					];
				}

				if (!$store['verantwortlich'] || $this->session->isAmbassador() || $this->session->may('orga')) {
					$menu[] = [
						'name' => $this->translator->trans('storeedit.team.leave'),
						'click' => 'u_betrieb_sign_out(' . $storeId . '); return false;',
					];
					$this->addStoreLeaveModal();
				}

				if (!empty($menu)) {
					$this->pageHelper->addContent($this->v_utils->v_menu(
						$menu, $this->translator->trans('store.actions')
					), CNT_LEFT);
				}

				/* team list */
				$this->pageHelper->addContent(
					$this->view->vueComponent('vue-storeteam', 'store-team', [
						'fsId' => $this->session->id(),
						'mayEditStore' => $this->storePermissions->mayEditStore($storeId),
						'team' => $this->getDisplayedStoreTeam($store),
						'storeId' => $storeId,
						'storeTitle' => $store['name'],
					]),
					CNT_LEFT
				);

				/* team status */
				if ($this->storePermissions->mayEditStore($storeId)) {
					$this->pageHelper->addContent(
						$this->v_utils->v_field(
							$this->view->u_legacyStoreTeamStatus($store),
							$this->translator->trans('status'),
							['class' => 'ui-padding']
						),
						CNT_LEFT
					);
				}

				if ($store['verantwortlich']) {
					$this->pageHelper->addContent(
						$this->view->vueComponent('vue-store-applications', 'StoreApplications', [
							'storeId' => $storeId,
							'storeTitle' => $store['name'] ?? '',
							'storeRequests' => $store['requests'] ?? [],
							'requestCount' => count($store['requests'] ?? []),
						])
					);
				}

				if ($this->storePermissions->maySeePickupHistory($storeId)) {
					$this->pageHelper->addContent(
						$this->view->vueComponent('vue-pickup-history', 'PickupHistory', [
							'storeId' => $storeId,
							'coopStart' => $store['begin'],
						])
					);
				}

				if ($this->storePermissions->mayReadStoreWall($storeId)) {
					$this->pageHelper->addContent(
						$this->view->vueComponent('vue-storeview', 'Store', [
							'storeId' => $storeId,
							'storeManagers' => $this->storeGateway->getStoreManagers($storeId),
							'mayWritePost' => $this->storePermissions->mayWriteStoreWall($storeId),
							'mayDeleteEverything' => $this->storePermissions->mayDeleteStoreWall($storeId),
							'expandWallByDefault' => !$this->session->isMob(),
						])
					);
				} else {
					$this->pageHelper->addContent($this->v_utils->v_info('Du bist momentan auf der Springerliste. Sobald Hilfe benötigt wird, wirst Du kontaktiert.'));
				}
				/* end of pinboard */

				/* fetchdates */
				if ($this->storePermissions->maySeePickups($storeId) && ($store['betrieb_status_id'] === CooperationStatus::COOPERATION_STARTING || $store['betrieb_status_id'] === CooperationStatus::COOPERATION_ESTABLISHED)) {
					$this->pageHelper->addContent(
						$this->view->vueComponent('vue-pickuplist', 'pickup-list', [
							'storeId' => $storeId,
							'storeTitle' => $store['name'],
							'isCoordinator' => $store['verantwortlich'],
							'teamConversationId' => $store['team_conversation_id'],
						]),
						CNT_RIGHT);
				}

				/* change regular fetchdates */
				if ($this->storePermissions->mayEditPickups($storeId)) {
					$width = $this->session->isMob() ? '$(window).width() * 0.96' : '$(window).width() / 2';
					$pickup_dates = $this->pickupGateway->getAbholzeiten($storeId);

					$this->pageHelper->hiddenDialog('editpickups',
						[
							$this->view->u_editPickups($pickup_dates),
							$this->v_utils->v_form_hidden('bid', 0)
						],
						$this->translator->trans('pickup.edit.add'),
						true,
						$width
					);
				}

				if (!$store['jumper']) {
					if (!in_array($store['betrieb_status_id'], [
						CooperationStatus::COOPERATION_STARTING,
						CooperationStatus::COOPERATION_ESTABLISHED,
					])) {
						$icon = $this->v_utils->v_getStatusAmpel($store['betrieb_status_id']);
						$this->pageHelper->addContent($this->v_utils->v_field(
							'<p>' . $icon . $this->translator->trans('storestatus.' . $store['betrieb_status_id']) . '</p>',
							$this->translator->trans('storeview.status'),
							['class' => 'ui-padding']
						), CNT_RIGHT);
					}
				}
			} else {
				if ($store = $this->storeGateway->getBetrieb($storeId)) {
					$this->pageHelper->addBread($store['name']);
					$this->flashMessageHelper->info($this->translator->trans('store.not-in-team'));
					$this->routeHelper->go('/?page=map&bid=' . $storeId);
				} else {
					$this->routeHelper->go('/karte');
				}
			}
		} else {
			$this->pageHelper->addBread($this->translator->trans('menu.entry.your_stores'));

			if ($this->storePermissions->mayCreateStore()) {
				$this->pageHelper->addContent($this->v_utils->v_menu(
					[
						['href' => '/?page=betrieb&a=new', 'name' => $this->translator->trans('storeedit.add-new')]
					],
					$this->translator->trans('storeedit.actions')), CNT_RIGHT);
			}

			$region = $this->regionGateway->getRegion($this->session->getCurrentRegionId());
			$stores = $this->storeGateway->getMyStores($this->session->id(), $this->session->getCurrentRegionId());
			$this->pageHelper->addContent($this->view->u_storeList(
				$stores['verantwortlich'],
				$this->translator->trans('storelist.managing')
			));
			$this->pageHelper->addContent($this->view->u_storeList(
				$stores['team'],
				$this->translator->trans('storelist.fetching')
			));

			if (!is_null($region)) {
				$regionName = $region['name'];
				$this->pageHelper->addContent($this->view->u_storeList(
					$stores['sonstige'],
					$this->translator->trans('storelist.others', ['{region}' => $regionName])
				));
			}
		}
	}

	private function getDisplayedStoreTeam(array $store): array
	{
		$allowedFields = [
			// personal info
			'id', 'name', 'photo', 'quiz_rolle', 'sleep_status', 'verified',
			// team-related info
			'verantwortlich', 'team_active', 'stat_fetchcount', 'add_date',
		];
		if ($this->storePermissions->maySeePhoneNumbers($store['id'])) {
			array_push($allowedFields, 'handy', 'telefon', 'last_fetch');
		}

		return array_map(
			function ($a) use ($allowedFields) {
				return array_filter($a, function ($key) use ($allowedFields) {
					return in_array($key, $allowedFields);
				}, ARRAY_FILTER_USE_KEY);
			},
			array_merge($store['foodsaver'], $store['springer']),
		);
	}

	private function addStoreLeaveModal(): void
	{
		$this->pageHelper->addHidden('
		<div id="signout_shure" title="' . $this->translator->trans('pickup.signout_confirm') . '">
			' . $this->v_utils->v_info('
				<strong>' . $this->translator->trans('pickup.signout_sure') . '</strong>
				<p>' . $this->translator->trans('pickup.signout_info') . '</p>'
			) . '
		</div>'
		);
	}
}
