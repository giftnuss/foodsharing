<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\PushNotification\PushNotificationGateway;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;

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
	 * @Rest\Post("pushnotification/subscription")
	 * @Rest\RequestParam("body")
	 */
	public function subscribeAction(ParamFetcher $paramFetcher)
	{
		if (!$this->session->may()) {
			return $this->handleHttpStatus(403);
		}

		$pushSubscription = $paramFetcher->get('body');
		$foodsaverId = $this->session->id();

		$this->gateway->addSubscription($foodsaverId, $pushSubscription);

		return $this->handleHttpStatus(200);
	}

	/**
	 * @Rest\Put("pushnotification/subscription")
	 * @Rest\RequestParam("body")
	 */
	public function updatePushSubscriptionAction(ParamFetcher $paramFetcher)
	{
		if (!$this->session->may()) {
			return $this->handleHttpStatus(403);
		}

		$subscription = $paramFetcher->get('body');
		$foodsaverId = $this->session->id();

		$numberOfAffectedRows = $this->gateway->updateSubscription($foodsaverId, $subscription);

		if ($numberOfAffectedRows == 0) {
			return $this->handleHttpStatus(404);
		}

		return $this->handleHttpStatus(200);
	}

	/**
	 * @Rest\Delete("pushnotification/subscription")
	 * @Rest\RequestParam("body")
	 */
	public function deletePushSubscriptionAction(ParamFetcher $paramFetcher)
	{
		if (!$this->session->may()) {
			return $this->handleHttpStatus(403);
		}

		$subscriptionArray = json_decode($paramFetcher->get('body'), true);
		$foodsaverId = $this->session->id();

		$numberOfAffectedRows = $this->gateway->deleteSubscription($foodsaverId, $subscriptionArray['endpoint']);

		if ($numberOfAffectedRows == 0) {
			return $this->handleHttpStatus(404);
		}

		return $this->handleHttpStatus(200);
	}

	private function handleHttpStatus(int $statusCode)
	{
		return $this->handleView($this->view([], $statusCode));
	}
}
