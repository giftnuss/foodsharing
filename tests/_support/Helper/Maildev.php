<?php

namespace Helper;

use GuzzleHttp\Client;

class Maildev extends \Codeception\Module
{
	protected $requiredFields = ['url'];

	public function __construct($moduleContainer, $config = null)
	{
		parent::__construct($moduleContainer, $config);
		$this->client = new Client(['base_uri' => $this->config['url'], 'headers' => ['Accept' => 'application/json']]);
	}

	public function getMails()
	{
		return json_decode($this->client->get('/email')->getBody());
	}

	public function _before(\Codeception\TestInterface $test)
	{
		$this->deleteAllMails();
	}

	public function deleteAllMails()
	{
		$this->client->delete('/email/all');
	}

	public function expectNumMails($num, $timeout = 0)
	{
		if ($timeout) {
			do {
				if (count($this->getMails()) == $num) {
					return;
				}
				--$timeout;
				sleep(1);
			} while ($timeout > 0);
		}
		$this->assertCount($num, $this->getMails());
	}
}
