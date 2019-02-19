<?php

namespace Foodsharing\Modules\GeoClean;

use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;

class GeoCleanXhr extends Control
{
	private $regionGateway;

	public function __construct(GeoCleanModel $model, RegionGateway $regionGateway)
	{
		$this->model = $model;
		$this->regionGateway = $regionGateway;

		parent::__construct();
	}

	public function masterupdate()
	{
		if (!$this->session->may('orga')) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if ($bezirke = $this->regionGateway->listIdsForDescendantsAndSelf($_GET['id'])) {
			$this->regionGateway->updateMasterRegions($bezirke, $_GET['id']);
		}
	}

	public function updateGeo()
	{
		if (!$this->session->may('orga')) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$lat = floatval($_GET['lat']);
		$lon = floatval($_GET['lon']);
		$fsid = (int)$_GET['id'];

		if ($this->model->updateGeo($fsid, $lat, $lon)) {
			return array(
				'status' => 1,
				'script' => '$("#fs-' . $fsid . '").parent().parent().remove();pulseInfo("Koordinaten gespeichert!");'
			);
		}
	}
}
