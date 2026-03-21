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

	public function testStartDateClauseValid()
	{
		$s = new Search();
		$clause = $s->getStartDateClause(2024, 3);
		$this->assertStringContainsString('date>=', $clause);
		$this->assertStringContainsString('2024-02-29', $clause);
	}

	public function testStartDateClauseMissingValues()
	{
		$s = new Search();
		$clause = $s->getStartDateClause(null, null);
		$this->assertEquals('', $clause);
	}

	public function testProcessDatesReturnsTwoClauses()
	{
		$_GET = [
			'startyear' => '2024',
			'startmonth' => '1',
			'endyear' => '2024',
			'endmonth' => '12',
		];
		$s = new Search();
		$clauses = $s->processDates();
		$this->assertCount(2, $clauses);
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

	public function testRunSearchesAgreementsOnly()
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
