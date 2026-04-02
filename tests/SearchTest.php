<?php
use PHPUnit\Framework\TestCase;

set_include_path('../' . PATH_SEPARATOR . '../public/');
require_once '../public/constants.php';
require_once '../public/logic/class_search.php';
require_once 'testing_utils.php';

class SearchTest extends TestCase {

	public function testParseGetVarsSetsProperties()
	{
		$search = new Search();

		$_GET = [
			'cmty' => '5',
			'show_docs' => 'minutes',
			'q' => 'Budget',
			'include_expired' => 'on'
		];
		$search->parseGetVars();

		$vals = $search->getCoreValues();
		$this->assertEquals(5, $vals['cmty_num']);
		$this->assertEquals('minutes', $vals['show_docs']);
		$this->assertEquals('budget', $vals['q']);
		$this->assertTrue($vals['include_expired']);
	}

	public function testInvalidDocTypeFallsBackToDefault()
	{
		$_GET = ['show_docs' => 'invalid'];
		$s = new Search();
		$s->parseGetVars();
		$vals = $s->getCoreValues();
		$this->assertEquals('agreements', $vals['show_docs']);
	}

	public function testAgainstClauseEscapesInput()
	{
		$s = new Search();
		$s->setTerms("O'Reilly");
		$clause = $s->getAgainstClause();
		$this->assertStringContainsString("against('o\\'reilly')", $clause);
	}

	public function testCreateAgrQueryIncludesCorePieces()
	{
		$_GET = ['q' => 'budget'];
		$s = new Search();
		$s->parseGetVars();
		$sql = $s->createAgrQuery();

		$this->assertStringContainsString('FROM agreements', $sql);
		$this->assertStringContainsString('match(', $sql);
		$this->assertStringContainsString('against', $sql);
		$this->assertStringContainsString('ORDER BY score DESC', $sql);
	}

	public function testCreateAgrQueryIncludesCommitteeFilter()
	{
		$_GET = ['cmty' => '3'];

		$s = new Search();
		$s->parseGetVars();
		$sql = $s->createAgrQuery();
		$this->assertStringContainsString("cid='3'", $sql);
	}

	public function testExpiredClauseOnlyWhenIncludeExpiredTrue()
	{
		$_GET = ['include_expired' => 'on'];
		$s = new Search();
		$s->parseGetVars();
		$sql = $s->createAgrQuery();
		$this->assertStringNotContainsString('expired=', $sql);
	}

	public function testNoExpiredClauseWhenGetEmpty()
	{
		$_GET = [];
		$s = new Search();
		$s->parseGetVars();
		$sql = $s->createAgrQuery();
		$this->assertStringContainsString('expired=0', $sql);
	}

	/**
	 * @dataProvider provideCreateAgrQuerySql
	 */
	public function testCreateAgrQuerySql($get_vars, $expected) {
		$s = new Search();
		$_GET = $get_vars;
		$s->parseGetVars();
		$sql = $s->createAgrQuery();
		$this->assertEquals($expected, remove_whitespace($sql));
	}

	public function provideCreateAgrQuerySql() {
		$parking = <<<EOSQL
 SELECT agreements.*, c.cmty, GROUP_CONCAT(DISTINCT t.tag ORDER BY t.tag SEPARATOR ', ') AS tags, match(title, summary, full, background, comments, processnotes) against('parking') AS score FROM agreements JOIN committees c ON c.cid = agreements.cid LEFT JOIN tags_to_agreements tta ON tta.agreement_id = agreements.id LEFT JOIN tags t ON t.id = tta.tag_id WHERE (date>="2000-12-31" and date<="2007-07-01" and match(title, summary, full, background, comments, processnotes) against('parking') and expired=0) GROUP BY agreements.id ORDER BY score DESC;
EOSQL;

		$vendor = <<<EOSQL
 SELECT agreements.*, c.cmty, GROUP_CONCAT(DISTINCT t.tag ORDER BY t.tag SEPARATOR ', ') AS tags, match(title, summary, full, background, comments, processnotes) against('trusted vendor') AS score FROM agreements JOIN committees c ON c.cid = agreements.cid LEFT JOIN tags_to_agreements tta ON tta.agreement_id = agreements.id LEFT JOIN tags t ON t.id = tta.tag_id WHERE (date>="2018-02-28" and date<="2018-07-01" and match(title, summary, full, background, comments, processnotes) against('trusted vendor') and expired=0) GROUP BY agreements.id ORDER BY score DESC;
EOSQL;

		return [
			[
				[
					'q' => 'parking',
					'endyear' => 2007,
					'endmonth' => 6,
				],
				$parking
			],
			[
				[
					'q' => 'trusted vendor',
					'startyear' => 2018,
					'startmonth' => 3,
					'endyear' => 2018,
					'endmonth' => 6,
				],
				$vendor
			],
		];
	}

	public function testMockRunSearchesAgreementsOnly()
	{
		$s = $this->getMockBuilder(Search::class)
			->onlyMethods(['searchAgreements', 'createAgrQuery'])
			->getMock();
		$s->setDocType('agreements');

		$s->method('createAgrQuery')->willReturn('SQL');
		$s->method('searchAgreements')->willReturn(['a']);
		$result = $s->runSearches();
		$this->assertEquals(['a'], $result);
	}

