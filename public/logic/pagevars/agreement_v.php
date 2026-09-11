<?php
require_once dirname(__DIR__) . '/class_agreement.php';

# adding a new entry
if (!isset( $_GET['num']) || (intval($_GET['num']) == 0)) {
	$show = '';
	$show_exp = false;
	if (is_authenticated() && isset( $_GET['show'] )) {
		if ( $_GET['show'] == 'expired' ) {
			$show = 'expired';
		}
	}
	require_once __DIR__ . '/all_agreements_v.php';
}
else {
	# display a single agreement
	$Date = new MyDate(); 
	$body = 'logic/agreement.php';
}
?>
