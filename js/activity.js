var activity = {
		
		/*
		 * Elements
		 */
		$container: null,
		$loader:null,
		$page:null,
		
		isLoading:null,
		page:null,
		
		init: function()
		{
			this.isLoading = false;
			this.page = 0;
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
			
			$(window).scroll(function () {
				if(!activity.isLoading)
				{
					if ($(window).scrollTop() >= $(document).height() - $(window).height() - 10) {
					      activity.isLoading = true;
					      activity.page++;
					      ajax.req('activity','loadmore',{
					    	  data:{
					    		  page:activity.page
					    	  },
					    	  success: function(ret)
					    	  {
					    		  if(ret.updates != undefined && ret.updates.length > 0)
									{
										for(var i = 0;i<ret.updates.length;i++)
										{
											activity.append(ret.updates[i]);
										}
									}
									activity.sortUpdates();
					    		  activity.isLoading = false;
					    	  }
					      });
					   }
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
			
			activity.$container.append('<li data-ts="'+up.time_ts+'"><a class="corner-all" href="'+href+'"'+click+'><span class="i"><img src="'+up.icon+'" /></span><span class="n">'+up.title+'</span><span class="t">'+up.desc+'</span><span class="time">'+timeformat.nice(up.time)+'</span><span class="c"></span></a></li>');
		},
		
		sortUpdates: function()
		{
			$('#activity li').tsort('',{order:'desc',attr:'data-ts'});
			/*
			$("#activity li").sort(function (a, b) {
			    return parseInt(a.id) > parseInt(b.id);
			}).each(function(){
			    var elem = $(this);
			    
			    elem.remove();
			    $(elem).prependTo("#activity > ul");
			});
			*/
		}
		
};