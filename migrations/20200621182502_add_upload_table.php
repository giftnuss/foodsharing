<?php

use Phinx\Migration\AbstractMigration;

class AddUploadTable extends AbstractMigration
{
	public function change()
	{
		$this->table('uploads', [
			'id' => false,
			'primary_key' => ['uuid']
		])
			->addColumn('uuid', 'char', ['null' => false, 'limit' => 36])
			->addColumn('user_id', 'integer', ['null' => false, 'signed' => false, 'limit' => 10])
			->addColumn('sha256hash', 'char', ['null' => false, 'limit' => 64])
			->addColumn('mimeType', 'string', ['null' => false, 'limit' => 255])
			->addColumn('uploaded_at', 'datetime', ['null' => false])
			->addColumn('lastaccess_at', 'datetime', ['null' => false])
			->addColumn('filesize', 'integer', ['null' => false, 'signed' => false, 'limit' => 10])
			->addIndex(['uuid'], [
				'name' => 'uuid',
				'unique' => true,
			])
			->create();
	}
}
