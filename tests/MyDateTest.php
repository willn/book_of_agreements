<?php
require_once '../public/constants.php';
require_once '../public/logic/mydate.php';

class MyDateTest extends PHPUnit_Framework_TestCase {
	const DEFAULTDATE = '1977-05-25';

	private $date;

	public function setUp() {
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
			['2018-1-30', self::DEFAULTDATE],
			['2018-01-3', self::DEFAULTDATE],
			[NULL, self::DEFAULTDATE],
		];
	}

	/**
	 * @dataProvider provideGetBefore
	 */
	public function testGetBefore($date, $days_offset, $expected) {
		$this->date->setDate($date);
		$result = $this->date->getBefore($days_offset);
		$this->assertEquals($result, $expected);
	}

	public function provideGetBefore() {
		return [
			['2018-01-30', 1, '2018-01-29'],
			['2018-02-01', 1, '2018-01-31'],
			['2018-01-01', 1, '2017-12-31'],
			['2018-01-30', 30, '2017-12-31'],
		];
	}

}
?>
