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
if ($_GET["ref"] <> "" ) {
	// from javascript
	$referer = $_GET["ref"];
	$page = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);	
} else {
	// from php
	$referer=$_SERVER['HTTP_REFERER'];
	$page=$_SERVER['PHP_SELF']; // with include via php		
} 	
// cleanup
if (basename($page) == basename(__FILE__)) $page="" ; // count not counter.php

// Language
$language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2);

// delete old IPs
$anfangGestern = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 48*60*60 ; // 48*60*60 => after 48 hours
$delete = $conn->prepare("DELETE FROM ".$db_prefix."visitors WHERE time < :time");
$delete->execute([':time' => $anfangGestern]);

// delete old pages and languages
$old_day=date("Y.m.d",mktime(0, 0, 0, date("n"), date("j")-$oldentries, date("Y"))); // delete older than $oldentries(config.php) days
$delete = $conn->prepare("DELETE FROM ".$db_prefix."pages WHERE day <= :day");
$delete->execute([':day' => $old_day]);
$delete = $conn->prepare("DELETE FROM ".$db_prefix."languages WHERE day <= :day");
$delete->execute([':day' => $old_day]);

// insert a new day
$neuerTag = $conn->prepare("SELECT day_id FROM ".$db_prefix."days WHERE day = :day");
$neuerTag->execute([':day' => $day]);
if ($neuerTag->rowCount() == 0) {
    $insert = $conn->prepare("INSERT INTO ".$db_prefix."days (day, user, view) VALUES (:day, '0', '0')");
    $insert->execute([':day' => $day]);
}
	
// check reload and set online time
$newuser=0;
$oldreload = $time-$reload;
$gesperrt = $conn->prepare("SELECT visitor_id FROM ".$db_prefix."visitors WHERE ipaddr = :ip AND time > :time ORDER BY visitor_id DESC LIMIT 1");
$gesperrt->execute([':ip' => $ip, ':time' => $oldreload]);
if ($gesperrt->rowCount() == 0) {
    // new visitor
    $newuser=1;
    $insert = $conn->prepare("INSERT INTO ".$db_prefix."visitors (ipaddr, time, online) VALUES (:ip, :time, :time)");
    $insert->execute([':ip' => $ip, ':time' => $time]);
    $update = $conn->prepare("UPDATE ".$db_prefix."days SET user = user + 1, view = view + 1 WHERE day = :day");
    $update->execute([':day' => $day]);
} else {
    // reload visitor
    $gesperrtID = $gesperrt->fetchColumn();
    $update = $conn->prepare("UPDATE ".$db_prefix."visitors SET online = :time WHERE visitor_id = :id");
    $update->execute([':time' => $time, ':id' => $gesperrtID]);
    $update = $conn->prepare("UPDATE ".$db_prefix."days SET view = view + 1 WHERE day = :day");
    $update->execute([':day' => $day]);
}

// Page
if($page <> "") {
    $ergebnis = $conn->prepare("SELECT page_id FROM ".$db_prefix."pages WHERE page = :page AND day = :day");
    $ergebnis->execute([':page' => $page, ':day' => $day]);
}

// Language 
if($language<>"" AND $newuser == 1) {
    $ergebnis = $conn->prepare("SELECT lang_id from ".$db_prefix."languages WHERE language=:language");
    $ergebnis->execute([':language' => $language]);
    if ($ergebnis->rowCount() == 0) {
        $insert = $conn->prepare("INSERT INTO ".$db_prefix."languages (day, language, view) VALUES (:day, :language, '1')");
        $insert->execute([':day' => $day, ':language' => $language]);
    } else {
        // Continue your code here...
    }
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