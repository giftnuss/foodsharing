<?php

class SanitizerTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Services\SanitizerService
	 */
	private $sanitizer;

	protected function _before()
	{
		$this->sanitizer = $this->tester->get(\Foodsharing\Services\SanitizerService::class);
	}

	protected function _after()
	{
	}

	// tests
	public function testPlainToHtmlEncodesTags()
	{
		$in = 'Hi<there>, you <b>keep this</b>?';
		$out = $this->sanitizer->plainToHtml($in);
		$this->assertEquals(
			'Hi&lt;there&gt;, you &lt;b&gt;keep this&lt;/b&gt;?',
			$out
		);
	}

	/* this test is supposed to fail until the input handling is changed */
	/*
	public function testMarkdownToHtmlEncodesTags()
	{
		$in = 'Hi<there>, you <b>keep this</b>?';
		$out = $this->sanitizer->markdownToHtml($in);
		$this->assertEquals(
			'Hi&lt;there&gt;, you &lt;b&gt;keep this&lt;/b&gt;?',
			$out
		);
	}
	*/

	/* This test is only supposed to guarantee temporary backwards compatibility until input handling is changed.
	Then, the test above should be used instead! */
	public function testMarkdownToHtmlDismissesTags()
	{
		$in = 'Hi<there>, you <b>keep this</b>?';
		$out = $this->sanitizer->markdownToHtml($in);
		$this->assertContains(
			'Hi, you keep this?',
			$out
		);
	}

	public function testMarkdownToHtmlHandlesNewline()
	{
		$in = "Hi\nthere";
		$out = $this->sanitizer->markdownToHtml($in);
		$this->assertContains(
			'Hi<br />',
			$out
		);
		/* We do not want to specify if it keeps newline or not, but we want to have a break in the output. */
		$this->assertContains(
			'there',
			$out
		);
	}

	public function testHtmlToPlainConvertsNewline()
	{
		$in = 'Hi<br />there';
		$out = $this->sanitizer->htmlToPlain($in);
		$this->assertEquals(
			"Hi\nthere",
			$out
		);
	}

	public function testMarkdownRendersSimpleList()
	{
		$in = "* Hi\n* there";
		$out = $this->sanitizer->markdownToHtml($in);
		$this->assertContains(
			'<li>Hi</li>',
			$out
		);
	}
}
