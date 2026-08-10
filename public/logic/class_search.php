<?php
require_once __DIR__ . '/class_agreement.php';
require_once __DIR__ . '/class_minute.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/lib_boa.php';

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

	protected $tags = [];

	public function __construct() {
		$this->start_date = new StartDate();
		$this->end_date = new EndDate();

		if (!is_authenticated() || ($_SESSION['boa_username'] == 'guest')) {
			$this->types_allowed = ['agreements'];
		}
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

		$tags = $this->getParam('tags');
		if (!empty($tags)) {
			$this->tags[] =  $tags;
		}
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

	public function getDateClauses() {
		return [
			'doc.date>="' . $this->start_date->getStartOfMonth() . '"',
			'doc.date<="' . $this->end_date->getEndOfMonth() . '"',
		];
	}

	public function getQueryClausesString($match, $against) {
		$clauses = [];
		$clauses[] = implode(' AND ', $this->getDateClauses());

		if (!$this->include_expired) {
			$clauses[] = 'expired=0';
		}

		if (!empty($against)) {
			$clauses[] = "({$match} {$against} OR tg.tags='{$this->terms}')";
		}

		if ($this->cmty_num != 0) {
			$clauses[] = "doc.cid='{$this->cmty_num}'";
		}

		$tags_clause = '';
		$tags_clause_entries = [];
		foreach($this->tags as $tag) {
			$tags_clause_entries[] = "tag='{$tag}'";
		}
		if (!empty($tags_clause_entries)) {
			$tags_clause = '(' . implode(' AND ', $tags_clause_entries) . ')';
		}

		return 'WHERE ' . implode("\n\t\tAND ", $clauses);
	}

	public function buildAgreementWhereClauses() {
		$clauses = [];

		if (!is_authenticated()) {
			$clauses[] = 'world_public=1';
		}

		// Date range
		$clauses = array_merge($clauses, $this->getDateClauses());

		if (!$this->include_expired) {
			$clauses[] = 'expired=0';
		}

		if ($this->cmty_num != 0) {
			$clauses[] = "doc.cid='{$this->cmty_num}'";
		}

		// Search text
		if (!empty($this->terms)) {
			$terms = addslashes($this->terms);

			//$tag_term = empty($this->tags) ? " OR t.tag='{$terms}'" : '';
			$clauses[] = "(
				MATCH(
					doc.title,
					doc.summary,
					doc.full,
					doc.background,
					doc.comments,
					doc.processnotes
				) AGAINST('{$terms}' IN NATURAL LANGUAGE MODE)
			)";
		}

		// Explicit tag filters
		foreach ($this->tags as $tag) {
			$tag = addslashes($tag);

			$clauses[] = "
				EXISTS (
					SELECT 1
					FROM tags_to_agreements tta2
					JOIN tags t2 ON t2.id = tta2.tag_id
					WHERE tta2.agreement_id = doc.id
					AND t2.tag = '{$tag}'
				)
			";
		}

		return $clauses;
	}

	protected function assembleWhereString(array $clauses) {
		if (empty($clauses)) {
			return '';
		}

		return "WHERE\n\t" . implode("\n\tAND ", $clauses);
	}

	public function createAgrQuery() {
		$score = '0 AS score';
		$order = '';

		if (!empty($this->terms)) {
			$terms = addslashes($this->terms);

			$score = "
				MATCH(
					doc.title,
					doc.summary,
					doc.full,
					doc.background,
					doc.comments,
					doc.processnotes
				) AGAINST('{$terms}' IN NATURAL LANGUAGE MODE) AS score
			";

			$order = 'ORDER BY score DESC';
		}

		$where = $this->assembleWhereString($this->buildAgreementWhereClauses());

		return <<<EOSQL
	SELECT 
		doc.*,
		c.cmty,
		tg.tags,
		{$score}
	FROM agreements doc
	JOIN committees c
		ON c.cid = doc.cid
	LEFT JOIN (
		SELECT
			tta.agreement_id,
			GROUP_CONCAT(DISTINCT t.tag ORDER BY t.tag SEPARATOR ', ') AS tags
		FROM tags_to_agreements tta
		JOIN tags t
			ON t.id = tta.tag_id
		GROUP BY tta.agreement_id
	) tg
		ON tg.agreement_id = doc.id
	{$where}
	{$order};
EOSQL;
	}

	/**
	 * search for agreements
	 *
	 * @return array list of agreement objects
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
			$agr->setTags($row['tags'] ?? '');
			$agreements[] = $agr;
		}

		return $agreements;
	}


	/**
	 * Create the SQL query for requesting minutes
	 */
	public function createMinsQuery() {
		$score = '0 AS score';
		$order = '';

		$clauses = $this->getDateClauses();
		if ($this->cmty_num != 0) {
			$clauses[] = "doc.cid='{$this->cmty_num}'";
		}

		if (!empty($this->terms)) {
			$terms = addslashes($this->terms);
			$match = 'MATCH(doc.notes, doc.agenda, doc.content)';
			$against = "AGAINST('{$terms}' IN NATURAL LANGUAGE MODE)";
			$clauses[] = "{$match} {$against}";
			$score = "{$match} {$against} AS score";
			$order = 'ORDER BY score DESC';
		}
		$where = implode("\n\t\tAND ", $clauses);

		return <<<EOSQL
	SELECT doc.*, {$score}
	FROM minutes doc
	WHERE {$where}
	{$order};
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
		if (empty($this->terms) && empty($this->tags) &&
			($this->cmty_num == 0)) {
			return;
		}

		$found = [];
		$skip_minutes = !empty($this->tags);

		switch($this->doc_type_chosen) {
			case 'agreements':
				$sql_a = $this->createAgrQuery();
				$found = $this->searchAgreements($sql_a);
				break;
			case 'minutes':
				if (!$skip_minutes) {
					$sql_m = $this->createMinsQuery();
					$found = $this->searchMinutes($sql_m);
				}
				break;
			case 'both':
			case 'all':
				$sql_a = $this->createAgrQuery();
				$found = $this->searchAgreements($sql_a);

				if (!$skip_minutes) {
					$sql_m = $this->createMinsQuery();
					$found = array_merge($found, $this->searchMinutes($sql_m));
				}
		}

		return $found;
	}

	/**
	 * Render the results of a SQL query
	 */
	public function renderResults($found) {
		$out = '';
		foreach($found as $doc) {
			$out .= $doc->renderSearchDisplay();
		}
		return $out;
	}

	public function getCommitteeOptions() {
		$com_options = '<option value="0">All</option>';
		$AllCmtys = getAllCommittees();
		foreach($AllCmtys as $cid=>$name) {
			$selected = ($cid == $this->cmty_num) ? ' selected' : '';
			$com_options .= "<option value=\"{$cid}\"{$selected}>{$name}</option>\n";
		}
		return $com_options;
	}

	/**
	 * Render HTML for a multi-tag selector
	 * array list the list of tags to render.
	 */
	public function renderTagSelector($list) {
		$tag_options = "<option value=\"\">None</option>\n";
		foreach($list as $id=>$name) {
			$selected = in_array($name, $this->tags) ? ' selected' : '';
			$tag_options .= "<option value=\"{$name}\"{$selected}>{$name}</option>\n";
		}

		return <<<EOHTML
			<div>
				Tag:&nbsp;<select name="tags">{$tag_options}</select>
			</div>
EOHTML;
	}

	public function renderDocTypeSelector() {
		if (count($this->types_allowed) < 2) {
			return '';
		}

		$types = '';
		foreach($this->types_allowed as $doc) {
			$checked = ($doc == $this->doc_type_chosen) ? ' checked' : '';
			$types .= <<<EOHTML
				<label>
					<input type="radio" name="show_docs" value="{$doc}" {$checked}> {$doc}
				</label>
EOHTML;
		}
		return $types;
	}

	/**
	 * Render this to HTML
	 */
	public function toString() {
		$exp_checked = ($this->include_expired) ? ' checked="checked"' : '';

		$search_terms_display = !empty($this->terms) ? 
			'query: [<b>' . $this->terms . '</b>]' : '';

		$com_options = $this->getCommitteeOptions();
		$tag_selector = $this->renderTagSelector(get_all_tags());

		$start_select = $this->start_date->selectDate();
		$end_select = $this->end_date->selectDate();

		$found = $this->runSearches();
		$num_matches = isset($found) ? count($found) : 0;
		$document_types = $this->renderDocTypeSelector();
		$start_string = $this->start_date->toString();
		$end_string = $this->end_date->toString();

		echo <<<EOHTML
			<h1>Search</h1>
			<div id="search_query">{$search_terms_display}
				number of results: {$num_matches}

				<div id="advanced_options">
					<h3>Advanced Search Options</h3>
					<form name="advanced_search" method="get" action="?id=search">
						<input type="hidden" name="id" value="search"/>
						<div><input type="search" name="q" value="{$this->terms}" size="50"/></div>
						<div>Committee:&nbsp;<select name="cmty">{$com_options}</select></div>
						{$tag_selector}
						{$start_select}
						{$end_select}
						<p>{$document_types}</p>
						<p>
							Include expired documents: 
							<input type="checkbox" name="include_expired"{$exp_checked}>
						</p>

						<div><input type="submit" value="search"></div>
					</form>
				</div>
			</div>
EOHTML;


		if ( !$num_matches ) {
			echo <<<EOHTML
<div class="highlight">No results found. Please do 1 or more of the following:
	<ul>
		<li>enter a search term</li>
		<li>select a tag</li>
		<li>select a committee</li>
	</ul>
</div>
EOHTML;
			return;
		}

		// XXX replace with renderResults()
		foreach($found as $doc) {
			echo $doc->renderSearchDisplay();
		}
	}
}
?>
