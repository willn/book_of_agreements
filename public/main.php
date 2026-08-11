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

// get links array info
require_once( "logic/links/main_lk.php" );

$page_id  = getPageId($_GET);
$pvar = "logic/pagevars/{$page_id}_v.php";

if (file_exists($pvar)) {
	require_once($pvar);
}

$temploc = "display/templates/$template";
require_once($temploc);
?>
