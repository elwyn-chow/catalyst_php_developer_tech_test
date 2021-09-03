<?php
use PHPUnit\Framework\Testcase;

final class foobarTest extends TestCase {
	// The output should be CSV with 100 values
	public function testOutputCount(): void {
		$output = `php src/foobar.php`;

		$parsed = str_getcsv($output, ',');

		$this->assertEquals(
			count($parsed), 
			100
		);
	}

	// Each value in the CSV should be a number, foo, bar, or foobar
	public function testOutputValuesFormat(): void {
		$output = `php src/foobar.php`;

		$parsed = str_getcsv($output, ',');

		foreach($parsed as $value) {
			$value = trim($value);
			$this->assertRegExp(
				"/^(foo|bar|foobar|\d+)/", 
				$value
			);
		}
	}

	// Test some specific values
	public function testOutputSpecific(): void {
		$output = `php src/foobar.php`;

		$parsed = str_getcsv($output, ',');

		// NOTE: $parsed is 0-indexed
		
		// value for 5 should be "bar"
		$this->assertEquals(trim($parsed[4]), "bar");
		// value for 30 should be "foobar"
		$this->assertEquals(trim($parsed[29]), "foobar");
		// value for 66 should be "foo"
		$this->assertEquals(trim($parsed[65]), "foo");
		// value for 31 should be 31 
		$this->assertEquals(trim($parsed[30]), 31);
	}

}
