<?php

namespace Foodsharing\Modules\FairTeiler;

use Foodsharing\Modules\Core\View;

class FairTeilerView extends View
{
	private $bezirk_id;
	private $bezirk;
	private $bezirke;

	private $fairteiler;
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

	public function loginToFollow()
	{
		return $this->v_utils->v_field(
			$this->v_utils->v_info('Wenn Du Dich einloggst kannst Du Dich benachrichtigen lassen bei Updates zu diesem FairTeiler')
			. $this->menu(array(array('name' => 'jetzt einloggen', 'click' => 'login();'))),
			false
		);
	}

	public function setFairteiler($fairteiler, $follower)
	{
		$this->fairteiler = $fairteiler;
		$this->follower = $follower;
	}

	public function fairteilerHead()
	{
		$style = '';

		if ($this->fairteiler['picture']) {
			$style = ' style="height:150px;background-image:url(' . $this->fairteiler['pic']['head'] . ');"';
		}

		$content = '<div class="ft-head ui-corner-bottom"' . $style . '></div>';

		return $this->v_utils->v_field($content, $this->fairteiler['name']);
	}

	public function checkFairteiler($ft)
	{
		/*
		 * Array
(
	[id] => 1
	[bezirk_id] => 4
	[name] => Neuland Garten
	[picture] => picture/52e3fed700753.png
	[status] => 1
	[desc] => ......
	[anschrift] => Alteburger StraÃŸe 146-150
	[plz] => 50968
	[ort] => KÃ¶ln
	[lat] => 50.91374876646103
	[lon] => 6.96526769416505
	[add_date] => 2014-01-24
	[time_ts] => 1390518000
	[add_foodsaver] => 56
	[fs_name] => Raphael
	[fs_nachname] => Wintrich
	[fs_id] => 56
	[pic] => Array
		(
			[thumb] => images/picture/crop_1_60_52e3fed700753.png
			[head] => images/picture/crop_0_528_52e3fed700753.png
			[orig] => images/picture/52e3fed700753.png
		)

)
		 */
		$content = '';
		if ($ft['pic']) {
			$content .= $this->v_utils->v_input_wrapper('Foto', '<img src="' . $ft['pic']['head'] . '" alt="' . $ft['name'] . '" />');
		}

		$content .= $this->v_utils->v_input_wrapper('Adresse', '
		' . $ft['anschrift'] . '<br />
		' . $ft['plz'] . ' ' . $ft['ort']);

		$content .= $this->v_utils->v_input_wrapper('Beschreibung', $ft['desc']);

		$content .= $this->v_utils->v_input_wrapper('Hinzugefügt am', date('d.m.Y', $ft['time_ts']));
		$content .= $this->v_utils->v_input_wrapper('Hinzugefügt von', '<a href="#" onclick="profile(' . (int)$ft['fs_id'] . ');">' . $ft['fs_name'] . ' ' . $ft['fs_nachname'] . '</a>');

		return $this->v_utils->v_field($content, $ft['name'] . ' freischalten', array('class' => 'ui-padding'));
	}

	public function address()
	{
		return $this->v_utils->v_field(
			$this->v_utils->v_input_wrapper('Anschrift', $this->fairteiler['anschrift']) .
			$this->v_utils->v_input_wrapper('PLZ / Ort', $this->fairteiler['plz'] . ' ' . $this->fairteiler['ort']),
			'Adresse',
			array('class' => 'ui-padding')
		);
	}

	public function fairteilerForm($data = false)
	{
		$title = $this->func->s('new_fairteiler');

		$tagselect = '';
		if ($data) {
			$this->func->setEditData($data);
			$title = $this->func->sv('edit_fairteiler_name', $this->fairteiler['name']);

			$tagselect = $this->v_utils->v_form_tagselect('bfoodsaver', array('data' => $data['bfoodsaver_values']));
			$this->func->addJs('
			$("#fairteiler-form").submit(function(ev){
				if($("#bfoodsaver input[type=\'hidden\']").length == 0)
				{
					ev.preventDefault();
					pulseError("Es muss mindestens einen Verantwortlichen für diesen Fair-Teiler geben!");
				}
			});
		');
		}

		return $this->v_utils->v_field($this->v_utils->v_form('fairteiler', array(
			$this->v_utils->v_form_select('bezirk_id', array('values' => $this->bezirke, 'required' => true)),
			$this->v_utils->v_form_text('name', array('required' => true)),
			$this->v_utils->v_form_textarea('desc', array('desc' => $this->func->s('desc_desc'), 'required' => true)),
			$this->v_utils->v_form_picture('picture', array('resize' => array(528, 60), 'crop' => array((528 / 170), 1))),
			$this->latLonPicker('latLng'),
			$tagselect,
		), array('submit' => $this->func->s('save'))), $title, array('class' => 'ui-padding'));
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
		$this->func->addJsFunc('
			function u_follow()
			{
				$("#follow-hidden").dialog("open");
			}
		');
		$this->func->addJs('
			$("#follow-hidden").dialog({
				modal: true,
				title: "' . $this->func->sv('infotype_title', $this->func->jsSafe($this->fairteiler['name']), '"') . '",
				autoOpen: false,
				width: 500,
				resizable: false,
				buttons: {
					"' . $this->func->s('save') . '": function(){
						goTo("' . $this->func->getSelf() . '&follow=1&infotype=" + $("input[name=\'infotype\']:checked").val());
					}
				}
			});		
		');

		global $g_data;
		$g_data['infotype'] = 1;

		return '
			<div id="follow-hidden">
				' . $this->v_utils->v_form_radio('infotype', array('desc' => $this->func->s('infotype_desc'), 'values' => array(
				array('id' => 1, 'name' => $this->func->s('infotype_email')),
				array('id' => 2, 'name' => $this->func->s('infotype_alert'))
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
			$out .= $this->v_utils->v_field($this->fsAvatarList($this->follower['follow']), $this->func->s('follower'));
		}

		return $out;
	}

	public function desc()
	{
		return $this->v_utils->v_field('<p>' . nl2br($this->fairteiler['desc'] . '</p>'), $this->func->s('desc'), array('class' => 'ui-padding'));
	}

	public function listFairteiler($bezirke)
	{
		$content = '';
		$count = 0;
		foreach ($bezirke as $bezirk) {
			$out = '
			<ul class="linklist fairteilerlist">';
			foreach ($bezirk['fairteiler'] as $ft) {
				++$count;
				$image = '<span class="image noimage ui-corner-all" style="background-image:url(img/fairteiler_thumb.png);"></span>';
				if ($ft['pic']) {
					$image = '<span class="image ui-corner-all" style="background-image:url(' . $ft['pic']['thumb'] . ');"></span>';
				}
				$out .= '
					<li>
						<a href="/?page=fairteiler&bid=' . $bezirk['id'] . '&sub=ft&id=' . $ft['id'] . '">
							' . $image . '
							<span class="name">' . $ft['name'] . '</span>
							<span class="clear"></span>
						</a>
					</li>';
			}
			$out .= '
				</ul>';

			$content .= $this->v_utils->v_field($out, count($bezirk['fairteiler']) . ' Fair-Teiler in ' . $bezirk['name']);
		}

		if ($this->bezirk_id > 0) {
			$this->func->addContent($this->topbar($this->func->sv('list_fairteiler', $this->bezirk['name']), 'Es gibt ' . $count . ' Fair-Teiler in ' . $this->bezirk['name'] . ' und allen Unterbezirken', '<img src="img/fairteiler_thumb.png" />'), CNT_TOP);
		} else {
			$this->func->addContent($this->topbar($this->func->s('your_fairteiler'), 'Es gibt ' . $count . ' Fair-Teiler in allen Bezirken in denen Du aktiv bist', '<img src="img/fairteiler_thumb.png" />'), CNT_TOP);
		}

		return $content;
	}

	public function ftOptions($bezirk_id)
	{
		$items = array();
		if ($this->func->isBotFor($bezirk_id) || $this->func->isOrgaTeam()) {
			$items[] = array('name' => 'Fair-Teiler eintragen', 'href' => '/?page=fairteiler&bid=' . (int)$bezirk_id . '&sub=addFt');
		} else {
			$items[] = array('name' => 'Fair-Teiler vorschlagen', 'href' => '/?page=fairteiler&bid=' . (int)$bezirk_id . '&sub=addFt');
		}

		return $this->v_utils->v_menu($items, 'Optionen');
	}
}
