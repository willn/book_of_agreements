<?php
	if ( $PUBLIC_USER ) {
		echo <<<EOHTML
			<div class="return_link">
				<img src="display/images/tango/32x32/actions/go-previous.png"
					class="tango" alt="previous">
				<a href="?id=agreement">Back to listing</a>
			</div>
EOHTML;
	}
	$Agrms->display('document');
?>
