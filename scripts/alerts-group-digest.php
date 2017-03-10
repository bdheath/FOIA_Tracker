<?
require_once("incl-dbconnect.php");
$db = new dbLink();

/* SET YOUR EMAIL SERVERS HERE */
ini_set('smtp_port','');
ini_set('SMTP','');

/* SET YOUR EMAIL HEADERS HERE */
$headers = '';

	// Go through all users
	
	$sql = "SELECT username FROM newsnet.users ORDER BY username ";
	$ur = $db->query($sql);
	while($user = mysql_fetch_array($ur)) {
		
		$msg = "";
		
		$prefs = $db->getHash("SELECT * FROM foia.user_prefs WHERE user = '$user[username]' ");
		
		if(($prefs[email] != '') && ($prefs[email_new_group_report] == 1)) {

			// create digest
			$sql = "SELECT foia_id, records_sought, title, owner, agency, date_filed, status_id FROM foia.requests WHERE (deleted = 0 OR deleted IS NULL) AND owner IN ( SELECT DISTINCT username FROM foia.groups_members WHERE group_id IN ( SELECT DISTINCT group_id FROM foia.groups_members WHERE username = \"$user[username]\") ) AND (deleted = 0 OR deleted IS NULL) AND (date_filed >= DATE_ADD(CURRENT_DATE(), INTERVAL -7 DAY))  ORDER BY date_filed DESC LIMIT 50 ";
			

		
			// process alerts
			$r = $db->query($sql);
			if(mysql_num_rows($r) > 0) {
				$msg .= "<div style='font-family:arial; font-size:9pt; color:white; background-color:darkgray; width:100%;'><i>&nbsp;&nbsp;eFOIA</i> Automatic E-Mail Notifications</div>";
				$msg .= "<br />";
				$msg .= "<div style='font-family:cambria; font-size:11pt;'>";
				$msg .= "<span style='font-family:cambria; font-size:18pt; font-weight:bold; color:black;'><i>eFOIA</i> digest of new requests</span>";
				$msg .= "<br />New FOIA requests filed in the past week by member of $user[username]'s groups.<br /><br />";
				$msg .= "<ul>";
				while($l = mysql_fetch_array($r)) {
					$request = $db->getHash("SELECT * FROM foia.requests WHERE foia_id = $l[foia_id] ");
					$msg .= "<li style='margin-bottom:10px; list-style:square; '><span style='font-size:14pt;'>";
					if($l[owner] == $user[username]) {
						$msg .= "<a href=http://usat-eddataba/foia/foiaform.php?foia_id=$l[foia_id]>$request[title]</a>";
					} else {
						$msg .= "<a href=http://usat-eddataba/foia/print.php?foia_id=$l[foia_id]>$request[title]</a>";
					}
					$msg .= "</span>";
					$t = preg_replace("/\s+/i"," ", $l[records_sought]);
					$msg .= "<br /><span style='color:gray;'>Filed $l[date_filed] by $l[owner] with $l[agency]</span>";
					$msg .= "<br />$t<br />";
					print "</li>";
				}
				$msg .= "</ul><br /><hr /><span style='font-size:8pt;'>This is an automated e-mail message; <b>do not reply</b>.</span>";
				$msg .= "</div>";
				mail($prefs[email],"eFOIA Alerts",$msg, $headers);

				$alc = mysql_num_rows($r);				
				$sql = "INSERT INTO foia.log(event,username,event_time) VALUES(\"Group Digest to $prefs[email]\",\"sys\",NOW())";
				$db->actionQuery($sql) or die($sql);
			}
			
			// group status report
			// find all new e-mail requests in this group			
		}
	}

?>
