<?php

namespace Foodsharing\Modules\RegionAdmin;

use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\RegionPermissions;

class RegionAdminControl extends Control
{
	private $regionGateway;
	private $identificationHelper;
	private $regionPermissions;

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
		$this->pageHelper->addBread($this->translationHelper->s('bezirk_bread'), '/?page=region');
		$this->pageHelper->addTitle($this->translationHelper->s('bezirk_bread'));
		$cnt = '
		<div>
			<div style="float:left;width:150px;" id="' . '..' . '"></div>
			<div style="float:right;width:250px;"></div>
			<div style="clear:both;"></div>
		</div>';

		$this->pageHelper->addStyle('#bezirk-buttons {left: 50%; margin-left: 5px;position: absolute;top: 77px;}');

		$bezirke = $this->regionGateway->getBasics_bezirk();

		array_unshift($bezirke, ['id' => RegionIDs::ROOT, 'name' => 'Ohne `Eltern` Bezirk']);

		$this->pageHelper->hiddenDialog('newbezirk', [
			$this->v_utils->v_form_text('Name'),
			$this->v_utils->v_form_text('email'),
			$this->v_utils->v_form_select('parent_id', ['values' => $bezirke])
		], 'Neuer Bezirk');

		$this->pageHelper->addContent($this->v_utils->v_field('<div><div id="' . $this->identificationHelper->id('bezirk_form') . '"></div></div>', 'Bezirk bearbeiten', ['class' => 'ui-padding']), CNT_LEFT);
		$this->pageHelper->addContent($this->v_utils->v_field($this->view->v_bezirk_tree($id) . '
				<div id="bezirk-buttons" class="bootstrap">
					<button id="deletebezirk" class="btn btn-secondary btn-sm" style="visibility:hidden;" onclick="deleteActiveGroup()">' . $this->translationHelper->s('group.delete') . '</button>
					' . $this->v_utils->v_dialog_button('newbezirk', 'Neuer Bezirk') . '
				</div>', 'Bezirke'), CNT_RIGHT);

		$this->view->i_map($id);
	}
}
