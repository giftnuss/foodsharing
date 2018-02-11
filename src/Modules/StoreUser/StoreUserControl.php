<?php

namespace Foodsharing\Modules\StoreUser;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Store\StoreModel;

class StoreUserControl extends Control
{
	public function __construct()
	{
		if (!S::may()) {
			goLogin();
		}
		$this->model = new StoreModel();
		$this->view = new StoreUserView();

		global $g_view_utils;

		$this->v_utils = $g_view_utils;

		parent::__construct();
	}

	public function index()
	{
		addScript('/js/contextmenu/jquery.contextMenu.js');
		addCss('/js/contextmenu/jquery.contextMenu.css');

		if (isset($_GET['id'])) {
			addBread(s('betrieb_bread'), '/?page=fsbetrieb');
			addTitle(s('betrieb_bread'));
			addStyle('.button{margin-right:8px;}#right .tagedit-list{width:256px;}#foodsaver-wrapper{padding-top:0px;}');
			global $g_data;

			$betrieb = $this->model->getMyBetrieb($_GET['id']);

			if (!$betrieb) {
				goPage();
			}

			if (isset($_POST['form_submit']) && $_POST['form_submit'] == 'team' && ($this->model->isVerantwortlich($_GET['id']) || isOrgaTeam() || isBotFor($betrieb['bezirk_id']))) {
				if ($_POST['form_submit'] == 'zeiten') {
					$range = range(0, 6);
					global $g_data;
					$this->model->clearAbholer($_GET['id']);
					foreach ($range as $r) {
						if (isset($_POST['dow' . $r])) {
							handleTagselect('dow' . $r);
							foreach ($g_data['dow' . $r] as $fs_id) {
								$this->model->addAbholer($_GET['id'], $fs_id, $r);
							}
						}
					}
				} else {
					handleTagselect('foodsaver');

					if (!empty($g_data['foodsaver'])) {
						$this->model->addBetriebTeam($_GET['id'], $g_data['foodsaver'], $g_data['verantwortlicher']);
					} else {
						info(s('team_not_empty'));
					}
				}
				info(s('changes_saved'));
				clearPost();
			} elseif (isset($_POST['form_submit']) && $_POST['form_submit'] == 'changestatusform' && ($this->model->isVerantwortlich($_GET['id']) || isOrgaTeam() || isBotFor($betrieb['bezirk_id']))) {
				$this->model->changeBetriebStatus($_GET['id'], $_POST['betrieb_status_id']);
				go(getSelf());
			}

			addTitle($betrieb['name']);

			if ($this->model->isInTeam($_GET['id']) || S::may('orga') || isBotFor($betrieb['bezirk_id'])) {
				if ((!$betrieb['verantwortlich'] && isBotFor($betrieb['bezirk_id']))) {
					$betrieb['verantwortlich'] = true;
					info('<strong>' . s('reference') . ':</strong> ' . s('not_responsible_but_bot'));
				} elseif (!$betrieb['verantwortlich'] && isOrgaTeam()) {
					$betrieb['verantwortlich'] = true;
					info('<strong>' . s('reference') . ':</strong> ' . s('not_responsible_but_orga'));
				}
				if ($betrieb['verantwortlich']) {
					if (!empty($betrieb['requests'])) {
						handleRequests($betrieb);
					}
				}

				setEditData($betrieb);

				addBread($betrieb['name']);

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
							$this->v_utils->v_form_tagselect('foodsaver', array('data' => $this->model->xhrGetTagFsAll())),
							$verantwortlich_select),
						array('submit' => s('save'))
					);

					addHidden('<div id="teamEditor">' . $edit_team . '</div>');

					addJs('
						
						$(".cb-verantwortlicher").click(function(){
							if($(".cb-verantwortlicher:checked").length >= 4)
							{
								pulseError(\'' . jsSafe(s('max_3_leader')) . '\');
								return false;
							}
							
						});		
						$("#team-form").submit(function(ev){
							if($(".cb-verantwortlicher:checked").length == 0)
							{
								pulseError(\'' . jsSafe(s('verantwortlicher_must_be')) . '\');
								ev.preventDefault();
								return false;
							}
						});	
						');

					addJsFunc('
							function u_fetchconfirm(fsid,date,el)
							{
								var item = $(el);
								showLoader();
								$.ajax({
									url:"xhr.php?f=fetchConfirm",
									data: {
										fsid:parseInt(fsid),
										bid:' . (int)$betrieb['id'] . ',
										date: date
									},
									success: function(ret){
										if(ret == 1)
										{
											item.parent().removeClass("unconfirmed");
										}
									},
									complete: function(){
										hideLoader();
									}
								});		
							}	
							function u_fetchdeny(fsid,date,el)
							{
								var item = $(el);
								showLoader();	
								$.ajax({
									url:"xhr.php?f=fetchDeny",
									data: {
										fsid:parseInt(fsid),
										bid:' . (int)$betrieb['id'] . ',
										date: date
									},
									success: function(ret){
										if(ret == 1)
										{
											item.parent().parent().append(\'<li class="filled empty timedialog-add-me"><a onclick="return false;" href="#"><img alt="nobody" src="img/nobody.gif"></a></li>\');
											item.parent().remove();
										}
									},
									complete: function(){
										hideLoader();
									}
								});			
							}
						');
				}

				/*pinnwand*/
				addJsFunc('
					function u_clearDialogs()
					{
						$(".datefetch").val("");		
						$(".shure_date").show();
						$(".shure_range_date").hide();
						$(".rangeFetch").hide();
						$("button").show();
						
					}
					function u_updatePosts(){
						$.ajax({
							dataType:"json",
							data: $("div#pinnwand form").serialize(),
							url:"xhr.php?f=getPinPost",
							success : function(data){
								if(data.status == 1)
								{
									$("#pinnwand .posts").html(data.html);
								}
							}
						});
					}
					
					function u_undate(date,date_format)
					{
						$("#u_undate").dialog("option","title","' . s('del_date_for') . ' " + date_format);
							
						$("#team_msg-wrapper").hide();
						$("#have_backup").show();
						$("#msg_to_team").show();
						$("#send_msg_to_team").hide();
							
						$("#undate-date").val(date);
						$("#u_undate").dialog("open");
						msg = "' . jsSafe(str_replace(array('{BETRIEB}'), array($betrieb['name']), s('tpl_msg_to_team')), '"') . '";
						msg = msg.replace("{DATE}",date_format);
						$("#team_msg").val(msg);
					}
					');
				addStyle('#team_msg{width:358px;}');
				addHidden('
						<div id="u_undate">
							' . $this->v_utils->v_info(s('shure_of_backup'), s('attention')) . '
							<input type="hidden" name="undate-date" id="undate-date" value="" />
							
							' . $this->v_utils->v_form_textarea('team_msg') . '
						</div>
					');

				addJs('
					$("#team_msg-wrapper").hide();
					$("#u_undate").dialog({
						autoOpen: false,
						modal: true,
						width:400,
						buttons: [
							{
								text:"' . s('have_backup') . '",
								click:function(){
									showLoader();
									$.ajax({
										url:"xhr.php?f=delDate",
										data:{"date":$("#undate-date").val(),"bid":' . (int)$betrieb['id'] . '},
										dataType:"json",
										success: function(ret){
											if(ret.status == 1)
											{
												$(".fetch-" + $("#undate-date").val().replace(/[^0-9]/g,"") + "-' . fsId() . '" ).hide();
											}
											else
											{
												hideLoader();	
											}
										},
										complete: function(){
											$("#u_undate").dialog("close");
											hideLoader();
										}
									});
								},
								id: "have_backup"
							},
							{
								text:"' . s('msg_to_team') . '",
								click:function(){
									$("#team_msg-wrapper").show();
									//$("#u_undate").dialog("option","height",400);
									$("#have_backup").hide();
									$("#msg_to_team").hide();
									$("#send_msg_to_team").show();
								},
								id: "msg_to_team"
							},
							{
								text:"' . s('del_and_send') . '",
								click:function(){
									showLoader();
									$.ajax({
										url:"xhr.php?f=delDate",
										data:{"date":$("#undate-date").val(),"msg":$("#team_msg").val(),"bid":' . (int)$betrieb['id'] . '},
										dataType:"json",
										success: function(ret){
											if(ret.status == 1)
											{
												$(".fetch-" + $("#undate-date").val().replace(/[^0-9]/g,"") + "-' . fsId() . '" ).hide();
											}
											else
											{
												hideLoader();	
											}
										},
										complete: function(){
											$("#u_undate").dialog("close");
											hideLoader();
										}
									});
								},
								id: "send_msg_to_team",
								css:{"display":"none"}
							}
						]
					});
							
					$("#comment-post").hide();
					$("div#pinnwand form textarea").focus(function(){
						$("#comment-post").show();
					});
					$("div#pinnwand form textarea").blur(function(){
						//$("#comment-post").hide();
					});
							
					
					$("div#pinnwand form input.submit").button().bind("keydown", function(event) {
						  $("div#pinnwand form").submit();
					});
					$("div#pinnwand form").submit(function(e){
						e.preventDefault();
						if($("div#pinnwand form textarea").val() != $("div#pinnwand form textarea").attr("title"))
						{
							$.ajax({
								dataType:"json",
								data: $("div#pinnwand form").serialize(),
								url:"xhr.php?f=addPinPost&team=' . $betrieb['team_js'] . '",
								success : function(data){
									if(data.status == 1)
									{
										$("div#pinnwand form textarea").val($("div#pinnwand form textarea").attr("title"));
										$("#pinnwand .posts").html(data.html);
									}
								}
							});
						}
					});
				');

				/*Infos*/
				$betrieb['menge'] = '';
				if ($menge = abhm($betrieb['abholmenge'])) {
					$betrieb['menge'] = $menge;
				}

				$info = '';
				if (!empty($betrieb['besonderheiten'])) {
					$info .= $this->v_utils->v_input_wrapper(s('besonderheiten'), nl2br($betrieb['besonderheiten']));
				}
				if ($betrieb['menge'] > 0) {
					$info .= $this->v_utils->v_input_wrapper(s('menge'), $betrieb['menge']);
				}
				if ($betrieb['presse'] == 1) {
					$info .= $this->v_utils->v_input_wrapper('Namensnennung', 'Dieser Betrieb darf &ouml;ffentlich genannt werden.');
				} elseif ($betrieb['presse'] == 0) {
					$info .= $this->v_utils->v_input_wrapper('Namensnennung', 'Bitte diesen Betrieb niemals &ouml;ffentlich (z.<span style="white-space:nowrap">&thinsp;</span>B. bei Essensk&ouml;rben, Facebook oder Presseanfragen) nennen!');
				}

				addContent($this->v_utils->v_field(
					$this->v_utils->v_input_wrapper(s('address'), $betrieb['str'] . ' ' . $betrieb['hsnr'] . '<br />' . $betrieb['plz'] . ' ' . $betrieb['stadt']) .
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
					$menu[] = array('name' => s('fetch_history'), 'click' => "ajreq('fetchhistory',{app:'betrieb',bid:" . (int)$betrieb['id'] . '});');
					$menu[] = array('name' => s('edit_betrieb'), 'href' => '/?page=betrieb&a=edit&id=' . $betrieb['id']);
					$menu[] = array('name' => s('edit_team'), 'click' => '$(\'#teamEditor\').dialog({modal:true,width:425,title:\'' . s('edit_team') . '\'});');
					$menu[] = array('name' => s('edit_fetchtime'), 'click' => '$(\'#bid\').val(' . (int)$betrieb['id'] . ');$(\'#dialog_abholen\').dialog(\'open\');return false;');
				}
				if (!$betrieb['verantwortlich'] || isOrgaTeam() || isBotschafter()) {
					$menu[] = array('name' => s('betrieb_sign_out'), 'click' => 'u_betrieb_sign_out(' . (int)$betrieb['id'] . ');return false;');
				}

				if (!empty($menu)) {
					addContent($this->v_utils->v_menu($menu, s('options')), CNT_LEFT);
				}

				addContent(
					$this->v_utils->v_field(
						$this->view->u_team($betrieb) . '',

						$betrieb['name'] . '-Team'
					),
					CNT_LEFT
				);

				if (!$betrieb['jumper'] || S::may('orga')) {
					addJs('
							u_updatePosts();
							//setInterval(u_updatePosts,5000);		
						');

					$opt = array();
					if (isMob()) {
						$opt = array('class' => 'moreswap moreswap-height-200');
					}
					addContent($this->v_utils->v_field('
							<div id="pinnwand">
								
								<div class="tools ui-padding">
									<form method="get" action="' . getSelf() . '">
										<textarea class="comment textarea inlabel" title="Nachricht schreiben..." name="text"></textarea>
										<div align="right">
											<input id="comment-post" type="submit" class="submit" name="msg" value="' . s('send') . '" />
										</div>
										<input type="hidden" name="bid" value="' . (int)$betrieb['id'] . '" />
									</form>
								</div>
							
								<div class="posts"></div>
							</div>', 'Pinnwand', $opt));
					/*pinnwand ende*/
				} else {
					addContent($this->v_utils->v_info('Du bist momentan auf der Warteliste, sobald Hilfe benötigt wird wirst Du kontaktiert.'));
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

					addContent($this->v_utils->v_field($cnt, s('responsible_foodsaver'), array('class' => 'ui-padding')), CNT_LEFT);
				}

				/*
				 * Abholzeiten
				 */

				$click = '';
				$click = 'profile(' . (int)fsId() . ');return false;';
				$confclass = 'unconfirmed';
				$pulseconf = 'pulseInfo("' . jsSafe(s('wait_for_confirm')) . '");';
				if ($betrieb['verantwortlich']) {
					$confclass = 'confirmed';
					$pulseconf = '';
				}

				addHidden('
					<div id="timedialog">
						
						<input type="hidden" name="timedialog-id" id="timedialog-id" value="" />
						<input type="hidden" name="timedialog-date" id="timedialog-date" value="" />
							
						<span class="shure_date" id="shure_date">' . $this->v_utils->v_info(sv('shure_date', array('label' => '<span id="date-label"></span>'))) . '</span>
						<span class="shure_range_date" id="shure_range_date" style="display:none;">' . $this->v_utils->v_info(sv('shure_range_date', array('label' => '<span id="range-day-label"></span>'))) . '</span>
						<div class="rangeFetch" id="rangeFetch" style="display:none;">
						
								' . $this->v_utils->v_input_wrapper(s('zeitraum'), '<input type="text" value="" id="timedialog-from" name="timedialog-from" class="datefetch input text value"> bis <input type="text" value="" id="timedialog-to" name="timedialog-to" class="datefetch input text value">') . '
						
						</div>
					</div>
					<div id="delete_shure" title="' . s('delete_sure_title') . '">
						' . $this->v_utils->v_info(s('delete_post_sure')) . '
						<span class="sure" style="display:none">' . s('sure') . '</span>
						<span class="abort" style="display:none">' . s('abort') . '</span>
					</div>
					<div id="signout_shure" title="' . s('signout_sure_title') . '">
						' . $this->v_utils->v_info(s('signout_sure')) . '
						<span class="sure" style="display:none">' . s('sure') . '</span>
						<span class="abort" style="display:none">' . s('abort') . '</span>
					</div>');

				addJsFunc('
						var clicked_pid = null;
						function u_delPost(id)
						{
							clicked_pid = id;
							$("#delete_shure").dialog("open");
						}
						var signout_bid;
						function u_betrieb_sign_out(bid)
						{
							signout_bid = bid;	
							$("#signout_shure").dialog("open");
						}
					');

				$verified = 0;
				if (isVerified()) {
					$verified = 1;
				}

				//Fix for Issue #171
				$seconds = $betrieb['prefetchtime'];
				if ($seconds >= 86400) {
					$days = $seconds / 86400;
				} else {
					//If Bieb did not set the option "how many weeks in advance can a foodsaver apply" an alternative value
					$days = 7;
				}

				addJs('
					
					$("#signout_shure").dialog({
						autoOpen:false,
						modal:true,
						buttons :[
							{
								text: $("#signout_shure .sure").text(),
								click:function(){
									showLoader();
									
									ajax.req("betrieb","signout",{
										data:{id:' . (int)$_GET['id'] . '},
										success: function(){
											
										}
									});
								}
							},
							{
								text: $("#signout_shure .abort").text(),
								click: function(){
									$("#signout_shure").dialog("close");
								}
							}
						]
					});
							
					$("#delete_shure").dialog({
						autoOpen:false,
						modal:true,
						buttons :[
							{
								text: $("#delete_shure .sure").text(),
								click:function(){
									showLoader();
									$.ajax({
										url:"xhr.php?f=delBPost",
										data:{"pid":clicked_pid},
										success:function(ret){
											if(ret == 1)
											{
												$(".bpost-" + clicked_pid).remove();
												$("#delete_shure").dialog("close");
			
											}
										},
										complete:function(){
											hideLoader();
										}
									});
								}
							},
							{
								text: $("#delete_shure .abort").text(),
								click: function(){
									$("#delete_shure").dialog("close");
								}
							}
						]
					});
							
						$(".timedialog-add-me").click(function(){
							u_clearDialogs();
							
							if(1 == ' . $verified . ')
							{
								date = $(this).children("input")[0].value.split("::")[0];
								day = $(this).children("input")[0].value.split("::")[2];
								label = $(this).children("input")[0].value.split("::")[1];
								id = $(this).children("input")[1].value;
								
								$("#timedialog-date").val(date);
								$("#date-label").html(day + ", " + label);
								$("#range-day-label").html(day.toLowerCase());
								$("#timedialog-id").val(id);
								$("#timedialog").dialog("open");
							}
							else
							{
								pulseInfo(\'' . jsSafe(s('not_verified')) . '\');
							}
						});
						
						$("#timedialog").dialog({
								title:"Sicher?",
								resizable: false,
								modal: true,
								autoOpen:false,
								width:500,
								buttons: {
									"Eintragen": function() {
						
						
										$.ajax({
											url : "xhr.php?f=addFetcher",
											data : {
												date:$("#timedialog-date").val(),
												bid:' . (int)$betrieb['id'] . ',
												from: $("#timedialog-from").val(),
												to: $("#timedialog-to").val()
											},
											success : function(ret){
												u_clearDialogs();
												$("#timedialog").dialog( "close" );
												if(ret == "2")
												{
													reload();
												}
												else if(ret != 0)
												{
									
													$("#"+ $("#timedialog-id").val() +"-button").last().remove();
									
													$("#"+ $("#timedialog-id").val() +"-imglist").prepend(\'<li class="' . $confclass . '"><a onclick="' . $click . '" href="#"><img src="\'+ret+\'" title="Du" /><span>&nbsp;</span></a></li>\');
													' . $pulseconf . '
			
													if($("#"+ $("#timedialog-id").val() +"-imglist li:last").hasClass("empty"))
													{
														$("#"+ $("#timedialog-id").val() +"-imglist li:last").remove();	
													}
			
													$("#"+ $("#timedialog-id").val() +"-imglist li.empty a").attr("title","");
													$("#"+ $("#timedialog-id").val() +"-imglist li.empty").unbind("click");
													$("#"+ $("#timedialog-id").val() +"-imglist li.empty").addClass("nohover");
													$("#"+ $("#timedialog-id").val() +"-imglist li.empty").removeClass("filled");
													$("#"+ $("#timedialog-id").val() +"-imglist li.empty a").tooltip("option", {disabled: true}).tooltip("close");
												}
						
											}
										});
										
						
									},
									"Regelmäßig abholen": function() {
										$("#shure_date").hide();
										$("#rangeFetch").show();
										$("#shure_range_date").show();
						
										 $( "#timedialog-from" ).datepicker({
											defaultDate: "+1w",
											minDate: "0",
											maxDate: "+' . (int)$days . '",
											numberOfMonths: 1,
											onClose: function( selectedDate ) {
												if(selectedDate!="")
												{
													$( "#timedialog-to" ).datepicker( "option", "minDate", selectedDate );
												}
												$( "#timedialog-to" ).datepicker( "option", "maxDate", "+' . (int)$days . '");
											}
										});
			
										$( "#timedialog-to" ).datepicker({
											defaultDate: "+1w",
											minDate: "+2",
											maxDate: "+' . (int)$days . '",
											numberOfMonths: 1,
											onClose: function( selectedDate ) {
												$( "#timedialog-from" ).datepicker( "option", "maxDate", selectedDate );
											}
										});
										$("#timedialog").next().children().children(":nth-child(2)").hide();
									},
									"Abbrechen": function() {
										u_clearDialogs();
										$( this ).dialog( "close" );
									}
								}
							});
					');

				if (is_array($betrieb['abholer'])) {
					foreach ($betrieb['abholer'] as $dow => $a) {
						$g_data['dow' . $dow] = $a;
					}
				}

				$zeiten = $this->model->getAbholzeiten($betrieb['id']);

				$next_dates = $this->view->u_getNextDates($zeiten, $betrieb, $this->model->listUpcommingFetchDates($_GET['id']));

				$abholer = $this->model->getAbholdates($betrieb['id'], $next_dates);

				$days = getDow();

				$scroller = '';

				foreach ($next_dates as $date => $time) {
					$part = explode(' ', $date);
					$date = $part[0];
					$scroller .= $this->view->u_form_checkboxTagAlt(
						$date . ' ' . $time['time'],
						array(
							'data' => $betrieb['team'],
							'url' => 'jsonTeam&bid=' . (int)$betrieb['id'],
							'label' => $days[date('w', strtotime($date))] . ' ' . format_db_date($date) . ', ' . format_time($time['time']),
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
					$zeit_cnt = $this->v_utils->v_info(sv('no_fetchtime', array('name' => $betrieb['name'])), s('attention') . '!') .
						'<p style="margin-top:10px;text-align:center;"><a class="button" href="#" onclick="ajreq(\'adddate\',{app:\'betrieb\',id:' . (int)$_GET['id'] . '});return false;">einzelnen Termin eintragen</a></p>';
				}

				/*
				 * Abholzeiten ändern
				 */
				if ($betrieb['verantwortlich'] || S::may('orga')) {
					hiddenDialog('abholen', array($this->view->u_form_abhol_table($zeiten), $this->v_utils->v_form_hidden('bid', 0), '<input type="hidden" name="team" value="' . $betrieb['team_js'] . '" />'), s('add_fetchtime'), array('reload' => true, 'width' => 500));
				}

				if (!$betrieb['jumper']) {
					if (($betrieb['betrieb_status_id'] == 3 || $betrieb['betrieb_status_id'] == 5)) {
						addContent($this->v_utils->v_field($zeit_cnt, s('next_fetch_dates'), array('class' => 'ui-padding')), CNT_RIGHT);
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
							addHidden('<div id="changeStatus-hidden">' . $this->v_utils->v_form('changeStatusForm', array(
									$this->v_utils->v_form_select('betrieb_status_id', array('value' => $betrieb['betrieb_status_id'], 'values' => $betriebStatusList))
								)) . '</div>');

							addJs('$("#changeStatus").button().click(function(){
								$("#changeStatus-hidden").dialog({
									title: "' . s('change_status') . '",
									modal:true
								});
							});');
							$bt = '<p><span id="changeStatus">' . s('change_status') . '</a></p>';
						}
						addContent($this->v_utils->v_field('<p>' . $this->v_utils->v_getStatusAmpel($betrieb['betrieb_status_id']) . $betriebsStatusName . '</p>' . $bt, s('status'), array('class' => 'ui-padding')), CNT_RIGHT);
					}
				}
			} else {
				if ($betrieb = $this->model->getBetrieb($_GET['id'])) {
					addBread($betrieb['name']);
					info(s('not_in_team'));
					go('/?page=map&bid=' . $_GET['id']);
				} else {
					go('/karte');
				}
			}
		} else {
			addBread('Deine Betriebe');
			addContent($this->v_utils->v_menu(array(
				array('href' => '/?page=betrieb&a=new', 'name' => s('add_new'))
			), 'Aktionen'), CNT_RIGHT);

			$bezirk = getBezirk();
			$betriebe = $this->model->getMyBetriebe();
			addContent($this->view->u_betriebList($betriebe['verantwortlich'], s('you_responsible'), true));
			addContent($this->view->u_betriebList($betriebe['team'], s('you_fetcher'), false));
			addContent($this->view->u_betriebList($betriebe['sonstige'], sv('more_stores', array('name' => $bezirk['name'])), false));
		}
	}

	public function handle_edit()
	{
		global $db;
		global $g_data;
		if (submitted()) {
			$g_data['foodsaver'] = array($g_data['foodsaver']);
			if ($this->model->update_betrieb($_GET['id'], $g_data)) {
				info(s('betrieb_edit_success'));
				goPage();
			} else {
				error(s('error'));
			}
		}
	}

	public function handle_add()
	{
		global $db;
		global $g_data;
		if (submitted()) {
			$g_data['foodsaver'] = array($g_data['foodsaver']);
			if ($this->model->add_betrieb($g_data)) {
				info(s('betrieb_add_success'));
				goPage();
			} else {
				error(s('error'));
			}
		}
	}
}
