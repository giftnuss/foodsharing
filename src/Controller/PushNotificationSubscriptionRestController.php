<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\PushNotification\PushNotificationGateway;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

class PushNotificationSubscriptionRestController extends FOSRestController
{
    private $gateway;
    private $session;

    public function __construct(PushNotificationGateway $gateway, Session $session)
    {
        $this->gateway = $gateway;
        $this->session = $session;
    }

    /**
     * @Rest\Get("pushnotification/publickey")
     */
    public function getPublicKeyAction()
    {
        $view = $this->view($this->gateway->getPublicKey(), 200);
        return $this->handleView($view);
    }

    /**
     * @Rest\Post("pushnotification/subscription/{pushSubscription}", requirements={"pushSubscription"=".+"})
     */
    public function handlePushSubscriptionAction(string $pushSubscription)
    {
        $foodsaverId = $this->session->id();

        $this->gateway->addSubscription($foodsaverId, $pushSubscription);

        return $this->handleView($this->view([], 200));
    }
}