var windowFocus = true;
var username;
var chatHeartbeatCount = 0;
var minChatHeartbeat = 1000;
var maxChatHeartbeat = 33000;
var chatHeartbeatTime = minChatHeartbeat;
var originalTitle;
var blinkOrder = 0;
var g_chatheartbeatTO = null;
var g_chatheartbeatTO1 = null;
var g_chatheartbeatTO2 = null;

var chatboxFocus = new Array();
var newMessages = new Array();
var newMessagesWin = new Array();
var chatBoxes = new Array();
var chatBlinkInterval = new Array();

$(document).ready(function(){
	originalTitle = document.title;
	startChatSession();

	$([window, document]).blur(function(){
		windowFocus = false;
	}).focus(function(){
		windowFocus = true;
		document.title = originalTitle;
	});
});

function restructureChatBoxes() {
	align = 0;
	for (x in chatBoxes) {
		chatboxtitle = chatBoxes[x];

		if ($("#chatbox_"+chatboxtitle).css('display') != 'none') 
		{
			if (align == 0) 
			{
				$("#chatbox_"+chatboxtitle).css('right', '20px');
			} 
			else 
			{
				width = (align)*(273+7)+20;
				$("#chatbox_"+chatboxtitle).css('right', width+'px');
			}
			align++;
		}
	}
}

function chatWith(id) {
	$('.ui-dialog-content').each(function(){
		$this = $(this);

		if($this.parent().hasClass('ui-dialog'))
		{
			$this.dialog({autoOpen:false});
			$this.dialog('close');
		}
	});
	
	if ($("#chatbox_"+id).length > 0) {
		if ($("#chatbox_"+id).css('display') == 'none') {
			$("#chatbox_"+id).css('display','block');
			restructureChatBoxes();
		}
		
		$("#chatbox_"+id + ' .chatboxcontent').css('display','block');
		$("#chatbox_"+id + ' .chatboxinput').css('display','block');
		
		$("#chatbox_"+id+" .chatboxtextarea").focus();
	}
	else
	{
		var id = id;
		$.ajax({
			url: 'xhrapp.php?app=chat&m=init&id=' + id,
			dataType: 'json',
			success: function(data){
				createChatBox(data);
				if(data.msg != false)
				{
					var last_time = '';
					for(i=0;i<data.msg.length;i++)
					{
						chatAddMsg(id, data.msg[i], false);
						last_time = data.msg[i].t;
					}
					if(last_time != '')
					{
						chatAppendTime(id, last_time);
						$("#chatbox_"+id+" .chatboxcontent").scrollTop($("#chatbox_"+id+" .chatboxcontent")[0].scrollHeight);
					}
				}
				$("#chatbox_"+id+" .chatboxtextarea").focus();
			}
		});
	}
	$("#msgbar-messages").hide();
	$("#msgbar-infos").hide();
}

function killBr(str)
{
	str = str.replace('\r','');
	for(i=0;i<40;i++)
	{
		str = str.replace('\n\n\n','\n\n');
	}
	return str;
}

