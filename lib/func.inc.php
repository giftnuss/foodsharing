<?php 
function __autoload($class_name)
{
	$first = substr($class_name,0,1);
	
	$folder = 'flourish';
	switch ($first)
	{
		case 'f' : $folder = 'flourish'; break;
		case 'v' : $folder = 'views'; break;
	}

	$file = ROOT_DIR . '/lib/' . $folder . '/' . $class_name . '.php';
	
	if (file_exists($file)) {
		include $file;
		return;
	}
	else
	{
		debug('file not loadable: '.$file);
	}
}

define('CNT_MAIN',0);
define('CNT_RIGHT',1);
define('CNT_TOP',2);
define('CNT_BOTTOM',3);
define('CNT_LEFT',4);
define('CNT_OVERTOP',5);
$g_debug = array();
$g_user_menu = array();
function jsonSafe($str)
{
	if((string)$str == '' || !is_string($str))
	{
		return '';
	}
	return htmlentities( (string)$str.'', ENT_QUOTES, 'utf-8', FALSE);
}

function loadView($view = 'core')
{
	require_once 'app/'.$view.'/'.$view.'.view.php';
	
	if($view = 'core')
	{
		$view = 'View';
	}
	else
	{
		$view = ucfirst($view).'View';
	}
	
	return new $view();
	
}

function addContent($new_content,$place = CNT_MAIN)
{
	global $content_main;
	global $content_right;
	global $content_left;
	global $content_bottom;
	global $content_top;
	global $content_overtop;
	
	switch($place)
	{
		case CNT_MAIN :
			
			$content_main .= $new_content;
			break;
		case CNT_RIGHT :
			
			$content_right .= $new_content;
			break;
			
		case CNT_TOP :
			$content_top .= $new_content;
			break;
			
		case CNT_BOTTOM :
			
			$content_bottom .= $new_content;
			break;
			
		case CNT_LEFT :
					
			$content_left .= $new_content;
			break;
			
		case CNT_OVERTOP :
					
			$content_overtop .= $new_content;
			break;
		
		default:
			break;
	}
}

function abhm($id)
{
	$arr = array(
				1 => array('id'=>1,'name'=> '1-3kg'),
				2 => array('id'=>2,'name'=> '3-5kg'),
				3 => array('id'=>3,'name'=> '5-10kg'),
				4 => array('id'=>4,'name'=> '10-20kg'),
				5 => array('id'=>5,'name'=> '20-30kg'),
				6 => array('id'=>6,'name'=> '40-50kg'),
				7 => array('id'=>7,'name'=> 'mehr als 50kg')
			);
	
	if(isset($arr[$id]))
	{
		return $arr[$id]['name'];
	}
	
	return false;
}

function niceDateShort($ts)
{
	if(date('Y-m-d',$ts) == date('Y-m-d'))
	{
		return s('today').' '.date('H:i',$ts);
	}
	else
	{
		return date('n.m.Y. H:i',$ts);
	}
}

function niceDate($ts)
{
	$pre = '';
	$date = new fDate($ts);
	
	if($date->eq('today'))
	{
		$pre = s('today').', ';
	}
	else if($date->eq('tomorrow'))
	{
		$pre = s('tomorrow').', ';
	}
	else if($date->eq('-1 day'))
	{
		$pre = s('yesterday').', ';
	}
	else
	{
		$days = getDow();
		$pre = $days[date('w',$ts)].', '.(int)date('d',$ts).'. '.s('smonth_'.date('n',$ts)).', ';
	}
	
	return $pre.date('H:i',$ts).' '.s('clock');
}

function incLang($id)
{
	global $g_lang;
	include ROOT_DIR.'lang/DE/'.$id.'.lang.php';
}

function orgaGlocke($msg,$title,$url = '')
{
	global $db;
	$fs = $db->getOrgateam();
	$db->addGlocke($fs, $msg,$title,$url);
}
function glocke($msg)
{
	
}
function s($id)
{
	global $g_lang;
	
	if(isset($g_lang[$id])) return $g_lang[$id];
	else return $id;
}
function format_d($ts)
{
	return date('d.m.Y',$ts);
}

function format_db_date($date)
{
	$part = explode('-', $date);
	
	return (int)$part[2].'. '.niceMonth((int)$part[1]);
}

function niceMonth($month)
{

	return s('month_'.$month);
}

function format_time($time)
{
	$p = explode(':', $time);
	if(count($p) >= 2)
	{
		return (int)$p[0].'.'.$p[1].' Uhr';
	}
	else
	{
		return '';
	}
}

$g_has_reconnected = false;

function getLatLon($anschrift,$plz,$stadt = '',$curl = false)
{
	global $g_has_reconnected;
	$address = urlencode($anschrift.', '.$plz.', '.$stadt.', Deutschland');
	
	$region = "DE";
	
	$url = "http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=DE&language=de";
	
	if(!$curl)
	{
		$json = file_get_contents($url);
	}
	else
	{
		$bot = new Bot();
		$bot->go($url);
		$json = $bot->getHtml();
	}
	//echo $json;
	
	$decoded = json_decode($json,true);
	
	if(isset($decoded['status']) && $decoded['status'] == 'OVER_QUERY_LIMIT')
	{
		if(!$g_has_reconnected)
		{
			$g_has_reconnected = true;
			//reconnect();
			sleep(2);
		}
		else
		{
			sleep(2);
		}
	}
	
	$lat = '';
	$lon = '';
	
	foreach ($decoded['results'] as $d)
	{
		$check = false;
		foreach ($d['address_components'] as $c)
		{
			if($c['long_name'] == $plz)
			{
					$check = true;
			}
		}
		
		if($check)
		{
			return $d['geometry']['location'];
			break;
		}
	}
	/*
	echo $plz.' '.$anschrift;
	echo '<pre>';
	print_r($decoded);
	echo '</pre>';
	*/
	return false;
}

function domainAvailable ( $strDomain )
{
	$rCurlHandle = curl_init ( $strDomain );

	curl_setopt ( $rCurlHandle, CURLOPT_CONNECTTIMEOUT, 4 );
	curl_setopt ( $rCurlHandle, CURLOPT_HEADER, TRUE );
	curl_setopt ( $rCurlHandle, CURLOPT_NOBODY, TRUE );
	curl_setopt ( $rCurlHandle, CURLOPT_RETURNTRANSFER, TRUE );

	$strResponse = curl_exec ( $rCurlHandle );

	curl_close ( $rCurlHandle );

	if ( !$strResponse )
	{
		return FALSE;
	}

	return TRUE;
}

function ts_day($ts)
{
	$days = getDow();
	return $days[date('w')];
}
function ts_time($ts)
{
	return date('H:i',$ts).' Uhr';
}

function msgTime($ts)
{
	$cur = time();
	$diff = $cur - $ts;
	
	if($diff < 600)
	{
		// letzte 10 minuten
		return s('currently');
	}
	elseif($diff < 86400)
	{
		// heute noch
		return sv('today_time',ts_time($ts));
	}
	elseif($diff < 604800)
	{
		// diese woche noch
		return ts_day($ts).', '.ts_time($ts);
	}
	else
	{
		return s('before_one_week');
	}
}

function makeThumbs($pic)
{
	if(!file_exists(ROOT_DIR.'images/mini_q_'.$pic) && file_exists(ROOT_DIR.'images/'.$pic))
	{
		copy(ROOT_DIR.'images/'.$pic, ROOT_DIR.'images/mini_q_'.$pic);
		copy(ROOT_DIR.'images/'.$pic, ROOT_DIR.'images/med_q_'.$pic);
		copy(ROOT_DIR.'images/'.$pic, ROOT_DIR.'images/q_'.$pic);
		
		$image = new fImage(ROOT_DIR.'images/mini_q_'.$pic);
		$image->cropToRatio(1, 1);
		$image->resize(35, 35);
		$image->saveChanges();
		
		$image = new fImage(ROOT_DIR.'images/med_q_'.$pic);
		$image->cropToRatio(1, 1);
		$image->resize(75, 75);	
		$image->saveChanges();
		
		$image = new fImage(ROOT_DIR.'images/q_'.$pic);
		$image->cropToRatio(1, 1);
		$image->resize(150, 150);
		$image->saveChanges();
	}
}

function handleTagselect($id)
{
	global $g_data;
	$recip = array();
	if(isset($g_data[$id]) && is_array($g_data[$id]))
	{		
		foreach ($g_data[$id] as $key => $r)
		{
			$part = explode('-', $key);
			$recip[$part[0]] = $part[0];
		}
	}
	
	$g_data[$id] = $recip;
}

function format_dt($ts)
{
	return date('d.m.Y H:i',$ts).' Uhr';
}
function format_day($dow)
{
	$days = getDow();
	return $days[$dow];
}
function sv($id,$var)
{
	global $g_lang;
	if(is_array($var))
	{
		$search = array();
		$replace = array();
		foreach ($var as $key => $value)
		{
			$search[] = '{'.$key.'}';
			$replace[] = $value;
		}
		return str_replace($search, $replace, $g_lang[$id]);
	}
	else 
	{
		return str_replace('{var}', $var, $g_lang[$id]);
	}
	
}
function addBread($name,$href = '')
{
	global $g_bread;
	$g_bread[] = array('name' => $name,'href'=>$href);
}
function getBread()
{
	global $g_bread;
	$out = '';
	if(!empty($g_bread))
	{
		$last_key = (count($g_bread)-1);
		$out = '
	<div class="pure-g">
		<div class="pure-u-1">
			<ul class="bread inside">';
		foreach ($g_bread as $key => $p)
		{
			if($key == $last_key)
			{
				$out .= '
				<li class="last">'.$p['name'].'</li>';
			}
			else
			{
				$out .= '
				<li><a href="'.$p['href'].'">'.$p['name'].'</a></li>';
			}
		}
		$out .= '
			</ul>
			<div class="clear"></div>
		</div>
	</div>';
	}
	
	return $out;
}

function setEditData($data)
{
	global $g_data;
	$g_data = $data;
}

