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
	$db = new dbLink();
	
	$sql = "SELECT foia_id, title, decedent, date_filed, status_id, owner FROM foia.requests WHERE death_request = 1 AND (deleted = 0 OR DELETED IS NULL) ORDER BY foia_id DESC LIMIT 200 ";
	$r = $db->getArray($sql);
	
	print "<b>Latest requests for FBI records</b>";
	
	print "<br /><br />";
	foreach($r as $l) {
		print "<a href=http://usat-eddataba/foia/print.php?foia_id=$l[foia_id]>$l[decedent]</a> ($l[title])";
		print "<br>$l[agency] <span style='color:gray;'>(Filed $l[date_filed] by $l[owner])</span><br /><br />";
	}
	
?>








<?
	include('incl-foia-bottom.php');
?>

<script language=javascript>
	<?php print "sndReq('showlist','&bucket=$bucket_id');"; ?>
</script>

