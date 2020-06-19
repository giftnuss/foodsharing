<?php

namespace Foodsharing\Modules\StoreUser;

use Carbon\Carbon;
use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\TimeHelper;
use Foodsharing\Helpers\WeightHelper;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\StoreModel;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Services\SanitizerService;
use Symfony\Contracts\Translation\TranslatorInterface;

class StoreUserControl extends Control
{
	private $storeModel;
	private $storeGateway;
	private $storePermissions;
	private $foodsaverGateway;
	private $sanitizerService;
	private $timeHelper;
	private $dataHelper;
	private $regionGateway;
	private $translator;
	private $weightHelper;

	public function __construct(
		StoreModel $model,
		StoreUserView $view,
		StoreGateway $storeGateway,
		StorePermissions $storePermissions,
		FoodsaverGateway $foodsaverGateway,
		SanitizerService $sanitizerService,
		TimeHelper $timeHelper,
		DataHelper $dataHelper,
		RegionGateway $regionGateway,
		TranslatorInterface $translator,
		WeightHelper $weightHelper
	) {
		$this->storeModel = $model;
		$this->view = $view;
		$this->storeGateway = $storeGateway;
		$this->storePermissions = $storePermissions;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->sanitizerService = $sanitizerService;
		$this->timeHelper = $timeHelper;
		$this->dataHelper = $dataHelper;
		$this->regionGateway = $regionGateway;
		$this->translator = $translator;
		$this->weightHelper = $weightHelper;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->goLogin();
		}
	}

	public function index()
	{
		if (isset($_GET['id'])) {
			$this->pageHelper->addBread($this->translationHelper->s('betrieb_bread'), '/?page=fsbetrieb');
			$this->pageHelper->addTitle($this->translationHelper->s('betrieb_bread'));
			$this->pageHelper->addStyle('.button{margin-right:8px;}#right .tagedit-list{width:256px;}#foodsaver-wrapper{padding-top:0px;}');
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
						$this->flashMessageHelper->error($this->translationHelper->s('too_many_storemanagers_warning'));
					} else {
						foreach ($g_data['storemanagers'] as $fsId) {
							$addedStoremanager = $this->storeGateway->addStoreManager($store['id'], $fsId);
						}
					}
				}

				$this->sanitizerService->handleTagSelect('foodsaver');
				if (!empty($g_data['foodsaver'])) {
					$addedTeam = $this->storeModel->addBetriebTeam($_GET['id'], $g_data['foodsaver'], $g_data['verantwortlicher']);
				} elseif (empty($g_data['storemanagers'])) {
					$this->flashMessageHelper->info($this->translationHelper->s('team_not_empty'));
				}
				if (isset($addedStoremanager) || isset($addedTeam)) {
					$this->flashMessageHelper->info($this->translator->trans('settings.saved'));
				}
				$this->routeHelper->goSelf();
			} elseif (isset($_POST['form_submit']) && $_POST['form_submit'] == 'changestatusform' && $this->storePermissions->mayEditStore($store['id'])) {
				$this->storeGateway->changeBetriebStatus($this->session->id(), $_GET['id'], $_POST['betrieb_status_id']);
				$this->routeHelper->go($this->routeHelper->getSelf());
			}

			$this->pageHelper->addTitle($store['name']);

			if ($this->storePermissions->mayAccessStore($store['id'])) {
				if ((!$store['verantwortlich'] && $this->session->isAdminFor($store['bezirk_id']))) {
					$store['verantwortlich'] = true;
					$this->flashMessageHelper->info('<strong>' . $this->translationHelper->s('reference') . ':</strong> ' . $this->translationHelper->s('not_responsible_but_bot'));
				} elseif (!$store['verantwortlich'] && $this->session->isOrgaTeam()) {
					$store['verantwortlich'] = true;
					$this->flashMessageHelper->info('<strong>' . $this->translationHelper->s('reference') . ':</strong> ' . $this->translationHelper->s('not_responsible_but_orga'));
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

					$elements = [
						$this->v_utils->v_form_tagselect('foodsaver', ['valueOptions' => $this->foodsaverGateway->xhrGetFoodsaversOfRegionsForTagSelect($this->session->listRegionIDs())]
						),
						$verantwortlich_select,
					];

					if (empty($checked)) {
						$noStoreManagerWarning = $this->v_utils->v_error($this->translationHelper->s('no_storemanager_warning'));
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
						['submit' => $this->translationHelper->s('save')]
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
					$menu[] = ['name' => $this->translationHelper->s('chat_with_regular_team'), 'click' => 'conv.chat(' . $store['team_conversation_id'] . ');'];
				}

				if ($this->storePermissions->mayChatWithJumperWaitingTeam($store)) {
					$menu[] = ['name' => $this->translationHelper->s('chat_with_jumper_waiting_team'), 'click' => 'conv.chat(' . $store['springer_conversation_id'] . ');'];
				}

				if ($this->storePermissions->mayEditStore($store['id'])) {
					$menu[] = ['name' => $this->translationHelper->s('fetch_history'), 'click' => "ajreq('fetchhistory',{app:'betrieb',bid:" . (int)$store['id'] . '});'];
					$menu[] = ['name' => $this->translationHelper->s('edit_betrieb'), 'href' => '/?page=betrieb&a=edit&id=' . $store['id']];
					$menu[] = ['name' => $this->translationHelper->s('edit_team'), 'click' => '$(\'#teamEditor\').dialog({modal:true,width:$(window).width()*0.95,title:\'' . $this->translationHelper->s('edit_team') . '\'});'];
					$menu[] = ['name' => $this->translationHelper->s('edit_fetchtime'), 'click' => '$(\'#bid\').val(' . (int)$store['id'] . ');$(\'#dialog_abholen\').dialog(\'open\');return false;'];
				}
				if (!$store['verantwortlich'] || $this->session->isAmbassador() || $this->session->isOrgaTeam()) {
					$menu[] = ['name' => $this->translationHelper->s('betrieb_sign_out'), 'click' => 'u_betrieb_sign_out(' . (int)$store['id'] . ');return false;'];
				}

				if (!empty($menu)) {
					$this->pageHelper->addContent($this->v_utils->v_menu($menu, $this->translationHelper->s('options')), CNT_LEFT);
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

				if ($this->storePermissions->mayReadStoreWall($store['id'])) {
					$this->pageHelper->addJs('u_updatePosts();');
					$this->pageHelper->addContent($this->v_utils->v_field('
							<div id="pinnwand">

								<div class="tools ui-padding">
									<form method="get" action="' . $this->routeHelper->getSelf() . '">
										<textarea class="comment textarea inlabel" title="Nachricht schreiben..." name="text"></textarea>
										<div align="right">
											<input id="comment-post" type="submit" class="submit" name="msg" value="' . $this->translationHelper->s('send') . '" />
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
					<div id="delete_shure" title="' . $this->translationHelper->s('delete_sure_title') . '">
						' . $this->v_utils->v_info($this->translationHelper->s('delete_post_sure')) . '
						<span class="sure" style="display:none">' . $this->translationHelper->s('delete_post') . '</span>
						<span class="abort" style="display:none">' . $this->translationHelper->s('abort') . '</span>
					</div>
					<div id="signout_shure" title="' . $this->translationHelper->s('signout_sure_title') . '">
						' . $this->v_utils->v_info($this->translationHelper->s('signout_sure')) . '
						<span class="sure" style="display:none">' . $this->translationHelper->s('betrieb_sign_out') . '</span>
						<span class="abort" style="display:none">' . $this->translationHelper->s('abort') . '</span>
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
						$this->translationHelper->s('add_fetchtime'), ['reload' => true, 'width' => $width]);
				}

				if (!$store['jumper']) {
					if ($store['betrieb_status_id'] === CooperationStatus::COOPERATION_STARTING || $store['betrieb_status_id'] === CooperationStatus::COOPERATION_ESTABLISHED) {
					} else {
						$bt = '';
						$storeStateName = '';
						$storeStateList = $this->storeGateway->getStoreStateList();
						foreach ($storeStateList as $storeState) {
							if ($storeState['id'] == $store['betrieb_status_id']) {
								$storeStateName = $storeState['name'];
							}
						}
						if ($store['verantwortlich']) {
							$this->pageHelper->addHidden('<div id="changeStatus-hidden">' . $this->v_utils->v_form('changeStatusForm', [
									$this->v_utils->v_form_select('betrieb_status_id', ['value' => $store['betrieb_status_id'], 'values' => $storeStateList])
								]) . '</div>');
							$bt = '<p><span id="changeStatus">' . $this->translationHelper->s('change_status') . '</a></p>';
						}
						$this->pageHelper->addContent($this->v_utils->v_field('<p>' . $this->v_utils->v_getStatusAmpel($store['betrieb_status_id']) . $storeStateName . '</p>' . $bt, $this->translationHelper->s('status'), ['class' => 'ui-padding']), CNT_RIGHT);
					}
				}
			} else {
				if ($store = $this->storeGateway->getBetrieb($_GET['id'])) {
					$this->pageHelper->addBread($store['name']);
					$this->flashMessageHelper->info($this->translationHelper->s('not_in_team'));
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
						['href' => '/?page=betrieb&a=new', 'name' => $this->translationHelper->s('add_new')]
					],
					'Aktionen'), CNT_RIGHT);
			}

			$region = $this->regionGateway->getRegion($this->session->getCurrentRegionId());
			$stores = $this->storeGateway->getMyStores($this->session->id(), $this->session->getCurrentRegionId());
			$this->pageHelper->addContent($this->view->u_storeList($stores['verantwortlich'], $this->translationHelper->s('you_responsible')));
			$this->pageHelper->addContent($this->view->u_storeList($stores['team'], $this->translationHelper->s('you_fetcher')));
			$this->pageHelper->addContent($this->view->u_storeList($stores['sonstige'], $this->translationHelper->sv('more_stores', ['name' => $region['name']])));
		}
	}
}
