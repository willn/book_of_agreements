<!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<title><?= $title; ?></title>
	<link rel="stylesheet" href="display/styles/default.css" type="text/css">
	<link rel="stylesheet" href="display/styles/print.css" type="text/css" media="print">

<?php
	foreach($stylesheets as $s) {
		echo <<<EOHTML
	<link rel="stylesheet" href="display/styles/{$s}" type="text/css">
EOHTML;
	}

	if ($use_jquery) {
		echo <<<EOHTML
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.5.1.min.js"></script>
EOHTML;
	}

	if (!is_null($js_code)) {
		echo <<<EOHTML
	<script type="text/javascript">{$js_code}</script>
EOHTML;
	}

	if (!is_null($jquery_code)) {
		echo <<<EOHTML
	<script type="text/javascript">
		$(document).ready(function() {
			{$jquery_code}
		});
	</script>
EOHTML;
	}

	if (!empty($js_files)) {
		$file_src = '';
		foreach($js_files as $f) {
			echo <<<EOHTML
	<script type="text/javascript" src="/{$f}"></script>
EOHTML;
		}
	}
?>

	<meta name="google-site-verification" content="g6Fg9AWOfsIvEGzT682MCUKNkYNRDVSH1bnmor4VEzU"/>
</head>
