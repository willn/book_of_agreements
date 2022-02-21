<!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Security-Policy" content="default-src 'self'">
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
?>

	<script src="js/utils.js"></script>
	<meta name="google-site-verification" content="g6Fg9AWOfsIvEGzT682MCUKNkYNRDVSH1bnmor4VEzU"/>
</head>
