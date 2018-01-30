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
		];
	}

	/**
	 * @dataProvider provide_format_html
	 */
	public function test_format_html($input, $keep_eol, $expected) {
		$result = format_html($input, $keep_eol);
		$this->assertEquals($result, $expected);
	}

	public function provide_format_html() {
		return [
			['x', FALSE, 'x'],
			['<b>bold</b>', FALSE, '&lt;b&gt;bold&lt;/b&gt;'],
			["new\nline", FALSE, "new<br>\nline"],
		];
	}

}
?>
