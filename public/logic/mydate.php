<?php

class MyDate
{
	var $curyear;
	var $year;
	var $month;
	var $day;
	var $label;

	/**
	 * Create a new MyDate object.
	 *
	 * @param[in] year int the 4-digit year in question.
	 * @param[in] month int the 2-digit month in question.
	 * @param[in] day int the 2-digit day of the month in question.
	 * @param[in] label string a prefix for a selector, e.g. "start" or "end".
	 */
	function __construct( $year='', $month='', $day='', $label=NULL) {
		$this->curyear = date('Y');
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
	 * Get a date prior to the current one, by offset.
	 *
	 * @param[in] num_days int the number of days earlier this should shift.
	 */
	function getBefore($num_days) {
		$current_ts = mktime(0, 0, 0, $this->month, $this->day, $this->year);
		$adjusted_ts = $current_ts - (NUM_SECS_PER_DAY * $num_days);
		echo "CUR: $current_ts, adj: $adjusted_ts secs:" . NUM_SECS_PER_DAY . "\n";
		return date('Y-m-d', $adjusted_ts);
	}

	function selectDate( ) {
		$disp_label = !is_null($this->label) ? 
			ucfirst($this->label) . ' ' : '';

		# create day drop-down
		$days = '';
		for ( $i=1; $i<=31; $i++ ) {
			$sel = ( $i == $this->day ) ? ' selected="selected"' : '';
			$days .= "<option value=\"{$i}\"{$sel}>{$i}</option>\n";
		}

		#create month drop-down
		$months = '';
		$Months_list = get_months();
		foreach( $Months_list as $num=>$m ) {
			$sel = ( $num == $this->month ) ? ' selected="selected"' : '';
			$months .= "<option value=\"{$num}\"{$sel}>{$m}</option>\n";
		}

		#create year drop-down
		$years = '';
		for ( $i=2001; $i<=$this->curyear; $i++ ) {
			$sel = ( $i == $this->year ) ? ' selected="selected"' : '';
			$years .= "<option value=\"{$i}\"{$sel}>{$i}</option>\n";
		}

		return <<<EOHTML
		<p>{$disp_label}Date:
		<select name="{$this->label}day" size="1">{$days}</select>
		<select name="{$this->label}month" size="1">{$months}</select>
		<select name="{$this->label}year" size="1">{$years}</select>
		</p>
EOHTML;
	}

	function toString( ) {
		return sprintf( "%04d-%02d-%02d", $this->year, 
			$this->month, $this->day );
	}
}

?>
