<?php

namespace Foodsharing\Modules\Blog;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Model;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;

class BlogModel extends Model
{
	private $bellGateway;
	private $foodsaverGateway;

	public function __construct(BellGateway $bellGateway, FoodsaverGateway $foodsaverGateway)
	{
		parent::__construct();
		$this->bellGateway = $bellGateway;
		$this->foodsaverGateway = $foodsaverGateway;
	}

	public function canEdit($article_id)
	{
		if ($val = $this->getValues(array('bezirk_id', 'foodsaver_id'), 'blog_entry', $article_id)) {
			if ($this->func->fsId() == $val['foodsaver_id'] || $this->func->isBotFor($val['bezirk_id'])) {
				return true;
			}
		}

		return false;
	}

	public function canAdd($fsId, $bezirkId)
	{
		if ($this->func->isOrgaTeam()) {
			return true;
		}

		if ($this->func->isBotFor($bezirkId)) {
			return true;
		}

		return false;
	}

	public function getPost($id)
	{
		return $this->qRow('
			SELECT
				b.`id`,
				b.`name`,
				b.`time`,
				UNIX_TIMESTAMP(b.`time`) AS time_ts,
				b.`body`,
				b.`time`,
				b.`picture`,
				CONCAT(fs.name," ",fs.nachname) AS fs_name
	
			FROM
				`fs_blog_entry` b,
				`fs_foodsaver` fs
	
			WHERE
				b.foodsaver_id = fs.id
	
			AND
				b.`active` = 1
	
			AND
				b.id = ' . (int)$id);
	}

	public function listNews($page)
	{
		$page = ((int)$page - 1) * 10;

		return $this->q('
			SELECT 	 	
				b.`id`,
				b.`name`,
				b.`time`,
				UNIX_TIMESTAMP(b.`time`) AS time_ts,
				b.`active`,
				b.`teaser`,
				b.`time`,
				b.`picture`,
				CONCAT(fs.name," ",fs.nachname) AS fs_name
		
			FROM 
				`fs_blog_entry` b,
				`fs_foodsaver` fs
		
			WHERE 
				b.foodsaver_id = fs.id
				
			AND
				b.`active` = 1
		
			ORDER BY 
				b.`id` DESC
				
			LIMIT ' . $page . ',10');
	}

	public function listArticle()
	{
		$not = '';
		if (!$this->func->isOrgaTeam()) {
			$not = 'WHERE 		`bezirk_id` IN (' . implode(',', $this->session->getBezirkIds()) . ')';
		}

		return $this->q('
			SELECT 	 	`id`,
						`name`,
						`time`,
						UNIX_TIMESTAMP(`time`) AS time_ts,
						`active`,
						`bezirk_id`
		
			FROM 		`fs_blog_entry`
	
			' . $not . '
	
			ORDER BY `id` DESC');
	}

	public function del_blog_entry($id)
	{
		return $this->del('
			DELETE FROM 	`fs_blog_entry`
			WHERE 			`id` = ' . (int)$id . '
		');
	}

	public function getOne_blog_entry($id)
	{
		$out = $this->qRow('
			SELECT
			`id`,
			`bezirk_id`,
			`foodsaver_id`,
			`active`,
			`name`,
			`teaser`,
			`body`,
			`time`,
			UNIX_TIMESTAMP(`time`) AS time_ts,
			`picture`
			
			FROM 		`fs_blog_entry`
			
			WHERE 		`id` = ' . (int)$id);

		return $out;
	}

	public function add_blog_entry($data)
	{
		$active = 0;
		if ($this->func->isOrgaTeam()) {
			$active = 1;
		} elseif ($this->func->isBotFor($data['bezirk_id'])) {
			$active = 1;
		}

		$id = $this->insert('
			INSERT INTO 	`fs_blog_entry`
			(
			`bezirk_id`,
			`foodsaver_id`,
			`name`,
			`teaser`,
			`body`,
			`time`,
			`picture`,
			`active`
			)
			VALUES
			(
			' . (int)$data['bezirk_id'] . ',
			' . (int)$data['foodsaver_id'] . ',
			' . $this->strval($data['name']) . ',
			' . $this->strval($data['teaser']) . ',
			' . $this->strval($data['body'], true) . ',
			' . $this->dateval($data['time']) . ',
			' . $this->strval($data['picture']) . ',
			' . $active . '
			)');

		$foodsaver = array();
		$orgateam = $this->foodsaverGateway->getOrgateam();
		$botschafter = $this->foodsaverGateway->getBotschafter($data['bezirk_id']);

		foreach ($orgateam as $o) {
			$foodsaver[$o['id']] = $o;
		}
		foreach ($botschafter as $b) {
			$foodsaver[$b['id']] = $b;
		}

		$this->bellGateway->addBell(
			$foodsaver,
			'blog_new_check_title',
			'blog_new_check',
			'fa fa-bullhorn',
			array('href' => '/?page=blog&sub=edit&id=' . $id),
			array(
				'user' => $this->session->user('name'),
				'teaser' => $this->func->tt($data['teaser'], 100),
				'title' => $data['name']
			),
			'blog-check-' . $id
		);

		return $id;
	}
}
