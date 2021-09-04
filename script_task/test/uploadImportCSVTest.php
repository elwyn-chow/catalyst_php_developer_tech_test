<?php declare(strict_types=1);
use PHPUnit\Framework\Testcase;

// These tests check that the CSV file was imported correctly
final class uploadImportCSVTest extends TestCase {

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

	// Test to see if script detects too many columns in CSV users file
	public function testCSVFileTooManyColumns(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];
		$database = $ini_array["correctdb"]["database"];
		$csv = $ini_array["csv"]["too_many_columns"];

		$db = new mysqli(
			$host,
			$user,
			$password,
			$database
		);

		// Delete rows of user table
		$result = $db->query("delete from user"); 

		$output = `php php/user_upload.php -u $user -p$password -h$host --file $csv 2>&1`;

		$this->assertRegExp(
			"/WARNING: While parsing the line number \d+, there were 4 fields when 3 were expected. Skipping this row.../",
			$output	
		);
	}

	// Test to see if script detects too few columns in CSV users file
	public function testCSVFileTooFewColumns(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];
		$database = $ini_array["correctdb"]["database"];
		$csv = $ini_array["csv"]["too_few_columns"];

		$db = new mysqli(
			$host,
			$user,
			$password,
			$database
		);

		// Delete rows of user table
		$result = $db->query("delete from user"); 

		$output = `php php/user_upload.php -u $user -p$password -h$host --file $csv 2>&1`;

		$this->assertRegExp(
			"/WARNING: While parsing the line number \d+, there were 2 fields when 3 were expected. Skipping this row.../",
			$output	
		);
	}


	// Test to see if script detects invalid email address in CSV users file
	public function testCSVFileInvalidEmailAddress(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];
		$database = $ini_array["correctdb"]["database"];
		$csv = $ini_array["csv"]["invalid_email_address"];

		$db = new mysqli(
			$host,
			$user,
			$password,
			$database
		);

		// Delete rows of user table
		$result = $db->query("delete from user"); 

		$output = `php php/user_upload.php -u $user -p$password -h$host --file $csv 2>&1`;

		$this->assertRegExp(
			"/WARNING: While parsing the line number \d+, the email address .+ value is invalid. Skipping this row.../",
			$output	
		);
	}


	// Test to see if script detects duplicate email address in CSV users file
	public function testCSVFileDuplicateEmailAddress(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];
		$database = $ini_array["correctdb"]["database"];
		$csv = $ini_array["csv"]["duplicate_email_address"];

		$db = new mysqli(
			$host,
			$user,
			$password,
			$database
		);

		// Delete rows of user table
		$result = $db->query("delete from user"); 

		$output = `php php/user_upload.php -u $user -p$password -h$host --file $csv 2>&1`;

		$this->assertRegExp(
			"/WARNING: While parsing the line number \d+, an error occurred: Duplicate entry 'jsmith@gmail.com' for key 'email'. Skipping row.../",
			$output	
		);
	}

	// Test to see if script detects weird characters in a firstname in CSV users file
	public function testCSVFileInvalidCharsInFirstname(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];
		$database = $ini_array["correctdb"]["database"];
		$csv = $ini_array["csv"]["invalid_chars_in_firstname"];

		$db = new mysqli(
			$host,
			$user,
			$password,
			$database
		);

		// Delete rows of user table
		$result = $db->query("delete from user"); 

		$output = `php php/user_upload.php -u $user -p$password -h$host --file $csv 2>&1`;

		$this->assertRegExp(
			"/WARNING: While parsing the line number \d+, the name Saul \"Slash\" had invalid characters that were stripped out.../",
			$output	
		);
	}

	// Test to see if script detects weird characters in a lastname in CSV users file
	public function testCSVFileInvalidCharsInLastname(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];
		$database = $ini_array["correctdb"]["database"];
		$csv = $ini_array["csv"]["invalid_chars_in_lastname"];

		$db = new mysqli(
			$host,
			$user,
			$password,
			$database
		);

		// Delete rows of user table
		$result = $db->query("delete from user"); 

		$output = `php php/user_upload.php -u $user -p$password -h$host --file $csv 2>&1`;
		$this->assertRegExp(
			"/WARNING: While parsing the line number \d+, the name Comment#This Is Not A Comment had invalid characters that were stripped out.../",
			$output	
		);
	}

	// Test to see if script accepts long valid email in CSV users file
	public function testCSVFileValidLongEmail(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];
		$database = $ini_array["correctdb"]["database"];
		$csv = $ini_array["csv"]["valid_long_email"];

		$db = new mysqli(
			$host,
			$user,
			$password,
			$database
		);

		// Delete rows of user table
		$result = $db->query("delete from user"); 

		$output = `php php/user_upload.php -u $user -p$password -h$host --file $csv 2>&1`;

		// Should only be one record
		$result = $db->query("select * from user"); 
		$row = $result->fetch_row();
		$this->assertEquals(
			"is_a_very_very_very_very_very_very_very_very_long_local_part_ok@this.is.the.very.very.very.very.very.very.very.very.very.very.very.very.very.very.very.very.very.very.very.long.local.part.which.can.be.a.maximum.of.two.hundred.and.fifty.five.characters.com",
			$row[2]
		);
	}

	// Test to see if script detects a very long firstname in CSV users file
	public function testCSVFileFieldTooLongFirstname(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];
		$database = $ini_array["correctdb"]["database"];
		$csv = $ini_array["csv"]["field_too_long_firstname"];

		$db = new mysqli(
			$host,
			$user,
			$password,
			$database
		);

		// Delete rows of user table
		$result = $db->query("delete from user"); 

		$output = `php php/user_upload.php -u $user -p$password -h$host --file $csv 2>&1`;

		$firstname = "This is way way way way way way way way way way way too long for a first name even though it has no invalid characters";
		$truncated_firstname = substr($firstname, 0, 80);

		$this->assertRegExp(
			"/WARNING: While parsing the line number \d+, firstname \"$firstname\" is too long. Truncating firstname.../",
			$output	
		);

		// Check record is truncated
		$result = $db->query("select * from user where email='shorty@short.com'"); 
		$row = $result->fetch_row();
		$this->assertEquals(
			$truncated_firstname,
			$row[0]
		);
	}

	// Test to see if script detects a very long lastname in CSV users file
	public function testCSVFileFieldTooLongLastname(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];
		$database = $ini_array["correctdb"]["database"];
		$csv = $ini_array["csv"]["field_too_long_lastname"];

		$db = new mysqli(
			$host,
			$user,
			$password,
			$database
		);

		// Delete rows of user table
		$result = $db->query("delete from user"); 

		$output = `php php/user_upload.php -u $user -p$password -h$host --file $csv 2>&1`;

		$lastname = "This is way way way way way way way way way way way too long for a last name even though it has no invalid characters";
		$truncated_lastname = substr($lastname, 0, 80);

		$this->assertRegExp(
			"/WARNING: While parsing the line number \d+, lastname \"$lastname\" is too long. Truncating lastname.../",
			$output	
		);

		// Check record is truncated
		$result = $db->query("select * from user where email='shorty@short.com'"); 
		$row = $result->fetch_row();
		$this->assertEquals(
			$truncated_lastname,
			$row[1]
		);
	}

}
