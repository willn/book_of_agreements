<p class="menu">
<?php
	if ( $id != 'home' )	#special link home
	{ echo '<a href="?id=home">Recent</a><br>'."\n"; }
	else { echo '<span class="link">Recent</span><br>'."\n"; }

	if (( $id == 'agreement' ) && ( !isset( $num )))
	{ echo '<span class="link">All Agreements</span><br>'."\n"; }
	else { echo '<a href="?id=agreement">All Agreements</a><br>'."\n"; }

	if (( $id == 'minutes' ) && ( !isset( $num )))
	{ echo '<span class="link">All Minutes</span><br>'."\n"; }
	else { echo '<a href="?id=minutes">All Minutes</a><br>'."\n"; }
?>
</p>

<p class="menu">
<?php
	foreach ( $Cmtys as $link=>$name )
	{
		#current
		if (( $cmty == $link ) && empty( $sub ) && ( $id == 'committee' ))
		{
			echo '<span class="link">'.$name.
				'&nbsp;<span class="linkcount">' . #$CmtyCount[$link] .
				"</span></span><br>\n";
		}
		else
		{
			echo <<<EOHTML
			<a href="?id=committee&amp;cmty={$link}">{$name}</a><br>
EOHTML;
		}

		# create the sub-nav items
		if ( isset( $SubCmtys[$link] ))
		{
			foreach ( $SubCmtys[$link] as $sublink=>$subname )
			{
				$link_content = '';
				#current
				if ( $sub == $sublink )
				{
					$link_content = <<<EOHTML
						<span class="link">&nbsp; &nbsp; &middot; {$subname}&nbsp;</span><br>
EOHTML;
				}
				else
				{
					$link_content = <<<EOHTML
						<a href="?id=committee&amp;cmty={$link}&amp;sub={$sublink}">
						&nbsp; &nbsp; &middot; {$subname}</a><br>
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
