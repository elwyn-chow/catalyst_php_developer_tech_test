<?php declare(strict_types=1);
use PHPUnit\Framework\Testcase;

// This test checks that the create_table option ran successfully.
final class uploadCreateTableTest extends TestCase {

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
}
