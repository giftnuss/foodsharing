<?php

use Foodsharing\DI;
use Foodsharing\Lib\Func;
use Foodsharing\Lib\Session;

/* @var $func Func */
$func = DI::$shared->get(Func::class);

/* @var $session \Foodsharing\Lib\Session */
$session = DI::$shared->get(Session::class);

$user = '';
if ($session->may()) {
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

if (!$session->may()) {
	$func->addJs('clearInterval(g_interval_newBasket);');
} else {
	$func->addJs('
		sock.connect();
		user.token = "' . $session->user('token') . '";
		info.init();
	');
}
/*
 * Browser location abfrage nur einmal dann in session speichern
 */
if ($pos = $session->get('blocation')) {
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
