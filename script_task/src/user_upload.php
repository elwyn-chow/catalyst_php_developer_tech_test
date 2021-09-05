<?php declare(strict_types=1);

/*******************************************************************************
This PHP script imports a CSV file containing user data (first and last 
names, and email address) into a MySQL/maria database.
 
The script has these command line options:
* --file [csv file name] – this is the name of the CSV to be parsed 
* --create_table – this will cause the MySQL users table to be built 
  (and no further action will be taken)
* --dry_run – this will be used with the --file directive in case we want to 
  run the script but not insert into the DB. All other functions will be 
  executed, but the database won't be altered
* -u – MySQL username
* -p – MySQL password
* -h – MySQL host
* --help – output the above list of directives with details.

*******************************************************************************/

//------------------------------------------------------------------------------
// Global variables and constants
//------------------------------------------------------------------------------

define('DATABASE_NAME', 'catalyst');	// Database name is catalyst
define('TABLE_NAME', 'user'); 		// Database table name is "user"
define('TABLE_FIELDS', 3);		// user table should only have 3 fields
define('DATABASE_SCHEMA_RELATIVE_PATH', "../database/catalyst_schema.sql");

// Maximum allowed length of firstname and lastname fields
define('MAXLENGTH_FIRSTNAME', 80);
define('MAXLENGTH_LASTNAME', 80);

// Remember whether script is being run in dry_run mode
$GLOBALS["dry_run_mode"] = false;

//------------------------------------------------------------------------------
/**
 * Displays help message for script
 */

function help_message() {
	$script_name = basename(__FILE__);
	echo <<<EOD

$script_name has the following options:
* --file [csv file name] – this is the name of the CSV to be parsed 
* --create_table – this will cause the MySQL users table to be built 
  (and no further action will be taken)
* --dry_run – this will be used with the --file directive in case we want to 
  run the script but not insert into the DB. All other functions will be 
  executed, but the database won't be altered
* -u – MySQL username
* -p – MySQL password
* -h – MySQL host
* --help – output the above list of directives with details.

Using dry_run mode overrides create_table option.
EOD;
}

//------------------------------------------------------------------------------
/**
 * Connects to database 
 * @param array		$options	getopts parameters
 * @return database handler
 */

function connect_database($options) {
	mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL);

	try {
		$db = new mysqli(
			$options["h"],
			$options["u"],
			$options["p"],
			DATABASE_NAME		
		);
	}
	catch (mysqli_sql_exception $e) {
		die("Could not open connection to database: " . $e->getMessage() . "\n");
	}

	echo "Database connected...\n";
	return $db;
}

//------------------------------------------------------------------------------
/**
 * Handles create table option by creating MySQL table
 * @param database handler	$db
 */

function create_table($db) {
	echo "Creating database table...\n";

	// Open the database schema file
	$path = dirname(__FILE__) . '/' . DATABASE_SCHEMA_RELATIVE_PATH;
	if ( !file_exists($path) ) {
		die("Database schema file $path does not exist.");
	}
	if ( !is_readable($path) ) {
		die("Database schema file $path is not readable");
	}

	$fh = fopen($path, 'r');

	// Read the schema file and parse it line by line. 
	// Execute SQL commands that end in a semi-colon. Skip comments.

	$query = "";
	while($line = fgets($fh)) {
		if (preg_match('/^--/', $line)) {
			// Skip comment line
		} elseif (preg_match('/\/\*.+\*\/;/', $line)) {
			// Skip comment line
		} elseif (preg_match('/;/', $line)) {
			// Found semi-colon. Execute SQL command
			$query .= $line;

			try {
				if (! $GLOBALS["dry_run_mode"] ) {
					mysqli_query($db, $query);
				}
			} 
			catch (mysqli_sql_exception $e) {
				die("Error: " . $e->getMessage() . "\n");
			}

			// Reset query
			$query = "";
		} else {
			$query .= $line;
		}
	}
	fclose($fh);

}

//------------------------------------------------------------------------------
/**
 * Imports users from CSV file
 * @param array			$options	getopts array
 * @param database handler	$dbh
 */

