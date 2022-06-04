<?php
ini_set('display_errors', '0');
header("Content-Security-Policy: script-src 'self'");
header("Content-Security-Policy: script-src-elem 'self'");
session_start();

require_once 'logic/mysql_api.php';

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
	echo <<<EOHTML
<form method="POST" action="/boa/?login=1">
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
