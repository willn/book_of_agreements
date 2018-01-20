<?php
if (!file_exists('../public/config.php')) {
	echo "unable to find config file\n";
	exit;
}
require_once '../public/config.php';

require_once '../public/logic/mysql_api.php';
$mysql = new MysqlApi($HDUP['host'], $HDUP['database'], $HDUP['user'],
	$HDUP['password']);

$agree_v = 'agreements_versions';
if (!does_table_exist($mysql, $HDUP['database'], $agree_v)) {
	if (!create_agreements_versions($mysql, $HDUP['database'])) {
		exit;
	}
}

upgrade_version_number('1.1');

echo "upgrade done\n";



/**
 * check to see if a table exists
 */
function does_table_exist($mysql, $database, $table) {
	$result = $mysql->get("SHOW TABLES FROM {$database}",
		'Tables_in_gocoho_boa');

	return isset($result[$table]);
}

/**
 * create the agreements_versions table
 */
function create_agreements_versions($mysql, $database) {
	$ag_vers_file = '../sql/agreements_versions.sql';
	if (!file_exists($ag_vers_file)) {
		echo "Could not find {$ag_vers_file}\n";
		return FALSE;
	}

	$contents = file_get_contents($ag_vers_file);
	if (is_null($contents)) {
		echo "creation file was empty\n";
		return FALSE;
	}

	$res = $mysql->query($contents);
}

function upgrade_version_number($version) {
	$version_file = '../VERSION';
	if (!file_exists($version_file)) {
		echo "was not able to find VERSION file\n";
		exit;
	}

	$result = file_put_contents($version_file,
		"Book of Agreements {$version}\n");
	return ($result !== FALSE);
}

?>
