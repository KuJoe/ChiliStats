<?PHP
require_once('config.php');

// Get Month and Year
$time=time();
if (!empty($_GET["m"])) {
	if (is_numeric($_GET["m"]) AND $_GET["m"] >= 1 AND $_GET["m"] <= 12 ) {
		$show_month = $_GET["m"];
	}
} else {
	$show_month = date("n", $time);
}
if (!empty($_GET["y"])) {
	if (is_numeric($_GET["y"]) AND $_GET["y"] >= 1 AND $_GET["y"] <= 9999 ) {
		$show_year = $_GET["y"];
	}
} else {
	$show_year = date("Y", $time);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>ChiliStats(Revived) - History</title>
<link href="chilistats.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="container">
<div id="logo"><h1>ChiliStats(Revived)</h1></div>
<div id="menu">
 <ul>
  <li><a href="stats.php">Dashboard</a></li>
  <li><a href="visitors.php">Visitors</a></li>
  <li><a href="history.php">History</a></li> 
 </ul>
</div>
  <div class="middle">
    <h3>History</h3>
	<?PHP
	// Determine total visitors
	$abfrage = $conn->prepare("SELECT SUM(user), SUM(view), MIN(day), AVG(user) FROM ".$db_prefix."days");
	$abfrage->execute();
	$result = $abfrage->fetch(PDO::FETCH_NUM);
	$visitors = $result[0];
	$visits = $result[1];
	$since = $result[2];
	$since = str_replace(".", "-", $since);
	$since = strtotime($since);
	$since = date("d F Y", $since);
	$total_avg = round($result[3], 2);
	?>
	<table width="100%" border="0" cellpadding="5" cellspacing="0">
      <tr valign="top">
	  <td colspan="4"><strong>Total since <?PHP echo $since;?></strong></td>
	  </tr>
	  <tr valign="top">
	  <td width="30%">Visitors</td><td width="20%"><?PHP echo $visitors; ?></td>
	  <td width="30%">Visits</td><td width="20%"><?PHP echo $visits; ?></td>
	  </tr>
	  <tr valign="top">
	  <td width="30%">&Oslash; Day</td><td width="20%"><?PHP echo $total_avg; ?></td>
	  <td width="30%">&nbsp;</td><td width="20%">&nbsp;</td>
	  </tr>
	</table>
	<br />
	<?PHP
	// Selected Month
	$sel_timestamp = mktime(0, 0, 0, $show_month, 1, $show_year);
	$sel_month = date("Y.m.%",$sel_timestamp);
	$abfrage = $conn->prepare("SELECT SUM(user), SUM(view), AVG(user) FROM ".$db_prefix."days WHERE day LIKE :sel_month");
	$abfrage->execute([':sel_month' => $sel_month]);
	$result = $abfrage->fetch(PDO::FETCH_NUM);
	$visitors = $result[0];
	$visits = $result[1];
	$day_avg = round($result[2], 2);  
	?>
	<table width="100%" border="0" cellpadding="5" cellspacing="0">
	  <tr valign="top">
		<td colspan="4"><strong>Selected is <?PHP echo date("F Y",mktime(0, 0, 0, $show_month, 1, $show_year)); ?></strong></td>
	  </tr>
	  <tr valign="top">
	    <td>Visitors</td><td><?PHP echo $visitors; ?></td><td>Visits</td><td><?PHP echo $visits; ?></td>
	  </tr>
	  <tr valign="top">
		<td>&Oslash; Day</td><td><?PHP echo $day_avg; ?></td><td>&nbsp;</td><td>&nbsp;</td>
	  </tr>
    </table>
  </div>
  <div class="middle">
    <h3>
	<?PHP 
	echo "Year ".date("Y",mktime(0, 0, 0, $show_month, 1, $show_year)); 
	
	$back_month=date("n",mktime(0, 0, 0, $show_month, 1, $show_year-1));
	$back_yaer=date("Y",mktime(0, 0, 0, $show_month, 1, $show_year-1));
	$next_month=date("n",mktime(0, 0, 0, $show_month, 1, $show_year+1));
	$next_yaer=date("Y",mktime(0, 0, 0, $show_month, 1, $show_year+1));
	
	echo "<span><a href=\"history.php?m=$back_month&y=$back_yaer\"><</a>&nbsp;<a href=\"history.php?m=$next_month&y=$next_yaer\">></a></span>";
	?>
	</h3>
	<table height="200" width="100%" cellpadding="0" cellspacing="0" align="right">
	<tr valign="bottom" height="180">
	<?PHP
	// Max Month
	$abfrage = $conn->prepare("SELECT LEFT(day,7) as month, SUM(user) as user_month FROM ".$db_prefix."days GROUP BY month ORDER BY user_month DESC LIMIT 1");
	$abfrage->execute();
	$max_month = $abfrage->fetch(PDO::FETCH_ASSOC)['user_month'];
	// Month query
	$bar_nr = 0;
	for($month = 1; $month <= 12; $month++) {
		$sel_timestamp = mktime(0, 0, 0, $month, 1, $show_year);
		$sel_month = date("Y.m.%", $sel_timestamp);
		$abfrage = $conn->prepare("SELECT SUM(user) FROM ".$db_prefix."days WHERE day LIKE :sel_month");
		$abfrage->execute([':sel_month' => $sel_month]);
		$User = $abfrage->fetchColumn();

		$bar[$bar_nr] = $User; // Save in array
		$bar_title[$bar_nr] = date("M.Y", $sel_timestamp);
		$bar_month[$bar_nr] = $month;

		$bar_nr++;
	}
	// Diagram
	for($i = 0; $i < $bar_nr; $i++) {
		$value = $bar[$i];
		if ($value == "") $value = 0;
		if ($max_month > 0) {$bar_height = round((170/$max_month)*$value);} else $bar_height = 0;
		if ($bar_height == 0) $bar_height = 1;

		echo "<td width=\"38\">";
		echo "<a href=\"history.php?m=".$bar_month[$i]."&y=$show_year\">";
		echo "<div class=\"bar\" style=\"height:".$bar_height."px;\" title=\"".$bar_title[$i]." - $value Visitors\"></div>";
		echo "</a></td>\n";
	}
	?>
    </tr><tr height="20">
	<td colspan="3" width="25%" class="timeline"><?PHP echo date("M.Y",mktime(0, 0, 0, 1, 1, $show_year)); ?></td>
	<td colspan="3" width="25%" class="timeline"><?PHP echo date("M.Y",mktime(0, 0, 0, 4, 1, $show_year)); ?></td>
	<td colspan="3" width="25%" class="timeline"><?PHP echo date("M.Y",mktime(0, 0, 0, 7, 1, $show_year)); ?></td>
	<td colspan="3" width="25%" class="timeline"><?PHP echo date("M.Y",mktime(0, 0, 0, 10, 1, $show_year)); ?></td>
	</tr></table>
  </div>
  <div style="clear:both"></div>
  <div class="full">
    <h3>
	<?PHP 
	echo date("F Y",mktime(0, 0, 0, $show_month, 1, $show_year)); 
	
	$back_month=date("n",mktime(0, 0, 0, $show_month-1, 1, $show_year));
	$back_yaer=date("Y",mktime(0, 0, 0, $show_month-1, 1, $show_year));
	$next_month=date("n",mktime(0, 0, 0, $show_month+1, 1, $show_year));
	$next_yaer=date("Y",mktime(0, 0, 0, $show_month+1, 1, $show_year));
	
	echo "<span><a href=\"history.php?m=$back_month&y=$back_yaer\"><</a>&nbsp;<a href=\"history.php?m=$next_month&y=$next_yaer\">></a></span>";
	?>
	</h3>
	<table height="230" width="100%" cellpadding="0" cellspacing="0" align="right">
	<tr valign="bottom" height="210">
	<?PHP
	// Display selected month
	$bar_nr = 0;
	$month_days = date('t', mktime(0, 0, 0, $show_month, 1, $show_year));
	for ($day = 1; $day <= $month_days; $day++) {
	$sel_timestamp = mktime(0, 0, 0, $show_month, $day, $show_year);
	$sel_tag = date("Y.m.d", $sel_timestamp);
	$abfrage = $conn->prepare("SELECT SUM(user) FROM ".$db_prefix."days WHERE day = :sel_tag");
	$abfrage->execute([':sel_tag' => $sel_tag]);
	$User = $abfrage->fetchColumn();

	$bar[$bar_nr] = $User; // Save in array
	$bar_nr++;
	}
	// Diagramm 		
	for($i=0; $i<$bar_nr; $i++)
		{
		$value=$bar[$i];
		if ($value == "") $value = 0;
		if (max($bar) > 0) {$bar_height=round((200/max($bar))*$value);} else $bar_height = 0;
		if ($bar_height == 0) $bar_height = 1;	
		echo "<td width=\"30\">";
		$barTitle = isset($bar_title[$i]) ? $bar_title[$i] : '0';
		echo "<div class=\"bar\" style=\"height:".$bar_height."px;\" title=\"".$barTitle." - $value Visitors\"></div></td>\n";
		}
	?>
    </tr><tr height="20">
	<td colspan="6" class="timeline"><?PHP echo date("j.M",mktime(0, 0, 0, $show_month, 1, $show_year)); ?></td>
	<td colspan="6" class="timeline"><?PHP echo date("j.M",mktime(0, 0, 0, $show_month, 7, $show_year)); ?></td>
	<td colspan="6" class="timeline"><?PHP echo date("j.M",mktime(0, 0, 0, $show_month, 13, $show_year)); ?></td>
	<td colspan="6" class="timeline"><?PHP echo date("j.M",mktime(0, 0, 0, $show_month, 19, $show_year)); ?></td>
	<td colspan="7" class="timeline"><?PHP echo date("j.M",mktime(0, 0, 0, $show_month, 25, $show_year)); ?></td>
	</tr></table>
  </div>
  <div style="clear:both"></div>
  <div id="footer"><a href="https://github.com/KuJoe/ChiliStats/" target="_blank" ><img src="github.svg" width="24" height="24" alt="GitHub Logo" title="ChiliStats(Revived)" /></a></div>
</div>
</body>
</html>