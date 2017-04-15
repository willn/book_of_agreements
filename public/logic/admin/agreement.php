<?php
	$update_string = '';
	$update = false;
	$TempDate = '';
	$expired = 0;
	$surpassed_by = '';
	if ( isset( $_POST['surpassed_by'] )) {
		$surpassed_by = intval($_POST['surpassed_by']);
	}

	# receiving a post of editing agreement, new or old
	$Agrms = new Agreement();
	if ( isset( $_POST['admin_post'] )) {
		$TempDate = new MyDate( intval( $_POST['year'] ), 
			intval( $_POST['month'] ), intval( $_POST['day'] ));

		if ( isset( $_POST['expired'] )) {
			$expired = 1;
		}

		$pub = false;
		if ( isset($_POST['world_public']) && $_POST['world_public'] == 'on') {
			$pub = true;
		}

		$Agrms->setContent(
			mysql_real_escape_string( $_POST['title'] ), 
			mysql_real_escape_string( $_POST['summary'] ), 
			mysql_real_escape_string( $_POST['full'] ), 
			mysql_real_escape_string( $_POST['background'] ), 
			mysql_real_escape_string( $_POST['comments'] ), 
			mysql_real_escape_string( $_POST['processnotes'] ), 
			intval( $_POST['cid'] ),
			$TempDate, 
			$surpassed_by,
			$expired,
			$pub
		);
		$update = true;
	}

	if ( isset( $_POST['save'] )) {
		$Agrms->save($update);
	}
	elseif( isset( $_GET['delete'] )) {
		$Agrms->delete();
	}
	else {
		$Agrms->display('form');
	}

?>
