<?php
	global $G_DEBUG;
	$G_DEBUG = array(0);

	$api_loc = 'logic/php_api/';
	require_once( $api_loc.'database/mysql_connex.php' );

	// if lacking config, create it
	if (!file_exists('config.php')) {
		require_once('logic/config_management.php');
		create_config_file();
	}

	$PUBLIC_USER = false;
	if (!$authenticated) {
		$PUBLIC_USER = true;
	}

	$use_jquery = FALSE;
	$js_code = NULL;
	$jquery_code = NULL;
	$js_files = array();

	require_once( 'logic/lib_boa.php' );

	$template = 'default_t.php';
	$js = '';
	$stylesheets = array();

	$MainNav = array();
	$Cmtys = array();
	$SubCmtys = array();
	$id = '';
	$cmty = '';
	$sub = '';
	$search_terms = '';
	$sub_summary_length = 150;

	#-------[ over-write defaults with page-specific variables ]---------
	$title = 'Great Oak Book of Agreements';
	#grab page id value
	$id = 'home';
	if ( isset( $_GET['id'] )) {
		preg_match( '/^(\w+)$/', $_GET['id'], $Match );
		if (!empty($Match[1])) {
			$id = $Match[1];
		}
	}

	#--- user must login before using the admin tool
	if ( $id == 'admin' ) {
		require_once( 'logic/admin/authentication.php' );
	}

	if ( isset( $_GET['cmty'] )) {
		preg_match( '/^(\w+)$/', $_GET['cmty'], $Match );
		$cmty = $Match[1];
	}

	if ( isset( $_GET['sub'] )) {
		preg_match( '/^(\w+)$/', $_GET['sub'], $Match );
		$sub = $Match[1];
	}

	if ( isset( $_GET['num'] )) {
		$num = intval( $_GET['num'] );
	}

	#-- get links array info
	require_once( "logic/links/main_lk.php" );

	if ( $PUBLIC_USER && $id != 'agreement' && $id != 'login' ) {
		$id = 'agreement';
	}

	$pvar = 'logic/pagevars/'.$id.'_v.php';
	if ( file_exists( $pvar )) {
		require_once($pvar);
	}
	elseif ( $PUBLIC_USER ) {
		# if this is a public user, then punt instead of 404
		punt_public_user();
	}
	else {
		require_once('logic/pagevars/errors_404_v.php');
	}

	$temploc = "display/templates/$template";
	require_once($temploc);
?>
