<?
	require_once("incl-dbconnect.php");
	$db = new dbLink();
	
	$sql = "SELECT v FROM foia.vars WHERE k = 'textsearchplus-time'";
	$v = $db->getScalar($sql);
	
	$sql = "SELECT item_id, item FROM foia.textsearchplus WHERE updated >= '$v' ";
	$r = $db->query($sql);
	
	while($l = mysql_fetch_array($r)) {
		print " -> $l[item_id]: ";
		$out = "";
		$wc = 0;
		$a = strip_tags($l[item]);
		$a = eregi_replace('/[\"\'\,\.\:\;\-]/','',$a);
		$w = explode(' ',$a);
		foreach($w as $word) {
			if(trim($word) != '') {
					$out .= soundex(trim($word)) . " ";
					$wc++;
			}
		}
		$sql = "UPDATE foia.textsearchplus SET sounds = \"$out\" WHERE item_id = $l[item_id]";
		$db->actionQuery($sql);
		print "ok ($wc)\n";
	}
	$db->actionQuery("UPDATE foia.vars SET v = NOW() WHERE k = 'textsearchplus-time'");
?>