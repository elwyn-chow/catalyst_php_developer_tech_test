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

$GLOBALS["mapping"] = array(
	3 => "foo",
	5 => "bar",
);

//------------------------------------------------------------------------------

function parseValue($number) {
	$return_value = "";

	foreach ($GLOBALS["mapping"] as $denomination => $str) {
		if ( $number % $denomination == 0 ) {
			// $number is divisible by $denomination
			$return_value .= $str;
		}
	}

	// $number is not divisible by any of the denominations, just return it.
	if ( strcmp($return_value, "" ) == 0) {
		$return_value = $number;
	}

	return $return_value;
}	

//------------------------------------------------------------------------------
$range = range(1, 100);

echo implode(', ', (array_map('parseValue', $range)));
