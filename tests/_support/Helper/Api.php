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

}