function getAction($a)
{
	if(isset($_GET['a']) && $_GET['a'] == $a)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function pageLink($page,$id, $action = '')
{

	if(!empty($action))
	{
		$action = '&a='.$action;
	}
	return array('href' => '?page='.$page.$action,'name' => s($id));
}

function getActionId($a)
{
	if(isset($_GET['a']) && $_GET['a'] == $a && isset($_GET['id']) && (int)$_GET['id'] > 0)
	{		
		return (int)$_GET['id'];
	}
	else
	{
		return false;
	}
}

function getDbValues($id)
{
	global $db;
	$func = 'get_'.str_replace('_id', '', $id);
	if(method_exists($db, $func))
	{
		return $db->$func();
	}
	else 
	{
		return false;
	}
}

function getContent($name)
{
	global $content;
	global $right;
	global $js; 
	global $db;
	
	include 'control/'.$name.'.php';
}

function isBotForA($bezirk_ids)
{
	if(isBotschafter() && is_array($bezirk_ids))
	{
		foreach ($_SESSION['client']['botschafter'] as $b)
		{
			foreach ($bezirk_ids as $bid)
			{
				if($b['bezirk_id'] == $bid)
				{
					return true;
					break;
				}
			}
			
		}
	}

	return false;
}

function isBotFor($bezirk_id)
{
	if(isBotschafter())
	{
		foreach ($_SESSION['client']['botschafter'] as $b)
		{
			if($b['bezirk_id'] == $bezirk_id)
			{
				return true;
				break;
			}
		}
	}
	
	return false;
}

function isBotschafter()
{

	if(isset($_SESSION['client']['botschafter']))
	{
		return true;
	}
	return false;
}

function isOrgaTeam()
{
	if(isset($_SESSION['client']['group']['orgateam']))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function getMobileMenu()
{	
	addJs('
		$("#mobilemenu").bind("change",function(){
			if($(this).val() != "")
			{
				showLoader();
				goTo($(this).val());
				
			}
		});		
	');
	
	
	
	$out = '
			<select id="mobilemenu">
				<option class="famenu" value="dashboard" selected="selected">&#xf0c9;</option>
				<option value="/">Home</option>
				<option value="/karte">Karte</option>';
	
	$out .= '
				<option value="/?page=fairteiler">Fair-Teiler</option>
				<option value="/?page=message">Interne Nachrichten</option>
				<option value="/?page=mailbox">E-Mail Postfächer</option>
				<option value="/news">Blog</option>
				<option value="/?page=listDocument">Dokumente</option>
				<option value="/?page=bcard">Persönliche Visitenkarte</option>
				<option value="/?page=listFaq">FAQs</option>
	
						';
	if(isBotschafter())
	{
		$out .= '
				<option value="/?page=email">E-Mail Verteiler</option>';
	}
	
	$bez = '';
	$ag = '';
	if(isset($_SESSION['client']['bezirke']) && !empty($_SESSION['client']['bezirke']))
	{
		foreach ($_SESSION['client']['bezirke'] as $i => $bezirk)
		{
			if(($bezirk['type'] != 7))
			{
				$bez .= '<option value="/?page=bezirk&bid='.$bezirk['id'].'&sub=forum">'.$bezirk['name'].'</option>';
			}
			else
			{
				$ag .= '<option value="/?page=bezirk&bid='.$bezirk['id'].'&sub=forum">'.$bezirk['name'].'</option>';
			}
		}
	}
	if(isset($_SESSION['client']['betriebe']) && !empty($_SESSION['client']['betriebe']))
	{
		$out .= '
		<optgroup label="Deine Betriebe">';
		foreach ($_SESSION['client']['betriebe'] as $cb)
		{
			$out .= '
				<option value="/?page=fsbetrieb&id='.$cb['id'].'">'.$cb['name'].'</option>';
		}
		$out .= '
		</optgroup>';
	
	}
	
	if(!empty($bez))
	{
		$out .= '
		<optgroup label="Deine Bezirke">
			'.$bez.'
		</optgroup>';
	}
	if(!empty($ag))
	{
		$out .= '
		<optgroup label="Deine Gruppen">
			'.$ag.'
		</optgroup>';
	}
	
	if(isOrgateam())
	{
		$out .= '
			<optgroup label="Orga">
				<option value="/?page=region">Regionen verwalten</option>
				<option value="/?page=newarea">Regionswünsche von Foodsavern</option>
				<option value="/?page=foodsaver&bid=0">Alle Foodsaver</option>
				<option value="/?page=betrieb&bid=0">Alle Betriebe</option>
				<option value="/?page=email">E-Mail Verteiler</option>
				<option value="/?page=kette">Unternehmens-Ketten</option>
				<option value="/?page=faq">FAQ\'s verwalten</option>
				<option value="/?page=document">Dokumente verwalten</option>
				<option value="/?page=lebensmittel">Lebensmittel-Typen verwalten</option>
				<option value="/?page=content">Inhalte/Texte bearbeiten</option>
				<option value="/?page=mailbox&a=manage">Mailboxen</option>
				<option value="/?page=message_tpl">E-Mail Vorlagen</option>
			</optgroup>';
	}
	
	$out .= '
			<option value="logout">Logout</option>
			</select>';
	
	return $out;
}

function getMenu()
{
	addJs('$("#top .menu").css("display","block");');
	addJs('
			$("#mainMenu").jMenu({
				ulWidth:200,
				absoluteTop:37,
				TimeBeforeClosing : 0,
				TimeBeforeOpening : 0,
		        effects : {
		          effectSpeedOpen : 0,
		          effectSpeedClose : 0
		      	},
			});
	');
	
	addJs('
		$("#mobilemenu").bind("change",function(){
			if($(this).val() != "")
			{
				showLoader();
				goTo($(this).val());
	
			}
		});
	');
	
	if(S::may())
	{
		
		if(false)
		{
			//return getMobileMenu();
			return '';
		}
		else
		{
			
			global $db;
			//$bezirk = $db->getBezirk();
			
			$out = array(
				'default' => '',
				'mobile' => ''
			);
			
			$bezirke = '
					<li><a>Bezirke</a>
						<ul class="jmenu-bezirke">';
			
			$bezirke_mob = '
					<optgroup label="Bezirke">';
			
			$ags = '
					<li><a href="/?page=groups"><i class="fa fa-group"></i> GRUPPEN-ÜBERSICHT</a></li>
					<li class="break"><span></span></li>';
			
			$ags_mob = '
					<option value="/?page=groups">GRUPPEN-ÜBERSICHT</option>';
			
			if(isset($_SESSION['client']['bezirke']) && is_array($_SESSION['client']['bezirke']))
			{
				foreach ($_SESSION['client']['bezirke'] as $i => $bezirk)
				{
					if(($bezirk['type'] != 7))
					{
						$bezirke_a = getBezirkMenu($bezirk);
						
						$bezirke .= $bezirke_a['default'];
						$bezirke_mob .= $bezirke_a['mobile'];
					}
					else
					{
						$ags_a = getAgMenu($bezirk);
						
						$ags .= $ags_a['default'];
						$ags_mob .= $ags_a['mobile'];
					}
				}
			}
			if(!empty($ags))
			{
				$ags = '<ul class="bigmenu">'.$ags.'</ul>';
			}
			
			$ags = '<li><a href="/?page=groups">Gruppen</a>'.$ags.'</li>';
			$ags_mob = '
					<optgroup label="Gruppen">
						'.$ags_mob.'
					</optgroup>';

			$foodsaver_mob = '';
			
			if(S::may('fs'))
			{
				if(S::may('bieb'))
				{
					
				}
				
				if(S::may('bot'))
				{
					
				}
			
			}
			$bezirke .= '
				<li class="break"><span></span></li>
				<li><a href="#" onclick="becomeBezirk();return false;"><i class="fa fa-plus-circle"></i> Bezirk beitreten</a></li>
			</ul>
		</li>';
			
			$bezirke_mob .= '	
		</optgroup>';
			
			$orgamenu = getOrgaMenu();
			$betriebe = getBetriebeMenu();
			$settings = getSettingsMenu();
			
			if(!S::may('fs'))
			{
				$bezirke = '';
				$ags = '';
			}
			
			return array(
				'default' => '
						<ul id="mainMenu" class="jMenu">
							'.$orgamenu['default'].'
							<li><a href="/"><i class="fa fa-home"></i></a></li>
							<li><a class="fNiv" href="/karte"><i class="fa fa-map-marker"></i></a></li>
							<li><a><i class="fa fa-question-circle"></i></a>
                                                       	<ul>
                                                                <li><a href="/?page=listFaq">F.A.Q.</a></li>
                                                                <li><a href="http://wiki.lebensmittelretten.de/">Wiki</a></li>
                                                        </ul>
							</li>
							'.$ags.'
							'.$betriebe['default'].'
							'.$bezirke.'
							'.$settings['default'].'
						</ul>',
					
				'mobile' => '
						<select id="mobilemenu">
							<option class="famenu" value="dashboard" selected="selected">&#xf0c9;</option>
							<option value="/">Home</option>
							<option value="/?page=dashboard">Dashboard</option>
							<option value="/karte">Karte</option>
							<option value="/?page=listFaq">F.A.Q.</option>
							<option value="http://wiki.lebensmittelretten.de">Wiki</option>
							'.$settings['mobile'].'
							'.$ags_mob.'
							'.$foodsaver_mob.'
							'.$betriebe['mobile'].'
							'.$bezirke_mob.'
							
							'.$orgamenu['mobile'].'
						</select>'

			);
		}
		
	}
	else
	{
		return array(
			'default' => '
				<ul id="mainMenu" class="jMenu">
					<li><a class="fNiv" href="/"><i class="fa fa-home"></i></a></li>
					<li><a class="fNiv" href="/karte"><i class="fa fa-map-marker"></i></a></li>
					<li><a class="fNiv" href="/?page=blog">News</a></li>
					<li><a class="fNiv" href="/?page=content&sub=about">Über Uns</a>
						<ul>
							<li><a href="/?page=content&sub=partner">Partner</a></li>
							<li><a href="/?page=statistics">Statistik</a></li>
							<li><a href="/?page=content&sub=faq">F.A.Q.</a></li>
							<li><a href="/?page=content&sub=impressum">Impressum</a></li>
						</ul>
					</li>
					
					<li><a class="fNiv" href="/?page=index&sub=ratgeber">Ratgeber</a></li>
					<li><a onclick="ajreq(\'join\',{app:\'login\'});return false;" class="fNiv" href="/?page=join">Mach-Mit!</a></li>
					<!-- <li><a class="fNiv" href="/?page=login" onclick="ajreq(\'login\',{app:\'login\'});return false;">Login</a></li> -->
				</ul>',
			'mobile' => '
				<select id="mobilemenu">
					<option class="famenu" value="dashboard" selected="selected">&#xf0c9;</option>
					<option value="/">Home</option>
					<option value="/karte">Karte</option>
					<option value="/ratgeber">Ratgeber</option>
					<option value="/mach-mit">Mach-Mit!</option>
					<option value="/login">Login</option>
				</select>'
		
			);
	}
}

function preZero($i)
{
	if($i<10)
	{
		return '0'.$i;
	}
	else
	{
		return $i;
	}
}

function getDow()
{
	return  array
	(
		1 => s('monday'),
		2 => s('tuesday'),
		3 => s('wednesday'),
		4 => s('thursday'),
		5 => s('friday'),
		6 => s('saturday'),
		0 => s('sunday')
	);
}

function getBetriebeMenuOld()
{
	$out = '';
	if($_SESSION['client']['betriebe'])
	{
		$out = '
		<ul class="sub">';
		foreach ($_SESSION['client']['betriebe'] as $b)
		{
			$out .= '
			<li><a href="/?page=fsbetrieb&id='.$b['id'].'">'.$b['name'].'</a></li>';
		}
		$out .= '
		</ul>';
	}
	
	return $out;
}

function getBetriebeMenu()
{
	if(!S::may('fs'))
	{
		return array(
			'mobile' => '',
			'default' => ''
		);
	}
	$out = '';
	$out_mob = '';
	if(isset($_SESSION['client']['betriebe']) && !empty($_SESSION['client']['betriebe']))
	{
		$out = '
		<li class="jmenu-foodsaver"><a onclick="return false" class="fNiv">Betriebe</a>
			<ul>';
		$out_mob = '
		<optgroup label="Betriebe">';
		
		foreach ($_SESSION['client']['betriebe'] as $cb)
		{
			$out .= '
				<li><a href="/?page=fsbetrieb&id='.$cb['id'].'&sub=forum">'.$cb['name'].'</a></li>';
			
			$out_mob .= '
				<option value="/?page=fsbetrieb&id='.$cb['id'].'&sub=forum">'.$cb['name'].'</option>';
		}
		$out .= '
			</ul>
		</li>';
		
		$out_mob .= '
		</optgroup>';
		
	}
	
	
	$id = id('becomeBezirkChooser');
	
	$swap_msg = 'Welche Gegend soll neu angelegt werden ? ...';
	$swap = v_swapText($id.'-neu',$swap_msg);
	
	
	addHidden('
		<div id="becomeBezirk">
			<div class="popbox">
				<h3>Wähle die Region aus, in der Du aktiv werden möchtest!</h3>
				<p class="subtitle">
					Es besteht auch die Möglichkeit eine neue Region/Bezirk zu gründen, wähle bitte dennoch die enspechende übergeordnete Region (Land / Bundesland Stadt etc.) aus.
				</p>
				<div style="height:260px;">
					'.v_bezirkChildChooser($id).'
					<span id="'.$id.'-btna">Gesuchte Region ist nicht dabei</span>
					<div class="middle" id="'.$id.'-notAvail">
						<h3>Dein/e Stadt / Region / Bezirk ist nicht dabei?</h3>
						'.$swap.'
					</div>
				</div>
				<p class="bottom">
					<span id="'.$id.'-button">Speichern</span>
				</p>
			</div>
		</div>
		<a id="becomeBezirk-link" href="#becomeBezirk">&nbsp;</a>
		
	');
	
	addJs('
		$("#'.$id.'-notAvail").hide();
				
		$("#'.$id.'-btna").button().click(function(){
			
			$("#'.$id.'-btna").fadeOut(200,function(){
				$("#'.$id.'-notAvail").fadeIn();
			});
		});
				
		$("#'.$id.'-button").button().click(function(){
			if(parseInt($("#'.$id.'").val()) > 0)
			{
				
				part = $("#'.$id.'").val().split(":");
				
				if(part[1] == 5)
				{
					pulseError(\'Das ist ein Bundesland wähle bitte eine Stadt, eine Region, oder einen Bezirk aus.\');	
					return false;	
				}
				else if(part[1] == 6)
				{
					pulseError(\'Das ist ein Land wähle bitte eine Stadt, eine Region, oder einen Bezirk aus.\');	
					return false;		
				}
				else if(part[1] == 8)
				{
					pulseError(\'Das ist eine Großstadt wähle bitte eine Stadt, eine Region, oder einen Bezirk aus.\');	
					return false;		
				}
				else if(part[1] == 1 || part[1] == 9 || part[1] == 2 || part[1] == 3)
				{
					bid = part[0];
					showLoader();
					neu = "";
					if($("#'.$id.'-neu").val() != "'.$swap_msg.'")
					{
						neu = $("#'.$id.'-neu").val();
					}
					$.ajax({
						dataType:"json",
						url:"xhr.php?f=becomeBezirk",
						data: "b=" + bid + "&new=" + neu,
						success : function(data){
							if(data.status == 1)
							{
								//if(data.active == 1)
								//{
									goTo( "/?page=relogin&url=" + encodeURIComponent("/?page=bezirk&bid=" +$("#'.$id.'").val()) );
								//}
								//pulseInfo(\''.jsSafe(s('bezirk_request_successfull')).'\');
								$.fancybox.close();
							}
							if(data.script != undefined)
							{
								$.globalEval(data.script);
							}
						},
						complete:function(){
							hideLoader();
						}
					});	
				}
				else
				{
					pulseError(\'In diesen Bezirk kanndst Du Dich nicht eintragen.\');	
					return false;		
				}
			}
			else
			{
				pulseError(\'<p><strong>Du musst eine Auswahl treffen</strong></p><p>Gibt es Deine Stadt, Deinen Bezirk oder Deine region noch nicht, dann treffe die passende übergeordnete Auswahl</p><p>Also für Köln-Ehrenfeld z.B. Köln</p><p>Und schreibe die Region die neu angelegt werden soll in das Feld unten</p>\');		
			}
		});		
	');
	
	return array(
		'default' => $out,
		'mobile' => $out_mob
	);
	/*
	return '
			<li class="jmenu-foodsaver"><a class="fNiv">Foodsaver</a>
				<ul>
					<li class="arrow"></li>
					<li><a href="/?page=blog_entry">Blog</a></li>
					<li><a href="/?page=fsbetrieb">Deine Betriebe</a></li>
					<li><a href=".">Übersichts-Karte</a></li>
					<li><a href="/?page=message&a=neu&list">Mailing-Liste</a></li>
					<li><a href="/?page=listDocument">Dokumente</a></li>
					<li class="menu-bottom"><a class="menu-bottom" href="/?page=listFaq">FAQs</a></li>
				</ul>
			</li>';
			*/
}

function gAnrede($gender)
{
	return genderWord($gender, 'Lieber', 'Liebe', 'Liebe/r');
}

function logDel($data)
{
	file_put_contents(ROOT_DIR.'data/del.txt', json_encode($data)."\n<!-#-!-#-!-#-!-#-!-#-!-#-!>\n",FILE_APPEND);
}

function tplMailList($tpl_id, $to, $from = false,$attach = false)
{
	
	if(!is_array($from) && validEmail($from))
	{
		$from = array(
			'email' => $from,
			'email_name' => $from
		);
	}
	else if($from === false)
	{
		$from = array(
				'email' => DEFAULT_EMAIL,
				'email_name' => DEFAULT_EMAIL_NAME
		);
	}
	
	global $db;
	
	if(!is_object($db))
	{
		$db = new ManualDb();
	}
	
	$tpl_message = $db->getOne_message_tpl($tpl_id);
	
	$socket = new SocketClient();
	
	foreach ($to as $t)
	{	
		if(!validEmail($t['email']))
		{
			continue;
		}
		
		$mail = new SocketMail();
		$mail->setFrom($from['email'], $from['email_name']);

		
		$search = array();
		$replace = array();
		foreach ($t['var'] as $key => $v)
		{
			$search[] = '{'.strtoupper($key).'}';
			$replace[] = $v;
		}
		
		$message = str_replace($search, $replace, $tpl_message['body']);
		$subject = str_replace($search, $replace, $tpl_message['subject']);
		
		$mail->addRecipient($t['email']);
		
		$mail->setSubject($subject);
		//Read an HTML message body from an external file, convert referenced images to embedded, convert HTML into a basic plain-text alternative body
		
		if(!isset($t['token']))
		{
			$t['token'] = false;
		}
		
		$mail->setHTMLBody(emailBodyTpl($message,$t['email'],$t['token']));
		
		// playintext body
		$message = str_replace('<br />', "\r\n", $message);
		$message = strip_tags($message);
		$mail->setBody($message);
		
		if(isAdmin())
		{
			debug(array(
				'bezirk' => $from,
				'email' => $t['email'],
				'subject' => $subject,
				'msg' => $message,
				'attach' => $attach
			));
		}
		
		/*
		 *  todo: implement logic that we dont have to send one attachment multiple time to the slave db ...
		*/
		
		if($attach !== false)
		{
			foreach ($attach as $a)
			{
				$mail->addAttachment($a['path'],$a['name']);
			}
		}
		
		$socket->queue($mail);
	}
	
	$socket->send();
	$socket->close();
}

function autolink($str, $attributes=array()) {
	$attributes['target'] = '_blank';
	$attrs = '';
	foreach ($attributes as $attribute => $value) {
		$attrs .= " {$attribute}=\"{$value}\"";
	}
	$str = ' ' . $str;
	$str = preg_replace(
			'`([^"=\'>])(((http|https|ftp)://|www.)[^\s<]+[^\s<\.)])`i',
			'$1<a href="$2"'.$attrs.'>$2</a>',
			$str
	);
	$str = substr($str, 1);
	$str = preg_replace('`href=\"www`','href="http://www',$str);
	// fügt http:// hinzu, wenn nicht vorhanden
	return $str;
}

function emailBodyTpl($message, $email = false, $token = false)
{
	
	$unsubscribe = '
	<tr>
		<td height="20" valign="top" style="background-color:#FAF7E5">
			<div style="text-align:center;padding-top:10px;font-size:11px;font-family:Arial;padding:15px;color:#594129;">
				Willst Du Keine Nachrichten mehr bekommen? Du kannst Deinen unter <a style="color:#F36933" href="'.BASE_URL.'?page=settings&sub=info" target="_blank">Deinen Einstellungen</a> einstellen, welche Mails Du bekommst.
			</div>
		</td>
	</tr>';
	
	if($email !== false && $token !== false)
	{
		$unsubscribe = '
		<tr>
			<td height="20" valign="top" style="background-color:#FAF7E5">
				<div style="text-align:center;padding-top:10px;font-size:11px;font-family:Arial;padding:15px;color:#594129;">
					Möchtest Du keinen Newsletter mehr erhalten? <a style="color:#F36933" href="http://www.lebensmittelretten.de/unsubscribe/'.$token.'-'.$email.'" target="_blank">Klicke hier zum Abbestellen.</a> Du kannst Deinen unter <a style="color:#F36933" href="http://www.lebensmittelretten.de/freiwillige/?page=settings&sub=info" target="_blank">Deinen Einstellungen</a> einstellen, welche Mails Du bekommst.
				</div>
			</td>
		</tr>';
	}
	
	$message = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $message);
	
	$search = array('<a','<td','<li');
	$replace = array('<a style="color:#F36933"','<td style="font-size:13px;font-family:Arial;color:#31210C;"','<li style="margin-bottom:11px"');
	
	return '<html><head><style type="text/css">a{text-decoration:none;}a:hover{text-decoration:underline;}a.button{display:inline-block;padding:6px 16px;border:1px solid #FFFFFF;background-color:#4A3520;color:#FFFFFF !important;font-weight:bold;border-radius:8px;}a.button:hover{border:1px solid #4A3520;background-color:#ffffff;color:#4A3520 !important;text-decoration:none !important;}.border{padding:10px;border-top:1px solid #4A3520;border-bottom:1px solid #4A3520;background-color:#FFFFFF;}</style></head>
	<body style="margin:0;padding:0;">
		<div style="background-color:#F1E7C9;border:1px solid #628043;border-top:0px;padding:2%;padding-top:0;margin-top:0px;">

<table width="100%" style="margin-bottom:10px;margin-top:-2px;">
<tr>
				<td valign="top" height="30" style="background-color:#4A3520">
					<div style="padding:5px;font-size:13px;font-family:Arial;color:#FAF7E5;overflow:hidden;" align="left">
						<a style="display:block;color:#FAF7E5;text-decoration:none;" href="http://www.lebensmittelretten.de/" target="_blank">
							<span style="margin-left:10px;font-size:20px;font-family:Arial Black, Arial;font-weight:bold;color:#FAF7E5;letter-spacing:-1px;">food</span><span style="margin-right:10px;font-size:20px;font-family:Arial Black, Arial;font-weight:bold;color:#4D971E;letter-spacing:-1px">sharing</span> <span style="font-style:italic">Lebensmittelretten<span style="color:#F36933">.</span>de</span>
						</a>
					</div>
				</td></tr>
</table>
			<table height="100%" width="100%">
				<tr>
				<td valign="top" style="background-color:#FAF7E5">
					<div style="padding:5px;font-size:13px;font-family:Arial;padding:15px;color:#31210C;">
						'.str_replace($search,$replace,$message).'
					</div>
				</td>
				</tr>
				'.$unsubscribe.'
			</table>
		</div>
	</body>
</html>';
}

function tplMail($tpl_id,$to,$var = array(),$from_email = false,$from_email = false)
{
	global $db;
	$mail = new SocketMail();
	
	if(!is_object($db))
	{
		$db = new ManualDb();
	}
	/*
	if($from_email !== false)
	{
		$mail->setFrom($from_email);
	}
	else 
	{
		$from = $db->getBezirkMail($from_bezirk_id);
		$mail->setFrom($from['email'],$from['email_name']);
	}
	*/
	
	if($from_email !== false && validEmail($from_email))
	{
		$mail->setFrom($from_email);
	}
	else
	{
		$mail->setFrom(DEFAULT_EMAIL,DEFAULT_EMAIL_NAME);
	}
	
	$message = $db->getOne_message_tpl($tpl_id);
	
	$search = array();
	$replace = array();
	foreach ($var as $key => $v)
	{
		$search[] = '{'.strtoupper($key).'}';
		$replace[] = $v;
	}
	
	$message['body'] = str_replace($search, $replace, $message['body']);
	
	$message['subject'] = str_replace($search, $replace, $message['subject']);
	
	$mail->setSubject($message['subject']);
	$mail->setHtmlBody(emailBodyTpl($message['body']));
	
	// playintext body
	$body = str_replace(array('<br />','<br>','<br/>','<p>','</p>'), "\r\n", $message['body']);
	$body = strip_tags($body);
	$mail->setBody($body);
	
	$mail->addRecipient($to);
	
	$socket = new SocketClient();
	$socket->queue($mail);
	
	$socket->send();
	$socket->close();
	
	//return libmail($from, $to, $message['subject'], $message['body']);
	
}

function getSearchMenu()
{
	if(isBotschafter() || isOrgaTeam())
	{
		return '<li class="searchIcon"><a class="fNiv" href="/?page=suche">&nbsp</a></li>';
	}
}

function getOrgaMenu()
{
	if(isOrgaTeam())
	{
		return 
			array(
				'default' => '<li><a class="fNiv"><i class="fa fa-gear"></i></a>
				    <ul class="bigmenu">
				      <li><a href="/?page=region">Regionen verwalten</a></li>
				      <li><a href="/?page=quiz">Quiz verwalten</a></li>
				      <li><a href="/?page=report&sub=uncom">Verstoßmeldungen</a></li>
					  <li><a href="/?page=newarea">Regionswünsche von Foodsavern</a></li>
					  <li><a href="/?page=foodsaver&bid=0">Alle Foodsaver</a></li>
					  <li><a href="/?page=betrieb&bid=0">Alle Betriebe</a></li>
				      <li><a href="/?page=email">E-Mail Verteiler</a></li>
					  <li><a href="/?page=kette">Unternehmens-Ketten</a></li>
					  <li><a href="/?page=faq">FAQ\'s verwalten</a></li>
					  <li><a href="/?page=lebensmittel">Lebensmittel-Typen verwalten</a></li>
					  <li><a href="/?page=content">Inhalte/Texte bearbeiten</a></li>
					  <li><a href="/?page=mailbox&a=manage">Mailboxen</a></li>
					  <li class="menu-bottom"><a class="menu-bottom" href="/?page=message_tpl">E-Mail Vorlagen</a></li>
				    </ul>
				  </li>',
			
				'mobile' => '
					<optgroup label="Orga">
				    	<option value="/?page=region">Regionen verwalten</option>
				    	<option value="/?page=quiz">Quiz verwalten</option>
				    	<option value="/?page=report&sub=uncom">Verstoßmeldungen</option>
						<option value="/?page=newarea">Regionswünsche von Foodsavern</option>
						<option value="/?page=foodsaver&bid=0">Alle Foodsaver</option>
						<option value="/?page=betrieb&bid=0">Alle Betriebe</option>
				    	<option value="/?page=email">E-Mail Verteiler</option>
						<option value="/?page=kette">Unternehmens-Ketten</option>
						<option value="/?page=faq">FAQ\'s verwalten</option>
					 	<option value="/?page=document">Dokumente verwalten</option>
						<option value="/?page=lebensmittel">Lebensmittel-Typen verwalten</option>
						<option value="/?page=content">Öffentliche Webseiten</option>
						<option value="/?page=autokennzeichen">KFZ-Kennzeichen</option>
						<option value="/?page=mailbox&a=manage">Mailboxen</option>
					 	<option value="/?page=message_tpl">E-Mail Vorlagen</option>
				    </optgroup>'
		);
	}
	else
	{
		return array(
			'default' => '',
			'mobile' => ''
		);
	}
}

function dt($ts)
{
	return date('n. M. Y H:i',$ts).' Uhr';
}

function makeUnique()
{
	return md5(date('Y-m-d H:i:s').':'.uniqid());
}

function imgMini($file = false)
{
	return img($file);
}

function imgPortait($photo)
{

	if(!(file_exists('images/thumb_crop_'.$photo)))
	{
		return('img/portrait.png');
	}
	else
	{
		return('images/thumb_crop_'.$photo);
	}

	return v_field($p_cnt, 'Dein Foto');
}

function idimg($file = false,$size)
{
	if(!empty($file))
	{
		return 'images/'.str_replace('/', '/'.$size.'_', $file);
	}
	else
	{
		return false;
	}
}

function img($file = false,$size = 'mini',$format = 'q',$altimg = false)
{
	
	if($file === false)
	{
		$file = $_SESSION['client']['photo'];
	}
	
	//if(!empty($file) && substr($file,0,1) != '.')
	if(!empty($file) && file_exists('images/'.$file))
	{
		if(!file_exists('images/'.$size.'_'.$format.'_'.$file))
		{
			resizeImg('images/'.$file,$size,$format);
		}
		return '/images/'.$size.'_'.$format.'_'.$file;
	}
	else
	{
		if($altimg === false)
		{
			return '/img/'.$size.'_'.$format.'_avatar.png';
		}
		else
		{
			return $altimg;
		}
	}

}

function getSettingsMenu()
{
	$mailbox = '';
	if(S::get('mailbox'))
	{
		$mailbox = '<li><a href="/?page=mailbox"><i class="fa fa-envelope"></i> E-Mail Postfach</a></li>';
	}
	$default = '<li class="g_settings"><a class="fNiv corner-all" style="background-image:url('.img().');"><span>&nbsp;</span></a>
				    <ul class="jmenu-settings">
					  <li><a href="/?page=settings"><i class="fa fa-gear"></i> Einstellungen</a></li>
					  '.$mailbox.'
				      <li class="menu-bottom"><a class="menu-bottom" href="/?page=logout"><i class="fa fa-sign-out"></i> Logout</a></li>
				    </ul>
				  </li>';
	
	return array(
		'default' => $default,
		'mobile' => '
			<option value="/?page=settings">Einstellungen</option>
			<option value="/?page=logout">Logout</option>'
	);
}

function isMob()
{
	if(isset($_SESSION['mob']) && $_SESSION['mob'] == 1)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function getAgMenu($ag)
{
	
	$out_mob = '
		<option value="/?page=bezirk&bid='.$ag['id'].'&sub=forum">'.$ag['name'].'</option>';

	$out = '
		<li><a href="/?page=bezirk&bid='.$ag['id'].'&sub=forum">'.$ag['name'].'</a>
			<ul>
				<li class="menu-top"><a class="menu-top" href="/?page=bezirk&bid='.$ag['id'].'&sub=forum">Forum</a></li>
				<li class="menu-top"><a class="menu-top" href="/?page=bezirk&bid='.$ag['id'].'&sub=events">Termine</a></li>';
	
	if(isBotFor($ag['id']))
	{
		$out .= '
			<li><a href="/?page=groups&sub=edit&id='.(int)$ag['id'].'">Gruppe/Mitglieder verwalten</a></li>
			<li><a href="/?page=message&a=neu&list&bid='.$ag['id'].'">Mailing-Liste</a></li>';
	}
	
	$out .= '
			</ul>';
	
	return array(
		'default' => $out . '</li>',
		'mobile' => $out_mob
	);
}

function getBezirkMenu($bezirk)
{
	global $db;
	
	$out = '<li><a href="/?page=bezirk&bid='.$bezirk['id'].'&sub=forum">'.$bezirk['name'].'</a>
			<ul>
				<li class="menu-top"><a class="menu-top" href="/?page=bezirk&bid='.$bezirk['id'].'&sub=forum">Forum</a></li>
				<li class="menu-top"><a class="menu-top" href="/?page=bezirk&bid='.$bezirk['id'].'&sub=fairteiler">Fair-Teiler</a></li>
				<li class="menu-top"><a class="menu-top" href="/?page=bezirk&bid='.$bezirk['id'].'&sub=events">Termine</a></li>';
	
	$out_mob = '<option value="/?page=bezirk&bid='.$bezirk['id'].'&sub=forum">'.$bezirk['name'].'</option>';
	
	if(S::may('fs'))
	{
		$out .= '
				<li class="menu-top"><a class="menu-top" href="/?page=betrieb&bid='.$bezirk['id'].'">Betriebe</a></li>';
	}
	
	if(isBotFor($bezirk['id']))
	{	
		$out .= '
			<li><a href="/?page=foodsaver&bid='.$bezirk['id'].'">Foodsaver</a></li>
			<li><a href="/?page=message&a=neu&list&bid='.$bezirk['id'].'">Mailing-Liste</a></li>
			<li class="menu-bottom"><a class="menu-bottom" href="/?page=passgen&bid='.$bezirk['id'].'">Ausweise / Verifizierungen</a></li>';
	}
	
	$out .= '
			</ul>';
	
	return array(
		'default' => $out . '</li>',
		'mobile' => $out_mob
	);
}

function id($name)
{
	global $g_ids;
	
	$id = makeId($name,$g_ids);
	
	$g_ids[$id] = true;
	
	return $id;
}

function jsValidate($option,$id,$name)
{
	$out = array('class'=>'','msg' => array());
	
	if(isset($option['required']))
	{
		$out['class'] .= ' required';
		if(isset($option['required']['msg']))
		{
			
		}
		else
		{
			$out['msg']['required'] = $name.' darf nciht leer sein';
		}
	}
	
	return $out;
}

function handleAttach($name)
{
	if(isset($_FILES[$name]) && $_FILES[$name]['size'] > 0)
	{
		$error = 0;
		$datei = $_FILES[$name]['tmp_name'];
		$size = $_FILES[$name]['size'];
		$datein = $_FILES[$name]['name'];
		$datein = strtolower($datein);
		$datein = str_replace('.jpeg', '.jpg', $datein);
		$dateiendung = strtolower(substr($datein, strlen($datein)-4, 4));
		
		$new_name = uniqid().$dateiendung;
		move_uploaded_file($datei, './data/attach/'.$new_name);
		
		return array
		(
			'name' => $datein,
			'path' => './data/attach/'.$new_name,
			'uname' => $new_name,
			'mime' => mime_content_type('./data/attach/'.$new_name),
			'size' => $size
		);
	}
	else
	{
		return false;
	}
}

function checkInput($option,$name)
{
	
	$class = '';
	if(isset($option['required']))
	{
		$class .= ' required';
	}
	if(isset($option['required']) || isset($option['validate']))
	{
		if(isset($_POST) && !empty($_POST))
		{
			if(isset($option['required']) && empty($value))
			{
				error($option['required']);
				$class .= ' empty';
			}
			if(isset($option['validate']))
			{
				foreach ($option['validate'] as $v)
				{
					$func = 'valid'.ucfirst($v);
					if(!$func($value))
					{
						$class .= ' error-'.$v;
					}
				}
			}
		}
	}
	
	if(!empty($class))
	{
		$class .= ' input-error';
	}	
	return $class;
}

function getPost($id)
{
	return $_POST[$id];
}

function getPostData()
{
	if(isset($_POST))
	{
		return $_POST;
	}
	else
	{
		return array();
	}
}

function getValue($id)
{
	global $g_data;
	
	if(isset($g_data[$id]))
	{
		return $g_data[$id];
	}
	else
	{
		return '';
	}
}

function jsSafe($str,$quote = "'")
{
	return str_replace(array($quote,"\n","\r"), array("\\".$quote."",'\\n',''), $str);
}

function goPage($page = false)
{
	if(!$page)
	{
		$page = getPage();
		if(isset($_GET['bid']))
		{
			$page .= '&bid='.(int)$_GET['bid'];
		}
	}
	go('?page='.$page);
}

function go($url)
{
	header('Location: '.$url);
	exit();
}

function goLogin()
{
	go('?page=login&ref=' . urlencode($_SERVER['REQUEST_URI']));
}

function goBack()
{
	header('Location: '.$_SERVER['HTTP_REFERER']);
	exit();
}

function setConfirmMsg($msg)
{
	addJs('$("#dialog-confirm-msg").html("'.$msg.'")');
}

function getBotschafterBezirk()
{
	return $_SESSION['client']['botschafter'];
}

function getBezirkId()
{	
	global $db;
	return $db->getCurrentBezirkId();
}

function getPage()
{
	return getGet('page');
}

function getGetId($name)
{
	if(isset($_GET[$name]) && (int)$_GET[$name] > 0)
	{
		return (int)$_GET[$name];
	}
	else
	{
		return false;
	}
}

function getGet($name)
{
	if(isset($_GET[$name]))
	{
		return $_GET[$name];
	}
	else
	{
		return false;
	}
}

function addGet($name,$val)
{
	$url = '';
	
	$vars = explode('&', $_SERVER['QUERY_STRING']);
	
	$i=0;
	foreach ($vars as $v)
	{
		$i++;
		$ex = explode('=', $v);
		if($ex[0] != $name)
		{
			$url .= '&'.$v;
		}
	}
	
	return $_SERVER['PHP_SELF'].'?'.substr($url, 1).'&'.$name.'='.$val;
	
	
}

function qs($txt)
{
	return $txt;
}

function safe_html($txt)
{
	return $txt;
}

function printHidden()
{
	global $hidden;
	if(!empty($hidden))
	{
		echo '<div style="display:none;">'.$hidden.'</div>';
	}
}

function addHidden($html)
{
	global $hidden;
	$hidden .= $html;
}

function makeId($text,$ids = false)
{
	$text = strtolower($text);
	str_replace(
			array('ä','ö','ü','ß',' '),
			array('ae','oe','ue','ss','_'),
			$text
	);
	$out = preg_replace('/[^a-z0-9_]/','',$text);
	
	if($ids!==false && isset($ids[$out]))
	{
		$id = $out;
		$i=0;
		while (isset($ids[$id]))
		{
			$i++;
			$id = $out.'-'.$i;
		}
		$out = $id;
	}
	
	return $out;
}

function submitted()
{
	if(isset($_POST) && !empty($_POST))
	{
		return true;
	}
	
	return false;
}

function handleForm($name)
{
	global $g_form_valid;
	$func = 'handle'.ucfirst($name);
	
	if($g_form_valid)
	{
		if(function_exists($func))
		{
			return $func();
		}
	}
	else
	{
		return false;
	}
}

function info($msg,$title = false)
{
	$t = '';
	if($title !== false)
	{
		$t = '<strong>'.$title.'</strong> ';
	}
	$_SESSION['msg']['info'][] = $msg;
}

function success($msg,$title = false)
{
	$t = '';
	if($title !== false)
	{
		$t = '<strong>'.$title.'</strong> ';
	}
	$_SESSION['msg']['success'][] = $t.$msg;
}

function error($msg,$title = false)
{
	$t = '';
	if($title !== false)
	{
		$t = '<strong>'.$title.'</strong> ';
	}
	$_SESSION['msg']['error'][] = $t.$msg;
}

function session_init()
{
	ini_set('session.use_only_cookies', 1);
	//ini_set("session.cookie_lifetime","86400");
	//@session_name('fs_session');
	@session_start();
	if(false)
	{
		$session_name = 'fs_session'; // Set a custom session name
		$secure = false; // Set to true if using https.
		$httponly = true; // This stops javascript being able to access the session id.
		
		ini_set('session.use_only_cookies', 1); // Forces sessions to only use cookies.
		$cookieParams = session_get_cookie_params(); // Gets current cookies params.
		session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);
		session_name($session_name); // Sets the session name to the one set above.
		session_start(); // Start the php session
		session_regenerate_id(true); // regenerated the session, delete the old one.
	}

	if(!isset($_SESSION['msg']))
	{
		$_SESSION['msg'] = array();
		$_SESSION['msg']['info'] = array();
		$_SESSION['msg']['error'] = array();
		$_SESSION['msg']['success'] = array();
	}
	/*
	if(!isset($_SESSION['geo']) || $_SESSION['geo'] == false)
	{
		if($_SERVER['HTTP_HOST'] == 'localhost')
		{
			$geo = json_decode(file_get_contents('http://www.geoplugin.net/json.gp?ip=87.79.104.108'),true);
		}
		else
		{
			$geo = json_decode(file_get_contents('http://www.geoplugin.net/json.gp?ip='.$_SERVER['REMOTE_ADDR']),true);
		}
		
		//$geo = json_decode(file_get_contents('http://www.geoplugin.net/json.gp?ip=78.35.240.57'),true);
		if(isset($geo['geoplugin_status']) && $geo['geoplugin_status'] == 200)
		{
	
			$geo['plz'] = json_decode(file_get_contents('http://www.geoplugin.net/extras/postalcode.gp?lat='.$geo['geoplugin_latitude'].'&long='.$geo['geoplugin_longitude'].'&format=json'),true);
	
			if(isset($geo['plz']['geoplugin_place']))
			{
				$_SESSION['geo'] = array
				(
						'lat' => $geo['geoplugin_latitude'],
						'lon' => $geo['geoplugin_longitude'],
						'region' => $geo['geoplugin_region'],
						'city' => $geo['plz']['geoplugin_place'],
						'plz' => $geo['plz']['geoplugin_postCode']
				);
			}
		}
	}
	*/
}

function getMessages()
{
	global $g_error;
	global $g_info;
	if(!isset($_SESSION['msg']))
	{
		$_SESSION['msg'] = array();
	}
	if(isset($_SESSION['msg']['error']) && !empty($_SESSION['msg']['error']))
	{
		$msg = '';
		foreach ($_SESSION['msg']['error'] as $e)
		{
			$msg .= '<div class="item">'.$e.'</div>';
			//addJs('error("'.$e.'");');
		}
		addJs('pulseError("'.jsSafe($msg,'"').'");');
	}
	if(isset($_SESSION['msg']['info']) && !empty($_SESSION['msg']['info']))
	{
		$msg = '';
		foreach ($_SESSION['msg']['info'] as $i)
		{
			$msg .= '<p>'.$i.'</p>';
			//addJs('info("'.$i.'");');
		}
		addJs('pulseInfo("'.jsSafe($msg,'"').'");');
	}
	if(isset($_SESSION['msg']['info']) && !empty($_SESSION['msg']['info']))
	{
		$msg = '';
		foreach ($_SESSION['msg']['info'] as $i)
		{
			$msg .= '<p>'.$i.'</p>';
		}
		addJs('pulseSuccess("'.jsSafe($msg,'"').'");');
	}
	$_SESSION['msg']['info'] = array();
	$_SESSION['msg']['success'] = array();
	$_SESSION['msg']['error'] = array();
	//return v_getMessages($g_error,$g_info);
}

function text_save($txt)
{
	return strip_tags($txt);
}

function save($txt)
{
	return preg_replace('/[^a-zA-Z0-9]/','',$txt);
}

function loggedIn()
{
	if(isset($_SESSION['client']) && $_SESSION['client']['id'] > 0)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function getCurrent($page = false)
{
	global $content;
	global $right;
	global $js; 
	global $db;
	
	if(S::may())
	{
		$db->updateActivity();
	}
	
	$page = 'index';
	if($p = getPage())
	{
		$page = $p;
	}
		/*
		if(file_exists('control/'.$page.'.php'))
		{
			include 'control/'.$page.'.php';
		}
		*/
		if(file_exists('control/'.$page.'.php'))
		{
			$lang = 'DE';
			if(file_exists('lang/'.$lang.'/'.$page.'.lang.php'))
			{
				include 'lang/'.$lang.'/'.$page.'.lang.php';
			}
			
			if(file_exists('model/'.$page.'.model.php'))
			{
				include 'model/'.$page.'.model.php';
				$mod = ucfirst($page).'Model';
				$db = new $mod();
			}
			
			if(isset($_GET['gid']))
			{
				$db->readGlocke($_GET['gid']);
			}
			include 'control/'.$page.'.php';
		}

}

function addScript($src,$global = false)
{
	global $g_script;
	$g_script[$src] = array('global'=>$global);
}

function loadModel($model = 'api')
{
	require_once ROOT_DIR.'app/core/core.model.php';
	require_once ROOT_DIR.'app/'.$model.'/'.$model.'.model.php';
	$mod = ucfirst($model).'Model';
	return new $mod();
}

function loadXhr($app)
{
	require_once ROOT_DIR.'app/core/core.model.php';
	require_once ROOT_DIR.'app/core/core.view.php';
	require_once ROOT_DIR.'app/core/core.control.php';
	require_once ROOT_DIR.'app/'.$app.'/'.$app.'.model.php';
	require_once ROOT_DIR.'app/'.$app.'/'.$app.'.view.php';
	require_once ROOT_DIR.'app/'.$app.'/'.$app.'.xhr.php';
	$mod = ucfirst($app).'Xhr';
	return new $mod();
}

function loadApp($app)
{	
	require_once ROOT_DIR.'app/core/core.control.php';
	require_once ROOT_DIR.'app/core/core.model.php';
	require_once ROOT_DIR.'app/core/core.view.php';
	
	require_once ROOT_DIR.'app/'.$app.'/'.$app.'.control.php';
	require_once ROOT_DIR.'app/'.$app.'/'.$app.'.model.php';
	require_once ROOT_DIR.'app/'.$app.'/'.$app.'.view.php';
	require_once ROOT_DIR.'lang/DE/'.$app.'.lang.php';
	
	addJsFunc(file_get_contents(ROOT_DIR.'app/'.$app.'/'.$app.'.script.js'));
	addStyle(file_get_contents(ROOT_DIR.'app/'.$app.'/'.$app.'.style.css'));
	
	$appUc = ucfirst($app);
	
	$appClass = $appUc.'Control';
	
	$app = new $appClass($appUc);
	

	if(isset($_GET['a']) && method_exists($app, $_GET['a']))
	{
		$meth = $_GET['a'];
		$app->$meth();
	}
	else
	{
		$app->index();
	}
	
	if(($sub = $app->getSubFunc()) !== false)
	{
		$app->$sub();
	}
}

function cssCompress()
{
	global $g_css;
	$genf = ROOT_DIR.'css/gen/style.css';
	$md5 = '';
	
	$write_new = false;
	if(isset($_GET['nocache']))
	{
		@file_put_contents($genf, '');
		$write_new = true;
	}
	
	if($write_new)
	{
		include_once 'lib/cssmin.php';
	}
	
	foreach ($g_css as $src => $i)
	{
		if($i['global'])
		{
			if($write_new)
			{
				if(strpos($src,'awesome') !== false)
				{
					file_put_contents($genf, (file_get_contents(ROOT_DIR.$src))."\n",FILE_APPEND);
				}
				else 
				{
					file_put_contents($genf, CssMin::minify(file_get_contents(ROOT_DIR.$src))."\n",FILE_APPEND);
				}
			}
			unset($g_css[$src]);
		}
	}
	$g_css = array_merge(array('/css/gen/style.css?v='.VERSION=>true),$g_css);
}

function scriptCompress()
{
	global $g_script;
	$genf = ROOT_DIR.'js/gen/script.js';
	$md5 = '';
	
	$write_new = false;
	if(isset($_GET['nocache']))
	{
		@file_put_contents($genf, '');
		$write_new = true;
	}
	
	foreach ($g_script as $script => $i)
	{
		if($i['global'])
		{
			if($write_new)
			{
				file_put_contents($genf, JSMin::minify(file_get_contents(ROOT_DIR.$script))."\n",FILE_APPEND);
			}
			unset($g_script[$script]);
		}
	}
	$g_script = array_merge(array('/js/gen/script.js?v='.VERSION=>true),$g_script);
}

function addJsFunc($nfunc)
{
	global $g_js_func;
	$g_js_func .= $nfunc;
}

function addJs($njs)
{
	global $js;

	$js .= $njs;
}

function getJs()
{
	global $js;
	return $js;
}

function addCss($src,$global = false)
{
	global $g_css;
	$g_css[$src] = array('global'=>$global);
}

function makeHead()
{
	scriptCompress();
	cssCompress();
	global $g_script;
	global $g_css;
	global $g_head;
	foreach ($g_css as $src => $s)
	{
		$g_head .= '<link rel="stylesheet" type="text/css" href="'.$src.'" />'."\n";
	}
	foreach ($g_script as $src => $s)
	{
		$g_head .= '<script type="text/javascript" src="'.$src.'"></script>'."\n";
	}
}

function addHead($str)
{
	global $g_head;
	$g_head .= "\n" . $str;
}

function addTitle($name)
{
	global $g_title;
	$g_title[] = $name;
}

function getHead()
{
	global $g_head;
	global $g_title;
	
	return '<title>'.implode(' | ',$g_title).'</title>'.
			$g_head;
}

function setTitle($name)
{
	global $g_title;
	$g_title = array($name);
}

function pv($el)
{
	//return '<pre>'.print_r($el,true).'</pre>';
}

function fsId()
{
	if(loggedIn())
	{
		return $_SESSION['client']['id'];
	}
	else
	{
		return 0;
	}
}

function isVerified()
{
	if(isOrgaTeam())
	{
		return true;
	}
	else if(isset($_SESSION['client']['verified']) && $_SESSION['client']['verified'] == 1)
	{
		return true;
	}
	
	return false;
}

function br2nl($str)
{
	$str = str_replace("\r\n", "\n", $str);
	$str = str_replace(array('<br>','<br />','<br/>','<br >'), "\n", $str);
	$str = str_replace("\n\n", "\n", $str);
	
	$str = str_replace(array('</p>','</ p>'), "\n\n", $str);
	$str = str_replace("\n\n\n", "\n\n", $str);
	
	return strip_tags($str);
}

function validEmail($email)
{
	if (filter_var($email, FILTER_VALIDATE_EMAIL))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function validPlz($plz)
{
	$plz = preg_replace('/[^0-9]/', '', $plz);



	if(strlen($plz) == 5)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function validUrl($url)
{
	if(!filter_var($url, FILTER_VALIDATE_URL))
	{
		return false;
	}
	
	return true;
}

function isAdmin()
{
	if(isset($_SESSION['client']['group']['admin']) && $_SESSION['client']['group']['admin'] === true)
	{
		return true;
	}
	return false;
}

function debug($arg)
{
	if(isAdmin())
	{
		global $g_debug;
		$g_debug[] = $arg;
	}
}

function logg($arg)
{
	file_put_contents(ROOT_DIR.'data/logg.txt', json_encode(array('date'=>date('Y-m-d H:i:s'),'session'=>$_SESSION,'data'=>$arg,'add'=>array($_GET))).'-|||-',FILE_APPEND);
}

function getDebugging()
{
	global $g_debug;
	if(!empty($g_debug))
	{
		//echo '<div class="g_debug"><pre>'.print_r($g_debug,true).'</pre></div>';
	}
}

function libmailList()
{
	
}

function libmail($bezirk, $email, $subject, $message, $attach = false, $token = false)
{	
	if(!is_array($bezirk))
	{
		$bezirk = array(
			'email' => $bezirk,
			'email_name' => $bezirk
		);
	}
	else 
	{
		if(!validEmail($bezirk['email']))
		{
			$bezirk['email'] = EMAIL_PUBLIC;
		}
		if(empty($bezirk['email_name']))
		{
			$bezirk['email_name'] = EMAIL_PUBLIC_NAME;
		}
	}
	
	if(!validEmail($email))
	{
		return false;
	}
	
	if($_SERVER['SERVER_NAME'] == 'localhost')
	{
		logg(array(
			'bezirk' => $bezirk,
			'email' => $email,
			'subject' => $subject,
			'msg' => $message,
			'attach' => $attach
		));
		return true;
	}
	elseif (isAdmin()) 
	{
		debug(array(
			'bezirk' => $bezirk,
			'email' => $email,
			'subject' => $subject,
			'msg' => $message,
			'attach' => $attach
		));
	}

	
	
	$mail = new SocketMail();
	$mail->setFrom($bezirk['email'], $bezirk['email_name']);
	$mail->addRecipient($email);
	$mail->setSubject($subject);
	$mail->setHtmlBody(emailBodyTpl($message,$email,$token));	
	
	//Replace the plain text body with one created manually
	$message = str_replace('<br />', "\r\n", $message);
	$message = strip_tags($message);
	$mail->setBody($message);
	
	
	if($attach !== false)
	{
		foreach ($attach as $a)
		{
			$mail->addAttachment($a['path'],$a['name']);
		}
	}
	
	$socket = new SocketClient();
	$socket->queue($mail);

	$socket->send();
	$socket->close();
}

function mailMessage($sender_id,$recip_id,$msg=NULL)
{
	// FIXME this function is pretty much a copy of Model::mailMessage() and should probably replaced
	$db = loadModel('mailbox');
	
	$info = $db->getVal('infomail_message', 'foodsaver', $recip_id);
	if((int)$info > 0)
	{
		if(!isset($_SESSION['lastMailMessage']))
		{
			$_SESSION['lastMailMessage'] = array();
		}
		if(!$db->isActive($recip_id))
		{
			if(!isset($_SESSION['lastMailMessage'][$recip_id]) || (time() - $_SESSION['lastMailMessage'][$recip_id]) > 600)
			{
				$_SESSION['lastMailMessage'][$recip_id] = time();
				$foodsaver = $db->getOne_foodsaver($recip_id);
				$sender = $db->getOne_foodsaver($sender_id);
				if (!isset($msg))
				{
					// FIXME this is error-prone;
					$msg = '';
				}
				
				tplMail(9, $foodsaver['email'],array(
					'anrede' => genderWord($foodsaver['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
					'sender' => $sender['name'],
					'name' => $foodsaver['name'],
					'message' => $msg,
					'link' => BASE_URL.'?page=msg&u2c='.(int)$sender_id
				));
			}
			
		}
	}
}

function getBezirk()
{
	global $db;
	return $db->getBezirk();
}

function genderWord($gender,$m,$w,$other)
{
	$out = $other;
	if($gender == 1)
	{
		$out = $m;
	}
	elseif ($gender == 2)
	{
		$out = $w;
	}
	
	return $out;
}

function fsMailPear($foodsaver,$subject,$message,$attach = false)
{
	global $db;
	$data[] = $subject;
	$data['message'] = $message;
	$data['attach'] = $attach;
	
	$bezirk = $db->getMailBezirk(getBezirkId());

	$attach_db = '';
	if($attach !== false)
	{
		$attach_db = json_encode(array($attach));
	}
	
	$recip_db = array();
	foreach ($foodsaver as $f)
	{
		$recip_db[] = $f['email'];
	}
	$recip_db = json_encode($recip_db);
	
	$id = $db->add_send_email(array(
		'foodsaver_id' => fsId(),
		'name' => $subject,
		'zeit' => date('Y-m-d H:i:s'),
		'message' => $message,
		'attach' => $attach_db,
		'recip' => $recip_db
	));
	
	
	//error_reporting(0);
	require_once "lib/Pear/mime.php";
	require_once "lib/Pear/mimePart.php";
	require_once "lib/Pear/Mail.php";
	
	$mailobj = new Mail();
	
	$host = "ssl://smtp.gmail.com";
	$port = "465";
	$username = $bezirk['email'];
	$password = $bezirk['email_pass'];
	
	$smtp = $mailobj->factory('smtp',array ('host' => $host, 'port' => $port, 'auth' => true, 'username' => $username, 'password' => $password));
	
	
	
	foreach ($foodsaver as $fs)
	{
		$anrede = 'Liebe/r';
		if($fs['geschlecht'] == 1)
		{
			$anrede = 'Lieber';
		}
		elseif ($fs['geschlecht'] == 2)
		{
			$anrede = 'Liebe';
		}
		
		$search = array('{NAME}','{ANREDE}');
		$replace = array($fs['name'],$anrede);
		
		$message = str_replace($search,$replace,$message);
		$subject = str_replace($search, $replace, $subject);
		
		try {
			
	
			
		
			$from = $bezirk['email'];
			$to = $fs['email'];
			
			$subject = $subject;
			$msg = utf8_decode($message);
			$headers = array (
					'From' => $from,
					'To' => $to,
					'Subject' => $subject,
					'X-Sender' => '<'.$bezirk['email_name'].'>'
			);
			$mime = new Mail_mime(array('eol' => "\n"));
			
			$mime->setTXTBody($msg);
			$mime->setHTMLBody('<html><body>'.nl2br($msg).'</body></html>');
			
			if($attach !== false)
			{
				$mime->addAttachment($attach['path'],$attach['mime'],$attach['name']);
			}
			
			$body = $mime->get();
			$headers = $mime->headers($headers);
			
			
			$mail = $smtp->send($to, $headers, $body);			
			
			if (PEAR::isError($mail))
			{
				if(strpos($mail->getMessage(),'authenticate') !== false)
				{
					// unable to authenticate to smtp server
					return false;
				}
				else
				{
					$db->add_mail_error(array(
							'send_mail_id' => $id,
							'foodsaver_id' => $fs['id']
					));
				}
			}
			
			$db->add_message(array(
				'sender_id' => fsId(),
				'recip_id' => $fs['id'],
				'unread' => 1,
				'name' => $subject,
				'msg' => $message,
				'time' => date('Y-m-d H:i:s'),
				'attach' => $attach_db
			));
			
		}
		catch (Exception $e)
		{
			$db->add_mail_error(array(
					'send_mail_id' => $id,
					'foodsaver_id' => $fs['id']
			));
		}
	}
	//error_reporting(ERROR_REPORT);
}

function mail_att($from,$to,$subject,$message,$anhang = false)
{

	$absender = $from['email_name'];
	$absender_mail = $from['email'];
	
	if(empty($absender))
	{
		$absender = 'Foodsharing';
	}
	
	$reply = $from['email'];

	$mime_boundary = "-----=" . md5(uniqid(mt_rand(), 1));

	$header  = 'From: '.$absender.' <'.$absender_mail.'>' . "\r\n";
	$header .= "Reply-To: ".$reply."\n";

	$header.= "MIME-Version: 1.0\r\n";
	$header.= "Content-Type: multipart/mixed;\r\n";
	$header.= " boundary=\"".$mime_boundary."\"\r\n";

	$content = "This is a multi-part message in MIME format.\r\n\r\n";
	$content.= "--".$mime_boundary."\r\n";
	$content.= "Content-Type: text/html charset=\"iso-8859-1\"\r\n";
	$content.= "Content-Transfer-Encoding: 8bit\r\n\r\n";
	$content.= nl2br(utf8_decode($message))."\r\n";

	//$anhang ist ein Mehrdimensionals Array
	//$anhang enthält mehrere Dateien
	if(is_array($anhang) AND is_array(current($anhang)))
	{
		foreach($anhang AS $dat)
		{
			$data = chunk_split(base64_encode(file_get_contents($dat['path'])));
			$content.= "--".$mime_boundary."\r\n";
			$content.= "Content-Disposition: attachment;\r\n";
			$content.= "\tfilename=\"".$dat['name']."\";\r\n";
			$content.= "Content-Length: .".$dat['size'].";\r\n";
			$content.= "Content-Type: ".$dat['mime']."; name=\"".$dat['name']."\"\r\n";
			$content.= "Content-Transfer-Encoding: base64\r\n\r\n";
			$content.= $data."\r\n";
		}
		$content .= "--".$mime_boundary."--";
	}
	elseif($anhang !== false) //Nur 1 Datei als Anhang
	{		
		$data = chunk_split(base64_encode(file_get_contents($anhang['path'])));
		$content.= "--".$mime_boundary."\r\n";
		$content.= "Content-Disposition: attachment;\r\n";
		$content.= "\tfilename=\"".$anhang['name']."\";\r\n";
		$content.= "Content-Length: .".$anhang['size'].";\r\n";
		$content.= "Content-Type: ".$anhang['mime']."; name=\"".$anhang['name']."\"\r\n";
		$content.= "Content-Transfer-Encoding: base64\r\n\r\n";
		$content.= $data."\r\n";
	}

	if(@mail($to, $subject, $content, $header)) return true;
	else return false;
}

function hiddenDialog($table,$fields,$title = '',$option = array())
{
	$width = '';
	if(isset($option['width']))
	{
		$width = 'width:'.$option['width'].',';
	}
	$id = id('dialog_'.$table);
	
	$form = '';
	foreach ($fields as $f)
	{
		$form .= $f;
	}
	
	$get = '';
	if(isset($_GET['id']))
	{
		$get = '<input type="hidden" name="id" value="'.(int)$_GET['id'].'" />';
	}
	
	addHidden('<div id="'.$id.'"><form>'.$form.$get.'</form></div>');
	//addJs('hiddenDialog("'.$id.'","'.$table.'","'.$title.'");');
	
	
	
	$success = '';
	if(isset($option['success']))
	{
		$success = $option['success'];
	}
	
	if(isset($option['reload']))
	{
		$success .= 'reload();';
	}
	
	addJs('
		$("#'.$id.'").dialog({
		'.$width.'
		autoOpen:false,
		modal:true,
		title:"'.$title.'",
		buttons:
		{
			"Speichern":function()
			{
				showLoader();
				$.ajax({
					
					dataType:"json",
					url:"xhr.php?f=update_'.$table.'&" + $("#'.$id.' form").serialize(),
					success : function(data){
						$("#'.$id.'").dialog(\'close\');
						'.$success.'
						if(data.script != undefined)
						{
							$.globalEval(data.script);
						}
					},
					complete : function(){
						hideLoader();
					}
				});
			}
		}
	});	
	');
	
}

function compress($buffer)
{
	return JSMin::minify($buffer);
}

function hasBezirk($bid)
{
	if(isset($_SESSION['client']['bezirke'][$bid]) || isBotFor($bid))
	{
		return true;
	}
	return false;
}

function mayBezirk($bid)
{
	if(isset($_SESSION['client']['bezirke'][$bid]) || isBotFor($bid) || isOrgaTeam())
	{
		return true;
	}
	return false;
}

function mayGroup($group)
{
	if(isset($_SESSION) && isset($_SESSION['client']['group'][$group]))
	{
		return true;
	}
	
	return false;
}

function may()
{
	if(isset($_SESSION) && isset($_SESSION['client']) && (int)$_SESSION['client']['id'] > 0)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function genPassword($length = 5)
{
	$pool = "qwertzupasdfghkyxcvbnm";
	$pool .= "1234567890";
	$pool .= "WERTZUPLKJHGFDSAYXCVBNM";

	srand ((double)microtime()*1000000);
	$pass_word = '';
	for($index = 0; $index < $length; $index++)
	{
		$pass_word .= substr($pool,(rand()%(strlen ($pool))), 1);
	}
	
	return $pass_word;
}

function getRolle($gender_id,$rolle_id)
{
	return s('rolle_'.$rolle_id.'_'.$gender_id);
}

function cropImg($path,$img,$i,$x,$y,$w,$h)
{
	$targ_w = $w;
	$targ_h = $h;
	$jpeg_quality = 100;

	$ext = explode('.',$img);
	$ext = end($ext);
	$ext = strtolower($ext);
	
	switch($ext)
	{
		case 'gif' : $img_r = imagecreatefromgif($path.'/'.$img); ;break;
		case 'jpg' : $img_r = imagecreatefromjpeg($path.'/'.$img); ;break;
		case 'png' : $img_r = imagecreatefrompng($path.'/'.$img); ;break;
	}


	$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

	imagecopyresampled($dst_r,$img_r,0,0,$x,$y,$targ_w,$targ_h,$w,$h);

	$new_path = $path.'/crop_'.$i.'_'.$img;
	
	@unlink($new_path);

	switch($ext)
	{
		case 'gif' : imagegif($dst_r, $new_path ); break;
		case 'jpg' : imagejpeg($dst_r, $new_path, $jpeg_quality ); break;
		case 'png' : imagepng($dst_r, $new_path, 0 ); break;
	}
}

function cropImage($bild,$x,$y,$w,$h)
{

	$targ_w = 467;
	$targ_h = 600;
	$jpeg_quality = 100;

	$ext = explode('.',$bild);
	$ext = end($ext);
	$ext = strtolower($ext);
	
	$img_r = null;
	
	switch($ext)
	{
		case 'gif' : $img_r = imagecreatefromgif('./tmp/'.$bild); ;break;
		case 'jpg' : $img_r = imagecreatefromjpeg('./tmp/'.$bild); ;break;
		case 'png' : $img_r = imagecreatefrompng('./tmp/'.$bild); ;break;
	}

	if($img_r === null)
	{
		return false;
	}

	$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

	imagecopyresampled($dst_r,$img_r,0,0,$x,$y,$targ_w,$targ_h,$w,$h);

	@unlink('../tmp/crop_'.$bild);

	switch($ext)
	{
		case 'gif' : imagegif($dst_r, './tmp/crop_'.$bild ); break;
		case 'jpg' : imagejpeg($dst_r, './tmp/crop_'.$bild, $jpeg_quality ); break;
		case 'png' : imagepng($dst_r, './tmp/crop_'.$bild, 0 ); break;
	}

	if(file_exists('./tmp/crop_'.$bild))
	{
		try {
			
			copy('./tmp/crop_'.$bild, './tmp/thumb_crop_'.$bild);
			$img = new fImage('./tmp/thumb_crop_'.$bild);
			$img->resize(200, 0);
			$img->saveChanges();
			
			return 'thumb_crop_'.$bild;
		}
		catch (Exception $e)
		{
			return false;
		}
	}
	return false;
}

function resizeImg($img,$width,$format)
{
	if(file_exists($img))
	{
		$opt = 'auto';
		if($format == 'q')
		{
			$opt = 'crop';
		}
		
		try {
			$newimg = str_replace('/', '/' . $width . '_' . $format . '_', $img);
			copy($img, $newimg);
			$img = new fImage($newimg);
			
			if($opt == 'crop')
			{
				$img->cropToRatio(1, 1);
				$img->resize($width, $width);
			}
			else
			{
				$img->resize($width, 0);
			}
			
			$img->saveChanges();
			return true;
		}
		catch (Exception $e)
		{
			
		}
	}
	return false;
}

function addStyle($css)
{
	global $g_add_css;
	$g_add_css .= trim($css);
}

function clearPost()
{
	go(getSelf());
}

function getSelf()
{
	/*
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}*/
	return $_SERVER['REQUEST_URI'];
}

function unsetAll($array,$fields)
{
	$out = array();
	foreach ($fields as $f)
	{
		if(isset($array[$f]))
		{
			$out[$f] = $array[$f];
		}
	}
	
	return $out;
}

function is_allowed($img)
{
	$img['name'] = strtolower($img['name']);
	$img['type'] = strtolower($img['type']);

	$allowed = array("jpg" => true, "jpeg" => true, "png" => true,'gif' => true);

	$filename  = $img['name'];
	$parts = explode('.', $filename);
	$ext = end($parts);

	$allowed_mime = array('image/gif'=>true,'image/jpeg'=>true,'image/png'=>true);

	if(isset($allowed[$ext]))
	{
		return true;
	}
	/*
	else if (isset($allowed_mime[$img['type']]))
	{

		return true;
	}
	*/

	return false;
}

function deleteFilesFromDirectory($ordnername){
	//überprüfen ob das Verzeichnis überhaupt existiert
	if (is_dir($ordnername)) {
		//Ordner öffnen zur weiteren Bearbeitung
		if ($dh = opendir($ordnername)) {
			//Schleife, bis alle Files im Verzeichnis ausgelesen wurden
			while (($file = readdir($dh)) !== false) {
				//Oft werden auch die Standardordner . und .. ausgelesen, diese sollen ignoriert werden
				if ($file!="." AND $file !="..") {
					//Files vom Server entfernen
					unlink("".$ordnername."".$file."");
				}
			}
			//geöffnetes Verzeichnis wieder schließen
			closedir($dh);
		}
	}
}

function cleanPhone($number)
{
	$number = preg_replace('/[^0-9+]/','',$number);

	if(substr($number,0,3) == '+49')
	{
		$number = '+'.str_replace('+','',$number);
	}

	if(substr($number,0,4) == '0049')
	{
		$number = '+49'.substr($number,4);
	}

	if(substr($number,0,1) == '0')
	{
		$number = '+49'.substr($number,1);
	}

	return $number;
}

function tt($str,$length = 160)
{
	if(strlen($str) > $length)
	{
		$str = preg_replace("/[^ ]*$/", '', substr($str, 0, $length)).' ...';
	}
	return $str;
}

function avatar($foodsaver,$size = 'mini',$altimg = false)
{
	return '<span style="background-image:url('.img($foodsaver['photo'],$size,'q',$altimg).');" class="avatar size-'.$size.' sleepmode-'.$foodsaver['sleep_status'].'"><i>'.$foodsaver['name'].'</i></span>';
}

function rolleWrapInt($roleInt)
{
	$roles = array(
		0 => 'user',
		1 => 'fs',
		2 => 'bieb',
		3 => 'bot',
		4 => 'orga',
		5 => 'admin'
	);

	return $roles[$roleInt];
}

function rolleWrap($roleStr)
{
	$roles = array(
		'user' => 0,
		'fs' => 1,
		'bieb' => 2,
		'bot' => 3,
		'orga' => 4,
		'admin' => 5
	);
	
	return $roles[$roleStr];
}

function convertKbSize($size)
{
	$unit=array('b','kb','mb','gb','tb','pb');
	return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

function forceLogin()
{
	go('?page=login&ref='.urlencode($_SERVER['REQUEST_URI']));
}

function sendSock($fsid,$app,$method,$options)
{
	if($sid = Mem::user($fsid, 'sid'))
	{
		$query = http_build_query(array(
			'c' => $sid, // client session id
			'a' => $app, // app
			'm' => $method, // method
			'o' => json_encode($options) // options
		));
		
		//$fp = fopen ('http://127.0.0.1:1338/?' . $query , 'r');
		
		file_get_contents('http://127.0.0.1:1338/?' . $query);
		
		/*
		if($fp)
		{
			fclose($fp);
		}
		*/
	}
}
