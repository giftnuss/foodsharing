<?php

namespace Foodsharing\Modules\Uploads;

use Foodsharing\Modules\Core\BaseGateway;

// our mysql query builder doesn't offer UUID(), so we use this PHP code
// until we moved to a new library
function uuid_v4()
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

class UploadsGateway extends BaseGateway
{
	public function getFile(string $uuid): array
	{
		return $this->db->fetchByCriteria('uploads', ['uuid', 'mimeType'], ['uuid' => $uuid]);
	}

	public function addFile(string $tmpFile): array
	{
		$hash = hash_file('sha256', $tmpFile);
		$size = filesize($tmpFile);
		$mimeType = mime_content_type($tmpFile);

		// same file already uploaded?
		if ($res = $this->db->fetchByCriteria('uploads', ['uuid'], ['sha256hash' => $hash])) {
			// update uploaded date
			$this->db->update('uploads', [
				'uploadedAt' => $this->db->now(),
				'lastAccessAt' => $this->db->now()
			], ['uuid' => $res['uuid']]);

			return [
				'uuid' => $res['uuid'],
				'mimeType' => $mimeType,
				'filesize' => $size,
				'isReuploaded' => true
			];
		}

		$uuid = uuid_v4();

		$this->db->insert('uploads', [
			'uuid' => $uuid,
			'sha256hash' => $hash,
			'mimeType' => $mimeType,
			'uploadedAt' => $this->db->now(),
			'lastAccessAt' => $this->db->now(),
			'filesize' => $size,
		]);

		return [
			'uuid' => $uuid,
			'mimeType' => $mimeType,
			'filesize' => $size,
			'isReuploaded' => false,
		];
	}

	public function touchFile(string $uuid): void
	{
		$this->db->update('uploads', ['lastAccessAt' => $this->db->now()], ['uuid' => $uuid]);
	}
}
