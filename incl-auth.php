<?php

	if(!empty($_GET)) extract($_GET);
	if(!empty($_POST)) extract($_POST);
	if(!empty($_COOKIE)) extract($_COOKIE);

	require_once('incl-dbconnect.php');
	require_once('incl-log.php');
	if(gettype($db) == 'NULL') {
		$db = new dbLink();
	}
	
	$nnusr = $_COOKIE['nnusr'];
	$nnid = $_COOKIE['nnid'];

	$encrypt_code = "0835719852346582";
	$YahooAppID = "yahoobheath";
	$auth = 0;
	$admin = 0;

	$null = "";
	$newsid = 0;

	// If no NNID exists, create a new session ID
	// The NNID is a 100-digit random number
	if($nnid == $null) {
		srand ((double) microtime( )*1000000);
		$sid = $null;
		for($j = 0; $j < 25; $j++) {
			$sid1 = rand(1111,9999);
			$sid .= $sid1;
		}
		$nnid = $sid;
		$newsid = 1;
	}
	// Flag new login attempts
	if($liusr <> $null) {
		$nnusr = $liusr;
		$loginattempt = -1;
		$logintry = 1;
	}

	// Handle logouts by deleting the active session record
	// Also, disable the user identification cookie
	if (($logoutaction == "LOGOUT") && ($nnusr <> $null)) {
		$log = new foiaLog($nnusr,'Logout by $nnusr',$foia_id);
		$bsql = "DELETE FROM newsnet.sessions WHERE DECODE(session_id,'$encrypt_code') = '$nnid' ";
		$r = mysql_query($bsql);
		$nnpw = "";
		$nnusr = "";
	}

	// Set site permission cookies. A valid return login consists of a valid
	// session ID and username pair
	SetCookie("nnid",$nnid,time()+7200);
	SetCookie("nnusr",$nnusr,time()+7200);


	// Check status of database server, and see if session ID is already logged
	$result = $db->getOne("SELECT 1 AS test ")
		or die("Really could not query");
	$result = $db->getArray( "SELECT session_id FROM newsnet.sessions WHERE DECODE(session_id,'$encrypt_code') = '$nnid'" );
	$matches = $db->numRows();


	// For new login attempts, log the session ID
	if(($newsid == 1) || ($matches == 0)) {
		$asql = "INSERT INTO newsnet.sessions(user,session_id,strikes) VALUES('<font color=993300>Unauthenticated User</font>',ENCODE('$nnid','$encrypt_code'),0) ";
		$db->run($asql);
	}


	// Handle authentication.
	// For new login attempts, seek username/password pair from user table
	// For returning users, check session for authorization.
	if ($loginattempt == -1) {
		/* If a login try, validate against the user list */
		$lipw = strtoupper($lipw);
		$asql = "SELECT * FROM newsnet.users WHERE username='$liusr' AND password=PASSWORD('$lipw') ";
		$result = $db->getArray($asql);
		if($db->numRows() == 1) {
			$auth = -1;
			$line = $result[0];
			$admin = $line[administrator];
			$real_name = $line[real_name];
			$usernum = $line[ID];
			$bsql = "UPDATE newsnet.sessions SET strikes=0, user='$liusr', session_id=ENCODE('$nnid','$encrypt_code'),usernum=$usernum, auth=PASSWORD('$nnid') WHERE DECODE(session_id,'$encrypt_code') = '$nnid' ";
			$db->run($bsql);
			$log = new foiaLog($nnusr,'Successful Login By ' . $nnusr . '',$foia_id);
		} else {
			$usql = "SELECT strikes FROM newsnet.sessions WHERE DECODE(session_id,'$encrypt_code') = '$nnid' ";
			$s = $db->getOne($usql);
			$s++;
			$usql = "UPDATE newsnet.sessions SET user='<font color=993300>Unauthenticated User - strike <b>$s</b></font>', strikes = $s WHERE DECODE(session_id,'$encrypt_code') = '$nnid' ";
			$db->run($usql);
			$auth = 0;
			$log = new foiaLog($nnusr,'Failed Login Attempt By ' . $nnusr . ' (' . $s . ' strikes)',$foia_id);
		}
	} else {
		/* If not a login try, validate active node */
		$asql = "SELECT * FROM newsnet.sessions WHERE user='$nnusr' AND DECODE(session_id,'$encrypt_code') = '$nnid' AND auth=PASSWORD('$nnid') ";
		$result = $db->getArray($asql);
		if($db->numRows() == 1) {
			$line = $result[0];
			$auth = -1;
			$usernum = $line[usernum];
		} else {
			$auth = 0;
		}
	}


	// Delete inactive nodes
	$bsql = "DELETE FROM newsnet.sessions WHERE round(DATE_FORMAT(now(),'%Y%m%d%H%i%s')) > round(DATE_FORMAT(DATE_ADD(expires,INTERVAL 2 HOUR),'%Y%m%d%H%i%s')) ";
	$db->run($bsql);


	// Update log file
	if($logoutaction == "LOGOUT") {
		$log_action = "logout";
		$success = 1;
	} else {
		$log_action = "authentication - page view";
		$success = $auth;
	}
	$requested_url = $_SERVER['REQUEST_URI'];
	$sql_log = "INSERT INTO newsnet.logs(user,action,success,page,session_id) VALUES(\"$nnusr\",\"$log_action\",$success,\"$requested_url\",ENCODE(\"$nnid\",\"$encrypt_code\")) ";
	$db->run($sql_log);

	// For authenticated users, set session expiration time,
	// check for default passwords and read global user variables
	if ($auth <> 0) {
		/* Set node time to current time */
		$bsql = "UPDATE newsnet.sessions SET expires = NOW() WHERE user='$nnusr' AND DECODE(session_id,'$encrypt_code') = '$nnid' ";
		$db->run($bsql);
		$asql = "SELECT * FROM newsnet.users WHERE id = $usernum";
		$line = $db->getRow($asql);
		$result = @mysql_query($asql);
		$bsql = "SELECT if(password=PASSWORD('PASSWORD'),1,0) as pw FROM users WHERE id = $usernum";
		$newuser = $db->getOne($bsql);
		$admin = $line[administrator];
		$view_databases = $line[view_databases];
		$blocked = $line[blocked];
		$create_databases = $line[create_databases];
	}



	/* Tests whether a MySQL server is online. Returns 0 or 1 */
	function fun_test_server() {
		$r = @mysql_query("SELECT 1 AS test");
		if (!$r) {
			$c = 0;
		} else {
			$c = 1; }
		return $c;
	}

?>