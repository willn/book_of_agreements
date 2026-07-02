<?php
require_once dirname(__DIR__) . '/class_minute.php';

if (!($_SESSION['boa_username'] === 'greatoak') ||
	($_SESSION['boa_username'] === 'admin')) { 
	echo <<<EOHTML
<h2>User Not Authorized</h2>
<p>Please return to the <a href="http://gocoho.org/boa/">main page</a>.</p>
EOHTML;

	error_log(__FILE__ . ' ' . __LINE__ . " unauthorized user");
	exit;
}

if ( !isset( $_GET['num'] )) {
	require __DIR__ . '/all_minutes_v.php';
}
else {
	$num = intval( $_GET['num'] );
	$Mins = new Minutes($num);
	$Cmty = new Committee($Mins->cid);
	$title = 'Minutes: ' . $Cmty->getName() . ' ' . 
		$Mins->Date->toString( ) . ' [Minutes]';

	$body = 'logic/minutes.php';
}

?>
