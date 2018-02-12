<?php

namespace Foodsharing\Lib;

use Twig_Environment;
use Twig_Loader_Filesystem;

class Twig
{
	/**
	 * @var Twig_Loader_Filesystem
	 */
	private $loader;

	/**
	 * @var Twig_Environment
	 */
	private $twig;

	public function __construct(TwigExtensions $twigExtensions)
	{
		$this->loader = new Twig_Loader_Filesystem(__DIR__ . '/../../views');

		$this->twig = new Twig_Environment($this->loader, [
			'debug' => FS_ENV === 'dev',
			'cache' => __DIR__ . '/../../.views-cache',
			'strict_variables' => true
		]);

		$this->twig->addExtension($twigExtensions);
	}

	public function render($view, $data)
	{
		return $this->twig->render($view, $data);
	}
}
