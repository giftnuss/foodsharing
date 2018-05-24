<?php

namespace Foodsharing\Modules\Legal;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;
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

	public function __construct(LegalGateway $gateway, View $view, Model $model)
	{
		$this->model = $model;
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
		$data->privacy_policy_date = $this->gateway->getPpVersion();
		$data->privacy_policy = S::user('privacy_policy_accepted_date') == $data->privacy_policy_date;
		$data->privacy_notice_date = $this->gateway->getPnVersion();
		$data->privacy_notice = S::user('privacy_notice_accepted_date') == $data->privacy_notice_date ? 1 : 0;
		$show_privacy_notice = S::user('rolle') >= 2;
		$form = $this->formFactory->getFormFactory()->create(LegalForm::class, $data);
		if (!$show_privacy_notice) {
			$form->remove('privacy_notice');
		}
		$form->handleRequest($request);
		if ($form->isSubmitted()) {
			if ($form->isValid()) {
				$this->gateway->agreeToPp(S::id(), $data->privacy_policy_date);
				if ($data->privacy_notice == 1) {
					$this->gateway->agreeToPn(S::id(), $data->privacy_notice_date);
				} elseif ($data->privacy_notice == 2) {
					/* ToDo: This is to be properly abstracted... */
					$this->gateway->downgradeToFoodsaver(S::id());
				}
				/* need to reload session cache. TODO: This should be further abstracted */
				$this->model->relogin();
				$this->func->goSelf();
			}
		}
		$response->setContent($this->render('pages/Legal/page.twig', [
			'privacy_policy' => $this->gateway->getPp(),
			'show_privacy_notice' => $show_privacy_notice,
			'privacy_notice' => $this->gateway->getPn(),
			'form' => $form->createView()]));
	}
}
