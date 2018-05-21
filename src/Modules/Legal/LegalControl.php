<?php

namespace Foodsharing\Modules\Legal;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;
use Foodsharing\Modules\Core\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LegalControl extends Control
{
	private $gateway;

	public function __construct(LegalGateway $gateway, View $view, Model $model)
	{
		$this->model = $model;
		$this->view = $view;
		$this->gateway = $gateway;

		parent::__construct();
	}

	public function index(Request $request, Response $response)
	{
		$response->setContent($this->render('pages/Legal/newPp.twig', ['pp' => $this->gateway->getPp()]));
	}
}
