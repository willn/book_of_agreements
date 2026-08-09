<?php
require_once( 'logic/lib_boa.php' );

$template = 'default_t.php';
$js = '';
$stylesheets = [];
$MainNav = [];
$search_terms = '';

#-------[ over-write defaults with page-specific variables ]---------
$id = getWordParam($_GET, 'id', 'recent');
$cmty = getWordParam($_GET, 'cmty');
$sub = getWordParam($_GET, 'sub');
$num = isset($_GET['num']) ? intval($_GET['num']) : null;

#--- user must login before using the admin tool
if ( $id == 'admin' ) {
	require_once( 'logic/admin/authentication.php' );
}

// get links array info
require_once( "logic/links/main_lk.php" );

$PUBLIC_USER = !is_authenticated();
$id = getPageId($_GET, $PUBLIC_USER);

$pvar = 'logic/pagevars/'.$id.'_v.php';
if (file_exists($pvar)) {
	require_once($pvar);
}
elseif ($PUBLIC_USER) {
	attempt_login();
	# if this is a public user, then punt instead of 404
	# punt_public_user();
}
else {
	error_log(__FILE__ . ' ' . __LINE__ . " unable to find pagevar");
}

$temploc = "display/templates/$template";
require_once($temploc);
?>
