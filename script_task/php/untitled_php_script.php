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
* --help – which will output the above list of directives with details.

*******************************************************************************/
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
var_dump($options);
