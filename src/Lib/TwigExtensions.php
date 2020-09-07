<?php

namespace Foodsharing\Lib;

use Twig\Extension\AbstractExtension;

class TwigExtensions extends AbstractExtension
{
	public function getFunctions()
	{
		return [
			new \Twig\TwigFunction('contentMainWidth', [$this, 'contentMainWidthFunction'])
		];
	}

	public function contentMainWidthFunction($hasLeft, $hasRight, $leftWidth, $rightWidth, $baseWidth = 24)
	{
		if ($hasLeft) {
			$baseWidth -= $leftWidth;
		}
		if ($hasRight) {
			$baseWidth -= $rightWidth;
		}

		return $baseWidth;
	}
}
