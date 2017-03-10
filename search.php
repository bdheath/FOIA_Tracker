<?php	include('incl-auth.php'); ?>

<html>
<head>
	<title>eFOIA</title>
</head>

<body style="margin-top:0px; margin-left:0px; margin-right:0px; margin-bottom:0px;">

<?php

	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);


	include('incl-foia-top.php');
	include('incl-foia-tools.php');

	if(!$auth) {
		include('incl-login.php'); }
7?>

	<script language=javascript src="foia.js"></script>
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
		    http.open('post', 'foia-ajax-search.php?type='+action+'&id='+p2+'&rndseed='+rnd_seed);
		    http.onreadystatechange = handleResponse;
		    http.send(null);
		    //fadeClose('requestlist',1);
		}

		function handleResponse() {
		    if(http.readyState == 4){
		        var response = http.responseText;
		        var update = new Array();
		        if(response.indexOf('|' != -1)) {
		            update = response.split('|');
		            document.getElementById(update[0]).innerHTML = update[1];
			    fadeInFast(update[0],0);
		        }
		    }
		}

		var http = createRequestObject();

	</script>


	<!- CONTENT AREA -!>

	<span style='font-size:18pt'>Search Results</span>
	<table cellspacing=0 cellpadding=0 border=0 style='background-color:#cccccc; width:90%; height:30px; font-size:10pt'><tr><td>
		&nbsp;&nbsp;&nbsp;Sort By:
		<?php
			$searchterm_enc = urlencode($searchterm);
			print "<select style='font-size:9pt' id=orderby onchange=\"sndReq('showlist','&searchterm=$searchterm_enc&searchwhat=$searchwhat&orderby='+document.getElementById('orderby').value+'&searchwhat='+document.getElementById('searchwhatsel').value+'&showabstracts='+document.getElementById('showabstracts').checked)\"> ";
		?>
			<option value=''>Relevance</option>
			<option value='date_filed'>Date Filed</option>
		</select>
		&nbsp;&nbsp;&nbsp;
		Show:
		<?php
			print "<select style='font-size:9pt' id=searchwhatsel onchange=\"sndReq('showlist','&searchterm=$searchterm_enc&searchwhat=$searchwhat&orderby='+document.getElementById('orderby').value+'&searchwhat='+document.getElementById('searchwhatsel').value+'&showabstracts='+document.getElementById('showabstracts').checked)\"> ";
			if($searchwhat == "mine") {
				print "<option selected value=mine>My Requests</option><option value=>All Requests</option>";
			} else {
				print "<option value=mine>My Requests</option><option selected value=>All Requests</option>"; }
		?>
		</select>
		&nbsp;&nbsp;&nbsp;
		<?
			print "Abstracts: <input type=checkbox checked id=showabstracts onclick=\"sndReq('showlist','&searchterm=$searchterm_enc&searchwhat=$searchwhat&orderby='+document.getElementById('orderby').value+'&searchwhat='+document.getElementById('searchwhatsel').value+'&showabstracts='+document.getElementById('showabstracts').checked)\"> ";
		?>
	</td></tr></table>
	<table cellspacing=0 cellpadding=0 border=0 style='width:90%'><tr><td>
	<div id=requestlist style='width:100%; background-color:white'></div>
	</td></tr></table>
<?
	include('incl-foia-bottom.php');
?>

<script language=javascript>
	<?php print "sndReq('showlist','&searchwhat=$searchwhat&searchterm=$searchterm_enc&showabstracts=true');"; ?>
</script>

