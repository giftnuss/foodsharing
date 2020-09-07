<?php

namespace Foodsharing\Modules\StoreUser;

use Carbon\Carbon;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
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
			$this->pageHelper->addBread($this->translator->trans('store.bread'), '/?page=fsbetrieb');
			$this->pageHelper->addTitle($this->translator->trans('store.bread'));
			$this->pageHelper->addStyle('.button{margin-right: 8px;} #right .tagedit-list{width: 256px;} #foodsaver-wrapper{padding-top: 0px;}');
			global $g_data;

			$store = $this->storeGateway->getMyStore($this->session->id(), $_GET['id']);

			if (!$store) {
				$this->routeHelper->goPage();
			}

			$this->pageHelper->jsData['store'] = [
				'id' => (int)$store['id'],
				'name' => $store['name'],
				'bezirk_id' => (int)$store['bezirk_id'],
				'verantwortlich' => $store['verantwortlich'],
				'prefetchtime' => $store['prefetchtime']
			];

			if (isset($_POST['form_submit']) && $_POST['form_submit'] == 'team' && $this->storePermissions->mayEditStore($store['id'])) {
				$this->sanitizerService->handleTagSelect('storemanagers');
				if (!empty($g_data['storemanagers'])) {
					if (count($g_data['storemanagers']) > 3) {
						$this->flashMessageHelper->error($this->translator->trans('storeedit.team.max-sm'));
					} else {
						foreach ($g_data['storemanagers'] as $fsId) {
							$addedStoremanager = $this->storeGateway->addStoreManager($store['id'], $fsId);
							$this->storeGateway->addStoreLog($store['id'], $this->session->id(), $fsId, null, StoreLogAction::APPOINT_STORE_MANAGER);
						}
					}
				}

				$this->sanitizerService->handleTagSelect('foodsaver');
				if (!empty($g_data['foodsaver'])) {
					$addedTeam = $this->storeModel->addBetriebTeam($_GET['id'], $g_data['foodsaver'], $g_data['verantwortlicher']);
				} elseif (empty($g_data['storemanagers'])) {
					$this->flashMessageHelper->info($this->translator->trans('storeedit.team.empty'));
				}
				if (isset($addedStoremanager) || isset($addedTeam)) {
					$this->flashMessageHelper->info($this->translator->trans('settings.saved'));
				}
				$this->routeHelper->goSelf();
			}

			$this->pageHelper->addTitle($store['name']);

			if ($this->storePermissions->mayAccessStore($store['id'])) {
				if ((!$store['verantwortlich'] && $this->session->isAdminFor($store['bezirk_id']))) {
					$store['verantwortlich'] = true;
					$this->flashMessageHelper->info(
						'<strong>' . $this->translator->trans('storeedit.team.note') . '</strong> '
						. $this->translator->trans('storeedit.team.amb')
					);
				} elseif (!$store['verantwortlich'] && $this->session->isOrgaTeam()) {
					$store['verantwortlich'] = true;
					$this->flashMessageHelper->info(
						'<strong>' . $this->translator->trans('storeedit.team.note') . '</strong> '
						. $this->translator->trans('storeedit.team.orga')
					);
				}
				if ($store['verantwortlich']) {
					if (!empty($store['requests'])) {
						$this->view->handleRequests($store);
					}
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
					$valueOptions = $this->foodsaverGateway->xhrGetFoodsaversOfRegionsForTagSelect($this->session->listRegionIDs());

					$elements = [
						$this->v_utils->v_form_tagselect('foodsaver', [
							'valueOptions' => $valueOptions,
							'label' => $this->translator->trans('storeedit.team.foodsaver'),
						]),
						$verantwortlich_select,
					];

					if (empty($checked)) {
						$noStoreManagerWarning = $this->v_utils->v_error($this->translator->trans('storeedit.team.unmanaged'));
						$hiddenField = $this->v_utils->v_form_hidden('set_new_store_manager', 'true');
						$elements = [
							$noStoreManagerWarning,
							$this->v_utils->v_form_tagselect('storemanagers', ['valueOptions' => $this->foodsaverGateway->xhrGetStoremanagersOfRegionsForTagSelect($this->session->listRegionIDs())]
							),
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

				if ($this->storePermissions->maySeePickupHistory($store['id'])) {
					$this->pageHelper->addContent(
						$this->view->vueComponent('vue-pickup-history', 'PickupHistory', [
							'storeId' => $store['id'],
							'coopStart' => $store['begin'],
						])
					);
				}
				if ($this->storePermissions->mayEditStore($store['id'])) {
					$menu[] = [
						'name' => $this->translator->trans('storeedit.bread'),
						'href' => '/?page=betrieb&a=edit&id=' . $store['id'],
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
						'click' => '$(\'#bid\').val(' . (int)$store['id'] . ');'
							. '$(\'#dialog_abholen\').dialog(\'open\');'
							. 'return false;',
					];
				}

				if (!$store['verantwortlich'] || $this->session->isAmbassador() || $this->session->isOrgaTeam()) {
					$menu[] = [
						'name' => $this->translator->trans('storeedit.team.leave'),
						'click' => 'u_betrieb_sign_out(' . (int)$store['id'] . '); return false;',
					];
				}

				if (!empty($menu)) {
					$this->pageHelper->addContent($this->v_utils->v_menu(
						$menu, $this->translator->trans('store.actions')
					), CNT_LEFT);
				}

				/* team list */
				$allowedFields = [
					// personal info
					'id', 'name', 'photo', 'quiz_rolle', 'sleep_status', 'verified',
					// team-related info
					'verantwortlich', 'team_active', 'stat_fetchcount', 'add_date',
				];
				if ($this->storePermissions->maySeePhoneNumbers($store['id'])) {
					array_push($allowedFields, 'handy', 'telefon', 'last_fetch');
				}

				$this->pageHelper->addContent(
					$this->view->vueComponent('vue-storeteam', 'store-team', [
						'fsId' => $this->session->id(),
						'mayEditStore' => $this->storePermissions->mayEditStore($store['id']),
						'team' => array_map(
							function ($a) use ($allowedFields) {
								return array_filter($a, function ($key) use ($allowedFields) {
									return in_array($key, $allowedFields);
								}, ARRAY_FILTER_USE_KEY);
							},
							array_merge($store['foodsaver'], $store['springer']),
						),
						'storeId' => $store['id'],
						'storeTitle' => $store['name'],
					]),
					CNT_LEFT
				);

				/* team status */
				if ($this->storePermissions->mayEditStore($store['id'])) {
					$this->pageHelper->addContent(
						$this->v_utils->v_field(
							$this->view->u_legacyStoreTeamStatus($store),
							$this->translator->trans('status'),
							['class' => 'ui-padding']
						),
						CNT_LEFT
					);
				}

				if ($this->storePermissions->mayReadStoreWall($store['id'])) {
					$this->pageHelper->addJs('u_updatePosts();');
					$this->pageHelper->addContent($this->v_utils->v_field('
						<div id="pinnwand">
							<div class="tools ui-padding">
								<form method="get" action="' . $this->routeHelper->getSelf() . '">
									<textarea class="comment textarea" placeholder="' . $this->translator->trans('wall.message_placeholder') . '" name="text"></textarea>
									<div align="right">
										<input id="comment-post" type="submit" class="submit" name="msg" value="' . $this->translator->trans('button.send') . '" />
									</div>
								</form>
							</div>

							<div class="posts"></div>
						</div>', 'Pinnwand', ['class' => 'truncate-content truncate-height-280 collapse-mobile force-collapse']));
				/* end of pinboard */
				} else {
					$this->pageHelper->addContent($this->v_utils->v_info('Du bist momentan auf der Springerliste. Sobald Hilfe benötigt wird, wirst Du kontaktiert.'));
				}

				/* fetchdates */
				$this->pageHelper->addHidden('
					<div id="delete_shure" title="' . $this->translator->trans('really_delete') . '">
						' . $this->v_utils->v_info($this->translator->trans('wall.confirm-deletion')) . '
						<span class="sure" style="display: none;">' . $this->translator->trans('wall.delete') . '</span>
						<span class="abort" style="display: none;">' . $this->translator->trans('button.cancel') . '</span>
					</div>
					<div id="signout_shure" title="' . $this->translator->trans('pickup.signout_confirm') . '">
						' . $this->v_utils->v_info('
							<strong>' . $this->translator->trans('pickup.signout_sure') . '</strong>
							<p>' . $this->translator->trans('pickup.signout_info') . '</p>'
						) . '
						<span class="sure" style="display: none;">' . $this->translator->trans('storeedit.team.leave') . '</span>
						<span class="abort" style="display: none;">' . $this->translator->trans('button.cancel') . '</span>
					</div>
');

				if ($this->storePermissions->maySeePickups($store['id']) && ($store['betrieb_status_id'] === CooperationStatus::COOPERATION_STARTING || $store['betrieb_status_id'] === CooperationStatus::COOPERATION_ESTABLISHED)) {
					$this->pageHelper->addContent(
						$this->view->vueComponent('vue-pickuplist', 'pickup-list', [
							'storeId' => $store['id'],
							'isCoordinator' => $store['verantwortlich'],
							'teamConversationId' => $store['team_conversation_id'],
						]),
						CNT_RIGHT);
				}

				/* change regular fetchdates */
				if ($this->storePermissions->mayEditPickups($store['id'])) {
					$width = $this->session->isMob() ? '$(window).width() * 0.96' : '$(window).width() / 2';
					$pickup_dates = $this->storeGateway->getAbholzeiten($store['id']);

					$this->pageHelper->hiddenDialog('abholen',
						[$this->view->u_form_abhol_table($pickup_dates),
							$this->v_utils->v_form_hidden('bid', 0)
						],
						$this->translator->trans('pickup.edit.add'), ['reload' => true, 'width' => $width]);
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
				if ($store = $this->storeGateway->getBetrieb($_GET['id'])) {
					$this->pageHelper->addBread($store['name']);
					$this->flashMessageHelper->info($this->translator->trans('store.not-in-team'));
					$this->routeHelper->go('/?page=map&bid=' . $_GET['id']);
				} else {
					$this->routeHelper->go('/karte');
				}
			}
		} else {
			$this->pageHelper->addBread('Deine Betriebe');

			if ($this->storePermissions->mayCreateStore()) {
				$this->pageHelper->addContent($this->v_utils->v_menu(
					[
						['href' => '/?page=betrieb&a=new', 'name' => $this->translator->trans('storeedit.add-new')]
					],
					$this->translator->trans('storeedit.actions')), CNT_RIGHT);
			}

			$region = $this->regionGateway->getRegion($this->session->getCurrentRegionId());
			$stores = $this->storeGateway->getMyStores($this->session->id(), $this->session->getCurrentRegionId());
			$this->pageHelper->addContent($this->view->u_storeList($stores['verantwortlich'], $this->translator->trans('storelist.managing')));
			$this->pageHelper->addContent($this->view->u_storeList($stores['team'], $this->translator->trans('storelist.fetching')));
			if (!is_null($region)) {
				$this->pageHelper->addContent($this->view->u_storeList($stores['sonstige'], $this->translator->trans('storelist.others', [
					'{region}' => $region['name'],
				])));
			}
		}
	}
}
