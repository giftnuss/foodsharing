<?php

namespace Foodsharing\Modules\Core;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\Func;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;

class View
{
	private $sub;

	/* @var \Foodsharing\Lib\View\Utils */
	protected $v_utils;
	protected $func;
	protected $session;

	/**
	 * @var \Twig\Environment
	 */
	public $twig;

	public function __construct(\Twig\Environment $twig, Func $func, Utils $viewUtils, Session $session)
	{
		$this->twig = $twig;
		$this->func = $func;
		$this->v_utils = $viewUtils;
		$this->session = $session;
	}

	public function setSub($sub)
	{
		$this->sub = $sub;
	}

	public function topbar($title, $subtitle = '', $icon = '')
	{
		if ($icon != '') {
			$icon = '<div class="img">' . $icon . '</div>';
		}

		if ($subtitle != '') {
			$subtitle = '<p>' . $subtitle . '</p>';
		}

		return '
		<div class="top corner-all">
			' . $icon . '
			<h3>' . $title . '</h3>
			' . $subtitle . '
			<div style="clear:both;"></div>		
		</div>';
	}

	public function distance($distance)
	{
		$distance = round($distance, 1);

		if ($distance == 1.0) {
			$distance = '1 km';
		} elseif ($distance < 1) {
			$distance = ($distance * 1000) . ' m';
		} else {
			$distance = number_format($distance, 1, ',', '.') . ' km';
		}

		return $distance;
	}

