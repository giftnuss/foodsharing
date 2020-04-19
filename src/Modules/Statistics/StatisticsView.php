<?php

namespace Foodsharing\Modules\Statistics;

use Foodsharing\Modules\Core\View;

class StatisticsView extends View
{
	public function getStatCities($cities)
	{
		$out = '
		<table class="stat_cities">
		';

		if (is_array($cities)) {
			$i = 0;
			foreach ($cities as $c) {
				++$i;
				$out .= '
				<tr>
					<td style="width:5px;text-align:right;padding-right:5px;" valign="top">
						<h4>' . $i . '.</h4>
					</td>
					<td class="city">
						<h4>' . $c['name'] . '</h4>
						<p>' . number_format($c['fetchweight'], 0, ',', '.') . '<span style="white-space:nowrap">&thinsp;</span>kg</p>
						<p>' . number_format($c['fetchcount'], 0, ',', '.') . '<span style="white-space:nowrap">&thinsp;</span>x abgeholt</p>
					</td>
				</tr>';
			}
		}
		$out .= '
		</table>';

		return $this->v_utils->v_field($out, $this->translationHelper->s('active_cities'), ['class' => 'ui-padding']);
	}

	public function getStatTotal($stat, int $foodsharerCount, int $avgDailyFetchCount, int $foodSharePointsCount)
	{
		/*
		 *  fetchweight,
		fetchcount,
		cooperationscount,
		botcount,
		fscount,
		fairteilercount
		*/
		return $this->v_utils->v_field('
		<div id="stat_whole">
			<div class="stat_item">
					<div class="stat_badge">
						<div class="stat_icon fetchweight">
							<i class="fas fa-apple-alt"></i>
						</div>
					</div>
					<div class="stat_text">
						<h4>' . number_format($stat['fetchweight'], 0, ',', '.') . '<span style="white-space:nowrap">&thinsp;</span>kg</h4>
						<p>Lebensmittel erfolgreich vor der Tonne gerettet.</p>
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
						<p>Betriebe kooperieren kontinuierlich und zufrieden mit uns.</p>
					</div>
			</div><br />
			<div class="stat_item">
					<div class="stat_badge">
						<div class="stat_icon fscount">
							<i class="fas fa-user-check", style="margin-left: 30px"></i>
						</div>
					</div>
					<div class="stat_text">
						<h4>' . number_format($stat['fscount'], 0, ',', '.') . '</h4>
						<p>Foodsaver engagieren sich ehrenamtlich für eine Welt ohne Lebensmittelverschwendung.</p>
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
						<p>Foodsharer sind derzeit auf foodsharing registriert und interessieren sich für unsere Arbeit.</p>
					</div>
			</div><br />
			<div class="stat_item">
					<div class="stat_badge">
						<div class="stat_icon fetchcount">
							<i class="fas fa-walking"></i>
						</div>
					</div>
					<div class="stat_text">
						<h4>' . number_format($stat['fetchcount'], 0, ',', '.') . '</h4>
						<p>Rettungseinsätze haben unsere Foodsaver gemeistert.</p>
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
						<p>' . $this->translationHelper->s('average_daily_fetches') . '</p>
					</div>
			</div><br />
			<div class="stat_item">
				<div class="stat_badge">
					<div class="stat_icon totalbaskets">
						<i class="fas fa-shopping-basket"></i>
					</div>
				</div>
					<div class="stat_text">
						<h4>' . number_format($stat['totalBaskets'], 0, ',', '.') . '</h4>
						<p>' . $this->translationHelper->s('total_baskets') . '</p>
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
						<p>' . $this->translationHelper->s('average_weekly_baskets') . '</p>
					</div>
			</div><br />
			<div class="stat_item">
					<div class="stat_badge">
						<div class="stat_icon fetchweight">
							<i class="fas fa-recycle"></i>
						</div>
					</div>
					<div class="stat_text">
						<h4>' . number_format($foodSharePointsCount, 0, ',', '.') . '</h4>
						<p>FairTeiler werden zum Tausch von Lebensmitteln genutzt.</p>
					</div>
			</div>
		</div>', $this->translationHelper->s('stat_whole'));
	}

	public function getStatFoodsaver($foodsaver)
	{
		$out = '
		<table class="stat_cities">
		';

		if (is_array($foodsaver)) {
			$i = 0;
			foreach ($foodsaver as $fs) {
				++$i;
				$out .= '
				<tr>
					<td style="width:5px;text-align:right;padding-right:5px;" valign="top">
						<h4>' . $i . '.</h4>
					</td>
					<td class="city">
						<h4>' . $fs['name'] . '</h4>
						<p>' . number_format($fs['fetchweight'], 0, ',', '.') . '<span style="white-space:nowrap">&thinsp;</span>kg</p>
						<p>' . number_format($fs['fetchcount'], 0, ',', '.') . '<span style="white-space:nowrap">&thinsp;</span>x abgeholt</p>
					</td>
				</tr>';
			}
		}
		$out .= '
		</table>';

		return $this->v_utils->v_field($out, $this->translationHelper->s('most_active_foodsavers'), ['class' => 'ui-padding']);
	}
}
