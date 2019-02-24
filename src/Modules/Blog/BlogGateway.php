<?php

namespace Foodsharing\Modules\Blog;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Services\SanitizerService;

final class BlogGateway extends BaseGateway
{
	private $bellGateway;
	private $foodsaverGateway;
	private $sanitizerService;
	private $session;

	public function __construct(
		BellGateway $bellGateway,
		Database $db,
		FoodsaverGateway $foodsaverGateway,
		SanitizerService $sanitizerService,
		Session $session
	) {
		parent::__construct($db);
		$this->bellGateway = $bellGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->sanitizerService = $sanitizerService;
		$this->session = $session;
	}

	public function update_blog_entry(int $id, array $data): int
	{
		$data_stripped = [
			'bezirk_id' => $data['bezirk_id'],
			'foodsaver_id' => $data['foodsaver_id'],
			'name' => strip_tags($data['name']),
			'teaser' => strip_tags($data['teaser']),
			'body' => strip_tags($data['body']),
			'time' => strip_tags($data['time']),
		];

		if (!empty($data['picture'])) {
			$data_stripped['picture'] = strip_tags($data['picture']);
		}

		return $this->db->update(
			'fs_blog_entry',
			$data_stripped,
			['id' => $id]
		);
	}

	public function canEdit(int $article_id): bool
	{
		if ($val = $this->db->fetchByCriteria('blog_entry', ['bezirk_id', 'foodsaver_id'], ['id' => $article_id])) {
			if ($this->session->id() == $val['foodsaver_id'] || $this->session->isAdminFor($val['bezirk_id'])) {
				return true;
			}
		}

		return false;
	}

	public function canAdd(int $bezirkId): bool
	{
		if ($this->session->isOrgaTeam()) {
			return true;
		}

		if ($this->session->isAdminFor($bezirkId)) {
			return true;
		}

		return false;
	}

	public function getPost(int $id): array
	{
		return $this->db->fetch('
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
				b.id = :fs_id',
		[':fs_id' => $id]);
	}

	public function listNews(int $page): array
	{
		$page = ($page - 1) * 10;

		return $this->db->fetchAll(
			'
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
			LIMIT :page,10',
			[':page' => $page]
		);
	}

	public function listArticle(): array
	{
		$not = '';
		if (!$this->session->isOrgaTeam()) {
			$not = 'WHERE 		`bezirk_id` IN (' . implode(',', $this->session->listRegionIDs()) . ')';
		}

		return $this->db->fetchAll('
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

	public function del_blog_entry($id): int
	{
		return $this->db->delete('fs_blog_entry', ['id' => (int)$id]);
	}

	public function getOne_blog_entry(int $id): array
	{
		return $this->db->fetch(
			'
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
			WHERE 		`id` = :fs_id',
			[':fs_id' => $id]
		);
	}

	public function add_blog_entry($data)
	{
		$active = 0;
		if ($this->session->isOrgaTeam() || $this->session->isAdminFor($data['bezirk_id'])) {
			$active = 1;
		}

		$id = $this->db->insert(
			'fs_blog_entry',
			[
				'bezirk_id' => (int)$data['bezirk_id'],
				'foodsaver_id' => (int)$data['foodsaver_id'],
				'name' => $this->$data['name'],
				'teaser' => $this->$data['teaser'],
				'body' => $this->$data['body'], true,
				'time' => $this->$data['time'],
				'picture' => $this->$data['picture'],
				'active' => $active
			]
		);

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
			'fas fa-bullhorn',
			['href' => '/?page=blog&sub=edit&id=' . $id],
			[
				'user' => $this->session->user('name'),
				'teaser' => $this->sanitizerService->tt($data['teaser'], 100),
				'title' => $data['name']
			],
			'blog-check-' . $id
		);

		return $id;
	}
}
