<?

	include('incl-dbconnect.php');
	$db = new dbLink();
	
	function get_var($var) {
		if(gettype($db) == 'NULL') {
			$db = new dbLink(); }
		$sql = "SELECT * FROM foia.prefs ";
		$line = $db->getArray($sql);
		return $line[$var];
	}


	header("Content-type: text/xml");
	print "<?xml version=\"1.0\" ?>\n";
	print "<rss version=\"2.0\">\n";

	extract($_GET);
	$urlstem = get_var('urlstem');

		$sql = "SELECT id, title, alert_date, alert_text, requests.foia_id, IF(DATE(alert_date) = CURDATE(),'y','n') AS duetoday FROM foia.alerts INNER JOIN foia.requests ON alerts.foia_id = requests.foia_id WHERE alert_owner=\"$user\" AND DATE(alert_date) <= CURDATE() ORDER BY title ASC, alert_date ASC";
		$r = $db->getArray($sql);

		print "<channel>\n<title>FOIA Alerts</title><link>$urlstem</link><description>FOIA alerts for $user</description><generator>eFOIA</generator>\n";
		if($db->numRows() > 0) {
			foreach($r as $line) {
				print "<item>";
					print "<title>$line[title]</title>";
					print "<description>$line[alert_text] - Due $line[alert_date]</description>";
					print "<link>" . $urlstem . "foiaform.php?type=edit&amp;id=$line[foia_id]</link>";
					print "<guid ispermalink=true>" . $urlstem . "foiaform.php?type=edit&amp;id=$line[foia_id]&amp;aid=$line[id]</guid>";
				print "</item>\n";
			}
		}
		print "</channel>";

	print "</rss>";
?>