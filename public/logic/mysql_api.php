<?php
/*
 * Database connection and interaction library
 */
class MysqlApi {
	private $host;
	private $database;
	private $user;
	private $password;

	private $link;

	/**
	 * Construct a new connection object.
	 *
	 * @param[in] host string The hostname of the database server.
	 */
	public function __construct($host='localhost', $database, $user='nobody',
		$password) {

		$this->host = $host;
		$this->database = $database;
		$this->user = $user;
		$this->password = $password;

		$this->connect();
	}

	public function setLink($link) {
		$this->link = $link;
	}

	public function getLink() {
		return $this->link;
	}

	/**
	 * Establish a connection to a mysql database
	 *
	 * @return boolean. If not FALSE, then the connection either
	 *     previously existed, or was established properly.
	 */
	public function connect() {
		if ($this->link && ($this->link !== FALSE)) {
			return TRUE;
		}

		mysqli_report(MYSQLI_REPORT_ERROR);
		$this->link = mysqli_connect($this->host, $this->user, $this->password);
		if (!$this->link) {
			error_log('unable to establish connection with mysql database');
			return FALSE;
		}

		if (!is_null($this->database) && 
			!mysqli_select_db($this->link, $this->database)) { 
			error_log('unable to select mysql database');
			return FALSE;
		}

		return TRUE; 
	}

	/**
	 * Query the database.
	 *
	 * @param[in] sql string A SQL command to be executed.
	 * @return mysql database connection resource.
	 */
	public function query($sql) {
		if (!($this->link) && (!$this->connect())) {
			return FALSE;
		}

		$result = mysqli_query($this->link, $sql);
		if (!$result) {
			$err = mysqli_error($this->link);
			error_log("Could not get a result from the query, err: {$err}");
			return FALSE;
		}
		return $result;
	}

	/**
	 * Retrieve data from the database, return an associative array
	 *
	 * @param[in] sql string A SQL command to be executed.
	 * @param[in] primary_key string (optional, defaults to NULL). If supplied,
	 *     then the results should be indexed by the value found in this column.
	 * @param[in] do_stripslashes boolean (default TRUE). If TRUE, then apply
	 *     stripslashes to the returned output.
	 */
	public function get($sql, $primary_key=NULL, $do_stripslashes=TRUE) {
		$found = array();

		$result = $this->query($sql);
		if (is_null($result) || ($result === FALSE)) {
			return FALSE;
		}

		while($info = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
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
