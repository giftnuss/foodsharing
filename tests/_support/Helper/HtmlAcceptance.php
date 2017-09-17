<?php

namespace Helper;

use Codeception\TestInterface;
use Codeception\Test\Descriptor;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class HtmlAcceptance extends \Codeception\Module
{
	public function _failed(TestInterface $test, $fail)
	{
		$filename = preg_replace('~\W~', '.', Descriptor::getTestSignature($test));
		$outputDir = codecept_output_dir();
		$this->getModule('PhpBrowser')->_savePageSource($outputDir . mb_strcut($filename, 0, 244, 'utf-8') . '.fail.html');
		$this->debug("page source was saved into '$outputDir' dir");
	}
}
