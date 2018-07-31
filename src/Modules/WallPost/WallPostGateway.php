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

	private function makeTargetLinkTableName($target)
	{
		if (!$this->isValidTarget($target, $this->targets)) {
			throw new \Exception('Invalid wall target');
		}

		return 'fs_' . $target . '_has_wallpost';
	}

	private function makeTargetLinkTableForeignIdColumnName($target)
	{
		if (!$this->isValidTarget($target, $this->targets)) {
			throw new \Exception('Invalid wall target');
		}

		return $target . '_id';
	}

	public function isValidTarget($target, $targetId = null)
	{
		return in_array($target, $this->targets);
	}

	public function unlinkPost($postId, $target)
	{
		return $this->db->delete($this->makeTargetLinkTableName($target), ['wallpost_id' => $postId]);
	}

	public function deletePost($postId)
	{
		return $this->db->delete('fs_wallpost', ['id' => $postId]);
	}

	public function getPost($postId)
	{
		$post = $this->db->fetch('
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

		return $post;
	}

	public function getPosts($target, $targetId)
	{
		$posts = $this->db->fetchAll('
		SELECT 	p.id,
					p.`body`, 
					p.`time`, 
					UNIX_TIMESTAMP(p.`time`) AS time_ts,
					p.`attach`,
					fs.id AS foodsaver_id,
					fs.`name`,
					fs.`photo`
				
			FROM 	`fs_wallpost` p,
					`' . $this->makeTargetLinkTableName($target) . '` hp,
					`fs_foodsaver` fs
				
			WHERE 	p.foodsaver_id = fs.id
			AND 	hp.wallpost_id = p.id
			AND 	hp.`' . $this->makeTargetLinkTableForeignIdColumnName($target) . '` = :targetId
				
			ORDER BY p.time DESC
				
			LIMIT 30
		', ['targetId' => $targetId]);
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
					$wp[$key]['gallery'] = $gallery;
				}
			}
		}

		return $posts;
	}

	public function getLastPostId($target, $targetId)
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

	public function linkPost($postId, $target, $targetId)
	{
		$this->db->insert($this->makeTargetLinkTableName($target), [$this->makeTargetLinkTableForeignIdColumnName($target) => $targetId, 'wallpost_id' => $postId]);
	}

	/**
	 * @param $message
	 * @param $fsId
	 * @param null $target
	 * @param string $attach
	 *
	 * @return int id of inserted wallpost
	 */
	public function addPost($message, $fsId, $target = null, $targetId = null, $attach = '')
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

	public function getFsByPost($postId)
	{
		return $this->db->fetchValueByCriteria('fs_wallpost', 'foodsaver_id', ['id' => $postId]);
	}

	public function isLinkedToTarget($postId, $target, $targetId)
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
