<?php

namespace Foodsharing\Modules\NewArea;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;

class NewAreaXhr extends Control
{
	private $regionGateway;
	private $newAreaGateway;

	public function __construct(Db $model, NewAreaGateway $newAreaGateway, NewAreaView $view, RegionGateway $regionGateway)
	{
		$this->model = $model;
		$this->newAreaGateway = $newAreaGateway;
		$this->view = $view;
		$this->regionGateway = $regionGateway;

		parent::__construct();
	}

	public function orderFs()
	{
		if ($this->func->isOrgaTeam()) {
			if ((int)$_GET['bid'] == 0) {
				return array(
					'status' => 1,
					'script' => 'error("Du musst noch einen Bezirk auswählen in den die Foodsaver sortiert werden.");'
				);
			} else {
				$bezirk_id = (int)$_GET['bid'];
				$fsids = explode('-', $_GET['fs']);
				if (count($fsids) > 0) {
					$count = 0;
					$js = '';
					foreach ($fsids as $fid) {
						$fid = (int)$fid;
						if ($fid > 0) {
							++$count;
							$this->regionGateway->linkBezirk($fid, $bezirk_id);

							$foodsaver = $this->model->getValues(array('geschlecht', 'email', 'name', 'nachname'), 'foodsaver', $fid);
							$anrede = $this->func->genderWord($foodsaver['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r');
							$name = $foodsaver['name'];

							$message = str_replace(array('{ANREDE}', '{NAME}'), array($anrede, $name), $_GET['msg']);

							$this->func->libmail(false, $foodsaver['email'], $_GET['subject'], $message);
							$this->newAreaGateway->clearWantNew($fid);

							$js .= '$(".wantnewcheck[value=\'' . $fid . '\']").parent().parent().remove();';
						}
					}

					return array(
						'status' => 1,
						'script' => 'pulseInfo("' . $count . ' E-Mails wurden versandt.");' . $js
					);
				}
			}
		}
	}

	public function deleteMarked()
	{
		if ($this->func->isOrgaTeam()) {
			$parts = explode('-', $_GET['del']);
			if (count($parts) > 0) {
				foreach ($parts as $p) {
					$this->newAreaGateway->clearWantNew($p);
				}
			}

			return array(
				'status' => 1
			);
		}
	}
}
