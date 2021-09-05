<?php declare(strict_types=1);
// These tests check the connection to the database
use PHPUnit\Framework\Testcase;

final class uploadDatabaseTest extends TestCase {
	// Test to see if incorrect database credentials are handled correctly 
	public function testWrongDatabaseCredentials(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["incorrectdb"]["user"];
		$password = $ini_array["incorrectdb"]["password"];
		$host = $ini_array["incorrectdb"]["host"];

		$output = `php src/user_upload.php -u $user -p$password -h$host 2>&1`;

		$this->assertEquals(
			"Could not open connection to database: Access denied for user '$user'@'$host' (using password: YES)\n",
			$output
		);
	}

	// Test to see if correct database credentials are handled correctly 
	public function testCorrectDatabaseCredentials(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];

		$output = `php src/user_upload.php -u $user -p$password -h$host 2>&1`;

		$this->assertRegExp(
			'/Database connected/',
			$output	
		);
	}
}
