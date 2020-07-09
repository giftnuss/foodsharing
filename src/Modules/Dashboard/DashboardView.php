<?php

namespace Foodsharing\Modules\Dashboard;

use Foodsharing\Modules\Core\View;

class DashboardView extends View
{
	public function newBaskets($baskets)
	{
		$out = '<ul class="linklist baskets">';
		foreach ($baskets as $b) {
			$out .= '
			<li>
				<a onclick="ajreq(\'bubble\',{app:\'basket\',id:' . (int)$b['id'] . '});return false;" href="#" class="corner-all">
					<span class="i">' . $this->img($b) . '</span>
					<span class="n">Essenskorb von ' . $b['fs_name'] . '</span>
					<span class="t">veröffentlicht: ' . $this->timeHelper->niceDate($b['time_ts']) . '</span>
					<span class="d">' . $b['description'] . '</span>
					<span class="c"></span>
				</a>

			</li>';
		}

		$out .= '
				</ul>';

		return $this->v_utils->v_field($out, $this->translationHelper->s('new_foodbaskets'));
	}

	public function updates()
	{
		$this->pageHelper->addContent($this->vueComponent('activity-overview', 'activity-overview', []));
	}

	public function foodsharerMenu()
	{
		return $this->menu([
			['name' => $this->translationHelper->s('new_basket'), 'click' => "ajreq('newBasket',{app:'basket'});return false;"],
			['name' => $this->translationHelper->s('all_baskets'), 'href' => '/karte?load=baskets']
		]);
	}

	public function nearbyBaskets($baskets)
	{
		$out = '<ul class="linklist baskets">';
		foreach ($baskets as $b) {
			$out .= '
			<li>
				<a onclick="ajreq(\'bubble\',{app:\'basket\',id:' . (int)$b['id'] . '});return false;" href="#" class="corner-all">
					<span class="i">' . $this->img($b) . '</span>
					<span class="n">Essenskorb von ' . $b['fs_name'] . ' (' . $this->distance($b['distance']) . ')</span>
					<span class="t">' . $this->timeHelper->niceDate($b['time_ts']) . '</span>
					<span class="d">' . $b['description'] . '</span>
					<span class="c"></span>
				</a>

			</li>';
		}

		$out .= '
				</ul>';

		return $this->v_utils->v_field($out, $this->translationHelper->s('close_foodbaskets'));
	}

	private function img($basket)
	{
		if ($basket['picture'] != '' && file_exists(ROOT_DIR . 'images/basket/50x50-' . $basket['picture'])) {
			return '<img src="/images/basket/thumb-' . $basket['picture'] . '" height="50" />';
		}

		return '<img src="/img/basket50x50.png" height="50" />';
	}

	public function becomeFoodsaver()
	{
		return '
	   <div class="msg-inside info">
			   <i class="fas fa-info-circle"></i> <strong><a href="/?page=settings&sub=upgrade/up_fs">Ich möchte jetzt das Foodsaver-Quiz machen und Foodsaver werden!</a></strong>
	   </div>';
	}

	public function u_nextDates($dates)
	{
		$out = '
		<div class="ui-padding">
			<ul class="datelist linklist">';
		foreach ($dates as $d) {
			$confirmSymbol = $d['confirmed'] == 1 ? '✓ ' : '? ';
			$out .= '
				<li>
					<a href="/?page=fsbetrieb&id=' . $d['betrieb_id'] . '" class="ui-corner-all">
						<span class="title">' . $confirmSymbol . $this->timeHelper->niceDate($d['date_ts']) . '</span>
						<span>' . $d['betrieb_name'] . '</span>
					</a>
				</li>';
		}
		$out .= '
			</ul>
		</div>';

		return $this->v_utils->v_field($out, $this->translationHelper->s('next_dates'), ['class' => 'truncate-content truncate-height-150 collapse-mobile']);
	}

