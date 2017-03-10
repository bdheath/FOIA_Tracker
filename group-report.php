<?php	include('incl-auth.php'); ?>

<html>
<head>
	<title>eFOIA</title>
</head>

<script type="text/javascript">

	function pgLoad() {
		window.onscroll = cs;
	}

	function winh() {
	var winW = 630, winH = 460;
		if (document.body && document.body.offsetWidth) {
		 winW = document.body.offsetWidth;
		 winH = document.body.offsetHeight;
		}
		if (document.compatMode=='CSS1Compat' &&
			document.documentElement &&
			document.documentElement.offsetWidth ) {
		 winW = document.documentElement.offsetWidth;
		 winH = document.documentElement.offsetHeight;
		}
		if (window.innerWidth && window.innerHeight) {
		 winW = window.innerWidth;
		 winH = window.innerHeight;
		}
		return winH;
	}
	
	var loadmore = 1;
	var currentBatch = 0;
	
	function cs() {
		var b = document.getElementById('pgbody');
		var wh = winh();
		var maxScroll = b.scrollHeight - wh;
		var curScroll = b.scrollTop;
		var remScroll = maxScroll - curScroll;
		if(remScroll < 200) {
			if(loadmore == 1) {
				getMoreResults();
			}
		}
	}
	
	function getMoreResults() {
		loadmore = 0;
		currentBatch = currentBatch + 1;
		var serviceUrl = 'foia-ajax-group-report.php?batch=' + currentBatch;
		sndReq(serviceUrl,'','getMoreResults');
	}
	
	function responseDispatcher(response,type) {
		if(type == 'getMoreResults') {
			var dl = document.getElementById('divLatest');
			var h = dl.innerHTML;
			h = h + response;
			dl.innerHTML = h;
			loadmore = 1;
		}
	}
	
</script>
<script type="text/javascript" src="/_common/ajaxframework.php"></script>

<body id=pgbody style="margin-top:0px; margin-left:0px; margin-right:0px" onload="pgLoad()">

<?php

	$reportsize = 25;

	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);


	include('incl-foia-top.php');

	if(!$auth) {
		include('incl-login.php'); }
	$db = new dbLink();
	
	$sql = "SELECT foia_id, title, owner, agency, date_filed, status_id FROM foia.requests WHERE (deleted = 0 OR deleted IS NULL) AND owner IN ( SELECT DISTINCT username FROM foia.groups_members WHERE group_id IN ( SELECT DISTINCT group_id FROM foia.groups_members WHERE username = \"$nnusr\") ) AND (deleted = 0 OR deleted IS NULL)  ORDER BY date_filed DESC LIMIT 0, $reportsize ";
	$r = $db->getArray($sql);

	print "<div id=divLatest>";
	print "<b>Latest Requests by Members of Your Groups</b>";
	print "<br /><br />";
	foreach($r as $l) {
		print "<a href=http://usat-eddataba/foia/print.php?foia_id=$l[foia_id]>$l[title]</a>";
		print "<br>$l[agency] <span style='color:gray;'>(Filed $l[date_filed] by $l[owner])</span><br /><br />";
	}
	print "</div>";
	
?>








<?
	include('incl-foia-bottom.php');
?>



