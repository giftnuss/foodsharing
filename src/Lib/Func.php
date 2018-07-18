<?php

namespace Foodsharing\Lib;

use Exception;
use Flourish\fDate;
use Flourish\fFile;
use Flourish\fImage;
use Foodsharing\DI;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Mail\AsyncMail;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\EmailTemplateAdmin\EmailTemplateGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Services\OutputSanitizerService;
use JSMin\JSMin;

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
	private $scripts;
	private $stylesheets;
	private $add_css;
	private $viewUtils;
	private $outputSanitizerService;

	private $webpackScripts;
	private $webpackStylesheets;

	public $jsData = [];

	/**
	 * @var \Twig\Environment
	 */
	private $twig;

	/**
	 * @var Session
	 */
	private $session;

	public function __construct(Utils $viewUtils, OutputSanitizerService $outputSanitizerService)
	{
		$this->viewUtils = $viewUtils;
		$this->outputSanitizerService = $outputSanitizerService;
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
		$this->scripts = array();
		$this->stylesheets = array();
		$this->add_css = '';
	}

	/**
	 * @required
	 */
	public function setTwig(\Twig\Environment $twig)
	{
		$this->twig = $twig;
	}

	/**
	 * @required
	 */
	public function setSession(Session $session)
	{
		$this->session = $session;
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
		return $this->bread;
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

	public function isBotForA($bezirk_ids, $include_groups = true, $include_parent_bezirke = false)
	{
		if ($this->session->isBotschafter() && is_array($bezirk_ids)) {
			if ($include_parent_bezirke) {
				/* @var $gw RegionGateway */
				$gw = DI::$shared->get(RegionGateway::class);
				$bezirk_ids = $gw->getParentBezirke($bezirk_ids);
			}
			foreach ($_SESSION['client']['botschafter'] as $b) {
				foreach ($bezirk_ids as $bid) {
					if ($b['bezirk_id'] == $bid && ($include_groups || $b['type'] != Type::WORKING_GROUP)) {
						return true;
						break;
					}
				}
			}
		}

		return false;
	}

	/**
	 * @deprecated use $this->session->isAdminFor($regionId) instead
	 */
	public function isBotFor($bezirk_id)
	{
		return $this->session->isAdminFor($bezirk_id);
	}

	/**
	 * @deprecated use $this->session->isBotschafter() instead
	 */
	public function isBotschafter()
	{
		return $this->session->isBotschafter();
	}

	/**
	 * @deprecated use $this->session->isOrgaTeam() instead
	 */
	public function isOrgaTeam()
	{
		return $this->session->isOrgaTeam();
	}

	public function getMenu()
	{
		$regions = [];
		$stores = [];
		$workingGroups = [];
		if (isset($_SESSION['client']['bezirke']) && is_array($_SESSION['client']['bezirke'])) {
			foreach ($_SESSION['client']['bezirke'] as $region) {
				$region = array_merge($region, ['isBot' => $this->isBotFor($region['id'])]);
				if ($region['type'] == Type::WORKING_GROUP) {
					$workingGroups[] = $region;
				} else {
					$regions[] = $region;
				}
			}
		}
		if (isset($_SESSION['client']['betriebe']) && is_array($_SESSION['client']['betriebe'])) {
			$stores = $_SESSION['client']['betriebe'];
		}

		$loggedIn = $this->session->may();

		return $this->getMenuFn(
			$loggedIn,
			$regions,
			$this->session->may('fs'),
			$this->isOrgaTeam(),
			$this->mayEditBlog(),
			$this->mayEditQuiz(),
			$this->mayHandleReports(),
			$stores,
			$workingGroups,
			$this->session->get('mailbox'),
			$this->fsId(),
			$loggedIn ? $this->img() : ''
		);
	}

	public function getMenuFn(
		bool $loggedIn, array $regions, bool $hasFsRole,
		bool $isOrgaTeam, bool $mayEditBlog, bool $mayEditQuiz, bool $mayHandleReports,
		array $stores, array $workingGroups,
		$sessionMailbox, int $fsId, string $image)
	{
		$params = array_merge([
			'loggedIn' => $loggedIn,
			'fsId' => $fsId,
			'image' => $image,
			'mailbox' => $sessionMailbox,
			'hasFsRole' => $hasFsRole,
			'isOrgaTeam' => $isOrgaTeam,
			'may' => [
				'editBlog' => $mayEditBlog,
				'editQuiz' => $mayEditQuiz,
				'handleReports' => $mayHandleReports
			],
			'stores' => $stores,
			'regions' => $regions,
			'workingGroups' => $workingGroups
		]);

		return [
			'default' => $this->twig->render('partials/menu.default.twig', $params),
			'mobile' => $this->twig->render('partials/menu.mobile.twig', $params)
		];
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
					Möchtest Du keinen Newsletter mehr erhalten? <a style="color:#F36933" href="https://foodsharing.de/?page=login&sub=unsubscribe&t=' . $token . '&e=' . $email . '" target="_blank">Klicke hier zum Abbestellen!</a> Du kannst unter <a style="color:#F36933" href="https://foodsharing.de/?page=settings&sub=info" target="_blank">Benachrichtigungen</a> einstellen, welche Mails Du erhältst.
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
						<a style="display:block;color:#FAF7E5;text-decoration:none;" href="https://foodsharing.de/" target="_blank">
							<span style="margin-left:10px;font-size:20px;font-family:Arial Black, Arial;font-weight:bold;color:#FAF7E5;letter-spacing:-1px;">food</span><span style="margin-right:10px;font-size:20px;font-family:Arial Black, Arial;font-weight:bold;color:#4D971E;letter-spacing:-1px">sharing</span><span style="color:#F36933">.</span>de
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
		$mail = new AsyncMail();

		if ($from_email !== false && $this->validEmail($from_email)) {
			$mail->setFrom($from_email);
		} else {
			$mail->setFrom(DEFAULT_EMAIL, DEFAULT_EMAIL_NAME);
		}

		/* @var $gw EmailTemplateGateway */
		$gw = DI::$shared->get(EmailTemplateGateway::class);
		$message = $gw->getOne_message_tpl($tpl_id);

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

		$mail->setSubject($this->outputSanitizerService->sanitizeForText($message['subject']));
		$htmlBody = $this->emailBodyTpl($message['body']);
		$mail->setHTMLBody($htmlBody);

		// playintext body
		$plainBody = $this->outputSanitizerService->sanitizeForText($htmlBody);
		$mail->setBody($plainBody);

		$mail->addRecipient($to);
		$mail->send();
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

	public function isMob()
	{
		if (isset($_SESSION['mob']) && $_SESSION['mob'] == 1) {
			return true;
		} else {
			return false;
		}
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

	public function getPage()
	{
		$page = $this->getGet('page');
		if (!$page) {
			$page = 'index';
		}

		return $page;
	}

	public function getSubPage()
	{
		$sub_page = $this->getGet('sub');
		if (!$sub_page) {
			$sub_page = 'index';
		}

		return $sub_page;
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

	public function addWebpackScript($src)
	{
		$this->webpackScripts[] = $src;
	}

	public function addScriptTop($src)
	{
		array_unshift($this->scripts, $src);
	}

	public function addJsFunc($nfunc)
	{
		$this->js_func .= $nfunc;
	}

	public function addJs($njs)
	{
		$this->js .= $njs;
	}

	public function addWebpackStylesheet($src)
	{
		$this->webpackStylesheets[] = $src;
	}

	public function addHead($str)
	{
		$this->head .= "\n" . $str;
	}

	public function addTitle($name)
	{
		$this->title[] = $name;
	}

	public function getTranslations()
	{
		global $g_lang;

		return $g_lang;
	}

	/**
	 * This is used to set window.serverData on in the frontend.
	 */
	public function getServerData()
	{
		$user = $this->session->get('user');

		$userData = [
			'id' => $this->fsId(),
			'firstname' => $user['name'],
			'lastname' => $user['nachname'],
			'may' => $this->session->may(),
			'verified' => $this->isVerified(),
			'avatar' => [
				'mini' => $this->img($user['photo'], 'mini'),
				'50' => $this->img($user['photo'], '50'),
				'130' => $this->img($user['photo'], '130')
			]
		];

		if ($this->session->may()) {
			$userData['token'] = $this->session->user('token');
		}

		$location = null;

		if ($pos = $this->session->get('blocation')) {
			$location = [
				'lat' => floatval($pos['lat']),
				'lon' => floatval($pos['lon']),
			];
		}

		$ravenConfig = null;

		if (defined('RAVEN_JAVASCRIPT_CONFIG')) {
			$ravenConfig = RAVEN_JAVASCRIPT_CONFIG;
		}

		return array_merge($this->jsData, [
			'user' => $userData,
			'page' => $this->getPage(),
			'subPage' => $this->getSubPage(),
			'location' => $location,
			'ravenConfig' => $ravenConfig,
			'translations' => $this->getTranslations(),
			'GOOGLE_API_KEY' => GOOGLE_API_KEY
		]);
	}

	public function getHeadData()
	{
		return [
			'title' => implode(' | ', $this->title),
			'extra' => $this->head,
			'css' => str_replace(["\r", "\n"], '', $this->add_css),
			'jsFunc' => JSMin::minify($this->js_func),
			'js' => JSMin::minify($this->js),
			'ravenConfig' => null,
			'stylesheets' => $this->webpackStylesheets,
			'scripts' => $this->webpackScripts
		];
	}

	public function generateAndGetGlobalViewData()
	{
		global $g_broadcast_message;
		global $g_body_class;
		global $content_left_width;
		global $content_right_width;

		$menu = $this->getMenu();

		$this->getMessages();

		$mainwidth = 24;

		$content_left = $this->getContent(CNT_LEFT);
		$content_right = $this->getContent(CNT_RIGHT);

		if (!empty($content_left)) {
			$mainwidth -= $content_left_width;
		}

		if (!empty($content_right)) {
			$mainwidth -= $content_right_width;
		}

		$msgbar = '';
		$logolink = '/';
		if ($this->session->may()) {
			$msgbar = $this->viewUtils->v_msgBar();
			$logolink = '/?page=dashboard';
		} else {
			$msgbar = $this->viewUtils->v_login();
		}

		return [
			'head' => $this->getHeadData(),
			'bread' => $this->getBread(),
			'bodyClass' => $g_body_class,
			'msgbar' => $msgbar,
			'serverDataJSON' => json_encode($this->getServerData()),
			'menu' => $menu,
			'dev' => FS_ENV == 'dev',
			'hidden' => $this->getHidden(),
			'isMob' => $this->isMob(),
			'logolink' => $logolink,
			'broadcast_message' => $g_broadcast_message,
			'SRC_REVISION' => defined('SRC_REVISION') ? SRC_REVISION : null,
			'HTTP_HOST' => $_SERVER['HTTP_HOST'],
			'is_foodsharing_dot_at' => strpos($_SERVER['HTTP_HOST'], 'foodsharing.at') !== false,
			'content' => [
				'main' => [
					'html' => $this->getContent(CNT_MAIN),
					'width' => $mainwidth
				],
				'left' => [
					'html' => $content_left,
					'width' => $content_left_width,
					'id' => 'left'
				],
				'right' => [
					'html' => $content_right,
					'width' => $content_right_width,
					'id' => 'right'
				],
				'top' => [
					'html' => $this->getContent(CNT_TOP),
					'id' => 'content_top'
				],
				'bottom' => [
					'html' => $this->getContent(CNT_BOTTOM),
					'id' => 'content_bottom'
				],
				'overtop' => [
					'html' => $this->getContent(CNT_OVERTOP)
				]
			]
		];
	}

	public function setTitle($name)
	{
		$this->title = array($name);
	}

	/**
	 * @deprecated use $this->session->id() instead
	 */
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
		$htmlBody = $this->emailBodyTpl($message, $email, $token);
		$mail->setHTMLBody($htmlBody);

		$plainBody = $this->outputSanitizerService->sanitizeForText($htmlBody);
		$mail->setBody($message);

		if ($attach !== false) {
			foreach ($attach as $a) {
				$mail->addAttachment(new fFile($a['path']), $a['name']);
			}
		}

		$mail->send();
	}

	public function getBezirk()
	{
		/* @var $gw RegionGateway */
		$gw = DI::$shared->get(RegionGateway::class);

		return $gw->getBezirk($this->session->getCurrentBezirkId());
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
					url:"/xhr.php?f=update_' . $table . '&" + $("#' . $id . ' form").serialize(),
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

	public function mayBezirk($bid): bool
	{
		return isset($_SESSION['client']['bezirke'][$bid]) || $this->isBotFor($bid) || $this->isOrgaTeam();
	}

	/**
	 * @deprecated use $this->session->mayGroup($group) instead
	 */
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
		return $this->session->may('orga') || $this->isBotFor(432);
	}

	public function mayEditQuiz()
	{
		return $this->session->may('orga') || $this->isBotFor(341);
	}

	public function mayEditBlog()
	{
		if ($all_group_admins = Mem::get('all_global_group_admins')) {
			return $this->session->may('orga') || in_array($this->fsId(), unserialize($all_group_admins));
		}

		return $this->session->may('orga');
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

	public function goSelf()
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
			/* this removes the part of the last word that might have been destroyed by substr */
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

	// https://stackoverflow.com/a/834355
	public function endsWith($haystack, $needle)
	{
		$length = strlen($needle);

		return $length === 0 ||
			(substr($haystack, -$length) === $needle);
	}
}
