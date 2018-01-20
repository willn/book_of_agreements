<?php
/**
 * This is used in conjunction with a cronjob to slurp in archived email
 * minutes email messages sent to an archiving server. Point this at the
 * destination directory and this will insert into the database.
 */

$G_DEBUG = array( 0 );

$find_mtime = '-mtime -1';

# if true - then suck in all minutes, false: just the previous 24 hours
$alltime = false;

$Directories = '';
$curdir = '';
$yest = time( ) - 86400;
$yest_year = date( 'Y', $yest );
$yest_month = date( 'm', $yest );
$yest_month_name = date( 'F', $yest );
$content = '';
$Months = array( 1=>'january', 2=>'february', 3=>'march', 4=>'april', 5=>'may', 6=>'june', 7=>'july', 8=>'august', 9=>'september', 10=>'october', 11=>'november', 12=>'december' );
$Short_Months = array( 1=>'jan', 2=>'feb', 3=>'mar', 4=>'apr', 6=>'jun', 7=>'jul', 8=>'aug', 9=>'sep', 10=>'oct', 11=>'nov', 12=>'dec' );

$HDUP = array( 
	'host'=>'', // enter database hostname
	'database'=>'', // database name
	'user'=>'', // database username
	'password'=>'' // database password
);

# pull in the database library
$api_loc = 'PATH_TO/logic/php_api/';
require_once( $api_loc . 'database/mysql_connex.php' );

# get a listing of all the minutes lists
$mailman = ''; // path to directory of mailman files
exec( "/usr/bin/find $mailman -name '*minutes_ORGANIZATION.org' -type l", &$Directories );

if ( empty( $Directories )) {
	if ( $G_DEBUG[0] > 1 ) {
		echo "Failed to find any mailman directories\n";
	}
	exit;
}

$sql = 'select cid, listname from committees where listname!="#none"';
$link = my_connect( $G_DEBUG, $HDUP );
if ($link === 0) {
	if ( $G_DEBUG[0] > 1 ) {
		echo "Unable to connect to remote database\n";
	}
	exit;
}
$Cmtys = my_getInfo( $G_DEBUG, $HDUP, $sql, $link, 'listname' );

if ( $G_DEBUG[0] > 1 ) {
	echo "==============\nCommittees\n";
	print_r( $Cmtys );
}

foreach( $Directories as $num=>$dir )
{
	$dir = str_replace( 'public', 'private', $dir );
	if ( $G_DEBUG[0] > 1 ) {
		echo "$dir\n";
	}
	if ( empty( $dir )) {
		echo "empty directory string\n";
		exit;
	}

	$curdir = $dir;
	if ( !$alltime ) {
		$curdir = $dir . '/' . $yest_year . '-' . $yest_month_name;
	}
	if ( !file_exists( $curdir )) {
		continue;
	}

	$Matches = array( );
	$cmtee = '';
	preg_match( '/private\/([^-]*)-?minutes_ORGANIZATION.org/', $curdir, $Matches );
	if ( !empty( $Matches )) {
		$cmtee = $Matches[1];
		if ( $cmtee == 'test' ) {
			continue;
		}

		if ( !isset( $Cmtys[$cmtee]['cid'] )) {
			echo "unmatched committee name: $cmtee $curdir\n";
			continue;
		}
		if ( $G_DEBUG[0] >= 1 ) { echo "Current Committee: $cmtee\n"; }
		$cmtee = $Cmtys[$cmtee]['cid'];
	}

	$Files = array( );
	$find_suffix = '';
	if ( $alltime ) {
		$find_mtime = '';
		$find_suffix = '/*-*';
	}
	$find_cmd = "/usr/bin/find $curdir$find_suffix -type f -name '0*.html' $find_mtime";
	if ( $G_DEBUG[0] > 1 ) { echo "Find Committee: $find_cmd\n"; }
	exec( $find_cmd, &$Files );

	if ( empty( $Files )) {
		if ( $G_DEBUG[0] ) {
			echo "no files found in $curdir with\n$find_cmd\nmoving on\n";
		}
		continue;
	}

	$FileInfo = array( );
	foreach( $Files as $file ) {
		if ( $G_DEBUG[0] > 0 ) {
			echo "\nF: $file\n";
		}

		if ( !file_exists( $file )) {
			echo "file doesn't exist: $file\n";
			exit;
		}

		$FileInfo = file( $file );
		if ( empty( $FileInfo )) {
			echo "unable to access content from file: $file\n";
			exit;
		}

		$Info = array(
			'm_id'=>NULL,
			'notes'=>'',
			'agenda'=>'',
			'content'=>'',
			'cid'=>$cmtee,
			'date'=>''
		);
		$start = false;
		$body_start = false;
		$content = '';

		$date = '';
		foreach( $FileInfo as $line ) {
			$line = trim( $line );

			if ( $line == '<!--beginarticle-->' ) {
				$start = true;
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
					if( $G_DEBUG[0] >= 1 ) { echo "H: $header\n"; }

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
						if ( $G_DEBUG[0] >= 1 ) { echo "parse date\n"; }
						$header = strtolower( $header );

						# search month names
						if ( $G_DEBUG[0] >= 1 ) { echo "check for long month names\n"; }
						$date_arr = search_months($Months, $header);
						if (empty($date_arr)) {
							if ( $G_DEBUG[0] >= 1 ) { echo "check for short months names\n"; }
							$date_arr = search_months($Short_Months, $header);
						}

						if (!empty($date_arr)) {
							if ( $G_DEBUG[0] >= 1 ) { echo "found parsed date:\n" . print_r($date_arr, true); }
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
					if ( $G_DEBUG[0] > 0 ) { echo "DATE: $date\n"; }
				}
				continue;
			}

			#if we've found the agenda divider...
			if ( !$body_start && preg_match( '/^-{3,}$/', $line )) {
				$body_start = true;
				$Info['agenda'] = mysql_real_escape_string( $content, $link );
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
		$Info['content'] = mysql_real_escape_string( $content, $link );
		$Info['date'] = $date;

		# probably a false match, swap content for agenda
		if ( strlen( $Info['agenda'] ) > strlen( $Info['content'] )) {
			$Info['content'] = $Info['agenda'];
			$Info['agenda'] = '';
		}

		$inserted = my_insert( $G_DEBUG, $HDUP, 'minutes', $Info );
		if ( $G_DEBUG[0] >= 1 ) { echo "inserted: $inserted\n-----\n"; }
	}
	if ( $G_DEBUG[0] > 1 ) { echo "==============\n"; }
}

function search_months($Months, $header)
{
	global $G_DEBUG;
	$date_arr = array();

	foreach ( $Months as $num=>$m ) {
		if ( preg_match( "/$m\.? (\d{1,2}),? (\d{2,4})?/i", $header, $Matches )) {
			if ( $G_DEBUG[0] > 1 ) {
				echo print_r( $Matches, true );
			}
			$date_arr['month'] = $num;
			$date_arr['day'] = $Matches[1];
			if ( isset( $Matches[2] )) {
				$date_arr['year'] = $Matches[2];
			}

			return $date_arr;
		}
		else if ( preg_match( "/(\d{1,2}) $m\.? ?(\d{2,4})?/i", $header, $Matches )) {
			if ( $G_DEBUG[0] > 1 ) {
				echo print_r( $Matches, true );
			}
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
