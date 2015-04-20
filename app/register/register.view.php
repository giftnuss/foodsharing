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
		$table = v_tablesorter($headline,$rows,array());
		
		
		$page = new vPage("Liste der Anmeldungen", v_form('event-registration-list',array("$table")));
		$page->addSection("Anzahl der Personen pro Tag: ".implode(', ',$days));
		$page->addSection("Anzahl der Personen pro Nacht: ".implode(', ',$nights));
		$page->render();
	}

	public function workshop_confirmation_matrix($uws, $workshops)
	{
	    addStyle('.fixed-table-container {
	            width:2500px;
      height: 400px;
      border: 1px solid black;
      margin: 10px auto;
      background-color: white;
      /* above is decorative or flexible */
      position: relative; /* could be absolute or relative */
      padding-top: 150px; /* height of header */
    }

    .fixed-table-container-inner {
      overflow-x: hidden;
      overflow-y: auto;
      height: 100%;
    }
	  .th-inner {
      position: absolute;
      top: 0;
      line-height: 20px; /* height of header */
      text-align: left;
      border-left: 1px solid black;
      padding-left: 5px;
      margin-left: -5px;
	            background-color: #000;
	            width:50px;
    }
	                td {
      border-bottom: 1px solid #ccc;
      padding: 5px;
      text-align: left; /* IE */
    }
    td + td {
      border-left: 1px solid #ccc;
    }
    th {
      padding: 0 5px;
      text-align: left; /* IE */
    }
	            #table.tablesorter td,th{border: 1px solid black;padding:0px; text-align:center; font-size:8pt;word-wrap:break-word;min-width:30px;max-width:30px;width:30px;} thead{display:table-header-group}');
	    $headline = array();
		$headline[] = array('name' => "Name");
		$col_to_wid = array();
		$col = 1;
		foreach($workshops as $workshop)
		{
			$headline[] = array('name' => '<div class="th-inner"><span>'.mb_substr($workshop['name'], 0, 20)."<br />(".niceDateShort($workshop['start'])."<br />(".$workshop['attendants']."/".$workshop['registrations']."/".$workshop['allowed_attendants'].")</span></div>",
			);
			$col_to_wid[$col] = $workshop['id'];
			$col++;
		}
		$rows = array();
		foreach($uws as $uw)
		{
			$temp_row = array();
			$workshops = explode(',', $uw['wids']);
			$confirmed = explode(',', $uw['confirmed']);
			$temp_row[] = array('cnt' => $uw['name']);
			for($i = 1; $i < $col; $i++)
			{
				$wid = $col_to_wid[$i];
				$wish = array_keys($workshops, $wid);
				if($wish) {
    				if($confirmed[$wish[0]]) {
    					$confirmlink = '<a href="/?page=register&list&workshops&confirmuid='.$uw['uid'].'&wid='.$wid.'&confirm=0" style="color:green">'.implode($wish, ',').'</a>';
    				} else {
    					$confirmlink = '<a href="/?page=register&list&workshops&confirmuid='.$uw['uid'].'&wid='.$wid.'&confirm=1" style="color:red">('.implode($wish, ',').')</a>';
    				}
					$text = $confirmlink;
				} else {
					$text = "";
				}
				$temp_row[] = array('cnt' => $text);
			}
			$rows[] = $temp_row;
		}
		$table = v_tablesorter($headline,$rows,array());
		$page = new vPage("Workshop Anmeldungen", '<div class="fixed-table-container"><div class="fixed-table-container-inner">'.$table.'</div></div>');
		$page->render();
	}
	
	public function workshopSignup($workshops, $infotext, $lang)
	{
	    $option = array();
	    foreach($workshops as $w)
	    {
	        if($lang == 'de')
	        {
	           $option[] = array('id' => $w['id'], 'name' => date("D d.m. H:i", $w['start'])." (".$w['duration']." min): ".htmlentities($w['name']));
	        } elseif($lang == 'en')
	        {
	            $option[] = array('id' => $w['id'], 'name' => date("D d.m. h:i a", $w['start'])." (".$w['duration']." min): ".htmlentities($w['name_en']));
	        }
	    }
	    $form = v_form('register_workshop', array(
	            v_form_select('wish1', array('values' => $option)),
	            v_form_select('wish2', array('values' => $option)),
	            v_form_select('wish3', array('values' => $option))
	    ));
	    $body = str_replace('{{FORM}}', $form, $infotext['body']);
	    $page = new vPage($infotext['title'], $body);
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
			v_form_textarea('comments'),
		    v_form_checkbox('available_thursday',array(
		        'values'=>array(
		        array('id' => 0,'name'=>s('yes')))))

		),array('submit'=>s($form_name)));
	}
}
