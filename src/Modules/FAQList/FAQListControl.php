<?php

namespace Foodsharing\Modules\FAQList;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\FAQAdmin\FAQGateway;

class FAQListControl extends Control
{
	private $faqGateway;

	public function __construct(Db $model, FAQGateway $faqGateway)
	{
		$this->model = $model;
		$this->faqGateway = $faqGateway;
		parent::__construct();
	}

	public function index()
	{
		if (isset($_GET['id'])) {
			if ($res = $this->faqGateway->getOne_faq($_GET['id'])) {
				$this->pageHelper->addBread('FAQ`s', '/?page=listFaq');
				$this->pageHelper->addBread(substr($res['name'], 0, 30));

				$cnt = '';

				if (!empty($res['answer'])) {
					$cnt .= $res['answer'];
				}

				$this->pageHelper->addContent($this->v_utils->v_field($cnt, $res['name'], array('class' => 'ui-padding')));
			} else {
				$this->routeHelper->goPage('listFaq');
			}
		} else {
			$this->pageHelper->addBread('FAQ`s', '/?page=listFaq');

			$docs = $this->faqGateway->getFaqIntern();
			$menu = array();
			foreach ($docs as $d) {
				$menu[] = array(
					'href' => '/?page=listFaq&id=' . $d['id'],
					'name' => $d['name']
				);
			}

			$this->pageHelper->addContent($this->v_utils->v_menu($menu, 'FAQ'));
		}
	}
}
