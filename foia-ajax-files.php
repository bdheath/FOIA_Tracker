<?php

	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);


	$nnusr = '';
	$nnusr = $_COOKIE[nnusr];

	include('incl-dbconnect.php');
	include('incl-auth.php');
	
	if(!$auth) { die(); }

	$db = new dbLink();

	if($type == "upload") {
		?>
			<form name=uploadform method=post enctype="multipart/form-data" action="/foia/foia-ajax-files-upload.php" target="uploadFrame">
		<?
		print "<b>Files</b>";
		print "<br />";
		print "<input type=file size=1 style='font-size:7pt;' id=docfilename name=docfilename onchange='changeFilename()'/>";
		print "<br /><span style='font-family:arial; font-size:10pt;'>Title</span>";
		print "<br /><input type=text name=doctitle id=doctitle size=10 style='width:95%;' doctitle=title />";
		print "<br /><input type=submit value='Add File' />";
		print "<input type=hidden id=foia_id name=foia_id value=$foia_id />";
		print "&nbsp;&nbsp;<a href='javascript:listFiles();'>Cancel</a>";
		print "</form>";
	}

	if($type == "showfiles") {
		if($auth) {
			print "<b>Files</b>";
			print "<br />";
			$sql = "SELECT DATE_FORMAT(uploaded, '%b %e, %Y') AS upl, dc_url, dc_exists, filename_mod, mime as document_type, document_id, title, filename FROM foia.documents_index WHERE foia_id = $foia_id";
			$r = $db->getArray($sql);
			if($db->numRows() == 0) {
				print "<span style='font-size:10pt;'>No files are associated with this request.</span>";	
			} else {
				print "<table cellspacing=0 cellpadding=0 border=0 style='width:99%; font-size:10pt;'>";
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
					print "<tr><td valign=top><img src=_images/$img height=25>&nbsp;&nbsp;</td><td><a href=/foia/document/$l[document_id]/$l[filename_mod] target=_blank>" . stripslashes($l[title]) . "</a>";
					print "<br /><span style='color:gray; font-size:9pt;'>Added $l[upl]</span>";
					if($l[dc_exists] == 1) {
						print "<br /><span class='greenlink'>Indexed By <a href=$l[dc_url] target=_blank>DocumentCloud</a></span>";
					}
					print "</td></tr>";
//					print "<li><a href=document.php?id=$l[document_id] target=_blank>" . stripslashes($l[title]) . "</a></li>";	
				}
				print "</table>";
			}
			print "<span style='font-size:10pt;'><br /><a href='javascript:uploadFile()'>Add New File</a></span>";
			
		}
	}

?>