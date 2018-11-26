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

	/**
	 * @dataProvider provide_get_date_parts
	 */
	public function test_get_date_parts($input, $expected) {
		$result = get_date_parts($input);
		$this->assertEquals($expected, $result);
	}

	public function provide_get_date_parts() {
		return [
			[
				"[Ch-minutes] Common House Minutes Aug 14, 2018", 
				[ 'month' => 8, 'day' => 14, 'year' => 2018 ]
			],
			[
				"[Ch-minutes] Common House Committee Minutes June 12, 2018",
				[ 'month' => 6, 'day' => 12, 'year' => 2018 ]
			],
			[
				"[Ch-minutes] Common House Committee Minutes June 23, 2018",
				[ 'month' => 6, 'day' => 23, 'year' => 2018 ]
			],
			[
				"[Finance-minutes] Minutes of 4-11-17 Finance &amp; Legal Meeting",
				[ 'month' => 4, 'day' => 11, 'year' => 2017 ]
			],
			[
				"[Finance-minutes] Minutes of 3-15-17 Finance &amp; Legal Committee	Meeting",
				[ 'month' => 3, 'day' => 15, 'year' => 2017 ]
			],
			[
				"[Finance-minutes] Fwd: 'invoice' for hotbox work",
				[]
			],
			[
				"[Finance-minutes] Minutes of 7-11-18 GO Finance &amp; Legal Committee Meeting",
				[ 'month' => 7, 'day' => 11, 'year' => 2018 ]
			],
			[
				"[Finance-minutes] Minutes of 6-27-18 GO Finance &amp; Legal Committee Meeting",
				[ 'month' => 6, 'day' => 27, 'year' => 2018 ]
			],
			[
				"[Finance-minutes] Minutes of 6-13-18 Finance &amp; Legal Committee Meeting",
				[ 'month' => 6, 'day' => 13, 'year' => 2018 ]
			],
			[
				"[Finance-minutes] Minutes of 5-9-18 GO Finance &amp; Legal Committee Meeting",
				[ 'month' => 5, 'day' => 9, 'year' => 2018 ]
			],
			[
				"[Finance-minutes] Minutes of 5-24-18 GO Finance &amp; Legal Committee Meeting",
				[ 'month' => 5, 'day' => 24, 'year' => 2018 ]
			],
			[
				"[Grounds-minutes] Grounds minutes 7/24/18",
				[ 'month' => 7, 'day' => 24, 'year' => 2018 ]
			],
			[
				"[Grounds-minutes] [go-talk]  Grounds minutes 7/24/18",
				[ 'month' => 7, 'day' => 24, 'year' => 2018 ]
			],
			[
				"[Grounds-minutes] Grounds minutes 6/19/18",
				[ 'month' => 6, 'day' => 19, 'year' => 2018 ]
			],
			[
				"[Grounds-minutes] Grounds minutes, July 11 meeting",
				[ 'month' => 7, 'day' => 11, 'year' => 2018 ]
			],
			[
				"[Grounds-minutes] Grounds minutes 4/23/18",
				[ 'month' => 4, 'day' => 23, 'year' => 2018 ]
			],
			[
				"[Meals-minutes] April 2 Meals Committee Minutes",
				[ 'month' => 4, 'day' => 2, 'year' => 2018 ]
			],
			[
				"[Meals-minutes] May 1 Meals Committee Minutes",
				[ 'month' => 5, 'day' => 1, 'year' => 2018 ]
			],
			[
				"[Meals-minutes] August 14 Meals Committee Meeting Minutes",
				[ 'month' => 8, 'day' => 14, 'year' => 2018 ]
			],
			[
				"[Meals-minutes] Minutes of July 17 Meals Committee meeting",
				[ 'month' => 7, 'day' => 17, 'year' => 2018 ]
			],
			[
				"[Meals-minutes] June 11 Meals Committee Meeting Minutes",
				[ 'month' => 6, 'day' => 11, 'year' => 2018 ]
			],
			[
				"[Membership-minutes] MEMBERSHIP MEETING MINUTES (4.30.2017)",
				[ 'month' => 4, 'day' => 30, 'year' => 2017 ]
			],
			[
				"[Minutes] Minutes of 5-3-17 Great Oak Community Meeting",
				[ 'month' => 5, 'day' => 3, 'year' => 2017 ]
			],
			[
				"[Minutes] Minutes, 7/16/2018 community meeting",
				[ 'month' => 7, 'day' => 16, 'year' => 2018 ]
			],
			[
				"[Minutes] minutes, 6/6/18 community meeting",
				[ 'month' => 6, 'day' => 6, 'year' => 2018 ]
			],
			[
				"[Minutes] Minutes, 5/19/18 community meeting, sharing circle",
				[ 'month' => 5, 'day' => 19, 'year' => 2018 ]
			],
			[
				"[Minutes] Minutes of 6-18-18 Great Oak Community Meeting",
				[ 'month' => 6, 'day' => 18, 'year' => 2018 ]
			],
			[
				"[Minutes] Minutes of 5-3-18 Great Oak Community Meeting",
				[ 'month' => 5, 'day' => 3, 'year' => 2018 ]
			],
			[
				"[Process-minutes] minutes, tri-community facilitator's brunch, 4/23/17",
				[ 'month' => 4, 'day' => 23, 'year' => 2017 ]
			],
			[
				"[Process-minutes] minutes, process cmtee, 3/23/17",
				[ 'month' => 3, 'day' => 23, 'year' => 2017 ]
			],
			[
				"[Process-minutes] minutes, process cmtee, 4/20/17",
				[ 'month' => 4, 'day' => 20, 'year' => 2017 ]
			],
			[
				"[Process-minutes] minutes, process committee meeting, 8/12/18",
				[ 'month' => 8, 'day' => 12, 'year' => 2018 ]
			],
			[
				"[Process-minutes] Process minutes, 7/18/18",
				[ 'month' => 7, 'day' => 18, 'year' => 2018 ]
			],
			[
				"[Work-minutes] 07-15-2018 work minutes",
				[ 'month' => 7, 'day' => 15, 'year' => 2018 ]
			],
			[
				"[Work-minutes] 06-24-2018 work minutes",
				[ 'month' => 6, 'day' => 24, 'year' => 2018 ]
			],
			[
				"[Work-minutes] 05-27-2018 work minutes",
				[ 'month' => 5, 'day' => 27, 'year' => 2018 ]
			],
		];
	}
}
?>
