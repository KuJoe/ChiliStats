<?php
/*
	--------------------------------------------------------
	ChiliStats(Revived) is based on the original code from Adam Pawlita (https://pawlita.de)
    Currently maintained by KuJoe (https://github.com/KuJoe/ChiliStats/)
    --------------------------------------------------------
    Original Copyright Notice:

	The script is protected by copyright law. All rights and
	copyrights are held by the author: Adam Pawlita
	This script may be freely used and redistributed so long
	the stated copyright notices in all parts of the script before-
	hands remain. For correct operation, or damage caused by
	the operation of this script is made only if the author has no
	Warranty. Commissioning is carried out in each case
	at their own risk of the operator.
	-------------------------------------------------------
*/


//
// !! These settings must be changed
//

// Database Connection
$db_host = 'localhost'; // database server (e.g. localhost)
$db_user = 'db_user'; // user
$db_pass = 'db_pass'; // password
$db_name = 'db_name';// database name
$db_prefix = 'chilli_stats_1_'; // database prefix


//
// Optional settings
//

$style = "dark"; // Counter Style "dark" or "light"
$show = "totally"; // Counter shows "totally"  or "last24h"  visitors
$size = "big"; // Size of the counter "small" or "big"

$reload=3*60*60; // Reload lock in seconds (3 * 60 * 60 => 3 hours)
$online=3*60; // online time in seconds (3 * 60 => 3 minutes)
$oldentries=7; // delete Visitor infos after x days (7 => 7 days)

//
// End of settings
//

// connect to database
try {
	$conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch(PDOException $e) {
	die("Connection failed: " . $e->getMessage());
  }
  
  // Check if database selection was successful
  if (!$conn) {
	echo "The database '$db_name' was not found!";
	exit;
  }
?>