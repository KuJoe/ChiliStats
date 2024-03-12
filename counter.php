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

require_once('config.php');

//
// initialization and visitor Information
//

// Date Time
$time=time();
$day=date("Y.m.d",$time); // YYYY.MM.DD
$month=date("Y.m",$time); // YYYY.MM

// IP adress
$ip=$_SERVER['REMOTE_ADDR']; 

// Get Referrer and Page
if ($_GET["ref"] <> "" ) 
	{
	// from javascript
	$referer = $_GET["ref"];
	$page = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);	
	} 
else 
	{
	// from php
	$referer=$_SERVER['HTTP_REFERER'];
	$page=$_SERVER['PHP_SELF']; // with include via php		
	} 	
// cleanup
if (basename($page) == basename(__FILE__)) $page="" ; // count not counter.php

$server_host=$_SERVER["HTTP_HOST"]; // Server Host
if (substr($server_host,0,4) == "www.") $server_host=substr($server_host,4); // Server Host without www.

$referer_host=parse_url($referer, PHP_URL_HOST); // Referrer Host
if (substr($referer_host,0,4) == "www.") $referer_host=substr($referer_host,4); // Referer Host without www.

// adjust search engines 
if (strstr($referer_host, "google."))
	{
	$referer_query=parse_url($referer, PHP_URL_QUERY);
	$referer_query.="&";
	preg_match('/q=(.*)&/UiS', $referer_query, $keys);
	
	$keyword=urldecode($keys[1]); // These are the search terms
	$referer_host="Google"; // adjust host
	}
if (strstr($referer_host, "yahoo."))
	{
	$referer_query=parse_url($referer, PHP_URL_QUERY);
	$referer_query.="&";
	preg_match('/p=(.*)&/UiS', $referer_query, $keys);
	
	$keyword=urldecode($keys[1]); // These are the search terms
	$referer_host="Yahoo"; // adjust host
	}
if (strstr($referer_host, "bing."))
	{
	$referer_query=parse_url($referer, PHP_URL_QUERY);
	$referer_query.="&";
	preg_match('/q=(.*)&/UiS', $referer_query, $keys);
	
	$keyword=urldecode($keys[1]); // These are the search terms
	$referer_host="Bing"; // adjust host
	}
		
// Language
$language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2);

// Counter

// delete old IPs
$anfangGestern = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 48*60*60 ; // 48*60*60 => after 48 hours
$delete = $conn->prepare("DELETE FROM ".$db_prefix."IPs WHERE time < :time");
$delete->execute([':time' => $anfangGestern]);

// delete old page,referrer,language and keywords
$old_day=date("Y.m.d",mktime(0, 0, 0, date("n"), date("j")-$oldentries, date("Y"))); // delete older than $oldentries(config.php) days
$delete = $conn->prepare("DELETE FROM ".$db_prefix."Page WHERE day <= :day");
$delete->execute([':day' => $old_day]);
$delete = $conn->prepare("DELETE FROM ".$db_prefix."Referer WHERE day <= :day");
$delete->execute([':day' => $old_day]);
$delete = $conn->prepare("DELETE FROM ".$db_prefix."Keyword WHERE day <= :day");
$delete->execute([':day' => $old_day]);
$delete = $conn->prepare("DELETE FROM ".$db_prefix."Language WHERE day <= :day");
$delete->execute([':day' => $old_day]);

// insert a new day
$neuerTag = $conn->prepare("SELECT id FROM ".$db_prefix."Day WHERE day = :day");
$neuerTag->execute([':day' => $day]);
if ($neuerTag->rowCount() == 0) {
    $insert = $conn->prepare("INSERT INTO ".$db_prefix."Day (day, user, view) VALUES (:day, '0', '0')");
    $insert->execute([':day' => $day]);
}
	
// check reload and set online time
$newuser=0;
$oldreload = $time-$reload;
$gesperrt = $conn->prepare("SELECT id FROM ".$db_prefix."IPs WHERE ip = :ip AND time > :time ORDER BY id DESC LIMIT 1");
$gesperrt->execute([':ip' => $ip, ':time' => $oldreload]);
if ($gesperrt->rowCount() == 0) {
    // new visitor
    $newuser=1;
    $insert = $conn->prepare("INSERT INTO ".$db_prefix."IPs (ip, time, online) VALUES (:ip, :time, :time)");
    $insert->execute([':ip' => $ip, ':time' => $time]);
    $update = $conn->prepare("UPDATE ".$db_prefix."Day SET user = user + 1, view = view + 1 WHERE day = :day");
    $update->execute([':day' => $day]);
} else {
    // reload visitor
    $gesperrtID = $gesperrt->fetchColumn();
    $update = $conn->prepare("UPDATE ".$db_prefix."IPs SET online = :time WHERE id = :id");
    $update->execute([':time' => $time, ':id' => $gesperrtID]);
    $update = $conn->prepare("UPDATE ".$db_prefix."Day SET view = view + 1 WHERE day = :day");
    $update->execute([':day' => $day]);
}

// Page
if($page <> "") {
    $ergebnis = $conn->prepare("SELECT id FROM ".$db_prefix."Page WHERE page = :page AND day = :day");
    $ergebnis->execute([':page' => $page, ':day' => $day]);
}

// Referer
if(stristr($server_host, $referer_host) === FALSE AND $referer_host<>"" AND $newuser == 1) {
    $ergebnis = $conn->prepare("SELECT id from ".$db_prefix."Referer WHERE referer=:referer_host AND day=:day");
    $ergebnis->execute([':referer_host' => $referer_host, ':day' => $day]);
    if ($ergebnis->rowCount() == 0) {
        $insert = $conn->prepare("INSERT INTO ".$db_prefix."Referer (day, referer, view) VALUES (:day, :referer_host, '1')");
        $insert->execute([':day' => $day, ':referer_host' => $referer_host]);
    } else { 
        $refererid = $ergebnis->fetchColumn();
        $update = $conn->prepare("UPDATE ".$db_prefix."Referer SET view=view+1 WHERE id=:refererid");
        $update->execute([':refererid' => $refererid]);
    }
}

