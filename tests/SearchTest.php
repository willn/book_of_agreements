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
	public function testRunSearchAgreements($search_q, $expected) {
		$_GET = ['q' => $search_q];
		$s = new Search();
		$s->parseGetVars();
		$sql = $s->createAgrQuery();
		$query_result = $s->searchAgreements($sql);
		$result = $s->renderResults($query_result);

		$this->assertEquals(remove_whitespace($expected), remove_whitespace($result));
	}

	public function provideRunSearchAgreements() {
		$egress_list = <<<EOHTML
 <div class="agreement"> 
<h2 class="agrm"> 2019-09-16 <a href="?id=agreement&amp;num=268">Basement Egress Proposal #1 - What's Allowed </a> [Great Oak Community] </h2> <div class="item_topic"> <div class="info">This proposal outlines the requirements for tenants to have a completely separate entrance/exit to their walkout apartment.</div> </div> </div> <div class="agreement"> 
<h2 class="agrm"> 2019-09-16 <a href="?id=agreement&amp;num=269">Basement Egress Proposal #2 - Building Center Stairs To Wetland and Connector Pathways</a> [Great Oak Community] </h2> <div class="item_topic"> <div class="info">Proposal to build a 3rd set of concrete stairs to the wetlands, along with 2 leveled gravel paths to the adjacent buildings, and pay for it with a loan from the Reserve.<br> </div> </div> </div> <div class="agreement"> 
<h2 class="agrm"> 2019-09-16 <a href="?id=agreement&amp;num=270">Basement Egress Proposal #3 - Creating Safe Pathways Behind Units 14-20</a> [Great Oak Community] </h2> <div class="item_topic"> <div class="info">This proposal creates gravel pathways behind units 14-20 and outlines the cost, maintenance, and job requirements for them.</div> </div> </div> <div class="agreement"> 
<h2 class="agrm"> 2002-04-14 <a href="?id=agreement&amp;num=120">Master Deed</a> [Finance &amp; Legal] </h2> <div class="item_topic"> <div class="info">This is the legal document which defines what you own, and how we're comprised as a legal entity. This document contains sections such as: "Definitions", "Title of Project", "Nature of Project", "Common Elements", "Unit Description and Percentage of Value", "Rights of Mortgagees", "Damage to Project", "Easements", "Future Access and Utility Easements", "Future Easements, Licenses and Rights-of-Way", "Amendment or Termination", "Assignment"</div> </div> </div>
EOHTML;

		$fence_list = <<<EOHTML
 <div class="agreement"> 
<h2 class="agrm"> 2003-06-30 <a href="?id=agreement&amp;num=90">GO Fence Policy</a> [Grounds] </h2> <div class="item_topic"> <div class="info">The GO fence policy allows backyard fences up to 6 feet high and front yard decorative fences up to 1.5 feet high. The policy aims to balance the community's desire for privacy and pet containment with the preservation of Great Oak's aesthetics and open space.</div> </div> </div> <div class="agreement"> 
<h2 class="agrm"> 2011-11-21 <a href="?id=agreement&amp;num=209">Proposal to Include Great Oak Garden in Annual Operating Budget</a> [Great Oak Community] </h2> <div class="item_topic"> <div class="info">The garden is considered a shared resource and specific expenses will be included in the Great Oak operating budget for Grounds, including woodchips, replacement hoses and sprinklers, a water meter, and a larger diameter water line to be installed in the center.</div> </div> </div> <div class="agreement"> 
<h2 class="agrm"> 2007-10-15 <a href="?id=agreement&amp;num=180">Hot Tub Trial Policies</a> [Common House: Hot Tub] </h2> <div class="item_topic"> <div class="info">The Common House Committee has issued a set of policies for a 6-month trial period that covers expenses, safety, health, reservations, respect and manners, and usage, and will be re-evaluated after 6 months.</div> </div> </div> <div class="agreement"> 
<h2 class="agrm"> 2022-05-28 <a href="?id=agreement&amp;num=289">Hot Tub Policies</a> [Common House: Hot Tub] </h2> <div class="item_topic"> <div class="info">This document outlines the policies for the usage of the Great Oak hot tub, including safety, health, reservations, respect, and manners. Policies include the need for adult supervision of children, showering before use, no food or glass containers, and a reservation system.</div> </div> </div> <div class="agreement"> 
<h2 class="agrm"> 2004-08-16 <a href="?id=agreement&amp;num=116">Proposal For Usage of Common Land Adjacent To Our LCEs</a> [Grounds] </h2> <div class="item_topic"> <div class="info">Proposal For Usage of Common Land Adjacent To Our LCEs</div> </div> </div> <div class="agreement"> 
<h2 class="agrm"> 2003-03-03 <a href="?id=agreement&amp;num=98">Pet Policy (minus the Cat section)</a> [Great Oak Community] </h2> <div class="item_topic"> <div class="info">The policy aims to create a harmonious environment for people, pets, and nature. It addresses various types of pets including dogs and other outdoor pets, as well as concerns around safety, cleanliness, damage, and noise. Pet owners are responsible for their pets' actions and damages. The "Cat Clause" covers cats and is a separate policy.</div> </div> </div> <div class="agreement"> 
<h2 class="agrm"> 2001-09-17 <a href="?id=agreement&amp;num=46">Proposal Template proposal</a> [Process] </h2> <div class="item_topic"> <div class="info">Proposals should be drafted according to a specific template which provides for certain categories of information.</div> </div> </div> <div class="agreement"> 
<h2 class="agrm"> 2003-08-06 <a href="?id=agreement&amp;num=99">Interim Architectural Review Committee (ARC)</a> [Buildings: ARC] </h2> <div class="item_topic"> <div class="info">Creation of a committee to review changes that members would like to make, especially to their units.</div> </div> </div> <div class="agreement"> 
<h2 class="agrm"> 2004-06-02 <a href="?id=agreement&amp;num=115">Planting Trees and Shrubs within Limited Common Elements (LCEs)</a> [Grounds] </h2> <div class="item_topic"> <div class="info">Households are free to plant trees and shrubs within their LCEs, which have an expected maximum growth height of 20 feet high or less without approval from the grounds committee, provided they follow specific guidelines. The proposal aims to address issues related to tree planting, which can cause conflicts among neighbors and damage to in-ground infrastructure.</div> </div> </div> <div class="agreement"> 
<h2 class="agrm"> 2004-04-19 <a href="?id=agreement&amp;num=108">Changes to the Great Oak Landscape: A Proposal to Empower the Grounds Cmtee</a> [Grounds] </h2> <div class="item_topic"> <div class="info">The Grounds Committee is empowered to propose, review, revise and approve changes to the Great Oak landscape.</div> </div> </div>
EOHTML;

		return [
			['egress', $egress_list],
			['fence', $fence_list],
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
