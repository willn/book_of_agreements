<?php
	$Types = array( 'minutes', 'agreement' );
	$num = 0;
	$update = false;
	$confirm_del = false;
	$stylesheets[] = 'admin.css';

	# fetch document id, if it exists...
	if ( isset( $_POST['num'] )) {
		$num = intval( $_POST['num'] );
	}
	elseif ( isset( $_GET['num'] )) {
		$num = intval( $_GET['num'] );
	}
	elseif ( isset( $_GET['delete'] )) {
		$num = intval( $_GET['delete'] );
	}
	
	# delete confirmation decision
	if ( isset( $_GET['confirm_del'] )) {
		$confirm_del = true;
	}

	# grab update value
	if ( isset( $_POST['update'] )) {
		$update = true;
	}

	# set some variables...
	$doctype = '';
	if ( isset( $_POST['doctype'] ) && in_array( $_POST['doctype'], $Types )) {
		$doctype = $_POST['doctype'];
	}
	elseif ( isset( $_GET['doctype'] ) && in_array( $_GET['doctype'], $Types )) {
		$doctype = $_GET['doctype'];
	}

	if ( empty( $doctype )) {
		$message = '<p>Please choose a document type to administer:</p>';
		foreach ( $Types as $d ) {
			$message .= '<p><a href="?id=admin&amp;doctype=' . $d . '">' .
				$d . "</a></p>\n";
		}
		$body = 'logic/admin/error.php';
	}
	else {
		switch( $doctype ) {
			case 'agreement': $body = 'logic/admin/agreement.php';
				break;
			case 'minutes': $body = 'logic/admin/minutes.php';
				break;
			default: $body = 'logic/admin/error.php';
		}
	}
?>
