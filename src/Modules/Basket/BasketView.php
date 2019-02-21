<?php

namespace Foodsharing\Modules\Basket;

use Foodsharing\Lib\View\vMap;
use Foodsharing\Lib\View\vPage;
use Foodsharing\Modules\Core\View;

class BasketView extends View
{
	public function find($baskets, $location)
	{
		$page = new vPage($this->func->s('baskets'), $this->findMap($location));

		if ($baskets) {
			$page->addSectionRight($this->closeBaskets($baskets), $this->func->s('basket_near'));
		}

		$page->render();
	}

	private function findMap($location)
	{
		$map = new vMap($location);

		if (is_array($location)) {
			$map->setCenter($location['lat'], $location['lon']);
		}

		$map->setSearchPanel('mapsearch');
		$map->setMarkerCluster();
		$map->setDefaultMarkerOptions('basket', 'green');

		return '<div class="ui-widget"><input id="mapsearch" type="text" name="mapsearch" value="" placeholder="Adresssuche..." class="input text value ui-corner-top"/><div class="findmap">' . $map->render(
			) . '</div></div>';
	}

	public function closeBaskets($baskets)
	{
		$out = '
		<ul class="linklist" id="cbasketlist">';
		foreach ($baskets as $b) {
			$img = '/img/basket.png';
			if (!empty($b['picture'])) {
				$img = '/images/basket/thumb-' . $b['picture'];
			}

			$distance = $this->distance($b['distance']);

			$out .= '
				<li>
					<a class="ui-corner-all" onclick="ajreq(\'bubble\',{app:\'basket\',id:' . (int)$b['id'] . ',modal:1});return false;" href="#">
						<span style="float:left;margin-right:7px;"><img width="35px" src="' . $img . '" class="ui-corner-all"></span>
						<span style="height:35px;overflow:hidden;font-size:11px;line-height:16px;"><strong style="float:right;margin:0 0 0 3px;">(' . $distance . ')</strong>' . $this->sanitizerService->tt(
					$b['description'],
					50
				) . '</span>
						
						<span style="clear:both;"></span>
					</a>
				</li>';
		}
		$out .= '
		</ul>
		<div style="text-align:center;">
			<a class="button" href="/karte?load=baskets">' . $this->func->s('basket_on_map') . '</a>
		</div>';

