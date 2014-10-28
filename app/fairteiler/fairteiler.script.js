$(function(){
	$("#wallpost-attach-image").before('<label id="fbshare-wrapper" style="margin:0 15px 0 0;padding:0;cursor:pointer;"><img style="margin:0;padding:0;position:relative;top:10px;" src="img/facebook_share.png" alt="Auf Facebook teilen" /><input style="position:relative;top:3px;" type="checkbox" name="fbshare" id="fbshare" value="1" /></label>');
	$("#wall-submit").bind('mousedown',function(){
		$("#ft-public-link").trigger('click');
	});
});

function u_fbshare(postid)
{
	if($('#fbshare:checked').length > 0)
	{
		goTo('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent( $("#ft-publicurl").val() + '_' + postid ));
	}
}

function u_wallpostReady(postid)
{
	ajreq('infofollower',{fid:$("#ft-id").val(),pid:postid});	
}