<?php
/**
 * This is used in conjunction with a cronjob to slurp in archived email
 * minutes email messages sent to an archiving server. Point this at the
 * destination directory and this will insert into the database.
 */

$G_DEBUG = [0];
require_once '../public/config.php';
require_once 'search_includes.php';

$Directories = '';
$curdir = '';

// get this time yesterday
$yest = time() - 86400;
$yest_year = date( 'Y', $yest );
$yest_month = date( 'm', $yest );
$yest_month_name = date( 'F', $yest );

$content = '';
$Months = [
	1=>'january',
	2=>'february',
	3=>'march',
	4=>'april',
	5=>'may',
	6=>'june',
	7=>'july',
	8=>'august',
	9=>'september',
	10=>'october',
	11=>'november',
	12=>'december'
];
$Short_Months = [
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
$link = my_connect( $G_DEBUG, $HDUP );
if ($link === 0) {
	echo "Unable to connect to remote database\n";
	exit;
}
$Cmtys = my_getInfo( $G_DEBUG, $HDUP, $sql, $link, 'listname' );

if ( $G_DEBUG[0] > 1 ) {
	echo "==============\nCommittees\n";
	print_r( $Cmtys );
}

// get_find_cmds
error_log(__CLASS__ . ' ' . __FUNCTION__ . ' ' . __LINE__ . " get find cmds");
$find_cmds = get_find_cmds($Directories, $yest_year, $yest_month_name);
error_log(__CLASS__ . ' ' . __FUNCTION__ . ' ' . __LINE__ . " ran get find cmds");

	// XXX
	// $find_result = trim(`{$find_cmd}`);
	$find_result = '';
	$Files = explode(PHP_EOL, $find_result);
	if ( empty( $Files )) {
		echo "empty! -> $find_cmd\n";
		continue;
	}

	foreach( $Files as $file ) {
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

					# if we're able to match on the numeric date
					if ( preg_match( '/\D(\d{1,2})( |\.|\/|-)(\d{1,2})( |\.|\/|-)?(\d{2,4})?/', $header, $Matches )) {
						$month = $Matches[1];
						$day = $Matches[3];
						if ( isset( $Matches[5] )) {
							$year = $Matches[5];
						}
					} 
					# parse the date from an english format
					else
					{
						$header = strtolower( $header );

						# search month names
						$date_arr = search_months($Months, $header);
						if (empty($date_arr)) {
							$date_arr = search_months($Short_Months, $header);
						}

						if (!empty($date_arr)) {
							$day = $date_arr['day'];
							$month = $date_arr['month'];
							$year = $date_arr['year'];
						}
					}

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

		$inserted = my_insert( 0, $HDUP, 'minutes', $Info );
	}
}

function search_months($Months, $header)
{
	$date_arr = array();

	foreach ( $Months as $num=>$m ) {
		if ( preg_match( "/$m\.? (\d{1,2}),? (\d{2,4})?/i", $header, $Matches )) {
			$date_arr['month'] = $num;
			$date_arr['day'] = $Matches[1];
			if ( isset( $Matches[2] )) {
				$date_arr['year'] = $Matches[2];
			}

			return $date_arr;
		}
		else if ( preg_match( "/(\d{1,2}) $m\.? ?(\d{2,4})?/i", $header, $Matches )) {
			$date_arr['month'] = $num;
			$date_arr['day'] = $Matches[1];
			if ( isset( $Matches[2] )) {
				$date_arr['year'] = $Matches[2];
			}

			return $date_arr;
		}
	}

	return NULL;
}

?>
