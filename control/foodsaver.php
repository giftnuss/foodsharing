<?php
loadApp('foodsaver');
/*
if(isset($_GET['deleteaccount']) && (isOrgaTeam()))
{
	global $db;
	$foodsaver = $db->getValues(array('email','name','nachname','bezirk_id'),'foodsaver',$_GET['id']);
	
	$db->del_foodsaver($_GET['id']);
	info('Foodsaver '.$foodsaver['name'].' '.$foodsaver['nachname'].' wurde gelöscht, für die Wiederherstellung wende Dich an it@lebensmittelretten.de');
	go('/?page=dashboard');
}

if(!isset($_GET['bid']))
{
	$bezirk_id = getBezirkId();
}
else
{
	$bezirk_id = (int)$_GET['bid'];
}

if(isBotFor($bezirk_id) || isOrgaTeam())
{
	$bezirk = array('name'=>'Komplette Datenbank');
	if($bezirk_id > 0)
	{
		$bezirk = $db->getBezirk($bezirk_id);
	}
	if(getAction('nneu') && isOrgaTeam())
	{
		addBread(s('bread_foodsaver'),'/?page=foodsaver');
		addBread('Vorhandene Foodsaver eintragen');
		
		addContent(u_invite($bezirk));
		
		addContent(v_field(v_menu(array(
		pageLink('foodsaver','back_to_overview')
		)),s('actions')),CNT_RIGHT);
	}
	elseif(getAction('neu') && isOrgaTeam())
	{
		handle_add();
	
		addBread(s('bread_foodsaver'),'/?page=foodsaver');
		addBread(s('bread_new_foodsaver'));
			
		addContent(foodsaver_form('Foodsaver eintragen'));
	
		addContent(v_field(v_menu(array(
				pageLink('foodsaver','back_to_overview')
		)),s('actions')),CNT_RIGHT);
	}
	elseif(isset($_GET['a']) && $_GET['a'] == 'delete' && isOrgaTeam())
	{		
		if($db->del_foodsaver($_GET['id']))
		{
			info(s('foodsaver_deleted'));
			goPage();
		}
	}
	elseif(($id = getActionId('edit')) && (isBotschafter() || isOrgaTeam()))
	{
		handle_edit();
		$data = $db->getOne_foodsaver($id);
		$bids = $db->getFsBezirkIds($id);
		
		addBread(s('bread_foodsaver'),'/?page=foodsaver');
		addBread(s('bread_edit_foodsaver'));
		
		if(isOrgaTeam() || isBotForA($bids))
		{
			setEditData($data);
				
			addContent(foodsaver_form($data['name'].' '.$data['nachname'].' bearbeiten'));
				
			addContent(picture_box(),CNT_RIGHT);
			
			addContent(v_field(v_menu(array(
			pageLink('foodsaver','back_to_overview')
			)),s('actions')),CNT_RIGHT);
			
			if(isOrgaTeam())
			{
				addContent(u_delete_account(),CNT_RIGHT);
			}
			
			
		}
		else
		{
			goPage('dashboard');
		}
	}
	else if(isset($_GET['id']))
	{
		$data = $db->getOne_foodsaver($_GET['id']);
		print_r($data);
	}
	else
	{
		addBread(s('foodsaver_bread'),'/?page=foodsaver');
	
		if($data = $db->listFoodsaverReq($bezirk_id))
		{
			$types = array('image','edit');
	
			if(isOrgateam())
			{
				$types[] = 'delete';
			}
	
			$rows = array();
			foreach ($data as $d)
			{
				$lastlogin = ''.date('Y-m-d',$d['last_login_ts']);
				if($d['last_login_ts'] == 0)
				{
					$lastlogin = '- noch nie -';
				}
					
				$rows[] = array(
						array('cnt' => '<span class="photo"><a href="#" onclick="profile('.(int)$d['id'].');return false;"><img id="miniq-'.$d['id'].'" src="'.img($d['photo']).'" /></a></span>'),
						array('cnt' => '<a class="linkrow ui-corner-all" href="#" onclick="profile('.(int)$d['id'].');return false;">'.$d['name'].'</a>'),
						array('cnt' => $d['anschrift']),
						array('cnt' => $d['bezirk_name']),
						array('cnt' => $lastlogin),
						array('cnt' => v_toolbar(array('id'=>$d['id'],'types' => $types,'confirmMsg'=>sv('delete_sure',$d['name'])))
						));
			}
	
			$table = v_tablesorter(array(
					array('name' => s('picture'),'sort'=> false, 'width' => 45),
					array('name' => s('name')),
					array('name' => s('anschrift')),
					array('name' => s('bezirk')),
					array('name' => s('last_login')),
					array('name' => s('actions'),'sort' => false,'width' => 75)
			),$rows,array('pager'=>true));
	
			addContent(v_field($table,'Alle Foodsaver aus '.$bezirk['name'].' '.count($data).' Insgesamt'));
		}
		else
		{
			info(s('foodsaver_empty'));
		}
	
		if(isOrgateam())
		{
			$opt = array();
			$opt[] = array('href' => '/?page=foodsaver&a=neu','name' => s('neu_foodsaver'));
			if(isOrgaTeam())
			{
				$opt[] = array('href' => '/?page=foodsaver&a=nneu','name' => 'Vorhandene Foodsaver eintragen');
			}
			addContent(v_field(v_menu($opt),'Aktionen'),CNT_RIGHT);
		}
	}
}
else
{
	goPage('dashboard');
}
				
function foodsaver_form($title = 'Foodsaver')
{
	global $db;
	
	/*
	$abholmoeglichkeit_values = $db->getBasics_abholmoeglichkeit();
	$beteiligung_values = $db->getBasics_beteiligung();
	$bezirk_values = $db->getBasics_bezirk();
	$ernaehrung_values = $db->getBasics_ernaehrung();
	$flatrate_values = $db->getBasics_flatrate();
	$heard_about_values = $db->getBasics_heard_about();
	$kontakte_werbung_values = $db->getBasics_kontakte_werbung();
	$lagerraum_values = $db->getBasics_lagerraum();
	$medienarbeit_values = $db->getBasics_medienarbeit();
	$sharing_netzwerk_values = $db->getBasics_sharing_netzwerk();
	$betrieb_values = $db->getBasics_betrieb();
	*/
