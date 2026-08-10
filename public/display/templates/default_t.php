<?php include dirname(__DIR__) . '/includes/head.php'; ?>
<body>

<div id="banner">
<img src="display/images/gocohologo.gif" alt="Great Oak Cohousing Book of Agreements" width="400" height="72">

<?php
$login_logout = <<<EOHTML
<div id="login">
	<span class="username">public view</span>
	<a href="?id=login">Member Login</a>
</div>
EOHTML;

if (is_authenticated()) {
	// show advanced search
	$terms = !isset($search_terms) ? '' :
		str_replace( '"', '&quot;', $search_terms );
	echo <<<EOHTML
<form id="search" method="get" action="?id=search">
	<input type="hidden" name="id" value="search">
	<input type="search" id="search_input" maxlength="70" size="30" name="q" value="{$terms}">
	<input type="submit" value="search">
	&nbsp; <a href="?id=search">advanced search</a>
</form>
EOHTML;

	$username = getWordParam($_SESSION, 'boa_username');
	$welcome = empty($username) ? '' : 'Welcome ';
	$login_logout = <<<EOHTML
<div id="logout">
	<span class="username">{$welcome}{$username}</span>
	<a href="?id=logout">Logout</a>
</div>
EOHTML;
}

echo $login_logout;

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
