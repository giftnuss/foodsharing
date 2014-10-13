<?php
/* Bezirk Index Aufbauen */
//$db->store->flush();

/* Bezirk Statistiken*/


/* foodsaver photos */
if($foodsaver = $db->q('SELECT id, photo FROM '.PREFIX.'foodsaver WHERE photo != ""'))
{
	$update = array();
	foreach ($foodsaver as $fs)
	{
		if(!file_exists('images/'.$fs['photo']))
		{
			$update[] = $fs['id'];
		}
	}
	if(!empty($update))
	{
		$db->update('UPDATE '.PREFIX.'foodsaver SET photo = "" WHERE id IN('.implode(',', $update).')');
	}
}
$check = array();
if($foodsaver = $db->q('SELECT id, photo FROM '.PREFIX.'foodsaver WHERE photo != ""'))
{
	foreach ($foodsaver as $fs)
	{
		$check[$fs['photo']] = $fs['id'];
	}
	$dir = opendir('./images');
	$count = 0;
	while (($file = readdir($dir)) !== false)
	{
		if(strlen($file) > 3 && !is_dir('./images/'.$file))
		{
			$cfile = $file;
			if(strpos($file, '_') !== false)
			{
				$cfile = explode('_', $file);
				$cfile = end($cfile);
			}
			if(!isset($check[$cfile]))
			{
				$count++;
				@unlink('./images/'.$file);
				@unlink('./images/130_q_'.$file);
				@unlink('./images/50_q_'.$file);
				@unlink('./images/med_q_'.$file);
				@unlink('./images/mini_q_'.$file);
				@unlink('./images/thumb_'.$file);
				@unlink('./images/thumb_crop_'.$file);
				@unlink('./images/q_'.$file);
			}
		}
	}
}

/*KG gerettet UPDATE Ende*/
Mem::set('cronjobs_daily_date', date('Y-m-d'));