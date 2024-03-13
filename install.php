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
        ) TYPE=MyISAM COMMENT='ChilliStats Days'",

        'visitors' => "CREATE TABLE `".$db_prefix."visitors` (
            `visitor_id` int(11) NOT NULL auto_increment,
            `ipaddr` varchar(45) NOT NULL default '',
            `time` int(20) NOT NULL default '0',
            `online` int(20) NOT NULL default '0',
            PRIMARY KEY  (`visitor_id`)
        ) TYPE=MyISAM COMMENT='ChiliStats Visitors'",

		'languages' => "CREATE TABLE `".$db_prefix."languages` (
            `lang_id` int(11) NOT NULL auto_increment,
            `day` varchar(10) NOT NULL default '',
            `language` int(2) NOT NULL default '',
            `view` int(10) NOT NULL default '0',
            PRIMARY KEY  (`lang_id`)
        ) TYPE=MyISAM COMMENT='ChiliStats Languages'",

		'pages' => "CREATE TABLE `".$db_prefix."pages` (
            `page_id` int(11) NOT NULL auto_increment,
            `day` varchar(10) NOT NULL default '',
            `page` var(255) NOT NULL default '',
            `view` int(20) NOT NULL default '0',
            PRIMARY KEY  (`page_id`)
        ) TYPE=MyISAM COMMENT='ChiliStats Pages'"
		
    ];

    foreach ($tables as $name => $sql) {
        $pdo->exec($sql);
        echo "<font color=\"#00CC00\">- Table ".$db_prefix.$name." was created successfully!<br>";
    }
} catch (PDOException $e) {
    echo "<font color=\"#CC0000\">- " . $e->getMessage() . "</font><br>";
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