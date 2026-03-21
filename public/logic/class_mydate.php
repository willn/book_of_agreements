<?php

define('FUZZY_SECONDS', 100);
date_default_timezone_set('America/Detroit');

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

		$this->year = is_int( $year ) ? $year : $this->curyear;
		$this->month = is_int( $month ) ? $month : date('n');
		$this->day = is_int( $day ) ? $day : date('j');
		$this->label = $label;
	}

	/**
	 * Set the date with a string.
	 *
	 * @param[in] date_string string the date to use, should be formatted
	 *     as '2018-01-30'.
	 */
	function setDate( $date_string ) {
		if (!preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $date_string, $Matches)) {
			return;
		}

		$this->year = $Matches[1];
		$this->month = $Matches[2];
		$this->day = $Matches[3];
	}

	/**
	 * Get a date prior to the given one, by offset.
	 * Typically used to show "previous X days"
	 *
	 * @param[in] num_days int the number of days earlier this should shift.
	 */
	function getBefore() {
		$ts = mktime(0, 0, 0, $this->month, $this->day, $this->year);
		return date('Y-m-d', ($ts - NUM_SECS_PER_DAY));
	}

	/**
	 * Render the HTML needed for choosing a date in the advanced search.
	 */
	function selectDate() {
		$disp_label = !is_null($this->label) ? 
			ucfirst($this->label) . ' ' : '';

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

	/**
	 * Round down to the 1st day of the month, then subtract a few fuzzy seconds
	 * to get the previous day.
	 */
	function getStartOfMonth() {
		$timestamp = mktime(0, 0, 0, $this->month, 1, $this->year);
		return date('Y-m-d', ($timestamp - FUZZY_SECONDS));
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
