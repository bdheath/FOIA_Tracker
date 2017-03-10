<?php

	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);


	$nnusr = '';
	$nnusr = $_COOKIE[nnusr];

	if($foia_id == '') { $foia_id = 'NULL'; }
	
	include('incl-dbconnect.php');
	require_once('incl-log.php');
	include('incl-auth.php');

		function make_version($fid) {
			if(gettype($db) == 'NULL') {
				$db = new dbLink(); }
			$sql = "INSERT INTO foia.requests_versions(foia_id,owner,title,agency,date_filed,date_entered,date_changed,date_acknowledged,date_received,date_closed,status_id,records_sought,notes,contact_name,contact_number,fee_initial,fee_charged,fee_paid,fee_method,deleted)
				SELECT foia_id,owner,COMPRESS(title),COMPRESS(agency),date_filed,date_entered,NOW(),date_acknowledged,date_received,date_closed,status_id,COMPRESS(records_sought),COMPRESS(notes),COMPRESS(contact_name),COMPRESS(contact_number),fee_initial,fee_charged,fee_paid,COMPRESS(fee_method),deleted FROM foia.requests WHERE foia_id = $fid ";
			$db->run($sql);
		}

	$db = new dbLink();


	if($type == "delete") {

		print "requestarea|";

		$sql = "UPDATE foia.requests SET deleted = 1 WHERE foia_id=$id AND owner=\"$nnusr\" ";
		$db->run($sql);
		$sql = "DELETE FROM foia.alerts WHERE foia_id=$foia_id AND alert_owner=\"$nnusr\" ";
		$db->run($sql);
		print "<table cellspacing=0 cellpadding=0 border=0 style='font-family:arial; font-size:16pt; width:98%; height:300px; background-color:lightred'><tr><td> ";
		print "<center>This request has been deleted from the database.</center>";
		print "</td></tr>";
		$log = new foiaLog($nnusr,'Delete FOIA Request',$foia_id);


	}

	if($type == "alertbox") {
		print "alertbox|";

		if($action == "snooze") {
			$sql = "UPDATE foia.alerts SET alert_date = DATE(CURDATE() + INTERVAL 1 DAY) WHERE id=$alert_id AND alert_owner=\"$nnusr\" ";
			$db->run($sql);
			$log = new foiaLog($nnusr,'Snooze Scheduled Alert',$foia_id);

		} elseif ($action == "clear") {
			$sql = "DELETE FROM foia.alerts WHERE id=$alert_id AND alert_owner=\"$nnusr\" ";
			$db->run($sql);
			$log = new foiaLog($nnusr,'Delete Scheduled Alert',$foia_id);

		}

		$sql = "SELECT id, alert_date, alert_text, IF(DATE(alert_date) = DATE(CURDATE()),'y','n') AS duetoday FROM foia.alerts WHERE foia_id = $foia_id AND DATE(alert_date) <= DATE(CURDATE()) ORDER BY alert_date DESC ";
		$r = $db->getArray($sql);
		if($db->numRows() > 0) {

			print "<br><table cellspacing=0 cellpadding=4 style='border-style:solid; border-width:1px; border-color:red; width:98%'>";
				print "<tr><td style='background-color: red; color:white; font-size:10pt; height:10px; font-weight:bold'>Alerts</td></tr>";
				print "<tr><td>";

					print "<table cellspacing=0 cellpadding=0 style='width:98%'>";
					foreach($r as $line) {
						print "<tr><td valign=top style='color:red; width:130px; font-size:10pt'>Due ";
							if($line[duetoday] == 'y') {
								print "Today";
							} else {
								print $line[alert_date];
							}
						print "</td><td valign=top style='font-size:10pt'>$line[alert_text]";
						print "&nbsp;&nbsp;<a href=\"javascript:snoozeAlert($line[id],$foia_id)\">Snooze</a>";
						print "&nbsp;&nbsp;<a href=\"javascript:clearAlert($line[id],$foia_id)\">Clear</a>";
						print "&nbsp;&nbsp;<a href=\"javascript:sndReq('editalert','&alert_id=$line[id]&foia_id=$foia_id')\">Change</a>";
						print "</td></tr>";
					}
					print "</table>";

				print "</td></tr></table>";
				print "<br>";
		}

	}

	if($type == "seealerts") {
		print "alerts|";
		print "<b>Alerts</b>";
		if($action == "addalert") {
			// add a new alert

			if($alert_date != '') {
				$alert_text = addslashes(urldecode($alert_text));
				$sql = "INSERT INTO foia.alerts(foia_id, alert_owner, date_created, alert_date, alert_text)
					VALUES($foia_id, \"$nnusr\", DATE(NOW()), \"$alert_date\", \"$alert_text\") ";
				$db->run($sql);
				$log = new foiaLog($nnusr,'Create Scheduled Alert',$foia_id);
			}

		} elseif ($action == "dropalert") {
			// delete an existing alert
			$sql = "DELETE FROM foia.alerts WHERE id = $alert_id AND alert_owner=\"$nnusr\" ";
			$db->run($sql);
			$log = new foiaLog($nnusr,'Delete Scheduled Alert',$foia_id);
		} elseif ($action == "savealert") {
			$alert_text = addslashes(urldecode($alert_text));
			$sql = "UPDATE foia.alerts SET alert_date=\"$alert_date\", alert_text=\"$alert_text\" WHERE id=$alert_id AND alert_owner=\"$nnusr\" ";
			$db->run($sql);
			$log = new foiaLog($nnusr,'Create Scheduled Alert',$foia_id);

		}
		$s = "SELECT IF(DATE(alert_date) <= DATE(CURDATE()),'y','n') AS due, alert_text, alert_date, DATE_FORMAT(alert_date, '%W, %b %e, %Y') AS alert_date_eng, id, alert_text FROM foia.alerts WHERE foia_id = $foia_id ORDER BY alert_date ASC ";
		$r = $db->getArray($s);
		
		print "<span style='font-size:9pt'>";
		if($db->numRows() == 0) {

			print "<br>No alerts have been set up for this request.";

		} else {
			print "<br><b>Alerts Scheduled On:</b>";
			foreach($r as $line) {
				if($line[due] == 'y') {
					$style = "color:red";
				} else {
					$style = ''; }
				print "<br><a style='$style' href=\"javascript:sndReq('editalert','&foia_id=$foia_id&alert_id=$line[id]')\">$line[alert_date_eng]</a><br><span style='font-size:8pt'>$line[alert_text]</span>";
		}
			print "<br>";

		}
		print "<br><a href=\"javascript:sndReq('addalert','&foia_id=$foia_id')\">New Alert</a>";
		print "</span>";
		print "<img src=y.gif height=1 width=1 onload=delayedReloadAlertboxHC()>";

	}

	if ($type == "editalert") {
		print "alerts|";
		print "<b>Alerts</b>";
		print "<span style='font-size:9pt'>";

		print "<br>Edit this alert:";

		$s = "SELECT alert_date, alert_text FROM foia.alerts WHERE id = $alert_id ";
		$line = $db->getRow($s);
		$alert_text = stripslashes($line[alert_text]);

		print "<br>Date<br><input type=text size=15 style='font-size:9pt' value=\"$line[alert_date]\" id=alert_date name=alert_date>";
		print "<a href=\"javascript:void(0)\" onClick=\"showCalendar(document.getElementById('alert_date'), 'YY-MM-DD','Choose data')\"><IMG SRC=\"CAL-icon.gif\" BORDER=\"0\" width=\"16\" height=\"16\" alt=\"Click for calendar\"></a>";
		print "<br>Reminder<br><textarea id=alert_text style='width:95%' rows=7>$alert_text</textarea>";
		print "<br><a href=\"javascript:sndReq('seealerts','&action=savealert&foia_id=$foia_id&alert_id=$alert_id&alert_date='+escape(document.getElementById('alert_date').value)+'&alert_text='+escape(document.getElementById('alert_text').value))\">OK</a> ";
		print "&nbsp;&nbsp;&nbsp;";
		print "<a href=\"javascript:clearAlert($alert_id,$foia_id)\">Clear</a>";
		print "</apan>";

	}

	if ($type == "addalert") {
		print "alerts|";
		print "<b>Alerts</b>";
		print "<span style='font-size:9pt'>";

		print "<br>Add a new alert for this request. Choose a date for the reminder and a message to display.";

		print "<br>Date<br><input type=text size=15 style='font-size:9pt' id=alert_date name=alert_date>";
		print "<a href=\"javascript:void(0)\" onClick=\"showCalendar(document.getElementById('alert_date'), 'YY-MM-DD','Choose data')\"><IMG SRC=\"CAL-icon.gif\" BORDER=\"0\" width=\"16\" height=\"16\" alt=\"Click for calendar\"></a>";
		print "<br>Reminder<br><textarea id=alert_text style='width:95%' rows=7></textarea>";
		print "<br><a href=\"javascript:sndReq('seealerts','&action=addalert&foia_id=$foia_id&alert_date='+escape(document.getElementById('alert_date').value)+'&alert_text='+escape(document.getElementById('alert_text').value))\">Save Alert</a> ";
		print "</apan>";
	}


	if($type == "seebuckets") {

		print "buckets|";
		print "<b>Buckets</b>";
		if($action == "newbucket") {
			$bucket_name = addslashes($bucket_name);
			$s = "SELECT bucket_name FROM foia.buckets WHERE bucket_owner=\"$nnusr\" AND bucket_name=\"$bucket_name\" ORDER BY bucket_name ";
			$r = $db->getArray($s);
			if(($bucket_name != '') && ($db->numRows() == 0)) {
				$s = "INSERT INTO foia.buckets(bucket_name,bucket_owner) VALUES(\"$bucket_name\", \"$nnusr\") ";
				$db->run($s);
			}
		}
		if($action == "addbucket") {
			$s = "SELECT foia_id FROM foia.buckets_contents WHERE foia_id=$foia_id AND bucket_id = $bucket_id ";
			$r = $db->getArray($s);
			if($db->numRows() == 0) {
				$s = "INSERT INTO foia.buckets_contents(bucket_id, foia_id) VALUES($bucket_id, $foia_id) ";
				$db->run($s);
			}
		}
		if($action == "dropbucket") {
			$s = "DELETE FROM foia.buckets_contents WHERE foia_id = $foia_id AND bucket_id = $bucket_id ";
			$db->run($s);
		}

		print "<span style='font-size:9pt'>";
		$sql = "SELECT bucket_name, buckets.bucket_id FROM foia.buckets INNER JOIN foia.buckets_contents ON buckets.bucket_id = buckets_contents.bucket_id WHERE buckets_contents.foia_id=$foia_id AND bucket_owner=\"$nnusr\" ORDER BY bucket_name ASC ";
		$r = $db->getArray($sql);
		if($db->numRows() == 0) {
			print "<p>This request is not assigned to any buckets.";
		} else {
			print "<br><b>Assigned To:</b>";
			foreach($r as $line) {
				print "<br>" . "<a href=index.php?bucket_id=$line[bucket_id]>$line[bucket_name]</a>";
				print "&nbsp;(<a href=\"javascript:sndReqBucket('seebuckets','&foia_id=$foia_id&action=dropbucket&bucket_id=$line[bucket_id]')\">x</a>)";
			}
		}

		print "<p>Add To: <select name=addbucket style='font-size:9pt' id=addbucket onChange=\"sndReqBucket('seebuckets','&foia_id=$foia_id&action=addbucket&bucket_id=' + document.getElementById('addbucket').value)\"> ";
			$s = "SELECT bucket_id, bucket_name FROM foia.buckets WHERE bucket_owner='$nnusr' ORDER BY bucket_name ASC ";
			$r = $db->getArray($s);
			print "<option value=''>- Choose One -</option>";
			foreach($r as $l) {
				print "<option value=$l[bucket_id]>$l[bucket_name]</option>";
			}
			print "</select>";
			print "<br><a href=\"javascript:sndReqBucket('newbucket','&foia_id=$foia_id')\">New Bucket</a> ";
		print "</span>";

	}

	if($type == "newbucket") {
		print "buckets|";
		print "<b>Buckets</b>";
		print "<span style='font-size:9pt'>";
		print "<br>Add a new bucket to help you categorize FOIA requests:";
		print "<p>Name<br><input style='font-size:8pt' type=text id=bucket_name size=25 maxlength=25>";
		print "<br><a href=\"javascript:sndReqBucket('seebuckets','&foia_id=$foia_id&action=newbucket&bucket_name=' + escape(document.getElementById('bucket_name').value))\">Add Bucket</a>";

		print "</span>";

	}

	if($type == "save") {

		print "savearea|";

		$notes = addslashes(strip_tags(urldecode($notes)));
		$owner = addslashes(strip_tags(urldecode($owner)));
		$title = addslashes(strip_tags(urldecode($title)));
		$decedebt = addslashes(strip_tags(urldecode($decedent)));
		if($title == '') {
			$title = "** UNTITLED REQUEST **"; }
		if($death_request == "on") {
			$death_request = 1;
		} else {
			$death_request = 0; 
		}
		$agency = addslashes(strip_tags(urldecode($agency)));
		$contact_name = addslashes(strip_tags(urldecode($contact_name)));
		$contact_number = addslashes(strip_tags(urldecode($contact_number)));
		$fee_method = addslashes(strip_tags(urldecode($fee_method)));
		$records_sought = addslashes(strip_tags(urldecode($records_sought)));
		if($fee_paid == '') { $fee_paid = "NULL"; }
		if($fee_initial == '') { $fee_initial = "NULL"; }
		if($fee_charged == '') { $fee_charged = "NULL"; }
		if($date_acknowledged == '') {
			$date_acknowledged = "NULL";
		} else {
			$date_acknowledged = "\"$date_acknowledged\""; }
		if($date_closed == '') {
			$date_closed = "NULL";
		} else {
			$date_closed = "\"$date_closed\""; }

		$sql = "UPDATE foia.requests
			SET title=\"$title\", agency=\"$agency\", date_filed=\"$date_filed\",
			date_acknowledged=$date_acknowledged, date_closed=$date_closed,
			status_id=$status_id, records_sought=\"$records_sought\", notes=\"$notes\",
			contact_name=\"$contact_name\", contact_number=\"$contact_number\",
			fee_initial=$fee_initial, fee_charged=$fee_charged, fee_paid=$fee_paid,
			fee_method=\"$fee_method\", date_changed = NOW(), death_request=$death_request,
			decedent=\"$decedent\"
			WHERE foia_id = $foia_id AND owner=\"$nnusr\" ";
		$db->run($sql);
		make_version($foia_id);

		// now update the text search
		$s = "SELECT COUNT(*) AS c FROM foia.textsearch WHERE item_type = 1 AND foia_id = $foia_id ";
		$l = $db->getRow($s);
		if($l[c] == 0) {
			$s = "INSERT INTO foia.textsearch(foia_id, item_type) VALUES($foia_id, 1)";
			$db->run($s);
		}
		$item = addslashes($title . " " . $agency ." " . $records_sought . " " . $notes . " " . $contact_name . " " . $contact_number . " " . $decedent . " ");
		$sql = "UPDATE foia.textsearch SET item = \"$item\" WHERE foia_id = $foia_id AND item_type = 1 ";
		$db->run($sql) or $savefail = 1;
		
		if($savefail == 1) {
			print "<span style='color:red'>ERROR - Cannot Save</span>";
		} else {
			print "<span style='color:green'>Changes Saved</span>"; }
		print "<img src=y.gif height=1 width=1 onload='setDelay()'>";

		if($savefail == 1) {
			print "|ERROR"; }
		$log = new foiaLog($nnusr,'Save Edits To FOIA Request',$foia_id);

	}


	if($type == "relatedtitle") {
		$st = addslashes(urldecode($st));
			$sql = "SELECT requests.foia_id, requests.title, requests.agency, requests.owner, requests.date_filed, t.matches, t.m 
				FROM foia.requests INNER JOIN ( 
					SELECT foia_id, COUNT(*) AS matches,
					  MAX(MATCH(item) AGAINST(\"$st\")) AS m
					FROM foia.textsearch
					WHERE MATCH(item) AGAINST(\"$st\") >= 5
					  AND foia_id <> $foia_id
					GROUP BY foia_id
					ORDER BY m DESC
					LIMIT 2
				) AS t USING(foia_id)
				WHERE deleted IS NULL AND requests.foia_id <> $foia_id
				";
			$r = $db->getArray($sql);
			if($db->numRows() > 0) {
				print "<table cellspacing=0 cellpadding=0 border=0 style='width:100%; font-family:arial; font-size:10pt;';>";
				print "<tr><td>&nbsp;&nbsp;<b>A similar request already exists</b></td><td><a href='javascript:titleUnWarningPerm();'>x</a></td></tr>";
				print "<tr><td colspan=2>";
				foreach($r as $l) {
					print "&nbsp;&nbsp;<a href=print.php?foia_id=$l[foia_id] target=_blank>$l[title]</a> <span style='color:gray;'> by $l[owner]</span><br />";	
				}
				print "</td></tr><tr><td colspan=2 style='height:5px;'></td></tr></table>";
			}
					
	}

	if($type == "related") {
		$sql = "SELECT title, agency, records_sought, decedent, date_filed, owner FROM foia.requests WHERE foia_id = $foia_id ";
		$h = $db->getRow($sql);
		
		extract($h);
		
		if(($title != '') ) {
		
			$st = addslashes($title . " " . $agency ." " . $records_sought . " " . $decedent);
			$sql = "SELECT requests.foia_id, requests.title, requests.agency, requests.owner, requests.date_filed, t.matches, t.m FROM foia.requests INNER JOIN ( 
				SELECT foia_id, COUNT(*) AS matches,
				  MAX(MATCH(item) AGAINST(\"$st\")) AS m
				FROM foia.textsearch
				WHERE MATCH(item) AGAINST(\"$st\") >= 5
				  AND foia_id <> $foia_id
				GROUP BY foia_id
				ORDER BY m DESC
				LIMIT 5
				) AS t USING(foia_id)
				WHERE deleted IS NULL AND requests.foia_id <> $foia_id
				";
			$r = $db->getArray($sql);
			if($db->numRows() > 0) {
				print "<br /><br /><b>Possible Related FOIA Requests</b>";
				print "<ul style='padding-left:15px;'>";
				foreach($r as $l) {
					print "<li><a href=print.php?foia_id=$l[foia_id]>$l[title]</a> - $l[agency] <span style='color:gray;'>(filed $l[date_filed] by $l[owner])</span></li>";
				}
				print "</ul>";
			}
		}
		
	}


?>