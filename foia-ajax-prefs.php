<?

	include('incl-dbconnect.php');

	extract($_GET);
	extract($_POST);

	include('incl-auth.php');


	if($auth) {

		$db = new dbLink();
	
	if($type == "prefs") {
		print "divprefs|";

		$sql = "SELECT * FROM foia.user_prefs WHERE user='$nnusr' ";
		$line = $db->getRow($sql);
		$phone = stripslashes($line[phone]);
		$name = stripslashes($line[name]);
		$email = stripslashes($line[email]);
		$address = stripslashes($line[address]);
		$city = stripslashes($line[city]);
		$state = stripslashes($line[state]);
		$zip = stripslashes($line[zip]);
		$dc_password = stripslashes($line[dc_password]);
		$email_alerts = $line[email_alerts];
		$email_new_group_report = $line[email_new_group_report];
		$dc_password = $line[dc_password];

		print "<span style='font-family: arial; font-size:10pt'><b>Change Your Preferences</b><br><br>";
		print "<table cellspacing=0 cellpadding=1 border=0 width=100% style='font-family:arial; font-size:10pt'>";
		print "<tr><td>Your Name</td><td><input type=text value=\"$name\" id=name size=45></td></tr>";
		print "<tr><td>Phone</td><td><input type=text value=\"$phone\" id=phone size=45></td></tr>";
		print "<tr><td>E-Mail</td><td><input type=text value=\"$email\" id=email size=45></td></tr>";
		print "<tr><td>Address</td><td><input type=text value=\"$address\" id=address size=45></td></tr>";
		print "<tr><td>City</td><td><input type=text value=\"$city\" id=city size=30></td></tr>";
		print "<tr><td>State</td><td><input type=text id=state value=\"$state\" size=4>&nbsp;&nbsp;&nbsp;&nbsp;ZIP Code&nbsp;&nbsp;&nbsp;<input type=text value=\"$zip\" id=zip size=8></td></tr>";
		print "</table>";
		print "<br /><b>E-Mail Preferences</b><br />";
		print "<table cellspacing=0 cellpadding=1 border=0 style='font-size:10pt;'>";
		print "<tr><td>Send e-mail reminders&nbsp;&nbsp;</td><td>";
			print "<select id=email_alerts >";
				if($email_alerts == 1) {
					print "<option value=0>No</option><option selected value=1>Yes</option>";
				} else {
					print "<option selected value=0>No</option><option value=1>Yes</option>";
				}
				print "</select>";
			print "</td></tr>";
		print "<tr><td>Send digest of new requests by groupmates&nbsp;&nbsp;</td><td>";
			print "<select id=email_new_group_report >";
				if($email_new_group_report == 1) {
					print "<option value=0>No</option><option selected value=1>Yes</option>";
				} else {
					print "<option selected value=0>No</option><option value=1>Yes</option>";
				}
				print "</select>";
			print "</td></tr>";
		print "</table>";
		print "<br /><b>DocumentCloud Integration</b>";
		print "<br />Password:&nbsp;&nbsp;&nbsp;<input type=text id=dc_password style='width:200px;' name=dc_password value=\"$dc_password\" />";
		?>
		<br><br>
		<table cellspacing=0 cellpadding=0 width=100% border=0><tr><td align=left>
		<a href="javascript:prefsSave()">Save</a>&nbsp;&nbsp;&nbsp;
		<a href="javascript:prefsClose()">Cancel</a>
		</td><td aligh=right>
		<a href="javascript:prefsChangePassword()">Change Password</a>&nbsp;
		</td></tr></table>
		<?

		print "</span>";

	}

	if($type == "prefsSave") {
		print "divprefs|";
		print " saving ";
			$name = addslashes(urldecode($name));
			$email = addslashes(urldecode($email));
			$phone = addslashes(urldecode($phone));
			$address = addslashes(urldecode($address));
			$city = addslashes(urldecode($city));
			$state = addslashes(urldecode($state));
			$zip = addslashes(urldecode($zip));
			$dc_password = addslashes(urldecode($dc_password));
			$db->run("DELETE FROM foia.user_prefs WHERE user='$nnusr' ");
			$sql = "INSERT INTO foia.user_prefs(user,name,email,phone,address,city,state,zip, email_alerts, email_new_group_report,dc_password)
				VALUES(\"$nnusr\", \"$name\", \"$email\", \"$phone\", \"$address\", \"$city\", \"$state\", \"$zip\", $email_alerts, $email_new_group_report, \"$dc_password\") ";
			$db->run($sql);

		print "|";
		print "prefsClose";

	}

	if($type == "prefsChangePassword") {
		print "divprefs|";
		print "<span style='font-family:arial; font-size:10pt'><b>Change Your Password</b></span>";
		if(($pw1 != '') && ($pw1 == $pw2)) {
			// got a matched password
			$pw1 = urldecode($pw1);
			$pw2 = urldecode($pw2);
			$pw1 = strtoupper($pw1);
			$sql = "UPDATE newsnet.users SET password = PASSWORD(\"$pw1\") WHERE username=\"$nnusr\" ";
			$db->run($sql);
			print "<p>Your password was changed successfully."; ?>
				<p><a href="javascript:prefsClose()">OK</a>
			<?
		} else {

			print "<p style='font-size:10pt'>Enter your new password twice:</p>";
			print "<p><table cellspacing=0 cellpadding=3 border=0 width=100% style='font-family:arial; font-size:10pt'>";
			print "<tr><td>New Password</td><td><input type=password id=pw1 size=30></td></tr>";
			print "<tr><td>Enter Password Again</td><td><input type=password id=pw2 size=30></td></tr>";
			print "</table>";
			if($pw1 != '') {
				// got an unmatched password
				print "<span style='font-family:arial; font-size:10pt; color:red'>The passwords do not match!</span>";
			}

			?>
			<p>
			<a href="javascript:prefsChangePasswordSubmit()">Change Password</a>&nbsp;&nbsp;&nbsp;
			<a href="javascript:prefsClose()">Cancel</a>

			<?

		}

	}

	} else {
		print "divprefs|Not auth!"; }


?>