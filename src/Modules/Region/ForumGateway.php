<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;

class ForumGateway extends BaseGateway
{
	private $bellGateway;

	public function __construct(Database $db, BellGateway $bellGateway)
	{
		parent::__construct($db);
		$this->bellGateway = $bellGateway;
	}

	// Thread-related

	public function listThreads($bezirk_id, $bot_theme = 0, $page = 0, $last = 0)
	{
		$threads_per_page = 15;

		if ($ret = $this->db->fetchAll('
			SELECT 		t.id,
						t.name,
						t.`time`,
						UNIX_TIMESTAMP(t.`time`) AS time_ts,
						fs.id AS foodsaver_id,
						IFNULL(fs.name,"abgemeldeter Benutzer") AS foodsaver_name,
						fs.photo AS foodsaver_photo,
						fs.sleep_status,
						p.body AS post_body,
						p.`time` AS post_time,
						UNIX_TIMESTAMP(p.`time`) AS post_time_ts,
						t.last_post_id,
						t.sticky

			FROM 		fs_theme t
						INNER JOIN
						fs_bezirk_has_theme bt
						ON bt.theme_id = t.id
						LEFT JOIN
						fs_theme_post p
						ON p.id = t.last_post_id
						INNER JOIN
						fs_foodsaver fs
						ON  fs.id = p.foodsaver_id

			WHERE       bt.bezirk_id = :bezirk_id
			AND 		bt.bot_theme = :bot_theme
			AND 		t.`active` = 1

			ORDER BY t.sticky DESC, t.last_post_id DESC

			LIMIT :offset, :size

		', [
			'bezirk_id' => $bezirk_id,
			'bot_theme' => $bot_theme,
			'offset' => $page * $threads_per_page,
			'size' => $threads_per_page
		])
		) {
			if ($last > 0) {
				$ll = end($ret);
				if ($ll['id'] == $last) {
					return false;
				}
			}

			return $ret;
		}

