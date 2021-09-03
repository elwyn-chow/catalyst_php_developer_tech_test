<?php
use PHPUnit\Framework\Testcase;

final class untitledTest extends TestCase {
	public function testHelp(): void {
		$output = `php php/untitled_php_script.php --help`;

		// Don't compare the whole output. Just look for key string
		$this->assertRegExp(
			'/output the above list of directives with details/',
			$output	
		);
	}

	public function testMissingDBUser(): void {
		$output = `php php/untitled_php_script.php 2>&1`;

		$this->assertEquals(
			"Exiting... must set the username option\n",
			$output
		);
	}

	public function testMissingDBPassword(): void {
		$output = `php php/untitled_php_script.php -u testuser 2>&1`;

		$this->assertEquals(
			"Exiting... must set the password option\n",
			$output
		);
	}

	public function testMissingDBHost(): void {
		$output = `php php/untitled_php_script.php -u testuser -p testpass 2>&1`;

		$this->assertEquals(
			"Exiting... must set the host option\n",
			$output
		);
	}
}
