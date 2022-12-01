<?php
	if (!isset( $_GET['num']) || (intval($_GET['num']) == 0)) {
		$max = 100;
		$show = '';
		$show_exp = false;
		if ( !$PUBLIC_USER && isset( $_GET['show'] )) {
			if ( $_GET['show'] == 'expired' ) {
				$show = 'expired';
			}
		}
		require_once( 'logic/pagevars/all_agreements_v.php' );
		$title = 'All Agreements';
	}
	else
	{
		$Date = new MyDate( ); 
		$Agrms = new Agreement();
		$title = "Agreement: {$Agrms->title} [Agreement]";
		$body = 'logic/agreement.php';
	}
?>
