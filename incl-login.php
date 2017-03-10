
<?php

	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);


	if( fun_test_server() == 0 ) {
		print "<p><font face=arial size=+3 color=993300><b>!</b></font>&nbsp;<font face=arial size=+2><b>Data server is offline.</font></b>";
		die("</td></tr></table>");
		}

	if(gettype($db) == 'NULL') {
		$db = new dbLink(); }
	$zsql = "SELECT strikes, blocked FROM newsnet.sessions WHERE DECODE(session_id,'$encrypt_code') = '$nnid' ";
	$line = $db->getRow($zsql);
	$strikes = $line[strikes];

	/* User's session has been blocked */
	if(($blocked == 1) || ($line[blocked] == 1)) {
		print "<p><b><font face=arial size=+1>Warning</font></b>";
		print "<br><font face=arial color=993300 size=-1>A system administrator has determined that you are no longer authorized to view this web site. To regain your privileges, please contact the appropriate system administrator.</font></td></tr></table>";
		die();
	}

	/* User has not logged in and will be blocked because of too many login errors */
	if($strikes >= 5) {
		print "<p><b><font face=arial size=+1>Warning</font></b>";
		print "<br><font face=arial color=993300 size=-1>Too many failed login attempts. Try again in two hours. Only authorized users may access this site.</font></td></tr></table>";
		die();
	}


	/* User has not logged in */
	if (!$auth) {
		$page = $_SERVER['REQUEST_URI'];

		print "<div id=d1 style='min-height:550px; height:auto;'>";
		print "<form name=frmlogin action=\"$page\" method=post>";
		print "<input type=hidden name=logoutaction value=''>";
		print "<table cellspacing=5 border=0 cellpadding=5><tr><td>";
		print "<p><br><b><font face=arial size=+2>Login to eFOIA</font></b>";
		if ($logintry) {
			print "<br><b><font face=arial color=993300>! </font> <font face=arial>Login attempt failed. Invalid username or password.</font></b>";
		} else {
			print "<br>Sign on using your username and password."; }
		print "<table cellspacing=0 cellpadding=3 border=0>";
		print "<tr><td width=80>Username</td><td><input type=text name=\"liusr\" size=30></td></tr>";
		print "<tr><td>Password</td><td><input type=password name=\"lipw\" size=30></td></tr>";
		print "<tr><td></td><td><input type=submit value=\"Login\"></td></tr>";
		print "</table>";
		print "<input type=hidden name=\"sql\" value=\"$sql\">";
		print "<input type=hidden name=\"database\" value=\"$database\">";
		print "</form>";
		print "</td></tr></table>";
		print "<script language=javascript>document.frmlogin.liusr.focus();</script>";
		print "</div>";
		require_once("incl-foia-bottom.php");
		die();
	}

	/* User has logged in, but account access is blocked */
	if ($blocked == 1) {
		print "<p><font size=+3 face=arial color=993300><b>BLOCKED</b></font>";
		print "<br>Your access to eFOIA has been temporarily blocked. Please contact your system administrator to restore privliges on the site. For now, please log out.";
		print "<form action=logout.php>";
		print "<input type=hidden name=\"logoutaction\" value=\"LOGOUT\">";
		print "<center><input type=submit value=\"Log out of eFOIA\"></center> ";
		print "</form>";
		$auth = 0;
		die ("</td></tr></table>");
	}

	/* User has logged in and has a default password which must be changed */
	if (($newuser == 1) && ($continue <> "ok")) {
		print "<p><font size=+2 face=arial color=993300><b>Welcome to eFOIA</b></font>";
		print "<br>Welcome to eFOIA. There are just a few housekeeping items to get out of the way before you can begin using the site. For security reasons, you must change your password before you continue.";
		print "<p><form action=admin-passwordchange.php method=post><input type=hidden name=\"continue\" value=\"ok\"><center><input type=hidden value=\"PASSWORD\" name=\"pwchangefrom\"><input type=submit value=\"Change My Password\"></center></form>";
		die("</td></tr></table>");
	}





?>


