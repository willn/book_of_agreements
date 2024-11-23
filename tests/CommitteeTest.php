<?php
use PHPUnit\Framework\TestCase;

set_include_path('../' . PATH_SEPARATOR . '../public/');
require_once '../public/constants.php';
require_once '../public/logic/committee.php';
require_once 'testing_utils.php';

class CommitteeTest extends TestCase {
	private $committee;

	private $ex_top = [
		1 => "Buildings",
		2 => "CDC",
		4 => "Design"
	];

	private $ex_sub = [
		1 => [103 => "ARC"],
		4 => [101 => "Color"],
	];

	public function setUp() : void {
		$this->committee = new Committee(4);
	}

	/**
	 * @dataProvider provideGetSelectCommittee
	 */
	public function testGetSelectCommittee($cmtys, $subcmtys, $expected) {
		$this->committee->setId(4);
		$result = $this->committee->getSelectCommittee($cmtys, $subcmtys);
		$this->assertEquals(remove_html_whitespace($result),
			remove_html_whitespace($expected));
	}

	public function provideGetSelectCommittee() {
		$html = <<<EOHTML
		<label>
			<span>Committee:</span>
			<select name="cid" size="1">
				<option value="1">Buildings</option>
<option value="103">Buildings:ARC</option>
<option value="2">CDC</option>
<option value="4" selected="selected">Design</option>
<option value="101">Design:Color</option>
			</select>
		</label>
EOHTML;

		return [
			[[], [], '<label><span>Committee:</span><select name="cid" size="1"></select></label>'],
			[$this->ex_top, $this->ex_sub, $html],
		];
	}
}
?>
