<?php

namespace Foodsharing\Modules\FoodSharePoint;

use Foodsharing\Modules\Core\DBConstants\Info\InfoType;
use Foodsharing\Modules\Core\View;

class FoodSharePointView extends View
{
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

	public function foodSharePointForm($data = []): string
	{
		$title = $this->translator->trans('fsp.new');

		$tagselect = '';
		if ($data) {
			$fspName = $this->foodSharePoint['name'];
			$title = $this->translator->trans('fsp.editName', ['{name}' => $fspName]);

			$tagselect = $this->v_utils->v_form_tagselect('fspmanagers', ['valueOptions' => $data['bfoodsaver_values'], 'values' => $data['bfoodsaver']]);
			$this->pageHelper->addJs('
			$("#fairteiler-form").on("submit", function(ev){
				if($("#fspmanagers input[type=\'hidden\']").length == 0)
				{
					ev.preventDefault();
					pulseError("' . $this->translator->trans('fsp.noCoordinator') . '");
				}
			});
			');

			foreach (['anschrift', 'plz', 'ort', 'lat', 'lon'] as $i) {
				$latLonOptions[$i] = $data[$i];
			}
			$latLonOptions['location'] = ['lat' => $data['lat'], 'lon' => $data['lon']];
		} else {
			$latLonOptions = [];
			$data['bezirk_id'] = null;
			$data['name'] = '';
			$data['desc'] = '';
			$data['picture'] = '';
		}

		// initial value for the image chooser can be empty (no image yet) or an old or new file path
		$initialValue = '';
		if (!empty($data['picture'])) {
			$initialValue = (strpos($data['picture'], '/api/uploads/') !== 0 ? '/images/' : '') . $data['picture'];
		}

		return $this->v_utils->v_field($this->v_utils->v_form('fairteiler', [
			$this->v_utils->v_form_select('bezirk_id', ['values' => $this->regions, 'selected' => $data['bezirk_id'], 'required' => true]),
			$this->v_utils->v_form_text('name', ['value' => $data['name'], 'required' => true]),
			$this->v_utils->v_form_textarea('desc', [
				'value' => $data['desc'],
				'desc' => $this->translator->trans('fsp.descLabel') . '<br>' . $this->translator->trans('info.md'),
				'required' => true,
			]),
			$this->vueComponent('image-upload', 'file-upload-v-form', [
				'inputName' => 'picture',
				'isImage' => true,
				'initialValue' => $initialValue,
				'imgHeight' => 525,
				'imgWidth' => 169
			]),
			$this->latLonPicker('latLng', $latLonOptions),
			$tagselect,
		], ['submit' => $this->translator->trans('button.save')]
		), $title, ['class' => 'ui-padding']);
	}

	public function options(array $items): string
	{
		return $this->v_utils->v_menu($items, $this->translator->trans('options'));
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
				title: "' . $this->translator->trans('fsp.followName', [
					'{name}' => $this->sanitizerService->jsSafe($this->foodSharePoint['name'], '"')
				]) . '",
				autoOpen: false,
				width: 500,
				resizable: false,
				buttons: {
					"' . $this->translator->trans('button.save') . '": function(){
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
						'desc' => $this->translator->trans('fsp.info.descModal'),
						'values' => [
							['id' => InfoType::BELL, 'name' => $this->translator->trans('fsp.info.bell')],
							['id' => InfoType::EMAIL, 'name' => $this->translator->trans('fsp.info.mail')],
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
			$out .= $this->v_utils->v_field(
				$this->fsAvatarList($this->follower['fsp_manager'], ['scroller' => false]),
				$this->translator->trans('fsp.managers')
			);
		}
		if (!empty($this->follower['follow'])) {
			$out .= $this->v_utils->v_field(
				$this->fsAvatarList($this->follower['follow'], ['height' => 700]),
				$this->translator->trans('fsp.followers')
			);
		}

		return $out;
	}

	public function desc(): string
	{
		return $this->v_utils->v_field(
			'<p>' . $this->sanitizerService->markdownToHtml($this->foodSharePoint['desc']) . '</p>',
			$this->translator->trans('fsp.description'),
			['class' => 'ui-padding fsp-desc']
		);
	}

	public function listFoodSharePoints(array $regions): string
	{
		$content = '';
		$count = 0;
		foreach ($regions as $region) {
			$count += count($region['fairteiler']);
			$content .= $this->twig->render('partials/listFoodSharePointsForRegion.html.twig', [
				'region' => $region,
				'food_share_point' => $region['fairteiler'],
			]);
		}

		$topbarHeader = $this->translator->trans('fsp.yours');
		$topbarText = $this->translator->trans('fsp.summary', ['{count}' => $count]);
		if ($this->region) {
			$regionName = $this->region['name'];
			$topbarHeader = $this->translator->trans('fsp.inRegion', ['{region}' => $regionName]);
			$topbarText = $this->translator->trans('fsp.summaryRegion', [
				'{count}' => $count,
				'{region}' => $regionName,
			]);
		}

		$this->pageHelper->addContent($this->topbar(
			$topbarHeader,
			$topbarText,
			'<img src="/img/foodSharePointThumb.png" />'
		), CNT_TOP);

		return $content;
	}

	public function foodSharePointOptions(int $regionId): string
	{
		// TODO instead, use FoodSharePointPermissions:mayAdd($regionId)
		$mayCreateFSP = $this->session->isAdminFor($regionId) || $this->session->isOrgaTeam();

		$item = [
			'name' => $this->translator->trans($mayCreateFSP ? 'fsp.add' : 'fsp.suggest'),
			'href' => '/?page=fairteiler&bid=' . $regionId . '&sub=add',
		];

		return $this->v_utils->v_menu([$item], $this->translator->trans('options'));
	}
}