	public function locationMumble()
	{
		$out = $this->v_utils->v_field('
		<p>Online-Termin</p>
		<p style="text-align:center;">
			<a target="_blank" href="https://wiki.foodsharing.de/Mumble"><img src="/img/mlogo.png" alt="Mumble" /></a>
		</p>
		<p>
			Online-Sprachkonferenzen machen wir mit Mumble	
		</p>
		<p>Unser Mumble-Server:<br />mumble.foodsharing.de</p>
		<p>Anleitung unter: <a target="_blank" href="https://wiki.foodsharing.de/Mumble">wiki.foodsharing.de/Mumble</a></p>
		', 'Ort', array('class' => 'ui-padding'));

		return $out;
	}

	public function location($location)
	{
		$out = $this->v_utils->v_field('
		<p>' . $location['name'] . '</p>
		<p>
			' . $location['street'] . '<br />
			' . $location['zip'] . ' ' . $location['city'] . '
		</p>
				
		', 'Ort', array('class' => 'ui-padding'));

		return $out;
	}

	public function headline($title, $img = '', $click = false)
	{
		if ($click !== false) {
			$click = ' onclick="' . $click . 'return false;"';
		}
		if (!empty($img)) {
			$img = '
			<div class="welcome_profile_image">
				<a href="#"' . $click . '>
					<img width="50" height="50" src="' . $img . '" alt="Raphael" class="image_online">
				</a>
			</div>';
		}

		return '
					
		<div class="welcome ui-padding margin-bottom ui-corner-all">
			' . $img . '
			<div class="welcome_profile_name">
				<div class="user_display_name">
					' . $title . '
				</div>
			</div>
		</div>';
	}

	public function fsIcons($foodsaver)
	{
		if (!empty($foodsaver)) {
			$out = '<ul class="fsicons">';

			if (count($foodsaver) > 100) {
				shuffle($foodsaver);
			}
			$i = 52;
			foreach ($foodsaver as $fs) {
				--$i;
				$out .= '
				<li>
					<a title="' . $fs['name'] . '" style="background-image:url(' . $this->func->img($fs['photo']) . ');" href="/profile/' . (int)$fs['id'] . '"><span></span></a>	
				</li>';
				if ($i <= 0) {
					$out .= '<li class="row">...und ' . (count($foodsaver) - 52) . ' weitere</li>';
					break;
				}
			}
			$out .= '</ul>';

			return $out;
		}

		return '';
	}

	public function fsAvatarList($foodsaver, $option = array())
	{
		if (!is_array($foodsaver)) {
			return '';
		}

		if (!isset($option['scroller'])) {
			$option['scroller'] = true;
		}

		$id = $this->func->id('team');
		if (isset($option['id'])) {
			$id = $option['id'];
		}

		$height = 185;
		if (isset($option['height'])) {
			$height = $option['height'];
		}

		$out = '
		<div>
			<ul id="' . $id . '" class="linklist">';
		if (!isset($option['noshuffle'])) {
			shuffle($foodsaver);
		}
		foreach ($foodsaver as $fs) {
			$jssaver[] = (int)$fs['id'];

			$photo = $this->func->avatar($fs);

			$click = ' onclick="profile(' . (int)$fs['id'] . ');return false;"';

			$href = '#';
			if (isset($fs['href'])) {
				$click = '';
				$href = $fs['href'];
			}

			$out .= '
				<li>
					<a href="' . $href . '"' . $click . ' class="ui-corner-all">
						<span style="float:left;margin-right:7px;">' . $photo . '</span>
						<span class="title">' . $fs['name'] . '</span>
						<span style="clear:both;"></span>
					</a>
				</li>';
		}
		$out .= '
			</ul>
			<div style="clear:both"></div>
		</div>';

		if ($option['scroller']) {
			$out = $this->v_utils->v_scroller($out, $height);
			$this->func->addStyle('.scroller .overview{left:0;}.scroller{margin:0}');
		}

		return $out;
	}

	public function menu($items, $option = array())
	{
		$title = false;
		if (isset($option['title'])) {
			$title = $option['title'];
		}

		$active = false;
		if (isset($option['active'])) {
			$active = $option['active'];
		}

		$id = $this->func->id('vmenu');

		$out = '';

		foreach ($items as $item) {
			if (!isset($item['href'])) {
				$item['href'] = '#';
			}

			$click = '';
			if (isset($item['click'])) {
				$click = ' onclick="' . $item['click'] . '"';
			}
			$class = '';
			if ($active !== false && (strpos($item['href'], '=' . $active) !== false)) {
				$class = 'active ';
			}

			$out .= '<li>
					<a class="' . $class . 'ui-corner-all" href="' . $item['href'] . '"' . $click . '>
						<span>' . $item['name'] . '</span>
					</a>
				</li>';
		}

		if (!$title) {
			return '
		<div class="ui-widget ui-widget-content ui-corner-all ui-padding margin-bottom">
			<ul class="linklist">
				' . $out . '
			</ul>
		</div>';
		}

		return '
		<h3 class="head ui-widget-header ui-corner-top">' . $title . '</h3>
		<div class="ui-widget ui-widget-content ui-corner-bottom ui-padding margin-bottom">
			<ul class="linklist">
				' . $out . '
			</ul>
		</div>';
	}

	public function peopleChooser($id, $option = array())
	{
		$this->func->addJs('
			var date = new Date(); 
			tstring = ""+date.getYear() + ""+date.getMonth() + ""+date.getDate() + ""+date.getHours();
			var localsource = [];
			$.ajax({
				url: "/cache/searchindex/' . $this->session->user('token') . '.json",
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
					$("#' . $id . ' input.tag").tagedit({
						autocompleteOptions: {
							delay: 0,
							source: function(request, response) { 
					            /* Remote results only if string > 3: */
								
								if(request.term.length > 3)
								{
									$.ajax({
						                url: "/xhrapp.php?app=msg&m=people",
										data: {term:request.term},
						                dataType: "json",
						                success: function(data) {
											response(data);
											// following doesn\'t work somehow => ignoring
											// local = [];
											// term = request.term.toLowerCase();
											// for(i=0;i<localsource.length;i++)
											// {
											// 	if(localsource[i].value.indexOf(term) > 0)
											// 	{
											// 		local.push(localsource[i]);
											// 	}
											// }
											// response(merge(local,data,"id"));
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
				
				var localsource = [];
		');

		$input = '<input type="text" name="' . $id . '[]" value="" class="tag input text value" />';

		return $this->v_utils->v_input_wrapper($this->func->s($id), '<div id="' . $id . '">' . $input . '</div>', $id, $option);
	}

	public function latLonPicker($id, $options = array())
	{
		if (isset($options['location'])) {
			$data = array_merge(['zoom' => 14], $options['location']);
		} else {
			global $container;
			/* @var $db Db */
			$db = $container->get(Db::class);
			$data = $db->getValues(array('lat', 'lon'), 'foodsaver', $this->func->fsId());
			$data['zoom'] = 14;
		}

		if (empty($data['lat']) || empty($data['lon'])) {
			/* set empty coordinates somewhere in germany */
			$data['lat'] = 51;
			$data['lon'] = 10;
			$data['zoom'] = 5;
		}

		// Default to blank values for these keys
		foreach (['anschrift', 'plz', 'ort'] as $key) {
			if (!isset($options[$key])) {
				$options[$key] = '';
			}
		}

		return $this->v_utils->v_input_wrapper($this->func->s('position_search'), '
		<input placeholder="Bitte hier Deine Adresse suchen! Falls nötig, danach unten korrigieren." type="text" value="" id="addresspicker" type="text" class="input text value ui-corner-top" />
		<div id="map" class="pickermap"></div>') .
			$this->v_utils->v_form_text('anschrift', ['value' => $options['anschrift'], 'required' => '1']) .
			$this->v_utils->v_form_text('plz', ['value' => $options['plz'], 'disabled' => '1', 'required' => '1']) .
			$this->v_utils->v_form_text('ort', ['value' => $options['ort'], 'disabled' => '1', 'required' => '1']) .
			$this->v_utils->v_form_text('lat', ['value' => $data['lat']]) .
			$this->v_utils->v_form_text('lon', ['value' => $data['lon']]) .
			'';
	}

	public function simpleContent($content)
	{
		$out = $this->v_utils->v_field($content['body'], $content['title'], array('class' => 'ui-padding'));

		return $out;
	}

	public function vueComponent($id, $component, $props = array(), $data = array())
	{
		return $this->twig->render('partials/vue-wrapper.twig', [
			'id' => $id,
			'component' => $component,
			'props' => $props,
			'initialData' => $data,
		]);
	}
}
