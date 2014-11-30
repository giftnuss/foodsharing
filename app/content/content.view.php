<?php
class ContentView extends View
{
	public function partner($cnt)
	{
		return v_field($cnt['body'],$cnt['title'],array('class'=> 'ui-padding'));
	}
	
	public function impressum($cnt)
	{
		return v_field($cnt['body'],$cnt['title'],array('class'=> 'ui-padding'));
	}
	
	public function about($cnt)
	{
		return v_field($cnt['body'],$cnt['title'],array('class'=> 'ui-padding'));
	}
	
	public function faq($faqs)
	{
		$out = '';
		$i = 1;
		foreach ($faqs as $f)
		{
			$out .= '
			<div class="faq ui-padding corner-all">
				<h3>'.$i.'. '.$f['name'].'</h3>
				<p>'.nl2br($f['answer']).'</p>
			</div>';
			$i++;
		}
		
		return $out;
	}
}