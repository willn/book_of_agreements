<?php
	$body = 'logic/recent_listing.php';

	if (!is_authenticated()) {
		$body = 'logic/list_all_agreements.php';
	}
?>
