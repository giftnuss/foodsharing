<?php

namespace Foodsharing\Modules\Application;

use Foodsharing\Modules\Core\Control;

class ApplicationXhr extends Control
{
	public function __construct()
	{
		$this->model = new ApplicationModel();
		$this->view = new ApplicationView();

		parent::__construct();
	}

	public function apply()
	{
		if ($this->func->isBotFor($_GET['bid']) || $this->func->isOrgaTeam()) {
			if ($this->model->apply($_GET['bid'], $_GET['fid'])) {
				$this->func->info('Bewerbung angenommen');

				return array(
					'status' => 1,
					'script' => 'goTo("/?page=bezirk&bid=' . (int)$_GET['bid'] . '");'
				);
			}
		}
	}

	public function maybe()
	{
		if ($this->func->isBotFor($_GET['bid']) || $this->func->isOrgaTeam()) {
			if ($this->model->maybe($_GET['bid'], $_GET['fid'])) {
				$this->func->info('Bewerbungs Status geändert');

				return array(
					'status' => 1,
					'script' => 'goTo("/?page=bezirk&bid=' . (int)$_GET['bid'] . '");'
				);
			}
		}
	}

	public function noapply()
	{
		if ($this->func->isBotFor($_GET['bid']) || $this->func->isOrgaTeam()) {
			$this->model->noapply($_GET['bid'], $_GET['fid']);

			$this->func->info('Bewerbung abgelehnt');

			return array(
				'status' => 1,
				'script' => 'goTo("/?page=bezirk&bid=' . (int)$_GET['bid'] . '");'
			);
		}
	}
}
