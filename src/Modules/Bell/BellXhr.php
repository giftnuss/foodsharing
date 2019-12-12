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
		$xhr->addData('list', array_map(function (BellData $bell) {
			if (isset($bell->link_attributes['onclick'])) {
				preg_match('/profile\((.*?)\)/', $bell->link_attributes['onclick'], $matches);
				if ($matches) {
					$bell->link_attributes['href'] = '/profile/' . $matches[1];
				}
			}

			return [
				'id' => $bell->id,
				'key' => $bell->body,
				'href' => $bell->link_attributes['href'],
				'payload' => $bell->vars,
				'icon' => $bell->icon[0] != '/' ? $bell->icon : null,
				'image' => $bell->icon[0] == '/' ? $bell->icon : null,
				'createdAt' => $bell->time->format('Y-m-d\TH:i:s'),
				'isRead' => $bell->seen, // TODO: this property is not part of the BellData class, but it gets added when the BellData is created from the database array
				'isCloseable' => $bell->closeable
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
