<?php

namespace Helper;

class Maildev extends \Codeception\Module
{
	protected $requiredFields = ['url'];

	public function getMails()
	{
		$headers = array('Accept' => 'application/json');
		$response = \Unirest\Request::get($this->config['url'] . '/email', $headers);

		return $response->body;
	}

	public function _before(\Codeception\TestInterface $test)
	{
		$this->deleteAllMails();
	}

	public function deleteAllMails()
	{
		\Unirest\Request::delete($this->config['url'] . '/email/all');
	}

	public function expectNumMails($num, $timeout = 0)
	{
		if ($timeout) {
			do {
				if (count($this->getMails()) == $num) {
					return;
				}
				$timeout -= 1;
				sleep(1);
			} while ($timeout > 0);
		}
		$this->assertCount($num, $this->getMails());
	}
}
