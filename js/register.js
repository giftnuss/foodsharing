var jcrop;
var g_current_region = 0;
function nextChildren(el)
{
	$(el).next('.region-chooser').remove();
	$.fancybox.showLoading();
	$.ajax({
		url: "../xhr.php?f=getBezirkChildren",
		data: 'p=' + el.value,
		dataType: "json",
		success: function(data) {
			if(data.status == 1)
			{
				$('.region-chooser-' + g_current_region).remove();
				name = ($(el).children(':selected').text());
				//$('#bezirk_chooser select').attr('name','nix');
				$('.region-chooser:last').after(row(name,data.html,el.value));
				//$('#bezirk_chooser').append(data.html);
				$('.region-chooser select:last').customSelect();
				g_current_region = el.value;
			}
		},
		complete: function(){
			$.fancybox.hideLoading();
		}
	});
}

function row(question,input,id)
{
	label = question;
	question = 'In '+ question + 'gibt es mehrere unter-Regionen w&auml;hle eine aus oder bleibe in der Eltern Region.';
	return '<div class="region-chooser region-chooser-'+id+' ss-form-question errorbox-good"><div class="ss-item ss-item-required ss-text" dir="ltr"><div class="form-entry"><label for="entry_790728861" class="ss-q-item-label"><div class="q-title">'+label+' <label aria-label="(Pflichtfeld)" for="itemView.getDomIdToLabel()"></label></div><div dir="ltr" class="ss-q-help ss-secondary-text">'+question+'</div> </label>'+input+'</div></div></div>';	
}

function showBezirke(el)
{
	$('.region').css('display','none');
	$('.region select').attr('name','nix');
	$('#region-'+el.value + ' select').attr('name','bezirk');
	
	if($('#region-'+el.value + ' select option').length > 2)
	{
		$('#region-'+el.value).css('display','block');
		$('#row-bezirk').css('display','block');
	}
	else
	{
		$('#row-bezirk').css('display','none');
	}
	
}


