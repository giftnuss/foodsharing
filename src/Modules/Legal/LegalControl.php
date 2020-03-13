<?php

namespace Foodsharing\Modules\Legal;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\View;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LegalControl extends Control
{
	private $gateway;

	/**
	 * @var FormFactoryBuilder
	 */
	private $formFactory;

	public function __construct(LegalGateway $gateway, View $view)
	{
		$this->view = $view;
		$this->gateway = $gateway;

		parent::__construct();
	}

	/**
	 * @required
	 */
	public function setFormFactory(FormFactoryBuilder $formFactory)
	{
		$this->formFactory = $formFactory;
	}

	public function index(Request $request, Response $response)
	{
		$data = new LegalData();
		$privacyPolicyDate = $this->gateway->getPpVersion();
		$privacyNoticeDate = $this->gateway->getPnVersion();
		$privacyNoticeNeccessary = $this->session->user('rolle') >= 2;

		$data->privacy_policy = $this->session->user('privacy_policy_accepted_date') == $privacyPolicyDate;
		$data->privacy_notice = $this->session->user('privacy_notice_accepted_date') == $privacyNoticeDate;

		$form = $this->formFactory->getFormFactory()->create(LegalForm::class, $data);
		if (!$privacyNoticeNeccessary) {
			$form->remove('privacy_notice');
		}
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			if ($form->isValid()) {
				$this->gateway->agreeToPp($this->session->id(), $privacyPolicyDate);
				if ($privacyNoticeNeccessary) {
					if ($data->privacy_notice) {
						$this->gateway->agreeToPn($this->session->id(), $privacyNoticeDate);
						$this->emailHelper->tplMail('user/privacy_notice', $this->session->user('email'), ['vorname' => $this->session->user('name')]);
					} else {
						/* ToDo: This is to be properly abstracted... */
						$this->gateway->downgradeToFoodsaver($this->session->id());
					}
				}
				/* need to reload session cache. TODO: This should be further abstracted */
				try {
					$this->session->refreshFromDatabase();
					$this->routeHelper->goSelf();
				} catch (\Exception $e) {
					$this->routeHelper->goPage('logout');
				}
			}
		}
		$response->setContent($this->render('pages/Legal/page.twig', [
			'privacy_policy' => $this->gateway->getPp(),
			'show_privacy_notice' => $privacyNoticeNeccessary,
			'privacy_notice' => $this->gateway->getPn(),
			'form' => $form->createView()]));
	}
}
