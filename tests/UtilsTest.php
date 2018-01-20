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
}
?>
