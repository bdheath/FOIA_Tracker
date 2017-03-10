<?php

	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);

	$nnusr = '';
	$nnusr = $_COOKIE[nnusr];

	require_once('incl-foia-tools.php');
	require_once('incl-auth.php');
	require_once('incl-dbconnect.php');

	$db = new dbLink();

	if($type == "showlist") {

		print "requestlist|";

		if($searchwhat == "mine") {
			$sql_searchwhat = " AND owner=\"$nnusr\" "; }

		if($orderby == '') {
			$orderby_sql = " search_score DESC ";
		} elseif ($orderby == 'date_filed') {
			$orderby_sql = " date_filed DESC ";
		}

		$searchterm = stripslashes(urldecode($searchterm));
		$searchterm_bol = parse_search($searchterm);
		$searchterm = stripslashes($searchterm);

		$sql = "SELECT title, agency, records_sought, foia_id, owner, notes, requests.status_id, status, (MATCH(owner,title,agency,records_sought,notes,contact_name, contact_number) AGAINST(\"$searchterm_bol\")) + 2*(MATCH(title) AGAINST(\"$searchterm_bol\")) AS search_score
			FROM foia.requests INNER JOIN foia.status ON requests.status_id = status.status_id WHERE deleted IS NULL AND MATCH(owner,title,agency,records_sought,notes,contact_name, contact_number) AGAINST(\"$searchterm_bol\" IN BOOLEAN MODE) $sql_searchwhat ORDER BY $orderby_sql ";
			
		$sql = "SELECT title, t.docs, agency, records_sought, requests.foia_id, owner, notes, requests.status_id, status, (MATCH(owner,title,agency,records_sought,notes,contact_name, contact_number) AGAINST(\"$searchterm_bol\")) + 2*(MATCH(title) AGAINST(\"$searchterm_bol\")) AS search_score
			FROM (foia.requests INNER JOIN foia.status USING(status_id))
				INNER JOIN ( 
					SELECT foia_id, GROUP_CONCAT((document_id)) as docs
					FROM foia.textsearch
					WHERE MATCH(item) AGAINST(\"$searchterm_bol\" IN BOOLEAN MODE)
					GROUP BY foia_id
				) as t USING(foia_id)
			WHERE requests.deleted IS NULL
			$sql_searchwhat
			ORDER BY $orderby_sql
			";

			
		$result = $db->getArray($sql);

		if($db->numRows() == 0) {

			print "<p>No requests matched your search for <b>$searchterm</b>.";

		} else {
			$r = number_format($db->numRows(),0);

			print "<p><b>$r</b> requests match your search for <b>$searchterm</b>.<br>";

			foreach($result as $line) {
				if($line[owner] == $nnusr) {
					$page = "foiaform.php";
					$more = "&nbsp;&nbsp;<a href=print.php?foia_id=$line[foia_id]><img align=absmiddle src=icon-printer.gif border=0></a> ";
				} else {
					$page = "print.php";
					$more = ""; }
				$title = k_abstract($line[title],$searchterm,100);
				if(($line[status_id] >= 1) && ($line[status_id] <= 4)) {
					$col = "DarkGreen";
				} elseif (($line[status_id] == 6) || ($line[status_id] == 8)) {
					$col = "Red";
				} elseif (($line[status_id] == 5) || ($line[status_id] == 7)) {
					$col = "Orange";
				} else {
					$col = "Gray"; }
				
				$agency = k_abstract($line[agency],$searchterm,100);
				
				$owner = k_abstract($line[owner],$searchterm,100);
				print "<br><span style='font-size:13pt;'><a href=$page?foia_id=$line[foia_id]>$title</a></span>$more<br><span style='font-size:10pt; color:gray'>$agency / <font color=$col>$line[status]</font> / $owner</span>";
				if(($showabstracts == 'true') && ($line[records_sought] != '')) {
					$ft = $line[records_sought] . " " . $line[notes];
					$abs = abstract2($ft,$searchterm,80);
					print "<br /><span style='font-size:10pt; font-family:arial'>$abs</span><br>";
				} else {
					print "<br>"; }
				if($line[docs] != '') {
					$documents = explode(",",$line[docs]);
					print "<table cellspacing=12 cellpadding=0 border=0 style='font-size:10pt; margin-left:20px;'>";
					foreach($documents as $doc) {
						$s = "SELECT DATE_FORMAT(uploaded, '%b %e, %Y') AS upl, dc_url, dc_exists, title, mime as document_type, document_id, filename_mod, dc_body FROM foia.documents_index WHERE document_id = $doc ";
						$l = $db->getRow($s);
						$title = k_abstract(stripslashes($l[title]),$searchterm,100);

						$docabs = abstract2($l[dc_body],$searchterm,50);
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
						print "<tr style='padding-top:5px; margin-top:5px;'><td valign=top><img src=_images/$img height=25>&nbsp;&nbsp;</td><td><span style='font-size:12pt;'><a href=/foia/document/$l[document_id]/$l[filename_mod] target=_blank>" . stripslashes($title) . "</a></span>";
						print "&nbsp;&nbsp;&nbsp;<span style='color:gray; font-size:9pt;'>Added $l[upl]</span>";
						if($l[dc_exists] == 1) {
							print "&nbsp;&nbsp;&nbsp;<span class='greenlink'> <a href=$l[dc_url] target=_blank>Searchable Text</a></span>&nbsp;&nbsp;<img src=/foia/_images/documentcloud.png height=23 align=absbottom />";
						}
						if($showabstracts == 'true') {
							print "<br />$docabs";
						}
						print "</td></tr>";
					}
					print "</table>";
				}
			}

		}

	}


?>
