<?

		if(!empty($_GET)) extract($_GET);
		if(!empty($_POST)) extract($_POST);
		if(!empty($_COOKIE)) extract($_COOKIE);

	require_once("incl-dbconnect.php");
	include('incl-auth.php');
	if($auth) {

		$db = new dbLink();

		$resultsize = 25;
		$start = $batch * $resultsize;
		
		
		$sql = "SELECT foia_id, title, owner, agency, date_filed, status_id FROM foia.requests WHERE (deleted = 0 OR deleted IS NULL) AND owner IN ( SELECT DISTINCT username FROM foia.groups_members WHERE group_id IN ( SELECT DISTINCT group_id FROM foia.groups_members WHERE username = \"$nnusr\") ) AND (deleted = 0 OR deleted IS NULL)  ORDER BY date_filed DESC LIMIT $start, $resultsize";
		$r = $db->getArray($sql);

		foreach($r as $l) {
			print "<a href=http://usat-eddataba/foia/print.php?foia_id=$l[foia_id]>$l[title]</a>";
			print "<br>$l[agency] <span style='color:gray;'>(Filed $l[date_filed] by $l[owner])</span><br /><br />";
		}

	}

?>