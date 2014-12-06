$(function(){
	$('#teamlist .foot i').mouseover(function(){
		
		var $this = $(this);
		
		var val = $this.children('span').text();
		if(val != '')
		{
			$this.parent().parent().attr('href',val);
		}
	});
	
	$('#teamlist .foot i').click(function(ev){
		
		var $this = $(this);
		if($this.hasClass('fa-lock'))
		{
			ev.preventDefault();
			u_tox($this.children('span').text());
			
		}
		
		if($this.hasClass('fa-envelope'))
		{
			ev.preventDefault();
			goTo($this.parent().parent().attr('href'));
		}
	});
	
	
	$('#teamlist .foot i').mouseout(function(){
		var $this = $(this).parent().parent();
		
		$this.attr('href','/team/' + $this.attr('id').substring(2));
		
	});
	
	if($('#contactform').length > 0)
	{
		$('#contactform-form').submit(function(ev){
			ev.preventDefault();
			
			ajax.req('team','contact',{
				data: $('#contactform-form').serialize(),
				method: 'post',
				success: function()
				{
					$("#team-user").next().fadeOut();
				}
			});
		});
	}
});


function u_tox(id)
{
	var p1 = new Popup();
	
	var $cnt = $('#tox-pop-' + id);
	var $qr = $cnt.children('.tox-qr');
	
	var tox_id = $qr.text();
	$qr.html('');
	
	$qr.qrcode(tox_id);
	p1.setContent($cnt.html());
	p1.open();
}