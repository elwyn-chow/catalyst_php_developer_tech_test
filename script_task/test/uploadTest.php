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
			'/Database connected/',
			$output	
		);
	}

	// Check that database table was created
	public function testDatabaseTableCreated(): void {
		$ini_array = parse_ini_file("database.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];
		$database = $ini_array["correctdb"]["database"];

		$db = new mysqli(
			$host,
			$user,
			$password,
			$database
		);

		// Delete table if it exists
		$result = $db->query("drop table user"); 

		// Check that table doesn't exist
		// Code copied from https://stackoverflow.com/questions/6432178/how-can-i-check-if-a-mysql-table_exists-with-php
		$result = $db->query("select 1 from user LIMIT 1"); 
		if ($result !== FALSE ) {
			// user table should not exist
			$this->assertTrue(false);	
			return;
		} else {
			// User table does not exist: pass test
			$this->assertTrue(true);	
		}

		// Create table
		$output = `php php/user_upload.php -u $user -p$password -h$host --create_table 2>&1`;


		$result = $db->query("select 1 from user LIMIT 1"); 
		if ($result !== FALSE ) {
			// user table should exist
			$this->assertTrue(true);	
			return;
		} else {
			// User table does not exist: fail test
			$this->assertTrue(false);	
		}
	}
}
