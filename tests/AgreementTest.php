<?php

use PHPUnit\Framework\TestCase;

$root = dirname(__DIR__);
set_include_path(get_include_path() .
	PATH_SEPARATOR . $root . '/public' .
	PATH_SEPARATOR . $root . '/public/logic');
require_once $root . '/public/logic/class_agreement.php';
require_once 'class_mydate.php';

/**
 * Simple stub replacements for dependencies
 */
class StubCommittee {
    private $name = 'Test Committee';
    public function setId($id) {}
    public function getName() {
        return $this->name;
    }
}

/**
 * Testable subclass that bypasses request processing
 */
class TestAgreement extends Agreement {
    public function processRequest() {
        // disable request handling
    }

    public function adminActions()
    {
        return '<div class="admin-actions">ADMIN</div>';
    }

    public function renderTags()
    {
        return '<div class="tags">TAGS</div>';
    }

    public function displayPreviousVersions()
    {
        return '<div class="previous-versions">PREVIOUS</div>';
    }

    public function getRelatedMinutes()
    {
        return '<div class="related-minutes">MINUTES</div>';
    }

    public function actionChoices()
    {
        return '<div class="action-choices">ACTIONS</div>';
    }
}

class AgreementTest extends TestCase
{

    public function testSetAndGetId()
    {
        $a = new TestAgreement();
        $a->setId(42);
        $this->assertSame(42, $a->getId());
    }

	public function testDefaultsAreEmptyStrings()
	{
		$a = new TestAgreement();
		$this->assertSame('', $a->title);
		$this->assertSame('', $a->full);
	}

    public function testSetContentAssignsFields()
    {
        $a = new TestAgreement();

        $a->setContent(
            "Title",
            "Summary",
            "Full text",
            "Background",
            "Comments",
            "Process notes",
            5,
            "2024-02-01",
            0,
            true
        );

        $this->assertEquals("Title", $a->title);
        $this->assertEquals("Summary", $a->summary);
        $this->assertEquals("Full text", $a->full);
        $this->assertEquals("Background", $a->background);
        $this->assertEquals("Comments", $a->comments);
        $this->assertEquals("Process notes", $a->processnotes);
        $this->assertEquals(5, $a->cid);
        $this->assertTrue($a->world_public);
    }

    public function testSetContentNormalizesLineEndings()
    {
        $a = new TestAgreement();

        $text = "line1\r\nline2\rline3\nline4";

        $a->setContent("Title", "", $text);

        $this->assertEquals(
            "line1\nline2\nline3\nline4",
            $a->full
        );
    }

	/**
	 * @dataProvider provideValidateFields
	 */
    public function testValidateFields($title, $full, $diff_comments,
		$id, $expected)
    {
        $a = new TestAgreement();
        $errs = $a->validateFields($title, $full, $diff_comments, $id);
		$this->assertEquals($expected, $errs);
    }

	public function provideValidateFields() {
		return [
			# all pass
			['title', 'full', 'diff comments', 123, []],

			# missing one key at a time
			['', 'full', 'diff comments', 124, ['title']],
			['title', '', 'diff comments', 125, ['full']],
			['title', 'full', '', 126, ['diff_comments']],

			# all are missing when adding
			['', '', '', 0, ['title', 'full']],

			# missing all when editing an existing agreement
			['', '', '', 1, ['title', 'full', 'diff_comments']],
		];
	}

    public function testGetTextVersionIncludesSections()
    {
        $a = new TestAgreement();

        $a->setContent(
            "Test Title",
            "Short summary",
            "Full proposal text",
            "Background text",
            "Some comments",
            "Process notes"
        );

        $text = $a->getTextVersion();

        $this->assertStringContainsString("Title: Test Title", $text);
        $this->assertStringContainsString("Summary:", $text);
        $this->assertStringContainsString("Background:", $text);
        $this->assertStringContainsString("Proposal:", $text);
        $this->assertStringContainsString("Comments:", $text);
        $this->assertStringContainsString("Process Comments:", $text);
    }

	public function testNormalizeNewlinesHandlesAllFormats()
	{
		$a = new TestAgreement();
		$input = "line1\r\nline2\rline3\nline4";
		$result = $a->normalizeNewlines($input);
		$this->assertEquals("line1\nline2\nline3\nline4", $result);
	}

	public function testRenderFormForNewAgreement()
	{
		$agreement = new TestAgreement();
		$agreement->id = 0;
		$agreement->title = 'Test Title';
		$agreement->full = 'Proposal text';

		$html = $agreement->renderDisplay('form');

		$this->assertStringContainsString('<h1>Add Agreement</h1>', $html);
		$this->assertStringNotContainsString('name="update"', $html);
		$this->assertStringNotContainsString('Diff comments:', $html);
	}

	public function testRenderFormForExistingAgreement()
	{
		$agreement = new TestAgreement();
		$agreement->id = 42;
		$agreement->title = 'Test Title';
		$agreement->full = 'Proposal text';

		$html = $agreement->renderDisplay('form');

		$this->assertStringContainsString('<h1>Edit Agreement</h1>', $html);
		$this->assertStringContainsString('name="update"', $html);
		$this->assertStringContainsString('Diff comments:', $html);
	}

