# ChiliStats(Revived)
ChiliStats(Revived)

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

ChiliStats is a basic, easy to use analytics script for tracking basic stats about your website visitors.  
This version has been rewritten to work with PHP 8.x and give it some added features and a facelift.

Demo
------------
https://chilistats.kujoe.net/  

Username/Password: admin/admin

Installation
------------

1. Unpack the "chilistats.zip" archive.

2. Open the file "chilistats/config.php" with a text editor and set the settings.  
   !! The database settings MUST be set !!	

3. Upload the complete "chilistats" folder via FTP to your web server.

4. Call the "install.php" in your web browser. e.g. https://example.com/chilistats/install.php  
    All points must be positive (green).

5. The following code must be included on each page:  

```
<script type="text/javascript">
	document.write('<img src="chilistats/counter.php?ref=' + escape(document.referrer) + '" border="0">')
</script>
<noscript><img src="chilistats/counter.php" border="0"></noscript>
```

6. Finished.