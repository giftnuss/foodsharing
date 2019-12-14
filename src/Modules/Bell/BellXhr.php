<?php

namespace Foodsharing\Modules\Bell;

use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Modules\Core\Control;

class BellXhr extends Control
{
	private $gateway;

	public function __construct(BellGateway $gateway)
	{
		$this->gateway = $gateway;

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

		// $xhr->addData('aaa', $bells);
		$xhr->addData('list', $bells);

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
