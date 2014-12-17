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
			
			/*
			 '<div class="updatepost">'
				+ '<img src="'+up.icon+'" />'
			+ '<div class="post">'		
			+ '<div class="activity_feed_content">'
				+ '<div class="activity_feed_content_text">'
					+ '<div class="activity_feed_content_info">'
						+ '<a onclick="profile(926);" href="#">Jessy</a> hat etwas auf die Pinnwand von <a href="/?page=fsbetrieb&amp;id=11">Alnatura Bayenthal</a> geschrieben.'
					+ '</div>'
				+ '</div>'
				+ '<div class="activity_feed_content_link">'
					+ 'Tragt euch fließig ein, die nächsten Tage gibt es noch viele freie Kästchen hier ;-)'
				+ '</div>'
			+ '</div>'
			+ '<div class="js_feed_comment_border">'
			+ '<div class="comment_mini_link_like">'
				+ '<div class="foot">'
					+ '<span class="time">Sonntag, 14. Dez, 12:59 Uhr</span>'
				+ '</div>'
			+ '</div>'
			+ '<div class="clear"></div>'
			+ '</div>'
			+ '</div>'
			+ '<div style="clear:both;"></div>'
		+ '</div>'
		
		<a class="corner-all" href="'+href+'"'+click+'>
		*/
			
			activity.$container.append('<li data-ts="'+up.time+'"><span class="i"><img src="'+up.icon+'" /></span><span class="n">'+up.title+'</span><span class="t">'+up.desc+'</span><span class="time"><i class="fa fa-clock-o"></i> '+$.timeago(up.time)+' <i class="fa fa-angle-right"></i> '+timeformat.nice(up.time)+'</span><span class="c"></span></li>');
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