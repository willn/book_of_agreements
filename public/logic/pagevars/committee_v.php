<?php
	if ( !isset( $_GET['cmty'] )) {
		require( 'logic/pagevars/home_v.php' );
	}
	else {
		$only = '';
		$max = 100;

		$cmty_num = NULL;
		if ( isset( $_GET['cmty'] )) {
			$cmty_num = intval( $_GET['cmty'] );
		}

		$sub_cmty_num = NULL;
		if ( isset( $_GET['sub'] )) {
			$sub_cmty_num = intval( $_GET['sub'] );
		}
		$body = 'logic/listing.php';

		if ( isset( $_GET['only'] )) {
			$only = ( $_GET['only'] == 'agreements' ) ? 'agreements' : '';
			$only = ( $_GET['only'] == 'minutes' ) ? 'minutes' : $only;
		}
	}
?>
