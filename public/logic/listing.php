<?php
	# don't make this plural, since it's used in a SQL query
	$limit_time = '90 day';

	$title = 'Recently Active Items (' . $limit_time . 's)';
	$cmty_num = NULL;
	$only = NULL;
	$max = 100;

	if ( isset( $_GET['cmty'] )) {
		$cmty_num = intval( $_GET['cmty'] );
	}

	if ( isset( $_GET['only'] ))
	{
		$only = ( $_GET['only'] == 'agreements' ) ? 'agreements' : '';
		$only = ( $_GET['only'] == 'minutes' ) ? 'minutes' : $only;
	}

    if (!is_authenticated()) {
		$only = 'agreements';
	}

	$Items = array( );
	$Item_Dates = array( );
	$limit = " and date_sub( curdate( ), interval $limit_time ) <= date ";

	if ( isset( $sub_cmty_num ) && is_int( $sub_cmty_num ))
	{
		$Cmty = new Committee($sub_cmty_num);
		$title = $Cmty->getName();
	}
	elseif ( isset( $cmty_num ) && is_int( $cmty_num ))
	{
		$Cmty = new Committee($cmty_num);
		$title = $Cmty->getName();
	}

	$id = getPageId($_GET);
	$cmty = getWordParam($_GET, 'cmty');
	$link = "?id=$id";
	if ( $id == 'committee' ) {
		if ( intval( $cmty )) { $link = "?id=committee&cmty=$cmty"; }
		if ( intval( $sub_cmty_num )) { $link .= "&sub=$sub_cmty_num"; }
	}

	$show_agreements = '<a href="' . $link . '&only=agreements">show agreements</a>';
	$show_minutes = is_authz_for_minutes() ? 
		'<a href="' . $link . '&only=minutes">show minutes</a>' : '';
	$show_both = is_authz_for_minutes() ? 
		'<a href="' . $link . '">show both</a>' : '';

	switch( $only )
	{
		case 'agreements': $show_agreements = 'show agreements';
			break;
		case 'minutes':
			$show_minutes = is_authz_for_minutes() ? 'show minutes' : '';
			break;
		default:
			$show_both = is_authz_for_minutes() ? 'show both' : '';
			break;
	}

	echo <<<EOHTML
		<h1>{$title}</h1>
		<div id="selectors">
			<span id="bothselector">
				{$show_both}
			</span>
			<span id="agrmselector">
				{$show_agreements}
			</span>
			<span id="minselector">
				{$show_minutes}
			</span>
		</div>
EOHTML;

	#------------- minutes -------------------
	if ( $only != 'agreements' )
	{
		$clause = '';
		if ( isset( $sub_cmty_num ) && is_int( $sub_cmty_num ))
		{ $clause .= " and minutes.cid=$sub_cmty_num "; }
		elseif ( isset( $cmty_num ) && is_int( $cmty_num ))
		{ $clause .= " and minutes.cid=$cmty_num "; }
		$clause .= $limit;

		$sql = "select minutes.*, committees.cmty from minutes, committees 
			where minutes.cid=committees.cid $clause order by date desc";
		$mysql_api = get_mysql_api();
		$Info = $mysql_api->get($sql );

		if ( !count( $Info ))
		{ echo '<p class="highlight">No minutes found</p>'."\n"; }
		else
		{
			foreach ( $Info as $i=>$Minutes )
			{
				$Cmty = new Committee( $Minutes['cid'] );

				$summary = '';
				if ( !empty( $Minutes['notes'] ))
				{
					$summary = '<div class="special">' . 
						nl2br( $Minutes['notes'] ) . "</div>\n";
				}
				if ( !empty( $Minutes['agenda'] ))
				{ $summary .= format_html( $Minutes['agenda'] ); }
				if ( empty( $summary ))
				{
					$summary = format_html( substr( $Minutes['content'], 
						0, SUB_SUMMARY_LENGTH ) . '...' );
				}

				$cmty_name = $Cmty->getName();

				$Items[] = <<<EOHTML
					<div class="minutes_entry">
						<h2 class="mins">
							<a href="?id=minutes&num={$Minutes['m_id']}">
							{$Minutes['date']} {$cmty_name}</a> minutes
						</h2>
						<div class="item_topic">
							<div class="info">{$summary}</div>
						</div>
					</div>
EOHTML;
				$Item_Dates[ count( $Items )-1 ] = $Minutes['date'];
			}
		}
	}

	#------------- agreements -------------------
	if ( $only != 'minutes' )
	{
		$clause = '';
		if ( isset( $sub_cmty_num ) && is_int( $sub_cmty_num ))
		{ $clause = " and agreements.cid=$sub_cmty_num "; }
		elseif ( isset( $cmty_num ) && is_int( $cmty_num ))
		{ $clause = " and agreements.cid=$cmty_num "; }
		$clause .= $limit;

		$sql = 'select agreements.id, agreements.date, ' .
			'agreements.title, agreements.summary, agreements.cid, ' .
			'agreements.expired, ' .
			"substr( agreements.full, 1, ' . SUB_SUMMARY_LENGTH . ') as partial, " .
			'committees.cmty from agreements, committees ' .
			"where agreements.cid=committees.cid and " . 
			"agreements.expired = 0 $clause order by agreements.date desc";
		if ( $max > 0 ) { $sql .= " limit $max"; }

		$mysql_api = get_mysql_api();
		$Info = $mysql_api->get($sql );

		if ( !count( $Info ))
		{ echo '<p class="highlight">No agreements found</p>'."\n"; }
		else
		{
			foreach ( $Info as $i=>$Agreement )
			{
				$Cmty = new Committee( $Agreement['cid'] );

				$short_version = nl2br( stripslashes( $Agreement['summary'] ));
				if ( empty( $short_version ))
				{ $short_version = $Agreement['partial']; }

				$cmty_name = '';
				if ( !isset( $cmty_num ))
				{ $cmty_name = ' &nbsp; [' . $Cmty->getName() . ']'; }

				$agr_title = nl2br( stripslashes( $Agreement['title'] ));
				$Items[] = <<<EOHTML
					<div class="agreement_entry">
						<h2 class="agrm">{$Agreement['date']} 
							<a href="?id=agreement&num={$Agreement['id']}">{$agr_title}</a>{$cmty_name}
						</h2>
						<div class="item_topic">
							<div class="info">{$short_version}</div>
						</div>
					</div>
EOHTML;
				$Item_Dates[ count( $Items )-1 ] = $Agreement['date'];
			}
		}
	}

	arsort( $Item_Dates );
	foreach( $Item_Dates as $i=>$text ) { echo $Items[$i]; }

?>
