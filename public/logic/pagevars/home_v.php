<?php
	# don't make this plural, since it's used in a SQL query
	$limit_time = '30 day';

	$title = 'Recently Active Items (' . $limit_time . 's)';
	$cmty_num = NULL;
	$only = NULL;
	$max = 100;

	if ( isset( $_GET['cmty'] )) {
		$cmty_num = intval( $_GET['cmty'] );
	}

	if ( isset( $_GET['only'] ))
	{
		$only = ( $_GET['only'] == 'agreements' ) ? 'agreements' : '';
		$only = ( $_GET['only'] == 'minutes' ) ? 'minutes' : $only;
	}

    if ( $PUBLIC_USER ) {
		$only = 'agreements';
	}
	
	$body = 'logic/listing.php';
?>
