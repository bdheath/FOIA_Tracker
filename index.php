<?php

	$_GET['auth'] = '';
	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);


	include('incl-auth.php');

	// errant data cleanup

	$sql = "DELETE FROM foia.buckets_contents WHERE foia_id IN(SELECT foia_id FROM foia.requests WHERE deleted = 1) ";
	$db->run($sql);

	$sql = "DELETE FROM foia.alerts WHERE foia_id IN(SELECT foia_id FROM foia.requests WHERE deleted = 1) ";
	$db->run($sql);

	# delete blank requesets
	$sql = "DELETE FROM foia.requests WHERE title is NULL AND agency IS NULL AND date_filed IS NULL AND records_sought IS NULL AND status_id=9999";
	$db->run($sql);
 
	# delete versions of requests that don't exist in the first place
	$sql = "DELETE FROM foia.requests_versions WHERE foia_id NOT IN (SELECT DISTINCT foia_id FROM foia.requests)";
	$db->run($sql);
 
	# delete alerts for which requests have been deleted
	$sql = "DELETE FROM foia.alerts WHERE foia_id NOT IN (SELECT DISTINCT foia_id FROM foia.requests)";
	$db->run($sql);

	$sql = "DELETE FROM foia.alerts WHERE foia_id in (SELECT DISTINCT foia_id FROM foia.requests WHERE deleted=1)";
	$db->run($sql);
 
	# delete text indexes for deleted files
	$sql = "DELETE FROM foia.textsearch WHERE item_type=2 AND document_id NOT IN (SELECT DISTINCT document_id FROM foia.documents)";
	$db->run($sql);


?>

<html>
<head>
	<title>eFOIA</title>
</head>

<body style="margin-top:0px; margin-left:0px; margin-right:0px; margin-bottom:0px;">

	<script language=javascript src="foia.js"></script>

	<script language=javascript>

		function rollClose(elm, op, factor) {
			if(op > 0) {
				op = op - .05;
				document.getElementById(elm).style.height = (document.getElementById(elm).offsetHeight - factor) + 'px';
				document.getElementById(elm).style.opacity = op;
				document.getElementById(elm).style.filter='alpha(opacity='+op*100+')';
				setTimeout("rollClose('"+elm+"',"+op+","+factor+")",25);
			} else {
				document.getElementById(elm).style.height=0;
				document.getElementById(elm).style.opacity=1;
			}
		}

		function snoozeAlert( alert_id) {
			sndReqAlerts('showalerts','&action=snooze&alert_id='+alert_id);
		}

		function clearAlert(alert_id) {
			if (confirm("Are you sure you want to clear this alert? If you clear an alert, it will be gone forever. ")) {
				sndReqAlerts('showalerts','&action=clear&alert_id='+alert_id); }
		}

		function toggleAlerts() {
			if(showAlerts == 1) {
				showAlerts = 0;
				sndReqAlerts('showalertshidden','');
			} else {
				showAlerts = 1;
				sndReqAlerts('showalerts','');
			}
		}

		function sndReq(action, p2) {
		    var rnd_seed = Math.random() * Math.round(Math.random() * 10000000);
		    http.open('post', 'foia-ajax-index.php?type='+action+'&id='+p2+'&rndseed='+rnd_seed);
		    http.onreadystatechange = handleResponse;
		    http.send(null);
		}
		function sndReqAlerts(action, p2) {
		    var rnd_seed = Math.random() * Math.round(Math.random() * 10000000);
		    httpAlerts.open('post', 'foia-ajax-index.php?type='+action+'&id='+p2+'&rndseed='+rnd_seed);
		    httpAlerts.onreadystatechange = handleResponseAlerts;
		    httpAlerts.send(null);
		}
		function handleResponse() {
		    if(http.readyState == 4){
		        var response = http.responseText;
		        var update = new Array();
		        if(response.indexOf('|' != -1)) {
		            update = response.split('|');
		            document.getElementById(update[0]).innerHTML = update[1];
		            fadeIn(update[0],0);
		        }
		    }
		}
		function handleResponseAlerts() {
		    if(httpAlerts.readyState == 4){
		        var response = httpAlerts.responseText;
		        var update = new Array();
		        if(response.indexOf('|' != -1)) {
		            update = response.split('|');
		            document.getElementById(update[0]).innerHTML = update[1];
		        }
		    }
		}


		var http = createRequestObject();
		var httpAlerts = createRequestObject();
		var showAlerts = 1;

	</script>


