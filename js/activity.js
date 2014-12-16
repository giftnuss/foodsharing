var activity = {
		
		/*
		 * Elements
		 */
		$container: null,
		$loader:null,
		
		init: function()
		{
			$('#activity').append('<ul class="linklist"></ul>');
			this.$container = $('#activity > ul.linklist');
			this.$loader = $('#activity > .loader');
			
			ajax.req('activity','load',{
				loader:false,
				success: function(ret){
					activity.$loader.hide();
					if(ret.updates != undefined && ret.updates.length > 0)
					{
						for(var i = 0;i<ret.updates.length;i++)
						{
							activity.append(ret.updates[i]);
						}
					}
					activity.sortUpdates();
				}
			});
		},
		
		append: function(up)
		{
			href = '#';
			click = '';
			
			if(up.attr.href != undefined)
			{
				href = up.attr.href;
			}
			
			if(up.attr.onclick != undefined)
			{
				click = ' onclick="'+up.attr.onclick+';return false;"';
			}
			
			activity.$container.append('<li id="'+up.time_ts+'"><a class="corner-all" href="'+href+'"'+click+'><span class="i"><img src="'+up.icon+'" /></span><span class="n">'+up.title+'</span><span class="t">'+up.desc+'</span><span class="time">'+timeformat.nice(up.time)+'</span><span class="c"></span></a></li>');
		},
		
		sortUpdates: function()
		{
			$("#activity li").sort(function (a, b) {
			    return parseInt(a.id) > parseInt(b.id);
			}).each(function(){
			    var elem = $(this);
			    
			    elem.remove();
			    $(elem).prependTo("#activity ul");
			});
		}
		
};