<?php
	$body = 'logic/search.php';

	require_once('logic/lib_boa.php');

	$Cmty = new Committee( );
	$Info = array( );
	$sql_a = '';
	$sql_m = '';
	$Found = array( );
	$Ignored = array( );
	$dropped = '';
	$ft_match_agr = 'match( title, summary, full, background, comments, processnotes )';
	$ft_match_min = 'match( notes, agenda, content )';
	$SQL_Agr_Clauses = array();
	$SQL_Min_Clauses = array();

	$docs_allowed = array('agreements', 'minutes', 'all');
	$show_docs = (isset($_GET['show_docs']) && in_array($_GET['show_docs'], $docs_allowed)) ?
		$_GET['show_docs'] : NULL;

	if (isset($_GET['include_expired_surpassed']) &&
		('on' == $_GET['include_expired_surpassed'])) {
		$include_expired_surpassed = TRUE;
	}
	else {
		$SQL_Agr_Clauses[] = 'expired=0 and surpassed_by=0';
		$include_expired_surpassed = FALSE;
	}

	if (!empty($_GET['q'])) {
		$q = $_GET['q'];
		$search_terms = htmlentities($q);
	}
	$cmty_num = isset($_GET['cmty']) ? intval($_GET['cmty']) : 0;

	#----------- begin dates ---------
	$start_year = isset($_GET['startyear']) ? intval($_GET['startyear']) : NULL;
	$start_month = isset($_GET['startmonth']) ? intval($_GET['startmonth']) : NULL;
	$start_day = isset($_GET['startday']) ? intval($_GET['startday']) : NULL;

	if (!is_null($start_year) && !is_null($start_month) && !is_null($start_day)) {
		$Start_Date = new MyDate($start_year, $start_month, $start_day, 'start');
		$SQL_Agr_Clauses[] = 'date>="' . $Start_Date->toString() . '"';
		$SQL_Min_Clauses[] = 'date>="' . $Start_Date->toString() . '"';
	}
	else {
		$Start_Date = new MyDate(2001, 1, 1, 'start');
	}

	$end_year = isset($_GET['endyear']) ? intval($_GET['endyear']) : NULL;
	$end_month = isset($_GET['endmonth']) ? intval($_GET['endmonth']) : NULL;
	$end_day = isset($_GET['endday']) ? intval($_GET['endday']) : NULL;

	if (!is_null($end_year) && !is_null($end_month) && !is_null($end_day)) {
		$End_Date = new MyDate($end_year, $end_month, $end_day, 'end');
		$SQL_Agr_Clauses[] = 'date<="' . $End_Date->toString() . '"';
		$SQL_Min_Clauses[] = 'date<="' . $End_Date->toString() . '"';
	}
	else {
		$End_Date = new MyDate(NULL, NULL, NULL, 'end');
	}
	#----------- finish dates ---------

	$ft_against = '';
	if ( !empty( $q )) {
		$q = strtolower(trim($q));
		if ( !get_magic_quotes_gpc( )) {
			$q = addslashes( $q );
		}
		$ft_against = "against( '{$q}' )"; 
	}

	// committees
	if (0 != $cmty_num) {
		$SQL_Agr_Clauses[] = "cid='{$cmty_num}'";
		$SQL_Min_Clauses[] = "cid='{$cmty_num}'";
	}

	$agr_sql_clauses = '';
	if (!empty($SQL_Agr_Clauses)) {
		$agr_sql_clauses = implode(' and ', $SQL_Agr_Clauses);
	}

	$min_sql_clauses = '';
	if (!empty($SQL_Min_Clauses)) {
		$min_sql_clauses = implode(' and ', $SQL_Min_Clauses);
	}

	if ('' != $ft_against) {
		if ('' != $agr_sql_clauses) {
			// prepend "and"
			$agr_sql_clauses = 'and ' . $agr_sql_clauses;
		}

		if ('' != $min_sql_clauses) {
			// prepend "and"
			$min_sql_clauses = 'and ' . $min_sql_clauses;
		}

		$sql_a = <<<EOSQL
			SELECT id, {$ft_match_agr} {$ft_against} as score from
				agreements where {$ft_match_agr} {$ft_against}
				{$agr_sql_clauses} order by score desc
EOSQL;

		$sql_m = <<<EOSQL
			select m_id, {$ft_match_min} {$ft_against} as score from
				minutes where {$ft_match_min} {$ft_against}
				{$min_sql_clauses} order by score desc
EOSQL;
	}
	else {
		$sql_a = "select id from agreements where {$agr_sql_clauses} order by date asc";
		$sql_m = "select m_id from minutes where {$min_sql_clauses} order by date asc";
	}

	/* #!# XXX stuff to get better:
		- refine search results, meaning don't look for union of words,
		  look for intersection
		- be able to easily dismiss (reset) start or end dates (js?)
		- isolate searches to only minutes or only agreements (UI work
		  done)
	*/


	// search for agreements
	if ('minutes' != $show_docs) {
		$Info = my_getInfo( $G_DEBUG, $HDUP, $sql_a, '', 'id' );
		$ak = array_keys($Info);
		foreach( array_keys($Info) as $id) {
			$agr = new Agreement();
			$agr->setId($id);
			$agr->loadById();
			$Found[] = $agr;
		}
	}

	if (('agreements' != $show_docs) && !is_null($show_docs)) {
		// search for minutes
		$Info = my_getInfo( $G_DEBUG, $HDUP, $sql_m, '', 'm_id' );
		foreach( array_keys($Info) as $id) {
			$Found[] = new Minutes( $id );
		}
	}

	#!# need to zipper together the agreements and minutes so that most
	# relevant docs bubble up...

	if ( !empty( $Ignored )) {
		$dropped = '<br>Some common words were ignored: [' .
			implode( ', ', $Ignored ) . ']';
	}
	$num_matches = count( $Found );

	// merge together committees and sub-committees
	$AllCmtys = array();
	foreach($Cmtys as $num=>$cm) {
		if (is_string($cm)) {
			$AllCmtys[$num] = $cm;
		}

		if (isset($SubCmtys[$num])) {
			foreach($SubCmtys[$num] as $subnum=>$subname) {
				$AllCmtys[$subnum] = $cm . ': ' . $subname;
			}
		}
	}
?>
