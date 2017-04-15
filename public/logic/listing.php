<?php
	$Items = array( );
	$Item_Dates = array( );
	$label = '';
	$limit = '';
	if ( isset( $limit_time ))
	{ $limit = " and date_sub( curdate( ), interval $limit_time ) <= date "; }

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

	$link = "?id=$id";
	if ( $id == 'committee' ) {
		if ( intval( $cmty )) { $link = "?id=committee&cmty=$cmty"; }
		if ( intval( $sub_cmty_num )) { $link .= "&sub=$sub_cmty_num"; }
	}

	$show_agreements = '<a href="' . $link . '&only=agreements">show agreements</a>';
	$show_minutes = '<a href="' . $link . '&only=minutes">show minutes</a>';
	$show_both = '<a href="' . $link . '">show both</a>';

	switch( $only )
	{
		case 'agreements': $show_agreements = 'show agreements';
			break;
		case 'minutes': $show_minutes = 'show minutes';
			break;
		default: $show_both = 'show both';
			break;
	}

	echo <<<EOHTML
		<h1>{$title} {$label}</h1>
		<div id="selectors">
			<span id="bothselector">
				<img class="tango"
					src="display/images/tango/32x32/status/folder-open.png">
				{$show_both}
			</span>
			<span id="agrmselector">
				<img class="tango"
					src="display/images/tango/32x32/mimetypes/application-certificate.png">
				{$show_agreements}
			</span>
			<span id="minselector">
				<img class="tango"
					src="display/images/tango/32x32/mimetypes/text-x-generic.png">
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
		$Info = my_getInfo( $G_DEBUG, $HDUP, $sql );

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
						0, $sub_summary_length ) . '...' );
				}

				$cmty_name = $Cmty->getName();

				$Items[] = <<<EOHTML
					<div class="minutes">
						<h2 class="mins">
							<a href="?id=minutes&num={$Minutes['m_id']}">
							{$Minutes['date']} {$cmty_name}</a> minutes
						</h2>
						<div class="item_topic">
							<img class="topic_img tango"
								src="display/images/tango/32x32/mimetypes/text-x-generic.png">
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
			'agreements.surpassed_by, agreements.expired, ' .
			"substr( agreements.full, 1, $sub_summary_length) as partial, " .
			'committees.cmty from agreements, committees ' .
			"where agreements.cid=committees.cid and " . 
			"agreements.expired = 0 $clause order by agreements.date desc";
		if ( $max > 0 ) { $sql .= " limit $max"; }
		$Info = my_getInfo( $G_DEBUG, $HDUP, $sql );

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
					<div class="agreement">
						<h2 class="agrm">{$Agreement['date']} 
							<a href="?id=agreement&num={$Agreement['id']}">{$agr_title}</a>{$cmty_name}
						</h2>
						<div class="item_topic">
							<img class="topic_img tango"
								src="display/images/tango/32x32/mimetypes/application-certificate.png">
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
