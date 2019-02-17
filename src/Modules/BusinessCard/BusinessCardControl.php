<?php

namespace Foodsharing\Modules\BusinessCard;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Core\Control;

class BusinessCardControl extends Control
{
	private $gateway;

	public function __construct(Db $model, BusinessCardView $view, BusinessCardGateway $gateway)
	{
		$this->model = $model;
		$this->view = $view;
		$this->gateway = $gateway;

		parent::__construct();
	}

	public function index()
	{
		$this->func->addBread($this->func->s('bcard_generator'));

		$this->func->addContent($this->view->top(), CNT_TOP);

		if ($data = $this->gateway->getMyData($this->session->id(), $this->session->may('bieb'))) {
			if (strlen($data['anschrift'] . ', ' . $data['plz'] . ' ' . $data['stadt']) >= 49) {
				$this->func->error('Deine Anschrift ist zu lang! Anschrift, Postleitzahl und Stadt dürfen zusammen maximal 49 Zeichen haben.');
				$this->func->go('/?page=settings');
			}
			if (strlen($data['telefon'] . $data['handy']) <= 3) {
				$this->func->error('Du musst eine gültige Telefonnummer angegeben haben, um Deine Visitenkarte zu generieren');
				$this->func->go('/?page=settings');
			}
			if ($data['verified'] == 0) {
				// you have to be a verified user to generate your business card.
				$this->func->error('Du musst verifiziert sein, um Deine Visitenkarte generieren zu können.');
				$this->func->go('/?page=settings');
			}
			$sel_data = array();
			if ($data['bot']) {
				foreach ($data['bot'] as $b) {
					$sel_data[] = array(
						'id' => 'bot:' . $b['id'],
						'name' => $this->func->sv('bot_for', $b['name'])
					);
				}
			}

			if ($data['sm']) {
				foreach ($data['sm'] as $fs) {
					$sel_data[] = array(
						'id' => 'sm:' . $fs['id'],
						'name' => $this->func->sv('sm_for', $fs['name'])
					);
				}
			}
			if ($data['fs']) {
				foreach ($data['fs'] as $fs) {
					$sel_data[] = array(
						'id' => 'fs:' . $fs['id'],
						'name' => $this->func->sv('fs_for', $fs['name'])
					);
				}
			}

			$this->func->addContent($this->view->optionform($sel_data));
		}
	}

	public function dl()
	{
		if ($short = $this->getRequest('b')) {
			$short = explode(':', $short);

			$short[0] = str_replace(array('/', '\\'), '', $short[0]);

			$foodsaver = $this->model->getValues(array('name', 'nachname'), 'foodsaver', $this->session->id());

			$file = 'data/visite/' . (int)$short[1] . '_' . $short[0] . '.pdf';

			if (file_exists($file)) {
				$Dateiname = basename($file);
				$size = filesize($file);
				header('Content-Type: application/pdf');
				header('Content-Disposition: attachment; filename=bcard-' . $this->func->id($short[0]) . '-' . $this->func->id($foodsaver['name']) . '-' . $this->func->id($foodsaver['nachname']) . '.pdf');
				header("Content-Length: $size");
				readfile($file);

				exit();
			}

			$this->func->goPage();
		}
	}
}
