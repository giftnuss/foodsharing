<?php

namespace Foodsharing\Services;

class OutputSanitizerService
{
	private $parsedown;
	private $htmlPurifier;

	public function __construct(\Parsedown $parsedown, \HTMLPurifier $HTMLPurifier)
	{
		$this->parsedown = $parsedown;
		$this->htmlPurifier = $HTMLPurifier;
	}

	public function sanitizeForHtml($text)
	{
		$parsedMarkdown = $this->parsedown->text(strip_tags($text));
		$purified = $this->htmlPurifier->purify($parsedMarkdown);

		return $purified;
	}
}
