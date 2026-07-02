<?php include dirname(__DIR__) . '/includes/head.php'; ?>
<body>

<div id="banner">
<img src="display/images/gocohologo.gif" alt="<?= $title ?? '' ?>"
width="400" height="72">

<?php
	# if user is logged in, then display advanced search
	if (isset($PUBLIC_USER) && (!$PUBLIC_USER)) {
		$terms = !isset($search_terms) ? '' :
			str_replace( '"', '&quot;', $search_terms );
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
<?php include dirname(__DIR__) . '/includes/nav.php'; ?>
</div>

<div id="content">
<?php
if (isset($body)) {
	include( $body );
}
?>
</div>

</body>
</html>
