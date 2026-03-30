<?php
/**
 * Various shared utiliies which can make testing easier.
 */

/**
 * Remove extraneous whitespace
 */
function remove_whitespace($input) {
	return preg_replace('/\s+/', ' ', $input);
}

/**
 * Remove whitespace before and after html tags
 */
function remove_html_whitespace($input) {
	$input = preg_replace('/>\s+/', ">", $input);
	return preg_replace('/\s+</', '<', $input);
}
