var g_no_requests_please = true;

$(function(){
	clearInterval(g_interval_newMsg);
	$('.startstat').click(function(){
		ajreq('startcalc');
	});
	$('.startstat_force').click(function(){
		ajreq('startcalc',{force:true});
	});
});