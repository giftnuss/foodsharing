<?php

namespace Foodsharing\Modules\Uploads;

use Exception;
use Foodsharing\Modules\Core\BaseGateway;

class UploadsGateway extends BaseGateway
{
	/**
	 * Returns the mimetype of the file with the specified UUID. Throws an exception if the file does not exist.
	 *
	 * @throws Exception
	 */
	public function getMimeType(string $uuid): string
	{
		return $this->db->fetchValueByCriteria('uploads', 'mimetype', ['uuid' => $uuid]);
	}

	/**
	 * Makes sure a file is listed in the database. If it does not yet exist, it will be created. If it does exist, the
	 * uploaded and access timestamps will be updated. Returns the UUID and a 'isReuploaded' flag.
	 */
	public function addFile(int $userId, string $hash, int $size, string $mimeType): array
	{
		// same file already uploaded?
		if ($res = $this->db->fetchByCriteria('uploads', ['uuid'], ['sha256hash' => $hash])) {
			// update uploaded date
			$this->db->update('uploads', [
				'uploaded_at' => $this->db->now(),
				'lastaccess_at' => $this->db->now()
			], ['uuid' => $res['uuid']]);

			return [
				'uuid' => $res['uuid'],
				'isReuploaded' => true
			];
		}

		$uuid = $this->uuid_v4();

		$this->db->insert('uploads', [
			'uuid' => $uuid,
			'user_id' => $userId,
			'sha256hash' => $hash,
			'mimetype' => $mimeType,
			'uploaded_at' => $this->db->now(),
			'lastaccess_at' => $this->db->now(),
			'filesize' => $size,
		]);

		return [
			'uuid' => $uuid,
			'isReuploaded' => false
		];
	}

	/**
	 * Updates the last access timestamp of the file with the specified UUID.
	 */
	public function touchFile(string $uuid): void
	{
		$this->db->update('uploads', ['lastaccess_at' => $this->db->now()], ['uuid' => $uuid]);
	}

	// our mysql query builder doesn't offer UUID(), so we use this PHP code
	// until we moved to a new library
	private function uuid_v4()
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		// 32 bits for "time_low"
		random_int(0, 0xffff), random_int(0, 0xffff),

		// 16 bits for "time_mid"
		random_int(0, 0xffff),

		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 4
		random_int(0, 0x0fff) | 0x4000,

		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		random_int(0, 0x3fff) | 0x8000,

		// 48 bits for "node"
		random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
		);
	}

	// our mysql query builder doesn't offer UUID(), so we use this PHP code
	// until we moved to a new library
	private function uuid_v4()
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		// 32 bits for "time_low"
		random_int(0, 0xffff), random_int(0, 0xffff),

		// 16 bits for "time_mid"
		random_int(0, 0xffff),

		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 4
		random_int(0, 0x0fff) | 0x4000,

		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		random_int(0, 0x3fff) | 0x8000,

		// 48 bits for "node"
		random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
		);
	}

	// our mysql query builder doesn't offer UUID(), so we use this PHP code
	// until we moved to a new library
	private function uuid_v4()
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		// 32 bits for "time_low"
		mt_rand(0, 0xffff), mt_rand(0, 0xffff),

		// 16 bits for "time_mid"
		mt_rand(0, 0xffff),

		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 4
		mt_rand(0, 0x0fff) | 0x4000,

		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		mt_rand(0, 0x3fff) | 0x8000,

		// 48 bits for "node"
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}
}
