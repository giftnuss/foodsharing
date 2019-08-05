<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Core\BaseGateway;

class ForumFollowerGateway extends BaseGateway
{
	/**
	 * Removes the forum subscriptions for all deleted members or ambassadors in the region or group.
	 *
	 * @param int $regionId id of the group
	 * @param array $remainingMemberIds list of remaining members, or null to remove all
	 * @param bool $useAmbassadors if the ambassador table should be used
	 */
	public function deleteForumSubscriptions(int $regionId, array $remainingMemberIds, bool $useAmbassadors): void
	{
		$foodsaverTableName = $useAmbassadors ? 'fs_botschafter' : 'fs_foodsaver_has_bezirk';
		$themeIds = $this->db->fetchAllValuesByCriteria('fs_bezirk_has_theme', 'theme_id', ['bezirk_id' => $regionId]);

		if ($themeIds && !empty($themeIds)) {
			$query = '
				DELETE	tf.*
				FROM		`fs_theme_follower` tf
				JOIN		`fs_bezirk_has_theme` ht
				ON			ht.`theme_id` = tf.`theme_id`
				LEFT JOIN	`' . $foodsaverTableName . '` b
				ON			b.`bezirk_id` = ht.`bezirk_id`
				AND			b.`foodsaver_id` = tf.`foodsaver_id`
				WHERE		tf.`theme_id` IN (' . implode(',', array_map('intval', $themeIds)) . ')
			';
			if ($remainingMemberIds && !empty($remainingMemberIds)) {
				$query .= 'AND	tf.`foodsaver_id` NOT IN(' . implode(',', array_map('intval', $remainingMemberIds)) . ')';
			}

			$this->db->execute($query);
		}
	}
}
