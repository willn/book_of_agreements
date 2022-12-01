<?php
	if ( !isset( $_GET['num'] )) {
		require( 'logic/pagevars/all_minutes_v.php' );
	}
	else {
		$num = intval( $_GET['num'] );
		$Date = new MyDate( ); 
		$Mins = new Minutes($num);
		$Cmty = new Committee($Mins->cid);
		$title = 'Minutes: ' . $Cmty->getName() . ' ' . 
			$Mins->Date->toString( ) . ' [Minutes]';

		$body = 'logic/minutes.php';
	}

?>
