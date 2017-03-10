<?php	include('incl-auth.php');

	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);
?>

<html>
<head>
	<title>eFOIA</title>

	<script language=javascript>

		function deleteBucket(bucket_id,seebucket) {
			if(confirm("Are you sure you want to delete this bucket?")) {
				sndReq('showbuckets','&seebucket='+seebucket+'&action=delete&bucket_id='+bucket_id); }
		}

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
		    http.open('post', 'foia-ajax-buckets.php?type='+action+'&id='+p2+'&rndseed='+rnd_seed);
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

		var http = createRequestObject();

	</script>

</head>

<body style="margin-top:0px; margin-left:0px; margin-right:0px">

<?php

	include('incl-foia-top.php');

	if(!$auth) {
		include('incl-login.php'); }
	
?>

	<!- CONTENT AREA -!>

	<span style='font-size:14pt; font-weight: bold'>FOIA Buckets</span>
	<br>You can put your requests into buckets to help keep track of related items. Use this page to manage your buckets.
	<p><a href="javascript:sndReq('newbucket','')">New Bucket</a>
	<p><div id=bucketarea></div>






<?
	include('incl-foia-bottom.php');
?>

<script language=javascript>
	sndReq('showbuckets','');
</script>

