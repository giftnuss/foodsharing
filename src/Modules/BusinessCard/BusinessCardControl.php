<?php

namespace Foodsharing\Modules\BusinessCard;

use Foodsharing\Modules\Core\Control;

class BusinessCardControl extends Control
{
	public function __construct()
	{
		$this->model = new BusinessCardModel();
		$this->view = new BusinessCardView();

		parent::__construct();
	}

	public function index()
	{
		$this->func->addBread($this->func->s('bcard_generator'));

		$this->func->addContent($this->view->top(), CNT_TOP);

		if ($data = $this->model->getMyData()) {
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

			$foodsaver = $this->model->getValues(array('name', 'nachname'), 'foodsaver', $this->func->fsId());

			$file = 'data/visite/' . (int)$short[1] . '_' . $short[0] . '.pdf';

			if (file_exists($file)) {
				$Dateiname = basename($file);
				$size = filesize($file);
				header('Content-Type: application/pdf');
				header('Content-Disposition: attachment; filename=bcard-' . $this->func->id($short[0]) . '-' . $this->func->id($foodsaver['name']) . '-' . $this->func->id($foodsaver['nachname']) . '.pdf');
				header("Content-Length: $size");
				readfile($file);

				exit();
			} else {
				$this->func->goPage();
			}
		}
	}
}
