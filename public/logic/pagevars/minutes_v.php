<?php
	if ($_SESSION['boa_username'] === 'guest') {
		echo <<<EOHTML
<h2>Guest User Not Authorized</h2>
<p>Please return to the <a href="http://gocoho.org/boa/">main page</a>.</p>
EOHTML;
		exit;
	}

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
