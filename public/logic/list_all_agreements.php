<?php
	require_once('logic/utils.php');

	$pub = '';
	$access_img = 'apps/system-users.png';
	$h1_class = 'agrm';
	$note = '';
	if ($PUBLIC_USER) {
		$pub = 'Public ';
		$access_img = 'apps/internet-web-browser.png';
		$h1_class = 'public';

		$note = <<<EOHTML
		<div class="explanation">
		<p>
		<a href="http://www.gocoho.org/">The Great Oak
		Cohousing Association</a> Book of Agreements is
		our collection of what others may describe as their
		"condominium documents". They are an extension of
		our <a href="?id=agreement&amp;num=120">Master Deed</a>
		and <a href="?id=agreement&amp;num=70">Bylaws</a>. As
		described in our Bylaws, we use a consensus
		decision making process, while using our <a
		href="?id=agreement&amp;num=127">vision
		statement</a> for guidance. Our <a
		href="http://www.dleg.state.mi.us/bcs_corp/results.asp?ID=776590&page_name=corp">state
		incorporation papers and subsequent updates</a>
		are also available on the State of Michigan's website.
		</p>

		<p>
		Below is a select list of documents we have chosen
		to share with the public. We hope this may help other
		communities, or educate prospective
		members. Also, this web application software is <a
		href="https://github.com/willn/book_of_agreements">available
		for download</a> under an open source license.
		</p>
		</div>
EOHTML;
	}

	echo <<<EOHTML
<div class="agreement">
<h1 class="{$h1_class}">
	<img class="tango" src="display/images/tango/32x32/{$access_img}"
		alt="agreements access icon">
	All {$pub}Agreements
</h1>

<div class="info">
{$note}
EOHTML;


	$show_link = '';
	$conditions = '';
	if ( $show == 'expired' ) {
		$conditions = 'and agreements.expired=1 ';
		$show_exp_msg = '<p><a href="?id=agreement">Show active agreements</a></p>';
		$show_link = '&amp;show=expired';
	}
	else {
		$conditions = 'and agreements.expired=0 ';
		$show_exp_msg = <<<EOHTML
			<p>
				Show <a href="?id=agreement&amp;show=expired">expired</a>
				agreements</a>
			</p>
EOHTML;
	}

	$show_exp_msg = '';
	if ( !$PUBLIC_USER ) {
		echo $show_exp_msg;
	}

	$order = ( $sort == 'committee' ) ?
		'order by committees.parent asc, agreements.cid asc' :
		'order by agreements.date desc, agreements.id desc';

	$pub_constrain = '';
	if ( $PUBLIC_USER ) {
		$pub_constrain = 'and agreements.world_public=1';
	}

	$sql = <<<EOSQL
		select agreements.id, agreements.cid, agreements.title,
			agreements.date, committees.cmty, committees.parent,
			agreements.summary
		from agreements, committees where committees.cid=agreements.cid
		{$pub_constrain}
		{$conditions}
		{$order}
EOSQL;
	$mysql_api = get_mysql_api();
	$All = $mysql_api->get($sql );

	if ( !sizeof( $All )) {
		echo '<p class="highlight">No passed agreements found.</p>' . "\n";
	}
	else {
		if ( sizeof( $All )) {
			echo <<<EOHTML
				<table cellpadding="7" cellspacing="0" border="0"
				summary="table containing list of public agreements">
				<tr>
					<th><a href="?id=agreement&amp;sort=committee{$show_link}">Committee</a></th>
					<th><a href="?id=agreement&amp;sort=date{$show_link}">Date</a></th>
					<th><a href="?id=agreement&amp;sort=agreement{$show_link}">sort by Agreement ID</a></th>
					<th>Summary</th>
				</tr>
EOHTML;
				
			$even_row = false;
			foreach( $All as $num=>$Item ) {
				$Cmty->setId($Item['cid']);
				$name = $Cmty->getName();
				$title = stripslashes( $Item['title'] );
				$summary = stripslashes( $Item['summary'] );
				$bgcolor = ($even_row) ? ' bgcolor="#eeeeee"' : '';

				echo <<<EOHTML
					<tr{$bgcolor}>
						<td valign="top">{$name}</td>
						<td valign="top" class="nowrap">{$Item['date']}</td>
						<td valign="top"><a href="?id=agreement&amp;num={$Item['id']}">{$title}</a></td>
						<td valign="top">{$summary}</td>
					</tr>
EOHTML;
				$even_row = !$even_row;
				
			}
			echo '</table>';
		}
	}
?>
</div>
</div>

<p>As of: <?php echo date( 'r' ); ?></p>