<?php

	require('incl-foia-top.php');
	if(!$auth) {
		require('incl-login.php'); }

?>

	<form action=search.php method=get name=frmSearch>
	<div class=toolLinks>
	<!- TOOLS SECTION -!>
	<a href=foiaform.php?type=new>New Request</a>
	<a href=buckets.php>Organize</a>


	<!- SEARCH WIDGET -!>
	&nbsp;&nbsp;|&nbsp;&nbsp;
	Search Requests: <input type=text name=searchterm size=40 id=searchterm>&nbsp;
	<select id=searchwhat name=searchwhat>
		<option value=mine>My Requests</option>
		<option value=all>All Requests</option>
	</select>
	<a href="javascript:frmSearch.submit()">Search</a>
	</div>

	<?php
		print "</form>";

		print "<table cellspacing=0 cellpadding=0 style='width:99%; height:30px' bgcolor=#cccccc><tr>";

			// first widget filters by status
			print "<td style='font-size:10pt'>";
				print "&nbsp;Show: ";
				print "<select id=filter name=filter style='font-size:9pt' onchange=\"sndReq('showlist','&orderby='+document.getElementById('orderby').value+'&filter='+document.getElementById('filter').value+'&bucket='+document.getElementById('bucket').value)\">";
					print "<option value=1>All Pending Requests</option>";
					print "<option value=2>All Requests</option>";
					print "<option value=3>All Closed Requests</option>";
					print "<option value=4>Abandoned Requests</option>";
					print "<option value=5>Appealed Requests</option>";
					print "<option value=6>Litigated Requests</option>";
				print "</select>";

			// second widget provides a sort
			print "&nbsp;&nbsp;&nbsp;&nbsp;Sort By: ";
			print "<select name=orderby id=orderby style='font-size:9pt' onchange=\"sndReq('showlist','&orderby='+document.getElementById('orderby').value+'&filter='+document.getElementById('filter').value+'&bucket='+document.getElementById('bucket').value)\">";
				print "<option value=date_filed>Date Filed</option>";
				print "<option value=title>Subject</option>";
				print "<option value=agency>Agency</option>";
				print "<option value=date_filed2>Oldest First</option>";
			print "</select>";


			print "&nbsp;&nbsp;&nbsp;&nbsp;In Bucket: ";
			print "<select name=bucket id=bucket style='font-size:9pt' onchange=\"sndReq('showlist','&orderby='+document.getElementById('orderby').value+'&filter='+document.getElementById('filter').value+'&bucket='+document.getElementById('bucket').value)\">";
				print "<option value=>All</option>";
				$sql = "SELECT bucket_id, bucket_name FROM foia.buckets WHERE bucket_owner = \"$nnusr\" ORDER BY bucket_name ";
				$result = $db->getArray($sql);
				foreach($result as $line) {
					if($bucket_id == $line[bucket_id]) {
						print "<option selected value=$line[bucket_id]>$line[bucket_name]</option>";
					} else {
						print "<option value=$line[bucket_id]>$line[bucket_name]</option>";
					}
				}

			print "</select>";
			print "&nbsp;(<a href=buckets.php>Explore</a>)";
		print "</td></tr></table>";

		// alerts div
		print "<div id=alertlist style='width:100%'></div>";

		// list div
		print "<div id=requestlist style='width:100%; background-color:white'></div>";




	?>


<?
	include('incl-foia-bottom.php');
?>

<script language=javascript>
	<?php print "sndReq('showlist','&bucket=$bucket_id');"; ?>
	sndReqAlerts('showalerts','');
	document.getElementById('searchterm').focus();
</script>

