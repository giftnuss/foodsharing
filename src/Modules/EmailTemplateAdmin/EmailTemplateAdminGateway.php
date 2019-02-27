<?php

namespace Foodsharing\Modules\EmailTemplateAdmin;

use Foodsharing\Modules\Core\BaseGateway;

/**
 * @deprecated
 *
 * @return $this
 */
class EmailTemplateAdminGateway extends BaseGateway
{
	/**
	 * @deprecated
	 *
	 * @return $this
	 */
	public function getOne_message_tpl($id): array
	{
		// This will break CI for now, first implement all messages as twig...
		//trigger_error('Method ' . __METHOD__ . ' is deprecated. Called for id=' . $id, E_USER_DEPRECATED);

		return $this->db->fetch('
			SELECT
			`id`,
			`language_id`,
			`name`,
			`subject`,
			`body`

			FROM 		`fs_message_tpl`

			WHERE 		`id` = :id',
			[':id' => $id]
		);
	}

	/**
	 * @deprecated
	 *
	 * @return $this
	 */
	public function add_message_tpl($data): int
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

		return $this->db->insert('fs_message_tpl', [
			'language_id' => $data['language_id'],
			'name' => strip_tags($data['name']),
			'subject' => strip_tags($data['subject']),
			'body' => strip_tags($data['body'])
		]);
	}

	/**
	 * @deprecated
	 *
	 * @return $this
	 */
	public function getBasics_message_tpl(): array
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

		$stm = '
			SELECT 	 	`id`,
						`name`
			
			FROM 		`fs_message_tpl`
			ORDER BY  	`name`';

		return $this->db->fetchAll($stm);
	}

	/**
	 * @deprecated
	 *
	 * @return $this
	 */
	public function del_message_tpl($id): int
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated. Called for id=' . $id, E_USER_DEPRECATED);

		return $this->db->delete('fs_message_tpl', ['id' => (int)$id]);
	}

	/**
	 * @deprecated
	 *
	 * @return $this
	 */
	public function update_message_tpl($id, $data): int
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated. Called for id=' . $id, E_USER_DEPRECATED);

		return $this->db->update('fs_message_tpl',
			[
				'language_id' => (int)$data['language_id'],
				'name' => strip_tags($data['name']),
				'subject' => strip_tags($data['subject']),
				'body' => $data['body']
			],
			['id' => (int)$id]
		);
	}
}
