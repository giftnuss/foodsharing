<?php
/**
 * Created by IntelliJ IDEA.
 * User: matthias
 * Date: 13.02.18
 * Time: 13:13.
 */

namespace Helper;

class FoodsharingUI extends \Codeception\Module
{
	private function getBrowser()
	{
		return $this->getModule('\Helper\WebDriver');
	}

	/**
	 * Searches for a value in a tagselect and marks it.
	 *
	 * @param $value string to search for and click on, first element will be used if multiple turn up
	 * @param string $tagEditId the ID of the tagselect itself
	 */
	public function addInTagSelect($value, $tagEditInSelector, $tagEditId = 'tagedit')
	{
		$inputId = $tagEditInSelector . ' #' . $tagEditId . '-input';
		$this->getBrowser()->clickWithLeftButton($tagEditInSelector, 3, 3);
		$this->getBrowser()->fillField($inputId, $value);
		$selector = '//a[contains(@id, \'ui-id\') and contains(text(), "' . $value . '")]';
		$this->getBrowser()->click($selector);
	}

	public function removeFromTagSelect($value, $tagEditInId = null)
	{
		if ($tagEditInId) {
			$selector = '//*[@id="' . $tagEditInId . '"]//*[@value="' . $value . '"]/following-sibling::*';
		} else {
			$selector = '//*[@value="' . $value . '"]/following-sibling::*';
		}

		$this->getBrowser()->click($selector);
		/* wait until it is gone, it might change the layout */
		$this->getBrowser()->dontSee($selector);
	}
}
