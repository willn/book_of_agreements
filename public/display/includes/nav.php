<p class="menu">
<?php
	if (isset($id) && ($id == 'agreement') && (!isset($num))) {
		echo '<span class="link">All Agreements</span><br>'."\n";
	}
	else {
		echo '<a href="?id=agreement">All Agreements</a><br>'."\n";
	}

	if (is_authz_for_minutes()) {
		if (isset($id) && ($id == 'minutes') && (!isset($num))) {
			echo '<span class="link">All Minutes</span><br>'."\n";
		}
		else {
			echo '<a href="?id=minutes">All Minutes</a><br>'."\n";
		}
	}
?>
</p>

<p class="menu">
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
			echo '<span class="link">'.$name.
				'&nbsp;<span class="linkcount">' . #$CmtyCount[$link] .
				"</span></span><br>\n";
		}
		else {
			echo <<<EOHTML
			<a href="?id=search&cmty={$link}&show_docs=agreements">{$name}</a><br>
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
						<span class="link">&nbsp; &nbsp; &middot; {$subname}&nbsp;</span><br>
EOHTML;
				}
				else
				{
					$link_content = <<<EOHTML
						<a href="?id=search&cmty={$sublink}&show_docs=agreements">{$subname}</a><br>
EOHTML;
				}
				echo <<<EOHTML
				<span class="sublink">{$link_content}</span>
EOHTML;
			}
		}
	}
?>
</p>
