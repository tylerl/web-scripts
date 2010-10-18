<?php
	// Change to FALSE to disable adding the "DROP TABLES" commands
	$DROP_TABLES = TRUE;
	$VERSION = "1.0";
	///////////////////////////////////////////////////////////////////////
	// Web MySQLdump
	// 
	// Generates output similar (nearly identical) to that of the command-line
	// mysqldump utility. Just put this script on your server and view it
	// with a web browser. It'll ask you for the parameters for you connection,
	// and then hit the "Run" button to retrieve your database dump.
	//
	// You can also skip the initial question form by specifying the parameters
	// in your query string. Technically only the "db" parameter is required.
	//
	// Note: gzip file encoding simply cannot be done on-the-fly with PHP. If
	// you have any ideas, let me know. 
	//
	// Copyright 2010 by Tyler Larson <devel@tlarson.com>
	// All rights reserved.
	//
	// This code is distributed according to the terms of the the MIT License,
	// a copy of which you can find at the following location:
	//   http://www.opensource.org/licenses/mit-license.html

	if (!empty($_REQUEST['db'])) {

		function on_error($errno, $errstr) { 
			die($errstr);
		}
		set_error_handler('on_error',E_ALL); // make WARNINGs fatal
	
		$HOST = !empty($_REQUEST['host'])? $_REQUEST['host'] : "localhost";
		$USER = !empty($_REQUEST['user'])? $_REQUEST['user'] : "root";
		$PASS = !empty($_REQUEST['pass'])? $_REQUEST['pass'] : "";
		$DB = $_REQUEST['db'];
		$OUTPUT = !empty($_REQUEST['out'])? $_REQUEST['out'] : "sql";

		/* ******************************************** */
		$timer_start = microtime(true);

		mysql_connect($HOST,$USER,$PASS);
		mysql_select_db($DB);
		$q_tables = mysql_query("SHOW TABLE STATUS");
		if (!$q_tables) {
			echo "Database <b>".htmlspecialchars("$DB")."</b> does not exist.";
			exit;
		}
		if (mysql_num_rows($q_tables) == 0) {
			echo "Database has no tables";
			exit;
		}

		header("content-type:text/plain");
		if ($OUTPUT != "txt") {
			$safename=preg_replace("/[^a-zA-Z0-9_-]/","_",$DB);
			header("content-type:text/plain");
			header("content-disposition:attachment;filename=\"$safename.sql\"");
		}

		// Preamble:
		echo "-- Web MySQLdump $VERSION by Tyler Larson\n-- \n";
		echo "-- Host: $HOST    Database: $DB\n";
		echo "-- -------------------------------------------------------\n\n";
		echo <<<END
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

END;
		while ($table = mysql_fetch_object($q_tables)) {
			if ($table->Engine=='') continue; // don't dump views

			echo "\n-- \n-- Table structure for table `$table->Name`\n-- \n\n";

			if ($DROP_TABLES) { 
				echo "DROP TABLE IF EXISTS `$table->Name`;\n";
			}

			echo "/*!40101 SET @saved_cs_client     = @@character_set_client */;\n/*!40101 SET character_set_client = utf8 */;\n";
			$CT = mysql_fetch_row(mysql_query("SHOW CREATE TABLE $table->Name"));
			echo "$CT[1];\n";
			echo "/*!40101 SET character_set_client = @saved_cs_client */;\n";
			
			echo "\n-- \n-- Dumping data for table `$table->Name`\n-- \n\n";
			$q_data = mysql_query("SELECT * FROM `$table->Name`");
			if (mysql_num_rows($q_data) > 0) {
				echo "LOCK TABLES `$table->Name` WRITE;\n";
				echo "/*!40000 ALTER TABLE `$table->Name` DISABLE KEYS */;\n";
				echo "INSERT INTO `$table->Name` VALUES\n";
				$notfirstrow = false;
				while ($data = mysql_fetch_row($q_data)) {
					if ($notfirstrow) { echo ",\n  ("; } else { echo "  ("; $notfirstrow = true; }
					$notfirstcol=false;
					foreach($data as $value) {
						if ($notfirstcol) { echo ","; } else { $notfirstcol=true; }
						echo "'" . mysql_real_escape_string($value) . "'";
					}
					echo ")";
				}
				echo ";\n";
				echo "/*!40000 ALTER TABLE `$table->Name` ENABLE KEYS */;\n";
				echo "UNLOCK TABLES;\n";
			} else {
				echo "-- No data for table `$table->Name`\n\n";
			}
		}
		$timer_end = microtime(true);
		echo "\n-- FINISHED ----------------------------\n-- Total time: ". ($timer_end - $timer_start) . " seconds.\n\n";
		exit();
	}
?>
<html><head><title>Web mysqldump</title>
<style>
body { background: #336890; font-family: sans-serif; }
h1 { font-size: 16pt; text-align:center; margin: 0 0 .5em; }
input { width: 100%; }
#box { width: 300px; border: 1px solid #2A5572; background: #95BCD6; padding: 1em .75em; }
#box th { text-align: right; padding-right: .25em; }
#box table { width: 100%; }
#run { width:6em; }
#trrun { text-align:center; padding-top:1em;}
#footer { font-size: smaller; color: #162E3F; }
#footer a { color: #81A8C1; }
</style>
</head>
<table style="width:100%;height:100%"><tr><td valign="middle" align="center">
<form method="get">
<div id="box">
<h1>Web MySQLdump</h1>
<table>
<tr><th>Host</th><td><input name="host" value="localhost"/></td>
<tr><th>Username</th><td><input name="user"/></td>
<tr><th>Password</th><td><input name="pass"/></td>
<tr><th>Database</th><td><input name="db"/></td>
<tr><th>Output</th><td><select name="out"/>
	<option value="sql">Download SQL file</option>
	<option value="txt">Display as text</option>
</select> </td></tr>
<tr><td colspan=2" id="trrun"><input type="submit" value="Run" id="run"></td></tr>
</table>
</div></form>
<div id="footer">
Copyright &copy; 2010 by Tyler Larson. All rights reserved.<br/>
Find the latest version of this code here:<br/>
<a href="http://github.com/tylerl/web-mysqldump">http://github.com/tylerl/web-mysqldump</a>
</div>
</td></tr>

</table>
</html>
