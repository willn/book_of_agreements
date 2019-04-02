<?php
require_once '../scripts/day_math.php';

class DayMathTest extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provide_get_days_since
	 */
	public function test_get_days_since($input, $expected) {
		$result = get_days_since($input);
		$this->assertEquals($result, $expected);
	}

	public function provide_get_days_since() {
		$day = (24 * 60 * 60);
		$week_ago = time() - (7 * $day);
		$month_ago = time() - (30 * $day);

		return [
			[NULL, NULL],
			[$week_ago, NULL],
			[$month_ago, NULL],
		];
	}
}
?>
