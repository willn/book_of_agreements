<?php
	require_once('logic/utils.php');

	$update_string = '';
	$update = false;
	$TempDate = '';

	$mysql_api = get_mysql_api();

	# receiving a post of editing minutes, new or old
	if ( isset( $_POST['admin_post'] )) {
		$TempDate = new MyDate( intval( $_POST['year'] ), 
			intval( $_POST['month'] ), intval( $_POST['day'] ));

		$mysql_link = $mysql_api->getLink();
		$Mins = new Minutes( 
			intval( $_POST['num'] ),
			mysqli_real_escape_string($mysql_link, $_POST['notes']), 
			mysqli_real_escape_string($mysql_link, $_POST['agenda']),
			mysqli_real_escape_string($mysql_link, $_POST['content']),
			intval( $_POST['cid'] ),
			$TempDate
		);
		$update = true;
	}
	elseif ( $num > 0 ) {
		# edit minutes, document number
		$Mins = new Minutes( $num );
	}
	else {
		# first visit
		$Mins = new Minutes( );
	}
	$Cmty = new Committee( $Mins->cid );

	if ( isset( $_POST['save'] )) {
		$Mins->save( $update );
	}
	elseif( isset( $_GET['delete'] )) {
		$Mins->delete( $confirm_del );
	}
	else {
		global $Cmtys;
		global $SubCmtys;
		$Cmty->setId($Mins->cid);

		if ( $num > 0 ) {
			$update_string = 
				'<input type="hidden" name="update" value="1">' . "\n";
		}

		echo '<h1>admin minutes entry tool</h1>'.
			'<form action="?id=admin" method="post">' . "\n".
			'<input type="hidden" name="doctype" value="minutes">' . "\n".
			'<input type="hidden" name="admin_post" value="1">' . "\n".
			'<input type="hidden" name="num" value="'.$num.'">' . "\n".
			$update_string . 
			$Mins->Date->selectDate( ) .
			$Cmty->getSelectCommittee($Cmtys, $SubCmtys);
		$Mins->display( 'form' );
		echo '<p><input type="submit" name="save" ' .
			'value="save changes &rarr;">' . "</p></form>\n";
	}
?>
