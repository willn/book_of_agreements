<div class="minutes">
<h1 class="mins">
	<img class="tango" src="display/images/tango/32x32/apps/system-users.png">
	All Minutes
</h1>

<div class="info">
<?php
	$sql = 'select minutes.m_id, minutes.cid, minutes.notes, ' .
		'minutes.date, committees.cmty, committees.parent ' .
		'from minutes, committees where committees.cid=minutes.cid ';

	if ( $sort == 'date' )
	{ $sql .= 'order by minutes.date desc, minutes.m_id desc'; }
	elseif ( $sort == 'committee' )
	{
		$sql .= 'order by committees.parent asc, minutes.cid asc, ' .
			'minutes.cid asc';
	}
	else { $sql .= 'order by minutes.cid asc, minutes.date desc'; }

	$All = my_getInfo( $G_DEBUG, $HDUP, $sql );

	if ( !sizeof( $All ))
	{ echo '<p class="highlight">No minutes found.</p>' . "\n"; }
	else
	{
		if ( sizeof( $All ))
		{
			echo <<<EOHTML
			<table cellpadding="7" cellspacing="0">
			<tr>
				<td><a href="?id=minutes&sort=committee">Committee</a></td>
				<td><a href="?id=minutes&sort=date">Date</a></td>
				<td>Special Notes</td>
			</tr>
EOHTML;

			$even_row = false;
			foreach( $All as $num=>$Item )
			{
				$Cmty->setId($Item['cid']);
				$name = $Cmty->getName();
				$notes = stripslashes( $Item['notes'] );
				$bgcolor = ($even_row) ? ' bgcolor="#eeeeee"' : '';

				echo <<<EOHTML
					<tr{$bgcolor}>
						<td>{$name}</td>
						<td><a href="?id=minutes&num={$Item['m_id']}">
							{$Item['date']}</a></td>
						<td>{$notes}</td>
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
