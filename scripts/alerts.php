<?
require_once("incl-dbconnect-new.php");
$db = new dbLink();

/* SET YOUR e-mail servers here */
ini_set('smtp_port','');
ini_set('SMTP','');

$headers = ''; /* ADD YOUR e-mail headers here */

	// Go through all users
	
	$sql = "SELECT username FROM newsnet.users ORDER BY username ";
	$ur = $db->query($sql);
	while($user = mysql_fetch_array($ur)) {
		
		$msg = "";
		
		$prefs = $db->getHash("SELECT * FROM foia.user_prefs WHERE user = '$user[username]' ");
		
		if($prefs[email] != '') {
		
			// process alerts
			$sql = "SELECT * FROM foia.alerts WHERE alert_owner=\"$user[username]\" AND alert_date <= CURRENT_DATE() ORDER BY alert_date ASC ";
			$r = $db->query($sql);
			if(mysql_num_rows($r) > 0) {
				$msg .= "<div style='font-family:arial; font-size:9pt; color:white; background-color:darkgray; width:100%;'><i>&nbsp;&nbsp;eFOIA</i> Automatic E-Mail Notifications</div>";
				$msg .= "<br />";
				$msg .= "<div style='font-family:cambria; font-size:11pt;'>";
				$msg .= "<span style='font-family:cambria; font-size:18pt; font-weight:bold; color:red;'>Daily <i>eFOIA</i> Alerts</span>";
				$msg .= "<br />These are your daily e-FOIA reminders:<br /><br />";
				$msg .= "<ul>";
				while($l = mysql_fetch_array($r)) {
					$request = $db->getHash("SELECT * FROM foia.requests WHERE foia_id = $l[foia_id] ");
					$msg .= "<li> <a href=http://usat-eddataba/foia/foiaform.php?foia_id=$l[foia_id]>$request[title]</a>: $l[alert_text] <span style='color:gray;'>(Due $l[alert_date])</span></li>";
				}
				$msg .= "</ul><br /><hr /><span style='font-size:8pt;'>This is an automated e-mail message; <b>do not reply</b>.</span>";
				$msg .= "</div>";
				mail($prefs[email],"eFOIA Alerts",$msg, $headers);

				$alc = mysql_num_rows($r);				
				$sql = "INSERT INTO foia.log(event,username,event_time,notes) VALUES(\"Daily Alert E-Mail to $prefs[email]\",\"sys\",NOW(),\"Sent $alc alerts\")";
				$db->actionQuery($sql);

			}
			
			// group status report
			// find all new e-mail requests in this group			
		}
	}

?>