$(document).ready(function(){
	
	
	$("#lat-wrapper,#lon-wrapper").hide();
    var addresspickerMap = $( "#addresspicker_map" ).addresspicker({
      regionBias: "de",
      updateCallback: showCallback,
	  reverseGeocode: true,
      mapOptions: {
        zoom: 4,
        center: new google.maps.LatLng(50.05478727164819, 10.3271484375),
        scrollwheel: false,
        mapTypeId: google.maps.MapTypeId.ROADMAP
      },
      elements: {
        map:      "#map",
        lat:      "#lat",
        lng:      "#lon"
      }
    });

    var gmarker = addresspickerMap.addresspicker( "marker");
    gmarker.setVisible(true);
    addresspickerMap.addresspicker( "updatePosition");

   

    function showCallback(geocodeResult, parsedGeocodeResult){
    	$("#anschrift").val("");
		if(parsedGeocodeResult.street_number != false)
		{
        	$("#anschrift").val(parsedGeocodeResult.route + " " + parsedGeocodeResult.street_number);
		}
		else if(parsedGeocodeResult.route != false)
		{
			$("#anschrift").val(parsedGeocodeResult.route);
		}
		$("#q-plz").val("");
		if(parsedGeocodeResult.postal_code != false)
		{
        	$("#q-plz").val(parsedGeocodeResult.postal_code);
		}
		$("#q-stadt").val("");
		if(parsedGeocodeResult.postal_code != false)
		{
        	$("#q-stadt").val(parsedGeocodeResult.locality);
		}
		
    }
    // Update zoom field
    var map = $("#addresspicker_map").addresspicker("map");
    google.maps.event.addListener(map, "idle", function(){
      $("#zoom").val(map.getZoom());
    });
	
	
	$('#no-js').css('display','none');
	$('#has-js').css('display','block');

	
	$("#q-email").blur(function(){
		setTimeout(function(){
			if($(document.activeElement).attr('id') != 'q-email')
			{
				$.ajax({
					url: '../xhr.php?f=checkmail',
					data:{'email':$("#q-email").val()},
					dataType:'json',
					success:function(data){
						if(data.status != 1)
						{
							error("Die eingegebene <strong>E-Mail Adresse</strong> existiert bereits");
							$("#q-email").val("");
						}
					}
				});
			}			
		},200);
	});
	
	
	$('#fotouploadopen').button().fancybox({
		minWidth : 600,
		scrolling :'auto',
		closeClick : false,
		helpers : { 
		  overlay : {closeClick: false}
		}
	});
	
	/*
	$('#fotouploadopen').button().click(function(e){
		e.preventDefault();
		//$("#fotoupload").dialog('open');
		
		$.fancybox.open([
		{
			href : 'http://fancyapps.com/fancybox/demo/1_b.jpg'
		}   
		], {
		      padding : 0   
		});
		
	});
	*/
	$('#reg-form').submit(function(e){
		
		len = $('div.form-entry').length;
		
		for(i=0;i<len;i++)
		{
			el = $('div.form-entry')[i];

			req = $(el).find('span.required-asterisk');


			if(req.html() == '*')
			{
				input = $(el).find('input, select, textarea');
				if(input.length > 0)
				{
					if(input.val() == "")
					{
						fname = $(el).find('div.q-title');
						if(fname.length > 0)
						{
							input.focus();
							return error("Das Feld <strong>" + $.trim(fname.text().replace("*","")) + "</strong> muss noch ausgef&uuml;llt werden ;)");
						}
					}
				}
			}

		}
		
		$("#q-plz").val($.trim($("#q-plz").val()));
		if(!$("#q-plz").val().match(/^[0-9]{4,5}$/))
		{
			$('#q-plz')[0].focus();
			return error("Mit Deiner <strong>Postleitzahl</strong> stimmt etwas nicht.");
		}
		
		if($('#bezirk_id').val() == "")
		{
			return error("Du musst eine <strong>Region</strong> ausw&auml;hlen in der Du aktiv werden willst.");
		}
		
		if(!validEmail($('#q-email').val()))
		{
			$('#q-email')[0].focus();
			return error("Mit Deiner <strong>E-Mail Adresse</strong> stimmt etwas nicht.");
		}
		
		
		
		if(document.getElementById('check-haftung').checked == false)
		{
			document.getElementById('check-haftung').focus();
			return error("Du musst den <strong>Haftungsausschluss</strong> zur Kenntnis nehmen!");
		}

		if(document.getElementById('datenschutz').checked == false)
		{
			document.getElementById('datenschutz').focus();
			return error("Du musst die <strong>Datenschutzerkl&auml;rung</strong> zur Kenntnis nehmen!");
		}
		
		
		
		
		if($("#u_festnetz").val() == "" && $("#u_handy").val() == "")
		{
			$("#u_festnetz")[0].focus()
			return error("Eine <strong>Telefonnummer</strong> brauchen wir von Dir Bitte trage mindestens eine Handy oder eine Festnetznummer ein!");
		}
		
		if(document.getElementById('u_aufg_bot').checked == false)
		{
			document.getElementById('u_aufg_bot').focus();
			return error("Du musst die <strong>Aufgaben der BotschafterInnen</strong> zur Kenntnis nehmen!");
		}
		
		/*
		if($('#pic_file').val()=='0')
		{
			document.getElementById("foto_focus").focus();
			return error(e,"Bitte lade ein <strong>Foto</strong> von Dir hoch!");
		}*/
		
		if($("#newstadtteil").val() != "")
		{
			/*
			$('div.u_dialog').remove();
			$("body").append('<div class="u_dialog">Bist Du Dir sicher das Du einen neuen Bezirk anlegen möchtest? Oder willst Di lieber noch einmal schauen</div>');
			$('div.u_dialog').dialog({
				
			});
			*/
		}
		
		
		
	});
	/*
	$("#fotoupload").dialog({
		autoOpen:false,
		width:600,
		height:700,
		title: 'Foto-Upload',
		modal : true,
		open: function(event, ui) {$(this).parent().children().children(".ui-dialog-titlebar-close").hide();},
		draggable: false
	});
	*/
	
	$("#dialog-error").dialog({
		autoOpen:false,
		width:450,
		title: 'Alles richtig ausgefüllt?',
		modal : true,
		buttons: {
			'Alles Klar!': function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
   $('select').customSelect();

    $('#reg-submit').button().click(function(){
		
    });
    
    $( "div.form-entry input" ).focus(
			function() {
				$( this ).addClass( "focus" );
			}
	);
	$( "div.form-entry input" ).blur(
			function() {
				$( this ).removeClass( "focus" );
			}
	);
	
	$("input.date").datepicker({
		changeYear: true,
		changeMonth: true,
		maxDate: "-14y",
		defaultDate: "-21y",
		minDate:"-120y",
		dateFormat: "yy-mm-dd",
		monthNames: [ "Januar", "Februar", "M&auml;rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember" ],
		yearRange: "1953:2073"
	});

});

function pic_loader()
{
	$('#foto_placeholder').css('background-image','none');
	$.fancybox.showLoading();
}
function pic_loader_hide()
{
	$.fancybox.hideLoading();
}

function updateCoords(c)
{
	$('#pic_x').val(c.x);
	$('#pic_y').val(c.y);
	$('#pic_w').val(c.w);
	$('#pic_h').val(c.h);
};

function fotoupload(file)
{
	$('#pic_file').val(file);
	
	d = new Date();
	img = file+'?'+d.getTime();
	
	$('#foto_placeholder').html('<img src="../tmp/'+img+'" />');
	jcrop = $('#foto_placeholder img').Jcrop({
         setSelect:   [ 100, 0, 400, 400 ],
         aspectRatio: 35 / 45,
         onSelect: updateCoords
     });
	 $('#pic_savebutton').show();
	 $('#pic_savebutton').button().click(function(){
		 pic_loader();
		 $('#pic_action').val('crop');
		 $('#fotoupload_form')[0].submit();
		 return false;
	 });
	 
	 $('#foto_placeholder').css('height','auto');
	 pic_loader_hide();
	 setTimeout(function(){
		 $.fancybox.update();
		 $.fancybox.reposition();
		 $.fancybox.toggle();
	 },200)
	 
}

function picFinish(img)
{
	$('#pic_action').val('upload');
	//$("#fotoupload").dialog('close');
	$.fancybox.close();
	d = new Date();
	imgp = img+'?'+d.getTime();
	$('#newpic_placeholder').html('<img src="../tmp/'+imgp+'" /><input type="hidden" name="photo" value="'+img+'" />');
	pic_loader_hide();
	$('#fotouploadopen').children('span').html('Foto bearbeiten');
}

function error(msg)
{
	$("#dialog-error span.cnt").html(msg);

	$("#dialog-error").dialog('open');

	
	return false;
}

function goTo(url)
{
	if(url != '#')
	{
		document.location.href = url;
	}
}

function pic_error(msg)
{
	msg = '<div class="ui-widget"><div style="padding: 15px;" class="ui-state-error ui-corner-all"><p><span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-alert"></span><strong>Fehler:</strong> ' + msg + '</p></div></div>';
	$('#foto_placeholder').html(msg);
	pic_loader_hide();
}

function validEmail(email) 
{
	  var strReg = "^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$";
	  var regex = new RegExp(strReg);
	  return(regex.test(email));
}