<?php

use Codeception\Lib\Friend;

/**
 * Inherited Methods.
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends Codeception\Actor
{
	use _generated\AcceptanceTesterActions;
	use \Codeception\Lib\Actor\Shared\Friend;

	/**
	 * Wait to see the body element.
	 */
	public function waitForPageBody()
	{
		return $this->waitForElement(['css' => 'body']);
	}

	public function login($email, $password = 'password')
	{
		$I = $this;
		$I->amOnPage('/');
		$I->executeJS('window.localStorage.clear();');
		$I->waitForElement('#login-email');
		$I->fillField('#login-email', $email);
		$I->fillField('#login-password', $password);
		$I->click('#topbar .btn');
		$I->waitForActiveAPICalls();
		$I->waitForElementNotVisible('#pulse-success');
		$I->waitForPageBody();
		$I->seeMatches('/Willkommen|Hallo/'); // depends on user type
	}

	public function logout()
	{
		$this->amOnPage('/?page=logout');
		$this->waitForPageBody();
	}

	/**
	 * Assert if a regexp is on the text content of the page.
	 *
	 * @param regexp to check
	 * @param string selector to check in, default 'html'
	 */
	public function seeMatches($regexp, $selector = 'html')
	{
		$text = $this->grabTextFrom($selector);
		$this->assertRegExp($regexp, $text);
	}

	public function waitForActiveAPICalls($timeout = 60)
	{
		$this->waitForJS('return window.fetch.activeFetchCalls == 0;', $timeout);
	}
}
