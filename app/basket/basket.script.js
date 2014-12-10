$(function(){
	if($('#mapsearch').length > 0)
	{
		$("#map-latLng").change(function(){
			//alert($(this).val())
		});
	
	}
})

var mapsearch = {
	lat: null,
	lon:null,
	init: function()
	{
		
		ajax.req()
	}
}