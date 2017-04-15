<?php
	$body = 'logic/list_all_minutes.php';
	if ( !isset( $_GET['sort'] )) { $sort = ''; }
	elseif (( $_GET['sort'] == 'date' ) || ( $_GET['sort'] == 'committee' ))
	{ $sort = $_GET['sort']; }

	$Cmty = new Committee( );
?>
