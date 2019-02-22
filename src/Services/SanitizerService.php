<?php

namespace Foodsharing\Services;

use Html2Text\Html2Text;

class SanitizerService
{
	private $parseDown;
	private $htmlPurifier;

	public function __construct(\Parsedown $parseDown, \HTMLPurifier $HTMLPurifier)
	{
		$this->parseDown = $parseDown;
		$this->htmlPurifier = $HTMLPurifier;
	}

	public function plainToHtml(string $text): string
	{
		return nl2br(htmlspecialchars($text));
	}

	public function markdownToHtml(string $text): string
	{
		$html = $this->parseDown->text($text);

		return $this->htmlPurifier->purify($html);
	}

	public function htmlToPlain(string $html): string
	{
		$html = new Html2Text($html);

		return $html->getText();
	}

	public function tagSelectIds(array $v): array
	{
		$result = [];
		foreach ($v as $idKey => $value) {
			$result[] = explode('-', $idKey)[0];
		}

		return $result;
	}

	public function handleTagSelect(string $identifier): void
	{
		global $g_data;
		$recip = array();
		if (isset($g_data[$identifier]) && is_array($g_data[$identifier])) {
			foreach ($g_data[$identifier] as $key => $r) {
				if ($key != '') {
					$part = explode('-', $key);
					$recip[$part[0]] = $part[0];
				}
			}
		}

		$g_data[$identifier] = $recip;
	}

	public function jsSafe($str, string $quote = "'")
	{
		return str_replace([$quote, "\n", "\r"], ['\\' . $quote . '', '\\n', ''], $str);
	}

	public function tt(string $str, int $length = 160): string
	{
		if (strlen($str) > $length) {
			/* this removes the part of the last word that might have been destroyed by substr */
			$str = preg_replace('/[^ ]*$/', '', substr($str, 0, $length)) . ' ...';
		}

		return $str;
	}
}
