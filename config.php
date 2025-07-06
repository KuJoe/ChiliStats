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
// !! Important settings
//

// Database Connection
$db_file_path = "stats.db";

//
// Optional settings
//

$reload=3*60*60; // Reload lock in seconds (3 * 60 * 60 => 3 hours)
$online=3*60; // online time in seconds (3 * 60 => 3 minutes)
$oldentries=7; // delete Visitor infos after x days (7 => 7 days)

//
// End of settings
//

try {
  $conn = new PDO("sqlite:$db_file_path");
  
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch(PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}

// Check if database selection was successful
if (!$conn) {
	exit;
}
?>