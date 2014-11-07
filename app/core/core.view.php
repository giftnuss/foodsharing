<?php
class View
{
	private $sub;
	
	public function setSub($sub)
	{
		$this->sub = $sub;
	}
	
	public function login($ref = false)
	{
		$action = '/?page=login';
		if($ref != false)
		{
			$action = '/?page=login&ref=' . urlencode($ref);
		}
		else if(!isset($_GET['ref']))
		{
			$action = '/?page=login&ref=' . urlencode($_SERVER['REQUEST_URI']);
		}
		
		addJs('
				storage.del("msg-chats");
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
								v_form_text('email_adress',array('label' => false,'placeholder' => s('email_adress'))),
								v_form_passwd('password', array('label' => false,'placeholder' => s('password'))),
								v_form_hidden('ismob', '0').
								'<p>
									<a href="/?page=login&sub=passwordReset">Passwort vergessen?</a>
								</p>
								<p class="buttons">
									<input class="button" type="submit" value="'.s('login').'" name="login" /> <a href="#" onclick="ajreq(\'join\',{app:\'login\'});return false;" class="button">'.s('register').'</a>
								</p>'
						),array('action' => $action,'submit' => false )),'Login',array('class' => 'ui-padding')).'
			</div>';
	}
	
	public function topbar($title,$subtitle = '',$icon = '')
	{
		if ($icon != '')
		{
			$icon = '<div class="img">'.$icon.'</div>';
		}
		
		if ($subtitle != '')
		{
			$subtitle = '<p>'.$subtitle.'</p>';
		}
		
		return '
		<div class="top corner-all">
			'.$icon.'
			<h3>'.$title.'</h3>
			'.$subtitle.'
			<div style="clear:both;"></div>		
		</div>';
	}
	
	public function distance($distance)
	{
		$distance = round($distance,1);
			
		if($distance == 1.0)
		{
			$distance = '1 km';
		}
		else if($distance < 1)
		{
			$distance = ($distance*1000).' m';
		}
		else
		{
			$distance = number_format($distance,1,',','.').' km';
		}
		
		return $distance;
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
		if(!isset($option['noshuffle']))
		{
			shuffle($foodsaver);
		}
		foreach ($foodsaver as $fs)
		{
			$jssaver[] = (int)$fs['id'];
			
			$title = $fs['name'].' ist offline';
			$ampel = 'ampel-grau';

			$photo = avatar($fs);
			
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
	
	public function peopleChooser($id,$option = array())
	{
		addJs('
			var date = new Date(); 
			tstring = ""+date.getYear() + ""+date.getMonth() + ""+date.getDate() + ""+date.getHours();
			var localsource = [];
			$.ajax({
				url: "/cache/searchindex/'.S::user('token').'.json",
				dataType: "json",
				data: {t:$.now()},
				success: function(json){
					
					if(json.length > 0 && json[0] != undefined && json[0].key != undefined && json[0].key == "buddies")
					{
						
						for(y=0;y<json[0].result.length;y++)
						{
							localsource.push({id:json[0].result[y].id,value:json[0].result[y].name});
						}
						
					}
				},
				complete: function(){
					$("#'.$id.' input.tag").tagedit({
						autocompleteOptions: {
							delay: 0,
							source: function(request, response) { 
							
								//response(localsource);
					            /* Remote results only if string > 3: */
								
								if(request.term.length > 3)
								{
									$.ajax({
						                url: "/xhrapp.php?app=msg&m=people",
										data: {term:request.term},
						                dataType: "json",
						                success: function(data) {
											
											local = [];
											term = request.term.toLowerCase();
											for(i=0;i<localsource.length;i++)
											{
												if(localsource[i].value.indexOf(term) > 0)
												{
													local.push(localsource[i]);
												}
											}
							
											response(merge(local,data,"id"));
						                }
						            });
								}
								else
								{
									response(localsource);
								}
								
					        },
							minLength: 1
						},
						allowEdit: false,
						allowAdd: false,
						animSpeed:1
					});
				}
			});
				
				var localsource = [{"id":"56","value":"Raphael Wintrich"},{"id":"62","value":"Raphael"}];
				/*
				for(i=0;i<data.length;i++)
				{
					//console.log(data[i]);
				}
				*/
				
				
			/*
			$.getJSON( "/cache/searchindex/'.S::user('token').'.json?t=" + tstring, function( data ) {
				
				
			});
			*/
		');
		
		$input = '<input type="text" name="'.$id.'[]" value="" class="tag input text value" />';
		
		return v_input_wrapper(s($id), '<div id="'.$id.'">'.$input.'</div>',$id,$option);
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