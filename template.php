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






<?
	include('incl-foia-bottom.php');
?>

<script language=javascript>
	<?php print "sndReq('showlist','&bucket=$bucket_id');"; ?>
</script>

