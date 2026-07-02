<?php
	if (!isset($Mins)) {
		error_log(__FILE__. ' ' . __LINE__ . " unable to find Mins");
		exit;
	}

	echo $Mins->display('document');
?>
