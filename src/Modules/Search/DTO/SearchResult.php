<?php

namespace Foodsharing\Modules\Search\DTO;

class SearchResult
{
	public int $id;

	public string $name;

	public string $teaser;

	public static function create(int $id, string $name, string $teaser): SearchResult
	{
		$s = new SearchResult();
		$s->id = $id;
		$s->name = $name;
		$s->teaser = $teaser;

		return $s;
	}
}
