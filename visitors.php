<?PHP
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>ChiliStats(Revived) - Visitors</title>
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
  	<h3>Top 10 Pages</h3>
	<table width="100%" cellpadding="5" cellspacing="0">
  	<tr>
      <td width="30"><strong>#</strong></td>
      <td width="280"><strong>Page</strong></td>
      <td width="120"><strong>%</strong></td>
  	</tr>
	<?php
	// Total Pages
	$abfrage = $conn->prepare("SELECT SUM(view) FROM pages");
	$abfrage->execute();
	$ges_page = $abfrage->fetchColumn();

	// Top Pages
	$nr = 1;
	$abfrage = $conn->prepare("SELECT page, SUM(view) AS views FROM pages GROUP BY page ORDER BY views DESC LIMIT 0, 10");
	$abfrage->execute();
	while($row = $abfrage->fetch(PDO::FETCH_ASSOC))
	{
		$page = htmlspecialchars($row['page']);
		if(strlen($page) > 35) {
			$shortpage = "<a href=\"#\" title=\"$page\">...</a>".substr($page,strlen($page)-30,strlen($page));
		} else {
			$shortpage = $page;
		}
		$views = $row['views'];
		$percent = (100/$ges_page)*$views;
		if ($percent < 0.1 ) $percent = round($percent,2);
		else $percent = round($percent,1);
		$bar_width = round((100/$ges_page)*$views);
		echo"   <tr>\n";
		echo"       <td>$nr</td>\n";
		echo"       <td>$shortpage</td>\n";
		echo"       <td nowrap><div class=\"vbar\" style=\"width:".$bar_width."px;\" title=\"$views Visits\" >&nbsp;$percent%</div></td>\n";
		echo"   </tr>\n";
		$nr++;
	}
	?>
	</table>
  </div>
  <div class="middle">
  <h3>Top 10 Languages</h3>
	<table width="100%" border="0" cellpadding="5" cellspacing="0">
      <tr>
        <td width="30"><strong>#</strong></td>
        <td width="280"><strong>Language</strong></td>
        <td width="120"><strong>%</strong></td>
      </tr>
	<?php
	// Total Languages
	$abfrage = $conn->prepare("SELECT SUM(view) FROM languages");
	$abfrage->execute();
	$ges_language = $abfrage->fetchColumn();

	// Code to Language
	$code2lang = array(
		'ar'=>'Arabic',
		'bn'=>'Bengali',
		'bg'=>'Bulgarian',
		'zh'=>'Chinese',
		'cs'=>'Czech',
		'da'=>'Danish',
		'en'=>'English',
		'et'=>'Estonian',
		'fi'=>'Finnish',
		'fr'=>'French',
		'de'=>'German',
		'el'=>'Greek',
		'hi'=>'Hindi',
		'id'=>'Indonesian',
		'it'=>'Italian',
		'ja'=>'Japanese',
		'kg'=>'Korean',
		'nb'=>'Norwegian',
		'nl'=>'Nederlands',
		'pl'=>'Polish',
		'pt'=>'Portuguese',
		'ro'=>'Romanian',
		'ru'=>'Russian',
		'sr'=>'Serbian',
		'sk'=>'Slovak',
		'es'=>'Spanish',
		'sv'=>'Swedish',    
		'th'=>'Thai',
		'tr'=>'Turkish',
		''=>'');

	// Top Languages
	$nr = 1;
	$abfrage = $conn->prepare("SELECT language, SUM(view) AS views FROM languages GROUP BY language ORDER BY views DESC LIMIT 0, 10");
	$abfrage->execute();
	while($row = $abfrage->fetch(PDO::FETCH_ASSOC))
	{
		$language = $row['language'];
		if (array_key_exists($language, $code2lang)) $language = $code2lang[$language];
		$views = $row['views'];
		$percent = (100/$ges_language)*$views;
		if ($percent < 0.1 ) $percent = round($percent,2);
		else $percent = round($percent,1);
		$bar_width = round((100/$ges_language)*$views);
		echo"   <tr>\n";
		echo"       <td>$nr</td>\n";
		echo"       <td>$language</td>\n";
		echo"       <td nowrap><div class=\"vbar\" style=\"width:".$bar_width."px;\" title=\"$views Visitors\" >&nbsp;$percent%</div></td>\n";
		echo"   </tr>\n";
		$nr++;
	}
	?>	  
	</table>
  </div>
  <div style="clear:both"></div>
  <div id="footer"><a href="https://github.com/KuJoe/ChiliStats/" target="_blank" ><img src="github.svg" width="24" height="24" alt="GitHub Logo" title="ChiliStats(Revived)" /></a></div>
</div>
</body>
</html>