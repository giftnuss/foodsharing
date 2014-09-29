<?php
class MapView extends View
{
	public function map($center)
	{
		return '
			<div class="map-wrapper">
				'.v_clustermap('foodsaver',array('latLng'=>$center)).'
			</div>';
	}
	
	public function lMap($center = false,$zoom = 13)
	{
		//addJs('u_init_map('.$center['lat'].','.$center['lon'].','.$zoom.');');
		
		if(!$center)
		{
			addJs('u_init_map();');
		}
		else
		{
			addJs('u_init_map('.$center['lat'].','.$center['lon'].','.$zoom.');');
		}
		
		addHidden('
			<div id="b_content" class="loading">
				<div class="inner">
					'.v_input_wrapper(s('status'), 'Betrieb spendet','bcntstatus').'
					'.v_input_wrapper('Verantwortliche Foodsaver', '...','bcntverantwortlich').'
					'.v_input_wrapper(s('specials'), '...','bcntspecial').'
				</div>
				<input type="hidden" class="fetchbtn" name="fetchbtn" value="'.s('want_to_fetch').'" />
			</div>
		');
		
		return '<div id="map"></div>';
	}
	
	public function mapControl()
	{
		
		$foodsaver = '';
		$betriebe = '';
		$botschafter = '';
		$additional = '';
		
		if(S::may('fs'))
		{
			$foodsaver = '<li><a name="foodsaver" class="ui-corner-all foodsaver"><span class="icon orange"><i class="img img-smile"></i></span><span>Foodsaver</span><span style="clear:both;float:none;"></span></a></li>';
			$betriebe = '<li><a name="betriebe" class="ui-corner-all betriebe"><span class="icon brown"><i class="img img-store"></i></span><span>Betriebe</span><span style="clear:both;float:none;"></span></a></li>';
			$botschafter = '<li><a name="botschafter" class="ui-corner-all botschafter"><span class="icon red"><i class="img img-smile"></i></span><span>Botschafter</span><span style="clear:both;float:none;"></span></a></li>';
			$additional = '
				<div id="map-option-wrapper" class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front">
					<div class="ui-dialog-content ui-widget-content">
						<div id="map-options">
							<h3>Betrieb Anzeigeoptionen</h3>
							<label><input type="checkbox" name="viewopt[]" value="allebetriebe" /> Alle Betriebe</label>
							<label><input checked="checked" type="checkbox" name="viewopt[]" value="needhelp" /> Helfer gesucht</label>
							<label><input checked="checked" type="checkbox" name="viewopt[]" value="needhelpinstant" /> Helfer dringend gesucht</label>
							<label><input type="checkbox" name="viewopt" value="nkoorp" /> in Verhandlung</label>
						</div>
					</div>
				</div>
			';
		}
		
		return '				
			<div id="map-control-wrapper">
				<div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front" tabindex="-1">
					<div class="ui-dialog-content ui-widget-content" style="width: auto; min-height: 98px; max-height: none; height: auto;">
						<div id="map-control">
							<ul class="linklist">
								<li><a name="baskets" class="ui-corner-all baskets"><span class="icon green"><i class="img img-basket"></i></span><span>Essenskörbe</span><span style="clear:both;float:none;"></span></a></li>
								'.$foodsaver.'
								'.$botschafter.'
								'.$betriebe.'
								
								<li><a name="fairteiler" class="ui-corner-all fairteiler"><span class="icon yellow"><i class="img img-recycle"></i></span><span>Fair-Teiler</span><span style="clear:both;float:none;"></span></a></li>
							</ul>	
						</div>	
					</div>
				</div>
				
				'.$additional.'
				
			</div>';
		/*
		 
		return ('
		
			<div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-draggable ui-resizable" tabindex="-1" role="dialog" aria-describedby="map-control" aria-labelledby="ui-id-4"><div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><span id="ui-id-4" class="ui-dialog-title">'.s('map_control_title').'???</span><button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" aria-disabled="false" title="Schließen"><span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span><span class="ui-button-text">Schließen</span></button></div><div id="map-control" class="ui-dialog-content ui-widget-content" style="width: auto; min-height: 98px; max-height: none; height: auto;">
				<ul class="linklist">
					<li><a class="ui-corner-all foodsaver">Foodsaver</a></li>
					<li><a class="ui-corner-all botschafter">Botschafter</a></li>
					<li><a class="ui-corner-all betriebe">Betriebe</a></li>
					<li><a class="ui-corner-all fairteiler">Fair-Teiler</a></li>
				</ul>
			</div><div class="ui-resizable-handle ui-resizable-n" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-e" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-s" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-w" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-sw" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-ne" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-nw" style="z-index: 90;"></div></div>
		');
		* 
		 */
	}
}