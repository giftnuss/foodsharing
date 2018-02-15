<?php

namespace Helper;

// here you can define custom WebDriver actions
// all public methods declared in helper class will be available in $I

use Codeception\Util\Locator;
use Facebook\WebDriver\WebDriverExpectedCondition;
use WebDriverBy;

class WebDriver extends \Codeception\Module\WebDriver
{
	/**
	 * Same as assertRegExp but makes it available inside AcceptanceTester
	 * (not sure why it isn't anyway... there might be a better way).
	 */
	public function doAssertRegExp($regexp, $text)
	{
		return $this->assertRegExp($regexp, $text);
	}

	public function formattedDateInRange($min, $max, $format, $actual)
	{
		$date = \DateTime::createFromFormat($format, $actual, new \DateTimeZone('Europe/Berlin'));
		$this->assertGreaterOrEquals($min, $date, 'Date is in past');
		$this->assertLessThanOrEqual($max, $date, 'Date is in future');
	}

	public function waitForFileExists($filename, $timeout = 4)
	{
		$condition = function () use ($filename) {
			return file_exists($filename);
		};
		$this->waitFor($condition, $timeout);
	}

	public function waitForTextNotVisible($text, $timeout = 10, $selector = null)
	{
		if ($selector === null) {
			$selector = WebDriverBy::xpath('//body');
		} else {
			$selector = $this->getLocator($selector);
		}

		$condition = WebDriverExpectedCondition::not(WebDriverExpectedCondition::elementTextContains($selector, $text));

		$message = sprintf(
			'Waited for %d secs but text %s still not found',
			$timeout,
			Locator::humanReadableString($text)
		);

		$this->webDriver->wait($timeout)->until($condition, $message);
	}

	/**
	 * Wait until the open URL equals given one.
	 *
	 * @param $url
	 * @param int $timeout
	 */
	public function waitUrlEquals($url, $timeout = 4)
	{
		$condition = WebDriverExpectedCondition::urlIs($url);
		$this->waitFor($condition, $timeout);
	}

	public function unlockAllInputFields()
	{
		$this->executeJs(
			'document.querySelectorAll(\'*[readOnly]\').forEach(el => el.readOnly = false)'
		);
	}

	private function waitFor($condition, $timeout)
	{
		$this->webDriver->wait($timeout)->until($condition);
	}
}
