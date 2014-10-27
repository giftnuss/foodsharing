var u_map = null;
var markers = null;

var fsIcon = L.AwesomeMarkers.icon({
    icon: 'smile',
    markerColor: 'orange',
    prefix: 'img'
});
/*
var fsIcon = L.icon({
    iconUrl: 'img/marker-foodsaver.png',
    shadowUrl: 'img/shadow-marker.png',

    iconSize:     [36, 50], // size of the icon
    shadowSize:   [62, 50], // size of the shadow
    iconAnchor:   [36, 50], // point of the icon which will correspond to marker's location
    shadowAnchor: [36, 50],  // the same for the shadow
    popupAnchor:  [-18, -52] // point from which the popup should open relative to the iconAnchor
});
*/
var bkIcon = L.AwesomeMarkers.icon({
    icon: 'basket',
    markerColor: 'green',
    prefix: 'img'
});
/*
var bkIcon = L.icon({
    iconUrl: 'img/marker-baskets.png',
    shadowUrl: 'img/shadow-marker.png',

    iconSize:     [36, 50], // size of the icon
    shadowSize:   [62, 50], // size of the shadow
    iconAnchor:   [36, 50], // point of the icon which will correspond to marker's location
    shadowAnchor: [36, 50],  // the same for the shadow
    popupAnchor:  [-18, -52] // point from which the popup should open relative to the iconAnchor
});
*/
var botIcon = L.AwesomeMarkers.icon({
    icon: 'smile',
    markerColor: 'red',
    prefix: 'img'
});
/*
var botIcon = L.icon({
    iconUrl: 'img/marker-botschafter.png',
    shadowUrl: 'img/shadow-marker.png',

    iconSize:     [36, 50], // size of the icon
    shadowSize:   [62, 50], // size of the shadow
    iconAnchor:   [36, 50], // point of the icon which will correspond to marker's location
    shadowAnchor: [36, 50],  // the same for the shadow
    popupAnchor:  [-18, -52] // point from which the popup should open relative to the iconAnchor
});
*/
var bIcon = L.AwesomeMarkers.icon({
    icon: 'store',
    markerColor: 'brown',
    prefix: 'img'
});
/*
var bIcon = L.icon({
    iconUrl: 'img/marker-supermarket.png',
    shadowUrl: 'img/shadow-marker.png',

    iconSize:     [36, 50], 
    shadowSize:   [62, 50], 
    iconAnchor:   [36, 50], 
    shadowAnchor: [36, 50],  
    popupAnchor:  [-18, -52]
});
*/
var fIcon = L.AwesomeMarkers.icon({
    icon: 'recycle',
    markerColor: 'yellow',
    prefix: 'img'
});
/*
var fIcon = L.icon({
    iconUrl: 'img/marker-fairteiler.png',
    shadowUrl: 'img/shadow-marker.png',

    iconSize:     [36, 50], 
    shadowSize:   [62, 50], 
    iconAnchor:   [36, 50], 
    shadowAnchor: [36, 50],  
    popupAnchor:  [-18, -52]
});
*/
var map = {
	initiated:false,
	init: function()
	{
		storage.setPrefix('map');
		
		if(storage.get('center') != undefined && storage.get('zoom') != undefined)
		{
			u_map = L.map('map').setView(storage.get('center'),storage.get('zoom'));
			
		}
		else
		{
			u_map = L.map('map').setView([50.89,10.13],6);
		}
		
		L.tileLayer('http://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
			attribution: 'Tiles &copy; Esri 2014'
		}).addTo(u_map);
		
		this.initiated = true;
		
		u_map.on('dragend',function(e){
			map.updateStorage();
		});
		
		u_map.on('zoomend',function(e){
			map.updateStorage();
		});
	},
	initMarker: function(items)
	{
		$('#map-control .linklist a').removeClass('active');
		if(storage.get('activeItems') != undefined)
		{
			items = (storage.get('activeItems'));
		}
		
		for(var i=0;i<items.length;i++)
		{
			$('#map-control .linklist a.' + items[i]).addClass('active');
		}
		
		loadMarker(items);
	},
	updateStorage: function(){		
		var center = u_map.getCenter();
		var zoom = u_map.getZoom();
		
		var activeItems = new Array();
		$('#map-control .linklist a.active').each(function(){
			activeItems.push($(this).attr('name'));
		});
		
		storage.set('center',[center.lat,center.lng]);
		storage.set('zoom',zoom);
		storage.set('activeItems',activeItems);
	},
	setView: function(lat,lon,zoom)
	{
		if(!this.initiated)
		{
			this.init();
		}
		u_map.setView([lat,lon], zoom, {animation: true});
	}
};

