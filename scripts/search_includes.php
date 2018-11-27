<?php
/**
 * Collection of functions (backed by unit tests) for the minutes search.
 */

require_once '../public/config.php';

// the daily routine is to go back 1 day
define('LOOK_BACK_DAYS', 1);

function get_short_months() {
	return [
		1=>'jan',
		2=>'feb',
		3=>'mar',
		4=>'apr',
		6=>'jun',
		7=>'jul',
		8=>'aug',
		9=>'sep',
		10=>'oct',
		11=>'nov',
		12=>'dec'
	];
}

/**
 * Create the list of 'find' commands to run.
 */
function parse_found_files($Directories, $Cmtys, $yest_year, $yest_month_name) {
	$entries = [];
	$find_mtime = '-mtime -' . LOOK_BACK_DAYS;
	$path = '/usr/local/cpanel/3rdparty/mailman/archives/private/%s_gocoho.org';

	foreach($Directories as $dir) {
		if (empty($dir)) {
			error_log(__CLASS__ . ' ' . __FUNCTION__ . ' ' . __LINE__ . " empty dir");
			return;
		}

		$curdir = sprintf($path, $dir) . '/';
		$find_suffix = '';
		if (LOOK_BACK_DAYS === 1) {
			$curdir = sprintf($path, $dir) . '/' . $yest_year . '-' . $yest_month_name;
			$find_suffix = '/*';
		}

		/*
		 * Check to see if this directory exists.
		 * Example: "...finance-minutes_gocoho.org/2018-August"
		 * Likely, this is because nobody has sent out minutes for this
		 * committee for this month yet.
		 */
		if (!file_exists($curdir)) {
			error_log(__CLASS__ . ' ' . __FUNCTION__ . ' ' . __LINE__ . " dir does not exist: {$dir}");
			continue;
		}

		$Matches = [];
		$cmtee_id = NULL;
		// XXX don't hardcode ".org" here...
		$match_string = '/private\/([^-]*)-?minutes_' . DOMAIN . '/';
		preg_match($match_string, $curdir, $Matches );
		if (!empty($Matches)) {
			$cmtee_name = $Matches[1];
			if ($cmtee_name === 'test') {
				continue;
			}

			if (!isset($Cmtys[$cmtee_name]['cid'])) {
				error_log(__CLASS__ . ' ' . __FUNCTION__ . ' ' . __LINE__ . " unmatched committee name: {$cmtee_name} {$curdir}");
				continue;
			}
			$cmtee_id = $Cmtys[$cmtee_name]['cid'];
		}
		else {
			echo __CLASS__ . ' ' . __FUNCTION__ . ' ' . __LINE__ . " match string:{$match_string}, curdir:{$curdir}\n";
		}

		// look for html files which live inside this directory
		$entries[] = [
			'find_cmd' => "/usr/bin/find {$curdir}{$find_suffix} -type f -name '0*.html' {$find_mtime}",
			'cid' => $cmtee_id,
		];
	}

	return $entries;
}

/**
 * Given a subject string, extract the date parts.
 * @param[in] header string the subject which may contain date parts.
 */
function get_date_parts($header) {
	$date_arr = [];
	$current_year = date('Y');

	# if we're able to match on the numeric date
	if ( preg_match( '/(\d{1,2})( |\.|\/|-)(\d{1,2})( |\.|\/|-)?(\d{2,4})?/',
		$header, $Matches )) {

		$year = $current_year;
		if ( isset( $Matches[5] )) {
			$year = $Matches[5];
		}

		$date_arr = [
			'month' => $Matches[1],
			'day' => $Matches[3],
			'year' => $year,
		];
	}
	# parse the date from an english format
	else
	{
		$header = strtolower($header);
		# search month names
		$date_arr = search_all_months($header);
	}

	if (!empty($date_arr) && !isset($date_arr['year'])) {
		$date_arr['year'] = $current_year;
	}

	// array_walk($date_arr, 'intval');
	if (is_numeric($date_arr['year']) && $date_arr['year'] < 2000) {
		$date_arr['year'] += 2000;
	}

	return $date_arr;
}

/**
 * Go through both the short and full month names.
 * @param[in] header string the subject which may contain date parts.
 */
function search_all_months($header) {
	$Months = get_months();
	$date_arr = search_months($Months, $header);
	if (!empty($date_arr)) {
		return $date_arr;
	}

	$Short_Months = get_short_months();
	$date_arr = search_months($Short_Months, $header);
	return $date_arr;
}

/**
 * Search the subject line for the date of the meeting.
 *
 * @param[in] Months array of months #!# ? really?
 * @param[in] header string the subject of the message, which may contain a date.
 */
function search_months($Months, $header) {
	$date_arr = [];

	foreach ($Months as $num=>$m) {
		// month-name, date, year
		if (preg_match( "/$m\.? (\d{1,2}),? (\d{2,4})?/i", $header, $Matches)) {
			$date_arr['month'] = $num;
			$date_arr['day'] = $Matches[1];
			if ( isset( $Matches[2] )) {
				$date_arr['year'] = $Matches[2];
			}
			return $date_arr;
		}

		// date month-name, year
		if (preg_match( "/(\d{1,2}) $m\.? ?(\d{2,4})?/i", $header, $Matches)) {
			$date_arr['month'] = $num;
			$date_arr['day'] = $Matches[1];
			if ( isset( $Matches[2] )) {
				$date_arr['year'] = $Matches[2];
			}
			return $date_arr;
		}
	}

	return [];
}

