<?php


if(isset($_GET['deleteaccount']))
{
	global $db;	
	$foodsaver = $db->getValues(array('email','name','nachname'),'foodsaver',fsId());
	
	libmail(array(
		'email' => $foodsaver['email'],
		'email_name' => $foodsaver['name'].' '.$foodsaver['nachname']
	), 'loeschen@lebensmittelretten.de', $foodsaver['name'].' hat Account gelöscht',$foodsaver['name'].' '.$foodsaver['nachname'].' hat Account gelöscht'."\n\nGrund für das Löschen:\n".strip_tags($_GET['reason']));
	$db->del_foodsaver(fsId());
	go('?page=logout');
}
else
{
	handle_edit();
	
	addBread('Profil & Foto','?page=settings');
	
	$data = $db->getOne_foodsaver(fsId());
	
	setEditData($data);
			
	addContent(foodsaver_form());
	
	addContent(picture_box(),CNT_RIGHT);
	
	addContent(u_delete_account(),CNT_RIGHT);
	
	if(!isBotschafter())
	{
		$upgradeMenu = array();
		/*
		if(isset($_SESSION['client']['rolle']) && $_SESSION['client']['rolle'] == 1)
		{
			$upgradeMenu[] = array('name'=>'werde Foodsaver','href'=>'?page=upgrade&form=foodsaver');
		}
		elseif(!isBotschafter())
		{
			$upgradeMenu[] = array('name'=>'Werde Botschafter','href'=> '?page=upgrade&form=botschafter');
		}
		*/
		$upgradeMenu[] = array('name'=>'Werde Botschafter','href'=> '?page=upgrade&form=botschafter');
		
		if(!empty($upgradeMenu))
		{
			addContent(v_menu($upgradeMenu,'Du möchtest mehr machen?'),CNT_RIGHT);
		}
	}	
}
function handle_edit()
{
	global $db;
	
	if(submitted())
	{
		$data = getPostData();
		$data['stadt'] = $data['ort']; 
		if($db->updateProfile(fsId(),$data))
		{
			info(s('foodsaver_edit_success'));
			//goPage();
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
	
	$photo = $db->getPhoto(fsId());
	
	if(!(file_exists('images/thumb_crop_'.$photo)))
	{
		$p_cnt = v_photo_edit('img/portrait.png');
	}
	else
	{
		$p_cnt = v_photo_edit('images/thumb_crop_'.$photo);
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
					goTo("?page=settings&deleteaccount=1&reason=" + encodeURIComponent($("#reason_to_delete").val()));
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
			'.v_form_textarea('reason_to_delete').'
		</div>
	');
	
	return v_field($content, s('delete_account'),array('class'=>'ui-padding'));
}

function foodsaver_form()
{
	global $db;
	global $g_data;
	
	addJs('$("#foodsaver-form").submit(function(e){
		if($("#photo_public").length > 0)
		{
			$e = e;
			if($("#photo_public").val()==4 && confirm("Achtung niemand kann Dich mit Deinen Einstellungen kontaktieren. Bist Du sicher?"))
			{
				
			}
			else
			{
				$e.preventDefault();
			}
		}
		
	});');
		
	$oeff = v_form_radio('photo_public',array('desc'=>'Du solltest zumindest intern den Menschen in Deiner Umgebung ermöglichen Dich zu kontaktieren. So kannst Du von anderen Foodsavern eingeladen werden, Lebensmittel zu retten und ihr Euch einander kennen lernen.','values' => array(
			array('name' => 'Ja ich bin einverstanden, dass mein Name und mein Foto veröffentlicht werden','id' => 1),
			array('name' => 'Bitte nur meinen Namen veröffentlichen','id' => 2),
			array('name' => 'Meinen Daten nur intern anzeigen','id' => 3),
			array('name' => 'Meine Daten niemandem zeigen','id' => 4)
	)));
	
	if(isBotschafter())
	{
		$oeff = '<input type="hidden" name="photo_public" value="1" />';
	}
	$bezirkchoose = '';
	if(isOrgaTeam())
	{
		$bezirk = array('id'=>0,'name'=>false);
		if($b = getBezirk($g_data['bezirk_id']))
		{		
			$bezirk['id'] = $b['id'];
			$bezirk['name'] = $b['name'];
		}
		
		$bezirkchoose = v_bezirkChooser('bezirk_id',$bezirk);
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
	
	$view = loadView();
	
	$g_data['ort'] = $g_data['stadt'];
	
	return v_quickform(s('settings'),array(
			$bezirkchoose,
			//v_form_text('name'),
			//v_form_text('nachname'),
			/*
			v_form_select('geschlecht',array('values' => array(
					array(
						'name' => 'Frau',
						'id' => 2
					),
					array(
						'name' => 'Mann',
						'id' => 1
					),
					array(
						'name' => 'Sonstiges oder Beides',
						'id' => 3		
					)
			))),
			*/
			$view->latLonPicker('LatLng'),
			v_form_text('telefon'),
			v_form_text('handy'),
			v_form_select('autokennzeichen_id'),
			v_form_text('fs_id',array('required'=>true,'desc'=>'Du findest Deine ID wenn Du Dich bei <a target="_blank" href="http://www.foodsharing.de">foodsharing.de</a> eingelogt hast und dann oben rechts neben "Logout" auf Deinen
									Namen klickst. Du gelangst dann auf Deine Profilseite wo es
									eine Auflistung Deiner persönlichen Daten wie Name, Adresse
									etc. gibt. Als letzter Punkt steht da: "Meine Benutzer ID". Die
									ID besteht aus 1-6 Ziffern. Wenn Du Dich als Organisation bei <a href="http://www.foodsharing.de" target="_blank">foodsharing.de</a>
									angemeldet hast, musst Du Dich für das Freiwilligenprogramm
									auch noch als Privatperson bei <a href="http://www.foodsharing.de" target="_blank">foodsharing.de</a>
									anmelden.')),
			v_form_textarea('about_me_public',array('desc'=>'Um möglichst transparent, aber auch offen, freundlich, seriös und einladend gegenüber den Lebensmittelbetrieben, den Foodsavern sowie allen, die bei foodsharing mitmachen wollen, aufzutreten, wollen wir neben Deinem Foto, Namen und Telefonnummer auch eine Beschreibung Deiner Person als Teil von foodsharing mit aufnehmen. Bitte fass Dich also relativ kurz, hier unsere Vorlage: http://foodsharing.de/ueber-uns Gerne kannst du auch Deine Website, Projekt oder sonstiges erwähnen, was Du öffentlich an Informationen teilen möchtest, die vorteilhaft sind.')),
			$oeff
	),array('submit'=>s('save')));
}