<?php
	// Much of this code is old, and ought to be re-written...

	$api_errors = 1;

	function ErrRegister($G_DEBUG, $function) {
		if (!isset($G_DEBUG)) {
			$G_DEBUG = array();
		}
		array_push($G_DEBUG, $function);
		if ($G_DEBUG[0] >= 2) 
		{ echo "\n<pre><font color=\"#009900\">F: $function</font></pre>\n\n"; }
		return $G_DEBUG;
	}

	function ErrReport( $errmsg, $file, $line, $States ) {
	    echo "<table><tr><td bgcolor=\"#ff9999\">
		<h2>Error with: [<font color=\"#990000\">$errmsg</font>]</h2>\n";

		#if debugging 
		if ( $States[0] )
		{
			echo "<h3>on: [$file:$line]</h3>\n";
			echo "<pre>\n";

			if ( sizeof( $States ) > 1 )
			{ foreach ( $States as $info ) { echo "[$info]\n"; } }
			else { echo "Debugging statements empty\n"; }
	
			echo "</pre>\n";
		}
		echo "</td></tr></table>\n";
	}
?>
