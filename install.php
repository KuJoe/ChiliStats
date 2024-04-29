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

$filename = 'LOCKED';
if (file_exists($filename)) {
    die("The directory is locked. Please delete the LOCKED file if you are sure you need to run the install.php file (this might overrite existing data in the database if it exists).");
}

require_once('config.php');

try {
	$conn = new PDO("sqlite:$db_file_path");
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	echo "<font color=\"#00CC00\">- The SQLite database connection was established successfully!</font><br>";
	$tables = [
	'days' => "CREATE TABLE days (
	  day_id INTEGER PRIMARY KEY AUTOINCREMENT,
	  day TEXT NOT NULL DEFAULT '',
	  user INTEGER NOT NULL DEFAULT 0,
	  view INTEGER NOT NULL DEFAULT 0
	)",

	'visitors' => "CREATE TABLE visitors (
	  visitor_id INTEGER PRIMARY KEY AUTOINCREMENT,
	  ipaddr TEXT NOT NULL DEFAULT '',
	  time INTEGER NOT NULL DEFAULT 0,
	  online INTEGER NOT NULL DEFAULT 0
	)",

	'languages' => "CREATE TABLE languages (
	  lang_id INTEGER PRIMARY KEY AUTOINCREMENT,
	  day TEXT NOT NULL DEFAULT '',
	  language TEXT NOT NULL DEFAULT '',
	  view INTEGER NOT NULL DEFAULT 0
	)",

	'pages' => "CREATE TABLE pages (
	  page_id INTEGER PRIMARY KEY AUTOINCREMENT,
	  day TEXT NOT NULL DEFAULT '',
	  page TEXT NOT NULL DEFAULT '',
	  view INTEGER NOT NULL DEFAULT 0
	)",

	'staff' => "CREATE TABLE staff (
	  staff_id INTEGER PRIMARY KEY AUTOINCREMENT,
	  seckey TEXT NOT NULL DEFAULT '',
	  user_email TEXT NOT NULL DEFAULT '',
	  user_password_hash TEXT NOT NULL DEFAULT 0,
	  user_active INTEGER NOT NULL DEFAULT 0,
	  user_rememberme_token TEXT NOT NULL DEFAULT 0,
	  user_ip TEXT NOT NULL DEFAULT 0,
	  user_lastlogin TEXT NULL DEFAULT NULL,
	  user_failed_logins INTEGER NOT NULL DEFAULT 0,
	  user_locked DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
	  unique_token TEXT NULL DEFAULT NULL
	)"
	];
	foreach ($tables as $name => $sql) {
		$stmt = $conn->prepare($sql);
		$stmt->execute();
		echo "<font color=\"#00CC00\">- Table ".$name." was created successfully!<br>";
	}
  
} catch(PDOException $e) {
	echo "<font color=\"#CC0000\">- " . $e->getMessage() . "</font><br>";
}
$file = fopen('LOCKED', 'w');
if ($file == false) {
    echo "<font color=\"#CC0000\">- Unable to lock the directory to prevent the install.php script from being run again. Either manually create a file named <strong>LOCKED</strong> in this directory or delete the install.php to be safe.</font><br>";
} else {
    echo "<font color=\"#00CC00\">- Lock file created to prevent the install.php file from being run again. You can delete the install.php file just to safe.<br>";
    fclose($file);
}
?>
<p>
Copy and Paste: <br />
<textarea name="textfield" cols="80" rows="5" wrap="off" readonly="readonly"><script type="text/javascript">
document.write('<img src="chilistats/counter.php?ref=' + escape(document.referrer) + '">')
</script>
<noscript><img src="chilistats/counter.php" /></noscript></textarea>
</p>
<div id="footer"><a href="https://github.com/KuJoe/ChiliStats/" target="_blank" ><img src="github.svg" width="24" height="24" alt="GitHub Logo" title="ChiliStats(Revived)" /></a></div>