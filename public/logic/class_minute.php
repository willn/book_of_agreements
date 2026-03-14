<?php

require_once('lib_boa.php');

/**
 * Minutes
 */
class Minutes extends BOADoc {
	public $doc_type = 'minutes';
	public $id = 0;
	public $notes = null;
	public $agenda = null;
	public $content = null;
	public $cid = 0;
	public $Date;
	public $search_points = 0;
	public $found = '';
	public $found_agenda = false;

	/**
	 * Create a new Minutes document
	 *
	 * m_id - minutes ID
	 * notes - notes section
	 * agenda - agenda section
	 * content - main content of the document
	 * cid - committee ID
	 * date - a MyDate object
	 */
	public function __construct($m_id='', $notes='', $agenda='',
		$content='', $cid='', $date='' ) {

		parent::__construct();

		$this->id = $m_id;
		$this->notes = clean_html($notes);
		$this->agenda = clean_html($agenda);
		$this->content = clean_html($content);
		$this->cid = $cid;
		$this->cmty->setId($cid);

		$this->Date = new MyDate( );
		if (!empty($date)) {
			$this->Date->setDate($date);
		}

		if (!empty($m_id) && empty($content) && empty($cid) && empty($date)) {
			$this->loadById($m_id);
		}
	}

	/**
	 * Load a Minutes instance by ID.
	 */
	public function loadById( $id='' )
	{
		$min_id = $id;
		if ($id == '') {
			$min_id = $this->id;
		}

		// don't try to load an invalid ID
		if (!is_int($min_id) || ($min_id == 0)) {
			return;
		}

		$HDUP = get_hdup();
		$entryDate = new MyDate( );

		$sql = 'select committees.cmty, minutes.* from minutes, '.
			"committees where m_id={$min_id} and committees.cid=minutes.cid";
		$this->init_mysql_api();
		$Min = $this->mysql_api->get($sql, NULL, FALSE);

		if ( empty( $Min )) {
			return;
		}
		$entryDate->setDate( $Min[0]['date'] );

		$this->__construct( $Min[0]['m_id'], $Min[0]['notes'], 
			$Min[0]['agenda'], $Min[0]['content'], $Min[0]['cid'], $Min[0]['date']);
	}

	/**
	 * Display a Minutes instance.
	 */
	public function display( $type='document' )
	{
		global $sub_summary_length;
		$admin_info = $this->displayAdminActions( );
		$short = '';

		$notes = format_html( $this->notes );
		$agenda = format_html( $this->agenda );
		$content = format_html( $this->content, FALSE);

		switch( $type )
		{
			case 'form':
				$notes = format_html( $this->notes, true );
				$agenda = format_html( $this->agenda, true );
				$content = format_html( $this->content, true );

				$notes = '<input type="text" name="notes" value="'.
					$notes . '" size="50">' . "\n";
				$agenda = '<textarea name="agenda" cols="85" rows="10">'.
					$agenda . "</textarea>\n";
				$content = '<textarea name="content" cols="85" rows="35">'.
					$content . "</textarea>\n";

				if ( !empty( $notes ))
				{ echo "<h3>Special Notes:</h3>\n$notes\n"; }
				if ( !empty( $agenda ))
				{ echo "<h3>Agenda:</h3>\n$agenda\n"; }
				if ( !empty( $content ))
				{ echo "<h3>Minutes:</h3>\n$content\n"; }

				break;

			case 'compact':
				echo "<tr>\n" .
					"\t<td>" . $this->cmty->getName() . "</td>\n" .
					"\t<td>" . '<a href="?id=minutes&num=' . $this->id . '">' .
						$this->Date->toString( ) . "</a></td>\n" . 
					"\t<td>" . $notes . "</td>\n";
					"</tr>\n";
				break;

			case 'search':
				if ( !empty( $this->found )) {
					$short = '<p class="short">FOUND:' . $this->found . "</p>\n";
					if (!$this->found_agenda) {
						$short .= "<br/>AGENDA: $agenda\n";
					}
				}
				// fall through to next step

			case 'summary':
				if ( empty( $short )) { $short = $agenda . $notes; }
				if ( empty( $short ))
				{ $short = substr( $content, 0, $sub_summary_length ) . '...'; }

				$date_string = $this->Date->toString( );
				$cmty_name = $this->cmty->getName();
				echo <<<EOHTML
					<div class="minutes">
						<h2 class="mins">
							<a href="?id=minutes&num={$this->id}">{$date_string} 
								{$cmty_name}</a> minutes
						</h2>
						<div class="item_topic">
							<div class="info">{$short}</div>
						</div>
					</div>
EOHTML;
				break;

			case 'document':
				echo '<div class="minutes">' . "\n" .
					'<h1 class="mins">' . $this->cmty->getName() .
					' minutes: ' . $this->Date->toString( ) . "</h1>\n" .
					'<div class="info">' . $admin_info;

				if ( !empty( $notes ))
				{ echo "<h3>Special Notes:</h3>\n$notes\n"; }
				if ( !empty( $agenda ))
				{ echo "<h3>Agenda:</h3>\n$agenda\n"; }
				if ( !empty( $content ))
				{ echo "<h3>Minutes:</h3>\n{$content}\n"; }

				echo "</div>\n</div>\n\n";
				break;
		}

		return 1;
	}

