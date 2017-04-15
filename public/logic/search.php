<?php
	$com_options = '<option value="0">All</option>';
	foreach($AllCmtys as $cid=>$name) {
		$selected = ($cid == $cmty) ? ' selected' : '';
		$com_options .= "<option value=\"{$cid}\"{$selected}>{$name}</option>\n";
	}

	$start = $Start_Date->selectDate();
	$end = $End_Date->selectDate();

	// the default type
	if (is_null($show_docs)) {
		$show_docs = 'agreements';
	}
	$document_types = '';
	foreach($docs_allowed as $d) {
		$checked = ($d == $show_docs) ? ' checked' : '';
		$document_types .= <<<EOHTML
			<label>
				<input type="radio" name="show_docs" value="{$d}" {$checked}> {$d}
			</label>
EOHTML;
	}

	$exp_surp_checked = ($include_expired_surpassed) ? ' checked="checked"' : '';

	$search_terms_display = ('' != $search_terms) ? 
		'query: [<b>' . $search_terms . '</b>]' : '';

	echo <<<EOHTML
		<h1>search</h1>
		<div id="search_query">{$search_terms_display}
			number of results: {$num_matches} {$dropped}

			<div id="advanced_options">
				<h3>Advanced Search Options</h3>
				<form name="advanced_search" method="get" action="?id=search">
					<input type="hidden" name="id" value="search"/>
					<p><input type="search" name="q" value="{$search_terms}" size="50"/></p>
					<p>Committee:&nbsp;<select name="cmty">{$com_options}</select></p>
					{$start}
					{$end}
					<p>{$document_types}</p>
					<p>
						Include expired and surpassed documents: 
						<input type="checkbox" name="include_expired_surpassed"{$exp_surp_checked}>
					</p>

					<p><input type="submit" value="search" style="margin-left: 300px;"></p>
				</form>
			</div>
		</div>
EOHTML;

	if ( !$num_matches ) {
		echo '<p class="highlight">No results found.</p>';
	}
	else {
		foreach( $Found as $doc ) {
			$doc->display('search');
		}
	}
?>
