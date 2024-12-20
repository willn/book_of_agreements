<?php
ini_set('display_errors', '0');
header("Content-Security-Policy: script-src 'self'");
header("Content-Security-Policy: script-src-elem 'self'");

/**
* If this file does not exist, then you'll need to make your own.
* cp config.php_default config.php
* Then modify this to store your database connectivity info, and any other
* customizations for your app.
*/
require_once('config.php');

session_start();

require_once 'logic/mysql_api.php';
$is_authenticated = is_authenticated();
require_once( 'main.php' );

/**
 * Is this user currently authenticated?
 */
function is_authenticated() {
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

	return $is_authenticated;
}

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
	if ($auth_users[$_POST['boa_username']] == hash('sha256', $_POST['boa_password'])) {
		$_SESSION['logged_in'] = 1;
		$_SESSION['boa_username'] = $_POST['boa_username'];
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
	$form_dest = '/boa/?login=1';

	$vars = get_query_string_vars();
	$passalong = '';
	if ($vars['page_id'] && $vars['num']) {
		$passalong = <<<EOHTML
<p>{$vars['page_id']}: {$vars['num']}</p>
<input type="hidden" name="id" value="{$vars['page_id']}">
<input type="hidden" name="num" value="{$vars['num']}">
EOHTML;

		$form_dest = "/boa/?id={$vars['page_id']}&num={$vars['num']}";
	}

	echo <<<EOHTML
<form method="POST" action="{$form_dest}">
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

	<div>{$passalong}</div>
</form>
EOHTML;

	exit;
}

/**
 * Look for certain query string parameters and return them.
 */
function get_query_string_vars() {
	$tmp_id = $_GET['id'];
	switch($tmp_id) {
		case 'agreement':
		case 'minutes':
			$out = ['page_id' => $tmp_id];

			$tmp_num = intval($_GET['num']);
			if ($tmp_num != 0) {
				$out['num'] = $tmp_num;
			}

			return $out;
	}

	return [];
}
?>
