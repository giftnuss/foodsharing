<?php 

if(S::may())
{
	go('/?page=dashboard');
}

addJs('
	if(isMob())
	{
		$("#ismob").val("1");
	}
	$(window).resize(function(){
		if(isMob())
		{
			$("#ismob").val("1");
		}
		else
		{
			$("#ismob").val("0");
		}
	});
	
	$("#login-form").submit(function(ev){
		ev.preventDefault();
		showLoader();
		$("body").append(\'<div id="bbsubmitter"><form target="bbframe" action="http://forum.lebensmittelretten.de/ucp.php?mode=login" method="post"><input type="hidden" name="username" value="\'+$("#email_adress").val()+\'" /><input type="hidden" name="password" value="\'+$("#password").val()+\'" /><input type="hidden" name="login" value="Login" /><input type="hidden" name="redirect" value="/success.json" /><input type="hidden" name="autologin" value="1" /></form><iframe name="bbframe" src="nix.html" frameborder="0" scrolling="no" style="width:1px;height:1px" id="bbframe"><iframe></div>\');
		$("#bbsubmitter form").submit();
		setTimeout(function(){
			$("#bbsubmitter form").submit();
			$("#bbframe").load(function(){
				$("#login-form").unbind("submit");
				$("#login-form").submit();
			});
		},1000);
		setTimeout(function(){
			$("#login-form").unbind("submit");
			$("#login-form").submit();
		},5000);
		
	});

');

addContent(v_form('Login',array(
	v_form_text('email_adress'),
	v_form_passwd('password'),
	v_form_hidden('ismob', '0').
	'<div class="ui-padding">
		<a href="/?p=passwordReset">Passwort vergessen?</a>
	</div>'
),array(
	'dialog' => true,
	'noclose' => true	
)));


function handleLogin()
{
	global $db;

	if($db->login($_POST['email_adress'],$_POST['password']))
	{
		$db->add_login(array(
			'foodsaver_id' => fsId(),
			'ip' => $_SERVER['REMOTE_ADDR'],
			'time' => date('Y-m-d H:i:s'),
			'agent' => $_SERVER['HTTP_USER_AGENT']
		));
		info(s('login_success'));
		
		if(isset($_POST['ismob']))
		{
			$_SESSION['mob'] = (int)$_POST['ismob'];
		}
		
		require_once 'lib/Mobile_Detect.php';
		$mobdet = new Mobile_Detect();
		if($mobdet->isMobile())
		{
			$_SESSION['mob'] = 1;
		}
		
		if(strpos($_SERVER['HTTP_REFERER'],URL_INTERN) !== false || isset($_GET['logout']))
		{
			if(isset($_GET['ref']))
			{
				go(urldecode($_GET['ref']));
			}
			go(str_replace('?page=login&logout','?page=dashboard',$_SERVER['HTTP_REFERER']));
		}
		else
		{
			go('?page=dashboard');
		}
		
		
	}
	else
	{
		error('Falsche Zugangsdaten');
	}
}
?>