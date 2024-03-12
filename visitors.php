<?PHP
require_once('config.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>ChiliStats - Visitors</title>
<link href="chilistats.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="container">
<div id="logo"><h1>ChiliStats</h1></div>
<div id="menu">
 <ul>
  <li><a href="stats.php">OneView</a></li>
  <li><a href="visitors.php">Visitors</a></li>
  <li><a href="history.php">History</a></li> 
 </ul>
</div>
  <div class="middle">
    <h3>Referrer Top10</h3>
	<table width="100%" border="0" cellpadding="5" cellspacing="0">
	<tr>
      <td width="30"><strong>Nr.</strong></td>
      <td width="280"><strong>Referrer</strong></td>
      <td width="120"><strong>Prozent</strong></td>
    </tr>
    <?php
	// gesammt Referrer
	$abfrage = $conn->prepare("SELECT SUM(view) FROM ".$db_prefix."Referer");
	$abfrage->execute();
	$ges_referer = $abfrage->fetchColumn();

	// Top Refferrer
	$nr = 1;
	$abfrage = $conn->prepare("SELECT referer, SUM(view) AS views FROM ".$db_prefix."Referer GROUP BY referer ORDER BY views DESC LIMIT 0, 10");
	$abfrage->execute();
	while($row = $abfrage->fetch(PDO::FETCH_ASSOC))
	{
		$referer = htmlspecialchars($row['referer']);
		if(strlen($referer) > 35) {
			$shortreferer = substr($referer,0,30)."<a href=\"#\" title=\"$referer\">...</a>";
		} else {
			$shortreferer = $referer;
		}
		$views = $row['views'];
		$prozent = (100/$ges_referer)*$views;
		if ($prozent < 0.1 ) $prozent = round($prozent,2);
		else $prozent = round($prozent,1);
		$bar_width = round((100/$ges_referer)*$views);
		echo"   <tr>\n";
		echo"       <td>$nr</td>\n";
		echo"       <td>$shortreferer</td>\n";
		echo"       <td nowrap><div class=\"vbar\" style=\"width:".$bar_width."px;\" title=\"$views Visitors\" >&nbsp;$prozent%</div></td>\n";
		echo"   </tr>\n";
		$nr++;
	}
	?>
    </table>
  </div>
  <div class="middle">
    <h3>Pages Top10</h3>
	<table width="100%" cellpadding="5" cellspacing="0">
  	<tr>
      <td width="30"><strong>Nr.</strong></td>
      <td width="280"><strong>Page</strong></td>
      <td width="120"><strong>Prozent</strong></td>
  	</tr>
	<?php
	// gesammt Pages
	$abfrage = $conn->prepare("SELECT SUM(view) FROM ".$db_prefix."Page");
	$abfrage->execute();
	$ges_page = $abfrage->fetchColumn();

	// Top Pages
	$nr = 1;
	$abfrage = $conn->prepare("SELECT page, SUM(view) AS views FROM ".$db_prefix."Page GROUP BY page ORDER BY views DESC LIMIT 0, 10");
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
		$prozent = (100/$ges_page)*$views;
		if ($prozent < 0.1 ) $prozent = round($prozent,2);
		else $prozent = round($prozent,1);
		$bar_width = round((100/$ges_page)*$views);
		echo"   <tr>\n";
		echo"       <td>$nr</td>\n";
		echo"       <td>$shortpage</td>\n";
		echo"       <td nowrap><div class=\"vbar\" style=\"width:".$bar_width."px;\" title=\"$views Visits\" >&nbsp;$prozent%</div></td>\n";
		echo"   </tr>\n";
		$nr++;
	}
	?>
	</table>
  </div>
  <div style="clear:both"></div>
   <div class="middle">
    <h3>Keywords Top10</h3>
	<table width="100%" border="0" cellpadding="5" cellspacing="0">
      <tr>
        <td width="30"><strong>Nr.</strong></td>
        <td width="280"><strong>Keywords</strong></td>
        <td width="120"><strong>Prozent</strong></td>
      </tr>
	<?php
	// gesammt keywords
	$abfrage = $conn->prepare("SELECT SUM(view) FROM ".$db_prefix."Keyword");
	$abfrage->execute();
	$ges_keyword = $abfrage->fetchColumn();

	// Top Keywords
	$nr = 1;
	$abfrage = $conn->prepare("SELECT keyword, SUM(view) AS views FROM ".$db_prefix."Keyword GROUP BY keyword ORDER BY views DESC LIMIT 0, 10");
	$abfrage->execute();
	while($row = $abfrage->fetch(PDO::FETCH_ASSOC))
	{
		$keyword = urldecode($row['keyword']);
		if(strlen($keyword) > 35) {
			$shortkeyword = substr($keyword,0,30)."<a href=\"#\" title=\"$keyword\">...</a>";
		} else {
			$shortkeyword = $keyword;
		}
		$views = $row['views'];
		$prozent = (100/$ges_keyword)*$views;
		if ($prozent < 0.1 ) $prozent = round($prozent,2);
		else $prozent = round($prozent,1);
		$bar_width = round((100/$ges_keyword)*$views);
		echo"   <tr>\n";
		echo"       <td>$nr</td>\n";
		echo"       <td>$shortkeyword</td>\n";
		echo"       <td nowrap><div class=\"vbar\" style=\"width:".$bar_width."px;\" title=\"$views Visitors\" >&nbsp;$prozent%</div></td>\n";
		echo"   </tr>\n";
		$nr++;
	}
	?>	  
    </table>
  </div>
  <div class="middle">
    <h3>Languages Top10</h3>
	<table width="100%" border="0" cellpadding="5" cellspacing="0">
      <tr>
        <td width="30"><strong>Nr.</strong></td>
        <td width="280"><strong>Language</strong></td>
        <td width="120"><strong>Prozent</strong></td>
      </tr>
	<?php
	// gesammt Languages
	$abfrage = $conn->prepare("SELECT SUM(view) FROM ".$db_prefix."Language");
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
	$abfrage = $conn->prepare("SELECT language, SUM(view) AS views FROM ".$db_prefix."Language GROUP BY language ORDER BY views DESC LIMIT 0, 10");
	$abfrage->execute();
	while($row = $abfrage->fetch(PDO::FETCH_ASSOC))
	{
		$language = $row['language'];
		if (array_key_exists($language, $code2lang)) $language = $code2lang[$language];
		$views = $row['views'];
		$prozent = (100/$ges_language)*$views;
		if ($prozent < 0.1 ) $prozent = round($prozent,2);
		else $prozent = round($prozent,1);
		$bar_width = round((100/$ges_language)*$views);
		echo"   <tr>\n";
		echo"       <td>$nr</td>\n";
		echo"       <td>$language</td>\n";
		echo"       <td nowrap><div class=\"vbar\" style=\"width:".$bar_width."px;\" title=\"$views Visitors\" >&nbsp;$prozent%</div></td>\n";
		echo"   </tr>\n";
		$nr++;
	}
	?>	  
	</table>
  </div>
  <div style="clear:both"></div>
  <div id="footer">ChiliStats by <a href="http://www.chiliscripts.com" target="_blank" >ChiliScripts.com</a></div>
</div>
</body>
</html>