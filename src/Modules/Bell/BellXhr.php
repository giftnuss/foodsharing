<?php

namespace Foodsharing\Modules\Bell;

use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;

class BellXhr extends Control
{
	private $gateway;

	public function __construct(Model $model, BellGateway $gateway)
	{
		$this->gateway = $gateway;
		$this->model = $model;

		parent::__construct();
	}

	/**
	 * ajax call to refresh infobar messages.
	 */
	public function infobar()
	{
		$this->session->noWrite();

		$xhr = new Xhr();
		$bells = $this->gateway->listBells($this->session->id(), 20);

		if (!empty($rbells)) {
			if ($bells) {
				$bells = array_merge($rbells, $bells);
			} else {
				$bells = $rbells;
			}
		}

		// additionall add bell for BIEB
		if (isset($_SESSION['client']['verantwortlich'])) {
			$ids = array();
			foreach ($_SESSION['client']['verantwortlich'] as $v) {
				$ids[] = (int)$v['betrieb_id'];
			}
			if (!empty($ids)) {
				if ($betrieb_bells = $this->gateway->getStoreBells($ids)) {
					$bbells = array();

					foreach ($betrieb_bells as $b) {
						$bbells[] = array(
							'id' => 'b-' . $b['id'],
							'name' => 'betrieb_fetch_title',
							'body' => 'betrieb_fetch',
							'vars' => array(
								'betrieb' => $b['name'],
								'count' => $b['count']
							),
							'attr' => array(
								'href' => '/?page=fsbetrieb&id=' . $b['id']
							),
							'icon' => 'img img-store brown',
							'time' => $b['date'],
							'time_ts' => $b['date_ts'],
							'seen' => 0,
							'closeable' => 0
						);
					}
					if ($bells) {
						$bells = array_merge($bbells, $bells);
					} else {
						$bells = $bbells;
					}
				}
			}
		}

		/*
		 * additional bells for new fairteiler
		 */
		if ($this->session->may('bot')) {
			if ($fbells = $this->gateway->getFairteilerBells($this->session->getBotBezirkIds())) {
				$bbells = array();

				foreach ($fbells as $b) {
					$bbells[] = array(
						'id' => 'f-' . $b['id'],
						'name' => 'sharepoint_activate_title',
						'body' => 'sharepoint_activate',
						'vars' => array(
							'bezirk' => $b['bezirk_name'],
							'name' => $b['name']
						),
						'attr' => array(
							'href' => '/?page=fairteiler&sub=check&id=' . $b['id']
						),
						'icon' => 'img img-recycle yellow',
						'time' => $b['add_date'],
						'time_ts' => $b['time_ts'],
						'seen' => 0,
						'closeable' => 0
					);
				}
				if ($bells) {
					$bells = array_merge($bbells, $bells);
				} else {
					$bells = $bbells;
				}
			}
		}

		// $xhr->addData('aaa', $bells);
		$xhr->addData('list', array_map(function ($bell) {
			if (isset($bell['attr']['onclick'])) {
				preg_match('/profile\((.*?)\)/', $bell['attr']['onclick'], $matches);
				if ($matches) {
					$bell['attr']['href'] = '/profile/' . $matches[1];
				}
			}
			return [
				'id' => $bell['id'],
				'key' => $bell['body'],
				'href' => $bell['attr']['href'],
				'payload' => $bell['vars'],
				'icon' => $bell['icon'][0] != '/' ? $bell['icon'] : null,
				'image' => $bell['icon'][0] == '/' ? $bell['icon'] : null,
				'createdAt' => $bell['time'],
				'isRead' => (bool)$bell['closeable'],
				'isCloseable' => (bool)$bell['closeable']
			];
		}, $bells));

		$xhr->send();
	}

	/**
	 * ajax call to delete a bell.
	 */
	public function delbell()
	{
		$this->gateway->delBellForFoodsaver($_GET['id'], $this->session->id());
	}
}
