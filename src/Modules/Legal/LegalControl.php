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
		$form = $this->formFactory->getFormFactory()->create(LegalForm::class, $data);
		$form->handleRequest($request);
		if ($form->isSubmitted()) {
			if ($form->isValid()) {
				$this->gateway->agreeToPp(S::id(), $data->privacy_policy_date);
				/* need to reload session cache. TODO: This should be further abstracted */
				$this->model->relogin();
			}
		}
		$response->setContent($this->render('pages/Legal/page.twig', [
			'pp' => $this->gateway->getPp(),
			'form' => $form->createView()]));
	}
}
