<?php
require_once('logic/class_agreement.php');
require_once('logic/class_minute.php');
require_once('logic/utils.php');
require_once('logic/lib_boa.php');

/**
 * Take input, perform a search, and output the results
 */
class Search {
	protected $include_expired = FALSE;
	protected $terms = '';
	protected $cmty_num = NULL;
	protected $Agr_Clauses = [];
	protected $Mins_Clauses = [];
	protected $types_allowed = ['agreements', 'minutes', 'all'];
	protected $doc_type_chosen = 'agreements';

	protected $start_date;
	protected $end_date;

	public function __construct() {
		$this->start_date = new StartDate();
		$this->end_date = new EndDate();
	}

	public function parseGetVars() {
		$this->cmty_num = intval($this->getParam('cmty'));
		$this->setDocType($this->getParam('show_docs'));
		$this->setTerms($this->getParam('q'));
		$this->setIncludeExpired($this->getParam('include_expired'));

		$this->start_date = new StartDate($this->getParam('startyear'),
			$this->getParam('startmonth'));
		$this->end_date = new EndDate($this->getParam('endyear'),
			$this->getParam('endmonth'));
	}

	public function setDocType($type) {
		if (!empty($type) && in_array($type, $this->types_allowed)) {
			$this->doc_type_chosen = $type;
		}
	}

	public function setIncludeExpired($input) {
		if (!isset($input)) {
			$this->include_expired = FALSE;
			return;
		}

		if ($input == 'on') {
			$this->include_expired = TRUE;
		}
	}

	public function setTerms($text) {
		$this->terms = strtolower(htmlentities($text));
	}

	public function getParam($key, $default = NULL) {
		return $_GET[$key] ?? $default;
	}

	public function getCoreValues() {
		return [
            'cmty_num' => $this->cmty_num,
            'show_docs' => $this->doc_type_chosen,
            'q' => $this->terms,
            'include_expired' => $this->include_expired,
		];
	}

	public function getAgainstClause() {
		$terms = addslashes($this->terms);
		return "against('{$terms}')";
	}

	public function getDateClauses() {
		return [
			'date>="' . $this->start_date->getStartOfMonth() . '"',
			'date<="' . $this->end_date->getEndOfMonth() . '"',
		];
	}

	/**
	 * Create the SQL query for searching for agreements
	 */
	public function createAgrQuery() {
		$clauses = $this->getDateClauses();

		$ft_match = 'match(title, summary, full, background, comments, processnotes) ' .
			$this->getAgainstClause();
		$clauses[] = $ft_match;

		if ($this->cmty_num != 0) {
			$clauses[] = "cid='{$this->cmty_num}'";
		}

		if (!$this->include_expired) {
			$clauses[] = 'expired=0';
		}

		$clause_string = implode(' and ', $clauses);

		return <<<EOSQL
			SELECT agreements.*, committees.cmty, {$ft_match} AS score
			FROM agreements
			JOIN committees
				ON committees.cid = agreements.cid
			WHERE ({$clause_string})
			ORDER BY score DESC;
EOSQL;
	}


	/**
	 * search for agreements
	 */
	public function searchAgreements($sql_a) {
		$mysql_api = get_mysql_api();
		$agreements = [];

		$Info = $mysql_api->get($sql_a, 'id');
		foreach($Info as $row) {
			$agr = new Agreement();
			$agr->setId($row['id']);
			$agr->setContent($row['title'], $row['summary'], $row['full'], $row['background'],
				$row['comments'], $row['processnotes'], $row['cid'], $row['date'],
				$row['expired'], $row['world_public']);
			$agreements[] = $agr;
		}

		return $agreements;
	}


