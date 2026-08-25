<div class="menu">
<?php
	if (isset($id) && ($id == 'agreement') && (!isset($num))) {
		echo '<div class="link">All Agreements</div>'."\n";
	}
	else {
		echo '<div><a href="?id=agreement">All Agreements</a></div>'."\n";
	}

	if (is_authz_for_minutes()) {
		if (isset($id) && ($id == 'minutes') && (!isset($num))) {
			echo '<div class="link">All Minutes</div>'."\n";
		}
		else {
			echo '<div><a href="?id=minutes">All Minutes</a></div>'."\n";
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

	foreach ( $Cmtys as $link=>$name )
	{
		#current
		if (isset($cmty) && ($cmty == $link) && isset($sub) && empty($sub) &&
			isset($id) && ($id == 'committee')) {
			echo '<div class="link">'.$name.
				'&nbsp;<div class="linkcount">' . #$CmtyCount[$link] .
				"</div></div>\n";
		}
		else {
			echo <<<EOHTML
			<div><a href="?id=search&cmty={$link}&show_docs=agreements">{$name}</a></div>
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
						<div class="link">&nbsp; &nbsp; &middot; {$subname}&nbsp;</div>
EOHTML;
				}
				else
				{
					$link_content = <<<EOHTML
						<div><a href="?id=search&cmty={$sublink}&show_docs=agreements">{$subname}</a></div>
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

	foreach ($tags as $id=>$name)
	{
		echo <<<EOHTML
		<div><a href="?id=search&tags={$name}">{$name}</a></div>
EOHTML;
	}
?>
</div>

