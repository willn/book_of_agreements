<?php
require_once '../public/logic/utils.php';
require_once '../scripts/search_includes.php';


class MinutesSearchTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider provide_get_find_cmds
	 */
	public function test_get_find_cmds($input, $year, $month, $expected) {
		$result = get_find_cmds($input, $year, $month);
		$debug = [
			'input' => $input,
			'expected' => $expected,
			'result' => $result,
		];
		$this->assertEquals($expected, $result, print_r($debug, TRUE));
	}

	public function provide_get_find_cmds() {
		$directories = [
			'buildings-minutes',
			'ch-minutes',
			'finance-minutes',
			'grounds-minutes',
			'infoco-minutes',
			'meals-minutes',
			'membership-minutes',
			'minutes',
			'process-minutes',
			'steering-minutes',
			'work-minutes',
			'workshop-minutes',
		];
		$cmds = [
			"/usr/bin/find /usr/local/cpanel/3rdparty/mailman/archives/private/buildings-minutes_gocoho.org/2018-November/* -type f -name '0*.html' -mtime -1",
			"/usr/bin/find /usr/local/cpanel/3rdparty/mailman/archives/private/ch-minutes_gocoho.org/2018-November/* -type f -name '0*.html' -mtime -1",
			"/usr/bin/find /usr/local/cpanel/3rdparty/mailman/archives/private/grounds-minutes_gocoho.org/2018-November/* -type f -name '0*.html' -mtime -1",
			"/usr/bin/find /usr/local/cpanel/3rdparty/mailman/archives/private/meals-minutes_gocoho.org/2018-November/* -type f -name '0*.html' -mtime -1",
			"/usr/bin/find /usr/local/cpanel/3rdparty/mailman/archives/private/membership-minutes_gocoho.org/2018-November/* -type f -name '0*.html' -mtime -1",
			"/usr/bin/find /usr/local/cpanel/3rdparty/mailman/archives/private/minutes_gocoho.org/2018-November/* -type f -name '0*.html' -mtime -1",
			"/usr/bin/find /usr/local/cpanel/3rdparty/mailman/archives/private/process-minutes_gocoho.org/2018-November/* -type f -name '0*.html' -mtime -1",
			"/usr/bin/find /usr/local/cpanel/3rdparty/mailman/archives/private/work-minutes_gocoho.org/2018-November/* -type f -name '0*.html' -mtime -1",
		];

		return [
			[$directories, 2018, 'November', $cmds]
		];
	}
}
?>