function u_init_map(lat,lon,zoom)
{
	map.init();
	
	if(lat == undefined && storage.get('center') == undefined)
	{
		getBrowserLocation(function(pos){
			map.setView(pos.lat, pos.lon, 12);
		});
	}
	
	/*
	u_map = L.map('map').setView([lat, lon], zoom);
	
	L.tileLayer('http://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
		attribution: 'Tiles &copy; Esri 2014'
	}).addTo(u_map);
	*/
	/*
	L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(u_map);
    */
	/*
	L.tileLayer('http://{s}.mqcdn.com/tiles/1.0.0/vy/map/{z}/{x}/{y}.png', {
	    attribution: ' Tiles by <a target="_blank" href="http://mapquest.com">MapQuest</a>. Map data © <a target="_blank" href="http://openstreetmap.org">OpenStreetMap</a> and contributors',
	    maxZoom: 18,
	    subdomains: ['mtile01','mtile02','mtile03','mtile04']
	}).addTo(u_map);
	*/
	/*
	L.tileLayer(\'http://{s}.tile.cloudmade.com/1c62b0ec78ee4aa2b85d9fdffe670b33/113144/256/{z}/{x}/{y}.png\', {
		attribution: ' Tiles by <a target="_blank" href="http://mapquest.com">MapQuest</a>. Map data © <a target="_blank" href="http://openstreetmap.org">OpenStreetMap</a> and contributors',
		maxZoom: 18,
	}).addTo(u_map);
	*/
	//loadMarker(['foodsaver','betriebe']);

}

function u_loadDialog(purl)
{
	$('#b_content').addClass('loading');
	$('#b_content').dialog('option','title','lade...');
	$('#b_content').dialog('open');
	var pos = $('#top .inner').offset();
	$('#b_content').parent().css({
		'left':pos.left+'px',
		'top':'80px'
	});
	
	if(purl != undefined)
	{
		$.ajax({
			url:purl,
			dataType:'json',
			success:function(data){
				if(data.status == 1)
				{
					u_setDialogData(data);
				}
			},
			complete:function(){
				if(loader)
				{
					hideLoader();
				}
			}
		});
	}
}

function u_setDialogData(data)
{
	$('#b_content .inner').html(data.html);
	$('#b_content').dialog('option','title',data.betrieb.name);
	$('#b_content').removeClass('loading');
	$('#b_content .lbutton').button();
}

function init_bDialog()
{
	//alert(pos.left);
	
	$('#b_content').dialog({
		autoOpen:false,
		modal :false,
		draggable:false,
		resizable:false
	});
}

