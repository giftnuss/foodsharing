<?php

namespace Foodsharing\Lib;

use Twig_Extension;
use Twig_Filter;
use Twig_SimpleFunction;

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
			new Twig_Filter('translate', array($this, 'translateFilter'))
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

	public function getFunctions()
	{
		return [
			new Twig_SimpleFunction('getImageUrl', array($this, 'getImageUrl')),
		];
	}

	public function getImageUrl($file = false, $size = 'mini', $format = 'q', $altimg = false)
	{
		if ($file === false) {
			$file = $_SESSION['client']['photo'];
		}

		if (!empty($file) && file_exists('images/' . $file)) {
			if (!file_exists('images/' . $size . '_' . $format . '_' . $file)) {
				$this->resizeImg('images/' . $file, $size, $format);
			}

			return '/images/' . $size . '_' . $format . '_' . $file;
		} else {
			if ($altimg === false) {
				return '/img/' . $size . '_' . $format . '_avatar.png';
			} else {
				return $altimg;
			}
		}
	}
}
