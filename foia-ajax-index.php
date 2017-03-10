<?php

	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);


	$nnusr = '';
	$nnusr = $_COOKIE[nnusr];

	include('incl-auth.php');
	require_once('incl-dbconnect.php');
	require_once('incl-log.php');

	if(!$auth) {
		die("showlist|<br>YOU ARE NOT AUTHORIZED TO VIEW THIS PAGE. YOU MUST <A href=index.php>LOGIN</A>."); }

	$db = new dbLink();
		
	if($type == "showalertshidden") {
		print "alertlist|";
		print "<br><table cellspacing=0 cellpadding=4 style='border-style:solid; border-width:1px; border-color:red; width:95%'>";
			print "<tr><td style='background-color: red; color:white; font-weight:bold'>";
			print "<table cellspacing=0 cellpadding=0 border=0 style='width:100%; color:white; font-weight:bold'><tr><td>";
			print "&nbsp;ALERTS / These requests require your immediate attention:</td>";
			print "</td><td align=right' class='toplinks'><a href='javascript:toggleAlerts()'>Show</td></tr></table>";
			print "</td></tr></table>";


	}

	if($type == "showalerts") {
		print "alertlist|";

		if($action == "snooze") {
			$sql = "UPDATE foia.alerts SET alert_date = DATE(CURDATE() + INTERVAL 1 DAY) WHERE alert_owner=\"$nnusr\" AND id = $alert_id ";
			$db->run($sql);
			$log = new foiaLog($nnusr,'Snooze Scheduled Alert (Homepage)',$foia_id);

		} elseif ($action == "clear") {
			$sql = "DELETE FROM foia.alerts WHERE alert_owner=\"$nnusr\" AND id=$alert_id ";
			$db->run($sql);
			$log = new foiaLog($nnusr,'Cleared Scheduled Alert (Homepage)',$foia_id);
		}

		$sql = "SELECT id, title, alert_date, alert_text, requests.foia_id, IF(DATE(alert_date) = CURDATE(),'y','n') AS duetoday FROM foia.alerts INNER JOIN foia.requests ON alerts.foia_id = requests.foia_id WHERE alert_owner=\"$nnusr\" AND DATE(alert_date) <= CURDATE() ORDER BY title ASC, alert_date ASC";
		$result = $db->getArray($sql);

		if($db->numRows() > 0) {
			print "<br><table cellspacing=0 cellpadding=4 style='border-style:solid; border-width:1px; border-color:red; width:95%'>";
			print "<tr><td style='background-color: red; color:white; font-weight:bold'>";
				print "<table cellspacing=0 cellpadding=0 border=0 style='width:100%; color:white; font-weight:bold'><tr><td>";
					print "&nbsp;ALERTS / These requests require your immediate attention</td>";
					print "</td><td align=right' class='toplinks'><a href='javascript:toggleAlerts()'>Hide</td></tr></table></td></tr>";
			print "<tr><td>";
			print "<table cellspacing=1 cellpadding=0>";
			foreach($result as $line) {
				if($curtitle != $line[title]) {
					print "<tr><td colspan=2><span class=alertlinks><a href=foiaform.php?type=edit&foia_id=$line[foia_id]>$line[title]</a></span></td></tr>";
					$curtitle = $line[title];
				}

				print "<tr><td valign=top style='color:red; width:110px; font-size:9pt'>";
				if($line[duetoday] == 'y') {
					print "Due Today";
				} else {
					print "Due $line[alert_date]"; }
				print "</td>";
				print "<td valign=top style='font-size:9pt'>$line[alert_text]&nbsp;&nbsp;<a href='javascript:snoozeAlert($line[id])'>Snooze</a>&nbsp;&nbsp;<a href='javascript:clearAlert($line[id])'>Clear</a></td></tr>";
			}
			print "</table>";
			print "</td></tr></table>";
		}
	}

	if($type == "showlist") {
		print "requestlist|";

		if($bucket != '') {
			$bucketsql = " AND foia_id IN (SELECT foia_id FROM foia.buckets_contents WHERE bucket_id = $bucket) ";
		}

		// handle sorts
		if(($orderby == '') || ($orderby == "date_filed")) {
			$orderbysql = "foia_id DESC";
		} elseif ($orderby == "date_filed2") {
			$orderbysql = "foia_id ASC";
		} elseif ($orderby == "title") {
			$orderbysql = "title ASC";
		} elseif ($orderby == "agency") {
			$orderbysql = "agency ASC";
		}

		if(($filter == '') || ($filter == 1)) {
			$whereadd = " AND status_id IN(1,2,3,4) ";
		} elseif ($filter == 2) {
			$whereadd = "";
		} elseif ($filter == 3) {
			$whereadd = " AND status_id IN(5,6,7) ";
		} elseif ($filter == 4) {
			$whereadd = " AND status_id = 8 ";
		} elseif ($filter == 5) {
			$whereadd = " AND status_id = 2 ";
		} elseif ($filter == 6) {
			$whereadd = " AND status_id = 4 ";
		}

		$sql = "SELECT title, foia_id, agency FROM foia.requests WHERE deleted IS NULL AND owner=\"$nnusr\" AND status_id <> 9999 $whereadd $bucketsql ORDER BY $orderbysql ";
		$result = $db->getArray($sql);

		if($db->numRows() == 0) {

			print "<p>No Matching Requests";

		} else {

			foreach($result as $line) {
				print "<br><a href=foiaform.php?type=edit&foia_id=$line[foia_id]>$line[title]</a>  ($line[agency]) ";
			}
		}

	}





?>