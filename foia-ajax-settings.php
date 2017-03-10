<?

	include('incl-auth.php');
	require_once('incl-dbconnect.php');

	if(($admin) && ($auth)) {

		$db = new dbLink();
		extract($_GET);
		extract($_POST);

		if($type == "savesettings") {
			print "settingsresults|";
			$urlstem = urldecode($urlstem);
			$sql = "UPDATE foia.prefs SET urlstem = \"$urlstem\" ";
			$db->run($sql);
		}

		if($type == "addtemplate") {
			print "settingsresults|";
			$name = trim(urldecode($name));
			$filename = trim(urldecode($filename));
			if(($name != '') && ($filename != '')) {
				$sql = "SELECT * FROM foia.templates WHERE name=\"$name\" AND filename=\"$filename\" ";
				$r = $db->getArray($sql);
				if($db->numRows() == 0) {
					$sql = "INSERT INTO foia.templates(name,filename) VALUES(\"$name\", \"$filename\") ";
					$db->run($sql);
				}
			}
		}

		if($type == "templateslist") {
			print "templatediv|";
			if($action == "delete") {
				$sql = "DELETE FROM foia.templates WHERE id = $id ";
				$db->run($sql);
			}
			print "<span style='font-family:arial; font-size:10pt'><b>Current Templates:</b>";
			$sql = "SELECT id, name, filename FROM foia.templates ORDER BY name ASC ";
			$r = $db->getArray($sql);
			foreach($r as $line) {
				print "<br />$line[name] --> $line[filename]&nbsp;&nbsp;&nbsp;<a href='javascript:deleteTemplate($line[id])'>Delete</a>";
			}
			print "</span>";

		}

	}

?>