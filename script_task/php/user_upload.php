<?php

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

define('DATABASE_NAME', 'catalyst');
define('TABLE_NAME', 'user');
define('DATABASE_SCHEMA_RELATIVE_PATH', "../database/catalyst_schema.sql");

$GLOBALS["dry_run_mode"] = false;

//------------------------------------------------------------------------------
//
// Displays help message for script
//
//------------------------------------------------------------------------------

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
//
// Connects to database 
// Input: getopts parameters
// Output: database handler
//
//------------------------------------------------------------------------------

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

	echo "Database connected\n";
	return $db;
}

//------------------------------------------------------------------------------
//
// Handles create table option by creating table
// Input: database handler
//
//------------------------------------------------------------------------------

function create_table($db) {
	echo "Creating database table...\n";

	// Open the database schema file
	$path = dirname(__FILE__) . '/' . DATABASE_SCHEMA_RELATIVE_PATH;
	if ( !file_exists($path) ) {
		die("Cannot open database schema file: $path");
	}
	if ( !is_readable($path) ) {
		die("Cannot read database schema file: $path");
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
	echo "Dry run mode ON\n";
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

