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
			new Twig_Filter('translate', array($this, 'translateFilter')),
			new Twig_Filter('id', array($this, 'idFilter'))
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
}
