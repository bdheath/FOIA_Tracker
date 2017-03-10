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

	<script language=javascript>

		function createRequestObject() {
		    var ro;
		    var browser = navigator.appName;
		    if(browser == "Microsoft Internet Explorer"){
		        ro = new ActiveXObject("Microsoft.XMLHTTP");
		    }else{
		        ro = new XMLHttpRequest();
		    }
		    return ro;
		}

		function sndReq(action, p2) {
		    var rnd_seed = Math.random() * Math.round(Math.random() * 10000000);
		    http.open('post', 'foia-ajax-admin.php?type='+action+'&id='+p2+'&rndseed='+rnd_seed);
		    http.onreadystatechange = handleResponse;
		    http.send(null);
		}

		function handleResponse() {
		    if(http.readyState == 4){
		        var response = http.responseText;
		        var update = new Array();
		        if(response.indexOf('|' != -1)) {
		            update = response.split('|');
		            document.getElementById(update[0]).innerHTML = update[1];
		        }
		    }
		}

		function resetPassword(user_id,username) {
			if(confirm("Are you sure you want to reset user "+username+"'s password?")) {
				sndReq('userlist','&action=resetpassword&id='+user_id); }
		}

		function deleteUser(user_id) {
			if(confirm("Are you sure you want to delete this user?")) {
				sndReq('userlist','&action=delete&id='+user_id); }
		}

		var http = createRequestObject();

	</script>

<?php

	include('incl-foia-top.php');

	if(!$auth) {
		include('incl-login.php'); }
	if(!$admin) {
		print "<p>You do not have permission to view this page.";
		include('incl-foia-bottom.php');
		die();
	}

?>

	<!- CONTENT AREA -!>
	<span style='font-family:arial; font-size:18pt'>Users</span>

	<p><div id=userarea></div>



<?
	include('incl-foia-bottom.php');
?>

<script language=javascript>
	<?php print "sndReq('userlist','&bucket=$bucket_id');"; ?>
</script>

