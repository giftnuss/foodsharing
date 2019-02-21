<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\PushNotification\PushNotificationGateway;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

class PushNotificationSubscriptionRestController extends FOSRestController
{
	/**
	 * @var PushNotificationGateway
	 */
	private $gateway;

	/**
	 * @var Session
	 */
	private $session;

	public function __construct(PushNotificationGateway $gateway, Session $session)
	{
		$this->gateway = $gateway;
		$this->session = $session;
	}

	/**
	 * @Rest\Get("pushnotification/{type}/publickey")
	 */
	public function getPublicKeyAction(string $type)
	{
		if (!$this->gateway->hasHandlerFor($type)) {
			return $this->handleHttpStatus(404);
		}

		$view = $this->view($this->gateway->getPublicKey($type), 200);

		return $this->handleView($view);
	}

	/**
	 * @Rest\Post("pushnotification/{type}/subscription")
	 */
	public function subscribeAction(Request $request, string $type)
	{
		if (!$this->gateway->hasHandlerFor($type)) {
			return $this->handleHttpStatus(404);
		}

		if (!$this->session->may()) {
			return $this->handleHttpStatus(403);
		}

		$pushSubscription = $request->getContent();
		$foodsaverId = $this->session->id();

		$this->gateway->addSubscription($foodsaverId, $pushSubscription);

		return $this->handleHttpStatus(200);
	}

	/**
	 * @Rest\Delete("pushnotification/{type}/subscription")
	 */
	public function deletePushSubscriptionAction(Request $request, string $type)
	{
		if (!$this->gateway->hasHandlerFor($type)) {
			return $this->handleHttpStatus(404);
		}

		if (!$this->session->may()) {
			return $this->handleHttpStatus(403);
		}

		$subscription = $request->getContent();
		$foodsaverId = $this->session->id();

		$numberOfAffectedRows = $this->gateway->deleteSubscription($foodsaverId, $subscription);

		if ($numberOfAffectedRows === 0) {
			return $this->handleHttpStatus(404);
		}

		return $this->handleHttpStatus(200);
	}

	private function handleHttpStatus(int $statusCode)
	{
		return $this->handleView($this->view([], $statusCode));
	}
}
