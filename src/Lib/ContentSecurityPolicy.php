<?php

namespace Foodsharing\Lib;

class ContentSecurityPolicy
{
	public function generate(string $reportUri, bool $reportOnly): string
	{
		$none = "'none'";
		$self = "'self'";
		$unsafeInline = "'unsafe-inline'";
		$unsafeEval = "'unsafe-eval'";

		$policy = [
			'default-src' => [
				$none
			],
			'script-src' => [
				$self,
				$unsafeInline,
				$unsafeEval // lots of `$.globalEval` still ... 😢
			],
			'connect-src' => [
				$self,
				$this->websocketUrlFor(BASE_URL),
				'https://sentry.io',
				'https://photon.komoot.de'
			],
			'img-src' => [
				$self,
				'data:',
				'https:'
			],
			'style-src' => [
				$self,
				$unsafeInline,
			],
			'font-src' => [
				$self,
				'data:'
			],
			'frame-src' => [
				$self
			],
			'frame-ancestors' => [
				$none
			],
			'report-uri' => [
				$reportUri
			]
		];

		$value = '';
		foreach ($policy as $key => $values) {
			$value .= $key . ' ' . implode(' ', $values) . '; ';
		}

		if ($reportOnly) {
			return 'Content-Security-Policy-Report-Only: ' . $value;
		}

		return 'Content-Security-Policy: ' . $value;
	}

	public function websocketUrlFor(string $baseUrl): string
	{
		return preg_replace('/^http(s)?:/', 'ws\1:', $baseUrl);
	}
}