function chatnl2br (str, is_xhtml) {
	
	str = killBr(str).autoLink({target:'_blank'});
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function createChatBox(user,minimizeChatBox) {
	if ($("#chatbox_"+user.f).length > 0) {
		if ($("#chatbox_"+user.f).css('display') == 'none') {
			$("#chatbox_"+user.f).css('display','block');
			restructureChatBoxes();
		}
		$("#chatbox_"+user.f+" .chatboxtextarea").focus();
		return;
	}
	
	chatboxtitle = user.f;
	chatHiddenImg(user.f, user.p);
	var $div = $('<div />').appendTo('body');
	$div.attr("id","chatbox_"+user.f);
	$div.addClass("chatbox");
	$div.addClass("ui-corner-top");
	
	$div.html('<div class="chatboxhead ui-corner-top"><a class="chatboxtitle" href="javascript:void(0)" onclick="javascript:toggleChatBoxGrowth(\''+user.f+'\')"><i class="fa fa-comment fa-flip-horizontal"></i> '+user.n+'</a><div class="chatboxoptions"><a class="fa fa-close" href="javascript:void(0)" onclick="javascript:closeChatBox(\''+user.f+'\')"></a></div><br clear="all"/></div><div class="chatboxcontent"></div><div class="chatboxinput"><textarea placeholder="schreibe etwas..." class="chatboxtextarea" onkeydown="javascript:return checkChatBoxInputKey(event,this,\''+user.f+'\');"></textarea></div>');
	$('.chatboxinput textarea').autosize();	   
	$("#chatbox_"+user.f).css('bottom', '0px');
	
	chatBoxeslength = 0;

	for (x in chatBoxes) {
		if ($("#chatbox_"+chatBoxes[x]).css('display') != 'none') {
			chatBoxeslength++;
		}
	}

	if (chatBoxeslength == 0) {
		$("#chatbox_"+user.f).css('right', '20px');
	} else {
		width = (chatBoxeslength)*(273+7)+20;
		$("#chatbox_"+user.f).css('right', width+'px');
	}
	
	
	chatBoxes.push(user.f);
	minimize = 0;
	
	if (minimizeChatBox == 1) {
		minimizedChatBoxes = new Array();

		if ($.cookie('chatbox_minimized')) {
			minimizedChatBoxes = $.cookie('chatbox_minimized').split(/\|/);
		}
		
		for (j=0;j<minimizedChatBoxes.length;j++) {
			if (minimizedChatBoxes[j] == chatboxtitle) {
				minimize = 1;
			}
		}
	}

	chatboxFocus[chatboxtitle] = false;
	
	$("#chatbox_"+chatboxtitle+" .chatboxtextarea").blur(function(){
		chatboxFocus[chatboxtitle] = false;
		$("#chatbox_"+chatboxtitle+" .chatboxtextarea").removeClass('chatboxtextareaselected');
	}).focus(function(){
		chatboxFocus[chatboxtitle] = true;
		newMessages[chatboxtitle] = false;
		$('#chatbox_'+chatboxtitle+' .chatboxhead').removeClass('chatboxblink');
		if(chatBlinkInterval[chatboxtitle] != undefined)
		{
			clearInterval(chatBlinkInterval[chatboxtitle]);
		}
		else if(chatBlinkInterval[chatboxtitle] != false)
		{
			clearInterval(chatBlinkInterval[chatboxtitle]);
		}
		chatBlinkInterval[chatboxtitle] = false;

	});

	$("#chatbox_"+chatboxtitle).click(function() {
		if ($('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display') != 'none') {
			//$("#chatbox_"+chatboxtitle+" .chatboxtextarea").focus();
		}
	});

	$("#chatbox_"+chatboxtitle).show();
	
	$('.chatboxtextarea').focus(function(){
		$('.chatboxtextarea').removeClass('chatboxtextareaselected');
		$(this).addClass('chatboxtextareaselected');
	});
	
	$('.chatboxtextarea').keydown(function(objEvent) {
	    if (objEvent.keyCode == 9) { 
	        objEvent.preventDefault();
	    }
	});
	
	$("#chatbox_"+chatboxtitle+" .chatboxcontent").slimScroll({
        height: '264px'
    });
	$('#chatbox_'+chatboxtitle+' .chatboxcontent').css('height','250px');
	
	if (minimize == 1) {
		$('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display','none');
		$('#chatbox_'+chatboxtitle+' .slimScrollDiv').css('display','none');
		$('#chatbox_'+chatboxtitle+' .chatboxinput').css('display','none');
	}
}


function chatHeartbeat(){

	var itemsfound = 0;
	
	if (windowFocus == false) {
 
		var blinkNumber = 0;
		var titleChanged = 0;
		for (x in newMessagesWin) {
			if (newMessagesWin[x] == true) {
				++blinkNumber;
				if (blinkNumber >= blinkOrder) {
					document.title = 'Neue Nachricht...';
					titleChanged = 1;
					break;	
				}
			}
		}
		
		if (titleChanged == 0) {
			document.title = originalTitle;
			blinkOrder = 0;
		} else {
			++blinkOrder;
		}

	} else {
		for (x in newMessagesWin) {
			newMessagesWin[x] = false;
		}
	}

	for (x in newMessages) {
		if (newMessages[x] == true) {
			if (chatboxFocus[x] == false) {
				//FIXME: add toggle all or none policy, otherwise it looks funny
				if(chatBlinkInterval[x] == undefined || chatBlinkInterval[x] == false)
				{
					chatBlinkInterval[x] = setInterval(function(){
						$('#chatbox_'+x+' .chatboxhead').toggleClass('chatboxblink');
					},1000);
				}
			}
		}
	}
	
	$.ajax({
	  url: "chat.php?action=chatheartbeat",
	  cache: false,
	  dataType: "json",
	  success: function(data) {
		
		$.each(data.items, function(i,item){
			if (item)	{ // fix strange ie bug
				chatAddMsg(item.f,item);
				itemsfound += 1;
				last_time = item.t;
			}
		});

		chatHeartbeatCount++;

		if (itemsfound > 0) {
			chatHeartbeatTime = minChatHeartbeat;
			chatHeartbeatCount = 1;
		} else if (chatHeartbeatCount >= 10) {
			chatHeartbeatTime *= 2;
			chatHeartbeatCount = 1;
			if (chatHeartbeatTime > maxChatHeartbeat) {
				chatHeartbeatTime = maxChatHeartbeat;
			}
		}
		
		g_chatheartbeatTO = setTimeout('chatHeartbeat();',chatHeartbeatTime);
	}});
}

function chatAddMsg(id,item,newmsg)
{
	if(newmsg == undefined)
	{
		newmsg = true;
	}
	chatboxtitle = id;
	name = item.n;

	if ($("#chatbox_"+chatboxtitle).length <= 0) {
		createChatBox(item);
	}
	if ($("#chatbox_"+chatboxtitle).css('display') == 'none') {
		$("#chatbox_"+chatboxtitle).css('display','block');
		restructureChatBoxes();
	}
	
	if (item.s == 1) {
		item.f = username;
	}

	if (item.s == 2) {
		chatAppendTime(id, item.m);
		
	} else {
		if(newmsg)
		{
			newMessages[chatboxtitle] = true;
			newMessagesWin[chatboxtitle] = true;
		}
		$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage" title="'+item.sent+'"><span class="chatboxmessagefrom">'+chatImg(item.f)+'</span><span class="chatboxmessagecontent">'+nl2br(item.m)+'</span><div style="clear:both;"></div></div>');
	}
	if($("#chatbox_"+chatboxtitle+" .chatboxcontent").length > 0)
	{
		$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
	}
}

function chatAppendTime(id,time)
{
	if(time != undefined)
	{
		$("#chatbox_"+id+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxinfo" title="'+time+'">'+time+'</span></div>');
		$('.chatboxinfo').timeago();
	}
}

function closeChatBox(chatboxtitle) {
	$('#chatbox_'+chatboxtitle).css('display','none');
	restructureChatBoxes();

	$.post("chat.php?action=closechat", { chatbox: chatboxtitle} , function(data){	
	});

}

function toggleChatBoxGrowth(chatboxtitle) {
	if ($('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display') == 'none') {  
		
		var minimizedChatBoxes = new Array();
		
		if ($.cookie('chatbox_minimized')) {
			minimizedChatBoxes = $.cookie('chatbox_minimized').split(/\|/);
		}

		var newCookie = '';

		for (i=0;i<minimizedChatBoxes.length;i++) {
			if (minimizedChatBoxes[i] != chatboxtitle) {
				newCookie += chatboxtitle+'|';
			}
		}

		newCookie = newCookie.slice(0, -1)


		$.cookie('chatbox_minimized', newCookie);
		$('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display','block');
		$('#chatbox_'+chatboxtitle+' .slimScrollDiv').css('display','block');
		//slimScrollDiv
		$('#chatbox_'+chatboxtitle+' .chatboxinput').css('display','block');
		$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
		
		
	} else {
		
		var newCookie = chatboxtitle;

		if ($.cookie('chatbox_minimized')) {
			newCookie += '|'+$.cookie('chatbox_minimized');
		}


		$.cookie('chatbox_minimized',newCookie);
		$('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display','none');
		$('#chatbox_'+chatboxtitle+' .slimScrollDiv').css('display','none');
		$('#chatbox_'+chatboxtitle+' .chatboxinput').css('display','none');
	}
	
}

function checkChatBoxInputKey(event,chatboxtextarea,chatboxtitle) {
	if(event.keyCode == 13 && event.shiftKey == 0)  {
		message = $(chatboxtextarea).val();
		message = message.replace(/^\s+|\s+$/g,"");
		killBr(message);
		$(chatboxtextarea).val('');
		//$(chatboxtextarea).focus();
		$(chatboxtextarea).css('height','44px');
		if (message != '') {
			$.ajax({
				url: "xhrapp.php?app=chat&m=sendchat",
				data: {to: chatboxtitle, message: message},
				type: 'post',
				dataType: 'json',
				success: function(data){
					message = message.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;");

					$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+chatImg(username)+'</span><span class="chatboxmessagecontent">'+chatnl2br(message)+'</span><div style="clear:both;"></div></div>');
					$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
				}
			});
			
		}
		chatHeartbeatTime = minChatHeartbeat;
		chatHeartbeatCount = 1;

		return false;
	}

	var adjustedHeight = chatboxtextarea.clientHeight;
	var maxHeight = 94;

	if (maxHeight > adjustedHeight) {
		adjustedHeight = Math.max(chatboxtextarea.scrollHeight, adjustedHeight);
		if (maxHeight)
			adjustedHeight = Math.min(maxHeight, adjustedHeight);
		if (adjustedHeight > chatboxtextarea.clientHeight)
			$(chatboxtextarea).css('height',adjustedHeight+8 +'px');
	} else {
		$(chatboxtextarea).css('overflow','auto');
	}
	 
}

function chatHiddenImg(id,photo)
{
	if(photo != undefined && photo != '')
	{
		$('#chatHidden .hImg-'+id).remove();
		photo = '<a onclick="profile('+parseInt(id)+');return false;" class="photo" href="#'+id+'"><img src="images/mini_q_'+photo+'" /></a>';
		$('#chatHidden').append('<div class="hImg-'+id+'">'+photo+'</div>');
	}
}

function startChatSession(){  
	$('body:last').append('<div id="chatHidden" style="display:none;"></div>');
	$.ajax({
	  url: "chat.php?action=startchatsession",
	  cache: false,
	  dataType: "json",
	  success: function(data) {
 
		username = data.username;
		chat_photo = data.photo;
		chatHiddenImg(username,data.photo);
		$.each(data.items, function(i,item){
			if (item)	{ // fix strange ie bug

				chatboxtitle = item.f;

				if ($("#chatbox_"+chatboxtitle).length <= 0) {
					createChatBox(item,1);
				}
				
				if (item.s == 1) {
					item.f = username;
				}

				if (item.s == 2) {
					$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxinfo" title="'+item.m+'">'+item.m+'</span></div>');
					$('.chatboxinfo').timeago();
				} else {
					$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+chatImg(item.f)+'</span><span class="chatboxmessagecontent">'+chatnl2br(item.m)+'</span><div style="clear:both;"></div></div>');
				}
			}
		});
		
		for (i=0;i<chatBoxes.length;i++) {
			chatboxtitle = chatBoxes[i];
			$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
			g_chatheartbeatTO1 = setTimeout('$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);', 100); // yet another strange ie bug
		}
	
		g_chatheartbeatTO2 = setTimeout('chatHeartbeat();',chatHeartbeatTime);
		
	}});
}

function chatImg(id)
{
	if($('#chatHidden .hImg-'+id).length > 0)
	{
		return $('#chatHidden .hImg-'+id).html();
	}
	else
	{
		return '';
		
	}
}

/**
 * Cookie plugin
 *
 * Copyright (c) 2006 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};
