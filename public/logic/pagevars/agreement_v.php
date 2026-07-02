<?php
require_once dirname(__DIR__) . '/class_agreement.php';

# adding a new entry
if (!isset( $_GET['num']) || (intval($_GET['num']) == 0)) {
	$show = '';
	$show_exp = false;
	if ((!isset($PUBLIC_USER) || !$PUBLIC_USER) && isset( $_GET['show'] )) {
		if ( $_GET['show'] == 'expired' ) {
			$show = 'expired';
		}
	}
	require_once __DIR__ . '/all_agreements_v.php';
	$title = 'All Agreements';
}
else {
	# display a single agreement
	$Date = new MyDate(); 
	$Agrms = new Agreement();
	$title = "Agreement: {$Agrms->title} [Agreement]";
	$body = 'logic/agreement.php';
}
?>
