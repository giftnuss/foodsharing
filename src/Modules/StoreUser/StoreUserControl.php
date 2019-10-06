<?php

namespace Foodsharing\Modules\StoreUser;

use Carbon\Carbon;
use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\TimeHelper;
use Foodsharing\Helpers\WeightHelper;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\StoreModel;
use Foodsharing\Modules\Store\StoreStatus;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Services\SanitizerService;

class StoreUserControl extends Control
{
	private $storeGateway;
	private $storePermissions;
	private $foodsaverGateway;
	private $sanitizerService;
	private $timeHelper;
	private $dataHelper;
	private $regionGateway;
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
		WeightHelper $weightHelper
	) {
		$this->model = $model;
		$this->view = $view;
		$this->storeGateway = $storeGateway;
		$this->storePermissions = $storePermissions;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->sanitizerService = $sanitizerService;
		$this->timeHelper = $timeHelper;
		$this->dataHelper = $dataHelper;
		$this->regionGateway = $regionGateway;
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
				'team_js' => $store['team_js'],
				'verantwortlich' => $store['verantwortlich'],
				'prefetchtime' => $store['prefetchtime']
			];

			if (isset($_POST['form_submit']) && $_POST['form_submit'] == 'team' && $this->storePermissions->mayEditStore($store['id'])) {
				$this->sanitizerService->handleTagSelect('foodsaver');
				if (!empty($g_data['foodsaver'])) {
					$this->model->addBetriebTeam($_GET['id'], $g_data['foodsaver'], $g_data['verantwortlicher']);
				} else {
					$this->flashMessageHelper->info($this->translationHelper->s('team_not_empty'));
				}
				$this->flashMessageHelper->info($this->translationHelper->s('changes_saved'));
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

				$bibsaver = array();
				foreach ($store['foodsaver'] as $fs) {
					if ($fs['rolle'] >= 2) {
						$bibsaver[] = $fs;
					}
				}

				if ($store['verantwortlich']) {
					$checked = array();
					foreach ($store['foodsaver'] as $fs) {
						if ($fs['verantwortlich'] == 1) {
							$checked[] = $fs['id'];
						}
					}
					$verantwortlich_select = $this->v_utils->v_form_checkbox('verantwortlicher', array('values' => $bibsaver, 'checked' => $checked));

					$edit_team = $this->v_utils->v_form(
						'team',

						[
							$this->v_utils->v_form_tagselect('foodsaver', ['valueOptions' => $this->foodsaverGateway->xhrGetTagFsAll($this->session->listRegionIDs())]
							),
							$verantwortlich_select
						],
						['submit' => $this->translationHelper->s('save')]
					);

					$this->pageHelper->addHidden('<div id="teamEditor">' . $edit_team . '</div>');
				}

				/*Infos*/

				$info = '';
				if (!empty($store['besonderheiten'])) {
					$info .= $this->v_utils->v_input_wrapper($this->translationHelper->s('besonderheiten'), nl2br($store['besonderheiten']));
				}
				if ($quantityName = $this->weightHelper->getFetchWeightName($store['abholmenge'])) {
					$info .= $this->v_utils->v_input_wrapper($this->translationHelper->s('menge'), $quantityName);
				}
				if ($press = $this->mentionPublicly($store['presse'])) {
					$info .= $this->v_utils->v_input_wrapper('Namensnennung', $press);
				}

				/* find yourself in the pickup list and show your last pickup date in store info */
				foreach ($store['foodsaver'] as $fs) {
					if ($fs['id'] === $this->session->id() && $fs['last_fetch'] != null) {
						$lastFetchDate = Carbon::createFromTimestamp($fs['last_fetch']);
						$info .= $this->v_utils->v_input_wrapper($this->translationHelper->s('my_last_pickup'), $lastFetchDate->format('d.m.Y') . ' (' . $this->translationHelper->s('prefix_Ago')
								. ' ' . Carbon::now()->diff($lastFetchDate)->days . ' ' . $this->translationHelper->s('days') . ')');
						break;
					}
				}

				$this->pageHelper->addContent($this->v_utils->v_field(
					$this->v_utils->v_input_wrapper($this->translationHelper->s('address'), $store['str'] . ' ' . $store['hsnr'] . '<br />' . $store['plz'] . ' ' . $store['stadt']) .
					$info,

					$store['name'],

					array('class' => 'ui-padding')
				), CNT_RIGHT);

				/* options menu */
				$menu = array();

				if ($this->storePermissions->mayChatWithRegularTeam($store)) {
					$menu[] = array('name' => $this->translationHelper->s('chat_with_regular_team'), 'click' => 'conv.chat(' . $store['team_conversation_id'] . ');');
				}

				if ($this->storePermissions->mayChatWithJumperWaitingTeam($store)) {
					$menu[] = array('name' => $this->translationHelper->s('chat_with_jumper_waiting_team'), 'click' => 'conv.chat(' . $store['springer_conversation_id'] . ');');
				}

				if ($this->storePermissions->mayEditStore($store['id'])) {
					$menu[] = array('name' => $this->translationHelper->s('fetch_history'), 'click' => "ajreq('fetchhistory',{app:'betrieb',bid:" . (int)$store['id'] . '});');
					$menu[] = array('name' => $this->translationHelper->s('edit_betrieb'), 'href' => '/?page=betrieb&a=edit&id=' . $store['id']);
					$menu[] = array('name' => $this->translationHelper->s('edit_team'), 'click' => '$(\'#teamEditor\').dialog({modal:true,width:$(window).width()*0.95,title:\'' . $this->translationHelper->s('edit_team') . '\'});');
					$menu[] = array('name' => $this->translationHelper->s('edit_fetchtime'), 'click' => '$(\'#bid\').val(' . (int)$store['id'] . ');$(\'#dialog_abholen\').dialog(\'open\');return false;');
				}
				if (!$store['verantwortlich'] || $this->session->isAmbassador() || $this->session->isOrgaTeam()) {
					$menu[] = array('name' => $this->translationHelper->s('betrieb_sign_out'), 'click' => 'u_betrieb_sign_out(' . (int)$store['id'] . ');return false;');
				}

				if (!empty($menu)) {
					$this->pageHelper->addContent($this->v_utils->v_menu($menu, $this->translationHelper->s('options')), CNT_LEFT);
				}

				/* team list */
				$this->pageHelper->addContent(
					$this->v_utils->v_field(
						$this->view->u_team($store) . '',
						$store['name'] . '-Team',
						($this->session->isMob() && count($store['foodsaver']) > 8) ? array('class' => 'moreswap moreswap-height-280') : []
					),
					CNT_LEFT,
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
										<input type="hidden" name="bid" value="' . (int)$store['id'] . '" />
									</form>
								</div>

								<div class="posts"></div>
							</div>', 'Pinnwand', $this->session->isMob() ? array('class' => 'moreswap moreswap-height-280') : []));
				/* end of pinboard */
				} else {
					$this->pageHelper->addContent($this->v_utils->v_info('Du bist momentan auf der Springerliste. Sobald Hilfe benötigt wird, wirst Du kontaktiert.'));
				}

				if ($verantwortlicher = $this->view->u_getVerantwortlicher($store)) {
					$cnt = '';

					foreach ($verantwortlicher as $v) {
						$phoneNumbers = $this->view->u_innerRow('telefon', $v);
						$phoneNumbers .= $this->view->u_innerRow('handy', $v);

						$cnt .= $this->v_utils->v_input_wrapper($v['name'], $phoneNumbers);
					}

					$this->pageHelper->addContent($this->v_utils->v_field($cnt, $this->translationHelper->s('responsible_foodsaver'), array('class' => 'ui-padding')), CNT_LEFT);
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

				if ($this->storePermissions->maySeePickups($store['id']) && ($store['betrieb_status_id'] === StoreStatus::COOPERATION_STARTING || $store['betrieb_status_id'] === StoreStatus::COOPERATION_ESTABLISHED)) {
					$this->pageHelper->addContent($this->view->vueComponent('vue-pickuplist', 'pickup-list', ['storeId' => $store['id'], 'isCoordinator' => $store['verantwortlich'], 'teamConversationId' => $store['team_conversation_id']]), CNT_RIGHT);
				}

				/* change regular fetchdates */
				if ($this->storePermissions->mayEditPickups($store['id'])) {
					$width = $this->session->isMob() ? '$(window).width() * 0.96' : '$(window).width() / 2';
					$pickup_dates = $this->storeGateway->getAbholzeiten($store['id']);

					$this->pageHelper->hiddenDialog('abholen',
						array($this->view->u_form_abhol_table($pickup_dates),
							$this->v_utils->v_form_hidden('bid', 0),
							'<input type="hidden" name="team" value="' . $store['team_js'] . '" />'
						),
						$this->translationHelper->s('add_fetchtime'), array('reload' => true, 'width' => $width));
				}

				if (!$store['jumper']) {
					if ($store['betrieb_status_id'] === StoreStatus::COOPERATION_STARTING || $store['betrieb_status_id'] === StoreStatus::COOPERATION_ESTABLISHED) {
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
							$this->pageHelper->addHidden('<div id="changeStatus-hidden">' . $this->v_utils->v_form('changeStatusForm', array(
									$this->v_utils->v_form_select('betrieb_status_id', array('value' => $store['betrieb_status_id'], 'values' => $storeStateList))
								)) . '</div>');
							$bt = '<p><span id="changeStatus">' . $this->translationHelper->s('change_status') . '</a></p>';
						}
						$this->pageHelper->addContent($this->v_utils->v_field('<p>' . $this->v_utils->v_getStatusAmpel($store['betrieb_status_id']) . $storeStateName . '</p>' . $bt, $this->translationHelper->s('status'), array('class' => 'ui-padding')), CNT_RIGHT);
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
			$this->pageHelper->addContent($this->v_utils->v_menu(array(
				array('href' => '/?page=betrieb&a=new', 'name' => $this->translationHelper->s('add_new'))
			), 'Aktionen'), CNT_RIGHT);

			$region = $this->regionGateway->getRegion($this->session->getCurrentRegionId());
			$stores = $this->storeGateway->getMyStores($this->session->id(), $this->session->getCurrentRegionId());
			$this->pageHelper->addContent($this->view->u_betriebList($stores['verantwortlich'], $this->translationHelper->s('you_responsible'), true));
			$this->pageHelper->addContent($this->view->u_betriebList($stores['team'], $this->translationHelper->s('you_fetcher'), false));
			$this->pageHelper->addContent($this->view->u_betriebList($stores['sonstige'], $this->translationHelper->sv('more_stores', array('name' => $region['name'])), false));
		}
	}

	private function mentionPublicly(int $id)
	{
		if ($id === 0) {
			return $this->translationHelper->s('may_not_referred_to_in_public');
		}

		if ($id === 1) {
			return $this->translationHelper->s('may_referred_to_in_public');
		}

		return false;
	}
}