	/**
	 * Display the links for and admin user per each minutes document.
	 */
	public function displayAdminActions( )
	{
		$link = '';
		if (!isset( $_SESSION['admin'] ) || (!$_SESSION['admin'] )) {
			return '';
		}

		return <<<EOHTML
			<div class="actions">
				<a href="?id=admin&amp;doctype=minutes&amp;num={$this->id}">
					edit
				</a>
				&nbsp;&nbsp;
				<a href="?id=admin&amp;doctype=minutes&amp;delete={$this->id}">
					delete
					</a>
			</div>
EOHTML;
	}

	/**
	 * Save a minutes entry.
	 */
	public function save( $update=false ) {
		$HDUP = get_hdup();
		$success = 0;
		if ( $this->id == 0 ) {
			$this->id = '';
		}

		# check for required items
		if ( empty( $this->content )) {
			echo <<<EOHTML
				<div class="error">Missing content! 
					<a href="javascript:history.go(-1)">Back</a></div>
EOHTML;
			return FALSE;
		}

		$this->init_mysql_api();
		# if an update then keep the id
		if (( $update ) && ( is_int( $this->id ))) {
			$Info = array( 'notes="' . $this->notes . '"',
				'agenda="' . $this->agenda . '"',
				'content="' . $this->content . '"',
				'cid="' . intval( $this->cid ) . '"',
				'date="' . $this->Date->toString( ) . '"'
			);

			$new_vals = implode(' , ', $Info);
			$sql = "UPDATE minutes SET {$new_vals} WHERE m_id={$this->id}";
			$success = $this->mysql_api->query($sql);
		}
		# otherwise, treat this as a new entry
		else {
			$Info = [
				'NULL',
				"'{$this->notes}'",
				"'{$this->agenda}'",
				"'{$this->content}'",
				"'" . intval($this->cid) ."'", 
				"'" . $this->Date->toString() . "'"
			];
			$values = join(', ', $Info);
			$sql = <<<EOSQL
INSERT INTO minutes VALUES({$values});
EOSQL;
			$success = $this->mysql_api->query($sql);
		}

		if ( !$success ) {
			echo "Save didn't work\n";
			return FALSE;
		}
		echo "Success - saved!\n";

		if ( !is_int( $this->id )) {
			$sql = 'select max( m_id ) as max from minutes';
			$Max = $this->mysql_api->get($sql);
			$this->id = $Max[0]['max'];
		}

		// display success message
		echo <<<EOHTML
		<p>Saved!</p>
		<p><a href="?id=minutes&num={$this->id}">{$this->notes}</a></p>
EOHTML;

		echo <<<EOHTML
			<script type="text/javascript">
				window.location = "{$_SERVER['SCRIPT_NAME']}?id=minutes&num={$this->id}";
			</script>
EOHTML;

		return TRUE;
	}

	/**
	 * Delete a minutes entry.
	 */
	public function delete( $confirm )
	{
		$HDUP = get_hdup();

		if ( !$confirm )
		{
			$date_string = $this->Date->toString( );
			$cmty_name = $this->cmty->getName();
			echo <<<EOHTML
			<div class="minutes">
				<h2>Are you sure you want to delete these minutes?</h2>
				<h1 class="mins">{$cmty_name}: {$date_string}</h1>
			</div>
			<div class="actions">
				<a href="?id=admin&amp;doctype=minutes&amp;delete={$this->id}&confirm_del=1">
						confirm delete</a>
			</div>
EOHTML;
		}
		else
		{
			$sql = "DELETE FROM minutes WHERE m_id={$this->id}";
			$this->init_mysql_api();
			$success = $this->mysql_api->query($sql);
			if ( $success ) { echo "<p>Item deleted\n"; }
			else
			{ echo '<div class="error">Error: Item was not deleted</div>' . "\n"; }
			
		}
	}
}

?>
