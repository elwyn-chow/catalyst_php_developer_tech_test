<?php
use PHPUnit\Framework\Testcase;

final class untitledTest extends TestCase {
	// Test to see help message
	public function testHelp(): void {
		$output = `php php/untitled_php_script.php --help`;

		// Don't compare the whole output. Just look for key string
		$this->assertRegExp(
			'/output the above list of directives with details/',
			$output	
		);
	}

	// Test to see if database user was entered
	public function testMissingDBUser(): void {
		$output = `php php/untitled_php_script.php 2>&1`;

		$this->assertEquals(
			"Exiting... must set the username option\n",
			$output
		);
	}

	// Test to see if database password was entered
	public function testMissingDBPassword(): void {
		$output = `php php/untitled_php_script.php -u testuser 2>&1`;

		$this->assertEquals(
			"Exiting... must set the password option\n",
			$output
		);
	}

	// Test to see if database host was entered
	public function testMissingDBHost(): void {
		$output = `php php/untitled_php_script.php -u testuser -p testpass 2>&1`;

		$this->assertEquals(
			"Exiting... must set the host option\n",
			$output
		);
	}

	// Test to see if incorrect database credentials are handled correctly 
	public function testWrongDBCredentials(): void {
		$output = `php php/untitled_php_script.php -u testuser -p testpassjibberish -hlocalhost 2>&1`;

		$this->assertEquals(
			"Could not open connection to database: Access denied for user 'testuser'@'localhost'\n",
			$output
		);
	}

}
