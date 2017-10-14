<?php


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
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class HtmlAcceptanceTester extends \Codeception\Actor
{
	use _generated\HtmlAcceptanceTesterActions;

	/**
	 * Define custom actions here.
	 */

	/**
	 * Wait to see the body element.
	 */
	public function waitForPageBody()
	{
		/* This is not needed and not even compatible with PHP Browser but should
		be available so tests can be switched to another backend anytime */
	}

	public function login($email, $password = 'password')
	{
		$I = $this;
		$I->amOnPage('/');
		$I->fillField('email_adress', $email);
		$I->fillField('password', $password);
		$I->click('#loginbar input[type=submit]');
	}
}
