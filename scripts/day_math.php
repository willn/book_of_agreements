<?php
/**
 * Count how many days have passed since a certain date.
 *
 * @param[in] date string a parseable date.
 * @return int number of days
 */
function get_days_since($date = NULL) {
	$now = time();
	$ts = strtotime($date);

	if (($ts === FALSE) || ($ts > $now)) {
		return 0;
	}

	return ($now - $ts) / (24 * 60 * 60);
}

// echo get_days_since('2019-02-26') . "\n";

