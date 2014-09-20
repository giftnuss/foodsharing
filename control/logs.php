<?php
if(isOrgateam())
{
	addStyle('div#content{width:818px;}div#right{display:none;}');
	$cnt = file_get_contents(ROOT_DIR.'data/logg.txt');
	$cnt = explode('-|||-', $cnt);
	$cnt = array_reverse($cnt);
	addBread('Logs');
	$content = '';
	foreach ($cnt as $c)
	{
		$data = json_decode($c,true);
		$content .= v_field('<pre style="font-size:10px;">'.print_r($data['data'],true)."\n===============================\n".print_r($data['session'],true)."\n===============================\n".print_r($data['add'],true).'</pre>',$data['date']);
	}
}

