<?PHP
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
define('ChiliAllowed', TRUE);
require_once('config.php');
require_once('functions.php');

//
// initialization and visitor Information
//

// Date Time
$time=time();
$day=date("Y.m.d",$time); // YYYY.MM.DD
$month=date("Y.m",$time); // YYYY.MM

// IP address
$ip = getRealUserIp();

// Get Referrer and Page
if (isset($_GET["ref"]) ) {
	// from javascript
	$referer = $_GET["ref"];
} else {
	// from php
	$referer=$_SERVER['HTTP_REFERER'];
}

// Page
$lastSlashPos = strrpos($referer, "/");

if ($lastSlashPos !== false) {
  $page = substr($referer, $lastSlashPos + 1);
} else {
  $page = "No page specified";
}

if (empty($page)) {
	$page = "No page specified";
}

// cleanup
if (basename($page) == basename(__FILE__)) $page="" ; // count not counter.php

// Language
$language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2);

// delete old IPs
$yesterday = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 48*60*60 ; // 48*60*60 => after 48 hours
try {
	$sql = "DELETE FROM visitors WHERE time < ?";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(1, $yesterday);
	$stmt->execute();
	$conn->commit();
	$stmt = null;
} catch(PDOException $e) {
  // Do nothing.
}

// delete old pages and languages
$old_day=date("Y.m.d",mktime(0, 0, 0, date("n"), date("j")-$oldentries, date("Y"))); // delete older than $oldentries(config.php) days
try {
	$delete = $conn->prepare("DELETE FROM pages WHERE day <= :day");
	$sql = "DELETE FROM pages WHERE day < ?";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(1, $yesterday);
	$stmt->execute();
	$conn->commit();
	$stmt = null;
} catch(PDOException $e) {
  // Do nothing.
}
try {
	$sql = "DELETE FROM languages WHERE day < ?";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(1, $yesterday);
	$stmt->execute();
	$conn->commit();
	$stmt = null;
} catch(PDOException $e) {
  // Do nothing.
}

// insert a new day
$sql = "SELECT COUNT(*) FROM days WHERE day = ?";
$stmt = $conn->prepare($sql);
$stmt->bindParam(1, $day);
$stmt->execute();
$count = $stmt->fetchColumn();

if ($count === 0) {
	$insert_sql = "INSERT INTO days (day, user, view) VALUES (?, '0', '0')";
	$insert_stmt = $conn->prepare($insert_sql);
	$insert_stmt->bindParam(1, $day);
	$insert_stmt->execute();
}
$stmt = null;
$insert_stmt = null;
	
// check reload and set online time
$newuser=0;
$oldreload = $time-$reload;
$sql = "SELECT COUNT(*) FROM visitors WHERE ipaddr = ? AND time > ? ORDER BY visitor_id DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(1, $ip);
$stmt->bindParam(2, $oldreload);
$stmt->execute();
$count = $stmt->fetchColumn();

if ($count === 0) {
	// New visitor
	$newuser = 1;
	$insert_sql = "INSERT INTO visitors (ipaddr, time, online) VALUES (?, ?, ?)";
	$insert_stmt = $conn->prepare($insert_sql);
	$insert_stmt->bindParam(1, $ip);
	$insert_stmt->bindParam(2, $time);
	$insert_stmt->bindParam(3, $time);
	$insert_stmt->execute();
	$update_days_sql = "UPDATE days SET user = user + 1, view = view + 1 WHERE day = ?";
} else {
	// Existing visitor (reload)
	$update_sql = "UPDATE visitors SET online = ? WHERE visitor_id = ?";
	$update_stmt = $conn->prepare($update_sql);
	$update_stmt->bindParam(1, $time);
	$update_stmt->bindParam(2, $visitor_id);
	$update_stmt->execute();
	$update_days_sql = "UPDATE days SET view = view + 1 WHERE day = ?";
}

// Update days table (common for both new and existing visitors)
$update_days_stmt = $conn->prepare($update_days_sql);
$update_days_stmt->bindParam(1, $day);
$update_days_stmt->execute();
$stmt = null;
$insert_stmt = null;
$update_stmt = null;
$update_days_stmt = null;

// Page
if(isset($page)) {
	$sql = "SELECT COUNT(*) FROM pages WHERE page = ? AND day = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(1, $page);
	$stmt->bindParam(2, $day);
	$stmt->execute();
	$count = $stmt->fetchColumn();

	if ($count === 0) {
		// New page
		$insert_sql = "INSERT INTO pages (day, page, view) VALUES (?, ?, 1)";
		$insert_stmt = $conn->prepare($insert_sql);
		$insert_stmt->bindParam(1, $day);
		$insert_stmt->bindParam(2, $page);
		$insert_stmt->execute();
	} else {
		// Existing page (update view count)
		$update_sql = "UPDATE pages SET view = view + 1 WHERE page_id = ?";
		$update_stmt = $conn->prepare($update_sql);
		$update_stmt->bindParam(1, $page_id);
		$update_stmt->execute();
	}
	$stmt = null;
	$insert_stmt = null;
	$update_stmt = null;
}

// Language 
if($language<>"" AND $newuser == 1) {
    $sql = "SELECT COUNT(*) FROM languages WHERE language = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(1, $language);
    $stmt->execute();
	$count = $stmt->fetchColumn();

	if ($count === 0) {
      // New language
      $insert_sql = "INSERT INTO languages (day, language, view) VALUES (?, ?, 1)";
      $insert_stmt = $conn->prepare($insert_sql);
      $insert_stmt->bindParam(1, $day);
      $insert_stmt->bindParam(2, $language);
      $insert_stmt->execute();
    }
    $stmt = null;
    $insert_stmt = null;
}

//
// Generate Image
//

//  Create a blank image
$im = imagecreatetruecolor('1','1');	

// Fill BG color
$bg_color = imagecolorallocatealpha($im, 0,0,0,0);
imagefill($im, 0, 0, $bg_color);
	
// image output
header ("Content-type: image/png");
// create PNG
imagepng($im);
// destroy temp image
imagedestroy($im);

?>