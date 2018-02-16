<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;

class ForumGateway extends BaseGateway
{
	private $themes_per_page = 15;

	private $bellGateway;

	public function __construct(Database $db, BellGateway $bellGateway)
	{
		parent::__construct($db);
		$this->bellGateway = $bellGateway;
	}

	// Theme-related

	public function listThemes($bezirk_id, $bot_theme = 0, $page = 0, $last = 0)
	{
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
			'offset' => $page * $this->themes_per_page,
			'size' => $this->themes_per_page
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

		return false;
	}

	public function getThread($bezirk_id, $thread_id, $bot_theme = 0)
	{
		return $this->db->fetch('
			SELECT 		t.id,
						t.name,
						t.`time`,
						UNIX_TIMESTAMP(t.`time`) AS time_ts,
						fs.id AS foodsaver_id,
						IF(fs.deleted_at IS NOT NULL,"abgemeldeter Benutzer", fs.name) AS foodsaver_name,
						fs.photo AS foodsaver_photo,
						IF(fs.deleted_at IS NOT NULL, "Beitrag von nicht mehr angemeldetem Benutzer", p.body) AS post_body,
						p.`time` AS post_time,
						UNIX_TIMESTAMP(p.`time`) AS post_time_ts,
						t.last_post_id,
						t.`active`

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

		', ['bezirk_id' => $bezirk_id, 'thread_id' => $thread_id, 'bot_theme' => $bot_theme]);
	}

	public function addTheme($fs_id, $bezirk_id, $name, $body, $bot_theme = 0, $active)
	{
		$theme_id = $this->db->insert('fs_theme', [
			'foodsaver_id' => $fs_id,
			'name' => strip_tags($name),
			'time' => date('Y-m-d H:i:s'),
			'active' => $active,
		]);

		$this->followTheme($fs_id, $theme_id);

		$this->db->insert('fs_bezirk_has_theme', [
			'bezirk_id' => $bezirk_id,
			'theme_id' => $theme_id,
			'bot_theme' => $bot_theme
		]);

		$this->addThemePost($fs_id, $theme_id, $body);

		return $theme_id;
	}

	public function activateTheme($theme_id)
	{
		$this->db->update('fs_theme', ['active' => 1], ['id' => $theme_id]);
	}

	public function deleteTheme($theme_id)
	{
		$this->db->delete('fs_theme_post', ['theme_id' => $theme_id]);
		$this->db->delete('fs_theme', ['id' => $theme_id]);
	}

	public function getThreadFollower($fs_id, $theme_id)
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
		', ['theme_id' => $theme_id, 'fs_id' => $fs_id]);
	}

	/*Does Foodsaver Follow Topic -> followCounter*/
	public function getFollowingCounter($fs_id, $theme_id)
	{
		return $this->db->fetchValue('
			SELECT  count(DISTINCT tf.theme_id)
			FROM
					fs_theme_follower tf
			WHERE   tf.theme_id = :theme_id
			AND 	tf.foodsaver_id = :fs_id
		', ['theme_id' => $theme_id, 'fs_id' => $fs_id]);
	}

	public function getBotThemestatus($theme_id)
	{
		return $this->db->fetch('
			SELECT  ht.bot_theme,
					ht.bezirk_id
			FROM
					fs_bezirk_has_theme ht
			WHERE   ht.theme_id = :theme_id
		', ['theme_id' => $theme_id]);
	}

	public function followTheme($fs_id, $theme_id)
	{
		return $this->db->insert(
			'fs_theme_follower',
			['foodsaver_id' => $fs_id, 'theme_id' => $theme_id, 'infotype' => 1]
		);
	}

	public function unfollowTheme($fs_id, $theme_id)
	{
		return $this->db->delete(
			'fs_theme_follower',
			['theme_id' => $theme_id, 'foodsaver_id' => $fs_id]
		);
	}

	public function stickTheme($theme_id)
	{
		return $this->db->update(
			'fs_theme',
			['sticky' => 1],
			['id' => $theme_id]
		);
	}

	public function unstickTheme($theme_id)
	{
		$this->db->update(
			'fs_theme',
			['sticky' => 0],
			['id' => $theme_id]
		);
	}

	public function getStickStatus($theme_id)
	{
		return $this->db->fetchValue('
			SELECT `sticky`
			FROM fs_theme
			WHERE id = :id
		', ['id' => $theme_id]);
	}

	// Post-related

	public function addThemePost($fs_id, $theme_id, $body, $reply = 0, $bezirk = false)
	{
		$post_id = $this->db->insert(
			'fs_theme_post',
			[
				'theme_id' => $theme_id,
				'foodsaver_id' => $fs_id,
				'reply_post' => $reply,
				'body' => strip_tags($body, '<p><a><ul><strong><b><i><ol><li><br>'),
				'time' => date('Y-m-d H:i:s')
			]
		);

		$this->db->update('fs_theme', ['last_post_id' => $post_id], ['id' => $theme_id]);

		if ($reply > 0) {
			$post_fs_id = $this->db->fetchValue('SELECT `foodsaver_id` FROM `fs_theme_post` WHERE `id` = :id', ['id' => $reply]);
			if ($post_fs_id != $fs_id) {
				$this->bellGateway->addBell(
					$post_fs_id,
					'forum_answer_title',
					'forum_answer',
					'fa fa-comments',
					array('href' => '/?page=bezirk&bid=' . $bezirk['id'] . '&sub=forum&tid=' . $theme_id . '&pid=' . $post_id . '#post' . $post_id),
					array('user' => S::user('name'), 'forum' => $bezirk['name']),
					'forum-post-' . $post_id
				);
			}
		}

		return $post_id;
	}

	public function listPosts($thread_id)
	{
		return $this->db->fetchAll('
			SELECT 		fs.id AS fs_id,
						IF(fs.deleted_at IS NOT NULL,"abgemeldeter Benutzer", fs.name) AS fs_name,
						fs.photo AS fs_photo,
						fs.sleep_status AS fs_sleep_status,
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
		$theme_id = $this->db->fetchValue('SELECT `theme_id` FROM `fs_theme_post` WHERE `id` = :id', ['id' => $id]);
		$this->db->delete('fs_theme_post', ['id' => $id]);

		if ($last_post_id = $this->db->fetchValue('SELECT MAX(`id`) FROM `fs_theme_post` WHERE `theme_id` = :theme_id', ['theme_id' => $theme_id])) {
			$this->db->update('fs_theme', ['last_post_id' => $last_post_id], ['id' => $theme_id]);
		} else {
			$this->db->delete('fs_theme', ['id' => $theme_id]);
		}

		return true;
	}

	public function getBezirkForPost($post_id)
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
