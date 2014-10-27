<?php 
/**
 * Automatically includes classes
*
* @throws Exception
*
* @param  string $class_name  Name of the class to load
* @return void
*/

error_reporting(E_ALL);
ini_set('display_errors','1');

require_once 'config.inc.php';
require_once 'lib/func.inc.php';
require_once 'lib/Session.php';

//session_init();
S::init();

require_once 'lib/db.class.php';
require_once 'lib/Caching.php';
require_once 'lib/Manual.class.php';
require_once 'lang/DE/de.php';
require_once 'lib/view.inc.php';
require_once 'lib/minify/JSMin.php';

error_reporting(E_ALL);



if(isset($_GET['logout']))
{
	$_SESSION['client'] = array();
	unset($_SESSION['client']);
}

$content_main = '';
$content_right = '';
$content_left = '';;
$content_top = '';
$content_bottom = '';

$content_left_width = 5;
$content_right_width = 6;

$g_template = 'default';
$content_overtop = '';
$js = '';
$g_js_func = '';
$g_head = '';
$g_title = array('foodsharing');
$g_bread = array();

$g_data = getPostData();

$g_form_valid = true;
$g_ids = array();
$g_script = array();
$g_css = array();
$g_add_css = '';
$hidden = '';
$db = new ManualDb();

addCss('/fonts/alfaslabone/stylesheet.css',true);
addCss('/css/font-awesome.min.css',true);
addCss('/css/jquery-ui.css',true);
addCss('/css/jMenu.jquery.css',true);
addCss('/js/fancybox/jquery.fancybox.css',true);
addCss('/css/style.css',true);
addCss('/css/content.css',true);
addCss('/css/jquery.Jcrop.min.css',true);
addCss('/js/tagedit/css/jquery.tagedit.css',true);
addCss('/css/chat.css',true);
addCss('/css/jquery.switchButton.css',true);
addCss('/css/info.css',true);
addCss('/css/icons.css',true);

//addHead('<script src="'.PROTOCOL.'://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>');
//addHead('<script src="'.PROTOCOL.'://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>');
//addHead('<script src="/js/jquery.1.11.0.js"></script>');
//addHead('<script src="/js/jquery-ui-1.10.4.js" /></script>');

addScript('/js/jquery.1.11.0.js',true);
addScript('/js/jquery-ui-1.10.4.js',true);

