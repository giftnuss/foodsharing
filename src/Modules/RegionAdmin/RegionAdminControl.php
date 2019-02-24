<?php

namespace Foodsharing\Modules\RegionAdmin;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;

class RegionAdminControl extends Control
{
	private $regionGateway;

	public function __construct(Db $model, RegionAdminView $view, RegionGateway $regionGateway)
	{
		$this->model = $model;
		$this->view = $view;
		$this->regionGateway = $regionGateway;

		parent::__construct();

		if (!$this->session->may('orga')) {
			$this->routeHelper->go('/');
		}
	}

	public function index()
	{
		$id = $this->func->id('tree');
		$this->pageHelper->addBread($this->func->s('bezirk_bread'), '/?page=region');
		$this->pageHelper->addTitle($this->func->s('bezirk_bread'));
		$cnt = '
		<div>
			<div style="float:left;width:150px;" id="' . '..' . '"></div>
			<div style="float:right;width:250px;"></div>
			<div style="clear:both;"></div>		
		</div>';

		$this->pageHelper->addStyle('#bezirk-buttons {left: 50%; margin-left: 5px;position: absolute;top: 77px;}');

		$bezirke = $this->regionGateway->getBasics_bezirk();

		array_unshift($bezirke, array('id' => '0', 'name' => 'Ohne `Eltern` Bezirk'));

		$this->pageHelper->hiddenDialog('newbezirk', array(
			$this->v_utils->v_form_text('Name'),
			$this->v_utils->v_form_text('email'),
			$this->v_utils->v_form_select('parent_id', array('values' => $bezirke))
		), 'Neuer Bezirk');

		$this->pageHelper->addContent($this->v_utils->v_field('<div><div id="' . $this->func->id('bezirk_form') . '"></div></div>', 'Bezirk bearbeiten', array('class' => 'ui-padding')), CNT_LEFT);
		$this->pageHelper->addContent($this->v_utils->v_field($this->view->v_bezirk_tree($id) . '
				<div id="bezirk-buttons" class="bootstrap">
					<button id="deletebezirk" class="btn btn-secondary btn-sm" style="visibility:hidden;" onclick="deleteActiveGroup()">' . $this->func->s('group.delete') . '</button>
					' . $this->v_utils->v_dialog_button('newbezirk', 'Neuer Bezirk') . '	
				</div>', 'Bezirke'), CNT_RIGHT);

		$this->view->i_map($id);
	}
}
