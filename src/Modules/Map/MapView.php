<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Modules\Core\View;

class MapView extends View
{
	public function lMap()
	{
		$this->pageHelper->addHidden('
			<div id="b_content" class="loading">
				<div class="inner">
					' . $this->v_utils->v_input_wrapper($this->translator->trans('status'), $this->translator->trans('map.donates'), 'bcntstatus') . '
					' . $this->v_utils->v_input_wrapper($this->translator->trans('storeview.managers'), '...', 'bcntverantwortlich') . '
					' . $this->v_utils->v_input_wrapper($this->translator->trans('specials'), '...', 'bcntspecial') . '
				</div>
				<input type="hidden" class="fetchbtn" name="fetchbtn" value="' . $this->translator->trans('storeview.want_to_fetch') . '" />
			</div>
		');

		return '<div id="map"></div>';
	}

	public function mapControl()
	{
		$betriebe = '';

		if ($this->session->may('fs')) {
			$betriebe = '<li><a name="betriebe" class="ui-corner-all betriebe"><span class="fa-stack fa-lg" style="color: #9E3235"><i class="fas fa-circle fa-stack-2x"></i><i class="fas fa-shopping-cart fa-stack-1x fa-inverse"></i></span><span>' . $this->translator->trans('menu.entry.stores') . '</span></a>
				<div id="map-options">
					<label><input type="checkbox" name="viewopt[]" value="allebetriebe" /> ' . $this->translator->trans('store.bread') . '</label>
					<label><input checked="checked" type="checkbox" name="viewopt[]" value="needhelp" /> ' . $this->translator->trans('menu.entry.helpwanted') . '</label>
					<label><input checked="checked" type="checkbox" name="viewopt[]" value="needhelpinstant" /> ' . $this->translator->trans('menu.entry.helpneeded') . '</label>
					<label><input type="checkbox" name="viewopt" value="nkoorp" /> ' . $this->translator->trans('menu.entry.in_negotiation') . '</label>
				</div>
			</li>';
		}

		return '
			<div id="map-control-wrapper">
				<div class="ui-dialog ui-widget ui-widget-content ui-corner-all" tabindex="-1">
					<div class="ui-dialog-content ui-widget-content">
						<div id="map-control">
							<ul class="linklist">
								<li><a name="baskets" class="ui-corner-all baskets"><span class="fa-stack fa-lg" style="color: #72B026"><i class="fas fa-circle fa-stack-2x"></i><i class="fas fa-shopping-basket fa-stack-1x fa-inverse"></i></span><span>' . $this->translator->trans('terminology.baskets') . '</span></a></li>
								' . $betriebe . '
								<li><a name="fairteiler" class="ui-corner-all fairteiler"><span class="fa-stack fa-lg" style="color: #FFCA92"><i class="fas fa-circle fa-stack-2x"></i><i class="fas fa-recycle fa-stack-1x fa-inverse"></i></span><span>' . $this->translator->trans('terminology.fsp') . '</span></a></li>
								<li><a name="communities" class="ui-corner-all communities"><span class="fa-stack fa-lg" style="color: cornflowerblue"><i class="fas fa-circle fa-stack-2x"></i><i class="fas fa-users fa-stack-1x fa-inverse"></i></span><span>' . $this->translator->trans('menu.entry.regionalgroups') . '</span></a></li>
							</ul>
						</div>
					</div>
				</div>

			</div>';
	}
}
