<?php

namespace Foodsharing\Lib;

use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\InfluxMetrics;
use Foodsharing\Modules\Core\View;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Utility\EmailHelper;
use Foodsharing\Utility\FlashMessageHelper;
use Foodsharing\Utility\PageHelper;
use Foodsharing\Utility\RouteHelper;
use Foodsharing\Utility\TranslationHelper;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Compatibility layer to make porting old "Control" based controllers to new "Symfony" style controllers easier.
 * Any controller based on this also opts into the setup code that used to live in IndexController.
 *
 * @see RenderControllerSetupSubscriber
 */
abstract class FoodsharingController extends AbstractController
{
	/**
	 * @var ContainerInterface Kernel container needed to access any service,
	 * instead of just the ones specified in AbstractController::getSubscribedServices
	 */
	protected ContainerInterface $fullServiceContainer;

	protected View $view;
	// $sub and $sub_func were deliberately left out in this compatibility layer for the time being.
	// However, a replacement or better solution for their behavior will be necessary for porting some controllers.

	protected PageHelper $pageHelper;
	protected Mem $mem;
	protected Session $session;
	protected Utils $v_utils;
	private Environment $twig;
	private FoodsaverGateway $foodsaverGateway;
	private InfluxMetrics $metrics;
	protected EmailHelper $emailHelper;
	protected FlashMessageHelper $flashMessageHelper;
	protected RouteHelper $routeHelper;
	protected TranslationHelper $translationHelper;
	protected TranslatorInterface $translator;

	public function __construct(ContainerInterface $containerInterface)
	{
		$this->fullServiceContainer = $containerInterface;

		// deprecated, but still needed by some legacy code, so we can't get rid of it yet.
		global $container;
		$container = $containerInterface;

		$this->mem = $container->get(Mem::class);
		$this->session = $container->get(Session::class);
		$this->v_utils = $container->get(Utils::class);
		$this->foodsaverGateway = $container->get(FoodsaverGateway::class);
		$this->metrics = $container->get(InfluxMetrics::class);
		$this->pageHelper = $container->get(PageHelper::class);
		$this->emailHelper = $container->get(EmailHelper::class);
		$this->routeHelper = $container->get(RouteHelper::class);
		$this->flashMessageHelper = $container->get(FlashMessageHelper::class);
		$this->translationHelper = $container->get(TranslationHelper::class);
		$this->translator = $container->get('translator'); // TODO TranslatorInterface is an alias

		$reflection = new ReflectionClass($this);
		$className = $reflection->getShortName();

		// $sub, $sub_func would be set up here.
		// as mentioned above, they and their behavior are not implemented

		/*
		 * This will make sure all controllers are suffixed 'Controller'.
		 * It also makes it relatively easy for a developer to catch the (unlikely) mistake.
		 * Also, when porting an old "Control" to a new Symfony "Controller" class,
		 * it makes it easy to have both working at the same time for comparisons.
		 */
		$pos = strpos($className, 'Controller');
		if ($pos === false) {
			throw new \Exception('Please rename the controller "' . $className . '" to end with "Controller".');
		}

		$projectDir = $container->get('kernel')->getProjectDir();
		$webpackModules = $projectDir . '/assets/modules.json';
		$manifest = json_decode(file_get_contents($webpackModules), true);
		$moduleName = substr($className, 0, $pos);
		$entry = 'Modules/' . $moduleName;
		if (isset($manifest[$entry])) {
			foreach ($manifest[$entry] as $asset) {
				if (substr($asset, -3) === '.js') {
					$this->pageHelper->addWebpackScript($asset);
				} elseif (substr($asset, -4) === '.css') {
					$this->pageHelper->addWebpackStylesheet($asset);
				}
			}
		}

		$this->metrics->addPageStatData(['controller' => $className]);
	}

	/**
	 * Previously, most controllers relied on IndexController actually rendering the website.
	 * They mostly talk to pageHelper, which is then used like this to generate the view data for the desired twig template.
	 * There are two things to be mentioned here:
	 * - MapControl and MessageControl are the only controllers changing the template from 'default'.
	 *   Currently, they are using the global $g_template for this,
	 *   but when they are ported, they can use this method's $template argument.
	 * - Some controllers call Control::render, which is different from AbstractController::render.
	 *
	 * If a controller method only interacts with PageHelper (directly, or indirectly through a View class that gets it through DI),
	 * all you have to do to port it (rendering wise) is call this method at the end, and then return its result.
	 *
	 * @param string $template which template should be used when rendering the website
	 */
	protected function renderGlobal($template = 'default'): Response
	{
		return $this->render('layouts/' . $template . '.twig', $this->pageHelper->generateAndGetGlobalViewData());
	}
}