	public function createMinsQuery() {
		$clauses = $this->getDateClauses();

		$ft_match = 'match(notes, agenda, content)' . $this->getAgainstClause();
		$clauses[] = $ft_match;

		if ($this->cmty_num != 0) {
			$clauses[] = "cid='{$this->cmty_num}'";
		}

		$clause_string = '';
		if (!empty($clauses)) {
			$clause_string = implode(' and ', $clauses);
		}

		return <<<EOSQL
			SELECT *, {$ft_match} as score
				FROM minutes
				WHERE {$clause_string}
				ORDER BY score desc
EOSQL;
	}


	/**
	 * search for minutes
	 */
	public function searchMinutes($sql_m) {
		$mysql_api = get_mysql_api();
		$minutes = [];

		$Info = $mysql_api->get($sql_m, 'm_id');
		foreach($Info as $row) {
			$minutes[] = new Minutes($row['m_id'], $row['notes'], $row['agenda'],
				$row['content'], $row['cid'], $row['date']);
		}

		return $minutes;
	}


	public function runSearches() {
		$found = [];

		switch($this->doc_type_chosen) {
			case 'agreements':
				$sql_a = $this->createAgrQuery();
				$found = $this->searchAgreements($sql_a);
				break;
			case 'minutes':
				$sql_m = $this->createMinsQuery();
				$found = $this->searchMinutes($sql_m);
				break;
			case 'both':
			case 'all':
				$sql_a = $this->createAgrQuery();
				$sql_m = $this->createMinsQuery();
				$found = array_merge($this->searchAgreements($sql_a),
					$this->searchMinutes($sql_m));
		}

		return $found;
	}

	/**
	 * Render the results of a SQL query
	 */
	public function renderResults($found) {
		$out = '';
		foreach($found as $doc) {
			$out .= $doc->renderDisplay('search');
		}
		return $out;
	}

	/**
	 * Render this to HTML
	 */
	public function toString() {
		$exp_checked = ($this->include_expired) ? ' checked="checked"' : '';

		$search_terms_display = !empty($this->terms) ? 
			'query: [<b>' . $this->terms . '</b>]' : '';

		$start_select = $this->start_date->selectDate();
		$end_select = $this->end_date->selectDate();

		$found = $this->runSearches();
		$num_matches = count($found);

		$com_options = '<option value="0">All</option>';
		$AllCmtys = getAllCommittees();
		foreach($AllCmtys as $cid=>$name) {
			$selected = ($cid == $this->cmty_num) ? ' selected' : '';
			$com_options .= "<option value=\"{$cid}\"{$selected}>{$name}</option>\n";
		}

		// the default type
		$document_types = '';
		foreach($this->types_allowed as $doc) {
			$checked = ($doc == $this->doc_type_chosen) ? ' checked' : '';
			$document_types .= <<<EOHTML
				<label>
					<input type="radio" name="show_docs" value="{$doc}" {$checked}> {$doc}
				</label>
EOHTML;
		}

		$start_string = $this->start_date->toString();
		$end_string = $this->end_date->toString();

		echo <<<EOHTML
			<h1>Search</h1>
			{$start_string} {$end_string}
			<div id="search_query">{$search_terms_display}
				number of results: {$num_matches}

				<div id="advanced_options">
					<h3>Advanced Search Options</h3>
					<form name="advanced_search" method="get" action="?id=search">
						<input type="hidden" name="id" value="search"/>
						<p><input type="search" name="q" value="{$this->terms}" size="50"/></p>
						<p>Committee:&nbsp;<select name="cmty">{$com_options}</select></p>
						{$start_select}
						{$end_select}
						<p>{$document_types}</p>
						<p>
							Include expired documents: 
							<input type="checkbox" name="include_expired"{$exp_checked}>
						</p>

						<p><input type="submit" value="search" style="margin-left: 300px;"></p>
					</form>
				</div>
			</div>
EOHTML;


		if ( !$num_matches ) {
			echo '<p class="highlight">No results found.</p>';
			return;
		}

		// XXX replace with renderResults()
		foreach($found as $doc) {
			$doc->display('search');
		}
	}
}
?>