	public function testRenderFormShowsTitleErrorClass()
	{
		$agreement = new TestAgreement();
		$html = $agreement->renderDisplay('form', ['title']);
		$this->assertStringContainsString( '<label class="err">', $html);
	}

	public function testRenderFormShowsFullErrorClass()
	{
		$agreement = new TestAgreement();
		$html = $agreement->renderDisplay('form', ['full']);
		$this->assertStringContainsString(
			'<span>Proposal: *</span>',
			$html
		);

		$this->assertStringContainsString(
			'label class="err"',
			$html
		);
	}

	public function testRenderFormEscapesHtmlInFields()
	{
		$agreement = new TestAgreement();
		$agreement->title = '<script>alert(1)</script>';
		$html = $agreement->renderDisplay('form');

		$this->assertStringNotContainsString('<script>', $html);
		$this->assertStringContainsString('&lt;script&gt;', $html);
	}

	public function testSearchDisplayUsesFoundSnippet()
	{
		$agreement = new TestAgreement();
		$agreement->id = 1;
		$agreement->title = 'Title';
		$agreement->found = 'MATCHED TEXT';
		$agreement->found_summary = true;

		$html = $agreement->renderDisplay('search');

		$this->assertStringContainsString('MATCHED TEXT', $html);
	}

	public function testSearchDisplayAppendsSummaryWhenFoundSummaryFalse()
	{
		$agreement = new TestAgreement();
		$agreement->summary = 'Summary text';
		$agreement->found = 'Matched';
		$agreement->found_summary = false;

		$html = $agreement->renderDisplay('search');

		$this->assertStringContainsString('SUMMARY:', $html);
		$this->assertStringContainsString('Summary text', $html);
	}

	public function testSearchDisplayUsesSummaryFallback()
	{
		$agreement = new TestAgreement();
		$agreement->summary = 'Summary fallback';

		$html = $agreement->renderDisplay('search');

		$this->assertStringContainsString('Summary fallback', $html);
	}

	public function testSearchDisplayFallsBackToTruncatedFullText()
	{
		$agreement = new TestAgreement();

		$agreement->summary = '';
		$agreement->full = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		$html = $agreement->renderDisplay('search');

		$expected = substr(format_html($agreement->full), 0,
			SUB_SUMMARY_LENGTH) . '...';
		$this->assertStringContainsString($expected, $html);
	}

	public function testSearchDisplayShowsExpiredNotice()
	{
		$agreement = new TestAgreement();
		$agreement->expired = 1;

		$html = $agreement->renderDisplay('search');

		$this->assertStringContainsString(
			'Agreement Expired',
			$html
		);
	}

	public function testDocumentDisplayRendersAllSections()
	{
		$agreement = new TestAgreement();

		$agreement->title = 'Agreement Title';
		$agreement->summary = 'Summary';
		$agreement->background = 'Background';
		$agreement->full = 'Proposal';
		$agreement->comments = 'Comments';
		$agreement->processnotes = 'Process Notes';

		$html = $agreement->renderDisplay('document');

		$this->assertStringContainsString('Summary:', $html);
		$this->assertStringContainsString('Background:', $html);
		$this->assertStringContainsString('Proposal:', $html);
		$this->assertStringContainsString('Comments:', $html);
		$this->assertStringContainsString('Process Comments:', $html);
	}

	public function testDocumentDisplayOmitsEmptySections()
	{
		$agreement = new TestAgreement();
		$agreement->full = 'Proposal only';

		$html = $agreement->renderDisplay('document');

		$this->assertStringNotContainsString('Summary:', $html);
		$this->assertStringNotContainsString('Background:', $html);
		$this->assertStringContainsString('Proposal:', $html);
	}

	public function testDocumentDisplayIncludesRelatedBlocks()
	{
		$agreement = new TestAgreement();

		$html = $agreement->renderDisplay('document');

		$this->assertStringContainsString('PREVIOUS', $html);
		$this->assertStringContainsString('MINUTES', $html);
		$this->assertStringContainsString('ADMIN', $html);
		$this->assertStringContainsString('TAGS', $html);
	}

	public function testDocumentDisplayIncludesPrintLink()
	{
		$agreement = new TestAgreement();

		$html = $agreement->renderDisplay('document');

		$this->assertStringContainsString(
			'id="print_document"',
			$html
		);
	}

	/**
	 * @dataProvider displayTypeProvider
	 */
	public function testRenderDisplayReturnsNonEmptyHtml($type)
	{
		$agreement = new TestAgreement();
		$agreement->title = 'Title';
		$agreement->full = 'Full';

		$html = $agreement->renderDisplay($type);

		$this->assertIsString($html);
		$this->assertNotEmpty(trim($html));
	}

	public static function displayTypeProvider()
	{
		return [
			['form'],
			['search'],
			['document'],
		];
	}
}
