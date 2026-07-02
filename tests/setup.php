<?php
require_once __DIR__ . '/../public/config.php';

use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		$HDUP = get_hdup();
		$conn = @new mysqli($HDUP['host'], $HDUP['user'], $HDUP['password'],
			$HDUP['database']);

		if ($conn->connect_errno) {
			self::fail(
				"MySQL is unavailable. Start it with:\n" .
				"brew services start mysql"
			);
		}
	}
}
