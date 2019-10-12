<?php

namespace Foodsharing\Modules\FairTeiler;

use Foodsharing\Modules\Core\View;

class FairTeilerView extends View
{
	private $regionId;
	private $region;
	private $regions;

	private $fairteiler;
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

	public function setFairteiler($fairteiler, $follower): void
	{
		$this->fairteiler = $fairteiler;
		$this->follower = $follower;
	}

	public function fairteilerHead(): string
	{
		return $this->twig->render('pages/Fairteiler/fairteilerTop.html.twig', ['fairteiler' => $this->fairteiler]);
	}

	public function checkFairteiler($ft): string
	{
		$htmlEscapedName = htmlspecialchars($ft['name']);
		$content = '';
		if ($ft['pic']) {
			$content .= $this->v_utils->v_input_wrapper('Foto', '<img src="' . $ft['pic']['head'] . '" alt="' . $htmlEscapedName . '" />');
		}

		$content .= $this->v_utils->v_input_wrapper('Adresse', '
		' . $ft['anschrift'] . '<br />
		' . $ft['plz'] . ' ' . $ft['ort']);

		$content .= $this->v_utils->v_input_wrapper('Beschreibung', $this->sanitizerService->markdownToHtml($ft['desc']));

		$content .= $this->v_utils->v_input_wrapper('Hinzugefügt am', date('d.m.Y', $ft['time_ts']));
		$content .= $this->v_utils->v_input_wrapper('Hinzugefügt von', '<a href="/profile/' . (int)$ft['fs_id'] . '">' . $ft['fs_name'] . ' ' . $ft['fs_nachname'] . '</a>');

		return $this->v_utils->v_field($content, $ft['name'] . ' freischalten', array('class' => 'ui-padding'));
	}

	public function address(): string
	{
		return $this->v_utils->v_field(
			$this->v_utils->v_input_wrapper('Anschrift', $this->fairteiler['anschrift']) .
			$this->v_utils->v_input_wrapper('PLZ / Ort', $this->fairteiler['plz'] . ' ' . $this->fairteiler['ort']),
			'Adresse',
			array('class' => 'ui-padding')
		);
	}

	public function fairteilerForm($data = false): string
	{
		$title = $this->translationHelper->s('new_fairteiler');

		$tagselect = '';
		if ($data) {
			$title = $this->translationHelper->sv('edit_fairteiler_name', $this->fairteiler['name']);

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

		return $this->v_utils->v_field($this->v_utils->v_form('fairteiler', [
			$this->v_utils->v_form_select('bezirk_id', ['values' => $this->regions, 'selected' => $data['bezirk_id'], 'required' => true]
			),
			$this->v_utils->v_form_text('name', ['value' => $data['name'], 'required' => true]),
			$this->v_utils->v_form_textarea('desc', ['value' => $data['desc'], 'desc' => $this->translationHelper->s('desc_desc'), 'required' => true]
			),
			$this->v_utils->v_form_picture('picture', ['pic' => $data['picture'], 'resize' => [528, 60], 'crop' => [(528 / 170), 1]]
			),
			$this->latLonPicker('latLng', $latLonOptions),
			$tagselect,
		], ['submit' => $this->translationHelper->s('save')]
		), $title, array('class' => 'ui-padding'));
	}

	public function wallposts(): void
	{
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
				title: "' . $this->translationHelper->sv('infotype_title', $this->sanitizerService->jsSafe($this->fairteiler['name'], '"')) . '",
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

	public function follower(): string
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

	public function desc(): string
	{
		return $this->v_utils->v_field('<p>' . $this->sanitizerService->markdownToHtml($this->fairteiler['desc']) . '</p>', $this->translationHelper->s('desc'), array('class' => 'ui-padding'));
	}

	public function listFairteiler(array $regions): string
	{
		$content = '';
		$count = 0;
		foreach ($regions as $bezirk) {
			$count += count($bezirk['fairteiler']);
			$content .= $this->twig->render('partials/listFairteilerForRegion.html.twig', ['region' => $bezirk, 'fairteiler' => $bezirk['fairteiler']]);
		}

		if ($this->regionId > 0) {
			$this->pageHelper->addContent($this->topbar($this->translationHelper->sv('list_fairteiler', $this->region['name']), 'Es gibt ' . $count . ' Fair-Teiler in ' . $this->region['name'] . ' und allen Unterbezirken', '<img src="/img/fairteiler_thumb.png" />'), CNT_TOP);
		} else {
			$this->pageHelper->addContent($this->topbar($this->translationHelper->s('your_fairteiler'), 'Es gibt ' . $count . ' Fair-Teiler in allen Bezirken in denen Du aktiv bist', '<img src="/img/fairteiler_thumb.png" />'), CNT_TOP);
		}

		return $content;
	}

	public function ftOptions(int $regionId): string
	{
		$items = array();
		if ($this->session->isAdminFor($regionId) || $this->session->isOrgaTeam()) {
			$items[] = ['name' => 'Fair-Teiler eintragen', 'href' => '/?page=fairteiler&bid=' . $regionId . '&sub=add'];
		} else {
			$items[] = ['name' => 'Fair-Teiler vorschlagen', 'href' => '/?page=fairteiler&bid=' . $regionId . '&sub=add'];
		}

		return $this->v_utils->v_menu($items, 'Optionen');
	}
}
