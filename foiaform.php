<?php	include('incl-auth.php'); ?>

<?php

	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);

		require_once('incl-foia-tools.php');

		$d = GetDate();
		$day = fdate($d);
		$today = fdate(getdate(time()));
		$nextweek = fdate(getdate(time()+(86400*30)));

		if ($type == "new") {
			// create new record, clean up old ones
			$sql = "SELECT foia_id FROM foia.requests WHERE status_id = 9999 AND owner=\"$nnusr\" ";
			$r = $db->getArray($sql);
			foreach($r as $line) {
				$s = "DELETE FROM foia.alerts WHERE foia_id = $line[foia_id] ";
				$db->run($s);
			}
			$sql = "DELETE FROM foia.requests WHERE status_id = 9999 AND owner=\"$nnusr\" ";
			$db->run($sql);
			$sql = "INSERT INTO foia.requests(status_id,owner,date_entered) VALUES(9999,\"$nnusr\",DATE(NOW())) ";
			$db->run($sql);
			$sql = "SELECT MAX(foia_id) AS fid FROM foia.requests WHERE owner=\"$nnusr\" ";
			$foia_id = $db->getOne($sql);

			// add new follow-up alert
			$sql = "INSERT INTO foia.alerts(foia_id, alert_owner, date_created, alert_date, alert_text)
				VALUES($foia_id, \"$nnusr\", DATE(NOW()), \"$nextweek\", \"This request has now been pending for a month.\") ";
			$db->run($sql);
		} else {
			$type = "edit"; }

		print "<script language=javascript> var fid = $foia_id; </script>";

		// read values
		$sql = "SELECT * FROM foia.requests WHERE foia_id = $foia_id ";
		$line = $db->getRow($sql);
		extract($line);
		$title = stripslashes($title);
		$contact_name = stripslashes($contact_name);
		$notes = stripslashes($notes);
		$contact_number = stripslashes($contact_number);
		$agency = stripslashes($agency);
		$fee_method = stripslashes($fee_method);
		$records_sought = stripslashes($records_sought);
		$decedent = stripslashes($decedent);

		if($type == "new") {
			$date_filed = $today; }

?>

<html>
<head>
	<?php print "<title>eFOIA | Request | $title</title>"; ?>
	
	
</head>

<body style="margin-top:0px; margin-left:0px; margin-right:0px; margin-bottom:0px;"  onload="load()">
<div id=bodyarea>

<?php

	include('incl-foia-top.php');

	if(!$auth) {
		include('incl-login.php'); }

?>

	<SCRIPT LANGUAGE="JavaScript" SRC="calendar.js"></SCRIPT>
	<script type="text/javascript" src="foiaform.js"></script>

	<script language=javascript>



		var IE = document.all?true:false;
		if (!IE) document.captureEvents(Event.MOUSEMOVE)
		document.onmousemove = getMouseXY;
		var tempX = 0;
		var tempY = 0;
		var isthing = 0;
		var isdragging = 0;
		var l = 20;
		var t = 20;
		var e;
		var pos;
		var updateFileWindow = true;
		var doNotWarnOfDuplicateTitles = 0;

		
		function findPos(obj) {
			var curleft = curtop = 0;
			if (obj.offsetParent) {
				curleft = obj.offsetLeft
				curtop = obj.offsetTop
				while (obj = obj.offsetParent) {
					curleft += obj.offsetLeft
					curtop += obj.offsetTop
				}
			}
			return [curleft,curtop];
		}

		function load() {
			isdragging = 0;
			e = document.getElementById('notes');
			pos = findPos(e);
			toggleDecedent();
		}

		function getMouseXY(e) {
			if (IE) { // grab the x-y pos.s if browser is IE
			tempX = event.clientX + document.body.scrollLeft;
			tempY = event.clientY + document.body.scrollTop;
			}
			else {  // grab the x-y pos.s if browser is NS
			tempX = e.pageX;
			tempY = e.pageY;
			}
			if (tempX < 0){tempX = 0;}
			if (tempY < 0){tempY = 0;}

			if(isdragging == 1) {
				var s = document.getElementById('notes');
				s.style.height = tempY - pos[1] - 5 + 'px';
			}

			return true;
		}

		function toggledragging(val) {
			isdragging = val;
			var s = document.getElementById('notes');
			if(val == 1) {
				s.style.color = 'Gray';
			} else {
				s.style.color = 'Black';
			}
		}

		function expandNotes() {
			var e = document.getElementById('notes');
			e.style.height = e.offsetHeight + 100 + 'px';
		}

		// page-specific
		var httpAlertbox = createRequestObject();
		var http = createRequestObject();
		var http2 = createRequestObject();
		var httpSave = createRequestObject();
		var noconfirm = 0;
		var anychanges = 0;
		var goHomeFlag = 0;


		function updateTitle() {
			setTimeout('updateTitle2()',1000);
		}

		function updateTitle2() {
			document.title = 'eFOIA | Request | ' + document.getElementById('title').value;
			anychanges = 1;
			titleUnWarning();
		}

		function madeChanges() {
			anychanges = 1;
			document.getElementById('btnSave').disabled = false;
		}

		function deleteRequest(foia_id) {
			if(confirm('Are you sure you want to delete this request?\n\nYou should only delete requests that were created in error. You should save closed requests to keep a record of the information that has been requested and received.\n\nOnce you delete a request you cannot recover it.')) {
				noconfirm = 1;
				saveAJAX();
				sndReq('delete','&id='+foia_id);
				document.getElementById('btnClose').disabled = true;
				document.getElementById('btnSave').disabled = true;
				setTimeout('goHome()',5000);
			}
		}

		function goHome() {
			window.location='index.php';
		}

		function funTest() {
			alert("IT WORKED");
		}

		function delayedReloadAlertboxHC() {
			setTimeout('reloadAlertboxHC()',2000);
		}

		function reloadAlertboxHC() {
			<?php print "sndReqAlertbox('alertbox','&foia_id=$foia_id'); "; ?>
		}

		function reloadAlertPaneHC() {
			<?php print "sndReq('seealerts','&foia_id=$foia_id'); "; ?>
		}

		function snoozeAlert(alert_id,foia_id) {
			sndReqAlertbox('alertbox','&action=snooze&alert_id='+alert_id+'&foia_id='+foia_id);
			setTimeout('reloadAlertPaneHC()',1000);
		}

		function clearAlert(alert_id, foia_id) {
			if (confirm("Are you sure you want to clear this alert? If you clear an alert, it will be gone forever. ")) {
				sndReqAlertbox('alertbox','&action=clear&alert_id='+alert_id+'&foia_id='+foia_id);
				setTimeout('reloadAlertPaneHC()',1000);
			}

		}

		function sndReq(action, p2) {
		    var rnd_seed = Math.random() * Math.round(Math.random() * 10000000);
		    http.open('post', 'foia-ajax-foiaform.php?type='+action+'&id='+p2+'&rndseed='+rnd_seed);
		    http.onreadystatechange = handleResponse;
		    http.send(null);
		}

		function sndReqAlertbox(action, p2) {
		    var rnd_seed = Math.random() * Math.round(Math.random() * 10000000);
		    httpAlertbox.open('post', 'foia-ajax-foiaform.php?type='+action+'&id='+p2+'&rndseed='+rnd_seed);
		    httpAlertbox.onreadystatechange = handleResponseAlertbox;
		    httpAlertbox.send(null);
		}

		function sndReqBucket(action, p2) {
		    var rnd_seed = Math.random() * Math.round(Math.random() * 10000000);
		    http2.open('post', 'foia-ajax-foiaform.php?type='+action+'&id='+p2+'&rndseed='+rnd_seed);
		    http2.onreadystatechange = handleResponseBucket;
		    http2.send(null);
		}

		function sndReqAlertbox(action, p2) {
		    var rnd_seed = Math.random() * Math.round(Math.random() * 10000000);
		    httpAlertbox.open('post', 'foia-ajax-foiaform.php?type='+action+'&id='+p2+'&rndseed='+rnd_seed);
		    httpAlertbox.onreadystatechange = handleResponseAlertbox;
		    httpAlertbox.send(null);
		}

		window.onbeforeunload = function() { 
			if(anychanges == 0) { 
			} else { 
				return "You have made changes to this request that have not been saved.\n\nAre you sure you want to leave the page and lose your changes?\n\n"; 
			}
		};
		
		function unloadPrompt() {
			alert('I am preparing to unload');
			if(anychanges == 1) {
				if(noconfirm != 1) {
					var agree = confirm("Do you want to save them now?\n\n ","Really?");
					if(agree) { saveAJAX(); }
				}
			}
		}

		function setDelay() {
			setTimeout('clearSaveStatus()',2000);
		}

		function clearSaveStatus() {
			document.getElementById('savearea').innerHTML = '&nbsp;';
			document.getElementById('btnClose').disabled = false;
		}

		function autoSave() {
			if(anychanges == 1 ) {
				saveAJAX(); }
			setTimeout("autoSave()",1000 * 60);
		}
		setTimeout("autoSave()",1000 * 60);
		
		function saveAJAX() {
			var rnd_seed = Math.random() * Math.round(Math.random() * 10000000);
			var strSave = 'foia-ajax-foiaform.php?type=save'
			var data =  'title='+escape(document.getElementById('title').value)
					+ '&agency=' + escape(document.getElementById('agency').value)
					+ '&date_filed=' + escape(document.getElementById('date_filed').value)
					+ '&date_acknowledged=' + escape(document.getElementById('date_acknowledged').value)
					+ '&date_closed=' + escape(document.getElementById('date_closed').value)
					+ '&status_id=' + document.getElementById('status_id').value
					+ '&records_sought=' + escape(document.getElementById('records_sought').value)
					+ '&notes=' + escape(document.getElementById('notes').value)
					+ '&contact_name=' + escape(document.getElementById('contact_name').value)
					+ '&contact_number=' + escape(document.getElementById('contact_number').value)
					+ '&fee_method=' + escape(document.getElementById('fee_method').value)
					+ '&fee_initial=' + document.getElementById('fee_initial').value
					+ '&fee_charged=' + document.getElementById('fee_charged').value
					+ '&fee_paid=' + document.getElementById('fee_paid').value
					+ '&death_request=' + document.getElementById('death_request').value
					+ '&decedent=' + escape(document.getElementById('decedent').value)
					+ '&rnd_seed='+rnd_seed
					+ '&foia_id='+fid;
			document.getElementById('savearea').innerHTML = "<span style='color:gray'>Saving...</span>";
			var header = 'Content-Type:application/x-www-form-urlencoded; charset=UTF-8';
			httpSave.open('post',strSave);
			httpSave.setRequestHeader(header.split(':')[0],header.split(':')[1]);
			httpSave.onreadystatechange = handleSave;
			httpSave.send(data);
			document.getElementById('btnSave').disabled = true;
			document.getElementById('btnClose').disabled = true;
			anychanges = 0;
		}

		function handleSave() {
				if(httpSave.readyState == 4) {
					var response = httpSave.responseText;
				        var update = new Array();
				        if(response.indexOf('|' != -1)) {
				            update = response.split('|');
				            document.getElementById(update[0]).innerHTML = update[1];
				            if(goHomeFlag == 1) {
				            	if(update[2] == 'ERROR') {
				            		goHomeFlag = 0;
				            	} else {
				            		goHome(); }
				            }
				        }
				}
		}

		function contextSearch() {
			var title = document.getElementById('title').value;
			if(doNotWarnOfDuplicateTitles == 0) {
				if(title.length >= 5) {
					var serviceUrl = 'foia-ajax-foiaform.php?type=relatedtitle&foia_id=' + fid + '&st=' + escape(title);
					ajaxReq(serviceUrl,'','relatedtitle');	
				} else { 
					titleUnWarning(); 
				}
			}
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

		function uploadFile() {
			var serviceUrl = 'foia-ajax-files.php?type=upload&foia_id=' + fid;
			ajaxReq(serviceUrl,'','files');	
			updateFileWindow = false;
		}
		
		function autoListFiles() {
			if(updateFileWindow == true) {
				listFiles();
				setTimeout(autoListFiles, 1000 * 10);
			}
		}
		
		function listFiles() {
			var serviceUrl = 'foia-ajax-files.php?type=showfiles&foia_id=' + fid;
			ajaxReq(serviceUrl,'','files');	
			updateFileWindow = true;
		}

		function findRelated() {
			var serviceUrl = 'foia-ajax-foiaform.php?type=related&foia_id=' + fid;
			ajaxReq(serviceUrl,'','related');	
		}
		
		function ajaxReq(serviceurl, data, type) {
			var httpRequest = createRequestObject();
			var rnd_seed = Math.random() * Math.round(Math.random() * 10000000);
			serviceurl = serviceurl + '&r=' + rnd_seed;
			httpRequest.onreadystatechange = function() {
				if(httpRequest.readyState == 4) {
					// dispatch responses (syn of responseDispatcher()
					response = httpRequest.responseText;
					if(type == 'files') {
						document.getElementById('files').innerHTML = response;	
					}
					if(type == 'related') {
						document.getElementById('divRelated').innerHTML = response;
					}
					if(type == 'relatedtitle') {
						if(response == '') {
							titleUnWarning();
						} else {
							titleWarning(response);
						}	
					}
				}
			}
			var header = 'Content-Type:application/x-www-form-urlencoded; charset=UTF-8';
			httpRequest.open("post",serviceurl);
			httpRequest.setRequestHeader(header.split(':')[0],header.split(':')[1]);
			httpRequest.send(data);
		}

		function titleUnWarningDel() {
			setTimeout('titleUnWarning()',1000);	
		}
		
		function titleUnWarningPerm() {
			doNotWarnOfDuplicateTitles = 1;
			titleUnWarning();	
		}

		function titleUnWarning() {
			var d = document.getElementById('divContext');
			d.style.position = 'absolute';
			d.style.borderWidth = '0px;';
			d.style.border = 'none';
			d.style.paddingLeft = 0;
			d.style.paddingRight = 0;
			d.style.paddingTop = 0;
			d.style.fontFamily = 'arial';
			d.style.fontSize = '10pt';
			d.style.backgroundColor = '';
			d.style.borderColor = '';
			d.style.width = 0;
			d.style.height = 0;
			d.innerHTML = '';
			
		}


		function titleWarning(response) {
			var t = document.getElementById('title');
			var d = document.getElementById('divContext');
			d.style.position = 'absolute';
			d.style.borderWidth = '1px;';
			d.style.border = 'solid';
			d.style.paddingLeft = 5;
			d.style.paddingRight = 5;
			d.style.paddingTop = 5;
			d.style.fontFamily = 'arial';
			d.style.fontSize = '10pt';
			d.style.backgroundColor = 'pink';
			d.style.borderColor = 'red';
			var pos = findPos(t);
			d.style.top = pos[1] + t.offsetHeight;
			d.style.left = pos[0];
			d.style.width = t.offsetWidth - 6;
			d.style.overflow = 'auto';
			d.style.height = '';
			d.innerHTML = response;
			
			
		}

		function handleResponseAlertbox() {
		    if(httpAlertbox.readyState == 4){
		        var response = httpAlertbox.responseText;
		        var update = new Array();
		        if(response.indexOf('|' != -1)) {
		            update = response.split('|');
		            if(document.getElementById(update[0]).innerHTML == '') {
		            	document.getElementById(update[0]).style.opacity = 0;
		            	document.getElementById(update[0]).style.filter='alpha(opacity=100)';
		            	fadeIn(update[0],0);
		            }
		            document.getElementById(update[0]).innerHTML = update[1];
		        }
		    }
		}

		function handleResponseBucket() {
		    if(http2.readyState == 4){
		        var response = http2.responseText;
		        var update = new Array();
		        if(response.indexOf('|' != -1)) {
		            update = response.split('|');
		            document.getElementById(update[0]).innerHTML = update[1];
		        }
		    }
		}

		function reallyLeave() {
			noconfirm = 1;
			saveAJAX();
			goHomeFlag = 1;
		}
		
		function toggleDecedent() {
			if(document.getElementById('death_request').checked) {
				document.getElementById('decedent').disabled = false;
				document.getElementById('decedent').style.backgroundColor = ''; 
				if(document.getElementById('agency').value == '') {
					document.getElementById('agency').value = 'Federal Bureau of Investigation';
				}
			} else {
				document.getElementById('decedent').disabled = true; 
				document.getElementById('decedent').style.backgroundColor = '#cccccc'
			}
		}
		
		function changeFilename() {
			if(document.getElementById('doctitle').value == '') {
				document.getElementById('doctitle').value = document.getElementById('docfilename').value;	
			}	
		}


	</script>

	<div id=alertbox style='width:100%; opacity:1; filter:alpha(opacity=100); background-color:#FFFFFF'></div>


	<?php

		// check that only this request's owner can edit the details
		if(($type == "edit") && ($owner != $nnusr)) {
			print "You are not authorized to edit this request.";
			include('incl-foia-bottom.php');
			die();
		}

	?>

	<table cellspacing=0 cellpadding=0 border=0 style="width:98%">
	<tr>
		<td valign=top>
		<!- MAIN PANEL -!>
		<span style="font-size:10pt">
		<form id=frmfoia name=frmfoia action=foiaform-save.php>

		<?php

			print "<div id=requestarea>";
			print "<table cellspacing=0 cellpadding=0 border=0 width=100%><tr>";
				print "<td>Request Subject<br><input onblur='titleUnWarningDel()' onchange=updateTitle() onkeypress=madeChanges() onkeyup='contextSearch()' type=text id=title name=title style='width:95%' maxlength=255 value=\"$title\"></td>";
				print "<td style='width:250px'>Status<br><select id=status_id name=status_id onchange=madeChanges() onkeypress=madeChanges()> ";
					$s = "SELECT status, status_id FROM foia.status ";
					$r = $db->getArray($s);
					foreach($r as $l) {
						if($l[status_id] == $status_id) {
							print "<option selected value=$l[status_id]>$l[status]</option>";
						} else {
							print "<option value=$l[status_id]>$l[status]</option>";
						}
					}
					print "</select>";
				print " </td> ";
				print "</td></tr></table>";


			print "<br><table cellspacing=0 cellpadding=0 border=0 width=100% style='font-size:10pt'><tr>";
				print "<td>Date Filed<br><input onchange=madeChanges() onkeypress=madeChanges() type=text size=15 id=date_filed name=date_filed value=\"$date_filed\"> <a href=\"javascript:void(0)\" onClick=\"showCalendar(frmfoia.date_filed, 'YY-MM-DD','Choose data');madeChanges()\"><IMG SRC=\"CAL-icon.gif\" BORDER=\"0\" width=\"16\" height=\"16\" alt=\"Click for calendar\"></a></td>";
				print "<td>Date Confirmed<br><input onchange=madeChanges() onkeypress=madeChanges() type=text size=15 id=date_acknowledged name=date_acknowledged value=\"$date_acknowledged\"> <a href=\"javascript:void(0)\" onClick=\"showCalendar(frmfoia.date_acknowledged, 'YY-MM-DD','Choose data');madeChanges()\"><IMG SRC=\"CAL-icon.gif\" BORDER=\"0\" width=\"16\" height=\"16\" alt=\"Click for calendar\"></a></td>";
				print "<td>Date Closed<br><input onchange=madeChanges() onkeypress=madeChanges() type=text size=15 id=date_closed name=date_closed value=\"$date_closed\"> <a href=\"javascript:void(0)\" onClick=\"showCalendar(frmfoia.date_closed, 'YY-MM-DD','Choose data');madeChanges()\"><IMG SRC=\"CAL-icon.gif\" BORDER=\"0\" width=\"16\" height=\"16\" alt=\"Click for calendar\"></a></td>";
				print "<td>Last Modified<br><input onchange=madeChanges() onkeypress=madeChanges() type=text size=20 disabled value=\"$date_changed\"></td>";
				print "</tr></table>";
			print "Records Sought<br><textarea onchange=madeChanges() onkeypress=madeChanges() rows=5 style='width:95%' id=records_sought name=records_sought>$records_sought</textarea>";
			print "<br>Agency<br><input onchange=madeChanges() onkeypress=madeChanges() type=text id=agency name=agency value=\"$agency\" size=70>";
			print "<br /><table cellspacing=0 cellpadding=0 border=0 style='font-size:10pt;'><tr>";
			print "<td valign=top>";
			print "FBI Request for Deceased Person&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			if($death_request == 1) {
				$chk = " checked ";
			} else { 
				$chk = ""; }
			print "<br /><input type=checkbox $chk id=death_request onchange='toggleDecedent()'/>";
			print "</td>";
			print "<td valign=top>Decedent<br /><input type=text id=decedent value=\"$decedent\" size=65 />";
			
			print "</tr></table>";
			print "<table cellspacing=0 cellpadding=0 border=0 width=100% style='font-size:10pt'><tr>";
				print "<td width=40%>Contact Person<br><input onchange=madeChanges() onkeypress=madeChanges() id=contact_name type=text style='width:95%' name=contact_name value=\"$contact_name\"></td>";
				print "<td width=60%>Contact Numbers / E-Mail<br><input onchange=madeChanges() onkeypress=madeChanges() id=contact_number type=text style='width:95%' name=contact_number value=\"$contact_number\"></td>";
				print "</tr></table>";
			print "<table cellspacing=0 cellpadding=0 border=0 width=100% style='font-size:10pt'><tr>";
				print "<td>Initial Fee<br>$<input onchange=madeChanges() onkeypress=madeChanges() size=10 id=fee_initial type=text name=fee_initial value=\"$fee_initial\"></td>";
				print "<td>Fee Charged<br>$<input onchange=madeChanges() onkeypress=madeChanges() size=10 id=fee_charged type=text name=fee_charged value=\"$fee_charged\"></td>";
				print "<td>Fee Paid<br>$<input onchange=madeChanges() onkeypress=madeChanges() size=10 id=fee_paid type=text name=fee_paid value=\"$fee_paid\"></td>";
				print "<td>Payment Method<br><input onchange=madeChanges() onkeypress=madeChanges() id=fee_method type=text name=fee_method value=\"$fee_method\" size=40></td>";
				print "</tr></table>";
			if(strlen($notes) >= 2000) {
				$notesrows = 30;
			} elseif (strlen($notes) >= 1000) {
				$notesrows = 20;
			} elseif (strlen($notes) <= 100) {
				$notesrows = 5;
			} else {
				$notesrows = 10; }
			print "Notes<br><textarea onchange=madeChanges() onkeypress=madeChanges() id=notes name=notes style='width:95%' rows=$notesrows>$notes</textarea>";
			print "<div id=clickdiv style=\"height:5px; width:95%; background-color:gray;\" onmousedown=\"toggledragging(1)\" onmouseup=\"toggledragging(0);\" ></div>";
			print "<span style='font-size:8pt'><a href='javascript:expandNotes()'>Expand</a></span>";
		?>
		</span>
		<div id=divRelated></div>
		</div>
		</form>
		

		</td>
		<td valign=top style="width:10px"></td>
		<td valign=top style="width:200px">

		<!- SIDE PANEL -!>
			<input type=button id=btnSave onClick=saveAJAX() value="Save Changes">
			<input type=button id=btnClose onClick=reallyLeave() value="Close">
			<div id=savearea>&nbsp;</div>
			<?php
				if($type == "new") {
					print "<a href='javascript:letterStart($foia_id)'>Create a Letter</a><p>";
				}
				print "<table cellspacing=0 cellpadding=0 border=0><tr><td><img src=icon-printer.gif align=top>&nbsp;</td><td><a href=print.php?foia_id=$foia_id onclick='saveAJAX()'>Printable Report</a></td></tr>";

				$sql = "SELECT COUNT(*) AS he FROM foia.requests_versions WHERE foia_id = $foia_id ";
				$z = $db->getRow($sql);

				if($type != "new") {
					print "<tr><td></td><td><a href='javascript:letterStart($foia_id)'>Letter Maker</a></td></tr>";
				}
				print "<tr><td></td><td><a href=history.php?id=$foia_id onclick='saveAJAX()'>History ($z[he])</a></td></tr>";
				print "</table>";

			?>

			<p>
			<div id=alerts></div>

			<p>
			<div id=buckets></div>
			
			<p>
			<div id=files></div>
		</td>

	</tr>
	</table>

<div id=shadow></div>
<iframe id=letterdivblocker scrolling=0 style='height:0px; width:0px; border:none'></iframe>
<iframe id=uploadFrame name=uploadFrame style='height:0px; width:0px; border:0px; overflow:none;' src=''></iframe>
<div id=letterdiv style='overflow:none'></div>
<div id=divContext></div>


<?
//	print "<textarea rows=10 style='width:100%' id=testarea></textarea>";

	print "<a href=\"javascript:deleteRequest($foia_id)\">Delete Request</a>";
	include('incl-foia-bottom.php');



?>

<script language=javascript>
	<?php
		// dynamic javascript calls to fetch page elements
		print "sndReq('seealerts','&foia_id=$foia_id');";
		print "sndReqBucket('seebuckets','&foia_id=$foia_id');";
		print "reloadAlertboxHC();";
		print "autoListFiles();";
	?>
	findRelated();
//	document.getElementById('title').focus();
	document.getElementById('btnSave').disabled = true;
</script>