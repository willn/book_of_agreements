<?php
	if ( $PUBLIC_USER ) {
		echo <<<EOHTML
			<div class="return_link">
				<a href="?id=agreement">Back to listing</a>
			</div>
EOHTML;
	}
	$Agrms->display('document');
?>
