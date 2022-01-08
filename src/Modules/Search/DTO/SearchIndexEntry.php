<?php

namespace Foodsharing\Modules\Search\DTO;

/**
 * Represents an entry in the search index which is stored in the frontend for faster search.
 */
class SearchIndexEntry
{
	public int $id;

	public string $name;

	public ?string $teaser;

	public ?string $image;

	public static function create(int $id, string $name, ?string $teaser, ?string $image): SearchIndexEntry
	{
		$s = new SearchIndexEntry();
		$s->id = $id;
		$s->name = $name;
		$s->teaser = $teaser;
		$s->image = $image;

		return $s;
	}
}
