<?php
class StatView extends View
{
	public function index()
	{
		return v_field('
			<div align="center">
				<span class="button startstat">Statistik Auswertung starten (normal)</span> 
				<span class="button startstat_force">Statistik Auswertung starten (forciert)</span>
			</div>', 'Statistik Tools',array('class'=>'ui-padding')).
			v_field('
			<div align="center">
				<span class="button" onclick="ajreq(\'statBetriebUpdate\');">Betriebe Auswertung starten</span> 
			</div>', 'Betriebe Tools',array('class'=>'ui-padding'));
	}
}