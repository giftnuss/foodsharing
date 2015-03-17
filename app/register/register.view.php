<?php
class RegisterView extends View
{
	public function signup($infotext, $edit = False)
	{
		$page = new vPage($infotext['title'],$infotext['body']);
		$page->addSection($this->signup_form($edit));
		$page->render();
	}

	public function signupError($reason, $edit = False, $additional_text = '')
	{
		$page = new vPage(s('error'), v_error(s($reason).$additional_text));
		$page->addSection($this->signup_form($edit));
		$page->render();
	}

	public function signupOkay()
	{
		$page = new vPage(s('thank_you'), s('signup_successful'));
		$page->render();
	}

	public function signupSuccess()
	{
		$page = new vPage(s('thank_you'), s('signup_validated'));
		$page->render();
	}

	public function registrationList($registrations)
	{
		$out = '';

		addStyle('#table td{ cursor:pointer; }');

		addJs('
			$("#table tr").click(function(){
				rid = parseInt($(this).children("td:first").children("input:first").val());
				ajreq("loadreport",{id:rid});
			});		
		');
		$headline = array();
		$h = array_keys($registrations[0]);
		foreach($h as $col) {
			$headline[] = array('name' => $col);
		}

		$rows = array();
		foreach($registrations as $r)
		{
			$temp_row = array();
			foreach($r as $row)
			{
				$temp_row[] = array('cnt' => $row);
			}
			$rows[] = $temp_row;
		}
		$table = v_tablesorter($headline,$rows,array('pager'=>true));
		
		$page = new vPage("Liste der Anmeldungen", $table);
		$page->render();
	}

	public function signup_form($edit)
	{
		global $g_data;
		$role_values=array();
		if(isset($g_data['rolle']))
		{
			$rolle = $g_data['rolle'] + 1;
			if($rolle > 4)
			{
				$rolle = 4;
			}
			$g_data['already_foodsaver'] = $rolle;
		}
		foreach(array('interested','foodsharer','foodsaver','bieb','bot') as $k=>$v)
		{
			$role_values[$k] = array('id' => $k, 'name'=>s($v));
		}
		if($edit)
		{
			$form_name = 'edit_meeting';
		} else {
			$form_name = 'signup_meeting';
		}
		return v_form($form_name,array(
			v_form_text('name', array('required'=>true)),
			v_form_date('geb_datum', array('required'=>true, 'yearRangeFrom' => 1890, 'yearRangeTo' => 2015)),
			v_form_text('ort', array('required'=>true)),
			v_form_text('email', array('required'=>true)),
			v_form_text('phone'),
			v_form_checkbox('take_part',array(
					'values'=>array(
						array('id' => 0,'name'=>s('thursday')),
						array('id' => 1,'name'=>s('friday')),
						array('id' => 2,'name'=>s('saturday')),
						array('id' => 3,'name'=>s('sunday'))
					), 'required' => true
			)),
			v_form_checkbox('sleep_at',array(
					'values'=>array(
						array('id' => 0,'name'=>s('thursday').'-'.s('friday')),
						array('id' => 1,'name'=>s('friday').'-'.s('saturday')),
						array('id' => 2,'name'=>s('saturday').'-'.s('sunday'))
					)
			)),
			v_form_select('sleep_slots',array(
					'values'=>array(
						array('id' => 0,'name'=>'-'),
						array('id' => 1,'name'=>'1','selected'=>true),
						array('id' => 2,'name'=>'2'),
						array('id' => 3,'name'=>'3'),
						array('id' => 4,'name'=>'4'),
						array('id' => 5,'name'=>'5'),
						array('id' => 6,'name'=>'6'),
						array('id' => 7,'name'=>'7'),
						array('id' => 8,'name'=>'8'),
						array('id' => 9,'name'=>'9'),
						array('id' => 10,'name'=>'10'),
						array('id' => 11,'name'=>'11'),
						array('id' => 12,'name'=>'12')
					), 'required'=>true
			)),
			v_form_checkbox('sleep_need',array(
					'values'=>array(
						array('id' => 0,'name'=>s('yes')),
					)
			)),
			v_form_checkbox('languages',array(
					'values'=>array(
						array('id' => 0,'name'=>'Deutsch'),
						array('id' => 1,'name'=>'English'),
						array('id' => 2,'name'=>'Francais'),
						array('id' => 3,'name'=>'Español'),
					)
			)),
			v_form_text('other_languages'),
			v_form_checkbox('languages_translate',array(
					'values'=>array(
						array('id' => 0,'name'=>'Deutsch'),
						array('id' => 1,'name'=>'English'),
						array('id' => 2,'name'=>'Francais'),
						array('id' => 3,'name'=>'Español'),
					)
			)),
			v_form_text('other_languages_translate'),
			v_form_radio('nutrition',array(
				'values'=>array(
				array('id' => 0,'name'=>s('vegan')),
				array('id' => 1,'name'=>s('vegetarian')),
				array('id' => 2,'name'=>s('everything'))
				)
			)),
			v_form_text('special_nutrition'),
			v_form_radio('translation_necessary',array(
				'values'=>array(
				array('id' => 0,'name'=>s('no')),
				array('id' => 1,'name'=>s('yes'))
				)
			)),
			v_form_radio('already_foodsaver',array(
				'values'=>$role_values
			)),
			v_form_textarea('childcare'),
			v_form_textarea('comments')

		),array('submit'=>s($form_name)));
	}
}
