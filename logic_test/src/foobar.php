<?php
/* This script is to be run from the command line. The script:
 * Output the numbers from 1 to 100
 * Where the number is divisible by three (3) output the word “foo”
 * Where the number is divisible by five (5) output the word “bar”
 * Where the number is divisible by three (3) and (5) output the word “foobar”
 *
 * Output has the format: 1, 2, foo, 4, bar, foo, 7,
 */

//------------------------------------------------------------------------------
// Constants and global variables
//------------------------------------------------------------------------------

// Associative array where key is a divisor and value is the text to print out.
 
$GLOBALS["mapping"] = array(
	3 => "foo",
	5 => "bar",
);

//------------------------------------------------------------------------------
/**
 * processes a number and outputs text (if it is divible by any of the divisors)
 * or the number if it is not.
 * @param number	$number
 */

function parseValue($number) {
	$return_value = "";

	// See if number is divisible by any of the divisors
	foreach ($GLOBALS["mapping"] as $denomination => $str) {
		if ( $number % $denomination == 0 ) {
			// $number is divisible by $denomination
			$return_value .= $str;
		}
	}

	// If $number is not divisible by any of the denominations, set return
	// value to equal number.
	if ( strcmp($return_value, "" ) == 0) {
		$return_value = $number;
	}

	return $return_value;
}	

//------------------------------------------------------------------------------
// MAIN	
//------------------------------------------------------------------------------

$range = range(1, 100);

// Map range of numbers then join them and print results.
echo implode(', ', (array_map('parseValue', $range)));
