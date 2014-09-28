var join = {
	currentStep:0,
	map:null,
	markerIcon:null,
	marker:null,
	init: function()
	{
		join.map = false;
		$("#login_location").geocomplete({
			details: 'form.join_geo_data'
		}).bind("geocode:result", function(event, result){
			latLng = [result.geometry.location.lat(),result.geometry.location.lng()];
			console.log(result);
			join.marker.setLatLng(latLng);
			join.map.setView(latLng,14);
		});
	},
	loadMap: function()
	{
		if(join.map === false)
		{
			join.map = L.map('join_mapview').setView([50.89, 10.13], 3);
			
			L.tileLayer('http://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
				attribution: 'OpenStreetMap'
			}).addTo(join.map);
			
			
			
			setTimeout(function(){ 
				join.map.invalidateSize();
				
				join.markerIcon = L.AwesomeMarkers.icon({
				    icon: 'fa-home',
				    markerColor: 'green',
				    prefix: 'fa'
				});
				
				join.marker = L.marker(new L.LatLng(50.89, 10.13), { icon: join.markerIcon });
				join.marker.addTo(join.map);
				
			}, 50);
		}
		else
		{
			setTimeout(function(){ join.map.invalidateSize()}, 50);
		}
	},
	finish: function(){
		alert('send...');
	},
	step: function(step)
	{
		
		if(join.currentStep >= step || join.stepCheck(step))
		{
			switch(step)
			{
				case 2:
					join.loadMap();
					break;
					
				default:
					break;
			}
			
			$('.step').hide();
			$('.step'+step).show();
			$('.linklist.join li').removeClass('active').children('a').children('i').remove();
			$('.linklist.join li.step'+step).addClass('active');
			$('.linklist.join li.step'+step).removeClass('hidden').children('a').append('<i class="fa fa-hand-o-right"></i>');
			join.currentStep = step;
		}
	},
	stepCheck: function(step){
		
		switch(join.currentStep)
		{
			case 1:
				check = true;
				
				if($('#login_name').val() == '')
				{
					pulseInfo('Bitte Gib einen Benutzernamen ein');
					$('#login_name').select();
					return false;
					check = false;
				}
				
				if(!checkEmail($('#login_email').val()))
				{
					pulseError('Mit Deiner E-Mail Adresse stimmt etwas nicht');
					$('#login_email').select();
					return false;
					check = false;
				}
				
				if($('#login_passwd1').val().length < 4) // || $('#login_passwd1').val() != $('#login_passwd2').val())
				{
					pulseInfo('Dein Passwort muss länger als 4 buchstaben sein');
					$('#login_passwd1').select();
					return false;
					check = false;
				}
				
				if($('#login_passwd1').val() != $('#login_passwd2').val())
				{
					pulseInfo('Deine Passwörter stimmen nicht überein');
					$('#login_passwd1').select();
					return false;
					check = false;
				}
				
				if(check)
				{
					return true;
				}
				else
				{
					return false;
				}
				
				break;
				
			default:
				return true;
				break;
		}
	}
};