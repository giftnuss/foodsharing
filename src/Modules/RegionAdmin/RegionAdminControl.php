<?php

namespace Foodsharing\Modules\RegionAdmin;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;

class RegionAdminControl extends Control
{
	public function __construct(Model $model, RegionAdminView $view)
	{
		$this->model = $model;
		$this->view = $view;
		parent::__construct();

		if (!S::may('orga')) {
			$this->func->go('/');
		}
	}

	public function index()
	{
		if ($this->func->isOrgaTeam() && isset($_GET['delete']) && (int)$_GET['delete'] > 0) {
			$this->model->deleteBezirk($_GET['delete']);
			$this->func->goPage('region');
		}

		$id = $this->func->id('tree');
		$this->func->addBread($this->func->s('bezirk_bread'), '/?page=region');
		$this->func->addTitle($this->func->s('bezirk_bread'));
		$cnt = '
		<div>
			<div style="float:left;width:150px;" id="' . '..' . '"></div>
			<div style="float:right;width:250px;"></div>
			<div style="clear:both;"></div>		
		</div>';

		$this->func->addStyle('#bezirk-buttons {left: 50%; margin-left: 5px;position: absolute;top: 77px;}');

		$this->func->addJs('
		$("#deletebezirk").button().click(function(){
			if(confirm($("#tree-hidden-name").val()+\' wirklich löschen?\'))
			{
				goTo(\'/?page=region&delete=\'+$("#tree-hidden").val());
			}
		});');

		$bezirke = $this->model->getBasics_bezirk();

		array_unshift($bezirke, array('id' => '0', 'name' => 'Ohne `Eltern` Bezirk'));

		$this->func->hiddenDialog('newbezirk', array(
			$this->v_utils->v_form_text('Name'),
			$this->v_utils->v_form_text('email'),
			$this->v_utils->v_form_select('parent_id', array('values' => $bezirke))
		), 'Neuer Bezirk');

		$this->func->addContent($this->v_utils->v_field('<div><div id="' . $this->func->id('bezirk_form') . '"></div></div>', 'Bezirk bearbeiten', array('class' => 'ui-padding')), CNT_LEFT);
		$this->func->addContent($this->v_utils->v_field($this->view->v_bezirk_tree($id) . '
				<div id="bezirk-buttons">
					<span id="deletebezirk" style="visibility:hidden;">Bezirk löschen</span>	
					' . $this->v_utils->v_dialog_button('newbezirk', 'Neuer Bezirk') . '	
				</div>', 'Bezirke'), CNT_RIGHT);

		$this->view->i_map($id);
	}
}