<?php


/**
 * Inherited Methods
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
class AcceptanceTester extends \Codeception\Actor
{
	use _generated\AcceptanceTesterActions;

	/**
	* Wait to see the body element
	*/
	public function waitForPageBody()
	{
		return $this->waitForElement(['css' => 'body']);
	}

	public function login($email, $password)
	{
		$I = $this;
		$I->amOnPage('/');
		$I->fillField('email_adress', $email);
		$I->fillField('password', $password);
		$I->click('#loginbar input[type=submit]');
		$I->waitForPageBody();
		$I->seeMatches('/Willkommen|Hallo/'); // depends on user type
	}

	/**
	* Assert if a regexp is on the text content of the page
	*
	* @param regexp to check
	* @param string selector to check in, default 'html'
	*/
	public function seeMatches($regexp, $selector = 'html')
	{
		$text = $this->grabTextFrom($selector);
		$this->doAssertRegExp($regexp, $text);
	}

}