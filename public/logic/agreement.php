<?php
	if (isset($PUBLIC_USER) && $PUBLIC_USER) {
		echo <<<EOHTML
			<div class="return_link">
				<a href="?id=agreement">Back to listing</a>
			</div>
EOHTML;
	}

	if (isset($Agrms)) {
		echo $Agrms->renderDocumentDisplay();
	}
?>
