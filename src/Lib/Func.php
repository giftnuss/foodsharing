<?php

namespace Foodsharing\Lib;

use Exception;
use Flourish\fDate;
use Flourish\fFile;
use Flourish\fImage;
use Foodsharing\DI;
use Foodsharing\Lib\Db\ManualDb;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Mail\AsyncMail;
use Foodsharing\Lib\Session\S;
use Foodsharing\Lib\View\Utils;
use JSMin;

class Func
{
	private $content_main;
	private $content_right;
	private $content_left;
	private $content_bottom;
	private $content_top;
	private $content_overtop;
	private $bread;
	private $hidden;
	private $js_func;
	private $js;
	private $head;
	private $title;
	private $ids;
	private $script;
	private $css;
	private $add_css;
	private $meta;
	private $viewUtils;

	public function __construct(Utils $viewUtils)
	{
		$this->viewUtils = $viewUtils;
		$this->content_main = '';
		$this->content_right = '';
		$this->content_left = '';
		$this->content_bottom = '';
		$this->content_top = '';
		$this->content_overtop = '';
		$this->bread = array();
		$this->hidden = '';
		$this->js_func = '';
		$this->js = '';
		$this->head = '';
		$this->title = array('foodsharing');

		$this->ids = array();
		$this->script = array();
		$this->css = array();
		$this->add_css = '';

		$this->meta = array(
			'description' => 'Auf foodsharing.de kannst Du Deine Lebensmittel vor dem Verfall an soziale Einrichtungen oder andere Personen abgeben',
			'keywords' => 'foodsharing, essen, lebensmittel, ablaufdatum, Lebensmittelverschwendung, essen wegschmeissen, spenden, lebensmitteltausch',
			'author' => 'foodsharing',
			'robots' => 'all',
			'allow-search' => 'yes',
			'revisit-after' => '1 days',
			'google-site-verification' => 'pZxwmxz2YMVLCW0aGaS5gFsCJRh-fivMv1afrDYFrks'
		);
	}

	public function jsonSafe($str)
	{
		if ((string)$str == '' || !is_string($str)) {
			return '';
		}

		return htmlentities((string)$str . '', ENT_QUOTES, 'utf-8', false);
	}

	public function getContent($place = CNT_MAIN)
	{
		switch ($place) {
			case CNT_MAIN:
				return $this->content_main;
				break;
			case CNT_RIGHT:
				return $this->content_right;
				break;
			case CNT_TOP:
				return $this->content_top;
				break;
			case CNT_BOTTOM:
				return $this->content_bottom;
				break;
			case CNT_LEFT:
				return $this->content_left;
				break;
			case CNT_OVERTOP:
				return $this->content_overtop;
				break;
			default:
				return '';
				break;
		}
	}

	public function addContent($new_content, $place = CNT_MAIN)
	{
		switch ($place) {
			case CNT_MAIN:

				$this->content_main .= $new_content;
				break;
			case CNT_RIGHT:

				$this->content_right .= $new_content;
				break;

			case CNT_TOP:
				$this->content_top .= $new_content;
				break;

			case CNT_BOTTOM:

				$this->content_bottom .= $new_content;
				break;

			case CNT_LEFT:

				$this->content_left .= $new_content;
				break;

			case CNT_OVERTOP:

				$this->content_overtop .= $new_content;
				break;

			default:
				break;
		}
	}

	public function abhm($id)
	{
		$arr = [
			1 => ['id' => 1, 'name' => '1-3 kg'],
			2 => ['id' => 2, 'name' => '3-5 kg'],
			3 => ['id' => 3, 'name' => '5-10 kg'],
			4 => ['id' => 4, 'name' => '10-20 kg'],
			5 => ['id' => 5, 'name' => '20-30 kg'],
			6 => ['id' => 6, 'name' => '40-50 kg'],
			7 => ['id' => 7, 'name' => 'mehr als 50 kg']
		];

		if (isset($arr[$id])) {
			return $arr[$id]['name'];
		}

		return false;
	}

	public function niceDateShort($ts)
	{
		if (date('Y-m-d', $ts) == date('Y-m-d')) {
			return $this->s('today') . ' ' . date('H:i', $ts);
		} else {
			return date('j.m.Y. H:i', $ts);
		}
	}

	public function niceDate($ts)
	{
		$pre = '';
		$date = new fDate($ts);

		if ($date->eq('today')) {
			$pre = $this->s('today');
		} elseif ($date->eq('tomorrow')) {
			$pre = $this->s('tomorrow');
		} elseif ($date->eq('-1 day')) {
			$pre = $this->s('yesterday');
		} else {
			$days = $this->getDow();
			$pre = $days[date('w', $ts)] . ', ' . (int)date('d', $ts) . '. ' . $this->s('smonth_' . date('n', $ts));
			$year = date('Y', $ts);
			if ($year != date('Y')) {
				$pre = $pre . ' ' . $year;
			}
		}

		return $pre . ', ' . date('H:i', $ts) . ' ' . $this->s('clock');
	}

	public function incLang($id)
	{
		global $g_lang;
		include ROOT_DIR . 'lang/DE/' . $id . '.lang.php';
	}

	public function s($id)
	{
		global $g_lang;

		if (isset($g_lang[$id])) {
			return $g_lang[$id];
		} else {
			return $id;
		}
	}

	public function format_d($ts)
	{
		return date('d.m.Y', $ts);
	}

	public function format_db_date($date)
	{
		$part = explode('-', $date);

		return (int)$part[2] . '. ' . $this->niceMonth((int)$part[1]);
	}

	public function niceMonth($month)
	{
		return $this->s('month_' . $month);
	}

	public function format_time($time)
	{
		$p = explode(':', $time);
		if (count($p) >= 2) {
			return (int)$p[0] . '.' . $p[1] . ' Uhr';
		} else {
			return '';
		}
	}

	public function ts_day($ts)
	{
		$days = $this->getDow();

		return $days[date('w')];
	}

	public function ts_time($ts)
	{
		return date('H:i', $ts) . ' Uhr';
	}

	public function msgTime($ts)
	{
		$cur = time();
		$diff = $cur - $ts;

		if ($diff < 600) {
			// letzte 10 minuten
			return $this->s('currently');
		} elseif ($diff < 86400) {
			// heute noch
			return $this->sv('today_time', $this->ts_time($ts));
		} elseif ($diff < 604800) {
			// diese woche noch
			return $this->ts_day($ts) . ', ' . $this->ts_time($ts);
		} else {
			return $this->s('before_one_week');
		}
	}

	public function makeThumbs($pic)
	{
		if (!file_exists(ROOT_DIR . 'images/mini_q_' . $pic) && file_exists(ROOT_DIR . 'images/' . $pic)) {
			copy(ROOT_DIR . 'images/' . $pic, ROOT_DIR . 'images/mini_q_' . $pic);
			copy(ROOT_DIR . 'images/' . $pic, ROOT_DIR . 'images/med_q_' . $pic);
			copy(ROOT_DIR . 'images/' . $pic, ROOT_DIR . 'images/q_' . $pic);

			$image = new fImage(ROOT_DIR . 'images/mini_q_' . $pic);
			$image->cropToRatio(1, 1);
			$image->resize(35, 35);
			$image->saveChanges();

			$image = new fImage(ROOT_DIR . 'images/med_q_' . $pic);
			$image->cropToRatio(1, 1);
			$image->resize(75, 75);
			$image->saveChanges();

			$image = new fImage(ROOT_DIR . 'images/q_' . $pic);
			$image->cropToRatio(1, 1);
			$image->resize(150, 150);
			$image->saveChanges();
		}
	}

