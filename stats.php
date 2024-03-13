<?PHP
define('ChiliAllowed', TRUE);
session_start();
require_once('config.php');
require_once('functions.php');

$token = getCSRFToken();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>ChiliStats(Revived) - Dashboard</title>
<link href="chilistats.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="container">
<div id="logo">
  <h1>ChiliStats(Revived)</h1>
</div>
<div id="menu">
 <ul>
  <li><a href="stats.php">Dashboard</a></li>
  <li><a href="visitors.php">Visitors</a></li>
  <li><a href="history.php">History</a></li> 
 </ul>
</div>
  <div class="middle">
    <h3>Dashboard</h3>
	<table width="100%" border="0" cellpadding="5" cellspacing="0" class="oneview">
      <tr valign="top">
      <?php
      // Determine total visitors
      $abfrage = $conn->prepare("SELECT SUM(user), SUM(view) FROM ".$db_prefix."days");
      $abfrage->execute();
      $result = $abfrage->fetch(PDO::FETCH_NUM);
      $visitors = $result[0];
      $visits = $result[1];
      echo "<td width=\"30%\">Visitors</td><td width=\"20%\">$visitors</td>\n";
      echo "<td width=\"30%\">Visits</td><td width=\"20%\">$visits</td>\n";
      ?>
	  </tr>
	  <tr valign="top">
      <?php
      // Online
      $time = time();
      $isonline = $time - (3 * 60);  // 3 Minuten Online Zeit
      $abfrage = $conn->prepare("SELECT COUNT(visitor_id) FROM ".$db_prefix."visitors WHERE online >= :isonline");
      $abfrage->execute([':isonline' => $isonline]);
      $online = $abfrage->fetchColumn();
      echo "<td>Online</td><td>$online</td>\n";
      echo "<td>&nbsp;</td><td>&nbsp;</td>\n";
      ?>
	  </tr>
	  <tr valign="top">
	  <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
	  </tr>
	  <tr valign="top">
	  <?php
		// Bounce
		$abfrage = $conn->prepare("SELECT COUNT(visitor_id) FROM ".$db_prefix."visitors");
		$abfrage->execute();
		$total = $abfrage->fetchColumn();

		$abfrage = $conn->prepare("SELECT COUNT(visitor_id) FROM ".$db_prefix."visitors WHERE online = time");
		$abfrage->execute();
		$onepage = $abfrage->fetchColumn();

		echo "<td>Bounce</td><td>".round(($onepage/$total)*100,2)."%</td>\n";

		// Page/User and 7 days average
		$from_day = date("Y.m.d", $time  -(7*24*60*60));
		$to_day = date("Y.m.d", $time  - (24*60*60)); // <= without today
		$abfrage = $conn->prepare("SELECT AVG(user), (SUM(view)/SUM(user)) FROM ".$db_prefix."days WHERE day >= :from_day AND day <= :to_day");
		$abfrage->execute([':from_day' => $from_day, ':to_day' => $to_day]);
		$result = $abfrage->fetch(PDO::FETCH_NUM);
		$avg_7 = round($result[0], 2);
		$page_user = round($result[1], 1);

		echo "<td>Page/Visitor</td><td>$page_user</td>\n";
	  ?>
	  </tr>
	  <tr valign="top">
	  <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
	  </tr>
	  <tr valign="top">
	  <?php
		echo "<td>&Oslash; 7 days</td>\n";
		echo "<td>$avg_7</td>\n";
		// 30 days average
		$from_day = date("Y.m.d", $time -(30*24*60*60));
		$to_day = date("Y.m.d", $time - (24*60*60)); // <= without today
		$abfrage = $conn->prepare("SELECT AVG(user) FROM ".$db_prefix."days WHERE day >= :from_day AND day <= :to_day");
		$abfrage->execute([':from_day' => $from_day, ':to_day' => $to_day]);
		$avg_30 = round($abfrage->fetchColumn(), 2);
		echo "<td>&Oslash; 30 days</td>\n";
		echo "<td>$avg_30</td>\n";
	  ?>
	  </tr>
	  <tr valign="top">
	  <?php
		// Total Users Today
		$sel_timestamp = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
		$sel_tag = date("Y.m.d",$sel_timestamp);
		$abfrage = $conn->prepare("SELECT SUM(user) FROM ".$db_prefix."days WHERE day = :sel_tag");
		$abfrage->execute([':sel_tag' => $sel_tag]);
		$today = $abfrage->fetchColumn();
		if ($today == "") $today = 0;
		echo "<td>Today</td><td>$today</td>\n";

		// Yesterday at the same time
		$anfangTag = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 24*60*60 ;
		$endeTag = $time - 24*60*60 ;
		$abfrage = $conn->prepare("SELECT COUNT(visitor_id) FROM ".$db_prefix."visitors WHERE time >= :anfangTag AND time <= :endeTag");
		$abfrage->execute([':anfangTag' => $anfangTag, ':endeTag' => $endeTag]);
		$yesterday = $abfrage->fetchColumn();
		echo "<td>Yesterday (".date("G:i",$time).")</td><td>$yesterday</td>\n";
	  ?>
	  </tr>	
    </table>
  </div>
  <div class="middle">
    <h3>Last 24 hours </h3>
	<table height="200" width="100%" cellpadding="0" cellspacing="0" align="right">
	<tr valign="bottom" height="180">
	<?php
	// Query users from the last 24 hours
	$bar_nr=0;
	$bar_mark="";
	for($Stunde=23; $Stunde>=0; $Stunde--)
	{
		$anfangStunde = mktime(date("H")-$Stunde, 0, 0, date("n"), date("j"), date("Y")) ;
		$endeStunde = mktime(date("H")-$Stunde, 59, 59, date("n"), date("j"), date("Y")) ;
		$abfrage = $conn->prepare("SELECT COUNT(visitor_id) FROM ".$db_prefix."visitors WHERE time >= :anfangStunde AND time <= :endeStunde");
		$abfrage->execute([':anfangStunde' => $anfangStunde, ':endeStunde' => $endeStunde]);
		$User = $abfrage->fetchColumn();
		// Diagramm vorbereiten, Array erstellen
		$bar[$bar_nr] = $User; 
		$bar_title[$bar_nr] = date("G:i",$anfangStunde)." - ".date("G:i",$endeStunde);            
		if (date("H")-$Stunde == 0) $bar_mark = $bar_nr;
		$bar_nr++;
	}
	// Diagramm      
	for($i=0; $i<$bar_nr; $i++)
	{
		$value=$bar[$i];
		if ($value == "") $value = 0;
		if (max($bar) > 0) {$bar_height=round((170/max($bar))*$value);} else $bar_height = 0;
		if ($bar_height == 0) $bar_height = 1;    
		if ($bar_mark == "$i" ) { echo "<td width=\"19\">";}
		else echo "<td width=\"19\">";
		echo "<div class=\"bar\" style=\"height:".$bar_height."px;\" title=\"".$bar_title[$i]." - $value Visitors\"></div></td>\n";
	}   
	?>
    </tr><tr height="20">
	<td colspan="6" width="25%" class="timeline"><?PHP echo date("G:i",mktime(date("H")-23, 0, 0, date("n"), date("j"), date("Y"))); ?></td>
	<td colspan="6" width="25%" class="timeline"><?PHP echo date("G:i",mktime(date("H")-17, 0, 0, date("n"), date("j"), date("Y"))); ?></td>
	<td colspan="6" width="25%" class="timeline"><?PHP echo date("G:i",mktime(date("H")-11, 0, 0, date("n"), date("j"), date("Y"))); ?></td>
	<td colspan="6" width="25%" class="timeline"><?PHP echo date("G:i",mktime(date("H")-5, 0, 0, date("n"), date("j"), date("Y"))); ?></td>
	</tr></table>
  </div>
  <div style="clear:both"></div>
  <div class="full">
    <h3>Last 30 days </h3>
	<table height="230" width="100%" cellpadding="0" cellspacing="0" align="right">
	<tr valign="bottom" height="210">
	<?php
	// Query users from the last 30 days
	$bar_nr=0;
	$bar_mark="";
	for($day=29; $day>=0; $day--)
	{
		$sel_timestamp = mktime(0, 0, 0, date("n"), date("j")-$day, date("Y"));
		$sel_tag = date("Y.m.d",$sel_timestamp);
		$abfrage = $conn->prepare("SELECT SUM(user) FROM ".$db_prefix."days WHERE day = :sel_tag");
		$abfrage->execute([':sel_tag' => $sel_tag]);
		$User = $abfrage->fetchColumn();

		$bar[$bar_nr]=$User; // Im Array Speichern
		$bar_title[$bar_nr] = date("j.M.Y",$sel_timestamp);

		if (date("j")-$day == 1) $bar_mark = $bar_nr;
		if ( date("w", $sel_timestamp) == 6 OR date("w", $sel_timestamp)== 0) {$weekend[$bar_nr]=true;}
		else {$weekend[$bar_nr]=false;}

		$bar_nr++;
	}
	// Diagramm      
	for($i=0; $i<$bar_nr; $i++)
	{
		$value=$bar[$i];
		if ($value == "") $value = 0;
		if (max($bar) > 0) {$bar_height=round((200/max($bar))*$value);} else $bar_height = 0;
		if ($bar_height == 0) $bar_height = 1;    
		if ($bar_mark == "$i" ) { echo "<td width=\"31\">";}
		else echo "<td width=\"31\">";
		echo "<div class=\"bar\" style=\"height:".$bar_height."px;\" title=\"".$bar_title[$i]." - $value Visitors\"></div></td>\n";
	}
	?>
    </tr><tr height="20">
	<td colspan="6" class="timeline"><?PHP echo date("j.M",mktime(0, 0, 0, date("n"), date("j")-29, date("Y"))); ?></td>
	<td colspan="6" class="timeline"><?PHP echo date("j.M",mktime(0, 0, 0, date("n"), date("j")-23, date("Y"))); ?></td>
	<td colspan="6" class="timeline"><?PHP echo date("j.M",mktime(0, 0, 0, date("n"), date("j")-17, date("Y"))); ?></td>
	<td colspan="6" class="timeline"><?PHP echo date("j.M",mktime(0, 0, 0, date("n"), date("j")-11, date("Y"))); ?></td>
	<td colspan="6" class="timeline"><?PHP echo date("j.M",mktime(0, 0, 0, date("n"), date("j")-5, date("Y"))); ?></td>
	</tr></table>
  </div>
  <div style="clear:both"></div>
  <div id="footer"><a href="https://github.com/KuJoe/ChiliStats/" target="_blank" ><img src="github.svg" width="24" height="24" alt="GitHub Logo" title="ChiliStats(Revived)" /></a></div>
</div>
</body>
</html>