function loadMarker(types,loader)
{
	$('#map-option-wrapper').hide();
	var options = [];
	for(i=0;i<types.length;i++)
	{
		if(types[i] == 'betriebe')
		{
			$('#map-options input:checked').each(function(){
				options[options.length] = $(this).val();
			});
			$('#map-option-wrapper').show();
		}
	}
	
	if(loader == undefined)
	{
		loader = true;
	}
	
	if(loader)
	{
		showLoader();
	}
	
	$.ajax({
		url: 'xhr.php?f=loadMarker',
		data:{types:types,options:options},
		dataType:'json',
		success:function(data){
			if(data.status==1)
			{
				if(markers != null)
				{
					u_map.removeLayer(markers);
				}			
				
				markers = null;
				
				markers = L.markerClusterGroup({maxClusterRadius: 50});
				url = '';
				markers.on('click', function(el){	
					
					fsid = (el.layer.options.id);
					var type = el.layer.options.type;
					
					if(type == 'fs')
					{
						url = 'xhr.php?f=fsBubble&id=' + fsid;
						showLoader();
					}
					else if(type == 'bk')
					{
						ajreq('bubble',{app:'basket',id:fsid});
					}
					else if(type == 'b')
					{
						url = 'xhr.php?f=bBubble&id=' + fsid;
						u_loadDialog();
					}
					else if(type == 'f')
					{
						bid = (el.layer.options.bid);
						goTo('?page=fairteiler&sub=ft&bid='+bid+'&id='+fsid);
					}
					if(url != '')
					{
						$.ajax({
							url:url,
							dataType:'json',
							success:function(data){
								if(data.status == 1)
								{
									if(type == 'fs')
									{
										var popup = new L.Popup({offset:new L.Point(1,-35)});
										popup.setLatLng(el.latlng);
										popup.setContent(data.html);
										u_map.openPopup(popup);
									}
									else if(type == 'b')
									{
										u_setDialogData(data);
										sleepmode.init();
									}
								}
							},
							complete:function(){
								if(loader)
								{
									hideLoader();
								}
							}
						});
					}
				});

				check = false;
				
				if(data.baskets != undefined)
				{
					$('#map-control li a.baskets').addClass('active');
					check = true;
					for (var i = 0; i < data.baskets.length; i++) {
						var a = data.baskets[i];
						var title = a.id;
						var marker = L.marker(new L.LatLng(a.lat, a.lon), { id: a.id, icon: bkIcon,type:'bk' });
						markers.addLayer(marker);
					}
				}
				
				if(data.foodsaver != undefined)
				{
					$('#map-control li a.foodsaver').addClass('active');
					check = true;
					for (var i = 0; i < data.foodsaver.length; i++) {
						var a = data.foodsaver[i];
						var title = a.id;
						var marker = L.marker(new L.LatLng(a.lat, a.lon), { id: a.id, icon: fsIcon,type:'fs' });
						markers.addLayer(marker);
					}
				}
				
				if(data.betriebe != undefined)
				{
					$('#map-control li a.betriebe').addClass('active');
					check = true;
					for (var i = 0; i < data.betriebe.length; i++) {
						var a = data.betriebe[i];
						var title = a.id;
						var marker = L.marker(new L.LatLng(a.lat, a.lon), { id: a.id, icon: bIcon, type:'b' });

						markers.addLayer(marker);
					}
				}
				
				if(data.fairteiler != undefined)
				{
					$('#map-control li a.fairteiler').addClass('active');
					check = true;
					for (var i = 0; i < data.fairteiler.length; i++) {
						var a = data.fairteiler[i];
						var title = a.id;
						var marker = L.marker(new L.LatLng(a.lat, a.lon), { id: a.id, bid: a.bid, icon: fIcon, type:'f' });

						markers.addLayer(marker);
					}
				}
				
				if(data.botschafter != undefined)
				{
					$('#map-control li a.botschafter').addClass('active');
					check = true;
					for (var i = 0; i < data.botschafter.length; i++) {
						var a = data.botschafter[i];
						var title = a.id;
						var marker = L.marker(new L.LatLng(a.lat, a.lon), { id: a.id, icon: botIcon,type:'fs' });
						markers.addLayer(marker);
					}
				}
				u_map.addLayer(markers);
				
			}
			else if(markers != null)
			{
				u_map.removeLayer(markers);
			}
		},
		complete:function(){
			hideLoader();
		}
	});
}




	
function print_r(arr, level) {
		 
		var dumped_text = "";
		if (!level) level = 0;
	 
		//The padding given at the beginning of the line.
		var level_padding = "";
		var bracket_level_padding = "";
	 
		for (var j = 0; j < level + 1; j++) level_padding += "    ";
		for (var b = 0; b < level; b++) bracket_level_padding += "    ";
	 
		if (typeof(arr) == 'object') { //Array/Hashes/Objects 
			dumped_text += "Array\n";
			dumped_text += bracket_level_padding + "(\n";
			for (var item in arr) {
	 
				var value = arr[item];
	 
				if (typeof(value) == 'object') { //If it is an array,
					dumped_text += level_padding + "[" + item + "] => ";
					dumped_text += print_r(value, level + 2);
				} else {
					dumped_text += level_padding + "[" + item + "] => " + value + "\n";
				}
	 
			}
			dumped_text += bracket_level_padding + ")\n\n";
		} else { //Stings/Chars/Numbers etc.
			dumped_text = "===>" + arr + "<===(" + typeof(arr) + ")";
		}
	 
		return dumped_text;
	 
}
var topSliderIsMoving = false;
var toolSliderIsMoving = false;
var topSliderAni = null;
var toolSliderAni = null;
var ani_tempo = 100;
function initSlide()
{
	$(document).click(function() {
		topSlideOut();
		toolSlideOut();
	});
	
	$('#map-control-wrapper').mouseover(function(){
		toolSlideIn();
	});
	$('#map-control-wrapper').mouseleave(function(){
		toolSlideOut();
	});
	
	$('#main').mouseover(function(){
		topSlideIn();
	});
	$('#main').mouseleave(function(){
		topSlideOut();
	});

}

