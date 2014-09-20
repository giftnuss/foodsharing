<?php
if(!S::may('orga'))
{
	go('/');
}
if(isOrgaTeam() && isset($_GET['delete']) && (int)$_GET['delete'] > 0)
{
	$db->deleteBezirk($_GET['delete']);
	goPage('region');
}

if(getAction('neu'))
{
	handle_add();
	
	addBread(s('bread_bezirk'),'?page=region');
	addBread(s('bread_new_bezirk'));
			
	addContent(bezirk_form());

	addContent(v_field(v_menu(array(
		pageLink('region','back_to_overview')
	)),s('actions')),CNT_RIGHT);
}
elseif($id = getActionId('delete'))
{
	if($db->del_bezirk($id))
	{
		info(s('bezirk_deleted'));
		goPage();
	}
}
elseif($id = getActionId('edit'))
{
	handle_edit();
	
	addBread(s('bread_bezirk'),'?page=bezirk');
	addBread(s('bread_edit_bezirk'));
	
	$data = $db->getOne_bezirk($id);
	
	setEditData($data);
			
	addContent(bezirk_form());
			
	addContent(v_field(v_menu(array(
		pageLink('region','back_to_overview')
	)),s('actions')));
}
else if(isset($_GET['id']))
{
	$data = $db->getOne_region($_GET['id']);	
	print_r($data);	
	
}
else if(false)
{
	addBread(s('region_bread'),'?page=region');
	
	if($data = $db->getBasics_region())
	{
		foreach ($data as $region)
		{
			
			
			$table = 'Noch keine Bezirke angelegt';
			
			if(/*$bezirk = $db->getBezirkByRegionId($region['id'])*/$bezirk = $db->getAllBezirke($region['id']))
			{
				
				$rows = array();
				foreach ($bezirk as $b)
				{
					
					$rows[] = array(
							array('cnt' => '<a href="?page=region&id='.$b['id'].'">'.$b['name'].'</a>'),
						array('cnt' => $b['anz_betriebe']),
						array('cnt' => $b['anz_fs']),
						array('cnt' => v_toolbar(array('id'=>$b['id'],'types' => array('comment','edit','delete'),'confirmMsg'=>'Soll '.$b['name'].' wirklich unwideruflich gel&ouml;scht werden?'))
					));
				}
				
				$table = v_tablesorter(array(
						array('name' => 'Name'),
						array('name' => 'Betriebe','width' => 75),
						array('name' => 'Aktive Foodsaver','width' => 120),
						array('name' => 'Aktionen','sort' => false,'width' => 75)
				),$rows);
					
				addContent(v_field($table,$region['name']));
			}
			else 
			{
				addContent(v_field($region['name'].' hat noch keine Bezirke',$region['name']));
			}
			
		}
	}
	else
	{
		info(s('region_empty'));		
	}
			
	addContent(v_field(v_menu(array(
		array('href' => '?page=region&a=neu','name' => s('neu_region'))
	)),'Aktionen'));
}		
else
{
	$id = id('tree');
	addBread(s('bezirk_bread'),'?page=region');
	
	$cnt = '
	<div>
		<div style="float:left;width:150px;" id="'.'..'.'"></div>
		<div style="float:right;width:250px;"></div>
		<div style="clear:both;"></div>		
	</div>';
	
	addStyle('#bezirk-buttons {left: 50%; margin-left: 5px;position: absolute;top: 77px;}');
	
	addJs('
	$("#deletebezirk").button().click(function(){
		if(confirm($("#tree-hidden-name").val()+\' wirklich löschen?\'))
		{
			goTo(\'?page=region&delete=\'+$("#tree-hidden").val());
		}
	});');
	
	/*
	 * ifconfirm("?page=region&delete="+$("#tree-hidden").val(),"Soll "+$("#tree-hidden-name").val()+" wirklich gelöscht werden?","Bezirk löschen");
	 * 
	 */
	$bezirke = $db->getBasics_bezirk();
	
	array_unshift($bezirke,array('id'=>'0','name'=>'Ohne `Eltern` Bezirk'));
	
	hiddenDialog('newbezirk', array(
		v_form_text('Name'),
		v_form_text('email'),
		v_form_select('parent_id',array('values'=>$bezirke))
	),'Neuer Bezirk');
	
	addContent(v_field('<div><div id="'.id('bezirk_form').'"></div></div>','Bezirk bearbeiten',array('class' => 'ui-padding')),CNT_LEFT);
	addContent(v_field(v_bezirk_tree($id).'
			<div id="bezirk-buttons">
				<span id="deletebezirk" style="visibility:hidden;">Bezirk Löschen</span>	
				'.v_dialog_button('newbezirk', 'Neuer Bezirk').'	
			</div>', 'Bezirke'),CNT_RIGHT);

	//$content = v_field($cnt,'Bezirke verwalten',array('class'=> 'ui-padding'));
	
	i_map($id);
	
}		

function v_bezirk_tree($id)
{
	
	addScript('/js/dynatree/jquery.dynatree.js');
	addScript('/js/jquery.cookie.js');
	addCss('/js/dynatree/skin/ui.dynatree.css');

	addJs('
	$("#'.$id.'").dynatree({
		onDblClick: function(node, event) {
			alert(node.data.ident);
		},
	    initAjax: {
			url: "xhr.php?f=bezirkTree",
			data: {p: "0" }
		},
		onActivate: function(node){
			$("#deletebezirk").css("visibility","visible");
			showLoader();
			$("#'.$id.'-hidden").val(node.data.ident);
			$("#'.$id.'-hidden-name").val(node.data.title);
			$.ajax({
				url: "xhr.php?f=getBezirk",
				data: { "id": node.data.ident },
				dataType: "json",
				success: function(data) {
					$("#bezirk_form").html(data.html);
					if(data.script != undefined)
					{
						$.globalEval(data.script);
					}
					'.$id.'_clearMarkers();
					image = new google.maps.MarkerImage("img/foodsaver.png",
						        new google.maps.Size(32.0, 37.0),
						        new google.maps.Point(0, 0),
						        new google.maps.Point(16.0, 18.0)
					);
					shadow = new google.maps.MarkerImage("img/shadow-foodsaver.png",
						         new google.maps.Size(51.0, 37.0),
						         new google.maps.Point(0, 0),
						         new google.maps.Point(16.0, 18.0)
					);
						
					if(data.foodsaver != undefined && data.foodsaver.length > 0)
					{
						
						
						for(i=0;i<data.foodsaver.length;i++)
						{
							loc = new google.maps.LatLng(data.foodsaver[i].lat,data.foodsaver[i].lon);
    						'.$id.'_bounds.extend(loc);
							
							'.$id.'_markers[i] = new google.maps.Marker({
						      position: loc,
						      map: '.$id.'_map,
						      title:data.foodsaver[i].name,
						      icon: image,
							  shadow:shadow,
						      content: \'<div style="height:80px;overflow:hidden;"><div style="margin-right:10px;float:left;"><a onclick="profile(\'+ data.foodsaver[i].id +\');return false;" href="#"><img src="\'+img(data.foodsaver[i].photo)+\'" /></a></div><h1 style="font-size:13px;font-weight:bold;margin-bottom:8px;"><a onclick="profile(\'+ data.foodsaver[i].id +\');return false;" href="#">\' + data.foodsaver[i].name + "</a></h1><p>" + data.foodsaver[i].anschrift + "</p><p>" + data.foodsaver[i].plz + " " + data.foodsaver[i].stadt + \'</p><div style="clear:both;"></div></div>\'
						  });
						      		
						  google.maps.event.addListener('.$id.'_markers[i], \'click\', function(e,ii) {
						    '.$id.'_infowindow.setContent(""+this.content);
						    '.$id.'_infowindow.open('.$id.'_map, this);
						  });
						  '.$id.'_map.fitBounds('.$id.'_bounds);
    					  '.$id.'_map.panToBounds('.$id.'_bounds); 
						}
    				}
    				if(data.betriebe != undefined && data.betriebe.length > 0)
    				{		
    					for(i=0;i<data.betriebe.length;i++)
						{
    					  	if(data.foodsaver != undefined)
    					  	{
    					  		y = (i+data.foodsaver.length);
    					  	}
    					  	else
    					  	{
    					  		y = i;	
    					  	}
							loc = new google.maps.LatLng(data.betriebe[i].lat,data.betriebe[i].lon);
    						'.$id.'_bounds.extend(loc);
							
							'.$id.'_markers[y] = new google.maps.Marker({
						      position: loc,
						      map: '.$id.'_map,
						      title:data.betriebe[i].name,
						      icon:   new google.maps.MarkerImage(
							  "img/supermarkt.png",
						        	new google.maps.Size(32.0, 37.0),
						        	new google.maps.Point(0, 0),
						        	new google.maps.Point(16.0, 18.0)
							  ),
							  shadow:shadow,
						      content: data.betriebe[i].bubble
						  });
						      		
						  google.maps.event.addListener('.$id.'_markers[y], \'click\', function(e,ii) {
						    '.$id.'_infowindow.setContent(""+this.content);
						    '.$id.'_infowindow.open('.$id.'_map, this);
						  });
						  '.$id.'_map.fitBounds('.$id.'_bounds);
    					  '.$id.'_map.panToBounds('.$id.'_bounds); 
						}
					}
			
				},
				complete: function(){
					hideLoader();
				}
			});
		},
		onLazyRead: function(node){
			 node.appendAjax({url: "xhr.php?f=bezirkTree",
				data: { "p": node.data.ident },
				dataType: "json",
				success: function(node) {
			
				},
				error: function(node, XMLHttpRequest, textStatus, errorThrown) {
			
				},
				cache: false
			});
		
		}
	});
	');

	return '<div><div id="'.$id.'"></div><input type="hidden" name="'.$id.'-hidden" id="'.$id.'-hidden" value="0" /><input type="hidden" name="'.$id.'-hidden-name" id="'.$id.'-hidden-name" value="0" /></div>';
}

function i_map($id)
{
	addHead('
		<script src="http://www.google.com/jsapi"></script>
		<script type="text/javascript" src="http://google-maps-utility-library-v3.googlecode.com/svn/tags/markerclusterer/1.0/src/markerclusterer.js"></script>
		<script type="text/javascript">
			
			google.load(\'maps\', \'3\', {
				other_params: \'sensor=false\'
			});
		</script>
	');
	
	addJsFunc('
	var '.$id.'_markers = [];
	var '.$id.'_bounds = new google.maps.LatLngBounds();
	var '.$id.'_infowindow = new google.maps.InfoWindow({content: \'Information!\'});
	function '.$id.'_clearMarkers()
	{
		for(i=0; i < '.$id.'_markers.length; i++)
		{
			'.$id.'_markers[i].setMap(null);
		}
		'.$id.'_bounds = new google.maps.LatLngBounds();
		'.$id.'_markers = [];
	}');
	
	addStyle('div.map{width:512px;}');
	addContent(v_field('<div class="map" id="'.$id.'_map"></div>','Karte'));
	
	$zoom = 6;
	$lat = '51.303145';
	$lon = '10.235595';
	
	addJs('
	 	var '.$id.'_center = new google.maps.LatLng('.$lat.','.$lon.');
		var '.$id.'_options = {
		  \'zoom\': '.$zoom.',
		  \'center\': '.$id.'_center,
		  \'mapTypeId\': google.maps.MapTypeId.ROADMAP
		};
		
		var '.$id.'_map = new google.maps.Map(document.getElementById("'.$id.'_map"), '.$id.'_options);
	');
	
	
}

function bezirk_form()
{
	global $db;
	
	
	
	$elements = array(
		
		v_form_select('region_id',array('required'=>true)),
		v_form_text('name',array('required'=>true)),
		v_form_list('plz')
	);
	
	global $g_data;
	
	if(empty($g_data['plz']))
	{
		$elements[] = v_form_info('Du musst diesem Bezirk erst Postleitzahlen zuordnen damit Du einen Botschafter ausw&auml;hlen kannst.');
	}
	else
	{
		$foodsaver_values = $db->getBasics_foodsaver($_GET['id']);
		$elements[] = v_form_checkbox('foodsaver',array('values' => $foodsaver_values));
	}
	
	
	return v_quickform('bezirk',$elements);
}

function handle_edit()
{
	global $db;
	global $g_data;
	
	
	if(submitted())
	{
		
		$plz = str_replace("\r", '', $g_data['plz']);
		$plz = explode("\n", $g_data['plz']);
		
		$g_data['plz'] = array();
		
		foreach ($plz as $p)
		{
			$p = trim($p);
			if(!empty($p))
			{
				$g_data['plz'][] = $db->getPlzId($p);
			}
		}
		
		if($db->update_bezirk($_GET['id'],$g_data))
		{
			$db->resortFoodsaver();
			info(s('bezirk_edit_success'));
			//goPage();
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
		if($db->add_bezirk($g_data))
		{
			$db->store->flush();
			info(s('bezirk_add_success'));
			goPage();
		}
		else
		{
			error(s('error'));
		}
	}
}
				
?>