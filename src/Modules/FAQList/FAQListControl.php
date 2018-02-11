<?php
/**
 * Created by IntelliJ IDEA.
 * User: matthias
 * Date: 11.02.18
 * Time: 17:37.
 */

namespace Foodsharing\Modules\FAQList;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;

class FAQListControl extends Control
{
	private $v_utils;

	public function __construct()
	{
		parent::__construct();

		global $g_view_utils;
		$this->v_utils = $g_view_utils;
		$this->model = new Model();
	}

	public function index()
	{
		if (isset($_GET['id'])) {
			if ($res = $this->model->getOne_faq($_GET['id'])) {
				addBread('FAQ`s', '/?page=listFaq');
				addBread(substr($res['name'], 0, 30));

				$cnt = '';

				if (!empty($res['answer'])) {
					$cnt .= $res['answer'];
				}

				addContent($this->v_utils->v_field($cnt, $res['name'], array('class' => 'ui-padding')));
			} else {
				goPage('listFaq');
			}
		} else {
			addBread('FAQ`s', '/?page=listFaq');

			$docs = $this->model->getFaqIntern();
			$menu = array();
			foreach ($docs as $d) {
				$menu[] = array(
					'href' => '/?page=listFaq&id=' . $d['id'],
					'name' => $d['name']
				);
			}

			addContent($this->v_utils->v_menu($menu, 'FAQ'));
		}
	}
}
