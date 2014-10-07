var conv = {
	
	initiated:false,
	
	chatboxes:null,
	
	/*
	 * here we want catch all the chat dom elements
	 */
	$chat:null,
	
	/*
	 * current count of active chat message
	 */
	chatCount:0,
		
	/*
	 * mark an active chatbox while writing
	 */
	activeBox:0,
	
	user2Conv:null,
	
	/*
	 * init function have to be called one time on domready
	 */
	init: function()
	{
		this.initiated = true;
		this.chatboxes = new Array();
		this.$chat = new Array();
		this.user2Conv = new Array();
	},
	userChat: function(fsid)
	{
		if(!this.initiated)
		{
			this.init();
		}
		var cid = this.getConvByFs(fsid);
		if(cid == false)
		{
			ajax.req('msg','user2conv',{
				data:{fsid:fsid},
				success: function(ret)
				{
					conv.user2Conv.push({
						fsid: fsid,
						cid: ret.cid
					});
					conv.chat(ret.cid);
				}
			});
		}
		else
		{
			conv.chat(cid);
		}
		
	},
	
	getConvByFs: function(fsid)
	{
		for(var i=0;i<conv.user2Conv.length;i++)
		{
			if(conv.user2Conv[i].fsid == fsid)
			{
				return conv.user2Conv[i].cid;
			}
		}
		return false;
	},
	
	chat: function(cid)
	{
		if(!this.initiated)
		{
			this.init();
		}
		
		this.appendChatbox(cid);
	},
	
	/**
	 * method to send the right data to the polling service
	 * 
	 */
	registerPollingService: function()
	{
		var ids = conv.getCids();
		
		if(ids.length > 0)
		{
			var infos = conv.getChatInfos();			
			info.editService('msg','chat',{
				speed:'fast',
				premethod:'setSessionInfo',
				ids:ids,
				infos:infos
			});
		}
		else
		{
			info.removeService('msg','chat')
		}
	},
	
	/**
	 * push retrieve function on recieved data by polling will execute this here 
	 */
	push: function(data)
	{
		if(data.msg_chat != undefined && data.msg_chat.length > 0)
		{
			var key = 0;
			for(var i=0;i<data.msg_chat.length;i++)
			{
				key = conv.getKey(data.msg_chat[i].cid);
				if(data.msg_chat[i].msg != undefined && data.msg_chat[i].msg.length > 0)
				{
					for(var x=0;x<data.msg_chat[x].msg.length;x++)
					{
						conv.append(key,data.msg_chat[i].msg[x]);
					}
					conv.maxbox(data.msg_chat[i].cid);
					conv.scrollBottom(data.msg_chat[i].cid);
				}
			}
		}
	},
	
	// minimize or maximize the chatbox
	togglebox: function(cid)
	{
		key = conv.getKey(cid);
		
		conv.chatboxes[key].el.children('.slimScrollDiv, .chatboxinput').toggle();
		//$('#chat-'+cid+' .slimScrollDiv, #chat-'+cid+' ').toggle();
		if($('#chat-'+cid+' .chatboxinput').is(':visible'))
		{
			conv.chatboxes[key].minimized = false;
		}
		else
		{
			conv.chatboxes[key].minimized = true;
		}
		
		conv.registerPollingService();
	},
	
	// maximoze mini box
	maxbox: function(cid)
	{
		key = conv.getKey(cid);
		conv.chatboxes[key].el.children('.slimScrollDiv, .chatboxinput').show();
		conv.chatboxes[key].minimized = false;
	},
	
	// minimize a box
	minbox: function(cid)
	{
		key = conv.getKey(cid);
		conv.chatboxes[key].el.children('.slimScrollDiv, .chatboxinput').hide();
		conv.chatboxes[key].minimized = true;
	},
	
	checkInputKey: function(event,chatboxtextarea,cid) 
	{
		var $ta = $(chatboxtextarea);
		var val = $ta.val().trim();
		var key = this.getKey(cid);
		
		if(event.keyCode == 13 && event.shiftKey == 0  && val != '')  
		{
			conv.showLoader(cid);
			
			setTimeout(function(){
				$ta.val('');
			},100);
			
			$ta.css('height','40px');
			$ta[0].focus();
				
			ajax.req('msg','sendmsg',{
				loader:false,
				method:'post',
				data:{
					c:cid,
					b:val	
				},
				success: function(data)
				{
					conv.append(key,data.msg);
					
					conv.scrollBottom(cid);	
				},
				complete: function(){
					conv.hideLoader(cid);
				}
			});
		}
	},
	
	/**
	 * scroll to bottom after appending messages
	 */
	scrollBottom: function(cid)
	{
		$('#chat-' + cid + ' .chatboxcontent').slimScroll({scrollTo : $('#chat-' + cid + ' .chatboxcontent').prop('scrollHeight') + 'px' });
		//var el = conv.chatboxes[conv.getKey(cid)].el.children('.chatboxcontent');
		//el.slimScroll({scrollTo : el.prop('scrollHeight') + 'px' });
	},
	
	img: function(photo,size)
	{
		if(size == undefined)
		{
			size = 'med';
		}
		if(photo.length > 3)
		{
			return 'images/'+size+'_q_'+photo;
		}
		else
		{
			return 'img/'+size+'_q_avatar.png';
		}
	},
	
	/**
	 * close the chatbox to thr given cid
	 */
	close: function(cid)
	{		
		var tmp = new Array();
		var x = 0;
		for(var i=0;i<conv.chatboxes.length;i++)
		{
			if(conv.chatboxes[i].id == cid)
			{
				conv.chatboxes[i].el.remove();
			}
			else
			{
				conv.chatboxes[i].el.css('right',(20 + (x * 285)) + 'px');
				tmp.push(conv.chatboxes[i]);
				x++;
			}
		}
		
		this.chatboxes = tmp;
		
		this.chatCount--;
		//this.rearrange();
		
		// re register polling service
		this.registerPollingService();
	},
	
	showLoader: function(cid)
	{
		key = this.getKey(cid);
		this.chatboxes[key].el.children('.chatboxhead').children('.chatboxtitle').children('i').removeClass('fa-comment fa-flip-horizontal').addClass('fa-spinner fa-spin');
	},
	
	hideLoader: function(cid)
	{
		key = this.getKey(cid);
		this.chatboxes[key].el.children('.chatboxhead').children('.chatboxtitle').children('i').removeClass('fa-spinner fa-spin').addClass('fa-comment fa-flip-horizontal');
	},
	
	/**
	 * get the array key for given conversation_id
	 */
	getKey: function(cid)
	{
		for(var i=0;i<conv.chatboxes.length;i++)
		{
			if(conv.chatboxes[i].id == cid)
			{
				return i;
			}
		}
		
		return -1;
	},
	
	/**
	 * get actic chatbox infos
	 */
	getChatInfos: function()
	{
		var tmp = new Array();
		
		for(var i=0;i<conv.chatboxes.length;i++)
		{			
			tmp.push({
				id: parseInt(conv.chatboxes[i].id),
				min: conv.chatboxes[i].minimized,
				lmid: conv.chatboxes[i].last_mid
			});
		}
		
		return tmp;
	},
	
	/**
	 * get all conversation ids from active windows
	 */
	getCids: function()
	{
		var tmp = new Array();
		
		for(var i=0;i<conv.chatboxes.length;i++)
		{
			tmp.push(parseInt(conv.chatboxes[i].id));
		}
		
		return tmp;
	},
	
	/**
	 * open settingsmenu to the given chatbox
	 */
	settings: function(cid)
	{
		key = this.getKey(cid);
		this.chatboxes[key].el.children('.chatboxhead').children('.settings').toggle();
	},
	
	/**
	 * append an chat message to chat window with given array index attention not conversation id ;)
	 */
	append: function(key,message)
	{
		conv.chatboxes[key].last_mid = parseInt(message.id);
		conv.chatboxes[key].el.children('.slimScrollDiv').children('.chatboxcontent').append('<div title="'+message.time+'" class="chatboxmessage"><span class="chatboxmessagefrom"><a href="#" class="photo" onclick="profile('+message.fs_id+');return false;"><img src="'+conv.img(message.fs_photo+'','mini')+'"></a></span><span class="chatboxmessagecontent">'+nl2br(message.body)+'<span class="time">'+timeformat.nice(message.time)+'</span></span><div style="clear:both;"></div></div>');
	},
	
	/**
	 * load the first content for one chatbox
	 */
	initChat: function(cid)
	{
		conv.showLoader(cid);
		
		var key = this.getKey(cid);
		var cid = cid;
		
		ajax.req('msg','loadconversation',{
			loader:false,
			data:{
				id:cid
			},
			success: function(ret){
				
				/*
				 * first make a title with all the usernames
				 */
				title = new Array();
				for(var i=0;i<ret.member.length;i++)
				{
					if(ret.member[i] != undefined && ret.member[i].id != user.id)
					{
						title.push(ret.member[i].name);
					}
					
				}
				
				conv.chatboxes[key].el.children('.chatboxhead').children('.chatboxtitle').children('i').after(' '+title.join(', '));
				
				/*
				 * now append all arrived messages
				 */
				if(ret.messages != undefined && ret.messages.length > 0)
				{
					/*
					 * list messages the reverse way
					 */
					for(var y=(ret.messages.length-1);y>=0;y--)
					{
						conv.append(key,ret.messages[y]);
					}
					
					conv.scrollBottom(cid);
				}
			},
			complete: function(){
				conv.hideLoader(cid);
				conv.registerPollingService();
			}
		});
	},
	
	appendChatbox: function(cid,min)
	{		
		if(min == undefined)
		{
			min = false;
		}
		if(conv.getKey(cid) === -1)
		{
			right = 20 + (this.chatCount*285);
			var $el = $('<div id="chat-'+cid+'" class="chatbox ui-corner-top" style="bottom: 0px; right: '+right+'px; display: block;"></div>').appendTo('body');
			$el.html('<div class="chatboxhead ui-corner-top"><a class="chatboxtitle" href="#" onclick="conv.togglebox(' + cid + ');return false;"><i class="fa fa-spinner fa-spin"></i> ' + name + '</a><ul style="display:none;" class="settings linklist linkbubble ui-shadow corner-all"><li><a href="?page=msg&cid='+cid+'">Alle Nachrichten</a></li></ul><div class="chatboxoptions"><a href="#" class="fa fa-gear" title="Einstellungen" onclick="conv.settings('+cid+');return false;"></a><a title="schließen" class="fa fa-close" href="#" onclick="conv.close('+cid+');return false;"></a></div><br clear="all"/></div><div class="chatboxcontent"></div><div class="chatboxinput"><textarea placeholder="schreibe etwas..." class="chatboxtextarea" onkeydown="conv.checkInputKey(event,this,\''+cid+'\');"></textarea></div>');
			
			$el.children('.chatboxcontent').slimScroll();
			$el.children('.chatboxinput').children('textarea').autosize();
			
			$el.children('.chatboxinput').children('textarea').focus(function(){
				conv.activeBox = cid;
			});
			
			this.chatboxes.push({
				el: $el,
				id: cid,
				minimized: false,
				last_mid:0
			});
			
			this.chatCount++;
			
			/*
			 * do the init ajax call
			 */
			this.initChat(cid);
			
			/*
			 * focus textarea
			 */
			$el.children('.chatboxinput').children('textarea').select();
			
			/*
			 * register service new
			 */
			if(min)
			{
				conv.minbox(cid);
			}			
		}
		else
		{
			this.maxbox(cid);
		}
	}
};