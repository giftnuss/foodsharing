// Damit im Plugin auch das kürzel $ verwendet werden kann
(function($) {
  $.fn.toolmenu = function (options){
    // Optionen welche übergeben wurden in eine Variable speichern und
    // mit den Standardwerten verbinden
    var opts = $.extend($.fn.toolmenu.defaults, options);
 
    // Für hedes Element die Boxen erstellen
    return this.each(function() {
    	
    	$(this).click(function(){
    		$el = $(this);
    	      
    	      var pos = $el.position();
    	      var width = $el.outerWidth();
    	      var height = $el.outerHeight();
    	      
    	      
    	      elwidth = 200;
    	      
    	      erg = (pos.left+(width/2)) - (elwidth/2);
    	      
    	      html = '<div style="display:block;width:'+elwidth+'px;position:absolute;top:'+(pos.top+height-1)+'px;left:'+erg+'px;" class="toolmenu"><ul class="linklist">';
    	      for(i=0;i<opts.items.length;i++)
    	      {
    	    	 html += '<li><a class="ui-corner-all" href="#">'+opts.items[i].name+'</a></li>';
    	      }
    	      html += '</ul></div>';
    	      $('body').append(html);
    	});
    	
      
      
    });
  };
 
  /*
  if(typeof opts.nameOfEvent == "function") {
   o.nameOfEvent(param1, param2);
  }
  */
 
  // Standard-Optionen für das Plugin
  $.fn.toolmenu.defaults = {
    width: 150,
    height: 300
    // nameOfEvent: function(param1, param2) {},
  }
})(jQuery);

$(document).ready(function(){
	$('a.toolmenu').toolmenu({
		items:[
		   {
			   name : 'bearbeiten'
		   }
		]
	});
});