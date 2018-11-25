<?php
/**
 * Collection of functions (backed by unit tests) for the minutes search.
 */

// the daily routine is to go back 1 day
define('LOOK_BACK_DAYS', 1);

/**
 * Create the list of 'find' commands to run.
 */
function get_find_cmds($Directories, $yest_year, $yest_month_name) {
	$cmds = [];
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

		// look for html files which live inside this directory
		$cmds[] = "/usr/bin/find {$curdir}{$find_suffix} -type f -name '0*.html' {$find_mtime}";
	}

	return $cmds;
}

