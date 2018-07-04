<?php

namespace Foodsharing\Modules\FAQList;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;
use Foodsharing\Modules\FAQAdmin\FAQGateway;

class FAQListControl extends Control
{
	private $faqGateway;

	public function __construct(Model $model, FAQGateway $faqGateway)
	{
		$this->model = $model;
		$this->faqGateway = $faqGateway;
		parent::__construct();
	}

	public function index()
	{
		if (isset($_GET['id'])) {
			if ($res = $this->faqGateway->getOne_faq($_GET['id'])) {
				$this->func->addBread('FAQ`s', '/?page=listFaq');
				$this->func->addBread(substr($res['name'], 0, 30));

				$cnt = '';

				if (!empty($res['answer'])) {
					$cnt .= $res['answer'];
				}

				$this->func->addContent($this->v_utils->v_field($cnt, $res['name'], array('class' => 'ui-padding')));
			} else {
				$this->func->goPage('listFaq');
			}
		} else {
			$this->func->addBread('FAQ`s', '/?page=listFaq');

			$docs = $this->faqGateway->getFaqIntern();
			$menu = array();
			foreach ($docs as $d) {
				$menu[] = array(
					'href' => '/?page=listFaq&id=' . $d['id'],
					'name' => $d['name']
				);
			}

			$this->func->addContent($this->v_utils->v_menu($menu, 'FAQ'));
		}
	}
}
