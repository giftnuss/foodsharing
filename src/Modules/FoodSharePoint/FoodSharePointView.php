<?php

namespace Foodsharing\Modules\FoodSharePoint;

use Foodsharing\Modules\Core\DBConstants\Info\InfoType;
use Foodsharing\Modules\Core\View;

class FoodSharePointView extends View
{
	private $regionId;
	private $region;
	private $regions;

	private $foodSharePoint;
	private $follower;

	public function setRegions(array $regions): void
	{
		$this->regions = $regions;
	}

	public function setRegion($region): void
	{
		$this->region = $region;
		$this->regionId = $region['id'];
	}

	public function setFoodSharePoint($foodSharePoint, $follower): void
	{
		$this->foodSharePoint = $foodSharePoint;
		$this->follower = $follower;
	}

	public function foodSharePointHead(): string
	{
		return $this->twig->render('pages/FoodSharePoint/foodSharePointTop.html.twig', ['food_share_point' => $this->foodSharePoint]);
	}

	public function checkFoodSharePoint($foodSharePoint): string
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

		return $this->v_utils->v_field($content, $foodSharePoint['name'] . ' freischalten', ['class' => 'ui-padding']);
	}

	public function address(): string
	{
		return $this->v_utils->v_field(
			$this->v_utils->v_input_wrapper('Anschrift', $this->foodSharePoint['anschrift']) .
			$this->v_utils->v_input_wrapper('PLZ / Ort', $this->foodSharePoint['plz'] . ' ' . $this->foodSharePoint['ort']),
			'Adresse',
			['class' => 'ui-padding']
		);
	}

	public function foodSharePointForm($data = false): string
	{
		$title = $this->translationHelper->s('new_food_share_point');

		$tagselect = '';
		if ($data) {
			$title = $this->translationHelper->sv('edit_food_share_point_name', $this->foodSharePoint['name']);

			$tagselect = $this->v_utils->v_form_tagselect('bfoodsaver', ['valueOptions' => $data['bfoodsaver_values'], 'values' => $data['bfoodsaver']]);
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

		return $this->v_utils->v_field($this->v_utils->v_form('fairteiler', [
			$this->v_utils->v_form_select('bezirk_id', ['values' => $this->regions, 'selected' => $data['bezirk_id'], 'required' => true]),
			$this->v_utils->v_form_text('name', ['value' => $data['name'], 'required' => true]),
			$this->v_utils->v_form_textarea('desc', ['value' => $data['desc'], 'desc' => $this->translationHelper->s('desc_desc'), 'required' => true]),
			$this->v_utils->v_form_picture('picture', ['pic' => $data['picture'], 'resize' => [528, 60], 'crop' => [(528 / 170), 1]]),
			$this->latLonPicker('latLng', $latLonOptions),
			$tagselect,
		], ['submit' => $this->translationHelper->s('save')]
		), $title, ['class' => 'ui-padding']);
	}

	public function options(array $items): string
	{
		return $this->v_utils->v_menu($items, 'Optionen');
	}

	public function followHidden(): string
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
				' . $this->v_utils->v_form_radio(
					'infotype',
					[
						'desc' => $this->translationHelper->s('infotype_desc'),
						'values' => [
							['id' => InfoType::EMAIL, 'name' => $this->translationHelper->s('infotype_email')],
							['id' => InfoType::BELL, 'name' => $this->translationHelper->s('infotype_bell')]
						]
					]
				) . '
			</div>
		';
	}

	public function follower(): string
	{
		$out = '';

		if (!empty($this->follower['fsp_manager'])) {
			$out .= $this->v_utils->v_field($this->fsAvatarList($this->follower['fsp_manager'], ['scroller' => false]), $this->translationHelper->s('contact_fsp'));
		}
		if (!empty($this->follower['follow'])) {
			$out .= $this->v_utils->v_field($this->fsAvatarList($this->follower['follow'], ['height' => 700]), $this->translationHelper->s('follower'));
		}

		return $out;
	}

	public function desc(): string
	{
		return $this->v_utils->v_field('<p>' . $this->sanitizerService->markdownToHtml($this->foodSharePoint['desc']) . '</p>', $this->translationHelper->s('desc'), ['class' => 'ui-padding']);
	}

	public function listFoodSharePoints(array $regions): string
	{
		$content = '';
		$count = 0;
		foreach ($regions as $region) {
			$count += count($region['fairteiler']);
			$content .= $this->twig->render('partials/listFoodSharePointsForRegion.html.twig', ['region' => $region, 'food_share_point' => $region['fairteiler']]);
		}

		if ($this->regionId > 0) {
			$this->pageHelper->addContent($this->topbar($this->translationHelper->sv('list_food_share_point', $this->region['name']), 'Es gibt ' . $count . ' Fair-Teiler in ' . $this->region['name'] . ' und allen Unterbezirken',
				'<img src="/img/foodSharePointThumb.png" />'
			), CNT_TOP);
		} else {
			$this->pageHelper->addContent($this->topbar($this->translationHelper->s('your_food_share_point'), 'Es gibt ' . $count . ' Fair-Teiler in allen Bezirken in denen Du aktiv bist',
				'<img src="/img/foodSharePointThumb.png" />'
			), CNT_TOP);
		}

		return $content;
	}

	public function foodSharePointOptions(int $regionId): string
	{
		$items = [];
		if ($this->session->isAdminFor($regionId) || $this->session->isOrgaTeam()) {
			$items[] = ['name' => 'Fair-Teiler eintragen', 'href' => '/?page=fairteiler&bid=' . $regionId . '&sub=add'];
		} else {
			$items[] = ['name' => 'Fair-Teiler vorschlagen', 'href' => '/?page=fairteiler&bid=' . $regionId . '&sub=add'];
		}

		return $this->v_utils->v_menu($items, 'Optionen');
	}
}