function stopTopAnimation()
{
	for(i=0;i<topSliderAni.length;i++)
	{
		topSliderAni[i].stop();
	}
}
function stopToolAnimation()
{
	toolSliderAni.stop();
}

function toolSlideOut()
{
	if(toolSliderAni != null)
	{
		stopToolAnimation();
	}
	
	toolSliderAni = null;
	toolSliderAni = $('#map-control-wrapper').animate({
		right : '-93px'
	},ani_tempo);
}
function toolSlideIn()
{
	if(toolSliderAni != null)
	{
		stopToolAnimation();
	}
	
	toolSliderAni = null;
	toolSliderAni = $('#map-control-wrapper').animate({
		right : '25px'
	},ani_tempo);
}

function topSlideIn()
{	
	if(topSliderAni != null)
	{
		stopTopAnimation();
	}
	topSliderAni = [];
	topSliderAni[0] = $('#main').animate({
		top : '0px'
	},ani_tempo);
	topSliderAni[1] = $('#msgBar-badge').animate({
		top : '0px'
	},ani_tempo);
	topSliderAni[2] = $('#msgBar').animate({
		top : '14px'
	},ani_tempo);
}

function topSlideOut()
{
	if(topSliderAni != null)
	{
		stopTopAnimation();
	}
	
	topSliderIsMoving = true;
	topSliderAni = [];
	topSliderAni[0] = $('#main').animate({
		top : '-40px'
	},ani_tempo);
	
	topSliderAni[1] = $('#msgBar-badge').animate({
		top : '-40px'
	},ani_tempo);
	topSliderAni[2] = $('#msgBar').animate({
		top : '-40px'
	},ani_tempo);
}

$(document).ready(function(){
	showLoader();
	$('#map-control li a').click(function(){
		$(this).toggleClass('active');
		
		types = [];
		i = 0;
		$('#map-control li a.active').each(function(el){
			types[i] = $(this).attr('name');
			i++;
		});
		loadMarker(types);
		map.updateStorage();
		return false;
	});
	
	$('#map-options input').change(function(){
		
		if($(this).val() == 'allebetriebe')
		{
			$('#map-options input').prop('checked', false);
			$('#map-options input[value=\'allebetriebe\']').prop('checked', true);
		}
		else
		{
			$('#map-options input[value=\'allebetriebe\']').prop('checked', false);
		}
		if($('#map-options input:checked').length == 0)
		{
			$('#map-options input[value=\'allebetriebe\']').prop('checked', true);
		}
		
		types = [];
		i = 0;
		$('#map-control li a.active').each(function(el){
			types[i] = $(this).attr('name');
			i++;
		});
		setTimeout(function(){
			loadMarker(types);
		},100);
	});
	
	//initSlide();
	
	
	init_bDialog();
	/*
	if(isMob())
	{
		$('#map-control-wrapper div:first').css({
			'overflow':'hidden',
			'margin-left':'142px',
			'padding':'0'
		});
		$('#map-control-wrapper div:first div:first').css({
			'padding':'2px'
		});
		setTimeout(function(){
			$('.leaflet-bottom.leaflet-right, #g-texter').hide();
		},200);
		
	}
	
	$(window).resize(function(){
		if(isMob())
		{
			$('#map-control-wrapper div:first').css({
				'overflow':'hidden',
				'margin-left':'142px',
				'padding':'0'
			});
			$('#map-control-wrapper div:first div:first').css({
				'padding':'2px'
			});
			$('.leaflet-bottom.leaflet-right, #g-texter').hide();
		}
	});
	*/
});



$(window).load(function(){
	
});
	/*
	$("div.map-wrapper").fadeTo( 1, 0 );
	
	$(window).load(function(){
		$(".ajax-loader").fadeOut(100);
		$("div.map-wrapper").fadeTo( 300, 1);
	});*/