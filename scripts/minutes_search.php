<?php
/**
 * This is used in conjunction with a cronjob to slurp in archived email
 * minutes email messages sent to an archiving server. Point this at the
 * destination directory and this will insert into the database.
 */

$options = getopt("d");
$is_debug = array_key_exists('d', $options) ? 1 : 0;
$G_DEBUG = [$is_debug];

require_once '../public/config.php';
require_once '../public/logic/utils.php';
require_once 'search_includes.php';

$Directories = '';
$curdir = '';

// get this time yesterday
$yest = time() - 86400;
$yest_year = date( 'Y', $yest );
$yest_month = date( 'm', $yest );
$yest_month_name = date( 'F', $yest );

$content = '';

# pull in the database library
$api_loc = '../public/logic/php_api/';
require_once($api_loc . 'database/mysqli_connex.php');

$Directories = [
	'buildings-minutes',
	'ch-minutes',
	'finance-minutes',
	'grounds-minutes',
	'infoco-minutes',
	'meals-minutes',
	'membership-minutes',
	'minutes',
	'process-minutes',
	'steering-minutes',
	'work-minutes',
	'workshop-minutes',
];

$sql = 'select cid, listname from committees where listname!="#none"';
$link = my_connect($G_DEBUG, $HDUP);
if ($link === 0) {
	echo "Unable to connect to remote database\n";
	exit;
}
$Cmtys = my_getInfo($G_DEBUG, $HDUP, $sql, $link, 'listname');
if ($G_DEBUG[0] > 1) {
	print_r( $Cmtys );
}

$Months = get_months();
$Short_Months = get_short_months();

$entries = parse_found_files($Directories, $Cmtys, $yest_year, $yest_month_name);
/*
// shortcut for catching up on missed minutes
$entries = [
	[
		'find_cmd' => "/usr/bin/find /usr/local/cpanel/3rdparty/mailman/archives/private/test_gocoho.org/2018-November/* -type f -name '0*.html' -mtime -1",
		'cid' => 14,
	],
];
*/


foreach($entries as $entry) {
	$find_cmd = $entry['find_cmd'];
	$cmtee_id = $entry['cid'];

	$find_result = trim(`{$find_cmd}`);
	if (empty($find_result)) {
		continue;
	}

	$Files = explode(PHP_EOL, $find_result);
	if (empty($Files)) {
		continue;
	}

	foreach( $Files as $file ) {
		if ($G_DEBUG[0] > 0) {
			$short_file = preg_replace('/^.*private/', '', $file);
			echo "FILE: {$short_file}\n";
		}

		if ( !file_exists( $file )) {
			echo "file doesn't exist: $file\n";
			continue;
		}

		$lines = file( $file );
		if ( empty( $lines )) {
			echo "unable to access content from file: $file\n";
			exit;
		}

		$Info = [
			'm_id'=>NULL,
			'notes'=>'',
			'agenda'=>'',
			'content'=>'',
			'cid'=>$cmtee_id,
			'date'=>''
		];
		$start = FALSE;
		$body_start = FALSE;
		$content = '';
		$date = '';

		// process each line of the HTML file
		// XXX can I refactor this into a separate function? or is there scope?
		foreach($lines as $line) {
			$line = trim($line);

			if ( $line == '<!--beginarticle-->' ) {
				$start = TRUE;
				continue;
			} 

			# if haven't started yet, look for date, then skip
			if ( !$start ) {
				$year = 0;
				$month = 0;
				$day = 0;

				if ( empty( $date ) && 
						preg_match( '/<H1>([^<]*)<\/H1>/', $line, $Matches )) {
					$header = $Matches[1];

					$date_arr = get_date_parts($header);
					if (!empty($date_arr)) {
						$day = $date_arr['day'];
						$month = $date_arr['month'];
						$year = $date_arr['year'];
					}

					// XXX move this below stanza into the get_date_parts function
					# if neither of the above set the year, do it here
					if ( $year == 0 ) {
						$year = $yest_year;
						# if the month was higher than the current month,
						# assume last year
						if ( $month > $yest_month ) {
							echo "M: $month, YM: $yest_month\n";
							$year--;
						}
					}
					# is this a 2-digit year?
					elseif ( $year < 2000 ) {
						$year += 2000;
					}

					# if date still isn't set, choose yesterday as a default
					if ( $month == 0 || $day == 0 ) {
						$month = $yest_month;
						$day = date( 'd', $yest );
					}

					$date = sprintf("%04d-%02d-%02d", $year, $month, $day);
				}
				continue;
			}

			#if we've found the agenda divider...
			if ( !$body_start && preg_match( '/^-{3,}$/', $line )) {
				$body_start = TRUE;
				$Info['agenda'] = mysqli_real_escape_string($link, $content);
				$content = '';
				continue;
			}

			if ( $line == '<!--endarticle-->' ) {
				break;
			}

			$line = strip_tags( trim( $line ));
			if ( empty( $content ) && empty( $line )) {
				continue;
			}

			$content .= $line . "\n";
		}
		$Info['content'] = mysqli_real_escape_string($link, $content);
		$Info['date'] = $date;

		# probably a false match, swap content for agenda
		if ( strlen( $Info['agenda'] ) > strlen( $Info['content'] )) {
			$Info['content'] = $Info['agenda'];
			$Info['agenda'] = '';
		}

		switch($G_DEBUG[0]) {
			// not debugging, do the insert
			case 0:
				$inserted = my_insert( 0, $HDUP, 'minutes', $Info );
				break;

			// lightweight debug
			case 1:
				echo "insert date:{$Info['date']} cid:{$Info['cid']} length:" .
					strlen($Info['content']) . ' ' . substr($Info['content'], 0, 50) .
					"\n";
				break;

			// full debugging
			default:
				echo "INSERT: " . var_dump($Info);
		}
	}
}

