<?php
	if (!is_authenticated()) {
		echo <<<EOHTML
			<div class="return_link">
				<a href="?id=agreement">Back to listing</a>
			</div>
EOHTML;
	}

	$Agrms = new Agreement();
	echo $Agrms->renderDocumentDisplay();
?>
