<?php
namespace Helper;

class Api extends \Codeception\Module
{

	/**
	* Checks the response content is html
	*/
	public function seeResponseIsHtml()
	{
		$response = $this->getModule('REST')->response;
		$this->assertRegExp('~<!doctype html>.*~im', $response);
	}

	/**
	 * Checks is a regular expression is found in response content
	 * @param $pattern
	 */
	public function seeRegExp($pattern)
	{
		$response = $this->getModule('REST')->response;
		$this->assertRegExp($pattern, $response);
	}

	public function dontSeeRegExp($pattern)
	{
		$response = $this->getModule('REST')->response;
		$this->assertNotRegExp($pattern, $response);
	}

	public function login($email, $pass = 'password')
	{
		$rest = $this->getModule('REST');
		$rest->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

		$rest->sendPOST('/?page=login', [
			'email_adress' => $email,
			'password' => $pass
		]);
	}

}
