<?php
use PHPUnit\Framework\TestCase;

require_once '../public/logic/utils.php';
require_once 'setup.php';

class UtilsTest extends DatabaseTestCase {
	/**
	 * @dataProvider get_clean_html
	 */
	public function test_clean_html($input, $expected) {
		$result = clean_html($input);
		$this->assertEquals($expected, $result);
	}

	public function get_clean_html() {
		return [
			['x', 'x'],
			['`', "'"],
			[ 'the community to\nconsider.\n\n\n2', 'the community to\nconsider.\n\n\n2' ],

			// Quotes
			['‘', "'"], // U+2018
			['’', "'"], // U+2019
			['‚', ","], // U+201A
			['‛', "'"], // U+201B
			['“', '"'], // U+201C
			['”', '"'], // U+201D
			['„', '"'], // U+201E
			['‟', '"'], // U+201F
			['‹', '<'], // U+2039
			['›', '>'], // U+203A
			['«', '<<'], // U+00AB
			['»', '>>'], // U+00BB

			// Dashes / hyphens
			['‐', '-'], // U+2010
			['-', '-'], // U+2011
			['‒', '-'], // U+2012
			['–', '-'], // U+2013
			['—', '--'], // U+2014
			['―', '--'], // U+2015

			// Ellipsis
			['…', '...'], // U+2026

			// Bullets
			['•', '*'], // U+2022
			['‣', '*'], // U+2023
			['◦', '*'], // U+25E6
			['⁃', '-'], // U+2043

			// Spaces
			[' ', ' '], // U+00A0
			[' ', ' '], // U+2000
			[' ', ' '], // U+2001
			[' ', ' '], // U+2002
			[' ', ' '], // U+2003
			[' ', ' '], // U+2004
			[' ', ' '], // U+2005
			[' ', ' '], // U+2006
			[' ', ' '], // U+2007
			[' ', ' '], // U+2008
			[' ', ' '], // U+2009
			[' ', ' '], // U+200A
			[' ', ' '], // U+202F
			[' ', ' '], // U+205F
			['　', ' '], // U+3000

			// Zero-width / invisible junk
			['​', ''], // U+200B
			['‌', ''], // U+200C
			['‍', ''], // U+200D
			['﻿', ''], // U+FEFF

			// Misc symbols commonly seen from word processors
			['™', '(TM)'], // U+2122
			['®', '(R)'], // U+00AE
			['©', '(C)'], // U+00A9
			['°', ' degrees'], // U+00B0
			['×', 'x'], // U+00D7
			['÷', '/'], // U+00F7
			['−', '-'], // U+2212

			// Arrows
			['→', '->'], // U+2192
			['←', '<-'], // U+2190

			// Fractions
			['¼', ' 1/4'], // U+00BC
			['½', ' 1/2'], // U+00BD
			['¾', ' 3/4'], // U+00BE
		];
	}

	/**
	 * @dataProvider provide_format_html
	 */
	public function test_format_html($input, $keep_eol, $expected) {
		$result = format_html($input, $keep_eol);
		$debug = [
			'input' => $input,
			'expected' => $expected,
			'result' => $result,
		];
		$this->assertEquals($result, $expected, var_export($debug, TRUE));
	}

	public function provide_format_html() {
		$example_file = implode("\n", file('example_doc.txt'));
		$example_file_expected = implode('', file('example_doc_cleaned.txt'));

		return [
			['x', FALSE, 'x'],
			['<b>bold</b>', FALSE, '&lt;b&gt;bold&lt;/b&gt;'],
			["new\nline", FALSE, "new<br>\nline"],
			[$example_file, FALSE, $example_file_expected],
			['the community to\nconsider.\n\n\n2', TRUE, "the community to\nconsider.\n\n\n2" ],
			['the community to\nconsider.\n\n\n2', FALSE, "the community to<br>\nconsider.<br>\n<br>\n<br>\n2" ],
			[
				'* Past-due bills policy\r\n* Seed money repayment plan\r\n* Head Cook receipts deadline',
				TRUE,
				"* Past-due bills policy\n* Seed money repayment plan\n* Head Cook receipts deadline",
			],
		];
	}

	public function test_get_months() {
		$months = get_months();
		$this->assertEquals(count($months), 12);
	}

	public function test_get_all_tags() {
		$tags = get_all_tags();	
		$this->assertNotEmpty($tags);
		$this->assertGreaterThan(5, count($tags));
	}

	public function testGetWordParamReturnsValueWhenValid()
	{
		$params = ['id' => 'recent'];
		$this->assertEquals('recent', getWordParam($params, 'id'));
	}

	public function testGetWordParamReturnsDefaultWhenMissing()
	{
		$params = [];
		$this->assertEquals('recent', getWordParam($params, 'id', 'recent'));
	}

	public function testGetWordParamRejectsInvalidValue()
	{
		$params = ['id' => 'foo/bar'];
		$this->assertEquals('recent', getWordParam($params, 'id', 'recent'));
	}

	public function testGetWordParamRejectsEmptyValue()
	{
		$params = ['id' => ''];
		$this->assertEquals('recent', getWordParam($params, 'id', 'recent'));
	}

	public function testGetWordParamAllowsNumbersAndUnderscores()
	{
		$params = ['id' => 'page_123'];
		$this->assertEquals('page_123', getWordParam($params, 'id'));
	}

	public function testAuthenticatedUserGetsRequestedPage()
	{
		$params = ['id' => 'recent'];
		$this->assertEquals('recent', getPageId($params, false));
	}

	public function testAuthenticatedUserDefaultsToAgreement()
	{
		$this->assertEquals('agreement', getPageId([], false));
	}

	public function testPublicUserCanAccessLogin()
	{
		$params = ['id' => 'login'];
		$this->assertEquals('login', getPageId($params, true));
	}

	public function testPublicUserCanAccessLogout()
	{
		$params = ['id' => 'logout'];
		$this->assertEquals('logout', getPageId($params, true));
	}
}
?>
