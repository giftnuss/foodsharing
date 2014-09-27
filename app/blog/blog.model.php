<?php
class BlogModel extends Model
{
	public function canEdit($article_id)
	{
		if(isOrgaTeam())
		{
			return true;
		}
		if($val = $this->getValues(array('bezirk_id','foodsaver_id'), 'blog_entry', $article_id))
		{
			if(fsId() == $val['foodsaver_id'] || isBotFor($val['bezirk_id']))
			{
				return true;
			}
		}
		return false;
	}
	
	public function listArticle()
	{
		$not = '';
		if(!isOrgaTeam())
		{
			$not = 'WHERE 		`bezirk_id` IN ('.implode(',', $this->getBezirkIds()).')';
		}
		return $this->q('
			SELECT 	 	`id`,
						`name`,
						`time`,
						UNIX_TIMESTAMP(`time`) AS time_ts,
						`active`,
						`bezirk_id`
		
			FROM 		`'.PREFIX.'blog_entry`
	
			'.$not.'
	
			ORDER BY `id` DESC');
	}
	
	public function del_blog_entry($id)
	{
		return $this->del('
			DELETE FROM 	`'.PREFIX.'blog_entry`
			WHERE 			`id` = '.$this->intval($id).'
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
			
			FROM 		`'.PREFIX.'blog_entry`
			
			WHERE 		`id` = ' . $this->intval($id));
	
	
	
		return $out;
	}
	
	public function add_blog_entry($data)
	{
		$active = 0;
		if(isOrgateam())
		{
			$active = 1;
		}
		elseif (isBotFor($data['bezirk_id']))
		{
			$active = 1;
		}
		
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'blog_entry`
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
			'.$this->intval($data['bezirk_id']).',
			'.$this->intval($data['foodsaver_id']).',
			'.$this->strval($data['name']).',
			'.$this->strval($data['teaser']).',
			'.$this->strval($data['body'],true).',
			'.$this->dateval($data['time']).',
			'.$this->strval($data['picture']).',
			'.$active.'
			)');
	
		if($active == 0)
		{
			$foodsaver = array();
			$orgateam = $this->getOrgateam();
			$botschafter = $this->getBotschafter($data['bezirk_id']);
			
			foreach ($orgateam as $o)
			{
				$foodsaver[$o['id']] = $o;
			}
			foreach ($botschafter as $b)
			{
				$foodsaver[$b['id']] = $b;
			}
			
			$this->addGlocke($foodsaver,$data['name'],'Neuer Blog Artikel','?page=blog&sub=edit&id='.$id);

		}
	
		return $id;
	}
}