<?php

namespace Foodsharing\Modules\RegionAdmin;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\RegionPermissions;
use Foodsharing\Utility\IdentificationHelper;

class RegionAdminControl extends Control
{
	private RegionGateway $regionGateway;
	private IdentificationHelper $identificationHelper;
	private RegionPermissions $regionPermissions;

	public function __construct(
		RegionAdminView $view,
		RegionGateway $regionGateway,
		IdentificationHelper $identificationHelper,
		RegionPermissions $regionPermissions
	) {
		$this->view = $view;
		$this->regionGateway = $regionGateway;
		$this->identificationHelper = $identificationHelper;
		$this->regionPermissions = $regionPermissions;

		parent::__construct();

		if (!$this->regionPermissions->mayAdministrateRegions()) {
			$this->routeHelper->go('/');
		}
	}

	public function index()
	{
		$id = $this->identificationHelper->id('tree');
		$this->pageHelper->addBread($this->translator->trans('region.bread'), '/?page=region');
		$this->pageHelper->addTitle($this->translator->trans('region.bread'));

		$this->pageHelper->addStyle('#bezirk-buttons {left: 50%; margin-left: 5px; position: absolute; top: 77px;}');

		$regions = $this->regionGateway->getBasics_bezirk();

		array_unshift($regions, [
			'id' => RegionIDs::ROOT,
			'name' => $this->translator->trans('region.noParent'),
		]);

		$this->pageHelper->hiddenDialog('newbezirk', [
			$this->v_utils->v_form_text('Name'),
			$this->v_utils->v_form_text('email'),
			$this->v_utils->v_form_select('parent_id', ['values' => $regions])
		], $this->translator->trans('region.new'));

		$this->pageHelper->addContent($this->v_utils->v_field(
			'<div><div id="' . $this->identificationHelper->id('bezirk_form') . '"></div></div>',
			$this->translator->trans('region.edit'),
			['class' => 'ui-padding']
		), CNT_LEFT);
		$this->pageHelper->addContent($this->v_utils->v_field(
			$this->view->v_bezirk_tree($id) . '
				<div id="bezirk-buttons" class="bootstrap">
					' . $this->deleteregion_button() . '
					' . $this->newregion_button() . '
				</div>',
			$this->translator->trans('terminology.regions')
		), CNT_RIGHT);

		$this->view->i_map($id);
	}

	private function deleteregion_button(): string
	{
		return '<button
			id="deletebezirk"
			class="btn btn-secondary btn-sm"
			style="visibility: hidden;"
			onclick="deleteActiveGroup()">'
				. $this->translator->trans('region.delete') .
			'</button>';
	}

	private function newregion_button(): string
	{
		$id = 'newbezirk';
		$label = $this->translator->trans('region.new');

		$this->pageHelper->addJs('$("#' . $id . '-button").button({}).on("click", function () {
			$("#dialog_' . $id . '").dialog("open");
		});');

		return '<span id="' . $id . '-button">' . $label . '</span>';
	}
}
