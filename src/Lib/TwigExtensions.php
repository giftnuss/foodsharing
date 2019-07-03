<?php

namespace Foodsharing\Lib;

use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Helpers\TranslationHelper;
use Twig_Extension;
use Twig_Filter;

class TwigExtensions extends Twig_Extension
{
	private $identificationHelper;
	private $translationHelper;

	public function __construct(IdentificationHelper $identificationHelper, TranslationHelper $translationHelper)
	{
		$this->identificationHelper = $identificationHelper;
		$this->translationHelper = $translationHelper;
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
			return $this->translationHelper->s($key);
		}

		return $this->translationHelper->sv($key, $data);
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
