<?php

namespace Foodsharing\Services;

use Html2Text\Html2Text;

class SanitizerService
{
	private $parsedown;
	private $htmlPurifier;

	public function __construct(\Parsedown $parsedown, \HTMLPurifier $HTMLPurifier)
	{
		$this->parsedown = $parsedown;
		$this->htmlPurifier = $HTMLPurifier;
	}

	public function plainToHtml($text)
	{
		return nl2br(htmlspecialchars($text));
	}

	public function markdownToHtml($text)
	{
		$html = $this->parsedown->text($text);

		return $this->htmlPurifier->purify($html);
	}

	public function htmlToPlain($html)
	{
		$html = new Html2Text($html);

		return $html->getText();
	}

	public function tagSelectIds($v): array
	{
		$result = [];
		foreach ($v as $idKey => $value) {
			$result[] = explode('-', $idKey)[0];
		}

		return $result;
	}

	public function handleTagselect($id): void
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

	public function jsSafe($str, $quote = "'")
	{
		return str_replace(array($quote, "\n", "\r"), array('\\' . $quote . '', '\\n', ''), $str);
	}

	public function tt($str, $length = 160)
	{
		if (strlen($str) > $length) {
			/* this removes the part of the last word that might have been destroyed by substr */
			$str = preg_replace('/[^ ]*$/', '', substr($str, 0, $length)) . ' ...';
		}

		return $str;
	}
}
