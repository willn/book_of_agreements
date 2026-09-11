<div class="menu">
<?php
	if (isset($id) && ($id == 'agreement') && (!isset($num))) {
		echo '<div class="current_link">All Agreements</div>'."\n";
	}
	else {
		echo '<a href="?id=agreement">All Agreements</a>'."\n";
	}

	if (is_authz_for_minutes()) {
		if (isset($id) && ($id == 'minutes') && (!isset($num))) {
			echo '<div class="current_link">All Minutes</div>'."\n";
		}
		else {
			echo '<a href="?id=minutes">All Minutes</a>'."\n";
		}
	}
?>
</div>


<div class="menu">
	<h2>Committees</h2>
<?php
	if (!isset($Cmtys)) {
		error_log(__FILE__ . " Cmtys is not set");	
		exit;
	}

	$current_cmty = $_GET['cmty'] ?? '';

	foreach ( $Cmtys as $link=>$name )
	{
		#current
		if ($link == $current_cmty) {
			echo '<div class="current_link">'.$name.'</div>'."\n";
		}
		else {
			echo <<<EOHTML
			<a href="?id=search&cmty={$link}&show_docs=agreements">{$name}</a>
EOHTML;
		}

		# create the sub-nav items
		if ( isset( $SubCmtys[$link] ))
		{
			foreach ( $SubCmtys[$link] as $sublink=>$subname )
			{
				$link_content = '';
				#current
				if (isset($sub) && ($sub == $sublink)) {
					$link_content = <<<EOHTML
						<div class="current_link">&nbsp; &nbsp; &middot; {$subname}</div>
EOHTML;
				}
				else
				{
					$link_content = <<<EOHTML
						<a href="?id=search&cmty={$sublink}&show_docs=agreements">{$subname}</a>
EOHTML;
				}
				echo <<<EOHTML
				<div class="sublink">{$link_content}</div>
EOHTML;
			}
		}
	}
?>
</div>

<div class="menu">
	<h2>Tags</h2>
<?php
	$tags = get_all_tags();
	$current_tag = $_GET['tags'] ?? '';

	foreach ($tags as $id=>$name)
	{
		$class = ($name === $current_tag) ? ' class="current_link"' : '';
		echo <<<EOHTML
		<a href="?id=search&tags={$name}"{$class}>{$name}</a>
EOHTML;
	}
?>
</div>