	public function u_myBetriebe($betriebe)
	{
		$out = '';
		$out .= $this->u_storeLinkList(
			$betriebe['verantwortlich'],
			$this->translator->trans('dashboard.you_are_responsible_for_stores'),
			'truncate-height-85'
		);

		$out .= $this->u_storeLinkList(
			$betriebe['team'],
			$this->translator->trans('dashboard.you_pickup_at_stores'),
			'truncate-height-140'
		);

		$out .= $this->u_storeLinkList(
			$betriebe['waitspringer'],
			$this->translator->trans('dashboard.you_wait_at_stores'),
			'truncate-height-85'
		);

		$out .= $this->u_storeLinkList(
			$betriebe['requested'],
			$this->translator->trans('dashboard.you_requested_to_join'),
			'truncate-height-50'
		);

		if (empty($out)) {
			$out = $this->v_utils->v_info(
				$this->translator->trans('dashboard.no_store_team')
			);
		}

		return $out;
	}

	private function u_storeLinkList($storeList, $title, $classes = ''): string
	{
		if (empty($storeList)) {
			return '';
		}
		$list = '<ul class="linklist">';
		foreach ($storeList as $store) {
			$list .=
			'<li>' .
				'<a class="ui-corner-all" href="/?page=fsbetrieb&id=' . $store['id'] . '">' .
					$store['name'] .
				'</a>' .
			'</li>';
		}
		$list .= '</ul>';

		return $this->v_utils->v_field(
			$list,
			$title,
			['class' => 'ui-padding collapse-mobile truncate-content ' . $classes]
		);
	}

	public function u_invites($invites)
	{
		$this->pageHelper->addStyle('
			@media (max-width: 410px)
			{
				.top_margin_on_small_screen
				{
					margin-top: 45px;
				}
			}
		');

		$out = '';
		foreach ($invites as $i) {
			$out .= '
			<div class="post event">
				<a href="/?page=event&id=' . (int)$i['id'] . '" class="calendar">
					<span class="month">' . $this->translationHelper->s('month_' . (int)date('m', $i['start_ts'])) . '</span>
					<span class="day">' . date('d', $i['start_ts']) . '</span>
				</a>


				<div class="container activity_feed_content">
					<div class="activity_feed_content_text">
						<div class="activity_feed_content_info">
							<p><a href="/?page=event&id=' . (int)$i['id'] . '">' . $i['name'] . '</a></p>
							<p>' . $this->timeHelper->niceDate($i['start_ts']) . '</p>
						</div>
					</div>

					<div class="row activity-feed-content-buttons">
						<div class="col mr-2"><a href="#" onclick="ajreq(\'accept\',{app:\'event\',id:\'' . (int)$i['id'] . '\'});return false;" class="button">Einladung annehmen</a></div>
						<div class="col-md-auto mr-2"><a href="#" onclick="ajreq(\'maybe\',{app:\'event\',id:\'' . (int)$i['id'] . '\'});return false;" class="button">Vielleicht</a></div>
						<div class="col-md-auto"><a href="#" onclick="ajreq(\'noaccept\',{app:\'event\',id:\'' . (int)$i['id'] . '\'});return false;" class="button">Nein</a></div>
					</div>
				</div>

				<div class="clear"></div>
			</div>
			';
		}

		return $this->v_utils->v_field($out, $this->translationHelper->s('you_were_invited'), ['class' => 'ui-padding truncate-content collapse-mobile']);
	}

	public function u_events($events)
	{
		$out = '';
		foreach ($events as $i) {
			$out .= '
			<div class="post event">
				<a href="/?page=event&id=' . (int)$i['id'] . '" class="calendar">
					<span class="month">' . $this->translationHelper->s('month_' . (int)date('m', $i['start_ts'])) . '</span>
					<span class="day">' . date('d', $i['start_ts']) . '</span>
				</a>

				<div class="activity_feed_content">
					<div class="activity_feed_content_text">
						<div class="activity_feed_content_info">
							<p><a href="/?page=event&id=' . (int)$i['id'] . '">' . $i['name'] . '</a></p>
							<p>' . $this->timeHelper->niceDate($i['start_ts']) . '</p>
						</div>
					</div>

					<div>
						<a href="/?page=event&id=' . (int)$i['id'] . '" class="button">Zum Event</a>
					</div>
				</div>

				<div class="clear"></div>
			</div>
			';
		}

		if (count($events) > 1) {
			$eventTitle = $this->translationHelper->s('events_headline') . ' (' . count($events) . ')';
		} else {
			$eventTitle = $this->translationHelper->s('event_headline');
		}

		return $this->v_utils->v_field($out, $eventTitle, ['class' => 'ui-padding truncate-content']);
	}
}
