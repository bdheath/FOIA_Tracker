<?php	include('incl-auth.php'); ?>

<html>
<head>
	<title>eFOIA</title>
</head>

<body style="margin-top:0px; margin-left:0px; margin-right:0px">

<?php

	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);


	include('incl-foia-top.php');

	if(!$auth) {
		include('incl-login.php'); }
?>

	<!- CONTENT AREA -!>

	<?php

		if($type == "hist") {

			$sql = "SELECT UNCOMPRESS(title) AS title, UNCOMPRESS(agency) AS agency,
				UNCOMPRESS(records_sought) AS records_sought, UNCOMPRESS(notes) AS notes,
				UNCOMPRESS(contact_name) AS contact_name, UNCOMPRESS(contact_number) AS contact_number,
				UNCOMPRESS(fee_method) AS fee_method, status_id, fee_initial, fee_charged,
				fee_paid, owner, date_filed, date_entered, date_acknowledged,
				date_received, date_closed, deleted,
				DATE_FORMAT(date_filed, '%W, %b %e, %Y') AS date_filed_eng,
				DATE_FORMAT(date_closed, '%W, %b %e, %Y') AS date_closed_eng,
				DATE_FORMAT(date_acknowledged, '%W, %b %e, %Y') AS date_acknowledged_eng,
				DATE_FORMAT(date_changed, '%W, %b %e, %Y') AS date_changed_eng
				FROM foia.requests_versions
				WHERE version_id = $vid ";


		} else {

			$sql = "SELECT *, DATE_FORMAT(date_filed, '%W, %b %e, %Y') AS date_filed_eng,
				DATE_FORMAT(date_closed, '%W, %b %e, %Y') AS date_closed_eng,
				DATE_FORMAT(date_acknowledged, '%W, %b %e, %Y') AS date_acknowledged_eng,
				DATE_FORMAT(date_changed, '%W, %b %e, %Y') AS date_changed_eng
				FROM foia.requests
				WHERE foia_id = $foia_id ";

		}
		extract($db->getRow($sql));

		$notes = stripslashes(eregi_replace("\n", "<br>", $notes));
		$records_sought = stripslashes(eregi_replace("\n", "<br>", $records_sought));

		print "<span style='font-size:10pt; color:green'>FOIA Request</span>";

		if($type == "hist") {

			print "<p><span style='font-family:arial; color:red; font-weight:bold'>ARCHIVED VERSION</span>";
			print "<br>This version of the request was crated on <b>$line[date_changed_eng]</b>. <a href='javascript:history.back(0)'>Back</a>.<br>";

		}

		print "<br><span style='font-size:18pt; font-weight: bold'>$title</span>";

		$when = date("l F j, Y \a\\t g:i a T");
		print "<br /><span style='font-size:9pt'>Report generated an $when.</span>";

		print "<br /><br /><table cellspacing=0 cellpadding=0 border=0 style='font-size:10pt'>";

		print "<tr><td style='width:100px'><b>Filed By:</b></td><td>$owner</td></tr>";
		$s = "SELECT status FROM foia.status WHERE status_id = $status_id ";
		$l = $db->getRow($s);
		print "<tr><td><b>Status:</b> </td><td>$l[status]</td></tr>";
		print "</table>";

		print "<br><table cellspacing=0 cellpadding=0 border=0 width=80% style='font-size:10pt'><tr>";
			print "<td valign=top><b>Date Filed</b><br>$date_filed_eng</td>";
			print "<td valign=top><b>Date Acknowledged</b><br>$date_acknowledged_eng</td>";
			print "<td valign=top><b>Date Closed</b><br>$date_closed_eng</td>";
			print "<td valign=top><b>Last Modified</b><br>$date_changed_eng</td>";

		print "</tr></table>";

		print "<div style='font-size:10pt; width:90%'>";
			print "<br><b>Records Sought</b><br>$records_sought";

		print "</div>";
		print "<br><table cellspacing=0 cellpadding=0 border=0 style='font-size:10pt'>";
			print "<tr><td valign=top style='width:100px'><b>Agency</b></td><td>$agency</td></tr>";
			print "<tr><td valign=top><b>Contact Name</b></td><td>$contact_name</td></tr>";
			print "<tr><td valign=top><b>Contact Number&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td><td>$contact_number</td></tr>";
		print "</table>";

		print "<br><table cellspacing=0 cellpadding=0 border=0 style='font-size:10pt; width:80%' >";
			print "<tr><td style='width:20%' valign=top><b>Initial Fee</b><br>$ $fee_initial</td>";
			print "<td style='width:20%' valign=top><b>Fee Charged</b><br>$ $fee_charged</td>";
			print "<td style='width:20%' valign=top><b>Fee Paid</b><br>$ $fee_paid</td>";
			print "<td style='width:40%' valign=top><b>Payment Method</b><br>$fee_method</td>";
			print "</tr>";
		print "</table>";
		print "<div style='font-size:10pt; width:90%'>";
			print "<br><b>Notes</b><br>$notes";

		print "</div>";
		
		$sql = "SELECT document_id, title, DATE_FORMAT(uploaded, '%b %e, %Y') AS upl, uploaded, dc_exists, dc_url, filename_mod, mime as document_type FROM foia.documents_index WHERE foia_id = $foia_id ";
		$r = $db->getArray($sql);
		if($db->numRows() > 0) {
			print "<div style='font-size:10pt;' width:90%'>";
				print "<br /><b>Files</b>";
				print "<table cellspacing=0 cellpadding=0 border=0 style='width:450px; font-size:10pt;'>";
				foreach($r as $l) {
									if($l[document_type] == 'application/pdf') {
										$img = "pdf.gif";
									} elseif ($l[document_type] == 'application/msword') {
										$img = "word.gif";
									} elseif ($l[document_type] == 'text/plain') {
										$img = "txt.jpg";
									} elseif ($l[document_type] == 'application/vnd.ms-excel') {
										$img = "excel.jpg"; 
									} elseif ($l[document_type] == 'audio/mpeg') {
										$img = "mp3.jpg"; 
									} else { 
										$img = "bullet.jpg";
									}
					print "<tr><td style='width:40px;' valign=top><img src=_images/$img height=25>&nbsp;&nbsp;</td><td><a href=/foia/document/$l[document_id]/$l[filename_mod] target=_blank>" . stripslashes($l[title]) . "</a>";
					print "<br /><span style='color:gray; font-size:9pt;'>Added $l[upl]</span>";
					if($l[dc_exists] == 1) {
						print "<br /><span style='color:green; font-size:9pt;'>Indexed By DocumentCloud</span>";
					}
					print "</td></tr>";
				
				}
				print "</table>";
			print "</div>";
		}

	?>




<?
	include('incl-foia-bottom.php');
?>

<script language=javascript>
	<?php print "sndReq('showlist','&bucket=$bucket_id');"; ?>
</script>