		return [];
	}

	public function getThreadInfo($threadId)
	{
		return $this->db->fetch('
		SELECT		t.name,
					bt.bezirk_id as region_id,
					bt.bot_theme as ambassador_forum
		FROM		fs_theme t
		LEFT JOIN   fs_bezirk_has_theme bt ON bt.theme_id = t.id
		WHERE		t.id = :thread_id
		', ['thread_id' => $threadId]);
	}

	public function getThread($bezirk_id, $thread_id, $bot_theme = false)
	{
		$bot_theme_v = $bot_theme ? 1 : 0;

		return $this->db->fetch('
			SELECT 		t.id,
						t.name,
						t.`time`,
						UNIX_TIMESTAMP(t.`time`) AS time_ts,
						fs.id AS author_id,
						IF(fs.deleted_at IS NOT NULL,"abgemeldeter Benutzer", fs.name) AS author_name,
						fs.photo AS author_photo,
						IF(fs.deleted_at IS NOT NULL, "Beitrag von nicht mehr angemeldetem Benutzer", p.body) AS post_body,
						p.`time` AS post_time,
						UNIX_TIMESTAMP(p.`time`) AS post_time_ts,
						t.last_post_id,
						t.`active`,
						t.`sticky`

			FROM 		fs_theme t
						INNER JOIN
						fs_theme_post p
						ON t.last_post_id = p.id
						INNER JOIN
						fs_bezirk_has_theme bt
						ON bt.theme_id = t.id
						INNER JOIN
						fs_foodsaver fs
						ON p.foodsaver_id = fs.id

			WHERE 		bt.bezirk_id = :bezirk_id
			AND 		t.id = :thread_id
			AND 		bt.bot_theme = :bot_theme

			LIMIT 1

		', ['bezirk_id' => $bezirk_id, 'thread_id' => $thread_id, 'bot_theme' => $bot_theme_v]);
	}

	public function addThread($fs_id, $bezirk_id, $name, $body, $bot_theme = false, $active)
	{
		$bot_theme_v = $bot_theme ? 1 : 0;
		$thread_id = $this->db->insert('fs_theme', [
			'foodsaver_id' => $fs_id,
			'name' => strip_tags($name),
			'time' => date('Y-m-d H:i:s'),
			'active' => $active,
		]);

		$this->followThread($fs_id, $thread_id);

		$this->db->insert('fs_bezirk_has_theme', [
			'bezirk_id' => $bezirk_id,
			'theme_id' => $thread_id,
			'bot_theme' => $bot_theme_v
		]);

		$this->addPost($fs_id, $thread_id, $body);

		return $thread_id;
	}

	public function activateThread($thread_id)
	{
		$this->db->update('fs_theme', ['active' => 1], ['id' => $thread_id]);
	}

	public function deleteThread($thread_id)
	{
		$this->db->delete('fs_theme_post', ['theme_id' => $thread_id]);
		$this->db->delete('fs_theme', ['id' => $thread_id]);
	}

	public function getThreadFollower($fs_id, $thread_id)
	{
		return $this->db->fetchAll('
			SELECT 	fs.name,
					fs.geschlecht,
					fs.email

			FROM 	fs_foodsaver fs,
					fs_theme_follower tf
			WHERE 	tf.foodsaver_id = fs.id
			AND 	tf.theme_id = :theme_id
			AND 	tf.foodsaver_id != :fs_id
		', ['theme_id' => $thread_id, 'fs_id' => $fs_id]);
	}

	public function isFollowing($fsId, $threadId)
	{
		return $this->db->exists(
			'fs_theme_follower',
			['theme_id' => $threadId, 'foodsaver_id' => $fsId]
		);
	}

	public function getBotThreadStatus($thread_id)
	{
		return $this->db->fetch('
			SELECT  ht.bot_theme,
					ht.bezirk_id
			FROM
					fs_bezirk_has_theme ht
			WHERE   ht.theme_id = :theme_id
		', ['theme_id' => $thread_id]);
	}

	public function followThread($fs_id, $thread_id)
	{
		return $this->db->insertIgnore(
			'fs_theme_follower',
			['foodsaver_id' => $fs_id, 'theme_id' => $thread_id, 'infotype' => 1]
		);
	}

	public function unfollowThread($fs_id, $thread_id)
	{
		return $this->db->delete(
			'fs_theme_follower',
			['theme_id' => $thread_id, 'foodsaver_id' => $fs_id]
		);
	}

	public function stickThread($thread_id)
	{
		return $this->db->update(
			'fs_theme',
			['sticky' => 1],
			['id' => $thread_id]
		);
	}

	public function unstickThread($thread_id)
	{
		$this->db->update(
			'fs_theme',
			['sticky' => 0],
			['id' => $thread_id]
		);
	}

	// Post-related

	public function addPost($fs_id, $thread_id, $body)
	{
		$post_id = $this->db->insert(
			'fs_theme_post',
			[
				'theme_id' => $thread_id,
				'foodsaver_id' => $fs_id,
				'body' => strip_tags($body, '<p><a><ul><strong><b><i><ol><li><br>'),
				'time' => date('Y-m-d H:i:s')
			]
		);

		$this->db->update('fs_theme', ['last_post_id' => $post_id], ['id' => $thread_id]);

		return $post_id;
	}

	public function listPosts($thread_id)
	{
		return $this->db->fetchAll('
			SELECT 		fs.id AS author_id,
						IF(fs.deleted_at IS NOT NULL,"abgemeldeter Benutzer", fs.name) AS author_name,
						fs.photo AS author_photo,
						fs.sleep_status AS author_sleep_status,
						IF(fs.deleted_at IS NOT NULL, "Beitrag von nicht mehr angemeldetem Benutzer", p.body) AS body,
						p.`time`,
						p.id,
						UNIX_TIMESTAMP(p.`time`) AS time_ts

			FROM 		fs_theme_post p
			INNER JOIN   fs_foodsaver fs
				ON 		p.foodsaver_id = fs.id
			WHERE 		p.theme_id = :thread_id

			ORDER BY 	p.`time`
		', ['thread_id' => $thread_id]);
	}

	public function deletePost($id)
	{
		$thread_id = $this->db->fetchValue('SELECT `theme_id` FROM `fs_theme_post` WHERE `id` = :id', ['id' => $id]);
		$this->db->delete('fs_theme_post', ['id' => $id]);

		if ($last_post_id = $this->db->fetchValue(
			'SELECT MAX(`id`) FROM `fs_theme_post` WHERE `theme_id` = :theme_id',
			['theme_id' => $thread_id]
		)) {
			$this->db->update('fs_theme', ['last_post_id' => $last_post_id], ['id' => $thread_id]);
		} else {
			$this->db->delete('fs_theme', ['id' => $thread_id]);
		}

		return true;
	}

	public function getRegionForPost($post_id)
	{
		return $this->db->fetchValue('
			SELECT 	bt.bezirk_id

			FROM 	fs_bezirk_has_theme bt,
					fs_theme_post tp,
					fs_theme t
			WHERE 	t.id = tp.theme_id
			AND 	t.id = bt.theme_id
			AND 	tp.id = :id
		', ['id' => $post_id]);
	}
}
