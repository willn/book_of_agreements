<?php include dirname(__DIR__) . '/includes/head.php'; ?>
<body>

<nav>
<?php
if (is_authenticated()) {
	// show search
	echo <<<EOHTML
<div class="menu">
	<form id="search" method="get" action="?id=search">
		<input type="hidden" name="id" value="search">
		<input type="search" class="search_input" name="q" placeholder="search">
		<a href="?id=search">Advanced Search</a>
	</form>
</div>
EOHTML;
}

include dirname(__DIR__) . '/includes/nav.php';
?>
</nav>

<div id="banner">
<img src="display/images/gocohologo.gif" alt="Great Oak Cohousing Book of Agreements" width="400" height="72">

<?php
$login_logout = <<<EOHTML
<div id="login">
	<span class="username">public view</span>
	<a href="?id=login" class="button_link">Member Login</a>
</div>
EOHTML;

if (is_authenticated()) {
	$username = getWordParam($_SESSION, 'boa_username');
	$welcome = empty($username) ? '' : 'Welcome ';
	$login_logout = <<<EOHTML
<div id="logout">
	<span class="username">{$welcome}{$username}</span>
	<a href="?id=logout" class="button_link">➜] Logout</a>
</div>
EOHTML;
}

echo $login_logout;
?>

</div>
<div id="content">
<?php
if (is_authenticated() && ($_SESSION['boa_username'] == 'admin')) {
	echo <<<EOHTML
	<div class="admin_actions">
		<a href="?id=admin&amp;doctype=agreement" class="button_link">➕ new agreement</a>
		<a href="?id=admin&amp;doctype=minutes" class="button_link">➕ new minutes</a>
	</div>
EOHTML;
}

if (isset($body)) {
	include( $body );
}
?>
</div>

</body>
</html>
