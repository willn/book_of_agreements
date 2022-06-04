<?php
	require_once('logic/utils.php');

	$update_string = '';
	$update = false;
	$TempDate = '';
	$expired = 0;

	$mysql_api = get_mysql_api();

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

		$mysql_link = $mysql_api->getLink();
		$Agrms->setContent(
			mysqli_real_escape_string($mysql_link, $_POST['title']), 
			mysqli_real_escape_string($mysql_link, $_POST['summary']), 
			mysqli_real_escape_string($mysql_link, $_POST['full']), 
			mysqli_real_escape_string($mysql_link, $_POST['background']), 
			mysqli_real_escape_string($mysql_link, $_POST['comments']), 
			mysqli_real_escape_string($mysql_link, $_POST['processnotes']), 
			intval( $_POST['cid'] ),
			$TempDate, 
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
