<?php include ('display/includes/head.php'); ?>
<body>

<div id="banner">
<img src="display/images/gocohologo.gif" alt="<?= $title; ?>"
width="400" height="72">

<?php
	if ( !$PUBLIC_USER ) {
		$terms = str_replace( '"', '&quot;', $search_terms );
		echo <<<EOHTML
	<form id="search" method="get" action="?id=search">
		<input type="hidden" name="id" value="search">
		<input type="search" id="search_input" maxlength="70" size="30" name="q" value="{$terms}">
		<input type="submit" value="search">
		&nbsp; <a href="?id=search">advanced search</a> &nbsp;
		<a href="http://www.gocoho.org/wiki/index.php/BookofAgreements">request a feature or change</a>
	</form>
	<div id="logout">
		<img class="tango" src="display/images/tango/32x32/apps/internet-web-browser.png" alt="web browser icon">
		<a href="?login=0">Change to Public View</a>
	</div>
EOHTML;
}
else {
	echo <<<EOHTML
	<div id="login">
		<img class="tango" src="display/images/tango/32x32/emblems/emblem-readonly.png" alt="lock icon">
		<a href="?login=1">Member Login</a>
	</div>
EOHTML;
}
?>

</div>


<?php
	if ( isset( $_SESSION['admin'] ) && ( $_SESSION['admin'] ))
	{
		echo <<<EOHTML
		<div class="admin_actions">
			<img class="tango" src="display/images/tango/32x32/actions/system-log-out.png" alt="logout">
			<a href="?id=logout">logout</a>
			<img class="tango" src="display/images/tango/32x32/mimetypes/application-certificate.png" alt="agreement">
			<a href="?id=admin&amp;doctype=agreement">new agreement</a>
			<img class="tango" src="display/images/tango/32x32/mimetypes/text-x-generic.png" alt="minutes">
			<a href="?id=admin&amp;doctype=minutes">new minutes</a>
		</div>
EOHTML;
}
?>

<div id="nav">
<?php include ('display/includes/nav.php'); ?>
</div>

<div id="content">
<?php include( $body ); ?>
</div>

</body>
</html>
