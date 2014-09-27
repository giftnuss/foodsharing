<?php
class View
{
	private $sub;
	
	public function setSub($sub)
	{
		$this->sub = $sub;
	}
	
	
	public function login()
	{
		$action = '/?page=login';
		if(!isset($_GET['ref']))
		{
			$action = '/?page=login&ref=' . urlencode($_SERVER['REQUEST_URI']);
		}
		
		addJs('
				if(isMob())
				{
					$("#ismob").val("1");
				}
				$(window).resize(function(){
					if(isMob())
					{
						$("#ismob").val("1");
					}
					else
					{
						$("#ismob").val("0");
					}
				});
			');
		
		return '
			<div id="g_login">'.v_field(
						v_form('Login',array(
								v_form_text('email_adress'),
								v_form_passwd('password'),
								v_form_hidden('ismob', '0').
								'<p>
						<a href="/?page=login&sub=passwordReset">Passwort vergessen?</a>
					</p>'
						),array('action' => $action)),'Login',array('class' => 'ui-padding')).'
			</div>';
	}
	
	public function topbar($title,$subtitle,$image = false)
	{
		$img = '';
		if($image !== false)
		{
			$img = '
			<div class="welcome_profile_image">
				<img width="50" height="50" src="'.$image.'" class="image_online">
			</div>';
		}
		return '
	<div class="welcome ui-padding margin-bottom ui-corner-all">
		'.$img.'
		<div class="welcome_profile_name">
			<div class="user_display_name">
				'.$title.'
			</div>
			<div class="welcome_quick_link">
				<ul>
					<li>'.$subtitle.'</li>
				</ul>
				<div class="clear"></div>
			</div>
		</div>
		<div class="clear"></div>
	</div>';
	}
	
