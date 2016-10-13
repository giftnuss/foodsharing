<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Api extends \Codeception\Module
{

	public function seeResponseIsHtml()
	{
		$response = $this->getModule('REST')->response;
		$this->assertRegExp('~<!doctype html>.*~im', $response);
	}

}
