<?/*

	SIMPLE PHP HANDLER FOR FILESYSTEM AND DATABASE CACHING
	
		- simpleCache			Simple filesystem-based cache for complete pages
		- simpleMemcache		Simple memcached system
								Has routines (start() and update()) for caching pages
								and routines (get() and set()) for caching objects
								* NOTE that this presumes you already have the PHP Memcache
								library installed and configured, and have access to a 
								working memcached server
		- dbCache				Simple database cache for complete pages
								* NOTE that this presumes you already have a working MySQL
								server, configured in incl-dbconnect.php
		- objectCache			Filesystem-based cache for php objects such;
								this is used for caching queries in the required
								dbCache database connection object
		- fragmentCache			Filesystem-based cache for page fragments, 
								identified by unique programatically-generated keys
								* NOTE that this should be deprecated; objectCache
								delivers the same results with a cleaner interface
								
	Brad Heath; brad@bradheath.org
		
*/
	
	
	class simpleMemcache { 
	
		private $_servers = array();
		private $_serversCount = 0;
		private $_ports = array();
		private $_m = array();
		private $_memcache;
		private $_TTL;
		private $_defaultTTL = 300;
		private $_lastValue;
		private $_objectName;
		private $_objectIdentifier;
		private $_key;
		
		function __construct() {
			if(func_num_args() == 1) { 
				$this->_TTL = func_get_arg(0);
			} else {
				$this->_TTL = $this->_defaultTTL; 
			}		
			$this->_memcache = new Memcache();
		}
		
		function start() {
			$key = ($_SERVER['REQUEST_URI']);
			$this->_key = $key;
			if($this->cacheRead('', $key, $ttl)) {
				echo $this->_lastValue;
				echo "<!--- cached --->";
				exit;
			} else {
				ob_start();
				return false; 
			}	
		}
		
		function cacheRead($on, $oi, $ttl) {
			$this->_objectName = $on;
			$this->_objectIdentifier = $oi;
			$this->_key = md5($on . $oi);
			if($this->_memcache->get($this->_key)) {
				$this->_lastValue = unserialize($this->_memcache->get($this->_key));
				return $this->_lastValue;
			} else {
				$this->_lastValue = false;
				return false; 
			}
		}
		
		function cachedValue() { 
			return $this->_lastValue;
		}
		
		function cacheWrite($v) {
			$this->_memcache->set($this->_key, serialize($v), false, $this->_TTL);
		}
		
		function update() {
			$this->cacheWrite(ob_get_contents());
		}
		
		function server($server, $port) {
			$this->_serverCount++;
			$this->_servers[$this->_serverCount] = $server;
			$this->_ports[$this->_serverCount] = $port;
			$this->_memcache->addServer($server, $port);
		}
		
		function status() {
			return $this->_memcache->getExtendedStats();
		}
	
	}
	
	class simpleCache {
	
		private $_cachePath = "/home1/bradheat/cache/";			// relative path to cache files
		private $_defaultUpdateTime;				// default time of 24 hours
		private $_cacheFileName;
		private $_updateTime;
		private $_enabled = true;
		
		function __construct() {
			ob_start();
			$this->_defaultUpdateTime = 60*60*24;
			$this->_cacheFileName = $this->_cachePath . $this->cache_fn($_SERVER['REQUEST_URI']) . ".cache";
			if(func_num_args() == 1) { 
				$this->_updateTime = func_get_arg(0);
			} else {
				$this->_updateTime = $this->_defaultUpdateTime; 
			}
			$this->dump();
		}

		function disable() {
			$this->_enabled = false;
		}
		
		private function cache_fn($fn) {
			// generate unique filename based on passed paramaters
			return md5(trim(strtoupper($fn)));
		}

		function update() {
			// update the cache file
			if($this->_enabled) {
				$bc = ob_get_contents();
				$f = fopen( $this->_cacheFileName , "w");
				fwrite($f, $bc);
				fclose($f);
				ob_end_clean;
			}
		}
		
		function dump() {
			// check for existing cache file and display if available and timely
			if($this->_enabled) {
				if(file_exists($this->_cacheFileName)
					&& (filemtime($this->_cacheFileName) > (time() - $this->_updateTime ))) {
					include($this->_cacheFileName);
					echo "<!-- Cached ".date('jS F Y H:i', filemtime($this->_cacheFileName))."  -->";
					exit();
				}
			}
		}
	}	

	// dbCache
	// cache pages or fragments to a predefined database table (cache.cache);
	// requires dbLink database connection class
	class dbCache { 
		private $_cacheTable = 'cache.cache';
		private $_defaultUpdateTime;
		private $_cacheIdentifier;
		private $_updateTime;
		private $_db;
		private $_makeNewDB;
		private $_objectID;
		private $_objectIDEncoded;
		private $_header;
		private $_purgeTime;
		
		function __construct() {
			ob_start();
			$this->_defaultUpdateTime = 60 * 60;
			$this->_db = new dbLink();
			$this->checkDB();
			if(func_num_args() == 1) { 
				$this->_updateTime = func_get_arg(0);
			} else {
				$this->_updateTime = $this->_defaultUpdateTime; 
			}
			$this->_objectID = $this->cache_fn($_SERVER['REQUEST_URI']);
			$this->_objectIDEncoded = addslashes($this->_objectID);
			$this->dump();
		}
		
		function setContentHeader($header) {
			$this->_header = $header;		
		}
		
		function update() {
			$this->checkDB();
			$bc = ob_get_contents();
			$ob = $this->_objectIDEncoded;
			$sql = "REPLACE DELAYED INTO " . $this->_cacheTable . " (objectid, stream, header) VALUES(\"$ob\", COMPRESS(\"$bc\"), \"" . $this->_header . "\") ";
			$ins = $this->_db->actionQuery($sql);
		}

		function dump() {
			$sql = "SELECT UNCOMPRESS(stream) AS u, header AS hdr FROM cache.cache 
				WHERE objectid = '" . $this->_objectIDEncoded . "' 
				AND TIME_TO_SEC(TIMEDIFF(NOW(), created)) <= " . $this->_updateTime . "
				";
			$h = $this->_db->getHash($sql);
			if($h) { 
				if($h[hdr] != '') {
					header($h[hdr]); }
				print $h[u];
				exit();
			}
		}			

		private function cache_fn($fn) {
			return md5(trim(strtoupper($fn)));
		}
		
		function checkDB() {
			$r = $this->_db->query("SELECT NULL FROM cache.cache LIMIT 1");
			if(!$r) {
				$this->_makeNewDB = true; }
			if($this->_makeNewDB == true) {
				$sql = "CREATE DATABASE IF NOT EXISTS cache ";
				$this->_db->actionQuery($sql);
				$sql = "CREATE TABLE IF NOT EXISTS cache.cache(objectid VARCHAR(255) PRIMARY KEY, created TIMESTAMP, stream LONGBLOB, header VARCHAR(255), url VARCHAR(255), KEY url_idx(url), KEY objectid_idx(objectid), KEY created_idx(created)) TYPE=MyISAM ";
				$this->_db->actionQuery($sql) or die($this->_db->error());
			}			
		}
		
		function purge() {
			$this->checkDB();
			if(func_num_args() == 1) { 
				$this->_purgeTime = func_get_arg(0);
			} else {
				$this->_purgeTime = $this->_defaultUpdateTime; 
			}
			$sql = "DELETE FROM cache.cache WHERE 
				TIME_TO_SEC(TIMEDIFF(NOW(), created)) <= " . $this->_updateTime . "
				";
			$r = $this->_db->actionQuery($sql) or die($sql);
		}		

		
	}


	// cache data objects
	class objectCache {
		private $_cacheFileName;
		private $_updateTime;
		private $_objectName;
		private $_objectIdentifier;
		private $_object;
		
		function __construct() {
		}	
		
		function cacheFileName ($objectName, $objectIdentifier) {
			$this->_cacheFileName = '/cache/' . md5($objectName . '-' . $objectIdentifier) . '.objectcache';
		}
		
		function cacheRead($objectName, $objectIdentifier, $updateTime) {
			$this->_objectName = $objectName;
			$this->_objectIdentifier = $objectIdentifier;
			$this->_updateTime = $updateTime;
			$this->cacheFileName($objectName, $objectIdentifier);
			return $this->dump();
		}
		
		function cacheWrite($object) {
			$timeCache = microtime(true);
			$bc = serialize($object);
			$f = fopen( $this->_cacheFileName , "w");
			fwrite($f,$bc);
			fclose($f);
		}
		
		function dump() {
			$out = false;
			if((file_exists($this->_cacheFileName)) 
				&& (filemtime($this->_cacheFileName) > (time() - $this->_updateTime ))) {
				
				$object = unserialize(file_get_contents($this->_cacheFileName));
				$this->_object = $object;
				return $object;
				
			} else {
				return false;
			}
		}	

		function cachedValue() {
			return $this->_object;
		}
	}
	
	

	// cache mechanism for PORTIONS of pages; 
	class fragmentCache {

		private $_cacheFileName;
		private $_updateTime;
		private $_fragmentName;
		private $_fragmentSpecialName;

		function __construct() {
			$this->_fragmentName = 0;
		}
	
		function startCache() {
			// start a new instance of the partial cache
			// USAGE IS startCache([timeout in milliseconds], [special cache name])
			ob_start();
			$this->_fragmentName++;
			if(func_num_args() >= 2) {
				$this->_fragmentSpecialName = func_get_arg(1);
			} else {
				$this->_fragmentName++;
				$this->_fragmentSpecialName = $this->_fragmentName;
			}
			$this->_cacheFileName = "c:\\cache\\" . cache_fn($_SERVER['REQUEST_URI']) . "_" . $this->_fragmentSpecialName . ".fragmentcache";
			$fn = "cache/" . $this->_cacheFileName . "_" . $this->_fragmentName . ".fragmentcache";
			if(func_num_args() >= 1) { 
				$this->_updateTime = func_get_arg(0);
			} else {
				$this->_updateTime = 60 * 60 * 24; 	// one day (60 sec * 60 min * 24 hr)
			}
			return $this->dump();	
		}
		
		function endCache() {
			$timeCache = microtime(true);
			$bc = ob_get_contents();
			$f = fopen( $this->_cacheFileName , "w");
			fwrite($f, $timeCache . "|" . $bc);
			fclose($f);
			ob_end_clean;					
		}
		
		function dump() {
			// dump the output of a partial cache
			$out = false;
			if(file_exists($this->_cacheFileName)) {
				$contents = file_get_contents($this->_cacheFileName);
				$segs = explode("|",$contents);
				$timeNow = microtime(true);
				if(($timeNow - $segs[0]) <= $this->_updateTime) {
					for($i = 1; $i <= count($segs); $i++) {
						print $segs[$i]; }
					$out = true;
//					exit();
				} 
			}			
			return $out;
		}
		
	}

?>