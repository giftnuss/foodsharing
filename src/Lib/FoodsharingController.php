<?php

namespace Foodsharing\Lib;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Compatibility layer to make porting old "Control" based controllers to new "Symfony" style controllers easier.
 * Any controller based on this also opts into the setup code that used to live in IndexController
 * @see RenderControllerSetupSubscriber
 * @package Foodsharing\Lib
 */
abstract class FoodsharingController extends AbstractController
{
}