/*	
	global $g_data;
	
	$orga = '';
	if(isOrgaTeam())
	{
		$options = array(
			'values' => array(
				array('id'=>1,'name'=>'ist im Bundesweiten Orgateam dabei')
			)
		);
		
		if($g_data['orgateam'] == 1)
		{
			$options['checkall'] = true;
		}
		
		$orga = v_form_checkbox('orgateam',$options);
		$orga .= v_form_select('rolle',array(
			'values' => array(
				array('id' => 0, 'name' => 'Foodsharer'),
				array('id' => 1, 'name' => 'Foodsaver'),
				array('id' => 2, 'name' => 'Botschafter'),
				array('id' => 3, 'name' => 'Orgamensch')		
			)		
		));
	}
	
	addJs('
		$("#plz, #stadt, #anschrift").bind("blur",function(){
	
	
			if($("#plz").val() != "" && $("#stadt").val() != "" && $("#anschrift").val() != "")
			{
				u_loadCoords({
					plz: $("#plz").val(),
					stadt: $("#stadt").val(),
					anschrift: $("#anschrift").val(),
					complete: function()
					{
						hideLoader();
					}
				},function(lat,lon){
					$("#lat").val(lat);
					$("#lon").val(lon);
				});
			}
		});
	
		$("#lat-wrapper").hide();
		$("#lon-wrapper").hide();
	');

	$bezirk = false;
	if((int)$g_data['bezirk_id'] > 0)
	{
		$bezirk = $db->getBezirk($g_data['bezirk_id']);
	}
	
	$bezirkchoose = v_bezirkChooser('bezirk_id',$bezirk);
	
	
	return v_quickform($title,array(
		$bezirkchoose,
		$orga,
		v_form_text('name',array('required' => true)),
		v_form_text('nachname',array('required' => true)),
		v_form_text('fs_id',array('required' => true)),
		v_form_text('stadt',array('required' => true)),
		v_form_text('plz',array('required' => true)),
		v_form_text('anschrift',array('required' => true)),
		v_form_text('lat'),
		v_form_text('lon'),
		v_form_text('email',array('required'=>true)),
		v_form_text('telefon'),
		v_form_text('handy'),
		v_form_select('geschlecht',array('values'=> array(
			array('name' => 'Frau','id' => 2),
			array('name' => 'Mann','id' => 1),
			array('name' => 'beides oder Sonstiges','id' => 3)
			
		),
		array('required' => true)
		)),
		
		v_form_date('geb_datum',array('required' => true)),
		v_form_select('autokennzeichen_id',array('required' => true))
		/*
		v_form_select('zuverlassig_id'),
		v_form_select('wohnung_id'),
		v_form_select('containert_id'),
		v_form_select('aktivbeifoodsharing_id'),
		v_form_select('promotionarbeit_id'),
		v_form_select('hotline_id'),
		v_form_select('zeitaufwand_id'),
		v_form_select('wohndauer_id'),
		v_form_select('pfand_id'),
		v_form_select('fleisch_abholen_id'),
		v_form_select('abholen_id'),
		v_form_select('foodsavertyp_id'),
		v_form_select('abholen_und_kuehlen_id'),
		v_form_select('autokennzeichen_id'),
		v_form_select('land_id'),
		v_form_select('bezirk_id'),
		v_form_select('plz_id'),
		v_form_text('email'),
		v_form_text('passwd'),
		v_form_text('name'),
		v_form_select('admin'),
		v_form_text('nachname'),
		v_form_text('anschrift'),
		v_form_text('telefon'),
		v_form_text('handy'),
		v_form_select('geschlecht'),
		v_form_text('geb_datum'),
		v_form_text('fs_id'),
		v_form_textarea('radius'),
		v_form_textarea('kontakte_betriebe'),
		v_form_textarea('raumlichkeit'),
		v_form_text('fs_international'),
		v_form_textarea('fs_orga'),
		v_form_textarea('talente'),
		v_form_text('anbau'),
		v_form_textarea('timetable'),
		v_form_textarea('legal_gerettet'),
		v_form_textarea('motivation'),
		v_form_textarea('about_me'),
		v_form_textarea('kommentar'),
		v_form_select('datenschutz'),
		v_form_select('haftungsausschluss'),
		v_form_text('anmeldedatum'),
		v_form_checkbox('abholmoeglichkeit',array('values' => $abholmoeglichkeit_values)),
		v_form_checkbox('beteiligung',array('values' => $beteiligung_values)),
		v_form_checkbox('bezirk',array('values' => $bezirk_values)),
		v_form_checkbox('ernaehrung',array('values' => $ernaehrung_values)),
		v_form_checkbox('flatrate',array('values' => $flatrate_values)),
		v_form_checkbox('heard_about',array('values' => $heard_about_values)),
		v_form_checkbox('kontakte_werbung',array('values' => $kontakte_werbung_values)),
		v_form_checkbox('lagerraum',array('values' => $lagerraum_values)),
		v_form_checkbox('medienarbeit',array('values' => $medienarbeit_values)),
		v_form_checkbox('sharing_netzwerk',array('values' => $sharing_netzwerk_values)),
		v_form_checkbox('betrieb',array('values' => $betrieb_values))	
		
		
	));
}

function handle_edit()
{
	global $db;
	global $g_data;
	if(submitted())
	{
		if(isset($g_data['orgateam']) && is_array($g_data['orgateam']) && $g_data['orgateam'][0] == 1)
		{
			$g_data['orgateam'] = 1;
			$db->addGlocke($_GET['id'], 'Du bist jetzt im Bundesweiten Orgateam','Willkommen','/?page=relogin');
		}
		else
		{
			$g_data['orgateam'] = 0;
		}
		
		if($db->update_foodsaver($_GET['id'],$g_data))
		{
			info(s('foodsaver_edit_success'));
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
		if($db->emailExists($g_data['email']))
		{
			error('Ein Foodsaver mit dieser E-Mail Adresse existiert schon.');
			return false;
		}
		else if($db->add_foodsaver($g_data))
		{
			info(s('foodsaver_add_success'));
			goPage();
		}
		else
		{
			error(s('error'));
		}
	}
}

function picture_box()
{
	global $g_data;
	global $db;

	$photo = $db->getPhoto($_GET['id']);

	if(!(file_exists('images/thumb_crop_'.$photo)))
	{
		$p_cnt = v_photo_edit('img/portrait.png',(int)$_GET['id']);
	}
	else
	{
		$p_cnt = v_photo_edit('images/thumb_crop_'.$photo,(int)$_GET['id']);
		//$p_cnt = v_photo_edit('img/portrait.png');
	}

	return v_field($p_cnt, 'Dein Foto');
}

function u_delete_account()
{
	addJs('
		$("#delete-account-confirm").dialog({
			autoOpen: false,
			modal: true,
			title: "'.s('delete_account_confirm_title').'",
			buttons: {
				"'.s('abort').'" : function(){
					$("#delete-account-confirm").dialog("close");
				},
				"'.s('delete_account_confirm_bt').'" : function(){
					goTo("/?page=foodsaver&a=edit&id='.(int)$_GET['id'].'&deleteaccount=1");
				}
			}
		});
		
		$("#delete-account").button().click(function(){
			$("#delete-account-confirm").dialog("open");
		});
	');
	$content = '
	<div style="text-align:center;margin-bottom:10px;">
		<span id="delete-account">'.s('delete_now').'</span>
	</div>
	'.v_info(s('posible_restore_account'),s('reference'));

	addHidden('
		<div id="delete-account-confirm">
			'.v_info(s('delete_account_confirm_msg')).'
		</div>
	');

	return v_field($content, s('delete_account'),array('class'=>'ui-padding'));
}

function u_invite($bezirk)
{
	if(isset($_POST['form_submit']) && $_POST['form_submit'] == 'inviteform')
	{
		handleTagselect('foodsaver');
		global $g_data;
		if(!empty($g_data['foodsaver']))
		{
			global $db;
			foreach ($g_data['foodsaver'] as $fsid)
			{
				$db->insert('
						REPLACE INTO `'.PREFIX.'foodsaver_has_bezirk`
						(
							`bezirk_id`,
							`foodsaver_id`,
							`active`,
							`added`
						)
						VALUES
						(
							'.(int)$bezirk['id'].',
							'.(int)$fsid.',
							1,
							NOW()
						)
				');
			}
			info('Foodsaver einsortiert!');
			go('/?page=foodsaver&bid='.(int)$bezirk['id'].'&a=nneu');
		}
	}
	
	$out = v_field(v_form('inviteform', array(
		v_form_tagselect('foodsaver')
	),array('submit'=>'Foodsaver dem Bezirk zuordnen')), 'Foodsaver in '.$bezirk['name'].' einsortieren',array('class'=>'ui-padding'));
	
	return $out;
}*/
