<LINK REL="SHORTCUT ICON"
       HREF="foia.ico">
<?php
	require_once('incl-dbconnect.php');
	require_once('incl-log.php');
	if(gettype($db) == 'NULL') {
		$db = new dbLink();
	}
	
	function get_var($var) {
		if(gettype($db) == 'NULL') {
			$db = new dbLink(); }
		$sql = "SELECT $var FROM foia.prefs ";
		return $db->getOne($sql);
	}


	$version_no = get_var('version');

?>
	<script language=javascript src="foia.js"></script>

	<script type="text/javascript">

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

		function prefsOpen() {
			var a = document.getElementById('fp');
			var pos = findPos(a);
			var p = document.getElementById('divprefs');
			p.style.opacity = 0;
			p.style.filter = 'alpha(opacity=0)';
			p.style.top = pos[1] + a.offsetHeight + 7 + 'px';
			p.style.left = pos[0] - 50 +  'px';
			p.style.width = '400px';
			p.style.height = '380px';
			p.style.position = 'absolute';
			p.style.border = 'solid';
			p.style.borderWidth = '1px';
			p.style.backgroundColor = 'white';
			p.style.padding = '7px';
			var e = document.getElementById('divblocker');
			e.style.top = pos[1] + a.offsetHeight + 'px';
			e.style.left = pos[0] - 50 + 'px';
			e.style.height = '320px';
			e.style.width = '400px';
			e.style.position = 'absolute';
			var e = document.getElementById('divprefsshadow');
			e.style.opacity = 0;
			e.style.filter = 'alpha(opacity=0)';
			e.style.top = pos[1] + a.offsetHeight + 5 + 'px';
			e.style.left = pos[0] - 45 + 'px';
			e.style.height = '320px';
			e.style.width = '400px';
			e.style.position = 'absolute';
			e.style.backgroundColor='#333333';
			var d = document.getElementById('divshadow');
			var m = document.getElementById('divMain');
			var pos = findPos(m);
			d.style.top = pos[1] + 'px';
			d.style.left = pos[0] + 'px';
			d.style.position = 'absolute';
			d.style.width = m.offsetWidth;
			d.style.height = m.offsetHeight;
			d.style.backgroundColor = 'black';
			d.style.opacity = 0;
			d.style.filter = 'alpha(opacity=0)';
			sndReqPrefs('prefs','');
			fadeInFast('divprefs',0);
			fadeInFast('divblocker',0);
			fadeInFastPartial('divshadow',0,.75);

		}

		function prefsChangePassword() {
			sndReqPrefs('prefsChangePassword','');
		}

		function prefsChangePasswordSubmit() {
			var data = 'pw1=' + escape(document.getElementById('pw1').value) +
				'&pw2=' + escape(document.getElementById('pw2').value);
			sndReqPrefs('prefsChangePassword',data);
		}

		function prefsClose() {
			var p = document.getElementById('divprefs');
			p.style.backgroundColor = '';
			p.style.border = '';
			p.style.height = '0px';
			p.style.width = '0px';
			p.style.padding = '0px';
			p.style.borderWidth = '0px';
			p.innerHTML = '';
			var p = document.getElementById('divprefsshadow');
			p.style.height = '0px';
			p.style.width = '0px';
			p.style.border = '';
			p.style.backgroundColor='';
			var p = document.getElementById('divblocker');
			p.style.height = '0px';
			p.style.border = '';
			p.style.width = '0px';
			p.style.borderWidth = '0px';
			p.style.backgroundColor='';
			p.style.opacity = 0;
			p.style.filter = 'alpha(opacity=0)';
			var d = document.getElementById('divshadow');
			d.style.top = '1px';
			d.style.left = '1px';
			d.style.position = 'absolute';
			d.style.width = 0 + 'px';
			d.style.height = 0 + 'px';
			d.style.backgroundColor = '';
			d.style.opacity = .7;
			d.style.filter = 'alpha(opacity=70)';
			
		}

		function prefsSave() {
			var data = '1=1&name=' + escape(document.getElementById('name').value) +
				'&phone=' + escape(document.getElementById('phone').value) +
				'&email=' + escape(document.getElementById('email').value) +
				'&address=' + escape(document.getElementById('address').value) +
				'&city=' + escape(document.getElementById('city').value) +
				'&state=' + escape(document.getElementById('state').value) +
				'&zip=' + escape(document.getElementById('zip').value) + 
				'&email_alerts=' + document.getElementById('email_alerts').value +
				'&email_new_group_report=' + document.getElementById('email_new_group_report').value +
				'&dc_password=' + document.getElementById('dc_password').value +
				'';
//			alert(document.getElementById('dc_password').value);
			sndReqPrefs('prefsSave',data);
		}

		function sndReqPrefs(action, data) {
			var httpRequest = createRequestObject();
			httpRequest.onreadystatechange = function() {
				if(httpRequest.readyState == 4) {
					var response = httpRequest.responseText;
					var update = response.split('|');
					document.getElementById('divprefs').innerHTML = update[1];
					if(update[2] == 'prefsClose') {
						prefsClose(); }
				}
			}
			var header = 'Content-Type:application/x-www-form-urlencoded; charset=UTF-8';
			var qs = 'foia-ajax-prefs.php?type=' + action;
			httpRequest.open('post',qs);
			httpRequest.setRequestHeader(header.split(':')[0],header.split(':')[1]);
			httpRequest.send(data);
		}

	</script>

	<style type="text/css">
		body {
			font-family: Arial;
			font-size: 12pt;
			color: black;
		}
		a:link {
			color: blue;
			text-decoration: underline;
		}
		a:visited {
			color: blue;
			text-decoration: underline;
		}
		a:hover {
			color: red;
			text-decoration: underline;
		}
		
		.toplinks { font-family:arial; font-size:10pt; padding: 2 0 5 0; }
		.toplinks a { text-decoration:none; color:white; padding:2 12 5 12; 
			display:inline; margin:0;
		}
		.toplinks a:hover { color:yellow; background-color:330066; }
		.toplinksplain { padding: 2 0 5 0; }
		.toplinksplain a { text-decoration:none; color:white; }
		.toplinksplain a:hover { text-decoration:underline; color:yellow; }

		.alertlinks a:link { color:#993300; text-decoration: underline; }
		.alertlinks a:visited { color:#993300; text-decoration: underline; }
		.alertlinks a:hover { color: red; text-decoration: underline; }

		.greenlink { font-family:arial; font-size:9pt; color:green; }
		.greenlink A:link { text-decoration:underline; color:green; }
		.greenlink A:visited { text-decoration:underline; color:green; }
		.greenlink A:hover { text-decortion:underline; color:black; }

		.toolLinks { font-family:arial; font-size:11pt; }
		.toolLinks a { padding:5 12 5 12; text-decoration:none; color:black; background-color:lightgray; border:solid; border-width:1px; border-color:purple; margin:2 10 2 10; }
		.toolLinks a:hover { color:white; background-color:black; }
		
	</style>

	<table style="width:100%" cellspacing=0 cellpadding=0>
	<tr><td colspan=2 bgcolor=#7A5DC7 style="padding-top:2px; padding-bottom:10px;">
		<span style="font-family: Cambria, Georgia; font-size:24pt; color:white">&nbsp;&nbsp;<b>eFOIA</b> Freedom of Information Request Tracker</span>
	</td></tr>
	<tr bgcolor=#9E7BFF><td valign=top bgcolor=#9E7BFF style="" class=toplinks>
		<a href=index.php>Home</a>
		<?
			if($auth) {
				// print "<img src=rssicon.gif height=10 align=absmiddle><a href=rss.php?user=$nnusr target=_blank>Alerts</a>";
				print "<a href=dashboard.php>Dashboard</a>";
				print "<span id=fp><a href=\"javascript:prefsOpen()\">Preferences</a></span>";
				print "<a href=group-report.php>Latest</a>";
				print "<a href=deaths-report.php>Deaths</a>";
			}
			if($admin) {
				print "<a href=admin.php>Users</a>";
				print "<a href=groups.php>Groups</a>";
				print "<a href=admin-settings.php>Settings</a>";
			}
		?>
		<a href=about.php>About</a>

	</td><td align=right valign=center style="toplinksplain">
		<span style='font-family: arial; font-size: 9pt; color:cccccc'>
			<? if($auth) {
				echo "User: ";
				echo "<span style='color:#cccccc'>$nnusr</span>";
				echo "&nbsp;&nbsp;";
				print "<span class=toplinksplain>";
				print "<a href=index.php?logoutaction=LOGOUT>Logout</a>";
				print "</span>";
				print "&nbsp;&nbsp;&nbsp;&nbsp;";
			}
			?>
			Version
			<?
				echo $version_no;
			?>
		</span>&nbsp;&nbsp;
	</td>
	</tr>
	<?
		// check if they have preferences set up:
		if($auth) {
			$sql = "SELECT name FROM foia.user_prefs WHERE user=\"$nnusr\" ";
			$line = $db->getRow($sql);
			if($line[name] == '') {
				print "<tr><td colspan=2 style='height: 20px; font-family:arial; font-size:10pt; color:white; background-color:red' >&nbsp;&nbsp;<span style='color:black'>NOTE: </span>";
				print "<b>You haven't set up your <a href=\"javascript:prefsOpen()\">preferences</a> yet. They help you create new records requests.</b></td></tr>";

			}
		}
	?>
	<tr><td colspan=2 style="background-color:black; height:1px"></td></tr>
	<tr><td colspan=2 style="background-color:666666; height:1px"></td></tr>
	<tr><td colspan=2 style="background-color:999999; height:1px"></td></tr>
	
	</table>
	<div id=divMain style='min-height:550px;height:auto!important;height:550px;display:block;'>
	<div id=divWaste style='height:10px;'></div>

	<table cellspacing=0 cellpadding=0 border=0 width=100%>
	<tr><td valign=top style="width:15px"></td>
	<td>

<?
	$log = new foiaLog($nnusr);


?>