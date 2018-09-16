<?php

namespace Foodsharing\Modules\FairTeiler;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Control;

class FairTeilerXhr extends Control
{
	private $gateway;
	private $bellGateway;

	public function __construct(Db $model, FairTeilerView $view, FairTeilerGateway $gateway, BellGateway $bellGateway)
	{
		$this->view = $view;
		$this->gateway = $gateway;
		$this->bellGateway = $bellGateway;
		$this->model = $model;

		parent::__construct();
	}

	public function infofollower()
	{
		if (!$this->mayFairteiler($_GET['fid'])) {
			return false;
		}
		$post = '';

		if ($ft = $this->gateway->getFairteiler($_GET['fid'])) {
			if ($followers = $this->gateway->getEmailFollower($_GET['fid'])) {
				$post = $this->gateway->getLastFtPost($_GET['fid']);

				$body = nl2br($post['body']);

				if (!empty($post['attach'])) {
					$attach = json_decode($post['attach'], true);
					if (isset($attach['image']) && !empty($attach['image'])) {
						foreach ($attach['image'] as $img) {
							$body .= '
							<div>
								<img src="' . BASE_URL . '/images/wallpost/medium_' . $img['file'] . '" />
							</div>';
						}
					}
				}

				foreach ($followers as $f) {
					$this->func->tplMail(18, $f['email'], array(
						'link' => BASE_URL . '/?page=fairteiler&sub=ft&id=' . (int)$_GET['fid'],
						'name' => $f['name'],
						'anrede' => $this->func->genderWord($f['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
						'fairteiler' => $ft['name'],
						'post' => $body
					));
				}
			}

			if ($followers = $this->gateway->getInfoFollowerIds($_GET['fid'])) {
				$followersWithoutPostAuthor = array_diff($followers, [$this->session->id()]);
				$this->bellGateway->addBell(
					$followersWithoutPostAuthor,
					'ft_update_title',
					'ft_update',
					'img img-recycle yellow',
					array('href' => '/?page=fairteiler&sub=ft&id=' . (int)$_GET['fid']),
					array('name' => $ft['name'], 'user' => $this->session->user('name'), 'teaser' => $this->func->tt($post['body'], 100)),
					'fairteiler-' . (int)$_GET['fid']
				);
			}
		}

		return array(
			'status' => 1
		);
	}

	private function mayFairteiler($fid)
	{
		if ($ids = $this->gateway->getFairteilerIds($this->func->fsId())) {
			if (in_array($fid, $ids)) {
				return true;
			}
		}

		return false;
	}
}