addHead('<link rel="stylesheet" href="/css/pure/pure.min.css">
    <!--[if lte IE 8]>
        <link rel="stylesheet" href="/css/pure/grids-responsive-old-ie-min.css">
    <![endif]-->
    <!--[if gt IE 8]><!-->
        <link rel="stylesheet" href="/css/pure/grids-responsive-min.css">
    <!--<![endif]-->');

addScript('/js/jquery-ui-addons.js',true);
addScript('/js/tablesorter/jquery.tablesorter.min.js',true);
addScript('/js/jMenu.jquery.js',true);
addScript('/js/fancybox/jquery.fancybox.pack.js',true);
addScript('/js/jquery.Jcrop.min.js',true);
addScript('/js/tagedit/js/jquery.autoGrowInput.js',true);
addScript('/js/tagedit/js/jquery.tagedit.js',true);
addScript('/js/timeago.js',true);
addScript('/js/autolink.js',true);
addScript('/js/js-time-format.js',true);
addScript('/js/jquery.slimscroll.min.js',true);
//addScript('js/typeahead.js',true);
addScript('/js/underscore.js',true);
addScript('/js/underscore.string.js',true);
addScript('/js/script.js',true);
addScript('/js/instant-search.js',true);
addScript('/js/conv.js',true);
addScript('/js/info.js',true);
addScript('/js/storage.js',true);

//scriptCompress();
//cssCompress();

addHidden('<a id="'.id('fancylink').'" href="#fancy">&nbsp;</a>');
addHidden('<div id="'.id('fancy').'"></div>');

addHidden('<div id="u-profile"></div>');
addHidden('<ul id="hidden-info"></ul>');
addHidden('<ul id="hidden-error"></ul>');
addHidden('<div id="sendMail">'.v_form_text('Betreff').''.v_form_textarea('Nachricht').'<input type="hidden" id="sendmail-fs-id" value="0" />		</div>');
addHidden('<div id="comment">'.v_form_textarea('Kommentar').'<input type="hidden" id="comment-id" name="comment-id" value="0" /><input type="hidden" id="comment-name" name="comment-name" value="0" /></div>');
addHidden('<div id="dialog-confirm" title="Wirklich l&ouml;schen?"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><span id="dialog-confirm-msg"></span><input type="hidden" value="" id="dialog-confirm-url" /></p></div>');
addHidden('<div id="uploadPhoto"><form method="post" enctype="multipart/form-data" target="upload" action="xhr.php?f=addPhoto"><input type="file" name="photo" onchange="uploadPhoto();" /> <input type="hidden" id="uploadPhoto-fs_id" name="fs_id" value="" /></form><div id="uploadPhoto-preview"></div><iframe name="upload" width="1" height="1" src=""></iframe></div>');
//addHidden('<audio id="xhr-chat-notify"><source src="img/notify.ogg" type="audio/ogg"><source src="img/notify.mp3" type="audio/mpeg"><source src="img/notify.wav" type="audio/wav"></audio>');

addHidden('<div id="fs-profile"></div>');

$user = '';
$g_body_class = '';
if(S::may())
{
	if(isset($_GET['uc']))
	{
		if(fsId() != $_GET['uc'])
		{
			$db->logout();
			goLogin();
		}
	}
	
	$g_body_class = ' class="loggedin"';
	$user = 'user = {id:'.(int)fsId().'};';
	
	/*
	 * little check for chat messages
	 */
	
	$user .= 'conv.init();';
	
	if(getPage() != 'msg' && ($chats = S::get('activechats')))
	{
		if(is_array($chats))
		{			
			foreach ($chats as $c)
			{
				$user .= ' conv.appendChatbox('.$c['id'].','.$c['min'].'); ';
			}

		}
	}
}


addJs('
	'.$user.'
	$("#mainMenu > li > a").each(function(){
		if(parseInt(this.href.length) > 2 && this.href.indexOf("'.getPage().'") > 0)
		{
			$(this).parent().addClass("active").click(function(ev){
				//ev.preventDefault();
			});
		}
	});
		
	$("#fs-profile-rate-comment").dialog({
		modal: true,
		title: "",
		autoOpen: false,
		buttons: 
		[
			{
				text: "Abbrechen",
				click: function(){
					$("#fs-profile-rate-comment").dialog("close");
				}
			},
			{
				text: "Absenden",
				click: function(){
					ajreq("rate",{app:"profile",type:2,id:$("#profile-rate-id").val(),message:$("#fsprofileratemsg").val()});
				}
			}
		]
	}).siblings(".ui-dialog-titlebar").remove();;
');
addHidden('<div id="fs-profile-rate-comment">'.v_form_textarea('fs-profile-rate-msg',array('desc'=>'...')).'</div>');

//$mobilemenu = getMobileMenu();

if(!S::may())
{
	addJs('clearInterval(g_interval_newBasket);');
}
else
{
	addJs('user.token = "'.S::user('token').'";');
}
/*
 * Browser location abfrage nur einmal dann in session speichern
 */
if($pos = S::get('blocation'))
{
	addJsFunc('
		function getBrowserLocation(success)
		{
			success({
				lat:'.floatval($pos['lat']).',
				lon:'.floatval($pos['lon']).'
			});
		}
	');
}
else
{
	addJsFunc('
		function getBrowserLocation(success)
		{
			if(navigator.geolocation)
			{
				navigator.geolocation.getCurrentPosition(function(pos){
					ajreq("savebpos",{app:"map",lat:pos.coords.latitude,lon:pos.coords.longitude});
					success({
						lat: pos.coords.latitude,
						lon: pos.coords.longitude
					});
				});
			}
		}
	');
}

/*
addHead('
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push([\'_setAccount\', \'UA-43313114-1\']);
  _gaq.push([\'_setDomainName\', \'lebensmittelretten.de\']);
  _gaq.push([\'_trackPageview\']);

  (function() {
    var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
    ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>		
');
*/
/*
addHead('<script type=\'text/javascript\'>

var _ues = {
host:\'foodsharingev.userecho.com\',
forum:\'22413\',
lang:\'de\',
tab_corner_radius:5,
tab_font_size:20,
tab_image_hash:\'ZmVlZGJhY2s%3D\',
tab_chat_hash:\'Y2hhdA%3D%3D\',
tab_alignment:\'left\',
tab_text_color:\'#FFFFFF\',
tab_text_shadow_color:\'#00000055\',
tab_bg_color:\'#57A957\',
tab_hover_color:\'#F4A631\'
};

(function() {
    var _ue = document.createElement(\'script\'); _ue.type = \'text/javascript\'; _ue.async = true;
    _ue.src = (\'https:\' == document.location.protocol ? \'https://\' : \'http://\') + \'cdn.userecho.com/js/widget-1.4.gz.js\';
    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(_ue, s);
  })();

</script>');
*/
?>
