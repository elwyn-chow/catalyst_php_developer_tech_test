<?php declare(strict_types=1);
// These tests are just for the command line options

use PHPUnit\Framework\Testcase;

final class uploadTestOptions extends TestCase {
	// Test to see help message
	public function testHelp(): void {
		$output = `php src/user_upload.php --help`;

		// Don't compare the whole output. Just look for key string
		$this->assertRegExp(
			'/output the above list of directives with details/',
			$output	
		);
	}

	// Test to see if database user was entered
	public function testMissingDBUser(): void {
		$output = `php src/user_upload.php 2>&1`;

		$this->assertEquals(
			"Exiting... must set the username option\n",
			$output
		);
	}

	// Test to see if database password was entered
	public function testMissingDBPassword(): void {
		$output = `php src/user_upload.php -u testuser 2>&1`;

		$this->assertEquals(
			"Exiting... must set the password option\n",
			$output
		);
	}

	// Test to see if database host was entered
	public function testMissingDBHost(): void {
		$output = `php src/user_upload.php -u testuser -p testpass 2>&1`;

		$this->assertEquals(
			"Exiting... must set the host option\n",
			$output
		);
	}

	// Test to see if filename was entered
	public function testMissingFilename(): void {
		$ini_array = parse_ini_file("test.ini");
		$user = $ini_array["correctdb"]["user"];
		$password = $ini_array["correctdb"]["password"];
		$host = $ini_array["correctdb"]["host"];

		$output = `php src/user_upload.php -u $user -p$password -h$host --file 2>&1`;

		$this->assertRegExp(
			'/Exiting\.\.\. must set the filename option/',
			$output	
		);
	}
}
