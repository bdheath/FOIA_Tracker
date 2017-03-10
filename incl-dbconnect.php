<?php

require_once(dirname(__FILE__) . '/incl-cache.php');

# MYSQL DATABASE WRAPPER; CENTRALIZE AND OPTIMIZE CONNECTION SETTINGS, CACHING, ETC.

/*
	MOST IMPORTANT METHODS
		QUERYING (Retrieving and managing data)
			getOne($sql)		- Return a single value from the database
			getRow($sql)		- Returns a single row as an associative array
			getArray($sql)		- Returns the results of a query as an array of associative arrays
								  (So $row[i][fieldname])
			run($sql)			- Run the specified action query
			query($sql)			- Run the specified query; return MySQL result resource
								  (Deprectated - doesn't include caching, optimization)
			getArray($sql)		- Get a two-dimensional array of the query results
								  Results are an array of an associative array
								  So $data[$i][name], $data[$i][address] ...
			getHash($sql)		- Get an associative array of a query that will return 1 row
								  (Same as getRow)
			getScalar($sql)		- Fetch a single value from the database
								  (Same as getOne)
			actionQuery($sql)	- Perform the specified action query (same as run())
			numRows()			- Number of rows returned by the last query
			getNumberOfResults()- Total number of rows that would have mached the last query 
								  had a limit not been used
			execStoredProcedure($proc)
			lastInsertID()		- Get the unique ID of the last row inserted
		CACHING (File-based results caching; does not impact MySQL native caching)
			cache($TTL)			- Enable filesystem caching for query results (for TTL seconds)
			memcache($TTL)		- Enable memcached caching for query results
			cacheServer($h,$p)	- Add a memcached server (host=$h, post=$p)
			cacheType($type)	- Choose type of cache used ('mem' = memcached)
			cacheUsed()			- Did the last query retrieve a cached value? (true/false)
			noCache()			- Turn off caching
		MANAGEMENT (Dealing with connections, errors, etc.)
			close()				- Close the currenct connection
			isConnected()		- Checks whether the database connection is open
								  Attempts to connect if not connected
								  Returns true if connection is successful
			checkConnection()	- Check the current connection; reconnect if needed
			debug()				- Debug mode. Verbose errors. DO NOT USE FOR PRODUCTION. 
								  (Also disables caching by fefault.)
			optimize()			- Display query optimization information for each SELECT
								  query executed, including MySQL explanation and execution
								  time. Disables query caching. 
			error()				- Return error message from this connection
			getLastQuery		- SQL of the last query executed
			
*/

	class dbLink {

		# YOUR DB SETTINGS GO HERE
		private $_dbUser = 'nobody';
		private $_dbPassword = '';
		private $_dbHost = 'localhost';

		private $_isConnected = false;
		private $_queryCount = 0;
		private $_lastQuery = '';
		private $_queryHistory = Array();
		private $_debug = false;
		private $_cache = false;
		private $_cacheTimer = 1;
		private $_cacheKey = '';
		private $_cacheUsed = false;
		private $_objectCache;
		private $_numRows;
		private $_dbSphinx;
		private $_optimize = false;
		public $link;

		public function __construct() {
			// OPTIONAL SYNTAX - (dbUser, [dbPassword], [dbHost])
			if(func_num_args() > 0) {
				$this->_dbUser = func_get_arg(0);	
				$this->_dbPassword = func_get_arg(1);
				if(func_num_args() > 2) {
					$this->_dbHost = func_get_arg(2); }
				if(func_num_args() > 3) {
					$this->_dbSphinx = func_get_args(3); }
			}
//			$this->link = mysql_connect($this->_dbHost, $this->_dbUser, $this->_dbPassword, false, 65536 );
			$this->_isConnected = false;
		}

		public function run($sql) {
			return $this->actionQuery($sql);
		}
		public function getRow($sql) {
			return $this->getHash($sql);
		}
		public function getOne($sql) {
			return $this->getScalar($sql); 
		}
		
		public function close() {
			mysql_close($this->link);
			$this->_isConnected = false;
		}

		public function query($sql) {
			# DEPRECATED; Use ->getArray($sql), which returns an array and permits caching
			$this->checkConnection();
			$this->_queryCount++;
			$this->_lastQuery = $sql;
			if(!$this->_debug) {
				return $this->mysqlquery($sql, $this->link);
			} else {
				if($r = $this->mysqlquery($sql, $this->link)) {
					return $r;
				} else {
					$this->_dbCrash();
				}	
			}
		}
		
		private function getArrayFromResults($r) {
			$data = Array();
			$this->_numRows = mysql_num_rows($r);
			while($l = mysql_fetch_array($r)) {
				$data[] = $l;
			}
			return $data;
		}
		
		public function getArray($sql) {
			# RETURNS
				# An array of records matching the query
				# An empty array if no records match 
				# False if the query fails
			$this->_cacheUsed = false;
			$this->_queryCount++;
			$this->_lastQuery = $sql;
			if(!$this->_cache) {
				$this->checkConnection();
				if(!$this->_debug) {
					if($r = $this->mysqlquery($sql, $this->link)) {
						$data = $this->getArrayFromResults($r);
						return $data;
					} else { 
						return false;
					}
				} else {
					if($r = $this->mysqlquery($sql, $this->link)) {
						$data = $this->getArrayFromResults($r);
						return $data;
					} else {
						$this->_dbCrash();
					}	
				}			
			} else {
				if(!$this->_objectCache->cacheRead('Query',$sql,$this->_cacheTimer)) {
				$this->checkConnection();
				if(!$this->_debug) {
					if($r = $this->mysqlquery($sql, $this->link)) {
							$data = $this->getArrayFromResults($r);
							$this->_objectCache->cacheWrite($data);
							return $data;
						} else { 
							return false;
						}
					} else {
						if($r = $this->mysqlquery($sql, $this->link)) {
							$data = $this->getArrayFromResults($r);
							$this->_objectCache->cacheWrite($data);
							return $data;
						} else {
							$this->_dbCrash();
						}	
					}						
				} else {
					$this->_cacheUsed = true;
					$this->_numRows = count($this->_objectCache->cachedValue());
					return $this->_objectCache->cachedValue();
				}
			}
		}

		public function actionQuery($sql) {
			$this->checkConnection();
			$this->_cacheUsed = false;
			$this->_queryCount++;
			$this->_lastQuery = $sql;
			if(@mysql_query($sql, $this->link)) {
				return true;
			} else {
				if($this->_debug) {	
					$this->_dbCrash();
				} else {
					return false; 
				}
			}
		}

		public function getHash($sql) {
			$this->_cacheUsed = false;
			$this->_queryCount++;
			$this->_lastQuery = $sql;
			if(!$this->_cache) {
				$this->checkConnection();
				$result = $this->mysqlquery($sql, $this->link);
				if(!$result) {
					if($this->_debug) {
						$this->_dbCrash();
					} else {
						return false; }	
				}	
				if(mysql_num_rows($result) == 0) {
					return false;
				} else {
					return mysql_fetch_array($result, MYSQL_ASSOC);
				}
			} else {
				if(!$this->_objectCache->cacheRead('Query',$sql,$this->_cacheTimer)) {
					$this->checkConnection();
					$result = $this->mysqlquery($sql, $this->link);
					if(!$result) {
						if($this->_debug) {
							$this->_dbCrash();
						} else {
							return false; }	
					}	
					if(mysql_num_rows($result) == 0) {
						$this->_objectCache->cacheWrite(false);
						return false;
					} else {
						$h = mysql_fetch_array($result, MYSQL_ASSOC);
						$this->_objectCache->cacheWrite($h);
						return $h;
					}										
				} else {
					$this->_cacheUsed = true;
					return $this->_objectCache->cachedValue();
				}
			}
		}

		public function getOptionsList($sql) {
			$this->checkConnection();
			$strOut = "";
			$this->_queryCount++;
			$this->_lastQuery = $sql;
			$result = @$this->mysqlquery($sql, $this->link)
				or die(mysql_error($this->link));
			if($result) {
				while($l = mysql_fetch_array($result, MYSQL_NUM)) {
					if(mysql_num_fields($result) == 1) {
						$strOut .= "<option value=\"$l[0]\">$l[0]</option>\n";
					} else {
						$strOut .= "<option value=\"$l[0]\">$l[1]</option>\n";
					}
				}
				mysql_free_result($result);
				return $strOut;
			} else {	
				return false;
			}
		}

		
		public function getScalar($sql) {
			$this->_cacheUsed = false;
			$this->_queryCount++;
			$this->_lastQuery = $sql;
			if(!$this->_cache) {
				$this->checkConnection();
				$result = $this->mysqlquery($sql, $this->link);
				if(!$result) {
					if($this->_debug) {
						$this->_dbCrash();
					} else {
						return false; }	
				}
				if(mysql_num_rows($result) == 0) {
					return false;
				} else {
					$l = mysql_fetch_array($result, MYSQL_NUM);
					return $l[0];
				}	
			} else {
				if($this->_objectCache->cacheRead('Query',$sql,$this->_cacheTimer)) {
					$this->_cacheUsed = true;
					return $this->_objectCache->cachedValue();
				} else {
					$this->checkConnection();
					$result = $this->mysqlquery($sql, $this->link);
					if(!$result) {
						if($this->_debug) {
							$this->_dbCrash();
						} else {
							return false; }	
					}
					if(mysql_num_rows($result) == 0) {
						$this->_objectCache->cacheWrite(false);
						return false;
					} else {
						$l = mysql_fetch_array($result, MYSQL_NUM);
						$this->_objectCache->cacheWrite($l[0]);
						return $l[0];
					}	
				}
			}
		}

		public function execStoredProcedure($sproc) {
			$this->checkConnection();
			$this->_queryCount++;
			$sql = "CALL $sproc";
			$this->_lastQuery = $sql;
			$result = $this->mysqlquery($sql, $this->link);
			if(!$result) {
				if($this->_debug) {
					$this->_dbCrash();
				} else {
					return false;
				}
			} else {
				return $result;
			}
		}
		
		public function lastInsertId() {
			$sql = "SELECT last_insert_id()";
			return $this->getScalar($sql);
		}

		public function checkConnection() {
			if(!$this->_isConnected) {
				if($this->_dbSphinx == true) {
					$this->link = @mysql_pconnect($this->_dbHost);
				} else {
					$this->link = mysql_connect($this->_dbHost, $this->_dbUser, $this->_dbPassword);
				}
				$this->_isConnected = true;
			}
#			$sql = "SELECT 1";
#			if(!$this->query($sql)) {
#				$this->link = mysql_pconnect($this->_dbHost, $this->_dbUser, $this->_dbPassword);
#				$this->_isConnected = true;
#			}
		}

		public function numRows() {
			// get the total number of rows returned by the last query
			// respects limit clause
			return $this->_numRows;
		}
		
		public function getNumberOfResults() {
			$this->checkConnection();
			// get the total number of results matching the last query,
			// regardless of any limit clauses	
			$sql = "SELECT FOUND_ROWS() AS total_rows ";
			$result = $this->mysqlquery($sql, $this->link);
			if(mysql_num_rows($result) == 0) {
				return false;	
			} else {
				$l = mysql_fetch_array($result);
				return $l[total_rows];
			}
		}

		public function getLastID() {
			$sql = "SELECT last_insert_id() AS l";
			$this->_queryCount++;
			return $this->getScalar($sql);
		}
			

		public function getLastQuery() {
			return $this->_lastQuery;	
		}

		public function hostInfo() {
			return mysql_get_host_info($this->link);
		}

		public function serverInfo() {
			return mysql_get_server_info($this->link);
		}

		public function queryInfo() {
			return mysql_info($this->link);
		}

		public function reconnect() {
			$this->link = mysql_connect($this->_dbHost, $this->_dbUser, $this->_dbPassword);
			$this->_isConnected = true;
		}

		public function error() {
			return mysql_error($this->link);
		}

		public function queries() {
			return $this->_queryCount;
		}
		
		public function debug() {
			$this->_cache = false;
			$this->_debug = true;	
		}
		
		public function cacheUsed() {
			return $this->_cacheUsed; 
		}
		
		public function cache($TTL) {
			$this->_cache = true;
			$this->_cacheTimer = $TTL;
			$this->_objectCache = new objectCache();
		}
		
		public function cacheType($t) {
			# options are file, mem
			if($t == 'file') {
				$this->_objectCache = new objectCache();
			} elseif ($t == 'mem') {
				$this->_objectCache = new simpleMemcache();
			}
		}
		
		public function memcache($TTL) {
			$this->_cache = true;
			$this->_cacheTimer = $TTL;
			$this->_objectCache = new simpleMemcache();
		}
		
		public function cacheServer($server, $port) {
			# Add servers to a memcache cache system
			$this->_objectCache->server($server, $port);
		}
		
		public function noCache() {
			$this->_cache = false;
		}
		
		private function _dbCrash() {
			$sql = $this->_lastQuery;
			$msg = " <p><p><b>DB FRAMEWORK ERROR</b><br /><br />Encountered problem: " . mysql_error($this->link) . "<br /><br />In SQL:<br /><br />$sql ";
			print $msg;
			die();
		}
	
		public function isConnected() {
			$this->checkConnection();
			if($this->_dbSphinx) { 
				$sql = "SHOW TABLES";
			} else {
				$sql = "SELECT 1";
			}
			if(mysql_query($sql, $this->link)) {
				return true;
			} else { 
				return false; 
			}
		}
	
		public function optimize() {
			if(!$this->_dbSphinx) { 
				set_time_limit(60*60*24);
				$this->_optimize = true; 
				$this->_cache = false; 
			}
		}
		
		private function optimizeInfo($sql) {
			$this->checkConnection();
			print "<b>Executed SQL:</b> $sql";
			$osql = "EXPLAIN " . $sql;
			$r = mysql_query($osql, $this->link);
			print "<table cellspacing=1 border=1 cellpadding=1 style='font-size:9pt; font-family:arial;'><tr><td>id</td><td>select_type</td><td>table</td><td>type</td><td>possible_keys</td><td>key</td><td>key_len</td><td>ref</td><td>rows</td><td>extra</td></tr>";
			while($l = mysql_fetch_array($r)) {
				print "<tr><td>$l[0]</td><td>$l[1]</td><td>$l[2]</td><td>$l[3]</td><td>$l[4]</td><td>$l[5]</td><td>$l[6]</td><td>$l[7]</td><td>$l[8]</td><td>$l[9]</td></tr>";
			}
			print "</table>";
		}
		
		private function mysqlquery($sql,$link) {
			if($this->_optimize) {
				$start = Microtime(); }
			$r = @mysql_query($sql,$link);
			if($this->_optimize) {
				$this->optimizeInfo($sql);
				print "Execution Time: " . number_format((Microtime() - $start), 2); }
			return $r;
		}
		
		public function sphinxSnippet($doc, $index, $q, $options) {
			if($this->_dbSphinx) {
				$sql = "CALL SNIPPETS('" . addslashes($doc) . "', '$index', '" . addslashes($q) . "', $options)";
				return $this->getScalar($sql);
			} else {
				return false; }
		}
		
		public function sphinxTotalRows() {
			// Get total number of documents matching the last query
			if($this->_dbSphinx) {
				$r = $this->getArray("SHOW META");
				return $r[1][1];
			} else {
				return false;
			}
		}
	
	}

?>