// keywords 
if($keyword<>"" AND $newuser == 1) {
    $ergebnis = $conn->prepare("SELECT id from ".$db_prefix."Keyword WHERE keyword=:keyword AND day=:day");
    $ergebnis->execute([':keyword' => $keyword, ':day' => $day]);
    if ($ergebnis->rowCount() == 0) {
        $insert = $conn->prepare("INSERT INTO ".$db_prefix."Keyword (day, keyword, view) VALUES (:day, :keyword, '1')");
        $insert->execute([':day' => $day, ':keyword' => $keyword]);
    } else { 
        $keywordid = $ergebnis->fetchColumn();
        $update = $conn->prepare("UPDATE ".$db_prefix."Keyword SET view=view+1 WHERE id=:keywordid");
        $update->execute([':keywordid' => $keywordid]);
    }
}

// Language 
if($language<>"" AND $newuser == 1) {
    $ergebnis = $conn->prepare("SELECT id from ".$db_prefix."Language WHERE language=:language");
    $ergebnis->execute([':language' => $language]);
    if ($ergebnis->rowCount() == 0) {
        $insert = $conn->prepare("INSERT INTO ".$db_prefix."Language (day, language, view) VALUES (:day, :language, '1')");
        $insert->execute([':day' => $day, ':language' => $language]);
    } else {
        // Continue your code here...
    }
}

//
// Generate Image
//

// Get Value from DB
if ($show == "last24h") {
    // Last24h
    $islast=$time-24*60*60;
    $abfrage = $conn->prepare("SELECT COUNT(id) FROM ".$db_prefix."IPs WHERE time>=:islast");
    $abfrage->execute([':islast' => $islast]);
    $value = $abfrage->fetchColumn();
    $title="Last 24 hours";
} else {
    // Totally Visitors    
    $abfrage = $conn->prepare("SELECT SUM(user) FROM ".$db_prefix."Day");
    $abfrage->execute();
    $value = $abfrage->fetchColumn();
    $title="Totally Visitors";
}

// short value
if ( $value > 999 ) { $value = $value / 1000; $einheit = "k"; }
if ($value > 999) { $value = $value / 1000; $einheit = "m"; }
if ( $value > 999 ) { $value = ">999"; $einheit = "m"; }
else { 
    if ( $value >=10) $value=round($value,0);
    else $value=round($value,1);
}
$value.=$einheit;
	
// Variables
$title_font="OpenSans-Regular.ttf";
$value_font="OpenSans-Bold.ttf";

if ($size == "small")
{
$width=1;
$height=1;
$title_font_size = 8;
$value_font_size = 9;
$title_pos_y = 15;
$value_pos_y = 16;	
// short title
if ($show == "last24h") {$title="Last24h";}
else {$title="Visitors";}
// left title
$size = imagettfbbox($title_font_size, 0, $title_font, $title);
$titleWidth = $size[2] - $size[0];
$title_pos_x = 8;
// right center value
$size = imagettfbbox($value_font_size, 0, $value_font, $value);
$valueWidth = $size[2] - $size[0];
$space_left = $title_pos_x + $titleWidth;
$value_pos_x = $space_left + ((($width - $space_left) / 2) - ($valueWidth / 2));
}
else
{
$width=1;
$height=1;
$title_font_size = 8;
$value_font_size = 24;
$title_pos_y = 15;
$value_pos_y = 48;
// center title
$size = imagettfbbox($title_font_size, 0, $title_font, $title);
$textWidth = $size[2] - $size[0];
$title_pos_x = ($width / 2) - ($textWidth / 2);
// center value
$size = imagettfbbox($value_font_size, 0, $value_font, $value);
$textWidth = $size[2] - $size[0];
$value_pos_x = ($width / 2) - ($textWidth / 2);
}

//  Create a blank image
$im = imagecreatetruecolor($width,$height);	
	
// Colors
if ($style == "light")
{
$bg_color = imagecolorallocatealpha($im, 235,235,235,0);
$title_color = imagecolorallocate($im, 50,50,50);
$value_color = imagecolorallocate($im, 25,25,25);	
}
else
{
$bg_color = imagecolorallocatealpha($im, 50,50,50,0);
$title_color = imagecolorallocate($im, 255,255,255);
$value_color = imagecolorallocate($im, 255,255,255);
}
$shadow_color = imagecolorallocatealpha($im, 0,0,0,115);
$red = imagecolorallocate($im, 223,1,1);

// Fill BG color
imagefill($im, 0, 0, $bg_color);
// Red line
imageline($im,0,0,$width,0,$red);
imageline($im,0,1,$width,1,$red);
//  title
imagettftext ($im, $title_font_size, 0, $title_pos_x+2, $title_pos_y+2, $shadow_color, $title_font, $title); 
imagettftext ($im, $title_font_size, 0, $title_pos_x, $title_pos_y, $title_color, $title_font, $title); 
// value
imagettftext ($im, $value_font_size, 0, $value_pos_x+2, $value_pos_y+2, $shadow_color, $value_font, $value);
imagettftext ($im, $value_font_size, 0, $value_pos_x, $value_pos_y, $value_color, $value_font, $value);
	
// image output
header ("Content-type: image/png");
// create PNG
imagepng($im);
// destroy temp image
imagedestroy($im);

?>