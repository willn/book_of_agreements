<?php
	$body = 'logic/list_all_agreements.php';
	$sort = '';

	if ( isset( $_GET['sort'] )) {
		switch( $_GET['sort'] ) {
			case 'date':
			case 'committee':
			case 'agreement':
				$sort = $_GET['sort'];
				break;
		}
	}

	$Cmty = new Committee( );
?>
