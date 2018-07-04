<?php

use Foodsharing\DI;
use Foodsharing\Lib\Func;
use Foodsharing\Lib\Session\S;

/* @var $func Func */
$func = DI::$shared->get(Func::class);

$user = '';
if (S::may()) {
	$user = 'user = {id:' . $func->fsId() . '};';
}

$func->addJs('
	' . $user . '
	$("#mainMenu > li > a").each(function(){
		if(parseInt(this.href.length) > 2 && this.href.indexOf("' . $func->getPage() . '") > 0)
		{
			$(this).parent().addClass("active").click(function(ev){
				//ev.preventDefault();
			});
		}
	});
		
	$("#fs-profile-rate-comment").dialog({
		modal: true,
		title: "",
		autoOpen: false,
		buttons: 
		[
			{
				text: "Abbrechen",
				click: function(){
					$("#fs-profile-rate-comment").dialog("close");
				}
			},
			{
				text: "Absenden",
				click: function(){
					ajreq("rate",{app:"profile",type:2,id:$("#profile-rate-id").val(),message:$("#fsprofileratemsg").val()});
				}
			}
		]
	}).siblings(".ui-dialog-titlebar").remove();
');

if (!S::may()) {
	$func->addJs('clearInterval(g_interval_newBasket);');
} else {
	$func->addJs('
		sock.connect();
		user.token = "' . S::user('token') . '";
		info.init();
	');
}
/*
 * Browser location abfrage nur einmal dann in session speichern
 */
if ($pos = S::get('blocation')) {
	$func->addJsFunc('
		function getBrowserLocation(success)
		{
			success({
				lat:' . floatval($pos['lat']) . ',
				lon:' . floatval($pos['lon']) . '
			});
		}
	');
} else {
	$func->addJsFunc('
		function getBrowserLocation(success)
		{
			if(navigator.geolocation)
			{
				navigator.geolocation.getCurrentPosition(function(pos){
					ajreq("savebpos",{app:"map",lat:pos.coords.latitude,lon:pos.coords.longitude});
					success({
						lat: pos.coords.latitude,
						lon: pos.coords.longitude
					});
				});
			}
		}
	');
}
