<?php

namespace Foodsharing\Modules\FoodSharePoint;

use Foodsharing\Modules\Core\View;

class FoodSharePointView extends View
{
	private $bezirk_id;
	private $bezirk;
	private $bezirke;

	private $foodSharePoint;
	private $follower;

	public function setBezirke($bezirke)
	{
		$this->bezirke = $bezirke;
	}

	public function setBezirk($bezirk)
	{
		$this->bezirk = $bezirk;
		$this->bezirk_id = $bezirk['id'];
	}

	public function setFoodSharePoint($foodSharePoint, $follower)
	{
		$this->foodSharePoint = $foodSharePoint;
		$this->follower = $follower;
	}

	public function foodSharePointHead()
	{
		return $this->twig->render('pages/FoodSharePoint/foodSharePointTop.html.twig', ['food_share_point' => $this->foodSharePoint]);
	}

	public function checkFoodSharePoint($foodSharePoint)
	{
		$htmlEscapedName = htmlspecialchars($foodSharePoint['name']);
		$content = '';
		if ($foodSharePoint['pic']) {
			$content .= $this->v_utils->v_input_wrapper('Foto', '<img src="' . $foodSharePoint['pic']['head'] . '" alt="' . $htmlEscapedName . '" />');
		}

		$content .= $this->v_utils->v_input_wrapper('Adresse', '
		' . $foodSharePoint['anschrift'] . '<br />
		' . $foodSharePoint['plz'] . ' ' . $foodSharePoint['ort']);

		$content .= $this->v_utils->v_input_wrapper('Beschreibung', $this->sanitizerService->markdownToHtml($foodSharePoint['desc']));

		$content .= $this->v_utils->v_input_wrapper('Hinzugefügt am', date('d.m.Y', $foodSharePoint['time_ts']));
		$content .= $this->v_utils->v_input_wrapper('Hinzugefügt von', '<a href="/profile/' . (int)$foodSharePoint['fs_id'] . '">' . $foodSharePoint['fs_name'] . ' ' . $foodSharePoint['fs_nachname'] . '</a>');

		return $this->v_utils->v_field($content, $foodSharePoint['name'] . ' freischalten', array('class' => 'ui-padding'));
	}

	public function address()
	{
		return $this->v_utils->v_field(
			$this->v_utils->v_input_wrapper('Anschrift', $this->foodSharePoint['anschrift']) .
			$this->v_utils->v_input_wrapper('PLZ / Ort', $this->foodSharePoint['plz'] . ' ' . $this->foodSharePoint['ort']),
			'Adresse',
			array('class' => 'ui-padding')
		);
	}

	public function foodSharePointForm($data = false)
	{
		$title = $this->translationHelper->s('new_food_share_point');

		$tagselect = '';
		if ($data) {
			$title = $this->translationHelper->sv('edit_food_share_point_name', $this->foodSharePoint['name']);

			$tagselect = $this->v_utils->v_form_tagselect('bfoodsaver', array('valueOptions' => $data['bfoodsaver_values'], 'values' => $data['bfoodsaver']));
			$this->pageHelper->addJs('
			$("#fairteiler-form").on("submit", function(ev){
				if($("#bfoodsaver input[type=\'hidden\']").length == 0)
				{
					ev.preventDefault();
					pulseError("Es muss mindestens einen Verantwortlichen für diesen Fair-Teiler geben!");
				}
			});
		');
		}
		foreach (['anschrift', 'plz', 'ort', 'lat', 'lon'] as $i) {
			$latLonOptions[$i] = $data[$i];
		}
		$latLonOptions['location'] = ['lat' => $data['lat'], 'lon' => $data['lon']];

		return $this->v_utils->v_field($this->v_utils->v_form('fairteiler', array(
			$this->v_utils->v_form_select('bezirk_id', array('values' => $this->bezirke, 'selected' => $data['bezirk_id'], 'required' => true)),
			$this->v_utils->v_form_text('name', array('value' => $data['name'], 'required' => true)),
			$this->v_utils->v_form_textarea('desc', array('value' => $data['desc'], 'desc' => $this->translationHelper->s('desc_desc'), 'required' => true)),
			$this->v_utils->v_form_picture('picture', array('pic' => $data['picture'], 'resize' => array(528, 60), 'crop' => array((528 / 170), 1))),
			$this->latLonPicker('latLng', $latLonOptions),
			$tagselect,
		), array('submit' => $this->translationHelper->s('save'))), $title, array('class' => 'ui-padding'));
	}

	public function wallposts()
	{
	}

	public function options($items)
	{
		return $this->v_utils->v_menu($items, 'Optionen');
	}

	public function followHidden()
	{
		$this->pageHelper->addJsFunc('
			function u_follow()
			{
				$("#follow-hidden").dialog("open");
			}
		');
		$this->pageHelper->addJs('
			$("#follow-hidden").dialog({
				modal: true,
				title: "' . $this->translationHelper->sv('infotype_title', $this->sanitizerService->jsSafe($this->foodSharePoint['name'], '"')) . '",
				autoOpen: false,
				width: 500,
				resizable: false,
				buttons: {
					"' . $this->translationHelper->s('save') . '": function(){
						goTo("' . $this->routeHelper->getSelf() . '&follow=1&infotype=" + $("input[name=\'infotype\']:checked").val());
					}
				}
			});		
		');

		global $g_data;
		$g_data['infotype'] = 1;

		return '
			<div id="follow-hidden">
				' . $this->v_utils->v_form_radio('infotype', array('desc' => $this->translationHelper->s('infotype_desc'), 'values' => array(
				array('id' => 1, 'name' => $this->translationHelper->s('infotype_email')),
				array('id' => 2, 'name' => $this->translationHelper->s('infotype_alert'))
			))) . '
			</div>
		';
	}

	public function follower()
	{
		$out = '';

		if (!empty($this->follower['verantwortlich'])) {
			$out .= $this->v_utils->v_field($this->fsAvatarList($this->follower['verantwortlich'], array('scroller' => false)), 'verantwortliche Foodsaver');
		}
		if (!empty($this->follower['follow'])) {
			$out .= $this->v_utils->v_field($this->fsAvatarList($this->follower['follow'], array('height' => 700)), $this->translationHelper->s('follower'));
		}

		return $out;
	}

	public function desc()
	{
		return $this->v_utils->v_field('<p>' . $this->sanitizerService->markdownToHtml($this->foodSharePoint['desc']) . '</p>', $this->translationHelper->s('desc'), array('class' => 'ui-padding'));
	}

	public function listFoodSharePoints($bezirke)
	{
		$content = '';
		$count = 0;
		foreach ($bezirke as $bezirk) {
			$count += count($bezirk['fairteiler']);
			$content .= $this->twig->render('partials/listFoodSharePointsForRegion.html.twig', ['region' => $bezirk, 'food_share_point' => $bezirk['fairteiler']]);
		}

		if ($this->bezirk_id > 0) {
			$this->pageHelper->addContent($this->topbar($this->translationHelper->sv('list_food_share_point', $this->bezirk['name']), 'Es gibt ' . $count . ' Fair-Teiler in ' . $this->bezirk['name'] . ' und allen Unterbezirken',
				'<img src="/img/foodSharePointThumb.png" />'
			), CNT_TOP);
		} else {
			$this->pageHelper->addContent($this->topbar($this->translationHelper->s('your_food_share_point'), 'Es gibt ' . $count . ' Fair-Teiler in allen Bezirken in denen Du aktiv bist',
				'<img src="/img/foodSharePointThumb.png" />'
			), CNT_TOP);
		}

		return $content;
	}

	public function foodSharePointOptions($bezirk_id)
	{
		$items = array();
		if ($this->session->isAdminFor($bezirk_id) || $this->session->isOrgaTeam()) {
			$items[] = array('name' => 'Fair-Teiler eintragen', 'href' => '/?page=fairteiler&bid=' . (int)$bezirk_id . '&sub=addFt');
		} else {
			$items[] = array('name' => 'Fair-Teiler vorschlagen', 'href' => '/?page=fairteiler&bid=' . (int)$bezirk_id . '&sub=addFt');
		}

		return $this->v_utils->v_menu($items, 'Optionen');
	}
}
