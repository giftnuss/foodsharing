<?php

namespace Foodsharing\Lib;

use Twig_Extension;
use Twig_Filter;

class TwigExtensions extends Twig_Extension
{
	private $func;

	public function __construct(Func $func)
	{
		$this->func = $func;
	}

	public function getFilters()
	{
		return [
			new Twig_Filter('translate', [$this, 'translateFilter']),
			new Twig_Filter('id', [$this, 'idFilter'])
		];
	}

	public function getFunctions()
	{
		return [
			new \Twig_Function('contentMainWidth', [$this, 'contentMainWidthFunction'])
		];
	}

	public function translateFilter($key, $data = null)
	{
		if ($data === null) {
			return $this->func->s($key);
		} else {
			return $this->func->sv($key, $data);
		}
	}

	public function idFilter($name)
	{
		return $this->func->id($name);
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
