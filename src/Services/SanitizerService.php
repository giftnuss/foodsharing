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
		$html = $this->parsedown->text(strip_tags($text));

		$purified = $this->htmlPurifier->purify($html);

		return $purified;
	}

	public function htmlToPlain($html)
	{
		$html = new Html2Text($html);

		return $html->getText();
	}
}
