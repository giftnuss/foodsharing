<?php

namespace Foodsharing\Modules\Bell;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Modules\Core\Control;

class BellXhr extends Control
{
	private $gateway;

	public function __construct(Db $model, BellGateway $gateway)
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
				'createdAt' => str_replace(' ', 'T', $bell['time']),
				'isRead' => (bool)$bell['seen'],
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

	/**
	 * ajax call to set bell as seen.
	 */
	public function markBellsAsRead(): void
	{
		$ids = json_decode($_GET['ids']);
		$this->gateway->setBellsAsSeen($ids, $this->session->id());
	}
}
