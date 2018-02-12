<?php

namespace Foodsharing\Modules\Search;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;

class SearchControl extends Control
{
	public function __construct()
	{
		$this->model = new SearchModel();
		$this->view = new SearchView();

		parent::__construct();

		if (!S::may('fs')) {
			$this->func->go('/?page=dashboard');
		}
	}

	public function index()
	{
		$this->func->addBread($this->func->s('search'));
		$value = '';
		$out = '';

		if (isset($_GET['q']) && strlen($_GET['q']) > 0) {
			$value = strip_tags($_GET['q']);
			if ($res = $this->model->search($value)) {
				foreach ($res as $key => $r) {
					$cnt = '';
					foreach ($r as $erg) {
						$cnt .= $this->v_utils->v_input_wrapper($erg['name'], $erg['teaser'], 'search', array('click' => $erg['click']));
					}
					$out .= $this->v_utils->v_field($cnt, count($r) . ' ' . $this->func->s($key) . ' gefunden', array('class' => 'ui-padding'));
				}
			} else {
				$out .= $this->v_utils->v_field($this->v_utils->v_info('Die Suche gab leider keine Treffer'), 'Ergebnis', array('class' => 'ui-padding'));
			}
		}

		$this->func->addContent($this->view->searchBox($value));
		$this->func->addContent($out);
	}
}
