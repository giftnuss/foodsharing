<?php

namespace Foodsharing\Modules\Content;

use Foodsharing\Lib\Db\Db;

class ContentModel extends Db
{
	public function listFaq($cat_ids)
	{
		return $this->q('
			SELECT 
				`id`,
				`name`,
				`answer`

			FROM 
				fs_faq
				
			WHERE 
				`faq_kategorie_id` IN(' . implode(',', $cat_ids) . ')
		');
	}
}