function import_users($options, $db) {
	echo "Importing users...\n";
	$csv = parse_csv_file($options["file"]);

	// Parse through all rows but ship header row 
	for($row_number = 1; $row_number < count($csv); $row_number++) {
		$row = $csv[$row_number];

		// Trim leading and trailing whitespace 
		for( $field_number = 0; $field_number < count($row); $field_number++) {
			$row[$field_number] = trim($row[$field_number]);
		}

		// Check the number of fields
		if ( count($row) != TABLE_FIELDS) {
			trigger_error(
				"WARNING: While parsing the line number $row_number, there were " . count($row) . " fields when " . TABLE_FIELDS . " were expected. Skipping this row...", 
				E_USER_WARNING
			); 
		  	continue;	
		}

		// Check for invalid characters in firstname and lastname,
		// strip them out and state warning
		foreach(range(0,1) as $field_number) {
			if (preg_match("/([^A-Za-z\s\-'])/", $row[$field_number])) {
				trigger_error(
					"WARNING: While parsing the line number $row_number, the name " . $row[$field_number] . " had invalid characters that were stripped out...", 
					E_USER_WARNING
				);
				$row[$field_number] = preg_replace("/([^A-Za-z\s\-'])/", "", $row[$field_number]);
			}
		}

		// Check if email address is valid looking
		if(!filter_var($row[2], FILTER_VALIDATE_EMAIL)) {
			trigger_error(
				"WARNING: While parsing the line number $row_number, the email address " . $row[2] . " value is invalid. Skipping this row...", 
				E_USER_WARNING
			);
			continue;
		}

		// Inputs seem okay. Now modify them slightly...
		// * by trimming spaces off left and right of string
		// * uppercasing the first letter of names
		// * lower casing the email
		$firstname = ucfirst($row[0]);
		$lastname = ucfirst($row[1]);
		$email = strtolower($row[2]);

		// Check length of names, truncate if too long
		if (strlen($firstname) > MAXLENGTH_FIRSTNAME) {
			trigger_error(
				"WARNING: While parsing the line number $row_number, firstname \"$firstname\" is too long. Truncating firstname...\n",
				E_USER_WARNING
			);
			$firstname = substr($firstname, 0, MAXLENGTH_FIRSTNAME);
		}

		if (strlen($lastname) > MAXLENGTH_LASTNAME) {
			trigger_error(
				"WARNING: While parsing the line number $row_number, lastname \"$lastname\" is too long. Truncating lastname...\n",
				E_USER_WARNING
			);
			$lastname = substr($lastname, 0, MAXLENGTH_LASTNAME);
		}
		
		try {
			if (! $GLOBALS["dry_run_mode"] ) {
				$query =<<<EO_SQL
insert into user VALUES ("$firstname", "$lastname", "$email");
EO_SQL;
				mysqli_query($db, $query);
			}
		} 
		catch (mysqli_sql_exception $e) {
			trigger_error(
				"WARNING: While parsing the line number $row_number, an error occurred: " . $e->getMessage() . ". Skipping row...\n",
				E_USER_WARNING
			);
			continue;
		}
	}
}

//------------------------------------------------------------------------------
/**
 * Parses a CSV file containing user data after running some checks
 * @param	string		$filename 	file to parse
 * @return	associative array of CSV contents
 */

function parse_csv_file($filename) {
	if ( !file_exists($filename) ) {
		die("CSV file $filename does not exist");
	}
	if ( !is_readable($filename) ) {
		die("CSV file $filename is not readable");
	}

	echo "Opening CSV file $filename...";
	$csv = array_map('str_getcsv', file($filename));
	return $csv;
}

//------------------------------------------------------------------------------
// MAIN
//------------------------------------------------------------------------------

// getopts settings
$shortopts = "";
$shortopts .= "u:"; // MySQL username
$shortopts .= "p:"; // MySQL passowrd 
$shortopts .= "h:"; // MySQL host
// If required later, it will be easy to add a port option
// $shortopts .= "P:"; // MySQL port 

$longopts = array(
	"file:",	// name of CSV file to be parsed
	"create_table",	// Causes sql table to be created and no further action
	"dry_run",	// dry run more
	"help"
);

$options = getopt($shortopts, $longopts);

//------------------------------------------------------------------------------

if (isset($options["help"])) {
	help_message();
	exit;
}

if (isset($options["dry_run"])) {
	echo "Dry run mode ON...\n";
	$GLOBALS["dry_run_mode"] = true;
}

// Store database authentication details and check that there are values.
if (!isset($options["u"])) {
	die("Exiting... must set the username option\n");
}

if (!isset($options["p"])) {
	die("Exiting... must set the password option\n");
}

if (!isset($options["h"])) {
	die("Exiting... must set the host option\n");
}

//------------------------------------------------------------------------------

$db = connect_database($options);

if (isset($options["create_table"])) {
	create_table($db);
	exit;
}

if (!isset($options["file"])) {
	die("Exiting... must set the filename option\n");
}

import_users($options, $db);
exit;
