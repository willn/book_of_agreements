<?php
require_once '../public/logic/utils.php';

class UtilsTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider get_clean_html
	 */
	public function test_clean_html($input, $expected) {
		$result = clean_html($input);
		$this->assertEquals($result, $expected);
	}

	public function get_clean_html() {
		$long_dash = 'Â'; // 194

		return [
			['x', 'x'],
			['`', "'"],
			[$long_dash, '-'],
			[ 'the community to\nconsider.\n\n\n2', 'the community to\nconsider.\n\n\n2' ],
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
		$example_file = implode(file('example_doc.txt'), "\n");
		$example_file_expected = implode(file('example_doc_cleaned.txt'), '');

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
}
?>
