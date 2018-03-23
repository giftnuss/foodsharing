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
			new \Twig_Function('contentMainWidth', [$this, 'contentMainWidthFunction']),
			new \Twig_Function('webpackSnippet', [$this, 'webpackSnippetFunction'])
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

	public function webpackSnippetFunction($name)
	{
		$entryPath = '/js/gen/webpack/js/snippets/' . $name . '.js';

		return '<script type="text/javascript" src="' . $entryPath . '"></script>';
		//return file_get_contents(__DIR__.'/../..);
	}
}
