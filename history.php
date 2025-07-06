<?php
session_start();
if (!isset($_SESSION['staff_id'])) {
  header("Location: index.php");
  exit;
} else {
	define('ChiliAllowed', TRUE);
	require_once('config.php');
	require_once('functions.php');
}

$token = getCSRFToken();

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
<meta name="description" content="ChiliStats(Revived) -  A simple, robust PHP script for tracking website visitor statistics and analytics.">
<link href="chilistats.css" rel="stylesheet" type="text/css" />
<link rel="icon" type="image/x-icon" href="favicon.ico">
</head>
<body>
<div id="container">
<div><img src="logo.png" alt="ChiliStats Logo" style="height:64px;width:auto;margin-bottom:-50px;" /></div>
<div id="menu">
 <ul>
  <li><a href="stats.php">Dashboard</a></li>
  <li><a href="visitors.php">Visitors</a></li>
  <li><a href="history.php">History</a></li>
  <li><a href="logout.php">Logout</a></li>
 </ul>
</div>
  <div class="middle">
    <h3>History</h3>
	<?php
	// Determine total visitors
	$query = $conn->prepare("SELECT SUM(user), SUM(view), MIN(day), AVG(user) FROM days");
	$query->execute();
	$result = $query->fetch(PDO::FETCH_NUM);
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
	  <td colspan="4"><strong>Total since <?php echo $since;?></strong></td>
	  </tr>
	  <tr valign="top">
	  <td width="30%">Visitors</td><td width="20%"><?php echo $visitors; ?></td>
	  <td width="30%">Visits</td><td width="20%"><?php echo $visits; ?></td>
	  </tr>
	  <tr valign="top">
	  <td width="30%">&Oslash; Day</td><td width="20%"><?php echo $total_avg; ?></td>
	  <td width="30%">&nbsp;</td><td width="20%">&nbsp;</td>
	  </tr>
	</table>
	<br />
	<?php
	// Selected Month
	$sel_timestamp = mktime(0, 0, 0, $show_month, 1, $show_year);
	$sel_month = date("Y.m.%",$sel_timestamp);
	$query = $conn->prepare("SELECT SUM(user), SUM(view), AVG(user) FROM days WHERE day LIKE :sel_month");
	$query->execute([':sel_month' => $sel_month]);
	$result = $query->fetch(PDO::FETCH_NUM);
	$visitors = $result[0];
	$visits = $result[1];
	$day_avg = round($result[2], 2);  
	?>
	<table width="100%" border="0" cellpadding="5" cellspacing="0">
	  <tr valign="top">
		<td colspan="4"><strong>Selected is <?php echo date("F Y",mktime(0, 0, 0, $show_month, 1, $show_year)); ?></strong></td>
	  </tr>
	  <tr valign="top">
	    <td>Visitors</td><td><?php echo $visitors; ?></td><td>Visits</td><td><?php echo $visits; ?></td>
	  </tr>
	  <tr valign="top">
		<td>&Oslash; Day</td><td><?php echo $day_avg; ?></td><td>&nbsp;</td><td>&nbsp;</td>
	  </tr>
    </table>
  </div>
  <div class="middle">
    <h3>
	<?php 
	echo "Year ".date("Y",mktime(0, 0, 0, $show_month, 1, $show_year)); 
	
	$back_month=date("n",mktime(0, 0, 0, $show_month, 1, $show_year-1));
	$back_year=date("Y",mktime(0, 0, 0, $show_month, 1, $show_year-1));
	$next_month=date("n",mktime(0, 0, 0, $show_month, 1, $show_year+1));
	$next_year=date("Y",mktime(0, 0, 0, $show_month, 1, $show_year+1));
	
	echo "<span><a href=\"history.php?m=$back_month&y=$back_year\"><</a>&nbsp;<a href=\"history.php?m=$next_month&y=$next_year\">></a></span>";
	?>
	</h3>
	<table height="200" width="100%" cellpadding="0" cellspacing="0" align="right">
	<tr valign="bottom" height="180">
	<?php
	// Max Month
	$query = $conn->prepare("SELECT strftime('%Y-%m', day) AS month, SUM(user) AS user_month FROM days GROUP BY month ORDER BY user_month DESC LIMIT 1");
	$query->execute();
	$max_month = $query->fetch(PDO::FETCH_ASSOC)['user_month'];
	// Month query
	$bar_nr = 0;
	for($month = 1; $month <= 12; $month++) {
		$sel_timestamp = mktime(0, 0, 0, $month, 1, $show_year);
		$sel_month = date("Y.m.%", $sel_timestamp);
		$query = $conn->prepare("SELECT SUM(user) FROM days WHERE day LIKE :sel_month");
		$query->execute([':sel_month' => $sel_month]);
		$User = $query->fetchColumn();

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
	<td colspan="3" width="25%" class="timeline"><?php echo date("M.Y",mktime(0, 0, 0, 1, 1, $show_year)); ?></td>
	<td colspan="3" width="25%" class="timeline"><?php echo date("M.Y",mktime(0, 0, 0, 4, 1, $show_year)); ?></td>
	<td colspan="3" width="25%" class="timeline"><?php echo date("M.Y",mktime(0, 0, 0, 7, 1, $show_year)); ?></td>
	<td colspan="3" width="25%" class="timeline"><?php echo date("M.Y",mktime(0, 0, 0, 10, 1, $show_year)); ?></td>
	</tr></table>
  </div>
  <div style="clear:both"></div>
  <div class="full">
    <h3>
	<?php 
	echo date("F Y",mktime(0, 0, 0, $show_month, 1, $show_year)); 
	
	$back_month=date("n",mktime(0, 0, 0, $show_month-1, 1, $show_year));
	$back_year=date("Y",mktime(0, 0, 0, $show_month-1, 1, $show_year));
	$next_month=date("n",mktime(0, 0, 0, $show_month+1, 1, $show_year));
	$next_year=date("Y",mktime(0, 0, 0, $show_month+1, 1, $show_year));
	
	echo "<span><a href=\"history.php?m=$back_month&y=$back_year\"><</a>&nbsp;<a href=\"history.php?m=$next_month&y=$next_year\">></a></span>";
	?>
	</h3>
	<table height="230" width="100%" cellpadding="0" cellspacing="0" align="right">
	<tr valign="bottom" height="210">
	<?php
	// Display selected month
	$bar_nr = 0;
	$month_days = date('t', mktime(0, 0, 0, $show_month, 1, $show_year));
	for ($day = 1; $day <= $month_days; $day++) {
	$sel_timestamp = mktime(0, 0, 0, $show_month, $day, $show_year);
	$sel_tag = date("Y.m.d", $sel_timestamp);
	$query = $conn->prepare("SELECT SUM(user) FROM days WHERE day = :sel_tag");
	$query->execute([':sel_tag' => $sel_tag]);
	$User = $query->fetchColumn();

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
	<td colspan="6" class="timeline"><?php echo date("j.M",mktime(0, 0, 0, $show_month, 1, $show_year)); ?></td>
	<td colspan="6" class="timeline"><?php echo date("j.M",mktime(0, 0, 0, $show_month, 7, $show_year)); ?></td>
	<td colspan="6" class="timeline"><?php echo date("j.M",mktime(0, 0, 0, $show_month, 13, $show_year)); ?></td>
	<td colspan="6" class="timeline"><?php echo date("j.M",mktime(0, 0, 0, $show_month, 19, $show_year)); ?></td>
	<td colspan="7" class="timeline"><?php echo date("j.M",mktime(0, 0, 0, $show_month, 25, $show_year)); ?></td>
	</tr></table>
  </div>
  <div style="clear:both"></div>
  <div id="footer"><a href="https://github.com/KuJoe/ChiliStats/" target="_blank" ><img src="github.svg" width="24" height="24" alt="GitHub Logo" title="ChiliStats(Revived)" /></a></div>
</div>
</body>
</html>