	/**
	 * @dataProvider provideRunSearchAgreements
	 */
	public function testRunSearchAgreements($get_vars, $count, $expected) {

		$s = new Search();
		$_GET = $get_vars;
		$s->parseGetVars();
		$sql = $s->createAgrQuery();
		$query_result = $s->searchAgreements($sql);
		$result = $s->renderResults($query_result);

		$this->assertEquals(count($query_result), $count);
		$this->assertEquals(remove_whitespace($expected), remove_whitespace($result));
	}

	public function provideRunSearchAgreements() {
		$fence_list = <<<EOHTML
 <div class="agreement"> 
<h2 class="agrm"> 2007-10-15 <a href="?id=agreement&amp;num=180">Hot Tub Trial Policies</a> [Common House: Hot Tub] </h2> <div class="item_topic"> <div class="info">The Common House Committee has issued a set of policies for a 6-month trial period that covers expenses, safety, health, reservations, respect and manners, and usage, and will be re-evaluated after 6 months.</div> </div> </div>
EOHTML;

		$garden_list = <<<EOHTML
 <div class="agreement"> <h2 class="agrm"> 2011-11-21 <a href="?id=agreement&amp;num=209">Proposal to Include Great Oak Garden in Annual Operating Budget</a> [Great Oak Community] </h2> <div class="item_topic"> <div class="info">The garden is considered a shared resource and specific expenses will be included in the Great Oak operating budget for Grounds, including woodchips, replacement hoses and sprinklers, a water meter, and a larger diameter water line to be installed in the center.</div> <div class="tags">Tags: <span class="tag_entry">budget</span> </div> </div> </div>
EOHTML;

		$parking_list = <<<EOHTML
 <div class="agreement"> <h2 class="agrm"> 2006-06-03 <a href="?id=agreement&amp;num=156">Scooter parking areas</a> [Common House] </h2> <div class="item_topic"> <div class="info">Great Oak will establish 1-3 parking areas for scooters at CH entrances out of the way of doors and walkways. Implementation will be handled by the Common House committee.</div> </div> </div> <div class="agreement"> <h2 class="agrm"> 2006-08-02 <a href="?id=agreement&amp;num=164">Parking Agreement</a> [Grounds] </h2> <div class="item_topic"> <div class="info">To live with limited parking spaces, these guidelines cover removing dead vehicles, storing little-used vehicles off-site, limiting parking of trailers, using garages solely for parking vehicles, and parking only in designated spaces.</div> </div> </div>
EOHTML;

		$effect_list = <<<EOHTML
 <div class="agreement"> <h2 class="agrm"> 2026-01-21 <a href="?id=agreement&amp;num=320">Committee Effectiveness Agreement with Convenor and Member Responsibilities</a> [Process] </h2> <div class="item_topic"> <div class="info">A revised Committee Effectiveness agreement to strengthen the Great Oak committee system by giving specific direction to committees on how to effectively function and fulfill their mandates. This is intended to institute best practices for committees to follow in three major areas, namely Committee Mandates, Roles and Responsibilities, and Questions, Concerns, and Controversial Decisions. Section A (Mandates) contains updated content; the rest of the agreement dates from 2019.</div> </div> </div>
EOHTML;

		$five_fall = <<<EOSQL
 <div class="agreement"> <h2 class="agrm"> 2012-06-18 <a href="?id=agreement&amp;num=213">Agreement to lengthen the five fall budget community meetings</a> [Process] </h2> <div class="item_topic"> <div class="info">The proposal suggests pre-approving five community meetings a year, when the annual budget is discussed, to extend up to 120 minutes instead of 90 minutes, from 6:30 to 8:30 pm, when needed. This is intended to allow for more in-depth discussions, finishing the budget in fewer meetings, and reducing tension around rushed agenda items.</div> <div class="tags">Tags: <span class="tag_entry">budget</span> <span class="tag_entry">meeting-format</span> </div> </div> </div>
EOSQL;

		return [
			[
				[
					'q' => 'fence',
					'startyear' => 2006,
					'startmonth' => 1,
					'endyear' => 2007,
					'endmonth' => 12,
					'cmty' => 0,
				],
				1,
				$fence_list
			],
			[
				[
					'q' => 'garden',
					'startyear' => 2011,
					'startmonth' => 1,
					'endyear' => 2011,
					'endmonth' => 12,
					'cmty' => 0,
				],
				1,
				$garden_list
			],
			[
				[
					'q' => 'parking',
					'startyear' => 2006,
					'startmonth' => 1,
					'endyear' => 2007,
					'endmonth' => 12,
					'cmty' => 0,
				],
				2,
				$parking_list
			],
			[
				[
					'q' => 'Effectiveness',
					'startyear' => 2022,
					'startmonth' => 1,
					'endyear' => 2026,
					'endmonth' => 12,
					'cmty' => 9,
				],
				1,
				$effect_list
			],
			[
				[
					'q' => 'five fall budget community meetings ',
					'startyear' => 2012,
					'startmonth' => 6,
					'endyear' => 2012,
					'endmonth' => 6,
					'cmty' => 9,
				],
				1,
				$five_fall
			],
		];
	}


	public function testRunSearchesMinutesOnly()
	{
		$s = $this->getMockBuilder(Search::class)
			->onlyMethods(['searchMinutes', 'createMinsQuery'])
			->getMock();
		$s->setDocType('minutes');

		$s->method('createMinsQuery')->willReturn('SQL');
		$s->method('searchMinutes')->willReturn(['m']);
		$result = $s->runSearches();
		$this->assertEquals(['m'], $result);
	}


}
?>
