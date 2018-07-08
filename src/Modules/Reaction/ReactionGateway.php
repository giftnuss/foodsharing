<?php

namespace Foodsharing\Modules\Reaction;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;

class ReactionGateway extends BaseGateway
{
	public function __construct(Database $db)
	{
		parent::__construct($db);
	}

	/**
	 * returns all reactions for a given target.
	 * if isPrefix is true, target is evaluated as a prefix search, e.g. to get reactions on all sub objects of an object
	 * when target is composed like module-obj-subobj.
	 */
	public function getReactions($target, $isPrefix = false)
	{
		$q = '
		SELECT
			r.target,
			r.emoji,
			r.time,
			r.foodsaver_id,
			fs.name as foodsaver_name

		FROM
			fs_reaction r
		LEFT JOIN
			fs_foodsaver fs
		ON
			fs.id = r.foodsaver_id';
		if ($isPrefix) {
			$q .= '
			WHERE r.target LIKE :target';
			$target .= '%';
		} else {
			$q .= '
			WHERE r.target = :target';
		}
		$res = $this->db->fetchAll($q, [
			'target' => $target
		]);

		return $res;
	}

	public function addReaction($target, $fsId, $emoji): bool
	{
		$this->db->insert(
			'fs_reaction',
			[
				'target' => $target,
				'foodsaver_id' => $fsId,
				'emoji' => $emoji,
				'time' => $this->db->now()
			]
		);

		return true;
	}

	public function removeReaction($target, $fsId, $emoji)
	{
		$this->db->delete(
			'fs_reaction',
			[
				'target' => $target,
				'foodsaver_id' => $fsId,
				'emoji' => $emoji
			]
		);
	}
}
