<?php
use PHPUnit\Framework\TestCase;

require_once '../public/constants.php';
require_once '../public/config.php';
require_once '../public/logic/class_mydate.php';

class EndDateTest extends TestCase {
	/**
	 * @dataProvider provideGetEndOfMonth
	 */
	public function testGetEndOfOMonth($date_string, $expected) {
		$date = new EndDate();
		$date->setDate($date_string);
		$result = $date->getEndOfMonth();
		$this->assertEquals($result, $expected);
	}

	public function provideGetEndOfMonth() {
		$now = date('Y-m-d');
		return [
			['2017-12-31', '2018-01-01'],
			['2018-01-01', '2018-02-01'],
			['2018-01-30', '2018-02-01'],
			['2024-02-29', '2024-03-01'],
			['2024-03-14', '2024-04-01'],
			[$now, $now],
		];
	}
}
?>
