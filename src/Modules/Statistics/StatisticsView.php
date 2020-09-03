<?php

namespace Foodsharing\Modules\Statistics;

use Foodsharing\Modules\Core\View;

class StatisticsView extends View
{
	public function getStatRegions(array $regions): string
	{
		$out = '<table class="leaderboard stat_regions">';

		$i = 0;
		foreach ($regions as $r) {
			++$i;
			$out .= '
			<tr>
				<td class="rank">
					<h4>' . $i . '.</h4>
				</td>
				<td class="name">
					<h4>' . $r['name'] . '</h4>
					<p class="fetchweight">'
					. number_format($r['fetchweight'], 0, ',', '.')
					. '<span style="white-space: nowrap;">&thinsp;</span>kg '
					. $this->translator->trans('profile.stats.weight')
					. '</p>
					<p class="fetchcount">'
					. number_format($r['fetchcount'], 0, ',', '.')
					. '<span style="white-space: nowrap;">&thinsp;</span>x '
					. $this->translator->trans('profile.stats.count')
					. '</p>
				</td>
			</tr>';
		}
		$out .= '</table>';

		return $this->v_utils->v_field(
			$out,
			$this->translator->trans('stats.leader.regions'),
			['class' => 'ui-padding']
		);
	}

	public function getStatTotal(array $stat, int $foodsharerCount, int $avgDailyFetchCount, int $foodSharePointsCount): string
	{
		return $this->v_utils->v_field('
	<div id="stat_whole">
		<div class="stat_item">
			<div class="stat_badge">
				<div class="stat_icon fetchweight">
					<i class="fas fa-apple-alt"></i>
				</div>
			</div>
			<div class="stat_text">
				<h4>' . number_format($stat['fetchweight'], 0, ',', '.') . '<span style="white-space: nowrap;">&thinsp;</span>kg</h4>
				<p>' . $this->translator->trans('stats.total.weight') . '</p>
			</div>
		</div>
		<div class="stat_item">
			<div class="stat_badge">
				<div class="stat_icon coorpcount">
					<i class="fas fa-store-alt"></i>
				</div>
			</div>
			<div class="stat_text">
				<h4>' . number_format($stat['cooperationscount'], 0, ',', '.') . '</h4>
				<p>' . $this->translator->trans('stats.total.cooperations') . '</p>
			</div>
		</div>
		<br>
		<div class="stat_item">
			<div class="stat_badge">
				<div class="stat_icon fscount">
					<i class="fas fa-user-check", style="margin-left: 30px"></i>
				</div>
			</div>
			<div class="stat_text">
				<h4>' . number_format($stat['fscount'], 0, ',', '.') . '</h4>
				<p>' . $this->translator->trans('stats.total.foodsaver') . '</p>
			</div>
		</div>
		<div class="stat_item">
			<div class="stat_badge">
				<div class="stat_icon fscount2">
					<i class="fas fa-users"></i>
				</div>
			</div>
			<div class="stat_text">
				<h4>' . number_format($foodsharerCount, 0, ',', '.') . '</h4>
				<p>' . $this->translator->trans('stats.total.foodsharer') . '</p>
			</div>
		</div>
		<br>
		<div class="stat_item">
			<div class="stat_badge">
				<div class="stat_icon fetchcount">
					<i class="fas fa-walking"></i>
				</div>
			</div>
			<div class="stat_text">
				<h4>' . number_format($stat['fetchcount'], 0, ',', '.') . '</h4>
				<p>' . $this->translator->trans('stats.total.pickups') . '</p>
			</div>
		</div>
		<div class="stat_item">
			<div class="stat_badge">
				<div class="stat_icon dailyfetchcount">
					<i class="fas fa-people-carry"></i>
				</div>
			</div>
			<div class="stat_text">
				<h4>' . number_format($avgDailyFetchCount, 0, ',', '.') . '</h4>
				<p>' . $this->translator->trans('stats.avg.pickups') . '</p>
			</div>
		</div>
		<br>
		<div class="stat_item">
			<div class="stat_badge">
				<div class="stat_icon totalbaskets">
					<i class="fas fa-shopping-basket"></i>
				</div>
			</div>
			<div class="stat_text">
				<h4>' . number_format($stat['totalBaskets'], 0, ',', '.') . '</h4>
				<p>' . $this->translator->trans('stats.total.baskets') . '</p>
			</div>
		</div>
		<div class="stat_item">
			<div class="stat_badge">
				<div class="stat_icon avgWeeklyBaskets">
					<span class="fa-stack">
						<i class="far fa-calendar fa-stack-2x"></i>
						<i class="fas fa-shopping-basket fa-stack-1x fa-stack-sm"></i>
					</span>
				</div>
			</div>
			<div class="stat_text">
				<h4>' . number_format($stat['avgWeeklyBaskets'], 0, ',', '.') . '</h4>
				<p>' . $this->translator->trans('stats.avg.baskets') . '</p>
			</div>
		</div>
		<br>
		<div class="stat_item">
			<div class="stat_badge">
				<div class="stat_icon fetchweight">
					<i class="fas fa-recycle"></i>
				</div>
			</div>
			<div class="stat_text">
				<h4>' . number_format($foodSharePointsCount, 0, ',', '.') . '</h4>
				<p>' . $this->translator->trans('stats.total.fsp') . '</p>
			</div>
		</div>
	</div>',
			$this->translator->trans('stats.title')
		);
	}

	public function getStatFoodsaver(array $foodsaver): string
	{
		$out = '<table class="leaderboard stat_foodsaver">';

		$i = 0;
		foreach ($foodsaver as $fs) {
			++$i;
			$out .= '
			<tr>
				<td class="rank">
					<h4>' . $i . '.</h4>
				</td>
				<td class="name">
					<h4>' . $fs['name'] . '</h4>
					<p class="fetchweight">'
					. number_format($fs['fetchweight'], 0, ',', '.')
					. '<span style="white-space: nowrap;">&thinsp;</span>kg '
					. $this->translator->trans('profile.stats.weight')
					. '</p>
					<p class="fetchcount">'
					. number_format($fs['fetchcount'], 0, ',', '.')
					. '<span style="white-space: nowrap;">&thinsp;</span>x '
					. $this->translator->trans('profile.stats.count')
					. '</p>
				</td>
			</tr>';
		}
		$out .= '</table>';

		return $this->v_utils->v_field(
			$out,
			$this->translator->trans('stats.leader.users'),
			['class' => 'ui-padding']
		);
	}
}
