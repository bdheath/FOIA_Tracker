<?php	include('incl-auth.php');

	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);


?>

<html>
<head>
	<title>eFOIA</title>
</head>

<body style="margin-top:0px; margin-left:0px; margin-right:0px" onload="load()">

<script type="text/javascript">

		var anychanges = 0;

		function load() {
			sndReqSettings('templateslist','');
		}

		function deleteTemplate(id) {
			var data = '&action=delete&id=' + id;
			sndReqSettings('templateslist',data);
		}

		function addTemplate() {
			var data = '&name=' + escape(document.getElementById('template_name').value) +
				'&filename=' + escape(document.getElementById('template_filename').value);
			sndReqSettings('addtemplate',data);
			sndReqSettings('templateslist','');
			document.getElementById('template_name').value = '';
			document.getElementById('template_filename').value = '';
		}

		function setChanges() {
			anychanges = 1;
			document.getElementById('savebutton').disabled = false;
		}

		function settingsSave() {
			var data = '&urlstem=' + escape(document.getElementById('urlstem').value);
			anychanges = 0;
			document.getElementById('savebutton').disabled = true;
			sndReqSettings('savesettings',data);
		}

		function sndReqSettings(action, data) {
			var httpRequest = createRequestObject();
			httpRequest.onreadystatechange = function() {
				if(httpRequest.readyState == 4) {
					var response = httpRequest.responseText;
					var update = response.split('|');
					document.getElementById(update[0]).innerHTML = update[1];
				}
			}
			var header = 'Content-Type:application/x-www-form-urlencoded; charset=UTF-8';
			httpRequest.open('post','foia-ajax-settings.php?type=' + action);
			httpRequest.setRequestHeader(header.split(':')[0],header.split(':')[1]);
			httpRequest.send(data);
		}

</script>

<?php

	include('incl-foia-top.php');

	if(!$auth) {
		include('incl-login.php'); }
?>

	<!- CONTENT AREA -!>
	<span style='font-family:arial; font-size:18pt'>Site Settings</span>
	<p><div id=settings></div>
	<span style='font-family:arial; font-size:10pt'>
		<?
			$urlstem = get_var('urlstem');
			print "<b>URL Stem</b>&nbsp;&nbsp;&nbsp;<input type=text onkeydown='setChanges()' onchange='setChanges()' value=\"$urlstem\" id=urlstem name=urlstem size=60>";
		?>
		<br />This is the address to eFOIA. It should take the form of <i>http://www.yoursite.com/foia/</i>. Note the ending slash.
		<p><input type=button id=savebutton disabled=true onclick='settingsSave()' value='Save'>
		<div id=settingsresults></div>
	</span>

	<p><span style='font-family:arial; font-size:18pt'>Templates</span>
	<span style='font-family:arial; font-size:10pt'>
	<p><b>New Template</b>
	<br>
	<table cellspacing=0 cellpadding=0 border=0 style='font-family:arial; font-size:10pt'>
	<tr><td>Template Name</td><td></td><td>Filename (in ../templates/)</td></tr>
	<tr><td><input type=text id=template_name size=30></td><td>&nbsp;&nbsp;-->&nbsp;&nbsp;</td><td><input type=text id=template_filename size=30>&nbsp;&nbsp;&nbsp;<input type=button onclick='addTemplate()' value='Add'></td></tr>
	</table>
	</span>
	<p>
	<div id=templatediv></div>




<?
	include('incl-foia-bottom.php');
?>

