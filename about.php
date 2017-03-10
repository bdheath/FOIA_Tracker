<?php	

	include('incl-auth.php');

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
?>

	<!- CONTENT AREA -!>

	<table cellspacing=0 cellpadding=0 style='width:80%'><tr><td>
	<span style='font-size:14pt; font-weight: bold'>About eFOIA</span>
	<p style='font-size:12pt; line-height:16pt'>This is version <span style='color:#993300'><? echo $version_no; ?></span></p>
	<p style='font-size:12pt; line-height:16pt'>eFOIA is an open-source web tool to track open records requests for multiple reporters and editors in newsrooms. Among the features in this release:</p>
	<ul style='font-size:12pt; line-height:16pt'>
		<li>Create, track and search multiple open records requests.</li>
		<li>Generate request letters using templates you create.</li>
		<li>Organize requests into custom buckets to keep track of all the requests for a particular story, subject or agency. Each request can be assigned to multiple buckets.</li>
		<li>Create custom alerts to remind you when it's time to follow up on a request. (The system automatically creates one alert to tell you it's time to follow up on a pending request.) Then subscribe to those alerts as an RSS feed, and get daily e-mail reminders.</li>
		<li>Integrates seamlessly with OpenInfo user authentication schemes to minimize administrative overhead.</li>
		<li>Reporter-proof data entry forms that save work even when you forget to hit the save button.</li>
		<li>Automatically store a complete history of every modification to each request.
		<li>Upload and search files. Integrate with <a href=http://documentcloud.org>DocumentCloud</a>.</li>
		<li>Daily e-mail reminders for FOIA alert.
		<li>Automatically cross-reference requests based on the records they're seeking.</li>
	</ul>
	<p style='font-size:12pt; font-weight: bold'>To-Do List</p>
	<p style='font-size:12pt; line-height:16pt'>These features will be implemented in future versions:</p>
	<ul style='font=font-size:12pt; line-height:16pt'>
		<li>Paged search results.</li>
		<li>Improved tracking and control of archived versions of requests.</li>
	</ul>
	<p style='font-size:12pt; line-height:18pt'>You can direct feedback to <a href='mailto:brad.heath@gmail.com'>brad.heath@gmail.com</a>.</p>

	</td></tr></table>

<?
	include('incl-foia-bottom.php');
?>

