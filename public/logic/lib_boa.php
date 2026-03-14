<?php

require_once('constants.php');
require_once('utils.php');
require_once('mydate.php');
require_once('committee.php');
require_once('config.php');


/**
 * Parent class to both Agreements and Minutes
 */
abstract class BOADoc {
	public $mysql_api;
	public $cmty;
	public $id;

	public function __construct() {
		$this->cmty = new Committee();
	}

	public function setId($id) {
		$this->id = $id;
		$this->cmty->setId($id);
	}

	public function getId() {
		return $this->id;
	}

	public function init_mysql_api() {
		if (!is_null($this->mysql_api)) {
			return;
		}

		$this->mysql_api = get_mysql_api();
	}
}

?>
