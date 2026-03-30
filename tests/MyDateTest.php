<?php
use PHPUnit\Framework\TestCase;

require_once '../public/constants.php';
require_once '../public/logic/class_mydate.php';

class MyDateTest extends TestCase {
	const DEFAULTDATE = '1977-05-25';

	private $date;

	public function setUp() : void {
		$this->date = new MyDate(1977, 5, 25, 'star wars');
	}

	public function testToString() {
		$result = $this->date->toString();
		$this->assertEquals($result, self::DEFAULTDATE);
	}

	/**
	 * @dataProvider provideSetDate
	 */
	public function testSetDate($input, $expected) {
		$this->date->setDate($input);
		$result = $this->date->toString();
		$this->assertEquals($result, $expected);
	}

	public function provideSetDate() {
		return [
			['2018-01-30', '2018-01-30'],
			['123-01-30', self::DEFAULTDATE],
			['2026-02-29', '2026-02-29'],
			['2018-01-03', '2018-01-03'],
		];
	}
}
?>
