<?php

	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);


	$nnusr = '';
	$nnusr = $_COOKIE[nnusr];

	include('incl-dbconnect.php');
	include('incl-auth.php');
	$db = new dbLink();


	if($type == "groupMemberChange") {
		$db->debug();
		if($action == "add") {
			$un = $db->getOne("SELECT username FROM newsnet.users WHERE id = $userId");
			$db->run("DELETE FROM foia.groups_members WHERE username=\"$un\" AND group_id = $id ");
			$db->run("INSERT INTO foia.groups_members(group_id,username) VALUES($id,\"$un\") ");	
		}
		if($action == "drop") {
			$un = $db->getOne("SELECT username FROM newsnet.users WHERE id = $userId");
			$db->run("DELETE FROM foia.groups_members WHERE username=\"$un\" AND group_id = $id ");			
		}
		print "<table cellspacing=0 cellpadding=0 border=0><tr>";
			print "<td style='wdith:350px;' valign=top>";
				print "Non-Members<br />";
				$sql = "SELECT username, id FROM newsnet.users WHERE username NOT IN ( SELECT DISTINCT username FROM foia.groups_members WHERE group_id = $id ) ORDER BY username ";
				$r = $db->getArray($sql);
				print "<div id=nonmembers>";
				print "<select id=selnonmembers size=20 style='font-size:10pt; width:340px;' >";
					foreach($r as $l) {
						print "<option value=$l[id]>$l[username]</option>";	
					}
				print "</select>";
				print "</div>";
			print "</td>";
			print "<td style='width:100px;'>";
				print "<center>";
				print "<input type=button onClick=\"addGroupMember($id)\" value='--->' />";
				print "<br /><br />";
				print "<input type=button onClick=\"dropGroupMember($id)\" value='<---' />";
				print "</center>";
			print "</td>";
			print "<td style='wdith:350px;' valign=top>";
				print "Members<br />";
				print "<div id=members>";
				$sql = "SELECT username, id FROM newsnet.users WHERE username IN ( SELECT DISTINCT username FROM foia.groups_members WHERE group_id = $id ) ORDER BY username ";
				$r = $db->getArray($sql);
				print "<select id=selmembers size=20 style='font-size:10pt; width:340px;'>";
					foreach($r as $l) {
						print "<option value=$l[id]>$l[username]</option>";	
					}
				
				print "</select>";
				print "</div>";
				
			print "</td>";
			
	
		print "</tr></table>";
	}

	if($type == "modifyGroup") {
		print "userarea|";
		$g = $db->getRow("SELECT * FROM foia.groups WHERE group_id = $id ");
		
		print "<b>$g[group_name] Group</b>";
		print "<br /><br />";
		
		print "<div id=membership>";
		print "<table cellspacing=0 cellpadding=0 border=0><tr>";
			print "<td style='wdith:350px;' valign=top>";
				print "Non-Members<br />";
				$sql = "SELECT username, id FROM newsnet.users WHERE username NOT IN ( SELECT DISTINCT username FROM foia.groups_members WHERE group_id = $id ) ORDER BY username ";
				$r = $db->getArray($sql);
				print "<div id=nonmembers>";
				print "<select id=selnonmembers size=20 style='font-size:10pt; width:340px;' >";
					foreach($r as $l) {
						print "<option value=$l[id]>$l[username]</option>";	
					}
				print "</select>";
				print "</div>";
			print "</td>";
			print "<td style='width:100px;'>";
				print "<center>";
				print "<input type=button onClick=\"addGroupMember($id)\" value='--->' />";
				print "<br /><br />";
				print "<input type=button onClick=\"dropGroupMember($id)\" value='<---' />";
				print "</center>";
			print "</td>";
			print "<td style='wdith:350px;' valign=top>";
				print "Members<br />";
				print "<div id=members>";
				$sql = "SELECT username, id FROM newsnet.users WHERE username IN ( SELECT DISTINCT username FROM foia.groups_members WHERE group_id = $id ) ORDER BY username ";
				$r = $db->getArray($sql);
				print "<select id=selmembers size=20 style='font-size:10pt; width:340px;'>";
					foreach($r as $l) {
						print "<option value=$l[id]>$l[username]</option>";	
					}
				
				print "</select>";
				print "</div>";
				
			print "</td>";
			
	
		print "</tr></table>";
		print "</div>";
		print "<br /><a href=groups.php>Done</a>";
		
	}

	if($type == "grouplist") {
		print "userarea|";

		if($action == "new") {
			$group_name = urldecode($group_name);
			$sql = "SELECT * FROM foia.groups WHERE group_name = \"$group_name\" ";
			$r = $db->getArray($sql);
			if($db->numRows() == 0) {
				$sql = "INSERT INTO foia.groups(group_name,created) VALUES(\"$group_name\", NOW())";
				$db->run($sql);
			}	
		}
		if($action == "delete") {
			$db->run("DELETE FROM foia.groups WHERE group_id = $id ");
			$db->run("DELETE FROM foia.groups_members WHERE group_id = $id ");
		}

		print "<p><a href=\"javascript:sndReq('newgroup','')\">New Group</a>";
		print "<p>";
		print "<span style='font-family:arial; font-size:12pt'>Current Groups</span><br>";

		$sql = "SELECT * FROM foia.groups ORDER BY group_name ";
		$r = $db->getArray($sql);
		foreach($r as $line) {
			print "<a href=\"javascript:sndReq('modifyGroup','&id=$line[group_id]')\">$line[group_name]</a>; &nbsp;";
		}

		
	}
	if($type == "newgroup") {
		print "userarea|";
		print "<span style='font-family:arial; font-size:12pt'>New Group</span><br>";
		print "<p>";
		print "Group Name&nbsp;&nbsp;&nbsp;</td><td><input type=text id=group_name size=40 />&nbsp;&nbsp;&nbsp;&nbsp;";
		print "<a href=\"javascript:sndReq('grouplist','&action=new&group_name=' + escape(document.getElementById('group_name').value))\">Save</a>&nbsp;&nbsp;&nbsp;&nbsp;";
		print "<a href=\"javascript:sndReq('grouplist','')\">Cancel</a> &nbsp;&nbsp;&nbsp;&nbsp;";
	
	}

	if($type == "userlist") {
		// nothing goes above this line
		print "userarea|";


		if($action == "delete") {
			$sql = "DELETE FROM newsnet.users WHERE id = $id ";
			$db->run($sql);
		}
		if($action == "update") {
			$username = urldecode($username);
			$sql = "UPDATE newsnet.users SET username=\"$username\", administrator=$administrator WHERE id = $id ";
			$db->run($sql);
		}
		if($action == "resetpassword") {
			$sql = "UPDATE newsnet.users SET password=PASSWORD('PASSWORD') WHERE id=$id ";
			$db->run($sql);
		}
		if($action == "new") {
			$password = strtoupper(urldecode($password));
			$username = urldecode($username);
			$sql = "SELECT username FROM newsnet.users WHERE username=\"$username\" ";
			$r = $db->getArray($sql);
			if($db->numRows() > 0) {
				print "<font color=red>Sorry, but that username is taken!</font>";
			} elseif ($password == '') {
				print "<font color=red>Sorry, but everyone needs a password!</font>";
			} else {

				$sql = "INSERT INTO newsnet.users(username,password,administrator) VALUES(\"$username\",PASSWORD(\"$password\"),$administrator) ";
				$db->run($sql);

			}

		}
		print "<p><a href=\"javascript:sndReq('newuser','')\">New User</a>";
		print "<p>";
		print "<span style='font-family:arial; font-size:12pt'>Current Users</span><br>";

		print "<span style='font-family:arial; font-size:9pt'>";
			print "Search for usernames: ";
			print "<input type=text size=30 id=narrower style='font-size:8pt'>";
			print "&nbsp;&nbsp;<a href=\"javascript:sndReq('userlist','&narrower='+document.getElementById('narrower').value)\">Search</a>";
			print "&nbsp;&nbsp:<a href=\"javascript:sndReq('userlist','')\">All</a>";
			print "</span>";
			print "<p>";
		if($narrower != '') {
			$narrow = " WHERE username LIKE '%$narrower%' "; }
		$sql = "SELECT username, administrator, id FROM newsnet.users $narrow ORDER BY username ";
		$r = $db->getArray($sql);
		foreach($r as $line) {
			print "<a href=\"javascript:sndReq('modifyuser','&id=$line[id]')\">$line[username]</a>; &nbsp;";
		}
	}

	if($type == "modifyuser") {
		print "userarea|";

		print "<span style='font-family:arial; font-size:12pt'>Change User Settings</span><br>";
		print "<p>";
		$sql = "SELECT username, administrator FROM newsnet.users WHERE id = $id ";
		$line = $db->getRow($sql);
		print "<table cellspacing=0 cellpadding=1 border=0>";
		print "<tr><td>Username&nbsp;&nbsp;&nbsp;</td><td><input type=text id=username size=30 value=\"$line[username]\"></td></tr>";
		print "<tr><td>Password</td><td><a href=\"javascript:resetPassword($id,'$line[username]')\">Reset Password</a>";
		print "<tr><td>Administrator&nbsp;&nbsp;&nbsp;</td><td>";
			print "<select id=administrator>";
				if($line[administrator] == 1) {
					print "<option value=0>No</option><option selected value=1>Yes</option>";
				} else {
					print "<option selected value=0>No</option><option value=1>Yes</option>";
				}
			print "</select>";
			print "</td></tr>";
		print "</table>";
		print "<p>";
		print "<a href=\"javascript:sndReq('userlist','&action=update&id=$id&username='+escape(document.getElementById('username').value)+'&administrator='+document.getElementById('administrator').value)\">Save</a> &nbsp;&nbsp;&nbsp;&nbsp;";
		print "<a href=\"javascript:sndReq('userlist','')\">Cancel</a> &nbsp;&nbsp;&nbsp;&nbsp;";
		print "<a href=\"javascript:deleteUser($id)\">Delete</a>";
	}
	if($type == "newuser") {
		print "userarea|";
		print "<span style='font-family:arial; font-size:12pt'>Change User Settings</span><br>";
		print "<p>";
		print "<tr><td>Username&nbsp;&nbsp;&nbsp;</td><td><input type=text id=username size=30></td></tr>";
		print "<tr><td>Password</td><td><input type=password id=password size=30></a>";
		print "<tr><td>Administrator&nbsp;&nbsp;&nbsp;</td><td>";
			print "<select id=administrator>";
			print "<option selected value=0>No</option><option value=1>Yes</option>";
			print "</select>";
			print "</td></tr>";
		print "</table>";
		print "<p>";
		print "<a href=\"javascript:sndReq('userlist','&action=new&id=$id&username='+escape(document.getElementById('username').value)+'&administrator='+document.getElementById('administrator').value+'&password='+escape(document.getElementById('password').value))\">Save</a> &nbsp;&nbsp;&nbsp;&nbsp;";
		print "<a href=\"javascript:sndReq('userlist','')\">Cancel</a> &nbsp;&nbsp;&nbsp;&nbsp;";

	}

?>