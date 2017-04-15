<?php
function create_config_file() {
	$self = $_SERVER['REQUEST_URI'];

	if (isset($_REQUEST['posted'])) {
		if (save_config_file()) {
			echo "success!\n<a href=\"{$self}\">view your site</a>\n";
			exit;
		}
		else {
			echo '<div class="error">Please fill in the required values</div>';
		}
	}

	echo <<<EOHTML
<h1>Fill in configuration values</h1>

<form method="post" action="{$self}">
<input type="hidden" name="posted" value="1">

<h2>Basic auth</h2>

<div>
* username:
<br><input type="text" name="basic_auth_user" value="user">
</div>

<div>
* password:
<br><input type="password" name="basic_auth_pw" value="">
</div>

<h2>mysql database</h2>

<div>
* hostname:
<br><input type="text" name="mysql_host" value="localhost">
</div>

<div>
* database name:
<br><input type="text" name="mysql_db" value="">
</div>

<div>
* username:
<br><input type="text" name="mysql_user" value="">
</div>

<div>
* password:
<br><input type="password" name="mysql_pw" value="">
</div>

<input type="submit" value="configure">

</form>
EOHTML;

	exit;
}

function save_config_file() {
	if (!isset($_REQUEST['basic_auth_user']) ||
		!isset($_REQUEST['basic_auth_pw']) ||
		!isset($_REQUEST['mysql_host']) ||
		!isset($_REQUEST['mysql_db']) ||
		!isset($_REQUEST['mysql_user']) ||
		!isset($_REQUEST['mysql_pw'])) {
		return FALSE;
	}

	// create user's basic auth password
	$basic_auth_pw = sha1($_REQUEST['basic_auth_pw']);

	// try connecting to the database...
	$HDUP = array(
		'host' => $_REQUEST['mysql_host'],
		'database' => $_REQUEST['mysql_db'],
		'user' => $_REQUEST['mysql_user'],
		'password' => $_REQUEST['mysql_pw'],
	);
	global $G_DEBUG;
	if (is_null($link = my_connect($G_DEBUG, $HDUP))) {
		echo "could not connect to database {$HDUP['database']}\n";
		return FALSE;
	}
	echo "<p>test database connection successful</p>\n";

	$config = <<<EOTXT
<?php

define('AUDIT_CONTACT', '');
define('FROM_ADDRESS', '');
define('SITE_NAME', '');

\$Basic_Auth_Users = array( 
        '{$_REQUEST['basic_auth_user']}' => '{$basic_auth_pw}',
);
\$Admin_Users = array('');
\$basic_auth_realm = SITE_NAME;

\$HDUP = array(
        'host'=>'{$HDUP['host']}',
        'database'=>'{$HDUP['database']}',
        'user'=>'{$HDUP['user']}',
        'password'=>'{$HDUP['password']}'
);
\$admin_string = '';

?>
EOTXT;
	file_put_contents('config.php', $config);
	if (!file_exists('config.php')) {
		echo '<div class="error">Was not able to write out a new config file</div>';
		return FALSE;
	}

	return TRUE;
}

?>
