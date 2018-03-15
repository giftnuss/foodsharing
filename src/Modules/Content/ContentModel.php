<?php

namespace Foodsharing\Modules\Content;

use Foodsharing\Modules\Core\Model;

class ContentModel extends Model
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
