<?php
session_start();
global $mysql_api;

/*
 * If the file exists, then use it to pull in the users list. Otherwise,
 * ignore it so it can be created.
 */
if (file_exists('config.php')) {
	require_once('config.php');

	require_once 'logic/mysql_api.php';
	$mysql_api = new MysqlApi($HDUP['host'], $HDUP['database'],
		$HDUP['user'], $HDUP['password']);
}

$is_authenticated = FALSE;

// is the user logged in already?
if ( isset( $_SESSION['logged_in'] ) && $_SESSION['logged_in'] == 1 ) {
	$is_authenticated = TRUE;

	// logout...?
	if ( isset( $_GET['login'] ) && $_GET['login'] == 0 ) {
		$is_authenticated = FALSE;
		unset($_SERVER['PHP_AUTH_USER']); 
		$_SESSION['logged_in'] = 0;
	}
}
elseif ( isset( $_GET['login'] ) && $_GET['login'] == 1 ) {
	$is_authenticated = attempt_login();	
}

require_once( 'main.php' );

/**
 * Attempt to login the user. Otherwise display the login form.
 */
function attempt_login() {

	$auth_users = get_authorized_users();
	global $PUBLIC_USER;

	if (!isset($_POST['boa_username']) || !isset($_POST['boa_password'])) {
		display_login_form();
		exit;
	}

	// check the password
	if ($auth_users[$_POST['boa_username']] == sha1( $_POST['boa_password'] )) {
		$_SESSION['logged_in'] = 1;
		$PUBLIC_USER = FALSE;
		return TRUE;
	}
	else {
		display_login_form();
		exit;
	}
}

/**
 * Display the login form.
 */
function display_login_form() {
	$base_url = BASE_URL;
	echo <<<EOHTML
<form method="POST" action="{$base_url}/?login=1">
	<label>
		<span>Username:</span>
		<input type="text" name="boa_username" value="">
	</label>

	<label>
		<span>Password:</span>
		<input type="password" name="boa_password" value="">
	</label>

	<label>
		<button type="submit" name="login_attempt">Log in</button>
	</label>
</form>
EOHTML;
	exit;
}
?>
