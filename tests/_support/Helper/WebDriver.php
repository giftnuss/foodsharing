<?php

namespace Helper;

// here you can define custom WebDriver actions
// all public methods declared in helper class will be available in $I

use Facebook\WebDriver\WebDriverExpectedCondition;

class WebDriver extends \Codeception\Module\WebDriver
{
	/**
	 * Checks if the element is REALLY visible in the current viewport.
	 * The available standard WebDriver actions only check if an element is
	 * somewhere in th DOM, but not nessecarily if it's visible.
	 */
	public function elementVisibleInViewport($selector)
	{
		$visible = $this->executeJS('
                    var elem = document.querySelector(arguments[0]);
                    box = elem.getBoundingClientRect();
                    cx = box.left + box.width / 2;
                    cy = box.top + box.height / 2;
                    return document.elementFromPoint(cx, cy) === elem;', [$selector]
		);

		return $this->assertTrue((bool)$visible, 'Element \'' . $selector . '\' not visible in viewport');
	}

	public function seeFormattedDateInRange($min, $max, $format, $actual)
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

	public function fillFieldJs($selector, $value)
	{
		$this->executeJS(
		'document.querySelectorAll(\'' . $selector . '\').forEach(el => el.value = \'' . $value . '\')'
		);
	}

	private function waitFor($condition, $timeout)
	{
		$this->webDriver->wait($timeout)->until($condition);
	}
}
