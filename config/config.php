<html><head><title>eFOIA | Configuration</title></head><body>

			<center>
			<p>

			<table cellspacing=0 cellpadding=0 border=0 style='width:400px'><tr><td>
					<span style='font-family:arial; font-size:18pt'><i>eFOIA / Configuration</i></span>
					<p><span style='font-family:arial; font-size:10pt;'>


<?

	extract($_GET);
	extract($_POST);

	if($step == '') {
		?>
					Welcome to eFOIA. This utility will configure your MySQL databases. To continue, you need to know the username and password for an administrative account on your server. (eFOIA does not retain this information.)
					<p>
					<form action=config.php method=post>
					<input type=hidden name=step value=2>
					<b>Server / Root User Information</b>
					<table cellspacig=0 cellpadding=2 border=0 style='font-family:arial; font-size:10pt'>
						<tr><td align=right>Server (IP or name):</td><td style='width:10px'></td><td><input type=text size=20 value='localhost' name=server></td></tr>
						<tr><td align=right>Username:</td><td></td><td><input type=text id=username name=username size=20></td></tr>
						<tr><td align=right>Password:</td><td></td><td><input type=password name=password size=20></td></tr>

					</table>
					<br /><input type=submit value='Continue ->'>
					</p>
				</span>
			<script type=text/javascript>
				document.getElementById('username').focus();
			</script>
		<?
	}


	if($step == 2) {
		// run through the setup commands
		$link = @mysql_connect($server, $username, $password)
			or die("<p>ERROR - That account doesn't have permission to connect to this server.");
		$f = file_get_contents("make_databases.sql");
		$commands = explode(";", $f);
		foreach($commands as $c) {
			$i++;
			$result = @mysql_query($c)
				or $errCount++;
		}
		?>
			Configuration complete.
			<p>
			For security purposes, you should delete the 'config' folder from your website. You won't need to run this utility again.
			<p>
			Go to <a href="../index.php">eFOIA</a>

		<?
	}



?>

			</td></tr></table>

</body></html>