<?php
class FoodsaverControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new FoodsaverModel();
		$this->view = new FoodsaverView();
		
		parent::__construct();
		
	}
	
	/*
	 * Default Method for ?page=foodsaver
	 */
	public function index()
	{
		
		
		
		// check bezirk_id and permissions
		if(isset($_GET['bid']) && ($bezirk = $this->model->getBezirk($_GET['bid'])) && (S::may('orga') || isBotFor($_GET['bid'])))
		{
			// permission granted so we can load the foodsavers
			if($foodsaver = $this->model->listFoodsaver($_GET['bid']))
			{
				// add breadcrumps
				addBread('Foodsaver','?page=foodsaver&bid='.$bezirk['id']);
				addBread($bezirk['name'],'?page=foodsaver&bid='.$bezirk['id']);
				
				// list fooodsaver
				addContent(
					$this->view->foodsaverList($foodsaver,$bezirk),
					CNT_LEFT
				);
				
				addContent($this->view->foodsaverForm());
				
				addContent(
					$this->view->addFoodsaver($bezirk),
					CNT_RIGHT
				);
			}
		}
		else if(($id = getActionId('edit')) && (isBotschafter() || isOrgaTeam()))
		{
			handle_edit();
			$data = $this->model->getOne_foodsaver($id);
			$bids = $this->model->getFsBezirkIds($id);
			
			addBread(s('bread_foodsaver'),'?page=foodsaver');
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
			
		}
		else
		{
			addContent(v_info('Du hast leider keine Berechtigung für diesen Bezirk'));
		}
	}
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
			$db->addGlocke($_GET['id'], 'Du bist jetzt im Bundesweiten Orgateam','Willkommen','?page=relogin');
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
					array('id' => 0, 'name' => 'Foodsharer/in'),
					array('id' => 1, 'name' => 'Foodsaver/in (FS)'),
					array('id' => 2, 'name' => 'Betriebsverantwortliche/r (BIEB)'),
					array('id' => 3, 'name' => 'Botschafter/in (BOT)'),
					array('id' => 4, 'name' => 'Orgamensch/in (ORG)')
			)
	));
	}

	addJs('
			$("#rolle").change(function(){
				if(this.value == 4)
				{
					$("#orgateam-wrapper input")[0].checked = true;
				}
				else
				{
					$("#orgateam-wrapper input")[0].checked = false;
				}
			});
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

			v_form_date('geb_datum',array('required' => true))
		
			/*
			 * v_form_select('autokennzeichen_id',array('required' => true)
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
			*/

		));
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
					goTo("?page=foodsaver&a=edit&id='.(int)$_GET['id'].'&deleteaccount=1");
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