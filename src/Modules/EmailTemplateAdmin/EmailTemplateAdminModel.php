<?php

namespace Foodsharing\Modules\EmailTemplateAdmin;

use Foodsharing\Modules\Core\Model;

class EmailTemplateAdminModel extends Model
{
	public function getBasics_message_tpl()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
			
			FROM 		`fs_message_tpl`
			ORDER BY  	`name`');
	}

	public function del_message_tpl($id)
	{
		return $this->del('
			DELETE FROM 	`fs_message_tpl`
			WHERE 			`id` = ' . $this->intval($id));
	}

	public function update_message_tpl($id, $data)
	{
		return $this->update('
		UPDATE 	`fs_message_tpl`

		SET 	`language_id` =  ' . $this->intval($data['language_id']) . ',
				`name` =  ' . $this->strval($data['name']) . ',
				`subject` =  ' . $this->strval($data['subject']) . ',
				`body` =  "' . $this->safe($data['body']) . '"

		WHERE 	`id` = ' . $this->intval($id));
	}
}
