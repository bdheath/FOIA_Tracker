<?

	$uploadPath = dirname(__FILE__) . '/_uploads/';

	require_once("incl-dbconnect.php");
	require_once("incl-log.php");
	$db = new dbLink();
	
	extract($_POST);
	extract($_GET);
	
       	$name = basename($_FILES['docfilename']['name']);

		$uploadfile = $uploadPath . $name;

		$nm = $_FILES['uploadfile']['tmp_name'];

		// handle upload
		if(is_uploaded_file($_FILES['docfilename']['tmp_name'])) {
			// this is an uploaded file
			$f = addslashes(file_get_contents($_FILES['docfilename']['tmp_name']));
			$filename = basename($_FILES['docfilename']['name']);

			if($doctitle == '') { $doctitle = $filename; };
			$mime = $_FILES['docfilename']['type'];

			$sql = "INSERT INTO foia.documents(mime, document) VALUES(\"$mime\", COMPRESS(\"" . $f . "\"))";
			$db->run($sql);
			$lid = $db->getLastID();

			$title = addslashes($doctitle);
			$filename_mod = $filename;
			$filename_mod = preg_replace("/[\'\"\n\r ]/","_",$filename_mod);

			$sql = "INSERT INTO foia.documents_index(foia_id, title, filename, filename_mod, mime, uploaded, document_id)"
				. "VALUES($foia_id,\"$title\", \"$filename\",\"$filename_mod\",\"$mime\",NOW(), $lid)";
			$db->run($sql) or die ("<script type=text/javascript>alert('$sql');</script>");
//			$lid = $db->lastInsertId();

			$log = new foiaLog($nnusr,'Uploaded File ' . $filename_mod . '(becomes document ' . $lid . ')',$foia_id);
			
			
		} else { 
			?>
				<script type='text/javascript'>alert('ALERT: There was a problem with your upload. Please try again.');</script>
			<?	
		}

	
	?>
	<script type='text/javascript'>
	
		window.parent.listFiles();
		window.parent.autoListFiles();
	
	</script>
	<?
	

?>