<?php

addStyle('div#content{width:587px;}div#right{display:none;}');
addBread('Suche');

addJs('$("#seachGo").button().click(function(){$("#seachForm").submit();});');

$s_msg = 'Suche nach Namen, Adressen und Betrieben in Deiner Region...';
if(isOrgateam())
{
	$s_msg = 'Suche nach Namen, Bezirken oder Betrieben...';
}

addContent('
	<form method="get" id="seachForm">
		<input type="hidden" name="page" value="suche" />
		<div id="searchBox">
			<input class="search inlabel" type="text" name="q" title="'.$s_msg.'" value="" /><span id="seachGo">Suche</span>
		</div>
		
	</form>');

if(isset($_GET['q']) && strlen($_GET['q']) > 0)
{
	if($res = $db->search($_GET['q']))
	{
		foreach ($res as $key => $r)
		{
			$cnt = '';
			foreach ($r as $erg)
			{
				$cnt .= v_input_wrapper($erg['name'], $erg['teaser'],'search',array('click'=>$erg['click']));
			}
			addContent(v_field($cnt, count($r).' '.s($key).' gefunden',array('class'=>'ui-padding')));
		}
	}
	else
	{
		addContent(v_field(v_info('Die Suche gab leider keine Treffer'), 'Ergebnis',array('class'=>'ui-padding')));
	}
}