	public function handleTagselect($id)
	{
		global $g_data;
		$recip = array();
		if (isset($g_data[$id]) && is_array($g_data[$id])) {
			foreach ($g_data[$id] as $key => $r) {
				if ($key != '') {
					$part = explode('-', $key);
					$recip[$part[0]] = $part[0];
				}
			}
		}

		$g_data[$id] = $recip;
	}

	public function format_dt($ts)
	{
		return date('d.m.Y H:i', $ts) . ' Uhr';
	}

	public function sv($id, $var)
	{
		global $g_lang;
		if (is_array($var)) {
			$search = array();
			$replace = array();
			foreach ($var as $key => $value) {
				$search[] = '{' . $key . '}';
				$replace[] = $value;
			}

			return str_replace($search, $replace, $g_lang[$id]);
		} else {
			return str_replace('{var}', $var, $g_lang[$id]);
		}
	}

	public function addBread($name, $href = '')
	{
		$this->bread[] = array('name' => $name, 'href' => $href);
	}

	public function getBread()
	{
		$out = '';
		if (!empty($this->bread)) {
			$last_key = (count($this->bread) - 1);
			$out = '
	<div class="pure-g">
		<div class="pure-u-1">
			<ul class="bread inside">';
			foreach ($this->bread as $key => $p) {
				if ($key == $last_key) {
					$out .= '
				<li class="last">' . $p['name'] . '</li>';
				} else {
					$out .= '
				<li><a href="' . $p['href'] . '">' . $p['name'] . '</a></li>';
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

	public function setEditData($data)
	{
		global $g_data;
		$g_data = $data;
	}

	public function getAction($a)
	{
		if (isset($_GET['a']) && $_GET['a'] == $a) {
			return true;
		} else {
			return false;
		}
	}

	public function pageLink($page, $id, $action = '')
	{
		if (!empty($action)) {
			$action = '&a=' . $action;
		}

		return array('href' => '/?page=' . $page . $action, 'name' => $this->s($id));
	}

	public function getActionId($a)
	{
		if (isset($_GET['a']) && $_GET['a'] == $a && isset($_GET['id']) && (int)$_GET['id'] > 0) {
			return (int)$_GET['id'];
		} else {
			return false;
		}
	}

	public function getDbValues($id)
	{
		global $db;
		$func = 'get_' . str_replace('_id', '', $id);
		if (method_exists($db, $func)) {
			return $db->$func();
		} else {
			return false;
		}
	}

	public function isBotForA($bezirk_ids, $include_groups = true, $include_parent_bezirke = false)
	{
		if ($this->isBotschafter() && is_array($bezirk_ids)) {
			if ($include_parent_bezirke) {
				global $db;
				$bezirk_ids = $db->getParentBezirke($bezirk_ids);
			}
			foreach ($_SESSION['client']['botschafter'] as $b) {
				foreach ($bezirk_ids as $bid) {
					if ($b['bezirk_id'] == $bid && ($include_groups || $b['type'] != 7)) {
						return true;
						break;
					}
				}
			}
		}

		return false;
	}

	public function isBotFor($bezirk_id)
	{
		if ($this->isBotschafter()) {
			foreach ($_SESSION['client']['botschafter'] as $b) {
				if ($b['bezirk_id'] == $bezirk_id) {
					return true;
					break;
				}
			}
		}

		return false;
	}

	public function isBotschafter()
	{
		if (isset($_SESSION['client']['botschafter'])) {
			return true;
		}

		return false;
	}

	public function isOrgaTeam()
	{
		return $this->mayGroup('orgateam');
	}

	public function getMenu()
	{
		$this->addJs('$("#top .menu").css("display","block");');

		$this->addJs('
		$("#mobilemenu").bind("change",function(){
			if($(this).val() != "")
			{
				showLoader();
				goTo($(this).val());
	
			}
		});
	');

		if (S::may()) {
			global $db;

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
				<li><a href="/?page=groups"><i class="fa fa-group"></i> GRUPPENÜBERSICHT</a></li>
				<li class="break"><span></span></li>';

			$ags_mob = '
				<option value="/?page=groups">GRUPPENÜBERSICHT</option>';

			if (isset($_SESSION['client']['bezirke']) && is_array($_SESSION['client']['bezirke'])) {
				foreach ($_SESSION['client']['bezirke'] as $i => $bezirk) {
					if (($bezirk['type'] != 7)) {
						$bezirke_a = $this->getBezirkMenu($bezirk);

						$bezirke .= $bezirke_a['default'];
						$bezirke_mob .= $bezirke_a['mobile'];
					} else {
						$ags_a = $this->getAgMenu($bezirk);

						$ags .= $ags_a['default'];
						$ags_mob .= $ags_a['mobile'];
					}
				}
			}
			if (!empty($ags)) {
				$ags = '<ul class="bigmenu">' . $ags . '</ul>';
			}

			$ags = '<li><a href="/?page=groups">Gruppen</a>' . $ags . '</li>';
			$ags_mob = '
				<optgroup label="Gruppen">
					' . $ags_mob . '
				</optgroup>';

			$foodsaver_mob = '';

			$bezirke .= '
			<li class="break"><span></span></li>
			<li><a href="#" onclick="becomeBezirk();return false;"><i class="fa fa-plus-circle"></i> Bezirk beitreten</a></li>
		</ul>
	</li>';

			$bezirke_mob .= '	
	</optgroup>';

			$orgamenu = $this->getOrgaMenu();
			$betriebe = $this->getBetriebeMenu();
			$settings = $this->getSettingsMenu();

			if (!S::may('fs')) {
				$bezirke = '';
				$ags = '';
			}

			return array(
				'default' => '
					<ul id="mainMenu" class="jMenu">
						<li><a href="/essenskoerbe/find">Essenskörbe</a></li>
						' . $orgamenu['default'] . '
						<li><a href="/"><i class="fa fa-home"></i></a></li>
						<li><a class="fNiv" href="/karte"><i class="fa fa-map-marker"></i></a></li>
						<li><a><i class="fa fa-question-circle"></i></a>
													<ul>
															<li><a href="/?page=listFaq">F.A.Q.</a></li>
															<li><a href="https://wiki.foodsharing.de/">Wiki</a></li>
															<li><a href="/?page=content&sub=changelog">' . $this->s('changelog') . '</a></li>
													</ul>
						</li>
						' . $ags . '
						' . $betriebe['default'] . '
						' . $bezirke . '
						' . $settings['default'] . '
					</ul>',

				'mobile' => '
					<select id="mobilemenu">
						<option class="famenu" value="dashboard" selected="selected">&#xf0c9;</option>
						<option value="/">Home</option>
						<option value="/?page=dashboard">Dashboard</option>
						<option value="/karte">Karte</option>
						<option value="/?page=listFaq">F.A.Q.</option>
						<option value="https://wiki.foodsharing.de">Wiki</option>
						<option value="/?page=content&sub=changelog">' . $this->s('changelog') . '</option>
						' . $settings['mobile'] . '
						' . $ags_mob . '
						' . $foodsaver_mob . '
						' . $betriebe['mobile'] . '
						' . $bezirke_mob . '
						
						' . $orgamenu['mobile'] . '
					</select>'
			);
		} else {
			return array(
				'default' => '
				<ul id="mainMenu" class="jMenu">
					<li><a class="fNiv" href="/karte"><i class="fa fa-map-marker"></i>Karte</a></li>
					<li><a class="fNiv">Über uns</a>
						<ul>
							<li><a href="/ueber-uns">Über uns</a>
							<li><a href="/?page=content&sub=forderungen">Forderungen</a></li>
                                                        <li><a href="/team">Team</a></li>
							<li><a href="/partner">Partner</a></li>
							<li><a href="/statistik">Statistik</a></li>
							<li><a href="/?page=content&sub=presse">Presse</a></li>
						</ul>
					</li>
					<li><a class="fNiv" href="/?page=content&sub=joininfo">Mach mit!</a></li>
					<li><a class="fNiv"><i class="fa fa-info"></i></a>
						<ul>
							<li><a href="/?page=content&sub=infohub">Infosammlung</a></li>
							<li><a href="/news">News</a></li>
							<li><a href="/faq">F.A.Q.</a></li>
							<li><a href="/ratgeber">Ratgeber</a></li>
							<li><a href="/unterstuetzung">Spendenaufruf</a></li>
						</ul>
					</li>
					<li><a class="fNiv" href="/login" title="User Login"><i class="fa fa-user-circle"></i></a></li>
				</ul>',
				'mobile' => '
				<select id="mobilemenu">
					<option class="famenu" value="dashboard" selected="selected">&#xf0c9;</option>
					<option value="/">Home</option>
					<option value="/karte">Karte</option>
					<option value="/ueber-uns">- Über uns</option>
					<option value="/?page=content&sub=forderungen">- Forderungen</option>
					<option value="/team">- Team und Kontaktdaten</option>
					<option value="/partner">- Partner</option>
					<option value="/?page=content&sub=presse">- Presse</option>
					<option value="/?page=content&sub=infohub">Infosammlung FAQ Ratgeber etc.</option>
					<option value="/?page=content&sub=joininfo">Mach mit!</option>
					<option value="/login">Login</option>
				</select>'
			);
		}
	}

	public function preZero($i)
	{
		if ($i < 10) {
			return '0' . $i;
		} else {
			return $i;
		}
	}

	public function getDow()
	{
		return array(
			1 => $this->s('monday'),
			2 => $this->s('tuesday'),
			3 => $this->s('wednesday'),
			4 => $this->s('thursday'),
			5 => $this->s('friday'),
			6 => $this->s('saturday'),
			0 => $this->s('sunday')
		);
	}

	public function getBetriebeMenu()
	{
		if (!S::may('fs')) {
			return array(
				'mobile' => '',
				'default' => ''
			);
		}
		$out = '';
		$out_mob = '';
		if (isset($_SESSION['client']['betriebe']) && !empty($_SESSION['client']['betriebe'])) {
			$out = '
		<li><a onclick="return false" class="fNiv">Betriebe</a>
			<ul class="jmenu-foodsaver">';
			$out_mob = '
		<optgroup label="Betriebe">';

			foreach ($_SESSION['client']['betriebe'] as $cb) {
				$out .= '
				<li><a href="/?page=fsbetrieb&id=' . $cb['id'] . '&sub=forum">' . $cb['name'] . '</a></li>';

				$out_mob .= '
				<option value="/?page=fsbetrieb&id=' . $cb['id'] . '&sub=forum">' . $cb['name'] . '</option>';
			}
			$out .= '
			</ul>
		</li>';

			$out_mob .= '
		</optgroup>';
		}

		$id = $this->id('becomeBezirkChooser');

		$swap_msg = 'Welcher Bezirk soll neu angelegt werden?';
		$swap = $this->viewUtils->v_swapText($id . '-neu', $swap_msg);

		$this->addHidden('
		<div id="becomeBezirk">
			<div class="popbox">
				<h3>Wähle den Bezirk aus, in dem Du aktiv werden möchtest!</h3>
				<p class="subtitle">
					Es besteht auch die Möglichkeit, einen neuen Bezirk zu gründen. Wähle bitte dennoch den entsprechenden übergeordneten Bezirk (Land, Bundeslan, Stadt etc.) aus!
				</p>
				<div style="height:260px;">
					' . $this->viewUtils->v_bezirkChildChooser($id) . '
					<span id="' . $id . '-btna">Gesuchter Bezirk ist nicht dabei</span>
					<div class="middle" id="' . $id . '-notAvail">
						<h3>Deine Stadt oder Region ist nicht dabei?</h3>
						' . $swap . '
					</div>
				</div>
				<p class="bottom">
					<span id="' . $id . '-button">Speichern</span>
				</p>
			</div>
		</div>
		<a id="becomeBezirk-link" href="#becomeBezirk">&nbsp;</a>
		
	');

		$this->addJs('
		$("#' . $id . '-notAvail").hide();
				
		$("#' . $id . '-btna").button().click(function(){
			
			$("#' . $id . '-btna").fadeOut(200,function(){
				$("#' . $id . '-notAvail").fadeIn();
			});
		});
				
		$("#' . $id . '-button").button().click(function(){
			if(parseInt($("#' . $id . '").val()) > 0)
			{
				
				part = $("#' . $id . '").val().split(":");
				
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
					if($("#' . $id . '-neu").val() != "' . $swap_msg . '")
					{
						neu = $("#' . $id . '-neu").val();
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
									goTo( "/?page=relogin&url=" + encodeURIComponent("/?page=bezirk&bid=" +$("#' . $id . '").val()) );
								//}
								//pulseInfo(\'' . $this->jsSafe($this->s('bezirk_request_successfull')) . '\');
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
					pulseError(\'In diesen Bezirk kannst Du Dich nicht eintragen.\');	
					return false;		
				}
			}
			else
			{
				pulseError(\'<p><strong>Du musst eine Auswahl treffen</strong></p><p>Gibt es Deine Stadt, Deinen Bezirk oder Deine Region noch nicht, dann triff die passende übergeordnete Auswahl</p><p>Also für Köln-Ehrenfeld z. B. Köln</p><p>Und schreibe die Region die neu angelegt werden soll in das Feld unten</p>\');		
			}
		});		
	');

		return array(
			'default' => $out,
			'mobile' => $out_mob
		);
	}

	public function autolink($str, $attributes = array())
	{
		$attributes['target'] = '_blank';
		$attrs = '';
		foreach ($attributes as $attribute => $value) {
			$attrs .= " {$attribute}=\"{$value}\"";
		}
		$str = ' ' . $str;
		$str = preg_replace(
			'`([^"=\'>])(((http|https|ftp)://|www.)[^\s<]+[^\s<\.)])`i',
			'$1<a href="$2"' . $attrs . '>$2</a>',
			$str
		);
		$str = substr($str, 1);
		$str = preg_replace('`href=\"www`', 'href="http://www', $str);
		// fügt http:// hinzu, wenn nicht vorhanden
		return $str;
	}

	public function emailBodyTpl($message, $email = false, $token = false)
	{
		$unsubscribe = '
	<tr>
		<td height="20" valign="top" style="background-color:#FAF7E5">
			<div style="text-align:center;padding-top:10px;font-size:11px;font-family:Arial;padding:15px;color:#594129;">
				Willst Du diese Art von Benachrichtigungen nicht mehr bekommen? Du kannst unter <a style="color:#F36933" href="' . BASE_URL . '/?page=settings&sub=info" target="_blank">Benachrichtigungen</a> einstellen, welche Mails Du erhälst.
			</div>
		</td>
	</tr>';

		if ($email !== false && $token !== false) {
			$unsubscribe = '
		<tr>
			<td height="20" valign="top" style="background-color:#FAF7E5">
				<div style="text-align:center;padding-top:10px;font-size:11px;font-family:Arial;padding:15px;color:#594129;">
					Möchtest Du keinen Newsletter mehr erhalten? <a style="color:#F36933" href="https://www.foodsharing.de/?page=login&sub=unsubscribe&t=' . $token . '&e=' . $email . '" target="_blank">Klicke hier zum Abbestellen!</a> Du kannst unter <a style="color:#F36933" href="https://www.foodsharing.de/?page=settings&sub=info" target="_blank">Benachrichtigungen</a> einstellen, welche Mails Du erhältst.
				</div>
<p style="font-size:11px;"><strong>Impressum</strong><br />
Angaben gemäß § 5 TMG:<br />
<br />foodsharing e.<span style="white-space:nowrap">&thinsp;</span>V.<br/>
Marsiliusstr. 36<br />
50937 Köln<br />
Vertreten durch:<br /><br />
Frank Bowinkelmann<br />
Kontakt:<br />E-Mail: info@foodsharing.de<br />
Registereintrag:<br /><br />Eintragung im Vereinsregister<br />
Registergericht: Amtsgericht Köln<br />
Registernummer: VR 17439<br />
Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV:<br />
<br />Frank Bowinkelmann<br /></p>
			</td>
		</tr>';
		}

		$message = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $message);

		$search = array('<a', '<td', '<li');
		$replace = array('<a style="color:#F36933"', '<td style="font-size:13px;font-family:Arial;color:#31210C;"', '<li style="margin-bottom:11px"');

		return '<html><head><style type="text/css">a{text-decoration:none;}a:hover{text-decoration:underline;}a.button{display:inline-block;padding:6px 16px;border:1px solid #FFFFFF;background-color:#4A3520;color:#FFFFFF !important;font-weight:bold;border-radius:8px;}a.button:hover{border:1px solid #4A3520;background-color:#ffffff;color:#4A3520 !important;text-decoration:none !important;}.border{padding:10px;border-top:1px solid #4A3520;border-bottom:1px solid #4A3520;background-color:#FFFFFF;}</style></head>
	<body style="margin:0;padding:0;">
		<div style="background-color:#F1E7C9;border:1px solid #628043;border-top:0px;padding:2%;padding-top:0;margin-top:0px;">

<table width="100%" style="margin-bottom:10px;margin-top:-2px;">
<tr>
				<td valign="top" height="30" style="background-color:#4A3520">
					<div style="padding:5px;font-size:13px;font-family:Arial;color:#FAF7E5;overflow:hidden;" align="left">
						<a style="display:block;color:#FAF7E5;text-decoration:none;" href="https://www.foodsharing.de/" target="_blank">
							<span style="margin-left:10px;font-size:20px;font-family:Arial Black, Arial;font-weight:bold;color:#FAF7E5;letter-spacing:-1px;">food</span><span style="margin-right:10px;font-size:20px;font-family:Arial Black, Arial;font-weight:bold;color:#4D971E;letter-spacing:-1px">sharing</span> <span style="font-style:italic">Lebensmittelretten<span style="color:#F36933">.</span>de</span>
						</a>
					</div>
				</td></tr>
</table>
			<table height="100%" width="100%">
				<tr>
				<td valign="top" style="background-color:#FAF7E5">
					<div style="padding:5px;font-size:13px;font-family:Arial;padding:15px;color:#31210C;">
						' . str_replace($search, $replace, $message) . '
					</div>
				</td>
				</tr>
				' . $unsubscribe . '
			</table>
		</div>
	</body>
</html>';
	}

	public function tplMail($tpl_id, $to, $var = array(), $from_email = false)
	{
		global $db;
		$mail = new AsyncMail();

		if (!is_object($db)) {
			$db = new ManualDb();
		}

		if ($from_email !== false && $this->validEmail($from_email)) {
			$mail->setFrom($from_email);
		} else {
			$mail->setFrom(DEFAULT_EMAIL, DEFAULT_EMAIL_NAME);
		}

		$message = $db->getOne_message_tpl($tpl_id);

		$search = array();
		$replace = array();
		foreach ($var as $key => $v) {
			$search[] = '{' . strtoupper($key) . '}';
			$replace[] = $v;
		}

		$message['body'] = str_replace($search, $replace, $message['body']);

		$message['subject'] = str_replace($search, $replace, $message['subject']);
		if (!$message['subject']) {
			$message['subject'] = 'Foodsharing-Mail';
		}

		$mail->setSubject($message['subject']);
		$mail->setHTMLBody($this->emailBodyTpl($message['body']));

		// playintext body
		$body = str_replace(array('<br />', '<br>', '<br/>', '<p>', '</p>'), "\r\n", $message['body']);
		$body = strip_tags($body);
		$mail->setBody($body);

		$mail->addRecipient($to);
		$mail->send();
	}

	public function getOrgaMenu()
	{
		$menu = array();
		if ($this->isOrgaTeam()) {
			$menu = [
				'all_store' => 'betrieb&bid=0',
				'all_fs' => 'foodsaver&bid=0',
				'regions_without_bots' => 'geoclean&sub=lostregion',
				'manage_regions' => 'region',
				'newarea' => 'newarea'
			];
		}

		if ($this->mayEditBlog()) {
			$menu['blog'] = 'blog&sub=manage';
		}

		if ($this->isOrgaTeam()) {
			$menu['email'] = 'email';
			$menu['email_tpl'] = 'message_tpl';
			$menu['faq'] = 'faq';
			$menu['foodsaver_without_region'] = 'geoclean';
			$menu['content'] = 'content';
			$menu['foodtypes'] = 'lebensmittel';
			$menu['mailbox_manage'] = 'mailbox&a=manage';
		}

		if ($this->mayEditQuiz()) {
			$menu['quiz'] = 'quiz';
		}

		if ($this->mayHandleReports()) {
			$menu['reports'] = 'report&sub=uncom';
		}

		$len = count($menu);
		if ($len) {
			$i = 0;
			$default = '<li><a class="fNiv"><i class="fa fa-gear"></i></a><ul class="bigmenu">';
			$mob = '<optgroup label="Orga">';
			foreach ($menu as $lang_id => $link) {
				$default .= '<li><a href="/?page=' . $link . '">' . $this->s('menu_' . $lang_id) . '</a></li>';
				$mob .= '<option value="/?page' . $link . '">' . $this->s('menu_' . $lang_id) . '</option>';
				++$i;
			}
			$default .= '</ul></li>';
			$mob .= '</optgroup>';
		} else {
			$default = '';
			$mob = '';
		}

		return
			array(
				'default' => $default,
				'mobile' => $mob
			);
	}

	public function dt($ts)
	{
		return date('n. M. Y H:i', $ts) . ' Uhr';
	}

	public function makeUnique()
	{
		return md5(date('Y-m-d H:i:s') . ':' . uniqid());
	}

	public function idimg($file = false, $size)
	{
		if (!empty($file)) {
			return 'images/' . str_replace('/', '/' . $size . '_', $file);
		} else {
			return false;
		}
	}

	public function img($file = false, $size = 'mini', $format = 'q', $altimg = false)
	{
		if ($file === false) {
			$file = $_SESSION['client']['photo'];
		}

		//if(!empty($file) && substr($file,0,1) != '.')
		if (!empty($file) && file_exists('images/' . $file)) {
			if (!file_exists('images/' . $size . '_' . $format . '_' . $file)) {
				$this->resizeImg('images/' . $file, $size, $format);
			}

			return '/images/' . $size . '_' . $format . '_' . $file;
		} else {
			if ($altimg === false) {
				return '/img/' . $size . '_' . $format . '_avatar.png';
			} else {
				return $altimg;
			}
		}
	}

	public function getSettingsMenu()
	{
		$mailbox = '';
		if (S::get('mailbox')) {
			$mailbox = '<li><a href="/?page=mailbox"><i class="fa fa-envelope"></i> E-Mail-Postfach</a></li>';
		}
		$default = '<li class="g_settings"><a href="/profile/' . $this->fsId() . '" class="fNiv corner-all" style="background-image:url(' . $this->img() . ');"><span>&nbsp;</span></a>
				    <ul class="jmenu-settings">
					  <li><a href="/?page=settings"><i class="fa fa-gear"></i> Einstellungen</a></li>
					  ' . $mailbox . '
				      <li><a href="/?page=logout"><i class="fa fa-sign-out"></i> Logout</a></li>
				    </ul>
				  </li>';

		return array(
			'default' => $default,
			'mobile' => '
			<option value="/?page=settings">Einstellungen</option>
			<option value="/?page=logout">Logout</option>'
		);
	}

	public function isMob()
	{
		if (isset($_SESSION['mob']) && $_SESSION['mob'] == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function getAgMenu($ag)
	{
		$out_mob = '
		<option value="/?page=bezirk&bid=' . $ag['id'] . '&sub=forum">' . $ag['name'] . '</option>';

		$out = '
		<li><a href="/?page=bezirk&bid=' . $ag['id'] . '&sub=forum">' . $ag['name'] . '</a>
			<ul>
				<li class="menu-top"><a class="menu-top" href="/?page=bezirk&bid=' . $ag['id'] . '&sub=forum">Forum</a></li>
				<li class="menu-top"><a class="menu-top" href="/?page=bezirk&bid=' . $ag['id'] . '&sub=events">Termine</a></li>';

		if ($this->isBotFor($ag['id'])) {
			$out .= '
			<li><a href="/?page=groups&sub=edit&id=' . (int)$ag['id'] . '">Gruppe/Mitglieder verwalten</a></li>';
		}

		$out .= '
			</ul>';

		return array(
			'default' => $out . '</li>',
			'mobile' => $out_mob
		);
	}

	public function getBezirkMenu($bezirk)
	{
		global $db;

		$out = '<li><a href="/?page=bezirk&bid=' . $bezirk['id'] . '&sub=forum">' . $bezirk['name'] . '</a>
			<ul>
				<li class="menu-top"><a class="menu-top" href="/?page=bezirk&bid=' . $bezirk['id'] . '&sub=forum">Forum</a></li>
				<li class="menu-top"><a class="menu-top" href="/?page=bezirk&bid=' . $bezirk['id'] . '&sub=fairteiler">Fair-Teiler</a></li>
				<li class="menu-top"><a class="menu-top" href="/?page=bezirk&bid=' . $bezirk['id'] . '&sub=events">Termine</a></li>';

		$out_mob = '<option value="/?page=bezirk&bid=' . $bezirk['id'] . '&sub=forum">' . $bezirk['name'] . '</option>';

		if (S::may('fs')) {
			$out .= '
				<li class="menu-top"><a class="menu-top" href="/?page=betrieb&bid=' . $bezirk['id'] . '">Betriebe</a></li>';
		}

		if ($this->isBotFor($bezirk['id'])) {
			$out .= '
			<li><a href="/?page=foodsaver&bid=' . $bezirk['id'] . '">Foodsaver</a></li>
			<li><a href="/?page=passgen&bid=' . $bezirk['id'] . '">Ausweise / Verifizierungen</a></li>';
		}

		$out .= '
			</ul>';

		return array(
			'default' => $out . '</li>',
			'mobile' => $out_mob
		);
	}

	public function id($name)
	{
		$id = $this->makeId($name, $this->ids);

		$this->ids[$id] = true;

		return $id;
	}

	public function jsValidate($option, $id, $name)
	{
		$out = array('class' => '', 'msg' => array());

		if (isset($option['required'])) {
			$out['class'] .= ' required';
			if (!isset($option['required']['msg'])) {
				$out['msg']['required'] = $name . ' darf nicht leer sein';
			}
		}

		return $out;
	}

	public function handleAttach($name)
	{
		if (isset($_FILES[$name]) && $_FILES[$name]['size'] > 0) {
			$error = 0;
			$datei = $_FILES[$name]['tmp_name'];
			$size = $_FILES[$name]['size'];
			$datein = $_FILES[$name]['name'];
			$datein = strtolower($datein);
			$datein = str_replace('.jpeg', '.jpg', $datein);
			$dateiendung = strtolower(substr($datein, strlen($datein) - 4, 4));

			$new_name = uniqid() . $dateiendung;
			move_uploaded_file($datei, './data/attach/' . $new_name);

			return array(
				'name' => $datein,
				'path' => './data/attach/' . $new_name,
				'uname' => $new_name,
				'mime' => mime_content_type('./data/attach/' . $new_name),
				'size' => $size
			);
		} else {
			return false;
		}
	}

	public function checkInput($option, $name)
	{
		$class = '';
		if (isset($option['required'])) {
			$class .= ' required';
		}
		if (isset($option['required']) || isset($option['validate'])) {
			if (isset($_POST) && !empty($_POST)) {
				if (isset($option['required']) && empty($value)) {
					error($option['required']);
					$class .= ' empty';
				}
				if (isset($option['validate'])) {
					foreach ($option['validate'] as $v) {
						$func = 'valid' . ucfirst($v);
						if (!$func($value)) {
							$class .= ' error-' . $v;
						}
					}
				}
			}
		}

		if (!empty($class)) {
			$class .= ' input-error';
		}

		return $class;
	}

	public function getPost($id)
	{
		return $_POST[$id];
	}

	public function getPostData()
	{
		if (isset($_POST)) {
			return $_POST;
		} else {
			return array();
		}
	}

	public function getValue($id)
	{
		global $g_data;

		if (isset($g_data[$id])) {
			return $g_data[$id];
		} else {
			return '';
		}
	}

	public function jsSafe($str, $quote = "'")
	{
		return str_replace(array($quote, "\n", "\r"), array('\\' . $quote . '', '\\n', ''), $str);
	}

	public function goPage($page = false)
	{
		if (!$page) {
			$page = $this->getPage();
			if (isset($_GET['bid'])) {
				$page .= '&bid=' . (int)$_GET['bid'];
			}
		}
		$this->go('/?page=' . $page);
	}

	public function go($url)
	{
		header('Location: ' . $url);
		exit();
	}

	public function goLogin()
	{
		$this->go('/?page=login&ref=' . urlencode($_SERVER['REQUEST_URI']));
	}

	public function goBack()
	{
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		exit();
	}

	public function getBezirkId()
	{
		global $db;

		return $db->getCurrentBezirkId();
	}

	public function getPage()
	{
		$page = $this->getGet('page');
		if (!$page) {
			$page = 'index';
		}

		return $page;
	}

	public function getGetId($name)
	{
		if (isset($_GET[$name]) && (int)$_GET[$name] > 0) {
			return (int)$_GET[$name];
		} else {
			return false;
		}
	}

	public function getGet($name)
	{
		if (isset($_GET[$name])) {
			return $_GET[$name];
		} else {
			return false;
		}
	}

	public function addGet($name, $val)
	{
		$url = '';

		$vars = explode('&', $_SERVER['QUERY_STRING']);

		$i = 0;
		foreach ($vars as $v) {
			++$i;
			$ex = explode('=', $v);
			if ($ex[0] != $name) {
				$url .= '&' . $v;
			}
		}

		return $_SERVER['PHP_SELF'] . '?' . substr($url, 1) . '&' . $name . '=' . $val;
	}

	public function qs($txt)
	{
		return $txt;
	}

	public function safe_html($txt)
	{
		return $txt;
	}

	public function printHidden()
	{
		if (!empty($this->hidden)) {
			echo '<div style="display:none;">' . $this->hidden . '</div>';
		}
	}

	public function getHidden()
	{
		return $this->hidden;
	}

	public function addHidden($html)
	{
		$this->hidden .= $html;
	}

	public function makeId($text, $ids = false)
	{
		$text = strtolower($text);
		str_replace(
			array('ä', 'ö', 'ü', 'ß', ' '),
			array('ae', 'oe', 'ue', 'ss', '_'),
			$text
		);
		$out = preg_replace('/[^a-z0-9_]/', '', $text);

		if ($ids !== false && isset($ids[$out])) {
			$id = $out;
			$i = 0;
			while (isset($ids[$id])) {
				++$i;
				$id = $out . '-' . $i;
			}
			$out = $id;
		}

		return $out;
	}

	public function submitted()
	{
		if (isset($_POST) && !empty($_POST)) {
			return true;
		}

		return false;
	}

	public function info($msg, $title = false)
	{
		$t = '';
		if ($title !== false) {
			$t = '<strong>' . $title . '</strong> ';
		}
		$_SESSION['msg']['info'][] = $msg;
	}

	public function success($msg, $title = false)
	{
		$t = '';
		if ($title !== false) {
			$t = '<strong>' . $title . '</strong> ';
		}
		$_SESSION['msg']['success'][] = $t . $msg;
	}

	public function error($msg, $title = false)
	{
		$t = '';
		if ($title !== false) {
			$t = '<strong>' . $title . '</strong> ';
		}
		$_SESSION['msg']['error'][] = $t . $msg;
	}

	public function session_init()
	{
		ini_set('session.use_only_cookies', 1);
		//ini_set("session.cookie_lifetime","86400");
		//@session_name('fs_session');
		@session_start();
		if (false) {
			$session_name = 'fs_session'; // Set a custom session name
			$secure = false; // Set to true if using https.
			$httponly = true; // This stops javascript being able to access the session id.

			ini_set('session.use_only_cookies', 1); // Forces sessions to only use cookies.
			$cookieParams = session_get_cookie_params(); // Gets current cookies params.
			session_set_cookie_params($cookieParams['lifetime'], $cookieParams['path'], $cookieParams['domain'], $secure, $httponly);
			session_name($session_name); // Sets the session name to the one set above.
			session_start(); // Start the php session
			session_regenerate_id(true); // regenerated the session, delete the old one.
		}

		if (!isset($_SESSION['msg'])) {
			$_SESSION['msg'] = array();
			$_SESSION['msg']['info'] = array();
			$_SESSION['msg']['error'] = array();
			$_SESSION['msg']['success'] = array();
		}
	}

	public function getMessages()
	{
		global $g_error;
		global $g_info;
		if (!isset($_SESSION['msg'])) {
			$_SESSION['msg'] = array();
		}
		if (isset($_SESSION['msg']['error']) && !empty($_SESSION['msg']['error'])) {
			$msg = '';
			foreach ($_SESSION['msg']['error'] as $e) {
				$msg .= '<div class="item">' . $e . '</div>';
				//addJs('error("'.$e.'");');
			}
			$this->addJs('pulseError("' . $this->jsSafe($msg, '"') . '");');
		}
		if (isset($_SESSION['msg']['info']) && !empty($_SESSION['msg']['info'])) {
			$msg = '';
			foreach ($_SESSION['msg']['info'] as $i) {
				$msg .= '<p>' . $i . '</p>';
				//addJs('info("'.$i.'");');
			}
			$this->addJs('pulseInfo("' . $this->jsSafe($msg, '"') . '");');
		}
		if (isset($_SESSION['msg']['info']) && !empty($_SESSION['msg']['info'])) {
			$msg = '';
			foreach ($_SESSION['msg']['info'] as $i) {
				$msg .= '<p>' . $i . '</p>';
			}
			$this->addJs('pulseSuccess("' . $this->jsSafe($msg, '"') . '");');
		}
		$_SESSION['msg']['info'] = array();
		$_SESSION['msg']['success'] = array();
		$_SESSION['msg']['error'] = array();
	}

	public function save($txt)
	{
		return preg_replace('/[^a-zA-Z0-9]/', '', $txt);
	}

	public function loggedIn()
	{
		if (isset($_SESSION['client']) && $_SESSION['client']['id'] > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function addScript($src)
	{
		$this->script[] = $src;
	}

	public function addScriptTop($src)
	{
		array_unshift($this->script, $src);
	}

	public function getFqcnPrefix($module)
	{
		return '\\Foodsharing\\Modules\\' . $module . '\\';
	}

	public function loadApp($app)
	{
		$className = $app . 'Control';
		$fqcn = $this->getFqcnPrefix($app) . $className;

		$appInstance = DI::$shared->get(ltrim($fqcn, '\\'));

		if (isset($_GET['a']) && method_exists($appInstance, $_GET['a'])) {
			$meth = $_GET['a'];
			$appInstance->$meth();
		} else {
			$appInstance->index();
		}

		if (($sub = $appInstance->getSubFunc()) !== false) {
			$appInstance->$sub();
		}

		return $appInstance;
	}

	public function addJsFunc($nfunc)
	{
		$this->js_func .= $nfunc;
	}

	// $js is echoed in tpl/default.php
	public function addJs($njs)
	{
		$this->js .= $njs;
	}

	public function addCssTop($src)
	{
		array_unshift($this->css, $src);
	}

	public function addCss($src)
	{
		$this->css[] = $src;
	}

	public function getAddCss()
	{
		return $this->add_css;
	}

	public function getJsFunc()
	{
		return JSMin::minify($this->js_func);
	}

	public function getJs()
	{
		return JSMin::minify($this->js);
	}

	public function makeHead()
	{
		foreach ($this->css as $src) {
			$this->head .= '<link rel="stylesheet" type="text/css" href="' . $src . '" />' . "\n";
		}
		foreach ($this->script as $src) {
			$this->head .= '<script type="text/javascript" src="' . $src . '"></script>' . "\n";
		}
	}

	public function addHead($str)
	{
		$this->head .= "\n" . $str;
	}

	public function addTitle($name)
	{
		global $title;
		$this->title[] = $name;
	}

	public function getHead()
	{
		foreach ($this->meta as $name => $content) {
			$this->head .= "\n" . '<meta name="' . $name . '" content="' . $content . '" />';
		}

		return '<title>' . implode(' | ', $this->title) . '</title>' .
			$this->head . '

<meta property="og:title" content="Lebensmittel teilen, statt wegwerfen - foodsharing Deutschland" />
<meta property="og:description" content="Auf foodsharing kannst Du Deine Lebensmitteln vor dem Verfall an soziale Einrichtungen oder andere Personen abgeben" />
<meta property="og:image" content="http://foodsharing.de/img/foodsharinglogo_200px.png" />
<meta property="og:url" content="http://foodsharing.de" />';
	}

	public function setTitle($name)
	{
		$this->title = array($name);
	}

	public function pv($el)
	{
		//return '<pre>'.print_r($el,true).'</pre>';
	}

	public function fsId()
	{
		if ($this->loggedIn()) {
			return $_SESSION['client']['id'];
		} else {
			return 0;
		}
	}

	public function isVerified()
	{
		if ($this->isOrgaTeam()) {
			return true;
		} elseif (isset($_SESSION['client']['verified']) && $_SESSION['client']['verified'] == 1) {
			return true;
		}

		return false;
	}

	public function validEmail($email)
	{
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return true;
		} else {
			return false;
		}
	}

	public function validUrl($url)
	{
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			return false;
		}

		return true;
	}

	public function isAdmin()
	{
		return $this->mayGroup('admin') && $_SESSION['client']['group']['admin'] === true;
	}

	public function logg($arg)
	{
		file_put_contents(ROOT_DIR . 'data/logg.txt', json_encode(array('date' => date('Y-m-d H:i:s'), 'session' => $_SESSION, 'data' => $arg, 'add' => array($_GET))) . '-|||-', FILE_APPEND);
	}

	public function libmail($bezirk, $email, $subject, $message, $attach = false, $token = false)
	{
		if ($bezirk === false) {
			$bezirk = array(
				'email' => DEFAULT_EMAIL,
				'email_name' => DEFAULT_EMAIL_NAME
			);
		} elseif (!is_array($bezirk)) {
			$bezirk = array(
				'email' => $bezirk,
				'email_name' => $bezirk
			);
		} else {
			if (!$this->validEmail($bezirk['email'])) {
				$bezirk['email'] = EMAIL_PUBLIC;
			}
			if (empty($bezirk['email_name'])) {
				$bezirk['email_name'] = EMAIL_PUBLIC_NAME;
			}
		}

		if (!$this->validEmail($email)) {
			return false;
		}

		$mail = new AsyncMail();
		$mail->setFrom($bezirk['email'], $bezirk['email_name']);
		$mail->addRecipient($email);
		if (!$subject) {
			$subject = 'Foodsharing-Mail';
		}
		$mail->setSubject($subject);
		$mail->setHTMLBody($this->emailBodyTpl($message, $email, $token));

		//Replace the plain text body with one created manually
		$message = str_replace('<br />', "\r\n", $message);
		$message = strip_tags($message);
		$mail->setBody($message);

		if ($attach !== false) {
			foreach ($attach as $a) {
				$mail->addAttachment(new fFile($a['path']), $a['name']);
			}
		}

		$mail->send();
	}

	/**
	 * @param $sender_id
	 * @param $recip_id
	 * @param null $msg
	 */
	public function mailMessage($sender_id, $recip_id, $msg = null)
	{
		// FIXME this function is pretty much a copy of Model::mailMessage() and should probably replaced
		$db = new ManualDb();

		$info = $db->getVal('infomail_message', 'foodsaver', $recip_id);
		if ((int)$info > 0) {
			if (!isset($_SESSION['lastMailMessage'])) {
				$_SESSION['lastMailMessage'] = array();
			}
			if (!$db->isActive($recip_id)) {
				if (!isset($_SESSION['lastMailMessage'][$recip_id]) || (time() - $_SESSION['lastMailMessage'][$recip_id]) > 600) {
					$_SESSION['lastMailMessage'][$recip_id] = time();
					$foodsaver = $db->getOne_foodsaver($recip_id);
					$sender = $db->getOne_foodsaver($sender_id);
					if (!isset($msg)) {
						// FIXME this is error-prone;
						$msg = '';
					}

					$this->tplMail(9, $foodsaver['email'], array(
						'anrede' => $this->genderWord($foodsaver['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
						'sender' => $sender['name'],
						'name' => $foodsaver['name'],
						'message' => $msg,
						'link' => BASE_URL . '/?page=msg&u2c=' . (int)$sender_id
					));
				}
			}
		}
	}

	public function getBezirk()
	{
		global $db;

		return $db->getBezirk();
	}

	public function genderWord($gender, $m, $w, $other)
	{
		$out = $other;
		if ($gender == 1) {
			$out = $m;
		} elseif ($gender == 2) {
			$out = $w;
		}

		return $out;
	}

	public function hiddenDialog($table, $fields, $title = '', $option = array())
	{
		$width = '';
		if (isset($option['width'])) {
			$width = 'width:' . $option['width'] . ',';
		}
		$id = $this->id('dialog_' . $table);

		$form = '';
		foreach ($fields as $f) {
			$form .= $f;
		}

		$get = '';
		if (isset($_GET['id'])) {
			$get = '<input type="hidden" name="id" value="' . (int)$_GET['id'] . '" />';
		}

		$this->addHidden('<div id="' . $id . '"><form>' . $form . $get . '</form></div>');
		//addJs('hiddenDialog("'.$id.'","'.$table.'","'.$title.'");');

		$success = '';
		if (isset($option['success'])) {
			$success = $option['success'];
		}

		if (isset($option['reload'])) {
			$success .= 'reload();';
		}

		$this->addJs('
		$("#' . $id . '").dialog({
		' . $width . '
		autoOpen:false,
		modal:true,
		title:"' . $title . '",
		buttons:
		{
			"Speichern":function()
			{
				showLoader();
				$.ajax({
					
					dataType:"json",
					url:"xhr.php?f=update_' . $table . '&" + $("#' . $id . ' form").serialize(),
					success : function(data){
						$("#' . $id . '").dialog(\'close\');
						' . $success . '
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

	public function compress($buffer)
	{
		return JSMin::minify($buffer);
	}

	public function hasBezirk($bid)
	{
		if (isset($_SESSION['client']['bezirke'][$bid]) || $this->isBotFor($bid)) {
			return true;
		}

		return false;
	}

	public function mayBezirk($bid)
	{
		if (isset($_SESSION['client']['bezirke'][$bid]) || $this->isBotFor($bid) || $this->isOrgaTeam()) {
			return true;
		}

		return false;
	}

	public function mayGroup($group)
	{
		if (isset($_SESSION) && isset($_SESSION['client']['group'][$group])) {
			return true;
		}

		return false;
	}

	public function mayHandleReports()
	{
		// group "Verstöße/Meldungen"
		return S::may('orga') || $this->isBotFor(432);
	}

	public function mayEditQuiz()
	{
		return S::may('orga') || $this->isBotFor(341);
	}

	public function mayEditBlog()
	{
		if ($all_group_admins = Mem::get('all_global_group_admins')) {
			return S::may('orga') || in_array($this->fsId(), unserialize($all_group_admins));
		}

		return S::may('orga');
	}

	public function may()
	{
		if (isset($_SESSION) && isset($_SESSION['client']) && (int)$_SESSION['client']['id'] > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function getRolle($gender_id, $rolle_id)
	{
		return $this->s('rolle_' . $rolle_id . '_' . $gender_id);
	}

	public function cropImg($path, $img, $i, $x, $y, $w, $h)
	{
		$targ_w = $w;
		$targ_h = $h;
		$jpeg_quality = 100;

		$ext = explode('.', $img);
		$ext = end($ext);
		$ext = strtolower($ext);

		switch ($ext) {
			case 'gif':
				$img_r = imagecreatefromgif($path . '/' . $img);
				break;
			case 'jpg':
				$img_r = imagecreatefromjpeg($path . '/' . $img);
				break;
			case 'png':
				$img_r = imagecreatefrompng($path . '/' . $img);
				break;
			default:
				$img_r = null;
		}

		$dst_r = imagecreatetruecolor($targ_w, $targ_h);

		imagecopyresampled($dst_r, $img_r, 0, 0, $x, $y, $targ_w, $targ_h, $w, $h);

		$new_path = $path . '/crop_' . $i . '_' . $img;

		@unlink($new_path);

		switch ($ext) {
			case 'gif':
				imagegif($dst_r, $new_path);
				break;
			case 'jpg':
				imagejpeg($dst_r, $new_path, $jpeg_quality);
				break;
			case 'png':
				imagepng($dst_r, $new_path, 0);
				break;
		}
	}

	public function cropImage($bild, $x, $y, $w, $h)
	{
		$targ_w = 467;
		$targ_h = 600;
		$jpeg_quality = 100;

		$ext = explode('.', $bild);
		$ext = end($ext);
		$ext = strtolower($ext);

		$img_r = null;

		switch ($ext) {
			case 'gif':
				$img_r = imagecreatefromgif('./tmp/' . $bild);
				break;
			case 'jpg':
				$img_r = imagecreatefromjpeg('./tmp/' . $bild);
				break;
			case 'png':
				$img_r = imagecreatefrompng('./tmp/' . $bild);
				break;
		}

		if ($img_r === null) {
			return false;
		}

		$dst_r = imagecreatetruecolor($targ_w, $targ_h);

		imagecopyresampled($dst_r, $img_r, 0, 0, $x, $y, $targ_w, $targ_h, $w, $h);

		@unlink('../tmp/crop_' . $bild);

		switch ($ext) {
			case 'gif':
				imagegif($dst_r, './tmp/crop_' . $bild);
				break;
			case 'jpg':
				imagejpeg($dst_r, './tmp/crop_' . $bild, $jpeg_quality);
				break;
			case 'png':
				imagepng($dst_r, './tmp/crop_' . $bild, 0);
				break;
		}

		if (file_exists('./tmp/crop_' . $bild)) {
			try {
				copy('./tmp/crop_' . $bild, './tmp/thumb_crop_' . $bild);
				$img = new fImage('./tmp/thumb_crop_' . $bild);
				$img->resize(200, 0);
				$img->saveChanges();

				return 'thumb_crop_' . $bild;
			} catch (Exception $e) {
				return false;
			}
		}

		return false;
	}

	public function resizeImg($img, $width, $format)
	{
		if (file_exists($img)) {
			$opt = 'auto';
			if ($format == 'q') {
				$opt = 'crop';
			}

			try {
				$newimg = str_replace('/', '/' . $width . '_' . $format . '_', $img);
				copy($img, $newimg);
				$img = new fImage($newimg);

				if ($opt == 'crop') {
					$img->cropToRatio(1, 1);
					$img->resize($width, $width);
				} else {
					$img->resize($width, 0);
				}

				$img->saveChanges();

				return true;
			} catch (Exception $e) {
			}
		}

		return false;
	}

	public function addStyle($css)
	{
		$this->add_css .= trim($css);
	}

	public function clearPost()
	{
		$this->go($this->getSelf());
	}

	public function getSelf()
	{
		return $_SERVER['REQUEST_URI'];
	}

	public function unsetAll($array, $fields)
	{
		$out = array();
		foreach ($fields as $f) {
			if (isset($array[$f])) {
				$out[$f] = $array[$f];
			}
		}

		return $out;
	}

	public function is_allowed($img)
	{
		$img['name'] = strtolower($img['name']);
		$img['type'] = strtolower($img['type']);

		$allowed = array('jpg' => true, 'jpeg' => true, 'png' => true, 'gif' => true);

		$filename = $img['name'];
		$parts = explode('.', $filename);
		$ext = end($parts);

		$allowed_mime = array('image/gif' => true, 'image/jpeg' => true, 'image/png' => true);

		if (isset($allowed[$ext])) {
			return true;
		}

		return false;
	}

	public function tt($str, $length = 160)
	{
		if (strlen($str) > $length) {
			$str = preg_replace('/[^ ]*$/', '', substr($str, 0, $length)) . ' ...';
		}

		return $str;
	}

	public function ttt($str, $length = 160)
	{
		if (strlen($str) > $length) {
			$str = substr($str, 0, ($length - 4)) . '...';
		}

		return $str;
	}

	public function avatar($foodsaver, $size = 'mini', $altimg = false)
	{
		/*
		 * temporary for quiz
		 */
		$bg = '';
		if (isset($foodsaver['quiz_rolle'])) {
			switch ($foodsaver['quiz_rolle']) {
				case 1:
					$bg = 'box-sizing:border-box;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;border:3px solid #4A3520;';
					break;
				case 2:
					$bg = 'box-sizing:border-box;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;border:3px solid #599022;';
					break;
				case 3:
					$bg = 'box-sizing:border-box;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;border:3px solid #FFBB00;';
					break;
				case 4:
					$bg = 'box-sizing:border-box;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;border:3px solid #FF4800;';
					break;
				default:
					break;
			}
		}

		return '<span style="' . $bg . 'background-image:url(' . $this->img($foodsaver['photo'], $size, 'q', $altimg) . ');" class="avatar size-' . $size . ' sleepmode-' . $foodsaver['sleep_status'] . '"><i>' . $foodsaver['name'] . '</i></span>';
	}

	public function rolleWrapInt($roleInt)
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

	public function rolleWrap($roleStr)
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

	public function sendSock($fsid, $app, $method, $options)
	{
		$query = http_build_query(array(
			'u' => $fsid, // user id
			'a' => $app, // app
			'm' => $method, // method
			'o' => json_encode($options) // options
		));
		file_get_contents(SOCK_URL . '?' . $query);
	}

	public function sendSockMulti($fsids, $app, $method, $options)
	{
		$query = http_build_query(array(
			'us' => join(',', $fsids), // user ids
			'a' => $app, // app
			'm' => $method, // method
			'o' => json_encode($options) // options
		));
		file_get_contents(SOCK_URL . '?' . $query);
	}

	public function getTemplate($tpl)
	{
		include 'tpl/' . $tpl . '.php';
	}

	public function getIp()
	{
		if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['REMOTE_ADDR'];
		} else {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		return false;
	}

	/**
	 * Function to check and block an ip address.
	 *
	 * @param int $duration
	 * @param string $context
	 *
	 * @return bool
	 */
	public function ipIsBlocked($duration = 60, $context = 'default')
	{
		$db = new ManualDb();
		$ip = $this->getIp();

		if ($block = $db->qRow('SELECT UNIX_TIMESTAMP(`start`) AS `start`,`duration` FROM ' . PREFIX . 'ipblock WHERE ip = ' . $db->strval($this->getIp()) . ' AND context = ' . $db->strval($context))) {
			if (time() < ((int)$block['start'] + (int)$block['duration'])) {
				return true;
			}
		}

		$db->insert('
	REPLACE INTO ' . PREFIX . 'ipblock
	(`ip`,`context`,`start`,`duration`)
	VALUES
	(' . $db->strval($ip) . ',' . $db->strval($context) . ',NOW(),' . (int)$duration . ')');

		return false;
	}

	/** Creates and saves a new API token for given user
	 * @param $fs Foodsaver ID
	 *
	 * @return false in case of error or weak algorithm, generated token otherwise
	 */
	public function generate_api_token($fs)
	{
		global $db;

		$token = bin2hex(openssl_random_pseudo_bytes(10, $strong));
		if (!$strong || $token === false) {
			return false;
		}

		$db->insert('INSERT INTO ' . PREFIX . 'apitoken (foodsaver_id, token) VALUES (' . (int)$fs . ', "' . $token . '")');

		return $token;
	}
}
