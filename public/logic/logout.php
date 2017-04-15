<?php
	session_register( "boa-admin-passwd" );
	$_SESSION['boa-admin-passwd'] = 'logout';
	$_SESSION['admin'] = '';

	echo "<h1>Thank you for logging out.</h1>\n";
?>
