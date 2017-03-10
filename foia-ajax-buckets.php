<?php

	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);


	$nnusr = '';
	$nnusr = $_COOKIE[nnusr];

	include('incl-dbconnect.php');
	include('incl-auth.php');

	$db = new dbLink();

	if($type == "newbucket") {

		print "bucketarea|";

		print "Bucket Name:&nbsp;&nbsp;&nbsp;";
		print "<input type=text size=60 maxlength=23 id=bucket_name>";
		print "&nbsp;&nbsp;";
		print "<a href=\"javascript:sndReq('showbuckets','&action=new&bucket_name='+escape(document.getElementById('bucket_name').value))\">Create</a>";
		print "&nbsp;&nbsp;&nbsp;<a href=\"javascript:sndReq('showbuckets','')\">Cancel</a>";

		print "<script language=javascript>document.getElementById('bucket_name').focus;</script>";

	}

	if($type == "showbuckets") {

		print "bucketarea|";
		if($seebucket == '') {
			$seebucket = 0; }

		if($action == "delete") {
			$sql = "DELETE FROM foia.buckets_contents WHERE bucket_id IN(SELECT bucket_id FROM foia.buckets WHERE bucket_owner=\"$nnusr\" AND bucket_id=$bucket_id) ";
			$db->run($sql);
			$sql = "DELETE FROM foia.buckets WHERE bucket_id = $bucket_id AND bucket_owner = \"$nnusr\" ";
			$db->run($sql);
		}

		if($action == "new") {
			$bucket_name = addslashes(urldecode($bucket_name));
			$sql = "SELECT bucket_name FROM foia.buckets WHERE bucket_name=\"$bucket_name\" AND bucket_owner=\"$nnusr\" ";
			$r = $db->getArray($sql);
			if($db->numRows() == 0) {
				$sql = "INSERT INTO foia.buckets(bucket_name,bucket_owner) VALUES(\"$bucket_name\", \"$nnusr\") ";
				$db->run($sql);
			}
		}

		$sql = "SELECT bucket_id, bucket_name FROM foia.buckets WHERE bucket_owner=\"$nnusr\" ORDER BY bucket_name ";
		$result = $db->getArray($sql);
		if($db->numRows() == 0) {

			print "<b>You have not set up any buckets yet.</b>";

		} else {

			print "<b>You have set up these buckets to organize your requests:</b> ";
			foreach($result as $line) {
				//print "<br>$line[bucket_name]";
				if($seebucket == $line[bucket_id]) {
					print "<br><a href=\"javascript:sndReq('showbuckets','')\">$line[bucket_name]</a>";
				} else {
					print "<br><a href=\"javascript:sndReq('showbuckets','&seebucket=$line[bucket_id]')\">$line[bucket_name]</a>"; }
				print "&nbsp;&nbsp;&nbsp;&nbsp;<span style='font-size:8pt'><a href=\"javascript:deleteBucket($line[bucket_id],$seebucket)\">Delete</a></span>";
				if($seebucket == $line[bucket_id]) {
					$s = "SELECT requests.title, requests.agency, requests.foia_id FROM foia.requests INNER JOIN foia.buckets_contents ON requests.foia_id = buckets_contents.foia_id WHERE buckets_contents.bucket_id = $seebucket ORDER BY title ASC ";
					$r = $db->getArray($s);
					print "<span style='font-size:9pt'>";
					if($db->numRows() == 0) {
						print "<br>&nbsp;&nbsp;&nbsp;No requests are assigned to this bucket.";
					} else {
						print "<br>&nbsp;&nbsp;&nbsp;" . $db->numRows() . " requests in this bucket.";
						foreach($r as $l) {
							$title = stripslashes($l[title]);
							print "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=foiaform.php?type=edit&foia_id=$l[foia_id]>$title</a> ($l[agency])";
						}
						print "<br>";
					}
					print "</span>";
				}
			}
		}

	}

?>