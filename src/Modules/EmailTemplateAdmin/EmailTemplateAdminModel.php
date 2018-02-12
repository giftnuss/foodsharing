<?php

namespace Foodsharing\Modules\EmailTemplateAdmin;

use Foodsharing\Modules\Core\Model;

class EmailTemplateAdminModel extends Model
{
	public function getBasics_message_tpl()
	{
		global $db;

		return $db->q('
			SELECT 	 	`id`,
						`name`
			
			FROM 		`' . PREFIX . 'message_tpl`
			ORDER BY  	`name`');
	}

	public function del_message_tpl($id)
	{
		global $db;

		return $db->del('
			DELETE FROM 	`' . PREFIX . 'message_tpl`
			WHERE 			`id` = ' . $db->intval($id));
	}
}
