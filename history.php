<?php	include('incl-auth.php');

	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);

?>

<html>
<head>
	<title>eFOIA</title>
</head>

<body style="margin-top:0px; margin-left:0px; margin-right:0px">

<?php

	include('incl-foia-top.php');

	if(!$auth) {
		include('incl-login.php'); }
	$db = new dbLink();
?>

	<!- CONTENT AREA -!>

	<?
		$sql = "SELECT title FROM foia.requests WHERE foia_id = $id ";
		$line = $db->getRow($sql);

		print "<span style='font-size:14pt; font-weight: bold'>History / $line[title]</span>";


		$sql = "SELECT version_id, DATE_FORMAT(date_changed, '%W, %b %e, %Y at %r') AS chgd, UNCOMPRESS(title) AS t, owner FROM foia.requests_versions WHERE foia_id = $id ORDER BY version_id DESC ";
		$result = $db->getArray($sql);
		print "<br>Found <b>" . $db->numRows() . "</b> history entries for this request:<p>";
		print "<p><a href=foiaform.php?type=edit&foia_id=$id>Current Request</a>";

		foreach($result as $line) {
			print "<li>$line[chgd] - <a href=print.php?type=hist&vid=$line[version_id]>$line[t]</a> - $line[owner]";
		}

	?>

<?
	include('incl-foia-bottom.php');
?>

<script language=javascript>
	<?php print "sndReq('showlist','&bucket=$bucket_id');"; ?>
</script>

