<?php

/**
 * Get the MySQL API object
 */
function get_mysql_api() {
	$mysql_api = NULL;

	if (file_exists('config.php')) {
		require_once('config.php');
		require_once 'logic/mysql_api.php';
		$mysql_api = new MysqlApi($HDUP['host'], $HDUP['database'],
			$HDUP['user'], $HDUP['password']);
	}

	return $mysql_api;
}

/**
 * Clean up user-supplied input, replacing certain characters for others within
 * the first 128 ascii characters.
 *
 * @param[in] str string input string to be sanitized
 * @return string the sanitized input
 */
function clean_html($str) { 
    $str = str_replace(chr(194), '-', $str);
    $str = str_replace(chr(226), '...', $str);

    # Quotes cleanup 
    $str = str_replace( chr(ord("`")), "'", $str );        # ` 
    $str = str_replace( chr(ord("´")), "'", $str );        # ´ 
    $str = str_replace( chr(ord("„")), ",", $str );        # „ 
    $str = str_replace( chr(ord("`")), "'", $str );        # ` 
    $str = str_replace( chr(ord("´")), "'", $str );        # ´ 
    $str = str_replace( chr(ord("“")), "\"", $str );        # “ 
    $str = str_replace( chr(ord("”")), "\"", $str );        # ” 
    $str = str_replace( chr(ord("´")), "'", $str );        # ´ 

    $unwanted_array = array(
		'Š'=>'S',
		'š'=>'s',
		'Ž'=>'Z',
		'ž'=>'z',
		'À'=>'A',
		'Á'=>'A',
		'Â'=>'A',
		'Ã'=>'A',
		'Ä'=>'A',
		'Å'=>'A',
		'Æ'=>'A',
		'Ç'=>'C',
		'È'=>'E',
		'É'=>'E',
		'Ê'=>'E',
		'Ë'=>'E',
		'Ì'=>'I',
		'Í'=>'I',
		'Î'=>'I',
		'Ï'=>'I',
		'Ñ'=>'N',
		'Ò'=>'O',
		'Ó'=>'O',
		'Ô'=>'O',
		'Õ'=>'O',
		'Ö'=>'O',
		'Ø'=>'O',
		'Ù'=>'U',
		'Ú'=>'U',
		'Û'=>'U',
		'Ü'=>'U',
		'Ý'=>'Y',
		'Þ'=>'B',
		'ß'=>'Ss',
		'à'=>'a',
		'á'=>'a',
		'â'=>'a',
		'ã'=>'a',
		'ä'=>'a',
		'å'=>'a',
		'æ'=>'a',
		'ç'=>'c',
		'è'=>'e',
		'é'=>'e',
		'ê'=>'e',
		'ë'=>'e',
		'ì'=>'i',
		'í'=>'i',
		'î'=>'i',
		'ï'=>'i',
		'ð'=>'o',
		'ñ'=>'n',
		'ò'=>'o',
		'ó'=>'o',
		'ô'=>'o',
		'õ'=>'o',
		'ö'=>'o',
		'ø'=>'o',
		'ù'=>'u',
		'ú'=>'u',
		'û'=>'u',
		'ý'=>'y',
		'ý'=>'y',
		'þ'=>'b',
		'ÿ'=>'y',
	); 
    $str = strtr($str, $unwanted_array); 

    # Bullets, dashes, and trademarks 
    $str = str_replace( chr(149), "&#8226;", $str );   # bullet • 
    $str = str_replace( chr(150), "&ndash;", $str );   # en dash 
    $str = str_replace( chr(151), "&mdash;", $str );   # em dash 
    $str = str_replace( chr(153), "&#8482;", $str );   # trademark 
    $str = str_replace( chr(169), "&copy;", $str );    # copyright mark 
    $str = str_replace( chr(174), "&reg;", $str );     # registration mark 

    return $str; 
}

/**
 * Format the html, removing and escaping.
 *
 * @param[in] html string the string to format / escape.
 * @param[in] keep_eol boolean (optional, default FALSE) If true, then keep
 *     end of line marks, otherwise remove them.
 */
function format_html($html, $keep_eol=FALSE) {
	# convert all newlines to \n
	$html = preg_replace("/\\\\r\\\\n|\\\\r|\\\\n/", "\n", $html);

	$normal_characters = "a-zA-Z0-9\s`~!@#$%^&*()_+-={}|:;<>?,.\/\"\'\\\[\]";
	$html = preg_replace("/[^$normal_characters]/", '', $html);

	# escape any html characters
	$html = htmlentities($html, ENT_SUBSTITUTE);

	# convert escaped characters to actual tabs
	$html = str_replace('&amp;#160;', "&nbsp;&nbsp;&nbsp;&nbsp;", $html);
	$html = str_replace('&amp;quot;', '"', $html);
	$html = str_replace('&amp;amp;', '&amp;', $html);
	$html = str_replace('&amp;gt;', '&gt;', $html);
	$html = str_replace('&amp;lt;', '&lt;', $html);
	$html = str_replace('&amp;#', '&#', $html);

	# whether to keep newlines, so this wraps
	if (!$keep_eol) {
		$html = nl2br($html, FALSE);
	}

	return stripslashes($html);
}

/**
 * Format the email message.
 */
function format_email( $s ) {
	# convert all newlines to \n
	$s = preg_replace( "/\\\\r\\\\n|\\\\r/", "\n", $s );
	return stripslashes($s);
}

/**
 * Get the list of month names
 */
function get_months() {
	return  [
		1=>'January',
		2=>'February',
		3=>'March',
		4=>'April',
		5=>'May',
		6=>'June',
		7=>'July',
		8=>'August',
		9=>'September',
		10=>'October',
		11=>'November',
		12=>'December'
	];
}

