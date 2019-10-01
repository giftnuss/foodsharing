<?php

namespace Foodsharing\Lib;

use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Helpers\TranslationHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigExtensions extends AbstractExtension
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
			new TwigFilter('translate', [$this, 'translateFilter']),
			new TwigFilter('id', [$this, 'idFilter'])
		];
	}

	public function getFunctions()
	{
		return [
			new \Twig\TwigFunction('contentMainWidth', [$this, 'contentMainWidthFunction'])
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
