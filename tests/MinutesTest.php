<?php

use PHPUnit\Framework\TestCase;

$root = dirname(__DIR__);
set_include_path(
    get_include_path()
    . PATH_SEPARATOR . $root . '/public'
    . PATH_SEPARATOR . $root . '/public/logic'
);
require_once $root . '/public/logic/class_minute.php';

class MinutesTest extends TestCase
{
    /**
     * Verify constructor assigns basic properties.
     */
    public function testConstructorSetsProperties()
    {
        $minutes = new Minutes(
            5,
            "<b>notes</b>",
            "<i>agenda</i>",
            "<p>content</p>",
            2,
            "2024-05-01"
        );

        $this->assertEquals(5, $minutes->id);
        $this->assertEquals(2, $minutes->cid);
        $this->assertInstanceOf(MyDate::class, $minutes->Date);
    }

    /**
     * Verify that a provided date initializes the Date object.
     */
    public function testDateIsAssigned()
    {
        $minutes = new Minutes(
            1,
            '',
            '',
            '',
            '',
            '2023-10-10'
        );

        $this->assertNotNull($minutes->Date);
    }

    /**
     * If content is provided, constructor should not overwrite it.
     */
    public function testConstructorDoesNotAutoLoadIfContentProvided()
    {
        $minutes = new Minutes(
            5,
            '',
            '',
            'content already present'
        );

        $this->assertEquals('content already present', $minutes->content);
    }

    /**
     * Verify default object state when constructed empty.
     */
    public function testDefaultState()
    {
        $minutes = new Minutes();

        $this->assertEquals('', $minutes->id, 'zero ID');
        $this->assertEquals('', $minutes->notes,
			print_r(['notes' => $minutes->notes], TRUE));
        $this->assertEquals('', $minutes->agenda, 'agenda');
    }

    /**
     * Ensure committee ID is assigned correctly.
     */
    public function testCommitteeIdIsAssigned()
    {
        $minutes = new Minutes(
            1,
            '',
            '',
            '',
            42
        );

        $this->assertEquals(42, $minutes->cid);
    }

    /**
     * loadById should fail gracefully if record is missing.
     * This test mainly ensures no fatal error occurs.
	 */
    public function testLoadByIdHandlesMissingRecord()
    {
        $minutes = new Minutes();

        $minutes->loadById(0);

        $this->assertTrue(true);
    }

    /**
     * Ensure display() works even with empty fields.
	*/
    public function testDisplayWithEmptyFields()
    {
        $minutes = new Minutes();
        $output = $minutes->display();
        $this->assertNotEmpty($output);
    }
}
