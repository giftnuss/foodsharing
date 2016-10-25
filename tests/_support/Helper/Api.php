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
	 * @param $raw
	 */
	public function seeRegExp($raw)
	{
		$response = $this->getModule('REST')->response;
		$this->assertRegExp($raw, $response);
	}

}
