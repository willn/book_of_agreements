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

	public function testCreateAgrQuerySql() {
		$s = new Search();
		$sql = $s->createAgrQuery();

		$today = date('Y-m-d');
		$expected = <<<EOSQL
 SELECT agreements.*, committees.cmty, match(title, summary, full, background, comments, processnotes) against('') AS score FROM agreements JOIN committees ON committees.cid = agreements.cid WHERE (date>="2000-12-31" and date<="{$today}" and match(title, summary, full, background, comments, processnotes) against('') and expired=0) ORDER BY score DESC;
EOSQL;

		$this->assertEquals($expected, remove_whitespace($sql));
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
	public function testRunSearchAgreements($search_q, $start_year, $end_year, $cmty, $count, $expected) {
		$_GET = [
			'q' => $search_q,
			'startyear' => $start_year,
			'startmonth' => 1,
			'endyear' => $end_year,
			'endmonth' => 12,
			'cmty' => $cmty,
		];
		$s = new Search();
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
 <div class="agreement"> <h2 class="agrm"> 2011-11-21 <a href="?id=agreement&amp;num=209">Proposal to Include Great Oak Garden in Annual Operating Budget</a> [Great Oak Community] </h2> <div class="item_topic"> <div class="info">The garden is considered a shared resource and specific expenses will be included in the Great Oak operating budget for Grounds, including woodchips, replacement hoses and sprinklers, a water meter, and a larger diameter water line to be installed in the center.</div> </div> </div>
EOHTML;

		$parking_list = <<<EOHTML
 <div class="agreement"> <h2 class="agrm"> 2006-06-03 <a href="?id=agreement&amp;num=156">Scooter parking areas</a> [Common House] </h2> <div class="item_topic"> <div class="info">Great Oak will establish 1-3 parking areas for scooters at CH entrances out of the way of doors and walkways. Implementation will be handled by the Common House committee.</div> </div> </div> <div class="agreement"> <h2 class="agrm"> 2006-08-02 <a href="?id=agreement&amp;num=164">Parking Agreement</a> [Grounds] </h2> <div class="item_topic"> <div class="info">To live with limited parking spaces, these guidelines cover removing dead vehicles, storing little-used vehicles off-site, limiting parking of trailers, using garages solely for parking vehicles, and parking only in designated spaces.</div> </div> </div>
EOHTML;

		$effect_list = <<<EOHTML
 <div class="agreement"> <h2 class="agrm"> 2026-01-21 <a href="?id=agreement&amp;num=320">Committee Effectiveness Agreement with Convenor and Member Responsibilities</a> [Process] </h2> <div class="item_topic"> <div class="info">A revised Committee Effectiveness agreement to strengthen the Great Oak committee system by giving specific direction to committees on how to effectively function and fulfill their mandates. This is intended to institute best practices for committees to follow in three major areas, namely Committee Mandates, Roles and Responsibilities, and Questions, Concerns, and Controversial Decisions. Section A (Mandates) contains updated content; the rest of the agreement dates from 2019.</div> </div> </div>
EOHTML;

		return [
			['fence', 2006, 2007, 0, 1, $fence_list],
			['garden', 2011, 2011, 0, 1, $garden_list],
			['parking', 2006, 2007, 0, 2, $parking_list],
			['Effectiveness', 2022, 2026, 9, 1, $effect_list],
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
