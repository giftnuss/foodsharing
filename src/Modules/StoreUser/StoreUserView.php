<?php
/**
 * Created by IntelliJ IDEA.
 * User: matthias
 * Date: 11.02.18
 * Time: 14:30.
 */

namespace Foodsharing\Modules\StoreUser;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\View;

class StoreUserView extends View
{
	public function u_getVerantwortlicher($betrieb)
	{
		$out = array();
		foreach ($betrieb['foodsaver'] as $fs) {
			if ($fs['verantwortlich'] == 1) {
				$out[] = $fs;
			}
		}

		return $out;
	}

	public function handleRequests($betrieb)
	{
		$out = '<table class="pintable">';
		$odd = 'odd';
		$this->func->addJs('$("table.pintable tr td ul li").tooltip();');

		$this->func->addJsFunc('
	function acceptRequest(fsid,bid){
		showLoader();
		$.ajax({
			dataType:"json",
			data: "fsid="+fsid+"&bid="+bid,
			url:"xhr.php?f=acceptRequest",
			success : function(data){
				if(data.status == 1)
				{
					reload();
					//$("tr.request-"+fsid).fadeOut();
				}
			},
			complete:function(){hideLoader();}
		});
	}
	function warteRequest(fsid,bid){
		showLoader();
		$.ajax({
			dataType:"json",
			data: "fsid="+fsid+"&bid="+bid,
			url:"xhr.php?f=warteRequest",
			success : function(data){
				if(data.status == 1)
				{
					reload();
					//$("tr.request-"+fsid).fadeOut();
				}
			},
			complete:function(){hideLoader();}
		});
	}
	function denyRequest(fsid,bid){
		showLoader();
		$.ajax({
			dataType:"json",
			data: "fsid="+fsid+"&bid="+bid,
			url:"xhr.php?f=denyRequest",
			success : function(data){
				if(data.status == 1)
				{
					reload();
				}
			},
			complete:function(){hideLoader();}
		});
	}');

		foreach ($betrieb['requests'] as $r) {
			if ($odd == 'even') {
				$odd = 'odd';
			} else {
				$odd = 'even';
			}
			$out .= '
		<tr class="' . $odd . ' request-' . $r['id'] . '">
			<td class="img" width="35px"><a href="#" onclick="profile(' . (int)$r['id'] . ');return false;"><img src="' . $this->func->img($r['photo']) . '" /></a></td>
			<td style="padding-top:17px;"><span class="msg"><a href="#" onclick="profile(' . (int)$r['id'] . ');return false;">' . $r['name'] . '</a></span></td>
			<td style="width:92px;padding-top:17px;"><span class="msg"><ul class="toolbar"><li class="ui-state-default ui-corner-left" title="Ablehnen" onclick="denyRequest(' . (int)$r['id'] . ',' . (int)$betrieb['id'] . ');"><span class="ui-icon ui-icon-closethick"></span></li><li class="ui-state-default" title="Auf die Springerliste setzen" onclick="warteRequest(' . (int)$r['id'] . ',' . (int)$betrieb['id'] . ');"><span class="ui-icon ui-icon-star"></span></li><li class="ui-state-default ui-corner-right" title="Akzeptieren" onclick="acceptRequest(' . (int)$r['id'] . ',' . (int)$betrieb['id'] . ');"><span class="ui-icon ui-icon-heart"></span></li></ul></span></td>
		</tr>';
		}

		$out .= '</table>';

		$this->func->hiddenDialog('requests', array($out));
		$this->func->addJs('$("#dialog_requests").dialog("option","title","Anfragen für ' . $this->func->jsonSafe($betrieb['name'], '"') . '");');
		$this->func->addJs('$("#dialog_requests").dialog("option","buttons",{});');
		$this->func->addJs('$("#dialog_requests").dialog("open");');
	}

	public function u_innerRow($id, $betrieb)
	{
		$out = '';
		if ($betrieb[$id] != '') {
			$betrieb[$id] = trim($betrieb[$id]);
			nl2br($betrieb[$id]);
			$out = '<div class="innerRow"><span class="label">' . $this->func->s($id) . '</span><span class="cnt">' . $betrieb[$id] . '</span></div><div style="clear:both"></div>';
		}

		return $out;
	}

	public function u_team($betrieb)
	{
		$id = $this->func->id('team');
		$out = '<ul id="' . $id . '" class="team">';
		$jssaver = array();
		$sleeper = '';

		foreach ($betrieb['foodsaver'] as $fs) {
			$jssaver[] = (int)$fs['id'];

			$class = '';
			$click = 'profile(' . (int)$fs['id'] . ');';
			if ($fs['verantwortlich'] == 1) {
				$class .= ' verantwortlich';
			} elseif ($betrieb['verantwortlich'] || $this->func->isBotFor($betrieb['bezirk_id']) || $this->func->isOrgaTeam()) {
				$class .= ' context-team';
				$click = '';
			}

			if ($fs['verified'] != 1) {
				$class .= ' notVerified';
			}

			$tel = '';
			$number = false;
			if (!empty($fs['handy'])) {
				$number = $fs['handy'];
				$tel .= '<span class="item phone">' . (($this->func->isMob()) ? '<a href="tel:' . $fs['handy'] . '"><span>' . $fs['handy'] . '</span></a>' : $fs['handy']) . '</span>';
			}
			if (!empty($fs['telefon'])) {
				$tel .= '<span class="item phone">' . (($this->func->isMob()) ? '<a href="tel:' . $fs['telefon'] . '"><span>' . $fs['telefon'] . '</span></a>' : $fs['telefon']) . '</span>';
			}

			if ((int)$fs['last_fetch'] > 0) {
				$last = $this->func->sv('stat_fetchcount', array(
					'date' => date('d.m.Y', $fs['last_fetch'])
				));
			} else {
				$last = $this->func->sv('stat_fetchcount_none', array());
			}

			$onclick = ' onclick="' . $click . 'return false;"';
			$href = '#';
			if ($number !== false && $this->func->isMob()) {
				$onclick = '';
				$href = 'tel:' . preg_replace('/[^0-9\+]/', '', $number);
			}

			$tmp = '
				<li class="team fs-' . $fs['id'] . '">
					<a class="ui-corner-all' . $class . '" title="#tt-tt-' . $fs['id'] . '" href="' . $href . '"' . $onclick . '>
						' . $this->func->avatar($fs) . '
						<span class="infos">
							<span class="item"><strong>' . $fs['name'] . '</strong> <span style="float:right">(' . $fs['stat_fetchcount'] . ')</span></span>
							' . $tel . '
						</span>
					</a>
					<span style="display:none" class="tt-' . $fs['id'] . '">
						' . $last . '
					</span>
				</li>';

			if ($fs['sleep_status'] == 0) {
				$out .= $tmp;
			} else {
				$sleeper .= $tmp;
			}
		}

		if ($betrieb['springer']) {
			foreach ($betrieb['springer'] as $fs) {
				$jssaver[] = (int)$fs['id'];

				$class = '';
				$click = 'profile(' . (int)$fs['id'] . ');';
				if ($betrieb['verantwortlich'] || $this->func->isBotFor($betrieb['bezirk_id']) || $this->func->isOrgaTeam()) {
					$class .= ' context-jumper';
					$click = '';
				}

				$tel = '';
				$number = false;
				if (!empty($fs['handy'])) {
					$tel .= '<span class="item phone"><span>' . $fs['handy'] . '</span></span>';
				}
				if (!empty($fs['telefon'])) {
					$tel .= '<span class="item phone"><span>' . $fs['telefon'] . '</span></span>';
				}

				$onclick = ' onclick="' . $click . 'return false;"';
				$href = '#';
				if ($this->func->isMob() && $number !== false) {
					$onclick = '';
					$href = 'tel:' . preg_replace('/[^0-9\+]/', '', $number);
				}

				$tmp = '
					<li class="jumper fs-' . $fs['id'] . '">
						<a class="ui-corner-all' . $class . '" title="#tt-tt-' . $fs['id'] . '" href="' . $href . '"' . $onclick . '>
							' . $this->func->avatar($fs) . '
							<span class="infos">
								<span class="item"><strong>' . $fs['name'] . '</strong></span>
								' . $tel . '
							</span>
						</a>
						<span style="display:none" class="tt-' . $fs['id'] . '">
							' . $fs['vorname'] . ' ist Springer seit ' . date('m/y', $fs['add_date']) . '
						</span>
					</li>';

				if ($fs['sleep_status'] == 0) {
					$out .= $tmp;
				} else {
					$sleeper .= $tmp;
				}
			}
		}

		$out .= $sleeper . '</ul><div style="clear:both"></div>';

		$this->func->addJsFunc('
		function u_contextAction(action,fsid)
		{
			if(action == "message")
			{
				chat(fsid);
			}
			else if(action == "report")
			{
				ajreq("reportDialog",{app:"report",fsid:fsid,bid:' . (int)$betrieb['id'] . '});
			}
			else
			{
				showLoader();
				$.ajax({
					url: "xhr.php?f=bcontext",
					data: {"action":action,"fsid":fsid,"bid":' . (int)$betrieb['id'] . ',"bzid":' . (int)$betrieb['bezirk_id'] . '},
					dataType: "json",
					success: function(data){
						if(data.status == 1)
						{
							if(action == "toteam")
							{
								$(".fs-"+fsid).removeClass("jumper");
								$(".fs-"+fsid).addClass("team");
							}
							else if(action == "tojumper")
							{
								$(".fs-"+fsid).removeClass("team");
								$(".fs-"+fsid).addClass("jumper");
							}
							else if(action == "delete")
							{
								$(".fs-"+fsid).remove();
							}
						}
					},
					complete: function(){
						hideLoader();
					}
				});
			}
		}
	');

		$this->func->addJs('
		function createJumperMenu() {
	        return {
	            callback: function(key, options) {
	                var m = "clicked: " + key;
					var li = $(this).parent();
				
					fsid = li.attr("class").split("fs-")[1];
				
	                u_contextAction(key,fsid);
	            },
	            items: {
					"report": {name: "Melden",icon:"report"},
	                "toteam": {name: "Ins Team aufnehmen",icon:"accept"},
	                "delete": {name: "Aus Team löschen",icon:"delete"},
					"message":{name: "Nachricht schreiben",icon:"message"}
	            }
	        };
	    }
				
		function createMenu() {
	        return {
	            callback: function(key, options) {
	                var m = "clicked: " + key;
					var li = $(this).parent();
				
					fsid = li.attr("class").split("fs-")[1];
				
	                u_contextAction(key,fsid);
	            },
	            items: {
					"report": {name: "Melden",icon:"report"},
	                "tojumper": {name: "Auf die Springerliste",icon:"wait"},
	                "delete": {name: "Aus Team löschen",icon:"delete"},
					"message":{name: "Nachricht schreiben",icon:"message"}
	            }
	        };
	    }
				
		$(".context-team").on("click", function(e){
	        var $this = $(this);
	        // store a callback on the trigger
	        $this.data("runCallbackThingie", createMenu);
	        var _offset = $this.offset(),
	            position = {
	                x: _offset.left - 42, 
	                y: _offset.top - 160
	            }
	        // open the contextMenu asynchronously
	        $this.contextMenu(position);
	    });
				
		$(".context-jumper").on("click", function(e){
	        var $this = $(this);
	        // store a callback on the trigger
	        $this.data("runCallbackThingie", createJumperMenu);
	        var _offset = $this.offset(),
	            position = {
	                x: _offset.left - 42, 
	                y: _offset.top - 95
	            }
	        // open the contextMenu asynchronously
	        $this.contextMenu(position);
	    });
			
				
		$.contextMenu({
	        selector: ".context-team, .context-jumper",
	        trigger: "none",
	        build: function($trigger, e) {
	            // pull a callback from the trigger
	            return $trigger.data("runCallbackThingie")();
	        }
	    });
	');

		if ($betrieb['verantwortlich']) {
			$this->func->addJs('
			$("#team_status").change(function(){
				var val = $(this).val();
				showLoader();
				$.ajax({
					url: "xhr.php?f=bteamstatus&bid=' . (int)$betrieb['id'] . '&s=" + val,
					success: function(){
						hideLoader();
					}
				});
			});		
		');
			global $g_data;
			$g_data['team_status'] = $betrieb['team_status'];

			$out .= '
			<div class="ui-padding">' .
				$this->v_utils->v_form_select('team_status', array(
					'values' => array(
						array('id' => 0, 'name' => 'Team ist voll'),
						array('id' => 1, 'name' => 'HelferInnen gesucht'),
						array('id' => 2, 'name' => 'Es werden dringend HelferInnen gesucht!')
					)
				)) . '</div>';
		}

		return $out;
	}

	public function u_betriebList($betriebe, $title, $verantwortlich)
	{
		if (empty($betriebe)) {
			return '';
		} else {
			$bezirk = false;
			$betriebrows = array();
			foreach ($betriebe as $i => $b) {
				$status = $this->v_utils->v_getStatusAmpel($b['betrieb_status_id']);

				$betriebrows[$i] = array(
					array('cnt' => '<a class="linkrow ui-corner-all" href="/?page=fsbetrieb&id=' . $b['id'] . '">' . $b['name'] . '</a>'),
					array('cnt' => $b['str'] . ' ' . $b['hsnr']),
					array('cnt' => $b['plz']),
					array('cnt' => $status)
				);

				if (isset($b['bezirk_name'])) {
					$betriebrows[$i][] = array('cnt' => $b['bezirk_name']);
					$bezirk = true;
				}

				if ($verantwortlich) {
					$betriebrows[$i][] = array('cnt' => $this->v_utils->v_toolbar(array('id' => $b['id'], 'types' => array('edit'), 'confirmMsg' => 'Soll ' . $b['name'] . ' wirklich unwiderruflich gel&ouml;scht werden?')));
				}
			}

			$head = array(
				array('name' => 'Name', 'width' => 180),
				array('name' => 'Anschrift'),
				array('name' => 'Postleitzahl', 'width' => 90),
				array('name' => 'Status', 'width' => 50));
			if ($bezirk) {
				$head[] = array('name' => 'Region');
			}
			if ($verantwortlich) {
				$head[] = array('name' => 'Aktionen', 'sort' => false, 'width' => 30);
			}

			$table = $this->v_utils->v_tablesorter($head, $betriebrows);

			return $this->v_utils->v_field($table, $title);
		}
	}

	public function betrieb_form($foodsaver)
	{
		return $this->v_utils->v_quickform('betrieb', array(
			$this->v_utils->v_form_text('name'),
			$this->v_utils->v_form_text('plz'),
			$this->v_utils->v_form_text('str'),
			$this->v_utils->v_form_text('hsnr'),

			$this->v_utils->v_form_select('kette_id', array('add' => true)),
			$this->v_utils->v_form_select('betrieb_kategorie_id', array('add' => true)),

			$this->v_utils->v_form_select('betrieb_status_id'),

			$this->v_utils->v_form_text('ansprechpartner'),
			$this->v_utils->v_form_text('telefon'),
			$this->v_utils->v_form_text('fax'),
			$this->v_utils->v_form_text('email'),
			$this->v_utils->v_form_select('foodsaver', array('values' => $foodsaver))
		));
	}

	/**
	 * @param $fetch_dow
	 * @param $betrieb
	 *
	 * @return array
	 */
	public function u_getNextDates($fetch_dow, $betrieb, $irregularPickups)
	{
		$out = array();

		if ($fetch_dow) {
			$start_days = array();
			foreach ($fetch_dow as $dow => $fd) {
				$part = explode('-', $dow);
				$dow = (int)$part[0];

				if ($dow == date('w')) {
					$start_days[] = array(
						// Zeige auch schon vergangene Termine an q'n'dirty
						'ts' => time(),
						'time' => $fd['time'],
						'fetcher' => $fd['fetcher'],
						'dow' => $dow
					);
				} else {
					$start_days[] = array(
						'ts' => strtotime('next ' . $this->u_day($dow)),
						'time' => $fd['time'],
						'fetcher' => $fd['fetcher'],
						'dow' => $dow
					);
				}
			}

			$month_change = 0;
			$y = 0;
			$cur_month = date('m', $start_days[0]['ts']);
			$i = 0;

			while ($i <= 35) {
				foreach ($start_days as $sd) {
					++$i;
					$ts = $sd['ts'] + ($y * 604800);
					$date = new \DateTime(date('Y-m-d', $sd['ts']) . ' ' . $sd['time'], new \DateTimeZone('Europe/Berlin'));
					//Ein Tag addieren
					if ($y > 0) {
						$date->add(new \DateInterval('P' . ($y * 7) . 'D'));
					}
					//DateTime Formatierte 2010-03-05 Ausgabe

					$out[$date->format('Y-m-d H-i')] = array(
						'time' => $date->format('H:i:s'),
						'fetcher' => $sd['fetcher']
					);
				}
				if (date('m', $ts) != $cur_month) {
					++$month_change;
				}

				++$y;
			}
		}

		if ($adddates = $irregularPickups) {
			$out = array_merge($out, $adddates);
		}

		ksort($out);

		if (!empty($out)) {
			$out2 = array();
			foreach ($out as $key => $o) {
				$k = explode(' ', $key);
				$k = explode('-', $k[0]);
				$time = mktime(0, 0, 0, $k[1], $k[2], $k[0]);
				$out2[$key] = $o;

				$max = 1209600;
				if ((int)$betrieb['prefetchtime'] > 0) {
					$max = $betrieb['prefetchtime'];
				}

				if ($time - time() >= $max) {
					return $out2;
				}
			}
		}

		return $out;
	}

	public function u_day($dow)
	{
		$days = array(
			0 => 'Sunday',
			1 => 'Monday',
			2 => 'Tuesday',
			3 => 'Wednesday',
			4 => 'Thursday',
			5 => 'Friday',
			6 => 'Saturday'
		);

		return $days[$dow];
	}

	public function u_form_checkboxTagAlt($date, $option = array())
	{
		$ago = false;
		if (strtotime($date) < time()) {
			$ago = true;
		}

		if (!$ago && ($option['verantwortlich'] || S::may('orga') || $this->func->isBotFor($option['bezirk_id']))) {
			$this->func->addJsFunc('
			function u_timetableAction(key,el)
			{
				val = $(el).children("input:first").val().split(":::");
				
				if(key == "confirm")
				{
					u_fetchconfirm(val[0],val[1],el);
				}
				else if(key == "deny")
				{
					u_fetchdeny(val[0],val[1],el);
				}
				else if(key == "message")
				{
					chat(val[0]);
				}
			}
		');
			$this->func->addJs('
			function createConfirmedMenu() {
		        return {
		            callback: function(key, options) {
		                u_timetableAction(key,this);
		            },
		            items: {
		                "deny": {name: "Austragen",icon:"delete"},
						"message":{name: "Nachricht schreiben",icon:"message"}
		            }
		        };
		    }
					
			function createUnconfirmedMenu() {
		        return {
		            callback: function(key, options) {				
		                u_timetableAction(key,this);
		            },
		            items: {
		                "confirm": {name: "Bestätigen",icon:"accept"},
		                "deny": {name: "Austragen",icon:"delete"},
						"message":{name: "Nachricht schreiben",icon:"message"}
		            }
		        };
		    }
					
			$(".context-confirmed").on("click", function(e){
		        var $this = $(this);
		        $this.data("runCallbackThingie", createConfirmedMenu);
		        var _offset = $this.offset(),
		            position = {
		                x: _offset.left - 42, 
		                y: _offset.top - 57
		            }
		        $this.contextMenu(position);
		    });
					
			$(".context-unconfirmed").on("click", function(e){
		        var $this = $(this);
		        $this.data("runCallbackThingie", createUnconfirmedMenu);
		        var _offset = $this.offset(),
		            position = {
		                x: _offset.left - 42, 
		                y: _offset.top - 95
		            }
		        $this.contextMenu(position);
		    });
				
					
			$.contextMenu({
		        selector: ".context-confirmed, .context-unconfirmed",
		        trigger: "none",
		        build: function($trigger, e) {
		            return $trigger.data("runCallbackThingie")();
		        }
		    });		
		');
		}
		$id = 'fetch-' . $this->func->id($date);
		$out = '<input type="hidden" id="' . $id . '-date" name="' . $id . '-date" value="' . $date . '" />';

		$bindabei = false;

		$out .= '
		<ul class="imglist" id="' . $id . '-imglist">';

		$i = 0;

		if ($values = $this->func->getValue($id)) {
			foreach ($values as $fs) {
				if ($fs['id'] == $this->func->fsId()) {
					$bindabei = true;
				}

				$aclass = '';
				$class = $id . '-' . $fs['id'];
				$click = 'profile(' . (int)$fs['id'] . ');';

				if (!$ago && $option['verantwortlich'] && $fs['confirmed'] == 0) {
					$aclass = 'context-unconfirmed';
					$click = '';
				} elseif (!$ago && ($option['verantwortlich'] || $this->func->isBotFor($option['bezirk_id']) || $this->func->isOrgaTeam())) {
					$aclass .= 'context-confirmed';
					$click = '';
				}

				if ($fs['id'] == $this->func->fsId() && !$ago) {
					$click = 'u_undate(\'' . $date . '\',\'' . $this->func->format_db_date($date) . '\');return false;';
					$aclass = '';
				}

				if ($fs['confirmed'] == 0) {
					$class .= ' unconfirmed';
					$fs['name'] = $this->func->sv('not_confirmed', array('name' => $fs['name']));
				}
				$out .= '
			<li class="filled ' . $class . '">
				<a class="' . $aclass . '" href="#" onclick="' . $click . 'return false;" title="' . $fs['name'] . '"><input type="hidden" name="date" value="' . $fs['id'] . ':::' . $date . '" /><img src="' . $this->func->img($fs['photo']) . '" alt="' . $fs['name'] . '" /><span>&nbsp;</span></a>
			</li>';
				++$i;
			}
		}

		if (isset($option['fetcher_count']) && $i < $option['fetcher_count']) {
			for ($y = 0; $y < ($option['fetcher_count'] - $i); ++$y) {
				if (!$bindabei) {
					$out .= '
				<li class="filled empty timedialog-add-me">
					<a href="#" onclick="return false;" title="' . $this->func->s('add_me_here') . '"><img src="img/nobody.gif" alt="nobody" /></a>
					<input type="hidden" name="' . $id . '-date" class="daydate" value="' . $date . '::' . $this->func->format_db_date($date) . '::' . $this->func->s('dow' . date('w', strtotime($date))) . '" />
					<input type="hidden" name="' . $id . '-dateid" class="dayid" value="' . $id . '" />
				</li>';
				} else {
					$out .= '
				<li class="empty nohover">
					<a href="#" onclick="return false;" title=""><img src="img/nobody.gif" alt="nobody" /></a>
				</li>';
				}
			}
		}

		$out .= '
		</ul><div style="clear:both;"></div>';

		$part = explode(' ', $date);

		if ($part[0] == date('Y-m-d')) {
			$option['class'] = 'today';
		}

		$dellink = '';

		if (!$ago && isset($option['field']['additional']) && ($option['verantwortlich'] || $this->func->isOrgaTeam() || $this->func->isBotFor($option['bezirk_id']))) {
			$dellink = '<br /><a class="button" href="#" onclick="if(confirm(\'Termin wirklich löschen?\')){ajreq(\'deldate\',{app:\'betrieb\',id:\'' . (int)$_GET['id'] . '\',time:\'' . $option['field']['datetime'] . '\'});}return false;">Termin löschen</a>';
		}

		return $this->v_utils->v_input_wrapper($this->func->s($id), $out . $dellink, $id, $option);
	}

	public function u_form_abhol_table($zeiten = false, $option = array())
	{
		$this->func->addJs('
	$(".nft-remove").button({
		text: false,
		icons: {
		 	primary: "ui-icon-minus"
		 }
	}).click(function(){
		$this = $(this);
		$this.parent().parent().remove();
	});
			
	$(".timetable").on("keyup", ".fetchercount", function(){
		if(this.value != "")
		{
			val = parseInt("0"+this.value,10);
			if(val == 0 )
			{
				val = 1;
			}
			else if(val > 2)
			{
				pulseError("Du hast mehr als zwei Personen zum Abholen angegeben.<br />In der Regel sollten <strong>nicht mehr als zwei Leute</strong> zu einem Betrieb gehen. Zu viele Abholer führten schon oft zum Ende einer Kooperation. <br />Zur Not geht einer von Euch mit Auto oder Anhänger vor und Ihr trefft Euch außer Reichweite vom Betrieb.",{
					sticky:true
				});
			}
			this.value = val;
		}
	});
	$("#nft-add").button({
		text: false
	}).click(function(){
		$("table.timetable tbody").append($("table#nft-hidden-row tbody").html());
		clname = "odd";
		$("table.timetable tbody tr").each(function(){
			if(clname == "odd")
			{
				clname = "even";
			}
			else
			{
				clname = "odd";
			}
			
			$this = $(this);
			$this.removeClass("odd even");
			$this.addClass(clname);
			
		});
		$(".nft-remove").button({
			text: false,
			icons: {
				primary: "ui-icon-minus"
			}
		}).click(function(){
			$this = $(this);
			$this.parent().parent().remove();
		});
	});
	');
		$out = '
		<table class="timetable">
			
			<thead>
				<tr>
					<th class="ui-padding">' . $this->func->s('day') . '</th>
					<th class="ui-padding">' . $this->func->s('time') . '</th>
					<th class="ui-padding">' . $this->func->s('fetcher_count') . '</th>
				</tr>
			</thead>
			<tfoot>
			    <tr>
					<td colspan="3"><span id="nft-add">' . $this->func->s('add') . '</span></td>
				</tr>
			</tfoot>
			<tbody>';
		$dows = range(1, 6);
		$dows[] = 0;
		$odd = 'even';
		if (is_array($zeiten)) {
			foreach ($zeiten as $z) {
				if ($odd == 'even') {
					$odd = 'odd';
				} else {
					$odd = 'even';
				}

				$day = '';
				foreach ($dows as $d) {
					$sel = '';
					if ($d == $z['dow']) {
						$sel = ' selected="selected"';
					}
					$day .= '<option' . $sel . ' value="' . $d . '">' . $this->func->s('dow' . $d) . '</option>';
				}

				$time = explode(':', $z['time']);

				$out .= '
			<tr class="' . $odd . '">
			    <td class="ui-padding">
					<select class="nft-row" style="width:100px;" name="newfetchtime[]" id="nft-dow">
						' . $day . '	
					</select>
				  </td>
			      <td class="ui-padding"><select class="nfttime-hour" name="nfttime[hour][]"><option selected="selected" value="' . (int)$time[0] . '">' . $time[0] . '</option><option value="0">00</option><option value="1">01</option><option value="2">02</option><option value="3">03</option><option value="4">04</option><option value="5">05</option><option value="6">06</option><option value="7">07</option><option value="8">08</option><option value="9">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option></select><select class="nfttime-min" name="nfttime[min][]"><option selected="selected" value="' . (int)$time[1] . '">' . $time[1] . '</option><option value="0">00</option><option value="5">05</option><option value="10">10</option><option value="15">15</option><option value="20">20</option><option value="25">25</option><option value="30">30</option><option value="35">35</option><option value="40">40</option><option value="45">45</option><option value="50">50</option><option value="55">55</option></select> Uhr</td>
				  <td class="ui-padding"><input class="fetchercount" style="width:25px;" type="text" name="nft-count[]" value="' . $z['fetcher'] . '"/><button style="float: right; height: 27px" class="nft-remove"></button></td>
			    </tr>';
			}
		}
		$out .= '</tbody></table>';

		$out .= '<table id="nft-hidden-row" style="display:none;">
			<tbody>
			<tr>
			    <td class="ui-padding">
					<select class="nft-row" style="width:100px;" name="newfetchtime[]" id="nft-dow">
						<option value="0">' . $this->func->s('dow0') . '</option>	
						<option value="1">' . $this->func->s('dow1') . '</option>	
						<option value="2">' . $this->func->s('dow2') . '</option>	
						<option value="3">' . $this->func->s('dow3') . '</option>	
						<option value="4">' . $this->func->s('dow4') . '</option>	
						<option value="5">' . $this->func->s('dow5') . '</option>	
						<option value="6">' . $this->func->s('dow6') . '</option>		
					</select>
				  </td>
			      <td class="ui-padding"><select class="nfttime-hour" name="nfttime[hour][]"><option value="0">00</option><option value="1">01</option><option value="2">02</option><option value="3">03</option><option value="4">04</option><option value="5">05</option><option value="6">06</option><option value="7">07</option><option value="8">08</option><option value="9">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20" selected="selected">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option></select><select class="nfttime-min" name="nfttime[min][]"><option value="0" selected="selected">00</option><option value="5">05</option><option value="10">10</option><option value="15">15</option><option value="20">20</option><option value="25">25</option><option value="30">30</option><option value="35">35</option><option value="40">40</option><option value="45">45</option><option value="50">50</option><option value="55">55</option></select> Uhr</td>
				  <td class="ui-padding"><input class="fetchercount" type="text" name="nft-count[]" style="width:25px" value="2"/><button style="float: right; height: 27px"class="nft-remove"></button></td>
			    </tr>
				</tbody>
			</table>';

		return $out;
	}
}
