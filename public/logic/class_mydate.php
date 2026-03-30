<?php

define('FUZZY_SECONDS', 100);
require_once('utils.php');

class MyDate
{
	protected $curyear;
	protected $curmonth;

	protected $year;
	protected $month;
	protected $day;
	protected $label;

	/**
	 * Create a new MyDate object.
	 *
	 * @param[in] year int the 4-digit year in question.
	 * @param[in] month int the 2-digit month in question.
	 * @param[in] day int the 2-digit day of the month in question.
	 * @param[in] label string a prefix for a selector, e.g. "start" or "end".
	 */
	function __construct($year='', $month='', $day='', $label=NULL) {
		$this->curyear = date('Y');
		$this->curmonth = date('m');

		$year = intval($year);
		$this->year = ($year) ? $year : STARTING_YEAR;

		$month = intval($month);
		$this->month = ($month) ? $month : 1;

		$day = intval($day);
		$this->day = ($day) ? $day : 1;

		$this->label = $label;
	}

	/**
	 * Set the date with a string.
	 *
	 * @param[in] date_string string the date to use, should be formatted
	 *     as '2018-01-30'.
	 */
	function setDate($date_string) {
		if (!preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $date_string, $Matches)) {
			return;
		}

		$this->year = $Matches[1];
		$this->month = $Matches[2];
		$this->day = $Matches[3];
	}

	/**
	 * Render the HTML needed for choosing a date in the advanced search.
	 */
	function selectDate() {
		#create month drop-down
		$months = '';
		$Months_list = get_months();
		foreach( $Months_list as $num=>$m ) {
			$sel = ( $num == $this->month ) ? ' selected="selected"' : '';
			$months .= "<option value=\"{$num}\"{$sel}>{$m}</option>\n";
		}

		#create year drop-down
		$years = '';
		for ($i = STARTING_YEAR; $i <= $this->curyear; $i++) {
			$sel = ( $i == $this->year ) ? ' selected="selected"' : '';
			$years .= "<option value=\"{$i}\"{$sel}>{$i}</option>\n";
		}

		$disp_label = !is_null($this->label) ? 
			ucfirst($this->label) . ' ' : '';

		return <<<EOHTML
		<p>{$disp_label}Date:
		<select name="{$this->label}year" size="1">{$years}</select>
		<select name="{$this->label}month" size="1">{$months}</select>
		</p>
EOHTML;
	}

	function toString( ) {
		if (is_null($this->year) || is_null($this->month)) {
			return '';
		}

		if (is_null($this->day)) {
			return sprintf("%04d-%02d", $this->year, $this->month);
		}

		return sprintf("%04d-%02d-%02d", $this->year, 
			$this->month, $this->day);
	}
}

class StartDate extends MyDate {
	protected $label = 'start';

	/**
	 * Create a new StartDate object.
	 *
	 * @param[in] year int the 4-digit year in question.
	 * @param[in] month int the 2-digit month in question.
	 * @param[in] day int the 2-digit day of the month in question.
	 * @param[in] label string a prefix for a selector, e.g. "start" or "end".
	 */
	function __construct($year='', $month='', $day='', $label=NULL) {
		parent::__construct($year, $month, $day, 'start');

		$year = intval($year);
		$this->year = ($year) ? $year : STARTING_YEAR;

		$month = intval($month);
		$this->month = ($month) ? $month : 1;

		$day = intval($day);
		$this->day = ($day) ? $day : 1;
	}

	/**
	 * Round down to the 1st day of the month, then subtract a few fuzzy seconds
	 * to get the previous day.
	 */
	function getStartOfMonth() {
		$timestamp = mktime(0, 0, 0, $this->month, 1, $this->year);
		return date('Y-m-d', ($timestamp - FUZZY_SECONDS));
	}

}

class EndDate extends MyDate {
	protected $label = 'end';
	
	/**
	 * Create a new StartDate object.
	 *
	 * @param[in] year int the 4-digit year in question.
	 * @param[in] month int the 2-digit month in question.
	 * @param[in] day int the 2-digit day of the month in question.
	 * @param[in] label string a prefix for a selector, e.g. "start" or "end".
	 */
	function __construct($year='', $month='', $day='', $label=NULL) {
		parent::__construct($year, $month, $day, 'end');
		$this->year = intval($year) ? intval($year) : $this->curyear;
		$this->month = intval($month) ? intval($month) : $this->curmonth;

		$year = intval($year);
		$this->year = ($year) ? $year : $this->curyear;

		$month = intval($month);
		$this->month = ($month) ? $month : $this->curmonth;

		$day = intval($day);
		$this->day = ($day) ? $day : date('d');
	}

	/**
	 * Round up to the last day of the month, then add a few fuzzy seconds
	 * to get the next day. If this is the current month, then return now.
	 */
	function getEndOfMonth() {
		// if date is not set, then use NOW
		if ($this->year == 0) {
			return date('Y-m-d');
		}

		// return early with NOW if this is the current month
		if (($this->year == $this->curyear) && ($this->month == $this->curmonth)) {
			return date('Y-m-d');
		}

		$num_days_in_mo = cal_days_in_month(CAL_GREGORIAN, $this->month,
			$this->year);
		$timestamp = mktime(0, 0, 0, $this->month, $num_days_in_mo, $this->year);
		return date('Y-m-d', ($timestamp + NUM_SECS_PER_DAY + FUZZY_SECONDS));
	}
}

?>
