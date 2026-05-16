<?php

use PHPUnit\Framework\TestCase;

$root = dirname(__DIR__);
set_include_path(
    get_include_path()
    . PATH_SEPARATOR . $root . '/public'
    . PATH_SEPARATOR . $root . '/public/logic'
);
require_once $root . '/public/logic/class_agreement.php';

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

class StubDate {
    private $date = '2024-01-01';

    public function setDate($d) {
        $this->date = $d;
    }

    public function toString() {
        return $this->date;
    }
}

/**
 * Testable subclass that bypasses request processing
 */
class TestAgreement extends Agreement {
    public function __construct() {
        // bypass parent constructor behavior
        $this->cmty = new StubCommittee();
        $this->Date = new StubDate();
    }

    public function processRequest() {
        // disable request handling
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
}
