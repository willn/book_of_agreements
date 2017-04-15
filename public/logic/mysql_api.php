<?php
/*
 * Database connection and interaction library
 */
class MysqlApi {
	var $host;
	var $database;
	var $user;
	var $password;

	var $link;

	/**
	 * Construct a new connection object.
	 *
	 * @param[in] host string The hostname of the database server.
	 */
	function MysqlApi($host='localhost', $database, $user='nobody',
		$password) {

		if ( !extension_loaded( 'mysql' )) {
			if ( !dl( 'mysql.so' )) {
				exit( 'Cannot load mysql extension.' );
			}
		}

		$this->host = $host;
		$this->database = $database;
		$this->user = $user;
		$this->password = $password;
	}

	function setLink($link) {
		$this->link = $link;
	}

	/**
	 * Establish a connection to a mysql database
	 *
	 * @return boolean. If TRUE, then the connection either previously existed,
	 *     or was established properly.
	 */
	function connect() {
		if (!is_null($this->link) && ($this->link !== FALSE)) {
			return TRUE;
		}

		$this->link = mysql_connect($this->host, $this->user, $this->password);
		if (is_null($this->link)) {
			error_log('unable to establish connection with mysql database');
			return FALSE;
		}

		if (!is_null($this->database) && 
			!mysql_select_db($this->database, $this->link)) { 
			error_log('unable to select mysql database');
			return FALSE;
		}

		return TRUE; 
	}

	/**
	 * Query the database.
	 *
	 * @param[in] query string A SQL command to be executed.
	 * @return mysql database connection resource.
	 */
	function query($query) {
		if (is_null($this->link) && (!$this->connect())) {
			return FALSE;
		}

		$result = mysql_query($query, $this->link);
		if (!$result) {
			$err = mysql_error();
			error_log("Could not get a result from the query, err: {$err}");
			return FALSE;
		}
		return $result;
	}

	/**
	 * Retrieve data from the database, return an associative array
	 *
	 * @param[in] query string A SQL command to be executed.
	 * @param[in] primary_key string (optional, defaults to NULL). If supplied,
	 *     then the results should be indexed by the value found in this column.
	 * @param[in] do_stripslashes boolean (default TRUE). If TRUE, then apply
	 *     stripslashes to the returned output.
	 */
	function get($query, $primary_key=NULL, $do_stripslashes=TRUE) {
		$found = array();

		$result = $this->query($query);
		if (is_null($result) || ($result === FALSE)) {
			return FALSE;
		}

		while($info = mysql_fetch_array($result, MYSQL_ASSOC)) {
			if ($do_stripslashes) {
				$info = array_map('stripslashes', $info);
			}

			if (is_null($primary_key)) {
				$found[] = $info;
			}
			else {
				$found[$info[$primary_key]] = $info;
			}
		}

		return $found;
	}
}
?>
