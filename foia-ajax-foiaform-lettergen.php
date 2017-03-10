<?

	include('incl-dbconnect.php');
	include('incl-auth.php');
	require_once('incl-log.php');
	$db = new dbLink();
	extract($_GET);

		function webformat($strIn) {
			$strIn = eregi_replace("\n", "<br />", $strIn);
			$strIn = eregi_replace("§", "&sect;", $strIn);
			$strIn = eregi_replace("\t", "&#09;", $strIn);
			return $strIn;
		}

	function right($string, $length) {
   		return substr($string, -$length, $length);
	}


	// make the letter-form area
	if($type == "makearea") {
		extract($_POST);
		$log = new foiaLog($nnusr,'Letter Generator',$foia_id);
		print "letterdiv|";
		print "<input type=hidden id=foia_id_ltr value=$foia_id>";

			// load data
			$sql = "SELECT * FROM foia.letters WHERE foia_id = $foia_id ";
			$line = $db->getRow($sql);
			$lrs = stripslashes($line[records]);
			$laddr = stripslashes($line[address]);
			$lgreeting = stripslashes($line[greeting]);
			$template_id = $line[template_id];

		?>
		<span style='font-family:arial; font-size:14pt'>eFOIA / Letter Creator
		<?
			if ($title != '') {
				$title = urldecode($title);
				echo " / <span style='color:gray'>" . $title . "</span>"; }
		?></span><br /><br />
		<table cellspacing=0 cellpadding=0 border=0 width=100%>
		<tr><td valign=top style='width:400px; font-family:arial; font-size:10pt'>
			<b>Type of Letter</b> <br />

			<?
				$sql = "SELECT id, name FROM foia.templates ORDER BY name ASC ";
				$r = $db->getArray($sql);
				print "<select onchange='letterGen()' id=template_id name=template_id>";
				foreach($r as $l) {
					if($template_id == $l[id]) {
						print "<option selected value=$l[id]>$l[name]</option>";
					} else {
						print "<option value=$l[id]>$l[name]</option>";
					}
				}
			?>
			</select>
			<p>
			<b>Recipient</b><br />
			<textarea id=address onchange="letterGen()" name=address style='width:400px;' rows=4><?php echo $laddr; ?></textarea>
			<p>
			<b>Dear ...</b><br />
			<input type=text onchange="letterGen()" id=greeting name=greeting value="<? echo $lgreeting; ?>" size=40>&nbsp;&nbsp;<a href="javascript:defaultGreeting()">Generic</a>
			<p>
			<b>I hereby request a copy of ...</b><br />
			<textarea id=rs name=rs onchange="letterGen()" style='cursor:text; width:400px; height:150px'><? echo $rs; ?></textarea>
			<br />


			<input type=checkbox id=highlight checked onclick="letterGen()" onchange="letterGen()"> Highlight as you edit
			<p><input type=button id=gen onclick='letterGen()' value='Update'>
			&nbsp;&nbsp;
			<input type=button onclick='letterClose()' value='Close'>

		</td><td style='width:20px'>&nbsp;&nbsp;&nbsp;</td>
		<td valign=top style='font-size:10pt; font-family:arial;'>
			<b>Your Letter</b> (Copy and paste to your word processor)
			<br /><img src=y.gif height=5 width=0>
			<div id=letter scrolling=1 style='border:solid; border-width:1px; padding:7px; height:400px; overflow:scroll; font-size:12pt; font-family:times;'></div>

		</td></tr>
		</table>
		|updateforms|
		<?
	}


	// generate the actual letter from templates and user input
	if($type == "lettergen") {
		print "letter|";
		extract($_POST);
		$sql = "SELECT filename FROM foia.templates WHERE id = $template_id ";
		
		
		$fn = "/xampp/htdocs/foia/templates/" . $db->getOne($sql);
		$f = file_get_contents($fn);
		$address = urldecode($address);
		$greeting = urldecode($greeting);
		$rs = urldecode($rs);
		// apply tags
		$sql = "SELECT * FROM foia.user_prefs WHERE user=\"$nnusr\" ";
		$line = $db->getOne($sql);
		if($highlight == "true") {
			$s = "<span style='background-color:#ffff66'>";
			$se = "</span>";
		}

		$rs = trim($rs);
		$greeting = trim($greeting);
		if(right($rs, 1) == ".") {
			$rs = substr($rs, 0, strlen($rs) -1);
		}

		$d = date("F j, Y");
		$fc = eregi_replace("\[@GREETING\]", $greeting, $f);
		$fc = eregi_replace("\[@TO\]", "$address", $f);
		$fc = eregi_replace("\[@RECORDS\]", "$rs", $f);
		$fc = eregi_replace("\[@PHONE\]", $line[phone], $f);
		$fc = eregi_replace("\[@REALNAME\]", $line[name], $f);
		$fc = eregi_replace("\[@ADDRESS\]", $line[address], $f);
		$fc = eregi_replace("\[@EMAIL\]", $line[email], $f);
		$fc = eregi_replace("\[@DATE\]", $d, $f);

		$f = eregi_replace("\[@GREETING\]", $s . $greeting . $se, $f);
		$f = eregi_replace("\[@TO\]", "$s$address$se", $f);
		$f = eregi_replace("\[@RECORDS\]", "$s$rs$se", $f);
		$f = eregi_replace("\[@PHONE\]", $line[phone], $f);
		$f = eregi_replace("\[@REALNAME\]", $line[name], $f);
		$f = eregi_replace("\[@ADDRESS\]", $line[address], $f);
		$f = eregi_replace("\[@EMAIL\]", $line[email], $f);
		$f = eregi_replace("\[@DATE\]", $d, $f);


//		print "<textarea id=lettertext style='height:0px; width:0px; border:none' hidden=1>$f</textarea>";

		// handle database stuff / store input values in db
		$sql = "DELETE FROM foia.letters WHERE foia_id = $foia_id ";
		$db->run($sql);
		$greeting = addslashes($greeting);
		$address = addslashes($address);
		$records = addslashes($rs);
		$letter = addslashes($f);
		$sql = "INSERT INTO foia.letters(foia_id,owner,address,greeting,records,letter,template_id)
			VALUES($foia_id, \"$nnusr\", \"$address\", \"$greeting\", \"$records\", \"$letter\", $template_id) ";
		$db->run($sql);
		if($rs != '') {
			$sql = "UPDATE foia.requests SET records_sought = \"$rs\" WHERE foia_id = $foia_id ";
			$db->run($sql);
		}

		$f = webformat($f);
		print $f;
		$log = new foiaLog($nnusr,'Save Changes to FOIA Request / Letter-Generator',$foia_id);


	}










?>