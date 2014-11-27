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
}