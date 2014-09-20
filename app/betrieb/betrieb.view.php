<?php
class BetriebView extends View
{
	public function dateForm()
	{
		return 
		'<div id="datepicker" style="height:195px;"></div>' . 
		v_input_wrapper('Uhrzeit', v_form_time('time')) .
		v_form_select('fetchercount',array('values' => array(
			array('id' => 1, 'name' => '1 Abholer/in'),
			array('id' => 2, 'name' => '2 Abholer/innen'),
			array('id' => 3, 'name' => '3 Abholer/innen'),
			array('id' => 4, 'name' => '4 Abholer/innen'),
			array('id' => 5, 'name' => '5 Abholer/innen'),
			array('id' => 6, 'name' => '6 Abholer/innen'),
			array('id' => 7, 'name' => '7 Abholer/innen'),
			array('id' => 8, 'name' => '8 Abholer/innen')
		)));
	}
}