<?php

namespace Foodsharing\Modules\Search;

use Foodsharing\Modules\Core\Control;

class SearchXhr extends Control
{
	private $helper;

	public function __construct(SearchHelper $helper)
	{
		$this->helper = $helper;

		parent::__construct();
	}

	public function search()
	{
		if ($this->session->may('fs')) {
			if ($res = $this->helper->search($_GET['s'])) {
				$out = [];
				foreach ($res as $key => $value) {
					if (count($value) > 0) {
						$out[] = [
							'title' => $this->translationHelper->s($key),
							'result' => $value
						];
					}
				}

				return [
					'data' => $out,
					'status' => 1
				];
			}
		}

		return [
			'status' => 0
		];
	}
}
