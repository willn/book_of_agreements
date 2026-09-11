<div class="minutes">
<h1 class="minutes_doc">All Minutes</h1>

<div class="info">
<?php
	$Cmty = new Committee();

	$sort = '';
    if (isset($_GET['sort']) && 
		(($_GET['sort'] == 'date') || ($_GET['sort'] == 'committee'))) {
		$sort = $_GET['sort'];
	}

	require_once __DIR__ . '/utils.php';

	$sql = 'select minutes.m_id, minutes.cid, minutes.notes, ' .
		'minutes.date, committees.cmty, committees.parent ' .
		'from minutes, committees where committees.cid=minutes.cid ';

	if ($sort == 'date') {
		$sql .= 'order by minutes.date desc, minutes.m_id desc';
	}
	elseif ($sort == 'committee') {
		$sql .= 'order by committees.parent asc, minutes.cid asc, ' .
			'minutes.cid asc';
	}
	else {
		$sql .= 'order by minutes.cid asc, minutes.date desc';
	}

	$mysql_api = get_mysql_api();
	$All = $mysql_api->get($sql);

	if ( !sizeof( $All ))
	{ echo '<p class="highlight">No minutes found.</p>' . "\n"; }
	else
	{
		if ( sizeof( $All ))
		{
			echo <<<EOHTML
			<table class="listing">
			<tr>
				<th><a href="?id=minutes&sort=committee">Committee</a></th>
				<th><a href="?id=minutes&sort=date">Date</a></th>
				<th>Special Notes</th>
			</tr>
EOHTML;

			foreach( $All as $num=>$Item )
			{
				$Cmty->setId($Item['cid']);
				$name = $Cmty->getName();

				$notes = stripslashes( $Item['notes'] );
				echo <<<EOHTML
					<tr>
						<td>{$name}</td>
						<td class="date"><a href="?id=minutes&num={$Item['m_id']}">{$Item['date']}</a></td>
						<td>{$notes}</td>
					</tr>
EOHTML;
			}
			echo '</table>';
		}
	}
?>
</div>
</div>

<p>As of: <?php echo date( 'r' ); ?></p>
