<?php
class BasketView extends View
{
	public function basketForm($foodsaver)
	{
		global $g_data;
		$g_data['weight'] = '3';
		$g_data['contact_type'] = 1;
		$g_data['tel'] = $foodsaver['telefon'];
		$g_data['handy'] = $foodsaver['handy'];
		
		$out = '';
		
		$out .= v_form_textarea('description',array('maxlength'=>1705));
		
		$values = array(
			array('id' => 0.25, 'name' => '250 g'),
			array('id' => 0.5, 'name' => '500 g'),
			array('id' => 0.75, 'name' => '750 g')
		);
		
		for ($i=1;$i<=10;$i++)
		{
			$values[] = array(
				'id' => $i,
				'name' => number_format($i,2, ",", "."). ' kg'
			);
		}
		
		for ($i=2;$i<=10;$i++)
		{
			$val = ($i*10);
			$values[] = array(
				'id' => $val,
				'name' => number_format($val,2, ",", "."). ' kg'
			);
		}
		
		$out .= v_form_select('weight',array(
			'values' => $values	
		));
		
		$out .= v_form_checkbox('contact_type',array(
			'values' => array(
				array('id' => 1, 'name' => 'Per Nachricht'),
				array('id' => 2, 'name' => 'Per Telefon-Anruf')
			)
		));
		
		$out .= v_form_text('tel');
		$out .= v_form_text('handy');
		
		$out .= v_form_checkbox('food_type',array(
			'values' => array(
				array('id' => 1, 'name' => 'Backwaren'),
				array('id' => 2, 'name' => 'Obst & Gemüse'),
				array('id' => 3, 'name' => 'Molkereiprodukte'),
				array('id' => 4, 'name' => 'Trockenware'),
				array('id' => 5, 'name' => 'Tiefkühlware'),
				array('id' => 6, 'name' => 'Zubreitetete Speisen'),
				array('id' => 7, 'name' => 'Tierfutter')
			)		
		));
		
		$out .= v_form_checkbox('food_art',array(
				'values' => array(
						array('id' => 1, 'name' => 'sind Bio'),
						array('id' => 2, 'name' => 'sind vegetarisch'),
						array('id' => 3, 'name' => 'sind vegan'),
						array('id' => 4, 'name' => 'sind glutenfrei')
				)
		));
		
		return $out;
	}
	
	public function contactMsg($basket)
	{
		return v_form_textarea('contactmessage');
	}
	
	public function contactTitle($basket)
	{
		return '<img src="'.img($basket['fs_photo']).'" style="float:left;margin-right:15px;" />
		<p>'.$basket['fs_name'].' Kontaktieren</p>
		<div style="clear:both;"></div>';
	}
	
	public function contactNumber($basket)
	{		
		
		$out = '';
		$content = '';
		if(!empty($basket['tel']))
		{
			$content .= ('<tr><td>Festnetz: &nbsp;</td><td>'.$basket['tel'].'</td></tr>');
		}
		if(!empty($basket['handy']))
		{
			$content .= ('<tr><td>Handy: &nbsp;</td><td>'.$basket['handy'].'</td></tr>');
		}
		if(!empty($content))
		{
			$out .= v_input_wrapper('Telefonisch kontaktieren', '<table>'.$content.'</table>');
		}
		
		return $out;
	}
	
	public function listUpdates($updates)
	{
		$out = '<li class="header" style="text-align:center;color:#4A3520;padding:10px;">Anfragen</li>';
		foreach ($updates as $u)
		{
			$out .= '<li class="msg msg-'.$u['id'].'-'.$u['fs_id'].'"><a onclick="ajreq(\'answer\',{app:\'basket\',id:'.(int)$u['id'].',fid:'.(int)$u['fs_id'].'});return false;" href="#"><span class="photo"><img src="'.img($u['fs_photo']).'" alt="avatar"></span><span class="subject"><span class="from">Anfrage von '.$u['fs_name'].'</span><span class="time"><button onclick="ajreq(\'removeRequest\',{app:\'basket\',id:'.(int)$u['id'].',fid:'.(int)$u['fs_id'].'});return false;" class="button" title="Anfrage verwerfen"><i class="fa fa-close"></i></button></span></span><span class="message">'.niceDate($u['time_ts']).'</span><span style="display:block;clear:both;"></span></a></li>';
		}
		
		return $out;
	}
	
	public function listMyBaskets($baskets)
	{
		$out = '<li class="header" style="text-align:center;color:#4A3520;padding:10px;">Deine Essenskörbe</li>';
		foreach ($baskets as $b)
		{
			$img = 'img/basket.png';
			if(!empty($b['picture']))
			{
				$img = 'images/basket/thumb-'.$b['picture'];
			}
			$out .= '<li class="msg basket-'.$b['id'].'"><a href="#" onclick="return false;"><span class="photo"><img src="'.$img.'" alt="avatar"></span><span class="subject"><span class="from">'.tt($b['description'],150).'</span><span class="time"><button onclick="ajreq(\'removeBasket\',{app:\'basket\',id:'.(int)$b['id'].'});return false;" class="button" title="Essenskorb entfernen"><i class="fa fa-close"></i></button></span></span><span class="message">'.niceDate($b['time_ts']).'</span><span style="display:block;clear:both;"></span></a></li>';
		}
		
		return $out;
	}
	
	public function fsBubble($basket)
	{
		$img = '';
		if(!empty($basket['picture']))
		{
			$img = '<div style="width:100%;max-height:200px;overflow:hidden;"><img src="http://media.myfoodsharing.org/de/items/200/'.$basket['picture'].'" /></div>';
		}
	
		return '
		'.$img.'
		'.v_input_wrapper('Beschreibung', nl2br(autolink($basket['description']))).'
		' .
		'<div style="text-align:center;"><a class="fsbutton" href="http://foodsharing.de/essenskoerbe/'.$basket['fsf_id'].'" target="_blank">Essenskorb anfragen auf foodsharing.de</a></div>';
	}
	
	public function bubbleNoUser($basket)
	{
		$img = '';
		if(!empty($basket['picture']))
		{
			$img = '<div style="width:100%;height:200px;overflow:hidden;"><img src="/images/basket/'.$basket['picture'].'" width="100%" /></div>';
		}
	
		return '
		'.$img.'
		'.v_input_wrapper('Beschreibung', nl2br(autolink($basket['description']))).'
		';
	}
	
	public function bubble($basket)
	{
		$img = '';
		if(!empty($basket['picture']))
		{
			$img = '<div style="width:100%;height:200px;overflow:hidden;"><img src="/images/basket/'.$basket['picture'].'" width="100%" /></div>';
		}
	
		return '
		'.$img.'
		'.v_input_wrapper('Beschreibung', nl2br(autolink($basket['description']))).'
		';
	}
}