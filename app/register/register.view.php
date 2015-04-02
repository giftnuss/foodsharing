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
	    global $g_data;
		$out = '';

		addStyle('#table td{ cursor:pointer; }');

		$headline = array();
		$h = array_keys($registrations[0]);
		foreach($h as $col) {
			$headline[] = array('name' => $col);
		}
		$days = array(0, 0, 0, 0);
		$nights = array(0, 0, 0, 0); 

		$rows = array();
		foreach($registrations as $r)
		{
			$temp_row = array();
			foreach($r as $col => $row)
			{
				if($col == "on_place") {
					$cnt = v_form_checkbox('on_place'.$r['id'],array(
					'values'=>array(array('id' => 0,'name'=>s('Seen'))),
					'checked' => ($row > 0) ? array(0) : False,
					'nolabel' => True
					));
				} elseif($col == "admin_comment") {
				    $g_data['admin_comment'.$r['id']] = htmlentities($row);
				    $cnt = v_form_textarea('admin_comment'.$r['id'],array(
				            'nolabel' => True,
				            'style'=>'width:300px;'
				    ));
				} else {
				    if($col == 'take_part')
				    {
				        foreach(explode(',', $row) as $day){
				            if(array_key_exists($day, $days))
				            {
				                $days[$day] ++;
				            }
				        }
				    }
				    if($col == 'sleep_at')
				    {
				        foreach(explode(',', $row) as $day){
				            if(array_key_exists($day, $days))
				            {
				                $nights[$day] ++;
				            }
				        }
				    }
				    $cnt = htmlentities($row);
				}
				$temp_row[] = array('cnt' => $cnt
				);
			}
			$rows[] = $temp_row;
		}
		$table = v_tablesorter($headline,$rows,array('pager'=>false));
		
		
		$page = new vPage("Liste der Anmeldungen", v_form('',array("$table")));
		$page->addSection("Anzahl der Personen pro Tag: ".implode(', ',$days));
		$page->addSection("Anzahl der Personen pro Nacht: ".implode(', ',$nights));
		$page->render();
	}

	public function workshop_confirmation_matrix($uws, $workshops)
	{
		$headline = array("Name");
		$col_to_wid = array();
		$col = 1;
		foreach($workshops as $workshop)
		{
			$headline[] = array('name' => substr($workshop['name'], 12)."<br />(".$workshop['attendants']."/".$workshop['registrations']."/".$workshop['allowed_attendants'].")");
			$col_to_wid[$col] = $workshop['id'];
		}
		$rows = array();
		foreach($uws as $uw)
		{
			$temp_row = array();
			$workshops = explode($uw['wids']);
			$confirmed = explode($uw['confirmed']);
			$temp_row[] = array('cnt' => $uw['name']);
			for($i = 1; $i < $col; $i++)
			{
				$wid = $col_to_wid[$col];
				$wish = array_search($wid, $workshops);
				if($confirmed[$wish]) {
					$confirmlink = '<a href="/?page=register&confirmuid='.$uw['uid'].'&wid='.$wid.'&confirm=0" style="color:green">'.$wish.'</a>';
				} else {
					$confirmlink = '<a href="/?page=register&confirmuid='.$uw['uid'].'&wid='.$wid.'&confirm=1" style="color:red">('.$wish.')</a>';
				}
				if($wish !== false) {
					$text = $confirmlink;
				} else {
					$text = "";
				}
				$temp_row[] = array('cnt' => $text);
			}
			$rows[] = $temp_row;
		}
		$table = v_tablesorter($headline,$rows,array('pager'=>false));

		return $table;
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
