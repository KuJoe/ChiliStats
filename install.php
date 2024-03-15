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
    // Connect to the server
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<font color=\"#00CC00\">- The DB server was reached successfully!</font><br>";

    // Create tables
    $tables = [
        'days' => "CREATE TABLE `".$db_prefix."days` (
            `day_id` int(11) NOT NULL auto_increment,
            `day` varchar(10) NOT NULL default '',
            `user` int(10) NOT NULL default '0',
            `view` int(10) NOT NULL default '0',
            PRIMARY KEY  (`day_id`)
        )",

        'visitors' => "CREATE TABLE `".$db_prefix."visitors` (
            `visitor_id` int(11) NOT NULL auto_increment,
            `ipaddr` varchar(45) NOT NULL default '',
            `time` int(20) NOT NULL default '0',
            `online` int(20) NOT NULL default '0',
            PRIMARY KEY  (`visitor_id`)
        )",

		'languages' => "CREATE TABLE `".$db_prefix."languages` (
            `lang_id` int(11) NOT NULL auto_increment,
            `day` varchar(10) NOT NULL default '',
            `language` varchar(2) NOT NULL default '',
            `view` int(10) NOT NULL default '0',
            PRIMARY KEY  (`lang_id`)
        )",

		'pages' => "CREATE TABLE `".$db_prefix."pages` (
            `page_id` int(11) NOT NULL auto_increment,
            `day` varchar(10) NOT NULL default '',
            `page` varchar(255) NOT NULL default '',
            `view` int(20) NOT NULL default '0',
            PRIMARY KEY  (`page_id`)
        )",

        'staff' => "CREATE TABLE `".$db_prefix."staff` (
            `staff_id` int(11) NOT NULL auto_increment,
            `seckey` varchar(12) NOT NULL default '',
            `user_email` varchar(64) NOT NULL default '',
            `user_password_hash` varchar(255) NOT NULL default '0',
            `user_active` tinyint(1) NOT NULL default '0',
            `user_rememberme_token` varchar(64) NOT NULL default '0',
            `user_ip` varchar(45) NOT NULL default '0',
            `user_lastlogin` timestamp NULL default NULL,
            `user_failed_logins` tinyint(1) NOT NULL default '0',
            `user_locked` datetime NOT NULL default '1970-01-01 00:00:01',
            `unique_token` varchar(32) NULL default NULL,
            PRIMARY KEY  (`staff_id`)
        )"
		
    ];

    foreach ($tables as $name => $sql) {
        $pdo->exec($sql);
        echo "<font color=\"#00CC00\">- Table ".$db_prefix.$name." was created successfully!<br>";
    }
} catch (PDOException $e) {
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