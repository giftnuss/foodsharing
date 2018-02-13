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

	public function update_message_tpl($id, $data)
	{
		return $this->update('
		UPDATE 	`' . PREFIX . 'message_tpl`

		SET 	`language_id` =  ' . $this->intval($data['language_id']) . ',
				`name` =  ' . $this->strval($data['name']) . ',
				`subject` =  ' . $this->strval($data['subject']) . ',
				`body` =  "' . $this->safe($data['body']) . '"

		WHERE 	`id` = ' . $this->intval($id));
	}
}
