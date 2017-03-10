<?php

	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);


	include('incl-auth.php');


?>

<html>
<head>
	<title>Dashboard - eFOIA</title>
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
	<style type="text/css">
		.closedRow { 
			color:gray;
		}
		.closedRow a { color:gray; text-decoration:none; }
	</style>


<?php

	include('incl-foia-top.php');

	if(!$auth) {
		include('incl-login.php'); }




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

	<h1>Dashboard</h1>
	<table cellspacing=3 cellpadding=1 style="font-family:arial; font-size:10pt;">
	<tr style="font-weight:bold;"><td>Request</td><td>Date&nbsp;Filed&nbsp;&nbsp;&nbsp;</td><td>Status</td><td>Reminders</td></tr>
	<?
		$sql = "SELECT requests.*, ma.alert_date, ma.alert_text, status.status 
				FROM (foia.requests INNER JOIN foia.status USING(status_id))
				LEFT JOIN ( SELECT foia_id, alert_date, alert_text FROM foia.alerts WHERE id IN ( SELECT DISTINCT id FROM foia.alerts WHERE alert_date <= CURRENT_DATE() ) ) AS ma USING(foia_id) WHERE owner='$nnusr' AND (deleted IS NULL OR deleted = 0) ORDER BY requests.foia_id DESC LIMIT 500";
				
		
		$r = $db->getArray($sql);
		foreach($r as $l) {
			$bg = 'lightgreen'; $fg = 'black';
			$cr = '';
			if(($l[status_id] == 5) || ($l[status_id] == 7)) {
				$bg = 'white'; $fg = 'green'; $cr="class='closedRow' "; }
			if(($l[status_id] == 6) || ($l[status_id] == 10)) {
				$bg = 'white'; $fg = 'red'; $cr = "class='closedRow'"; }
			if(($l[status_id] == 8)) {
				$bg = 'gray'; $fg = 'white'; }
			if(($l[status_id] == 2)) {
				$bg = 'yellow'; $fg = 'black'; }
			if(($l[status_id] == 3) || ($l[status_id] == 4)) {
				$bg = 'orange'; $fg = 'white'; }
			print "<tr valign='top' $cr style='margin-bottom:5px; margin-top:6px;'><td $cr><div $cr><a href=foiaform.php?type=edit&foia_id=$l[foia_id]>$l[title]</a></div></td><td>$l[date_filed]</td><td style='background-color:$bg; color:$fg;'>$l[status]</td><td style='color:red;'>$l[alert_text] $l[alert_date]</td></tr>";
		}

	?>
	</table>

<?
	include('incl-foia-bottom.php');
?>

