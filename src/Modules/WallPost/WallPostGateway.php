<?php

namespace Foodsharing\Modules\WallPost;

use Foodsharing\Modules\Core\BaseGateway;

class WallPostGateway extends BaseGateway
{
	private $targets = [
		'application',
		'basket',
		'bezirk',
		'event',
		'fairteiler',
		'foodsaver',
		'fsreport',
		'question',
		'usernotes'
	];

	private function makeTargetLinkTableName(string $target): string
	{
		if (!$this->isValidTarget($target)) {
			throw new \Exception('Invalid wall target');
		}

		return 'fs_' . $target . '_has_wallpost';
	}

	private function makeTargetLinkTableForeignIdColumnName(string $target): string
	{
		if (!$this->isValidTarget($target)) {
			throw new \Exception('Invalid wall target');
		}

		return $target . '_id';
	}

	public function isValidTarget(string $target)
	{
		return in_array($target, $this->targets, true);
	}

	public function unlinkPost(int $postId, string $target): int
	{
		return $this->db->delete($this->makeTargetLinkTableName($target), ['wallpost_id' => $postId]);
	}

	public function deletePost(int $postId): int
	{
		return $this->db->delete('fs_wallpost', ['id' => $postId]);
	}

	public function getPost(int $postId): array
	{
		return $this->db->fetch('
		SELECT 	p.id,
					p.`body`,
					p.`time`,
					fs.id AS foodsaver_id,
					fs.`name`,
					fs.`photo`

			FROM 	`fs_wallpost` p
			LEFT JOIN `fs_foodsaver` fs
			ON fs.id = p.foodsaver_id

			WHERE 	p.id = :postId

			LIMIT 1
		', ['postId' => $postId]);
	}

	public function getPosts(string $target, int $targetId, int $limit = 60): array
	{
		$posts = $this->db->fetchAll('
		SELECT 	p.id,
					p.body,
					p.time,
					UNIX_TIMESTAMP(p.time) AS time_ts,
					p.attach,
					fs.id AS foodsaver_id,
					fs.name,
					fs.photo
			FROM 	fs_wallpost p,
					' . $this->makeTargetLinkTableName($target) . ' hp,
					fs_foodsaver fs
			WHERE 	p.foodsaver_id = fs.id
			AND 	hp.wallpost_id = p.id
			AND 	hp.`' . $this->makeTargetLinkTableForeignIdColumnName($target) . '` = :targetId
			ORDER BY p.time DESC
			LIMIT :limit
		', ['targetId' => $targetId, 'limit' => $limit]);
		foreach ($posts as $key => $w) {
			if (!empty($w['attach'])) {
				$data = json_decode($w['attach'], true);
				if (isset($data['image'])) {
					$gallery = array();
					foreach ($data['image'] as $img) {
						$gallery[] = array(
							'image' => 'images/wallpost/' . $img['file'],
							'medium' => 'images/wallpost/medium_' . $img['file'],
							'thumb' => 'images/wallpost/thumb_' . $img['file']
						);
					}
					$posts[$key]['gallery'] = $gallery;
				}
			}
		}

		return $posts;
	}

	public function getLastPostId(string $target, int $targetId)
	{
		return $this->db->fetchValue('
			SELECT 	MAX(id)
			FROM 	`fs_wallpost` wp,
					`' . $this->makeTargetLinkTableName($target) . '` hp
			WHERE 	hp.wallpost_id = wp.id
			AND 	hp.`' . $this->makeTargetLinkTableForeignIdColumnName($target) . '` = :targetId',
			['targetId' => $targetId]
		);
	}

	public function linkPost(int $postId, string $target, int $targetId): void
	{
		$this->db->insert($this->makeTargetLinkTableName($target), [$this->makeTargetLinkTableForeignIdColumnName($target) => $targetId, 'wallpost_id' => $postId]);
	}

	/**
	 * @param string $message
	 * @param int $fsId
	 * @param string $target
	 * @param int $targetId
	 * @param string $attach
	 *
	 * @return int id of inserted wallpost
	 *
	 * @throws \Exception
	 */
	public function addPost(string $message, int $fsId, string $target = '', int $targetId = 0, string $attach = ''): int
	{
		$postId = $this->db->insert('fs_wallpost', [
			'foodsaver_id' => $fsId,
			'body' => $message,
			'time' => $this->db->now(),
			'attach' => $attach
		]);
		if ($target && $targetId) {
			$this->linkPost($postId, $target, $targetId);
		}

		return $postId;
	}

	public function getFsByPost(int $postId)
	{
		return $this->db->fetchValueByCriteria('fs_wallpost', 'foodsaver_id', ['id' => $postId]);
	}

	public function isLinkedToTarget(int $postId, string $target, int $targetId): bool
	{
		return $this->db->exists(
			$this->makeTargetLinkTableName($target),
			[
				'wallpost_id' => $postId,
				$this->makeTargetLinkTableForeignIdColumnName($target) => $targetId
			]
		);
	}
}
