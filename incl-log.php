<?
	require_once("incl-dbconnect.php");

	class foiaLog {
	
		private $_db;
		private $_event;
		private $_foia_id;
		private $_ip;
		private $_url;
		private $_user;
		
		function __construct() {
			# Optional syntax is ([event], [foia_id])
			$this->_db = new dbLink();
			$this->_ip = $_SERVER['REMOTE_ADDR'];
			$this->_url = $_SERVER['REQUEST_URI'];
			$this->_user = func_get_arg(0);
			if(func_num_args() > 1) {
				$this->_event = func_get_arg(1);
				if(func_num_args() > 2) {
					$this->_foia_id = func_get_arg(2);
				}	
			}
			
			if($this->_event != '') {
				$sql = "INSERT INTO foia.log(event, username, foia_id, event_time, ip, url) " 
					. "VALUES("
					. "\"" . $this->_event . "\","
					. "\"" . $this->_user . "\","
					. "\"" . $this->_foia_id . "\","
					. "NOW(),"
					. "\"" . $this->_ip . "\","
					. "\"" . $this->_url . "\""
					. ")";
			} else {
				$sql = "INSERT INTO foia.log(username, event_time, ip, url)"
					. "VALUES("
					. "\"" . $this->_user . "\","
					. "NOW(),"
					. "\"" . $this->_ip . "\","
					. "\"" . $this->_url . "\""
					. ")";
			}
			$this->_db->actionQuery($sql);
			
		}
		
	
	}

?>