	public function locationMumble()
	{
		$out = v_field('
		<p>Online-Termin</p>
		<p style="text-align:center;">
			<a target="_blank" href="http://wiki.lebensmittelretten.de/Mumble"><img src="img/mlogo.png" alt="Mumble" /></a>
		</p>
		<p>
			Online-Sprachkonferenzen machen wir mit Mumble	
		</p>
		<p>Unser Mumble Server:<br />mumble.lebensmittelretten.de</p>
		<p>Anleitung unter: <a target="_blank" href="http://wiki.lebensmittelretten.de/Mumble">wiki.lebensmittelretten.de/Mumble</a></p>
		', 'Ort',array('class' => 'ui-padding'));
		
		return $out;
	}
	
	public function location($location)
	{
		//$map = new vMap();
		//$map->setLocation($location['lat'], $location['lon']);
		
		$out = v_field('
		<p>'.$location['name'].'</p>
		<p>
			'.$location['street'].'<br />
			'.$location['zip'].' '.$location['city'].'
		</p>
				
		', 'Ort',array('class' => 'ui-padding'));
		
		return $out;
	}
	
	public function headline($title,$img = '',$click = false)
	{
		if($click !== false)
		{
			$click = ' onclick="'.$click.'return false;"';
		}
		if(!empty($img))
		{
			$img = '
			<div class="welcome_profile_image">
				<a href="#"'.$click.'>
					<img width="50" height="50" src="'.$img.'" alt="Raphael" class="image_online">
				</a>
			</div>';
		}
		return '
					
		<div class="welcome ui-padding margin-bottom ui-corner-all">
			'.$img.'
			<div class="welcome_profile_name">
				<div class="user_display_name">
					'.$title.'
				</div>
			</div>
		</div>';
	}
	
	public function fsIcons($foodsaver)
	{
		if(!empty($foodsaver))
		{
			$out = '<ul class="fsicons">';
			
			if(count($foodsaver) > 100)
			{
				shuffle($foodsaver);
			}
			$i = 52;
			foreach ($foodsaver as $fs)
			{
				$i--;
				$out .= '
				<li>
					<a title="'.$fs['name'].'" style="background-image:url('.img($fs['photo']).');" href="#" onclick="profile('.(int)$fs['id'].');return false;"><span></span></a>	
				</li>';
				if($i <= 0)
				{
					$out .= '<li class="row">...und '.(count($foodsaver)-52).' weitere</li>';
					break;
				}
			}
			$out .= '</ul>';
			
			return $out;
		}
		return '';
	}
	
	public function fsAvatarList($foodsaver,$option = array())
	{
		if(!is_array($foodsaver))
		{
			return '';
		}
		
		if(!isset($option['scroller']))
		{
			$option['scroller'] = true;
		}
		
		$id = id('team');
		if(isset($option['id']))
		{
			$id = $option['id'];
		}
		
		
		$out = '
		<div>
			<ul id="'.$id.'" class="linklist">';
		if(!isset($option['shuffle']) || (isset($option['shuffle']) && $option['shuffle'] === true))
		{
			shuffle($foodsaver);
		}
		foreach ($foodsaver as $fs)
		{
			$jssaver[] = (int)$fs['id'];
			
			$title = $fs['name'].' ist offline';
			$ampel = 'ampel-grau';

			if(!empty($fs['photo']))
			{
				$photo = '<img class="ui-corner-all" src="'.img($fs['photo']).'" alt="'.$fs['name'].'" />';
			}
			else
			{
				$photo = '<img class="ui-corner-all" src="img/avatar-mini.png" alt="'.$fs['name'].'" />';
			}
			
			$click = ' onclick="profile('.(int)$fs['id'].');return false;"';
			
			$href = '#';
			if(isset($fs['href']))
			{
				$click = '';
				$href = $fs['href'];
			}
			
			$out .= '
				<li>
					<a href="'.$href.'"'.$click.' class="ui-corner-all">
						<span style="float:left;margin-right:7px;">'.$photo.'</span>
						<span class="title">'.$fs['name'].' '.$fs['nachname'].'</span>
						<span style="clear:both;"></span>
					</a>
				</li>';
		}
		$out .= '
			</ul>
			<div style="clear:both"></div>
		</div>';
		
		//addJs('setInterval(function(){checkOnline("'.implode(',', $jssaver).'")},10000);');
		
		if($option['scroller'])
		{
			$out = v_scroller($out,185);
			addStyle('.scroller .overview{left:0;}.scroller{margin:0}');
		}
		return $out;
	}
	
	public function menu($items, $option = array())
	{
		$title = false;
		if(isset($option['title']))
		{
			$title = $option['title'];
		}
		
		$active = false;
		if(isset($option['active']))
		{
			$active = $option['active'];
		}
		
		$id = id('vmenu');
	
	//addJs('$("#'.$id.'").menu();');
	
	$out = '';
	
	foreach ($items as $item)
	{
		if(!isset($item['href']))
		{
			$item['href'] = '#';
		}
		
		$click = '';
		if(isset($item['click']))
		{
			$click = ' onclick="'.$item['click'].'"';
		}
		$class = '';
		if($active !== false && (strpos($item['href'], '='.$active) !== false))
		{
			$class = 'active ';
		}
		
		$out .= '<li>
					<a class="'.$class.'ui-corner-all" href="'.$item['href'].'"'.$click.'>
						<span>'.$item['name'].'</span>
					</a>
				</li>';
	}

	
	if(!$title)
	{
		return '
		<div class="ui-widget ui-widget-content ui-corner-all ui-padding margin-bottom">
			<ul class="linklist">
				'.$out.'
			</ul>
		</div>';
	}
	else
	{
		return '
			<h3 class="head ui-widget-header ui-corner-top">'.$title.'</h3>
			<div class="ui-widget ui-widget-content ui-corner-bottom ui-padding margin-bottom">
				<ul class="linklist">
					'.$out.'
				</ul>
			</div>';
	}
	
	return $out;
	}
	
	public function latLonPicker($id,$options = array())
	{
		addHead('<script src="http://maps.google.com/maps/api/js?sensor=false"></script>');
		addScript('/js/jquery.ui.addresspicker.js');
		
		$data = array();
		global $g_data;
		if(isset($g_data['lat']) && isset($g_data['lon']) && !empty($g_data['lat']) && !empty($g_data['lon']))
		{
			$data = array(
				'lat' => $g_data['lat'],
				'lon' => $g_data['lon']
			);
		}
		else
		{
			global $db;
			$data = $db->getValues(array('lat','lon'), 'foodsaver', fsId());	
		}
		
		
		addJs('
			$("#lat-wrapper,#lon-wrapper").hide();
		    var addresspickerMap = $( "#addresspicker_map" ).addresspicker({
		      regionBias: "de",
		      updateCallback: showCallback,
			  reverseGeocode: true,
		      mapOptions: {
		        zoom: 14,
		        center: new google.maps.LatLng('.floatval($data['lat']).', '.floatval($data['lon']).'),
		        scrollwheel: false,
		        mapTypeId: google.maps.MapTypeId.ROADMAP
		      },
		      elements: {
		        map:      "#map",
		        lat:      "#lat",
		        lng:      "#lon",
		        locality: "#ort",
		        postal_code: "#plz"
		      }
		    });
		
		    var gmarker = addresspickerMap.addresspicker( "marker");
		    gmarker.setVisible(true);
		    addresspickerMap.addresspicker( "updatePosition");
		
		   
		
		    function showCallback(geocodeResult, parsedGeocodeResult){
				if(parsedGeocodeResult.street_number != false)
				{
		        	if($("#anschrift").length > 0)
		        	{
						var val = "";
						if(parsedGeocodeResult.route != false)
						{
							val += parsedGeocodeResult.route;
						}
						if(parsedGeocodeResult.street_number != false)
						{
							val +=  " " + parsedGeocodeResult.street_number;
						}
		        		$("#anschrift").val(val);
		        	}
		        	else
		        	{
						if(parsedGeocodeResult.route != false)
						{
		        			$("#str").val(parsedGeocodeResult.route);
						}
						else
						{
							$("#str").val("");
						}
						if(parsedGeocodeResult.street_number != false)
						{
							$("#hsnr").val(parsedGeocodeResult.street_number);
						}
						else
						{
							$("#hsnr").val("");
						}
		        	}
				}
				else
				{
					if($("#anschrift").length > 0)
		        	{
						if(parsedGeocodeResult.route != false)
						{
							$("#anschrift").val(parsedGeocodeResult.route);
						}
						else
						{
							$("#anschrift").val("");
						}
		        		
		        	}
		        	else
		        	{
						if(parsedGeocodeResult.route != false)
						{
		        			$("#str").val(parsedGeocodeResult.route);
						}
						else
						{
							$("#str").val("");
						}
		        		$("#hsnr").val("");
		        	}
				}
		    }
		    // Update zoom field
		    var map = $("#addresspicker_map").addresspicker("map");
		    google.maps.event.addListener(map, "idle", function(){
		      $("#zoom").val(map.getZoom());
		    });		
		');
		
		
		$hsnr = v_form_text('anschrift');
		if(isset($options['hsnr']))
		{
			$hsnr = v_form_text('str').v_form_text('hsnr');
		}
		
		return v_input_wrapper(s('position_search'), '
		<input placeholder="Staße, Ort..." type="text" value="" id="addresspicker_map" name="addresspicker_map" class="input text value ui-corner-top" />
		<div id="map" class="pickermap"></div>').
		$hsnr.
		v_form_text('plz').
		v_form_text('ort').
		v_form_text('lat').
		v_form_text('lon').
		'';
	}

}