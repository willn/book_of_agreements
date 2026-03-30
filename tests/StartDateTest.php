<?php
use PHPUnit\Framework\TestCase;

require_once '../public/constants.php';
require_once '../public/config.php';
require_once '../public/logic/class_mydate.php';

class StartDateTest extends TestCase {
	/**
	 * @dataProvider provideGetStartConstruct
	 */
	public function testGetStartConstruct($year, $month, $day, $expected) {
		$date = new StartDate($year, $month, $day);
		$result = $date->toString();
		$this->assertEquals($expected, $result);
	}

	public function provideGetStartConstruct() {
		$default = '2001-01-01';
		return [
			[2026, 3, 29, '2026-03-29'],
			[2014, 10, 11, '2014-10-11'],
			[0, 0, 1, $default],
			[NULL, NULL, NULL, $default],
		];
	}

	/**
	 * @dataProvider provideGetStartOfMonth
	 */
	public function testGetStartOfOMonth($date_string, $expected) {
		$date = new StartDate();
		$date->setDate($date_string);
		$result = $date->getStartOfMonth();
		$this->assertEquals($result, $expected);
	}

	public function provideGetStartOfMonth() {
		return [
			['2018-01-01', '2017-12-31'],
			['2018-01-30', '2017-12-31'],
			['2018-02-01', '2018-01-31'],
			['2024-02-29', '2024-01-31'],
			['2024-03-14', '2024-02-29'],
			['2026-03-14', '2026-02-28'],
		];
	}

	public function testRenderSelectDate() {
		$date = new StartDate();
		$date->setDate('2021-03-29');
		$result = $date->selectDate();

		$sample = <<<EOHTML
		<p>Start Date:
		<select name="startyear" size="1"><option value="2001">2001</option>
<option value="2002">2002</option>
<option value="2003">2003</option>
<option value="2004">2004</option>
<option value="2005">2005</option>
<option value="2006">2006</option>
<option value="2007">2007</option>
<option value="2008">2008</option>
<option value="2009">2009</option>
<option value="2010">2010</option>
<option value="2011">2011</option>
<option value="2012">2012</option>
<option value="2013">2013</option>
<option value="2014">2014</option>
<option value="2015">2015</option>
<option value="2016">2016</option>
<option value="2017">2017</option>
<option value="2018">2018</option>
<option value="2019">2019</option>
<option value="2020">2020</option>
<option value="2021" selected="selected">2021</option>
<option value="2022">2022</option>
<option value="2023">2023</option>
<option value="2024">2024</option>
<option value="2025">2025</option>
<option value="2026">2026</option>
</select>
		<select name="startmonth" size="1"><option value="1">January</option>
<option value="2">February</option>
<option value="3" selected="selected">March</option>
<option value="4">April</option>
<option value="5">May</option>
<option value="6">June</option>
<option value="7">July</option>
<option value="8">August</option>
<option value="9">September</option>
<option value="10">October</option>
<option value="11">November</option>
<option value="12">December</option>
</select>
		</p>
EOHTML;

		$this->assertEquals($result, $sample);
	}
}
?>
