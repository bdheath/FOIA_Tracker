<?
	extract($_GET);
	$params = explode("/",$inp);
	$id = $params[0];

	require_once("incl-log.php");
	require_once("incl-dbconnect.php");
	require_once("incl-auth.php");
	$log = new foiaLog($nnusr,'Document View');

	if($auth) {
		
		$db = new dbLink();
		$sql = "SELECT mime, UNCOMPRESS(document) AS d FROM foia.documents WHERE document_id = $id";
		$h = $db->getRow($sql);
		header("Content-type: $h[mime]");
		print ($h[d]);
	}

?>