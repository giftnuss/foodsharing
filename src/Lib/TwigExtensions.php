<?php

namespace Foodsharing\Lib;

use Foodsharing\Helpers\IdentificationHelper;
use Twig_Extension;
use Twig_Filter;

class TwigExtensions extends Twig_Extension
{
	private $func;
	private $identificationHelper;

	public function __construct(Func $func, IdentificationHelper $identificationHelper)
	{
		$this->func = $func;
		$this->identificationHelper = $identificationHelper;
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
		}

		return $this->func->sv($key, $data);
	}

	public function idFilter($name)
	{
		return $this->identificationHelper->id($name);
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
