<?php

namespace Foodsharing\Modules\Settings;

use Foodsharing\Modules\Core\BaseGateway;

class SettingsGateway extends BaseGateway
{
	public function logChangedSetting(int $fsId, array $old, array $new, array $logChangedKeys, int $changerId = null): void
	{
		if (!$changerId) {
			$changerId = $fsId;
		}
		/* the logic is not exactly matching the update mechanism but should be close enough to get all changes... */
		foreach ($logChangedKeys as $k) {
			if (array_key_exists($k, $new) && $new[$k] != $old[$k]) {
				$this->db->insert(
					'fs_foodsaver_change_history',
					[
						'date' => date(\DateTime::ISO8601),
						'fs_id' => $fsId,
						'changer_id' => $changerId,
						'object_name' => $k,
						'old_value' => $old[$k],
						'new_value' => $new[$k]
					]
				);
			}
		}
	}

	public function saveInfoSettings(int $fsId, int $newsletter, int $infomail): int
	{
		return $this->db->update(
			'fs_foodsaver',
			[
				'newsletter' => $newsletter,
				'infomail_message' => $infomail
			],
			['id' => $fsId]
		);
	}

	public function unsubscribeNewsletter(string $email)
	{
		$this->db->update('fs_foodsaver', ['newsletter' => 0], ['email' => $email]);
	}

	public function getSleepData(int $fsId): array
	{
		return $this->db->fetchByCriteria(
			'fs_foodsaver',
			[
				'sleep_status',
				'sleep_from',
				'sleep_until',
				'sleep_msg'
			],
			['id' => $fsId]
		);
	}

	public function updateSleepMode(int $fsId, int $status, string $from, string $to, string $msg): int
	{
		return $this->db->update(
			'fs_foodsaver',
			[
				'sleep_status' => $status,
				'sleep_from' => $from,
				'sleep_until' => $to,
				'sleep_msg' => strip_tags($msg)
			],
			['id' => $fsId]
		);
	}

	public function addNewMail(int $fsId, string $email, string $token): int
	{
		return $this->db->insertOrUpdate(
			'fs_mailchange',
			[
				'foodsaver_id' => $fsId,
				'newmail' => strip_tags($email),
				'time' => $this->db->now(),
				'token' => strip_tags($token)
			]
		);
	}

	public function changeMail(int $fsId, string $email): int
	{
		$this->deleteMailChanges($fsId);

		return $this->db->update(
			'fs_foodsaver',
			['email' => strip_tags($email)],
			['id' => $fsId]
		);
	}

	public function abortChangemail(int $fsId): int
	{
		return $this->deleteMailChanges($fsId);
	}

	private function deleteMailChanges(int $fsId): int
	{
		return $this->db->delete(
			'fs_mailchange',
			['foodsaver_id' => $fsId]
		);
	}

	public function getMailChange(int $fsId): string
	{
		return $this->db->fetchValueByCriteria(
			'fs_mailchange',
			'newmail',
			['foodsaver_id' => $fsId]
		);
	}

	public function getNewMail(int $fsId, string $token): string
	{
		return $this->db->fetchValueByCriteria(
			'fs_mailchange',
			'newmail',
			[
				'token' => strip_tags($token),
				'foodsaver_id' => $fsId
			]
		);
	}

	public function saveApiToken(int $fsId, string $token): void
	{
		$this->db->insert(
			'fs_apitoken',
			[
				'foodsaver_id' => $fsId,
				'token' => $token
			]
		);
	}
}
