<?php
use PHPUnit\Framework\Testcase;

final class uploadTest extends TestCase {
	// Test to see help message
	public function testHelp(): void {
		$output = `php php/user_upload.php --help`;

		// Don't compare the whole output. Just look for key string
		$this->assertRegExp(
			'/output the above list of directives with details/',
			$output	
		);
	}

	// Test to see if database user was entered
	public function testMissingDBUser(): void {
		$output = `php php/user_upload.php 2>&1`;

		$this->assertEquals(
			"Exiting... must set the username option\n",
			$output
		);
	}

	// Test to see if database password was entered
	public function testMissingDBPassword(): void {
		$output = `php php/user_upload.php -u testuser 2>&1`;

		$this->assertEquals(
			"Exiting... must set the password option\n",
			$output
		);
	}

	// Test to see if database host was entered
	public function testMissingDBHost(): void {
		$output = `php php/user_upload.php -u testuser -p testpass 2>&1`;

		$this->assertEquals(
			"Exiting... must set the host option\n",
			$output
		);
	}

	// Test to see if incorrect database credentials are handled correctly 
	public function testWrongDatabaseCredentials(): void {
		$ini_array = parse_ini_file("database.ini");
		$user = $ini_array["incorrectdb"]["user"];
		$password = $ini_array["incorrectdb"]["password"];
		$host = $ini_array["incorrectdb"]["host"];

		$output = `php php/user_upload.php -u $user -p$password -h$host 2>&1`;

		$this->assertEquals(
			"Could not open connection to database: Access denied for user '$user'@'$host' (using password: YES)\n",
			$output
		);
	}

	// Test to see if correct database credentials are handled correctly 
	public function testCorrectDatabaseCredentials(): void {
		$ini_array = parse_ini_file("database.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];

		$output = `php php/user_upload.php -u $user -p$password -h$host 2>&1`;

		$this->assertRegExp(
			'/^Database connected/',
			$output	
		);
	}

}
