<?php
addScript('/js/dynatree/jquery.dynatree.js');
addScript('/js/jquery.cookie.js');
addCss('/js/dynatree/skin/ui.dynatree.css');




if($new = $db->getWantNew(getBezirkId()))
{
	
	addJs('
		$("#tree").dynatree();
	');
	
	foreach ($new as $n)
	{
		
		$out .= '
		<li>'.$n['name'].' m&ouml;chte in '.$n['new_bezirk'].' Aktiv werden</li>';
		
	}
}

