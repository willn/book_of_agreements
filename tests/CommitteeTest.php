<?php
use PHPUnit\Framework\TestCase;

set_include_path('../' . PATH_SEPARATOR . '../public/');
require_once '../public/constants.php';
require_once '../public/logic/committee.php';
require_once 'testing_utils.php';
require_once 'logic/utils.php';
require_once 'setup.php';

class CommitteeTest extends DatabaseTestCase {
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
	public function testGetSelectCommittee($expected) {
		$this->committee->setId(4);
		$result = $this->committee->getSelectCommittee();
		$this->assertEquals(remove_html_whitespace($result),
			remove_html_whitespace($expected));
	}

	public function provideGetSelectCommittee() {
		$html = <<<EOHTML
<label><span>Committee:</span><select name="cid" size="1"><option value="14">Great Oak Community</option><option value="1">Buildings</option><option value="3">Common House</option><option value="5">Finance &amp; Legal</option><option value="102">Finance &amp; Legal:Budget</option><option value="102">Finance &amp; Legal: Budget</option><option value="6">Grounds</option><option value="7">Meals</option><option value="8">Membership</option><option value="9">Process</option><option value="108">Process:Infoco</option><option value="108">Process: Infoco</option><option value="10">Steering</option><option value="12">Work</option><option value="13">Workshop</option></select></label>
EOHTML;

		return [
			[$html],
		];
	}
}
?>
