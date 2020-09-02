<?php

namespace Foodsharing\Modules\Dashboard;

use Foodsharing\Modules\Core\View;
use Foodsharing\Modules\Event\InvitationStatus;

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

		return $this->v_utils->v_field($out, $this->translator->trans('basket.recent'));
	}

	public function updates()
	{
		$this->pageHelper->addContent($this->vueComponent('activity-overview', 'activity-overview', []));
	}

	public function foodsharerMenu()
	{
		return $this->menu([
			['name' => $this->translator->trans('basket.new'), 'click' => "ajreq('newBasket',{app:'basket'});return false;"],
			['name' => $this->translator->trans('basket.all_map'), 'href' => '/karte?load=baskets']
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

		return $this->v_utils->v_field($out, $this->translator->trans('basket.nearby'));
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

		return $this->v_utils->v_field($out, $this->translator->trans('dashboard.pickupdates'), [
			'class' => 'truncate-content truncate-height-150 collapse-mobile',
		]);
	}

	public function u_myBetriebe($betriebe)
	{
		$out = '';
		$out .= $this->u_storeLinkList(
			$betriebe['verantwortlich'],
			$this->translator->trans('dashboard.my.managing'),
			'truncate-height-85'
		);

		$out .= $this->u_storeLinkList(
			$betriebe['team'],
			$this->translator->trans('dashboard.my.stores'),
			'truncate-height-140'
		);

		$out .= $this->u_storeLinkList(
			$betriebe['waitspringer'],
			$this->translator->trans('dashboard.my.waiting'),
			'truncate-height-85'
		);

		$out .= $this->u_storeLinkList(
			$betriebe['requested'],
			$this->translator->trans('dashboard.my.pending'),
			'truncate-height-50'
		);

		if (empty($out)) {
			$out = $this->v_utils->v_info(
				$this->translator->trans('dashboard.my.no-stores')
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
			$eventId = intval($i['id']);
			$out .= '
			<div class="post event">
				<a href="/?page=event&id=' . $eventId . '" class="calendar">
					<span class="month">' . $this->timeHelper->month($i['start_ts']) . '</span>
					<span class="day">' . date('d', $i['start_ts']) . '</span>
				</a>

				<div class="container activity_feed_content">
					<div class="activity_feed_content_text">
						<div class="activity_feed_content_info">
							<p><a href="/?page=event&id=' . $eventId . '">' . $i['name'] . '</a></p>
							<p>' . $this->timeHelper->niceDate($i['start_ts']) . '</p>
						</div>
					</div>

					<div class="row activity-feed-content-buttons">
						<div class="col mr-md-1"><a href="#" onclick="'
							. $this->buildEventResponse($eventId, InvitationStatus::ACCEPTED) .
						'" class="button">' . $this->translator->trans('events.button.yes') . '</a></div>
						<div class="col-md-auto mx-1"><a href="#" onclick="'
							. $this->buildEventResponse($eventId, InvitationStatus::MAYBE) .
						'" class="button">' . $this->translator->trans('events.button.maybe') . '</a></div>
						<div class="col-md-auto ml-md-1"><a href="#" onclick="'
							. $this->buildEventResponse($eventId, InvitationStatus::WONT_JOIN) .
						'" class="button">' . $this->translator->trans('events.button.no') . '</a></div>
					</div>
				</div>

				<div class="clear"></div>
			</div>
			';
		}

		return $this->v_utils->v_field($out, $this->translator->trans('dashboard.invitations'), [
			'class' => 'ui-padding truncate-content collapse-mobile',
		]);
	}

	/** TODO Duplicated in EventView right now.
	 * @param int $newStatus  The invitation response (a valid {@see InvitationStatus})
	 */
	private function buildEventResponse(int $eventId, $newStatus): string
	{
		return "ajreq('eventresponse',{app:'event',id:'" . $eventId . "',s:'" . $newStatus . "'});return false;";
	}

	public function u_events($events)
	{
		$out = '';
		foreach ($events as $i) {
			$eventId = intval($i['id']);
			$out .= '
			<div class="post event">
				<a href="/?page=event&id=' . $eventId . '" class="calendar">
					<span class="month">' . $this->timeHelper->month($i['start_ts']) . '</span>
					<span class="day">' . date('d', $i['start_ts']) . '</span>
				</a>

				<div class="activity_feed_content">
					<div class="activity_feed_content_text">
						<div class="activity_feed_content_info">
							<p><a href="/?page=event&id=' . $eventId . '">' . $i['name'] . '</a></p>
							<p>' . $this->timeHelper->niceDate($i['start_ts']) . '</p>
						</div>
					</div>

					<div>
						<a href="/?page=event&id=' . $eventId . '" class="button">'
						. $this->translator->trans('events.goto') .
						'</a>
					</div>
				</div>

				<div class="clear"></div>
			</div>
			';
		}

		if (count($events) > 1) {
			$eventTitle = $this->translator->trans('dashboard.events', ['{count}' => count($events)]);
		} else {
			$eventTitle = $this->translator->trans('dashboard.event');
		}

		return $this->v_utils->v_field($out, $eventTitle, ['class' => 'ui-padding truncate-content']);
	}
}
