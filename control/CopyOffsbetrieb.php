<?php

if(getAction('new'))
{
	handle_add();
	
	addBread(s('bread_betrieb'),'/?page=fsbetrieb');
	addBread(s('bread_new_betrieb'));
			
	$content = betrieb_form();

	$right = v_field(v_menu(array(
		pageLink('betrieb','back_to_overview')
	)),s('actions'));
}
elseif($id = getActionId('delete'))
{
	if($db->del_betrieb($id))
	{
		info(s('betrieb_deleted'));
		goPage();
	}
}
elseif($id = getActionId('edit'))
{
	if($db->isVerantwortlich($_GET['id']) || isOrgaTeam()){
		go('/?page=betrieb&a=edit&id='.(int)$_GET['id']);
	}
	else{
		goPage();
	}
}
else if(isset($_GET['id']))
{
	addBread(s('betrieb_bread'),'/?page=fsbetrieb');
	addStyle('textarea.comment{width:475px}.button{margin-right:8px;}div#content{width:514px;}div#right{width:290px;}#right .tagedit-list{width:256px;}#foodsaver-wrapper{padding-top:0px;}');
	global $g_data;
	
	if(isset($_POST['form_submit']) && $_POST['form_submit'] == 'team')
	{
		if($_POST['form_submit'] == 'zeiten')
		{
			$range = range(0,6);
			global $g_data;
			$db->clearAbholer($_GET['id']);
			foreach ($range as $r)
			{
	
				if(isset($_POST['dow'.$r]))
				{
					handleTagselect('dow'.$r);
					foreach ($g_data['dow'.$r] as $fs_id)
					{
						$db->addAbholer($_GET['id'],$fs_id,$r);
					}
						
				}
			}
		}
		else
		{
			handleTagselect('foodsaver');
				
			if(!empty($g_data['foodsaver']))
			{
				$db->addBetriebTeam($_GET['id'],$g_data['foodsaver'],$g_data['verantwortlicher']);
			}
			else
			{
				info(s('team_not_empty'));
			}
		}
		info(s('changes_saved'));
		clearPost();
	}
	else if(isset($_POST['form_submit']) && $_POST['form_submit'] == 'changestatusform')
	{
		$db->changeBetriebStatus($_GET['id'],$_POST['betrieb_status_id']);
		go(getSelf());
	}
	
	$betrieb = $db->getMyBetrieb($_GET['id']);
	
	if($db->isInTeam($_GET['id']) || isOrgaTeam() || isBotFor($betrieb['bezirk_id']))
	{		
		
		
		if((!$betrieb['verantwortlich'] && isBotFor($betrieb['bezirk_id'])))
		{
			$betrieb['verantwortlich'] = true;
			info('<strong>'.s('reference').':</strong> '.s('not_responsible_but_bot'));
		}
		elseif(!$betrieb['verantwortlich'] && isOrgaTeam())
		{
			$betrieb['verantwortlich'] = true;
			info('<strong>'.s('reference').':</strong> '.s('not_responsible_but_orga'));
		}
		if($betrieb['verantwortlich'])
		{
			if(!empty($betrieb['requests']))
			{
				handleRequests($betrieb);
			}
		}
		
		setEditData($betrieb);
		
		
		
		addBread($betrieb['name']);

		$edit_team = '';
		
		$verantwortlich_select = '';
		
		if($betrieb['verantwortlich'])
		{
			$verantwortlich_select = v_form_select('verantwortlicher',array('values'=>$betrieb['foodsaver']));
			
			$edit_team = v_form('team',array(v_form_tagselect('foodsaver',array('data'=>$db->xhrGetTagFs(getBezirkId()))),$verantwortlich_select),array('submit'=>s('save')));
			
			addHidden('<div id="teamEditor">'.$edit_team.'</div>');
			
			$edit_team = '<div class="ui-padding-left ui-padding-right ui-padding-bottom"><span id="teamEditor-open">'.s('edit_team').'</span></div>';
			
			addJs('$("#teamEditor-open").button().click(function(){
				$("#teamEditor").dialog({modal:true,width:425,title:"'.s('edit_team').'"});
			});');
			
			/*
			$edit_team = '<div class="ui-padding-left ui-padding-right ui-padding-bottom">'.v_accordion(array(
				array('name'=>'Team bearbeiten','cnt'=>$edit_team)
			)).'</div>';
			*/
			addJsFunc('
				function u_fetchconfirm(fsid,date,el)
				{
					var item = $(el);
					showLoader();
					$.ajax({
						url:"xhr.php?f=fetchConfirm",
						data: {
							fsid:parseInt(fsid),
							bid:'.(int)$betrieb['id'].',
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
			$("#u_undate").dialog("option","title","'.s('del_date_for').' " + date_format);
				
			$("#team_msg-wrapper").hide();
			$("#have_backup").show();
			$("#msg_to_team").show();
			$("#send_msg_to_team").hide();
				
			$("#undate-date").val(date);
			$("#u_undate").dialog("open");
			msg = "'.jsSafe(str_replace(array('{BETRIEB}'), array($betrieb['name']), s('tpl_msg_to_team'))).'";
			msg = msg.replace("{DATE}",date_format);
			$("#team_msg").val(msg);
		}
		');
		addStyle('#team_msg{width:358px;}');
		addHidden('
			<div id="u_undate">
				<strong>'.v_info(s('attention').'</strong> '.s('shure_of_backup')).'
				<input type="hidden" name="undate-date" id="undate-date" value="" />
				
				'.v_form_textarea('team_msg').'
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
					text:"'.s('have_backup').'",
					click:function(){
						showLoader();
						$.ajax({
							url:"xhr.php?f=delDate",
							data:{"date":$("#undate-date").val(),"bid":'.(int)$betrieb['id'].'},
							dataType:"json",
							success: function(ret){
								if(ret.status == 1)
								{
									$(".fetch-" + $("#undate-date").val().replace(/[^0-9]/g,"") + "-'.fsId().'" ).hide();
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
					text:"'.s('msg_to_team').'",
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
					text:"'.s('del_and_send').'",
					click:function(){
						showLoader();
						$.ajax({
							url:"xhr.php?f=delDate",
							data:{"date":$("#undate-date").val(),"msg":$("#team_msg").val(),"bid":'.(int)$betrieb['id'].'},
							dataType:"json",
							success: function(ret){
								if(ret.status == 1)
								{
									$(".fetch-" + $("#undate-date").val().replace(/[^0-9]/g,"") + "-'.fsId().'" ).hide();
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
				
		u_updatePosts();
		setInterval(u_updatePosts,5000);
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
					url:"xhr.php?f=addPinPost&team='.$betrieb['team_js'].'",
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
		$content .= v_field(u_team($betrieb).$edit_team, $betrieb['name'].'-Team');
		$content .= v_field('
				<div id="pinnwand">
					
					<div class="tools ui-padding">
						<form method="get" action="'.getSelf().'">
							<textarea class="comment textarea inlabel" title="Nachricht schreiben..." name="text"></textarea>
							<div align="right">
								<input id="comment-post" type="submit" class="submit" name="msg" value="'.s('send').'" />
							</div>
							<input type="hidden" name="bid" value="'.(int)$betrieb['id'].'" />
						</form>
					</div>
				
					<div class="posts"></div>
				</div>', 'Pinnwand');
		/*pinnwand ende*/
		
		$zeiten_button = v_dialog_button('abholen',s('edit_fetchtime'),array('click'=>'$("#bid").val('.$betrieb['id'].')'));
		$zeit_cnt = '';
		if($betrieb['verantwortlich'])
		{
			$zeit_cnt = $zeiten_button;
			$zeit_cnt .= v_form_info(s('click_to_confirm'));
			
		}
		
		
		if($verantwortlicher = u_getVerantwortlicher($betrieb))
		{
			$cnt = u_innerRow('name',$verantwortlicher);
			$cnt .= u_innerRow('telefon',$verantwortlicher);
			$cnt .= u_innerRow('handy',$verantwortlicher);
		}
		
		
		
		if(is_array($betrieb['abholer']))
		{
			foreach ($betrieb['abholer'] as $dow => $a)
			{
				$g_data['dow'.$dow] = $a;
			}
		}
		$zeiten = false;
		if($zeiten = $db->getAbholzeiten($betrieb['id']))
		{			
			$next_dates = u_getNextDates($zeiten);
			
			$abholer = $db->getAbholdates($betrieb['id'],$next_dates);
			
			$days = getDow();
			
			$scroller = '';
			
			foreach ($next_dates as $date => $time)
			{
				$scroller .= u_form_checkboxTagAlt(
					$date.' '.$time,
					array(
						'data'=>$betrieb['team'],
						'url'=>'jsonTeam&bid='.(int)$betrieb['id'],
						'label'=> $days[date('w',strtotime($date))].' '.format_db_date($date).', '.format_time($time),
						'betrieb_id' => $betrieb['id'],
						'verantwortlich' => $betrieb['verantwortlich']
					)
				);
				
			}
			
			$zeit_cnt .= v_scroller($scroller);
			
			/*
			foreach ($zeiten as $dow => $z)
			{
				$values = false;
					
				//array()
				if($betrieb['verantwortlich'])
				{
					$zeit_cnt .= v_form_tagselect('dow'.$dow,array('data'=>$betrieb['team'],'url'=>'jsonTeam&bid='.(int)$betrieb['id'],'label'=> $days[$dow].'s, '.format_time($z)));
				}
				else
				{
					addJs('$("#dow6-wrapper").next().hide();');
					$zeit_cnt .= v_form_checkboxTagAlt('dow'.$dow,array('data'=>$betrieb['team'],'url'=>'jsonTeam&bid='.(int)$betrieb['id'],'label'=> $days[$dow].'s, '.format_time($z)));
				}
			}*/
		
			//$zeit_cnt = v_form('zeiten',array($zeit_cnt),array('submit'=>s('save'),'buttons'=>array($zeiten_button)));
		}
		elseif($betrieb['verantwortlich'])
		{
		
			$zeit_cnt = v_info(sv('no_fetchtime',$betrieb['name']),s('attention').'!');
			$zeit_cnt .= '<div class="ui-padding-top">'.$zeiten_button.'</div>';
		}
		
		/*
		 * Abholzeiten ändern
		 */
		hiddenDialog('abholen', array(u_form_abhol_table($zeiten),v_form_hidden('bid', 0),'<input type="hidden" name="team" value="'.$betrieb['team_js'].'" />'),s('add_fetchtime'),array('reload' => true));
		//hiddenDialog('abholer', array(v_form_hidden('bbdow', 0),v_form_hidden('bbid', 0),v_form_desc('abholerdesc', ''),v_form_select_foodsaver(array('nolabel'=>true))),'Abholer auswählen',array('reload' => true));
		
		$right .= v_field(v_input_wrapper(s('verantwortlicher'),$cnt) , s('info'),array('class'=>'ui-padding'));
		
		if($betrieb['betrieb_status_id'] == 3 || $betrieb['betrieb_status_id'] == 5)
		{
			$right .= v_field($zeit_cnt, s('next_fetch_dates'),array('class'=>'ui-padding'));
		}
		else
		{
			$bt = '';
			if($betrieb['verantwortlich'])
			{
				addHidden('<div id="changeStatus-hidden">'.v_form('changeStatusForm', array(
					v_form_select('betrieb_status_id',array('value'=>$betrieb['betrieb_status_id']))
				)).'</div>');
				
				
				
				addJs('$("#changeStatus").button().click(function(){
					$("#changeStatus-hidden").dialog({
						title: "'.s('change_status').'",
						modal:true
					});
				});');
				$bt = '<p><span id="changeStatus">'.s('change_status').'</a></p>';
			}
			$right .= v_field('<p>'.s('not_ready').'</p>'.$bt,s('status'),array('class'=>'ui-padding'));
		}
		
		
		
	}
	else
	{
		$betrieb = $db->getBetrieb($_GET['id']);
		addBread($betrieb['name']);
		info(s('not_in_team'));
		addStyle('div.map {height: 400px;width: 818px;}');
		$content = v_clustermap('foodsaver',array('center'=>$betrieb));
	}
}
else
{
	addBread('Deine Betriebe');
	
	$right .= v_menu(array(
			array('href' => '/?page=betrieb&a=new','name' => s('add_new'))
	),'Aktionen');
	
	$bezirk = getBezirk();
	
	$betriebe = $db->getMyBetriebe();
	$content .= u_betriebList($betriebe['verantwortlich'],s('you_responsible'),true);
	$content .= u_betriebList($betriebe['team'],s('you_fetcher'),false);
	$content .= u_betriebList($betriebe['sonstige'],sv('more_stores',$bezirk['name']),false);
}

function u_getVerantwortlicher($betrieb)
{
	foreach ($betrieb['foodsaver'] as $fs)
	{
		if($fs['verantwortlich'] == 1)
		{
			return $fs;
		}
	}
	return false;
}

function handleRequests($betrieb)
{
	/*
	 * <table class="pintable">
					<tbody><tr class="even">
						<td class="img"><img src="images/mini_q_7aaad3eca0b5ed0484a509588878618d.jpg"></td>
						<td><span class="msg">dlkjfh djöosdj fs</span><span class="time">28.08.2013 12:29 Uhr</span></td>
					</tr>
					<tr class="odd">
						<td class="img"><img src="images/mini_q_3bb6c18170002870ae99f0ace537ce61.jpg"></td>
						<td><span class="msg">dffg</span><span class="time">28.08.2013 12:34 Uhr</span></td>
					</tr>
					<tr class="even">
						<td class="img"><img src="images/mini_q_7aaad3eca0b5ed0484a509588878618d.jpg"></td>
						<td><span class="msg">sdgfggs</span><span class="time">28.08.2013 14:35 Uhr</span></td>
					</tr></tbody></table>
	 */
	$out = '<table class="pintable">';
	$odd = 'odd';
	addJs('$("table.pintable tr td ul li").tooltip();');
	
	addJsFunc('
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
	
	foreach ($betrieb['requests'] as $r)
	{
		if($odd == 'even')
		{
			$odd = 'odd';
		}
		else
		{
			$odd = 'even';
		}
		$out .= '
		<tr class="'.$odd.' request-'.$r['id'].'">
			<td class="img" width="35px"><a href="#" onclick="profile('.(int)$r['id'].');return false;"><img src="'.img($r['photo']).'" /></a></td>
			<td style="padding-top:17px;"><span class="msg"><a href="#" onclick="profile('.(int)$r['id'].');return false;">'.$r['name'].'</a></span></td>
			<td style="width:66px;padding-top:17px;"><span class="msg"><ul class="toolbar"><li class="ui-state-default ui-corner-left" title="Ablehnen" onclick="denyRequest('.(int)$r['id'].','.(int)$betrieb['id'].');"><span class="ui-icon ui-icon-closethick"></span></li><li class="ui-state-default ui-corner-right" title="Akteptieren" onclick="acceptRequest('.(int)$r['id'].','.(int)$betrieb['id'].');"><span class="ui-icon ui-icon-heart"></span></li></ul></span></td>
		</tr>';
	}
	
	$out .= '</table>';
	
	hiddenDialog('requests', array($out));
	addJs('$("#dialog_requests").dialog("option","title","Anfragen für '.$betrieb['name'].'");');
	addJs('$("#dialog_requests").dialog("option","buttons",{});');
	addJs('$("#dialog_requests").dialog("open");');
}

function u_innerRow($id,$betrieb)
{
	$out = '';
	if($betrieb[$id] != '')
	{
		$out = '<div class="innerRow"><span class="label">'.s($id).'</span><span class="cnt">'.$betrieb[$id].'</span></div><div style="clear:both"></div>';
	}
	return $out;
}

function u_team($betrieb)
{
	global $db;
	$id = id('team');
	$out = '<ul id="'.$id.'" class="team">';
	$jssaver = array();
	foreach ($betrieb['foodsaver'] as $fs)
	{
		$jssaver[] = (int)$fs['id'];
		if($db->isActive($fs['id']))
		{
			$title = $fs['vorname'].' ist online';
			$ampel = 'ampel-gruen';
		}
		else
		{
			$title = $fs['vorname'].' ist offline';
			$ampel = 'ampel-grau';
		}
		
		
		
		$out .= '
				<li><a class="ui-corner-all" title="'.$title.'" href="#" onclick="profile('.(int)$fs['id'].');return false;"><img class="ui-corner-all" src="'.img($fs['photo'],'med').'" alt="'.$fs['vorname'].'" /></a><a href="#" onclick="chat('.(int)$fs['id'].');return false;" class="saver-ampel-'.$fs['id'].' ampel status '.$ampel.'"><span></span></a></li>';
	}
	$out .= '</ul><div style="clear:both"></div>';
	
	addJs('setInterval(function(){checkOnline("'.implode(',', $jssaver).'")},10000);');
	
	return $out;
}

function u_betriebList($betriebe,$title,$verantwortlich)
{
	if(empty($betriebe))
	{
		return '';
	}
	else
	{
		$bezirk = false;
		$betriebrows = array();
		foreach ($betriebe as $i => $b)
		{
			$status = v_getStatusAmpel($b['betrieb_status_id']);
		
			$betriebrows[$i] = array(
					array('cnt' => '<a href="/?page=fsbetrieb&id='.$b['id'].'">'.$b['name'].'</a>'),
					array('cnt' => $b['str'].' '.$b['hsnr']),
					array('cnt' => $b['plz']),
					array('cnt' => $status)
			);
			
			if(isset($b['bezirk_name']))
			{
				$betriebrows[$i][] = array('cnt'=>$b['bezirk_name']);
				$bezirk = true;
			}
			
			if($verantwortlich)
			{
				$betriebrows[$i][] = array('cnt' => v_toolbar(array('id'=>$b['id'],'types' => array('edit'),'confirmMsg'=>'Soll '.$b['name'].' wirklich unwideruflich gel&ouml;scht werden?')));
			}
		}
		
		$head = array(
				array('name' => 'Name','width'=>180),
				array('name' => 'Anschrift','width'=>150),
				array('name' => 'Postleitzahl','width'=>90),
				array('name' => 'Status'));
		if($bezirk)
		{
			$head[] = array('name'=>'Region');
		}
		if($verantwortlich)
		{
			$head[] = array('name' => 'Aktionen','sort' => false,'width' => 30);
		}
		
		$table = v_tablesorter($head,$betriebrows);
		
		return v_field($table,$title);
	}
}

function betrieb_form()
{
	global $db;
	
	$foodsaver_values = $db->getBasics_foodsaver();

	return v_quickform('betrieb',array(
	
			v_form_text('name'),
			v_form_text('plz'),
			v_form_text('str'),
			v_form_text('hsnr'),
			
			
			v_form_select('kette_id',array('add'=>true)),
			v_form_select('betrieb_kategorie_id',array('add'=>true)),
			
			v_form_select('betrieb_status_id'),
			
			v_form_text('ansprechpartner'),
			v_form_text('telefon'),
			v_form_text('fax'),
			v_form_text('email'),
			v_form_select('foodsaver',array('values' => $foodsaver_values))
	));
}

function u_getNextDates($fetch_dow)
{
	$out = array();
	
	
	
	$start_days = array();
	foreach ($fetch_dow as $dow => $fd)
	{

		if($dow == date('w'))
		{

			$start_days[] = array
			(
				'ts' => time(),
				'time' => $fd
			);
		}
		else
		{
			$start_days[] = array
			(
				'ts' => strtotime('next '.u_day($dow)),
				'time' => $fd
			);
		}
	}
	$month_change = 0;
	$y = 0;
	$cur_month = date('m',$start_days[0]['ts']);
	$i=0;
	while($i<=35)
	{
		foreach ($start_days as $sd)
		{
			$i++;
			$ts = $sd['ts']+($y*604800);			
			
			$out[date('Y-m-d',$ts)] = $sd['time'];
		}
		
		if(date('m',$ts) != $cur_month)
		{
			$month_change++;
		}
		
		$y++;
	}
	ksort($out);
	
	return $out;
}

function u_day($dow)
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

function handle_edit()
{
	global $db;
	global $g_data;
	if(submitted())
	{
		$g_data['foodsaver'] = array($g_data['foodsaver']);
		if($db->update_betrieb($_GET['id'],$g_data))
		{
			info(s('betrieb_edit_success'));
			goPage();
		}
		else
		{
			error(s('error'));
		}
	}
}
function handle_add()
{
	global $db;
	global $g_data;
	if(submitted())
	{
		$g_data['foodsaver'] = array($g_data['foodsaver']);
		if($db->add_betrieb($g_data))
		{
			info(s('betrieb_add_success'));
			goPage();
		}
		else
		{
			error(s('error'));
		}
	}
}

function u_form_checkboxTagAlt($date,$option=array())
{
	$id = 'fetch-'.id($date);
	$out = '<input type="hidden" id="'.$id.'-date" name="'.$id.'-date" value="'.$date.'" />';

	$bindabei = false;

	$out .= '
		<ul class="imglist" id="'.$id.'-imglist">';
	if($values = getValue($id))
	{
		foreach ($values as $fs)
		{
			if($fs['id'] == fsId())
			{
				$bindabei = true;
			}
			
			$class = $id.'-'.$fs['id'];
			$click = 'profile('.(int)$fs['id'].');return false;';
			if($fs['id'] == fsid())
			{
				$click = 'u_undate(\''.$date.'\',\''.format_db_date($date).'\');return false;';
			}
			
			
			if($option['verantwortlich'] && $fs['confirmed'] == 0)
			{
				$click = 'u_fetchconfirm('.(int)$fs['id'].',\''.$date.'\',this);return false;';
			}
			
			if($fs['confirmed'] == 0)
			{
				$class .= ' unconfirmed';
				$fs['name'] = sv('not_confirmed',$fs['name']);
			}
			$out .= '
			<li class="'.$class.'">
				<a href="#" onclick="'.$click.'" title="'.$fs['name'].'"><img src="'.img($fs['photo']).'" alt="'.$fs['name'].'" /><span>&nbsp;</span></a>
			</li>';
		}
		
	}
	$out .= '
		</ul><div style="clear:both;"></div>';

	if(!$bindabei)
	{
		$out .= '
			<span id="'.$id.'-button">'.s('add_me_here').'</span>';
	}
	else
	{
		$out .= '
			<!-- <span id="'.$id.'-buttonOut">Abmelden</span>-->';
	}

	

	addHidden('
	<div id="'.$id.'-timedialogOut">
		'.v_info('<strong>'.s('attention').'!</strong> Es darf '.$option['label'].' keine Lücke entstehen achte darauf das andere abholen.</span>').'
	</div>
	<div id="'.$id.'-timedialog">
		<span class="shure_date" id="'.$id.'shure_date">'.v_info(sv('shure_date',$option['label'])).'</span>
		<span class="shure_range_date" id="'.$id.'shure_range_date" style="display:none;">'.v_info(sv('shure_range_date',s('dow'.date('w',strtotime($date))))).'</span>
		<div class="rangeFetch" id="'.$id.'rangeFetch" style="display:none;">
				
				'.v_input_wrapper(s('zeitraum'), '<input type="text" value="" id="'.$id.'from" name="'.$id.'from" class="datefetch input text value"> bis <input type="text" value="" id="'.$id.'to" name="'.$id.'to" class="datefetch input text value">').'
				
		</div>
	</div>');

	$click = '';
	$click = 'profile('.(int)fsId().');return false;';
	$confclass = 'unconfirmed';
	$pulseconf = 'pulseInfo("'.jsSafe(s('wait_for_confirm')).'");';
	if($option['verantwortlich'])
	{
		//$click = 'u_fetchconfirm('.(int)fsId().',\\\''.$date.'\\\',this);return false;';
		$click = 'u_undate(\\\''.$date.'\\\',\\\''.format_db_date($date).'\\\');return false;';
		$confclass = 'confirmed';
		$pulseconf = '';
	}
	
	addJs('
		
			$("#'.$id.'-timedialog").dialog({
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
								date:"'.$date.'",
								bid:'.$option['betrieb_id'].',
								from: $("#'.$id.'from").val(),
								to: $("#'.$id.'to").val()
							},
							success : function(ret){
								u_clearDialogs();
								$("#'.$id.'-timedialog").dialog( "close" );
								if(ret == "2")
								{
									reload();		
								}
								else if(ret != 0)
								{
									
									$("#'.$id.'-button").last().remove();
									
									$("#'.$id.'-imglist").append(\'<li class="'.$confclass.'"><a onclick="'.$click.'" href="#"><img src="\'+ret+\'" title="Du" /><span>&nbsp;</span></a></li>\');
									'.$pulseconf.'
								}
								
							}
						});
											
						
					},
					"Regelmäßig Abholen": function() {
						$("#'.$id.'shure_date").hide();
						$("#'.$id.'rangeFetch").show();
						$("#'.$id.'shure_range_date").show();
						
						 $( "#'.$id.'from" ).datepicker({
							defaultDate: "+1w",
						 	minDate: "0",
							numberOfMonths: 1,
							onClose: function( selectedDate ) {
								$( "#'.$id.'to" ).datepicker( "option", "minDate", selectedDate );
								$( "#'.$id.'to" ).datepicker( "option", "maxDate", "+60" );
							}
						});
						$( "#'.$id.'to" ).datepicker({
							defaultDate: "+1w",
							minDate: "+2",
							maxDate: "+60",
							numberOfMonths: 1,
							onClose: function( selectedDate ) {
								$( "#'.$id.'from" ).datepicker( "option", "maxDate", selectedDate );
							}
						});
						$("#'.$id.'-timedialog").next().children().children(":nth-child(2)").hide();
					},
					"Abbrechen": function() {
						u_clearDialogs();
						$( this ).dialog( "close" );
					}
				}
			});
										

											
			
		
	$("#'.$id.'-button").button().click(function(){
		$("#'.$id.'-timedialog").dialog("open");
	});

	$("#'.$id.'-timedialogOut").dialog({
				title:"Denke an Nachfolger",
				resizable: false,
				modal: true,
				autoOpen:false,
				buttons: {
					"'.s('i_do').'": function() {
						$("#'.$id.'-buttonOut").last().remove();
						$("#'.$id.'-tagAltlist li.dasbistDu").remove();
						$( this ).dialog( "close" );
						$("#zeiten-form").submit();
					},
					"'.s('abort').'": function() {
						$( this ).dialog( "close" );
					}
				}
			});
		
	$("#'.$id.'-buttonOut").button().click(function(){
		$("#'.$id.'-timedialogOut").dialog("open");
	});

	');

	$part = explode(' ', $date);

	if($part[0] == date('Y-m-d'))
	{
		$option['class'] = 'today';
	}
	
	return v_input_wrapper(s($id),$out ,$id, $option);

}

function u_form_abhol_table($zeiten = false,$option = array())
{

	$dow = range(1,7);
	$days = getDow();



	addJs('
		$("table.timetable td label").click(function(){
		
			cb = $(this).children("input");
			if(cb[0].checked)
			{
				$("#timepick-"+cb.attr("value")).css("visibility","visible");
			}
			else
			{
				$("#timepick-"+cb.attr("value")).css("visibility","hidden");
			}
		})
	');

	$out = '
		<table class="timetable">';



	foreach ($days as $dow => $d)
	{
		$hidden = 'hidden';
		$chk = '';
		$value = false;
		if($zeiten !== false && isset($zeiten[$dow]))
		{
			$hidden = 'visible';
			$chk = ' checked="checked"';
			$value = $zeiten[$dow];
		}
		$out .= '
			<tr>
				<td><label><input type="checkbox" name="dow[]" value="'.$dow.'"'.$chk.' /> '.$d.'</label></td><td><div id="timepick-'.$dow.'" style="visibility:'.$hidden.';">'.v_form_time('day'.$dow,$value).'</div></td>
			</tr>';
	}

	$out .= '</table>';

	return $out;
}
				
?>