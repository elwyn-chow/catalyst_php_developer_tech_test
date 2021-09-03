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
		$ini_array = parse_ini_file("test.ini");
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
		$ini_array = parse_ini_file("test.ini");
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
		$ini_array = parse_ini_file("test.ini");
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

	// Test to see if filename was entered
	public function testMissingFilename(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];

		$output = `php php/user_upload.php -u $user -p$password -h$host --file 2>&1`;

		$this->assertRegExp(
			'/Exiting\.\.\. must set the filename option/',
			$output	
		);
	}

	// Test to see if script opens a valid CSV users file
	public function testCSVFileValid(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];
		$csv = $ini_array["csv"]["valid"];

		$output = `php php/user_upload.php -u $user -p$password -h$host --file $csv 2>&1`;

		$this->assertRegExp(
			'/Opening CSV file/',
			$output	
		);
	}

	// Test to see if script tries to open a CSV users file that doesn't exist
	public function testCSVFileDoesntExist(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];
		$csv = $ini_array["csv"]["doesntexist"];

		$output = `php php/user_upload.php -u $user -p$password -h$host --file $csv 2>&1`;

		$this->assertRegExp(
			"/CSV file .+ does not exist/",
			$output	
		);
	}

	// Test to see if script tries to open a non-readable CSV users file
	public function testCSVFileUnreadable(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];
		$csv = $ini_array["csv"]["unreadable"];

		// Make the file unreadable (to commit it into git, 
		// it had to be readable)
		chmod($csv, 0000); // Make file unreadable

		$output = `php php/user_upload.php -u $user -p$password -h$host --file $csv 2>&1`;

		$this->assertRegExp(
			"/CSV file .+ is not readable/",
			$output	
		);
	}

	// Test to see if script notices too many columns in CSV users file
	public function testCSVFileTooManyColumns(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];
		$csv = $ini_array["csv"]["too_many_columns"];

		$output = `php php/user_upload.php -u $user -p$password -h$host --file $csv 2>&1`;

		$this->assertRegExp(
			"/WARNING: While parsing the line number 1, there were 4 fields when 3 were expected. Skipping this row.../",
			$output	
		);
	}

	// Test to see if script notices too few columns in CSV users file
	public function testCSVFileTooFewColumns(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];
		$csv = $ini_array["csv"]["too_few_columns"];

		$output = `php php/user_upload.php -u $user -p$password -h$host --file $csv 2>&1`;

		$this->assertRegExp(
			"/WARNING: While parsing the line number \d+, there were 2 fields when 3 were expected. Skipping this row.../",
			$output	
		);
	}


	// Test to see if script notices invalid email address in CSV users file
	public function testCSVFileInvalidEmailAddress(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];
		$csv = $ini_array["csv"]["invalid_email_address"];

		$output = `php php/user_upload.php -u $user -p$password -h$host --file $csv 2>&1`;

		$this->assertRegExp(
			"/WARNING: While parsing the line number \d+, the email address .+ value is invalid. Skipping this row.../",
			$output	
		);
	}








}
