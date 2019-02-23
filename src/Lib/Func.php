<?php

namespace Foodsharing\Lib;

use Foodsharing\Modules\Region\RegionGateway;

final class Func
{
	private $ids;
	private $regionGateway;

	/**
	 * @var Session
	 */
	private $session;

	public function __construct(
		RegionGateway $regionGateway
	) {
		$this->regionGateway = $regionGateway;
		$this->ids = array();
	}

	/**
	 * @required
	 */
	public function setSession(Session $session)
	{
		$this->session = $session;
	}

	public function s($id)
	{
		global $g_lang;

		if (isset($g_lang[$id])) {
			return $g_lang[$id];
		}

		return $id;
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
		}

		return str_replace('{var}', $var, $g_lang[$id]);
	}

	public function setEditData($data)
	{
		global $g_data;
		$g_data = $data;
	}

	public function getAction($a): bool
	{
		return isset($_GET['a']) && $_GET['a'] == $a;
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
		if (isset($_GET['a'], $_GET['id']) && $_GET['a'] == $a && (int)$_GET['id'] > 0) {
			return (int)$_GET['id'];
		}

		return false;
	}

	public function preZero($i)
	{
		if ($i < 10) {
			return '0' . $i;
		}

		return $i;
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

	public function isMob(): bool
	{
		return isset($_SESSION['mob']) && $_SESSION['mob'] == 1;
	}

	public function id($name)
	{
		$id = $this->makeId($name, $this->ids);

		$this->ids[$id] = true;

		return $id;
	}

	public function getPostData()
	{
		if (isset($_POST)) {
			return $_POST;
		}

		return array();
	}

	public function getValue($id)
	{
		global $g_data;

		if (isset($g_data[$id])) {
			return $g_data[$id];
		}

		return '';
	}

	public function getGetId($name)
	{
		if (isset($_GET[$name]) && (int)$_GET[$name] > 0) {
			return (int)$_GET[$name];
		}

		return false;
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

	public function submitted(): bool
	{
		return isset($_POST) && !empty($_POST);
	}

	public function info($msg, $title = false)
	{
		$t = '';
		if ($title !== false) {
			$t = '<strong>' . $title . '</strong> ';
		}
		$_SESSION['msg']['info'][] = $msg;
	}

	public function error($msg, $title = false)
	{
		$t = '';
		if ($title !== false) {
			$t = '<strong>' . $title . '</strong> ';
		}
		$_SESSION['msg']['error'][] = $t . $msg;
	}

	public function getBezirk()
	{
		return $this->regionGateway->getBezirk($this->session->getCurrentBezirkId());
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
}
