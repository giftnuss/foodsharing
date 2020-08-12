<?php

namespace Foodsharing\Modules\Search;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Search\DTO\SearchResult;

class SearchGateway extends BaseGateway
{
	private $regionGateway;

	public function __construct(Database $db, RegionGateway $regionGateway)
	{
		parent::__construct($db);
		$this->regionGateway = $regionGateway;
	}

	/**
	 * Searches the given term in the database of regions.
	 *
	 * @param string $q Query string / search term
	 *
	 * @return array SearchResult[] Array of regions containing the search term
	 */
	public function searchRegions(string $q): array
	{
		return $this->searchTable(
			'fs_bezirk',
			['name'],
			$q,
			[
				'name' => '`name`',
				'teaser' => 'CONCAT("")'
			]
		);
	}

	/**
	 * Searches the given term in the database of stores.
	 *
	 * @return array SearchResult[] Array of stores containing the search term
	 */
	public function searchStores(string $q, array $regions = null): array
	{
		return $this->searchTable(
			'fs_betrieb',
			['name', 'stadt', 'plz', 'str'],
			$q,
			[
				'name' => '`name`',
				'teaser' => 'CONCAT(`str`,", ",`plz`," ",`stadt`)'
			],
			$regions
		);
	}

	private function searchTable(string $table, array $fields, string $query, array $show = [], array $regions_to_search = null): array
	{
		$q = trim($query);

		str_replace([',', ';', '+', '.'], ' ', $q);

		do {
			$q = str_replace('  ', ' ', $q);
		} while (strpos($q, '  ') !== false);

		$terms = explode(' ', $q);

		foreach ($terms as $i => $t) {
			$terms[$i] = $this->db->quote('%' . $t . '%');
		}

		$fsql = 'CONCAT(' . implode(',', $fields) . ')';

		$fs_sql = '';
		if (!empty($regions_to_search)) {
			$fs_sql = ' AND bezirk_id IN(' . implode(',', $regions_to_search) . ')';
		}

		$results = $this->db->fetchAll('
			SELECT 	`id`,
					 ' . $show['name'] . ' AS name,
					 ' . $show['teaser'] . ' AS teaser


			FROM 	' . $table . '

			WHERE ' . $fsql . ' LIKE ' . implode(' AND ' . $fsql . ' LIKE ', $terms) . '
			' . $fs_sql . '

			ORDER BY `name`

			LIMIT 0,50
		');

		return array_map(function ($x) {
			return SearchResult::create($x['id'], $x['name'], $x['teaser']);
		}, $results);
	}

	/**
	 * @param string $q Search string as provided by an end user. Individual words all have to be found in the result, each being the prefixes of words of the results
	 *(e.g. hell worl is expanded to a MySQL match condition of +hell* +worl*). The input string is properly sanitized, e.g. no further control over the search operation is possible.
	 * @param bool $showDetails show detailed address info if true. Show only city if false
	 * @param array|null $groupIds the groupids a person must be in to be found. Set to null to query over all users.
	 *
	 * @return array SearchResult[] Array of foodsavers containing the search term
	 */
	public function searchUserInGroups(string $q, bool $showDetails, ?array $groupIds = []): array
	{
		$searchString = $this->prepareSearchString($q);
		$select = 'SELECT fs.id, fs.name, fs.nachname, fs.anschrift, fs.stadt, fs.plz FROM fs_foodsaver fs';
		$fulltextCondition = 'MATCH (fs.name, fs.nachname) AGAINST (? IN BOOLEAN MODE) AND deleted_at IS NULL';
		$groupBy = ' GROUP BY fs.id';
		if (empty($groupIds)) {
			$results = $this->db->fetchAll($select . ' WHERE ' . $fulltextCondition . $groupBy, [$searchString]);
		} else {
			$results = $this->db->fetchAll(
				$select . ', fs_foodsaver_has_bezirk hb WHERE ' .
				$fulltextCondition .
				' AND fs.id = hb.foodsaver_id AND hb.bezirk_id IN (' . $this->db->generatePlaceholders(count($groupIds)) . ')' . $groupBy,
				array_merge([$searchString], $groupIds));
		}

		return array_map(function ($x) use ($showDetails) {
			$teaser = $showDetails ? $x['anschrift'] . ', ' . $x['plz'] . ' ' . $x['stadt'] : $x['stadt'];
			$teaser ??= '';

			return SearchResult::create($x['id'], $x['name'] . ' ' . $x['nachname'], $teaser);
		}, $results);
	}

	/**
	 * Searches in the titles of forum themes of a group for a given string.
	 *
	 * @param string $q Search string as provided by an end user. Individual words all have to be found in the result, each being the prefixes of words of the results
	 *(e.g. hell worl is expanded to a MySQL match condition of +hell* +worl*). The input string is properly sanitized, e.g. no further control over the search operation is possible.
	 * @param int $groupId ID of a group (region or work group) in which will be searched
	 * @param int $subforumId ID of the forum in the group
	 *
	 * @return array SearchResult[] Array of forum themes containing the search term
	 */
	public function searchForumTitle(string $q, int $groupId, int $subforumId): array
	{
		$searchString = $this->prepareSearchString($q);
		$results = $this->db->fetchAll(
			'SELECT t.id, t.name
				   FROM fs_theme t, fs_bezirk_has_theme ht
				   WHERE MATCH (t.name) AGAINST (? IN BOOLEAN MODE)
				   AND t.id = ht.theme_id AND ht.bezirk_id = ?
				   AND t.active = 1 AND ht.bot_theme = ?
				   GROUP BY t.id',
			[$searchString, $groupId, $subforumId]
		);

		return array_map(function ($x) {
			return SearchResult::create($x['id'], $x['name'], '');
		}, $results);
	}

	/**
	 * Sanitises a search query for an SQL request.
	 */
	private function prepareSearchString(string $q): string
	{
		/* remove all non-word characters as they will not be indexed by the database and might change the search condition */
		$q = mb_ereg_replace('\W', ' ', $q);
		/* put + before and * after the words, omitting all words with less than 3 characters, because they would not be found in the result. */
		/* TODO: this number depends on innodb_ft_min_token_size MySQL setting. It could be viable setting it to 1 alternatively. */
		return implode(' ',
			array_map(
				function ($a) {
					return '+' . $a . '*';
				},
				array_filter(
					explode(' ', $q),
					function ($v) {
						return mb_strlen($v) > 2;
					}
				)
			)
		);
	}
}