		return $out;
	}

	public function basket($basket, $wallposts, $requests)
	{
		$page = new vPage(
			$this->func->s('basket') . ' #' . $basket['id'], '
		
		<div class="pure-g">
		    <div class="pure-u-1 pure-u-md-1-3">
				' . $this->pageImg($basket['picture']) . '	
			</div>
		    <div class="pure-u-1 pure-u-md-2-3">
				<p>' . nl2br($basket['description']) . '</p>
			</div>
		</div>
		'
		);

		$page->setSubTitle($this->getSubtitle($basket));

		if ($wallposts) {
			$page->addSection($wallposts, $this->func->s('wallboard'));
		}
		if ($this->session->may()) {
			$page->addSectionRight($this->userBox($basket), $this->func->s('provider'));

			if ($basket['lat'] != 0 || $basket['lon'] != 0) {
				$map = new vMap([$basket['lat'], $basket['lon']]);
				$map->addMarker($basket['lat'], $basket['lon']);

				$map->setDefaultMarkerOptions('basket', 'green');

				$map->setCenter($basket['lat'], $basket['lon']);

				$page->addSectionRight($map->render(), 'Wo?');
			}

			if ($basket['fs_id'] == $this->session->id() && $requests) {
				$page->addSectionRight($this->requests($requests), $this->func->sv('req_count', array('count' => count($requests))));
			}
		} else {
			$page->addSectionRight(
				$this->v_utils->v_info($this->func->s('basket_detail_login_hint'), $this->func->s('reference')),
				false,
				array('wrapper' => false)
			);
		}

		$page->render();
	}

	public function basketTaken($basket)
	{
		$page = new vPage(
			$this->func->s('basket') . ' #' . $basket['id'], '
		
		<div class="pure-g">
		    <div class="pure-u-1 pure-u-md-2-3">
				<p>' . $this->func->s('basket_picked_up') . '</p>
			</div>
		</div>
		'
		);
		$page->render();
	}

	public function requests($requests)
	{
		$out = '
		<ul class="linklist conversation-list">';

		foreach ($requests as $r) {
			$out .= '
			<li><a onclick="chat(' . (int)$r['fs_id'] . ');return false;" href="#"><span class="pics"><img width="50" alt="avatar" src="' . $this->func->img(
					$r['fs_photo']
				) . '"></span><span class="names">' . $r['fs_name'] . '</span><span class="msg"></span><span class="time">' . $this->func->niceDate(
					$r['time_ts']
				) . '</span><span class="clear"></span></a></li>';
		}

		$out .= '
		</ul>';

		return $out;
	}

	private function getSubtitle($basket)
	{
		$subtitle = '<p>' . $this->func->s('create_at') . ' <strong>' . $this->func->niceDate(
				$basket['time_ts']
			) . '</strong>';

		$subtitle .= '</p><p>' . $this->func->s('until') . ' <strong>' . $this->func->niceDate($basket['until_ts']) . '</strong></p>';
		if ($basket['update_ts']) {
			$subtitle .= '<p>' . $this->func->s('update_at') . ' <strong>' . $this->func->niceDate($basket['update_ts']) . '</strong></p>';
		}

		return $subtitle;
	}

	private function userBox($basket)
	{
		if ($basket['fs_id'] != $this->session->id()) {
			$request = '<div><a class="button button-big" href="#" onclick="ajreq(\'request\',{app:\'basket\',id:' . (int)$basket['id'] . '});">' . $this->func->s('basket_request') . '</a>	</div>';
		} else {
			$request = '
				<div class="ui-padding-bottom">
					<a class="button button-big" href="#" onclick="ajreq(\'editBasket\',{app:\'basket\',id:' . (int)$basket['id'] . '});">' . $this->func->s('basket_edit') . '</a>
				</div><div>
					<a class="button button-big" href="#" onclick="ajreq(\'removeBasket\',{app:\'basket\',id:' . (int)$basket['id'] . '});">' . $this->func->s('basket_delete') . '</a>
				</div>';
		}

		return $this->fsAvatarList(
				array(
					array(
						'id' => $basket['fs_id'],
						'name' => $basket['fs_name'],
						'photo' => $basket['fs_photo'],
						'sleep_status' => $basket['sleep_status'],
					),
				),
				array('height' => 600, 'scroller' => false)
			) .
			$request;
	}

	private function pageImg($img): string
	{
		if ($img != '') {
			return '<img class="basket-img" src="/images/basket/medium-' . $img . '" />';
		}

		return '<img class="basket-img" src="/img/foodloob.gif" />';
	}

	public function basketForm($foodsaver): string
	{
		$out = '';

		$out .= $this->v_utils->v_form_textarea('description', array('maxlength' => 1705));

		$values = [
			['id' => 0.25, 'name' => '250 g'],
			['id' => 0.5, 'name' => '500 g'],
			['id' => 0.75, 'name' => '750 g'],
		];

		for ($i = 1; $i <= 10; ++$i) {
			$values[] = [
				'id' => $i,
				'name' => number_format($i, 2, ',', '.') . '<span style="white-space:nowrap">&thinsp;</span>kg',
			];
		}

		for ($i = 2; $i <= 10; ++$i) {
			$val = ($i * 10);
			$values[] = [
				'id' => $val,
				'name' => number_format($val, 2, ',', '.') . '<span style="white-space:nowrap">&thinsp;</span>kg',
			];
		}

		$out .= $this->v_utils->v_form_select(
			'weight',
			[
				'values' => $values,
				'selected' => 3,
			]
		);

		$out .= $this->v_utils->v_form_checkbox(
			'contact_type',
			[
				'values' => [
					['id' => 1, 'name' => 'Per Nachricht'],
					['id' => 2, 'name' => 'Per Telefonanruf'],
				],
				'checked' => [1],
			]
		);

		$out .= $this->v_utils->v_form_text('tel', ['value' => $foodsaver['telefon']]);
		$out .= $this->v_utils->v_form_text('handy', ['value' => $foodsaver['handy']]);

		$lifetimeNames = $this->func->sv('lifetime_options', array());
		$out .= $this->v_utils->v_form_select(
			'lifetime',
			[
				'values' => [
					['id' => 1, 'name' => $lifetimeNames[0]],
					['id' => 2, 'name' => $lifetimeNames[1]],
					['id' => 3, 'name' => $lifetimeNames[2]],
					['id' => 7, 'name' => $lifetimeNames[3]],
					['id' => 14, 'name' => $lifetimeNames[4]],
					['id' => 21, 'name' => $lifetimeNames[5]]
				],
				'selected' => 7,
			]
		);

		$out .= $this->v_utils->v_form_checkbox(
			'food_type',
			[
				'values' => [
					['id' => 1, 'name' => 'Backwaren'],
					['id' => 2, 'name' => 'Obst & Gemüse'],
					['id' => 3, 'name' => 'Molkereiprodukte'],
					['id' => 4, 'name' => 'Trockenware'],
					['id' => 5, 'name' => 'Tiefkühlware'],
					['id' => 6, 'name' => 'Zubereitete Speisen'],
					['id' => 7, 'name' => 'Tierfutter'],
				],
			]
		);

		$out .= $this->v_utils->v_form_checkbox(
			'food_art',
			[
				'values' => [
					['id' => 1, 'name' => 'sind Bio'],
					['id' => 2, 'name' => 'sind vegetarisch'],
					['id' => 3, 'name' => 'sind vegan'],
					['id' => 4, 'name' => 'sind glutenfrei'],
				],
			]
		);

		return $out;
	}

	public function basketEditForm($basket): string
	{
		$out = '';

		$out .= $this->v_utils->v_form_textarea('description', array('maxlength' => 1705, 'value' => $basket['description']));
		$out .= $this->v_utils->v_form_hidden('basket_id', $basket['id']);

		return $out;
	}

	public function contactMsg(): string
	{
		return $this->v_utils->v_form_textarea('contactmessage');
	}

	public function contactTitle($basket): string
	{
		return '<img src="' . $this->func->img($basket['fs_photo']) . '" style="float:left;margin-right:15px;" />
		<p>' . $this->func->sv('foodsaver_contact', array('name' => $basket['fs_name'])) . '</p>
		<div style="clear:both;"></div>';
	}

	public function contactNumber($basket): string
	{
		$out = '';
		$content = '';
		if (!empty($basket['tel'])) {
			$content .= ('<tr><td>' . $this->func->s('telefon') . ': &nbsp;</td><td>' . $basket['tel'] . '</td></tr>');
		}
		if (!empty($basket['handy'])) {
			$content .= ('<tr><td>' . $this->func->s('handy') . ': &nbsp;</td><td>' . $basket['handy'] . '</td></tr>');
		}
		if (!empty($content)) {
			$out .= $this->v_utils->v_input_wrapper($this->func->s('phone_contact'), '<table>' . $content . '</table>');
		}

		return $out;
	}

	public function fsBubble($basket)
	{
		$img = '';
		if (!empty($basket['picture'])) {
			$img = '<div style="width:100%;max-height:200px;overflow:hidden;"><img src="http://media.myfoodsharing.org/de/items/200/' . $basket['picture'] . '" /></div>';
		}

		return '
		' . $img . '
		' . $this->v_utils->v_input_wrapper($this->func->s('desc'), nl2br($this->func->autolink($basket['description']))) . '
		' .
			'<div style="text-align:center;"><a class="fsbutton" href="' . BASE_URL . '/essenskoerbe/' . $basket['fsf_id'] . '" target="_blank">' . $this->func->s('basket_request_on_page') . '</a></div>';
	}

	public function bubbleNoUser($basket): string
	{
		$img = '';
		if (!empty($basket['picture'])) {
			$img = '<div style="width:100%;overflow:hidden;"><img src="/images/basket/medium-' . $basket['picture'] . '" width="100%" /></div>';
		}

		return '
		' . $img . '
		' . $this->v_utils->v_input_wrapper($this->func->s('desc'), nl2br($this->func->autolink($basket['description']))) . '
		';
	}

	public function bubble($basket): string
	{
		$img = '';
		if (!empty($basket['picture'])) {
			$img = '<div style="width:100%;overflow:hidden;"><img src="/images/basket/medium-' . $basket['picture'] . '" width="100%" /></div>';
		}

		return '
		' . $img . '
		' . $this->v_utils->v_input_wrapper($this->func->s('set_date'), $this->func->niceDate($basket['time_ts'])) . '
		' . $this->v_utils->v_input_wrapper($this->func->s('desc'), nl2br($this->func->autolink($basket['description']))) . '
		';
	}
}
