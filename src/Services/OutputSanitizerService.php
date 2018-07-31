<?php

namespace Foodsharing\Services;

use Html2Text\Html2Text;

class OutputSanitizerService
{
	private $parsedown;
	private $htmlPurifier;

	public function __construct(\Parsedown $parsedown, \HTMLPurifier $HTMLPurifier)
	{
		$this->parsedown = $parsedown;
		$this->htmlPurifier = $HTMLPurifier;
	}

	public function sanitizeForHtmlNoMarkup($text)
	{
		return nl2br(htmlspecialchars($text));
	}

	public function sanitizeForHtml($html, $containsMarkdown = true)
	{
		if ($containsMarkdown) {
			$html = $this->parsedown->text(strip_tags($html));
		}

		$purified = $this->htmlPurifier->purify($html);

		return $purified;
	}

	public function sanitizeForText($html)
	{
		$html = new Html2Text($html);

		return $html->getText();
	}
}
