<?php

namespace Foodsharing\Modules\StoreUser;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\StoreModel;

class StoreUserControl extends Control
{
	private $storeGateway;

	public function __construct(StoreModel $model, StoreUserView $view, StoreGateway $storeGateway)
	{
		$this->model = $model;
		$this->view = $view;
		$this->storeGateway = $storeGateway;

		parent::__construct();

		if (!S::may()) {
			$this->func->goLogin();
		}
	}

	public function index()
	{
		if (isset($_GET['id'])) {
			$this->func->addBread($this->func->s('betrieb_bread'), '/?page=fsbetrieb');
			$this->func->addTitle($this->func->s('betrieb_bread'));
			$this->func->addStyle('.button{margin-right:8px;}#right .tagedit-list{width:256px;}#foodsaver-wrapper{padding-top:0px;}');
			global $g_data;

			$betrieb = $this->storeGateway->getMyBetrieb(S::id(), $_GET['id']);

			if (!$betrieb) {
				$this->func->goPage();
			}

			$this->func->jsData['store'] = [
				'id' => (int)$betrieb['id'],
				'name' => (int)$betrieb['name'],
				'bezirk_id' => (int)$betrieb['bezirk_id'],
				'team_js' => $betrieb['team_js'],
				'verantwortlich' => $betrieb['verantwortlich'],
				'prefetchtime' => $betrieb['prefetchtime']
			];

			if (isset($_POST['form_submit']) && $_POST['form_submit'] == 'team' && (S::isOrgaTeam() || $this->storeGateway->isVerantwortlich(S::id(), $_GET['id']) || $this->func->isBotFor($betrieb['bezirk_id']))) {
				if ($_POST['form_submit'] == 'zeiten') {
					$range = range(0, 6);
					global $g_data;
					$this->storeGateway->clearAbholer($_GET['id']);
					foreach ($range as $r) {
						if (isset($_POST['dow' . $r])) {
							$this->func->handleTagselect('dow' . $r);
							foreach ($g_data['dow' . $r] as $fs_id) {
								$this->storeGateway->addAbholer($_GET['id'], $fs_id, $r);
							}
						}
					}
				} else {
					$this->func->handleTagselect('foodsaver');

					if (!empty($g_data['foodsaver'])) {
						$this->model->addBetriebTeam($_GET['id'], $g_data['foodsaver'], $g_data['verantwortlicher']);
					} else {
						$this->func->info($this->func->s('team_not_empty'));
					}
				}
				$this->func->info($this->func->s('changes_saved'));
				$this->func->goSelf();
			} elseif (isset($_POST['form_submit']) && $_POST['form_submit'] == 'changestatusform' && (S::isOrgaTeam() || $this->storeGateway->isVerantwortlich(S::id(), $_GET['id']) || $this->func->isBotFor($betrieb['bezirk_id']))) {
				$this->storeGateway->changeBetriebStatus(S::id(), $_GET['id'], $_POST['betrieb_status_id']);
				$this->func->go($this->func->getSelf());
			}

			$this->func->addTitle($betrieb['name']);

			if ($this->storeGateway->isInTeam(S::id(), $_GET['id']) || S::may('orga') || $this->func->isBotFor($betrieb['bezirk_id'])) {
				if ((!$betrieb['verantwortlich'] && $this->func->isBotFor($betrieb['bezirk_id']))) {
					$betrieb['verantwortlich'] = true;
					$this->func->info('<strong>' . $this->func->s('reference') . ':</strong> ' . $this->func->s('not_responsible_but_bot'));
				} elseif (!$betrieb['verantwortlich'] && $this->func->isOrgaTeam()) {
					$betrieb['verantwortlich'] = true;
					$this->func->info('<strong>' . $this->func->s('reference') . ':</strong> ' . $this->func->s('not_responsible_but_orga'));
				}
				if ($betrieb['verantwortlich']) {
					if (!empty($betrieb['requests'])) {
						$this->view->handleRequests($betrieb);
					}
				}

				$this->func->setEditData($betrieb);

				$this->func->addBread($betrieb['name']);

				$edit_team = '';

				$verantwortlich_select = '';

				$bibsaver = array();
				foreach ($betrieb['foodsaver'] as $fs) {
					if ($fs['rolle'] >= 2) {
						$bibsaver[] = $fs;
					}
				}

				if ($betrieb['verantwortlich']) {
					$checked = array();
					foreach ($betrieb['foodsaver'] as $fs) {
						if ($fs['verantwortlich'] == 1) {
							$checked[] = $fs['id'];
						}
					}
					$verantwortlich_select = $this->v_utils->v_form_checkbox('verantwortlicher', array('values' => $bibsaver, 'checked' => $checked));

					$edit_team = $this->v_utils->v_form(
						'team',

						array(
							$this->v_utils->v_form_tagselect('foodsaver', array('valueOptions' => $this->model->xhrGetTagFsAll())),
							$verantwortlich_select),
						array('submit' => $this->func->s('save'))
					);

					$this->func->addHidden('<div id="teamEditor">' . $edit_team . '</div>');
				}
				$this->func->addStyle('#team_msg{width:358px;}');
				$this->func->addHidden('
						<div id="u_undate">
							' . $this->v_utils->v_info($this->func->s('shure_of_backup'), $this->func->s('attention')) . '
							<input type="hidden" name="undate-date" id="undate-date" value="" />
							
							' . $this->v_utils->v_form_textarea('team_msg') . '
						</div>
					');

				/*Infos*/
				$betrieb['menge'] = '';
				if ($menge = $this->func->abhm($betrieb['abholmenge'])) {
					$betrieb['menge'] = $menge;
				}

				$info = '';
				if (!empty($betrieb['besonderheiten'])) {
					$info .= $this->v_utils->v_input_wrapper($this->func->s('besonderheiten'), nl2br($betrieb['besonderheiten']));
				}
				if ($betrieb['menge'] > 0) {
					$info .= $this->v_utils->v_input_wrapper($this->func->s('menge'), $betrieb['menge']);
				}
				if ($betrieb['presse'] == 1) {
					$info .= $this->v_utils->v_input_wrapper('Namensnennung', 'Dieser Betrieb darf &ouml;ffentlich genannt werden.');
				} elseif ($betrieb['presse'] == 0) {
					$info .= $this->v_utils->v_input_wrapper('Namensnennung', 'Bitte diesen Betrieb niemals &ouml;ffentlich (z.<span style="white-space:nowrap">&thinsp;</span>B. bei Essensk&ouml;rben, Facebook oder Presseanfragen) nennen!');
				}

				$this->func->addContent($this->v_utils->v_field(
					$this->v_utils->v_input_wrapper($this->func->s('address'), $betrieb['str'] . ' ' . $betrieb['hsnr'] . '<br />' . $betrieb['plz'] . ' ' . $betrieb['stadt']) .
					$info,

					$betrieb['name'],

					array('class' => 'ui-padding')
				), CNT_RIGHT);

				/*Optionsn*/

				$menu = array();

				if (!$betrieb['jumper'] || S::may('orga')) {
					if (!is_null($betrieb['team_conversation_id'])) {
						$menu[] = array('name' => 'Nachricht ans Team', 'click' => 'conv.chat(' . $betrieb['team_conversation_id'] . ');');
					}
					if ($betrieb['verantwortlich'] && !is_null($betrieb['springer_conversation_id'])) {
						$menu[] = array('name' => 'Nachricht an Springer', 'click' => 'conv.chat(' . $betrieb['springer_conversation_id'] . ');');
					}
				}
				if ($betrieb['verantwortlich'] || S::may('orga')) {
					$menu[] = array('name' => $this->func->s('fetch_history'), 'click' => "ajreq('fetchhistory',{app:'betrieb',bid:" . (int)$betrieb['id'] . '});');
					$menu[] = array('name' => $this->func->s('edit_betrieb'), 'href' => '/?page=betrieb&a=edit&id=' . $betrieb['id']);
					$menu[] = array('name' => $this->func->s('edit_team'), 'click' => '$(\'#teamEditor\').dialog({modal:true,width:425,title:\'' . $this->func->s('edit_team') . '\'});');
					$menu[] = array('name' => $this->func->s('edit_fetchtime'), 'click' => '$(\'#bid\').val(' . (int)$betrieb['id'] . ');$(\'#dialog_abholen\').dialog(\'open\');return false;');
				}
				if (!$betrieb['verantwortlich'] || $this->func->isOrgaTeam() || $this->func->isBotschafter()) {
					$menu[] = array('name' => $this->func->s('betrieb_sign_out'), 'click' => 'u_betrieb_sign_out(' . (int)$betrieb['id'] . ');return false;');
				}

				if (!empty($menu)) {
					$this->func->addContent($this->v_utils->v_menu($menu, $this->func->s('options')), CNT_LEFT);
				}

				$this->func->addContent(
					$this->v_utils->v_field(
						$this->view->u_team($betrieb) . '',

						$betrieb['name'] . '-Team'
					),
					CNT_LEFT
				);

				if (!$betrieb['jumper'] || S::may('orga')) {
					$this->func->addJs('u_updatePosts();');

					$opt = array();
					if ($this->func->isMob()) {
						$opt = array('class' => 'moreswap moreswap-height-200');
					}
					$this->func->addContent($this->v_utils->v_field('
							<div id="pinnwand">
								
								<div class="tools ui-padding">
									<form method="get" action="' . $this->func->getSelf() . '">
										<textarea class="comment textarea inlabel" title="Nachricht schreiben..." name="text"></textarea>
										<div align="right">
											<input id="comment-post" type="submit" class="submit" name="msg" value="' . $this->func->s('send') . '" />
										</div>
										<input type="hidden" name="bid" value="' . (int)$betrieb['id'] . '" />
									</form>
								</div>
							
								<div class="posts"></div>
							</div>', 'Pinnwand', $opt));
				/*pinnwand ende*/
				} else {
					$this->func->addContent($this->v_utils->v_info('Du bist momentan auf der Springerliste. Sobald Hilfe benötigt wird, wirst Du kontaktiert.'));
				}
				$zeit_cnt = '';
				if ($betrieb['verantwortlich']) {
					$zeit_cnt .= '<p style="text-align:center;"><a class="button" href="#" onclick="ajreq(\'adddate\',{app:\'betrieb\',id:' . (int)$_GET['id'] . '});return false;">einzelnen Termin eintragen</a></p>';
				}

				if ($verantwortlicher = $this->view->u_getVerantwortlicher($betrieb)) {
					$cnt = '';

					foreach ($verantwortlicher as $v) {
						$tmp = $this->view->u_innerRow('telefon', $v);
						$tmp .= $this->view->u_innerRow('handy', $v);

						$cnt .= $this->v_utils->v_input_wrapper($v['name'], $tmp);
					}

					$this->func->addContent($this->v_utils->v_field($cnt, $this->func->s('responsible_foodsaver'), array('class' => 'ui-padding')), CNT_LEFT);
				}

				/*
				 * Abholzeiten
				 */

				$this->func->addHidden('
					<div id="timedialog">
						
						<input type="hidden" name="timedialog-id" id="timedialog-id" value="" />
						<input type="hidden" name="timedialog-date" id="timedialog-date" value="" />
							
						<span class="shure_date" id="shure_date">' . $this->v_utils->v_info($this->func->sv('shure_date', array('label' => '<span id="date-label"></span>'))) . '</span>
						<span class="shure_range_date" id="shure_range_date" style="display:none;">' . $this->v_utils->v_info($this->func->sv('shure_range_date', array('label' => '<span id="range-day-label"></span>'))) . '</span>
						<div class="rangeFetch" id="rangeFetch" style="display:none;">
						
								' . $this->v_utils->v_input_wrapper($this->func->s('zeitraum'), '<input type="text" value="" id="timedialog-from" name="timedialog-from" class="datefetch input text value"> bis <input type="text" value="" id="timedialog-to" name="timedialog-to" class="datefetch input text value">') . '
						
						</div>
					</div>
					<div id="delete_shure" title="' . $this->func->s('delete_sure_title') . '">
						' . $this->v_utils->v_info($this->func->s('delete_post_sure')) . '
						<span class="sure" style="display:none">' . $this->func->s('sure') . '</span>
						<span class="abort" style="display:none">' . $this->func->s('abort') . '</span>
					</div>
					<div id="signout_shure" title="' . $this->func->s('signout_sure_title') . '">
						' . $this->v_utils->v_info($this->func->s('signout_sure')) . '
						<span class="sure" style="display:none">' . $this->func->s('sure') . '</span>
						<span class="abort" style="display:none">' . $this->func->s('abort') . '</span>
					</div>');

				if (is_array($betrieb['abholer'])) {
					foreach ($betrieb['abholer'] as $dow => $a) {
						$g_data['dow' . $dow] = $a;
					}
				}

				$zeiten = $this->storeGateway->getAbholzeiten($betrieb['id']);

				$next_dates = $this->view->u_getNextDates($zeiten, $betrieb, $this->model->listUpcommingFetchDates($_GET['id']));

				$abholdates = $this->storeGateway->listFetcher($betrieb['id'], array_keys($next_dates));

				global $g_data;
				foreach ($abholdates as $r) {
					$key = 'fetch-' . str_replace(array(':', ' ', '-'), '', $r['date']);
					if (!isset($g_data[$key])) {
						$g_data[$key] = array();
					}
					$g_data[$key][] = $r;
				}

				$days = $this->func->getDow();

				$scroller = '';

				foreach ($next_dates as $date => $time) {
					$part = explode(' ', $date);
					$date = $part[0];
					$scroller .= $this->view->u_form_checkboxTagAlt(
						$date . ' ' . $time['time'],
						array(
							'data' => $betrieb['team'],
							'url' => 'jsonTeam&bid=' . (int)$betrieb['id'],
							'label' => $days[date('w', strtotime($date))] . ' ' . $this->func->format_db_date($date) . ', ' . $this->func->format_time($time['time']),
							'betrieb_id' => $betrieb['id'],
							'verantwortlich' => $betrieb['verantwortlich'],
							'fetcher_count' => $time['fetcher'],
							'bezirk_id' => $betrieb['bezirk_id'],
							'field' => $time
						)
					);
				}

				$zeit_cnt .= $this->v_utils->v_scroller($scroller, 200);

				if ($betrieb['verantwortlich'] && empty($next_dates)) {
					$zeit_cnt = $this->v_utils->v_info($this->func->sv('no_fetchtime', array('name' => $betrieb['name'])), $this->func->s('attention') . '!') .
						'<p style="margin-top:10px;text-align:center;"><a class="button" href="#" onclick="ajreq(\'adddate\',{app:\'betrieb\',id:' . (int)$_GET['id'] . '});return false;">einzelnen Termin eintragen</a></p>';
				}

				/*
				 * Abholzeiten ändern
				 */
				if ($betrieb['verantwortlich'] || S::may('orga')) {
					$this->func->hiddenDialog('abholen', array($this->view->u_form_abhol_table($zeiten), $this->v_utils->v_form_hidden('bid', 0), '<input type="hidden" name="team" value="' . $betrieb['team_js'] . '" />'), $this->func->s('add_fetchtime'), array('reload' => true, 'width' => 500));
				}

				if (!$betrieb['jumper']) {
					if (($betrieb['betrieb_status_id'] == 3 || $betrieb['betrieb_status_id'] == 5)) {
						$this->func->addContent($this->v_utils->v_field($zeit_cnt, $this->func->s('next_fetch_dates'), array('class' => 'ui-padding')), CNT_RIGHT);
					} else {
						$bt = '';
						$betriebsStatusName = '';
						$betriebStatusList = $this->model->q('SELECT id, name FROM fs_betrieb_status');
						foreach ($betriebStatusList as $betriebStatus) {
							if ($betriebStatus['id'] == $betrieb['betrieb_status_id']) {
								$betriebsStatusName = $betriebStatus['name'];
							}
						}
						if ($betrieb['verantwortlich']) {
							$this->func->addHidden('<div id="changeStatus-hidden">' . $this->v_utils->v_form('changeStatusForm', array(
									$this->v_utils->v_form_select('betrieb_status_id', array('value' => $betrieb['betrieb_status_id'], 'values' => $betriebStatusList))
								)) . '</div>');
							$bt = '<p><span id="changeStatus">' . $this->func->s('change_status') . '</a></p>';
						}
						$this->func->addContent($this->v_utils->v_field('<p>' . $this->v_utils->v_getStatusAmpel($betrieb['betrieb_status_id']) . $betriebsStatusName . '</p>' . $bt, $this->func->s('status'), array('class' => 'ui-padding')), CNT_RIGHT);
					}
				}
			} else {
				if ($betrieb = $this->storeGateway->getBetrieb($_GET['id'])) {
					$this->func->addBread($betrieb['name']);
					$this->func->info($this->func->s('not_in_team'));
					$this->func->go('/?page=map&bid=' . $_GET['id']);
				} else {
					$this->func->go('/karte');
				}
			}
		} else {
			$this->func->addBread('Deine Betriebe');
			$this->func->addContent($this->v_utils->v_menu(array(
				array('href' => '/?page=betrieb&a=new', 'name' => $this->func->s('add_new'))
			), 'Aktionen'), CNT_RIGHT);

			$bezirk = $this->func->getBezirk();
			$betriebe = $this->storeGateway->getMyBetriebe(S::id(), S::getCurrentBezirkId());
			$this->func->addContent($this->view->u_betriebList($betriebe['verantwortlich'], $this->func->s('you_responsible'), true));
			$this->func->addContent($this->view->u_betriebList($betriebe['team'], $this->func->s('you_fetcher'), false));
			$this->func->addContent($this->view->u_betriebList($betriebe['sonstige'], $this->func->sv('more_stores', array('name' => $bezirk['name'])), false));
		}
	}
}
