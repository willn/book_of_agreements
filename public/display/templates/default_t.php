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
		&nbsp; <a href="?id=search">advanced search</a>
	</form>
	<div id="logout">
		<a href="?login=0">Change to Public View</a>
	</div>
EOHTML;
}
else {
	echo <<<EOHTML
	<div id="login">
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
			<a href="?id=logout">logout</a>
			<a href="?id=admin&amp;doctype=agreement">new agreement</a>
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
