<?php

require_once dirname(__DIR__) . '/logic/mysql_api.php';
require_once dirname(__DIR__) . '/config.php';

/**
 * Get the MySQL API
 */
function get_mysql_api() {
	$HDUP = get_hdup();
	return new MysqlApi($HDUP['host'], $HDUP['database'],
		$HDUP['user'], $HDUP['password']);
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

function getCommitteesList() {
	$mysql_api = get_mysql_api();

	$sql = <<<EOSQL
select cid, cmty from committees where parent=cid or parent=0 order by parent
EOSQL;
	$CInfo = $mysql_api->get($sql, 'cid');

	$Cmtys = [];
	foreach($CInfo as $i=>$Info) {
		$Cmtys[$Info['cid']] = $Info['cmty'];
	}

	return $Cmtys;
}

function getSubCommitteesList() {
	$mysql_api = get_mysql_api();

	$sql = 'select * from committees where cid!=parent order by cid';
	$SubInfo = $mysql_api->get($sql);

	$SubCmtys = [];
	foreach( $SubInfo as $i=>$Info ) {
		$SubCmtys[$Info['parent']][$Info['cid']] = $Info['cmty'];
	}

	return $SubCmtys;
}

function getAllCommittees() {
	$Cmtys = getCommitteesList();
	$SubCmtys = getSubCommitteesList();

	$AllCmtys = [];
	foreach($Cmtys as $num=>$cm) {
		if (is_string($cm)) {
			$AllCmtys[$num] = $cm;
		}
		if (isset($SubCmtys[$num])) {
			foreach($SubCmtys[$num] as $subnum=>$subname) {
				$AllCmtys[$subnum] = $cm . ': ' . $subname;
			}
		}
	}

	return $AllCmtys;
}


/**
 * Query the database for a full list of all existing tags.
 */
function get_all_tags() {
	$mysql_api = get_mysql_api();

	$sql = 'select id, tag from tags order by tag ASC';
	$results = $mysql_api->get($sql);

	$tags = [];
	foreach($results as $entry=>$row) {
		$tags[$row['id']] = $row['tag'];
	}

	return $tags;
}


function render_tags($tags_list) {
	if (empty($tags_list)) {
		return '';
	}

	if (is_string($tags_list)) {
		$tags_list = explode(', ', $tags_list);
	}

	$inner = '';
	foreach($tags_list as $entry) {
		$inner .= <<<EOHTML
<a href="/boa/?id=search&tags={$entry}" class="tag_entry">{$entry}</a> 
EOHTML;
	}
	return <<<EOHTML
<div class="tags">Tags: {$inner}</div>
EOHTML;
}


/**
 * Is this user currently authenticated?
 * @return boolean determines if the user logged in or not
 */
function is_authenticated() {
	if (PHP_SAPI === 'cli') {
		return FALSE;
	}

	session_start();
	return (array_key_exists('boa_username', $_SESSION) &&
		!empty($_SESSION['boa_username']));
}

/**
 * End the user's session
 */
function do_logout() {
	unset($_SERVER['PHP_AUTH_USER']); 
	$_SESSION['boa_username'] = FALSE;
}

/**
 * Attempt to login the user. Otherwise display the login form.
 */
function attempt_login() {
	$authz_users = get_authorized_users();

	if (!isset($_POST['boa_username']) || !isset($_POST['boa_password'])) {
		display_login_form();
		error_log(__FUNCTION__ . ' ' . __LINE__ . " username or password not set");
	}

	// check the password
	if ($authz_users[$_POST['boa_username']] == hash('sha256', $_POST['boa_password'])) {
		// password checks out, mark them as logged in
		$_SESSION['boa_username'] = $_POST['boa_username'];
		return TRUE;
	}
	else {
		display_login_form();
		error_log(__FUNCTION__ . ' ' . __LINE__ . " display login form");
	}
}

/**
 * Display the login form.
 */
function display_login_form() {
	$form_dest = '?id=login';

	$vars = get_query_string_vars();
	$passalong = '';
	if ($vars['page_id'] && $vars['num']) {
		$passalong = <<<EOHTML
<div class="agreement_id">
	<div>Continue to {$vars['page_id']}: {$vars['num']} after logging in.</div>
	<input type="hidden" name="id" value="{$vars['page_id']}">
	<input type="hidden" name="num" value="{$vars['num']}">
</div>
EOHTML;

		$form_dest = "?id={$vars['page_id']}&num={$vars['num']}";
	}

	include 'display/templates/login_screen.html';
	exit;
}

/**
 * Look for certain query string parameters and return them.
 */
function get_query_string_vars() {
	$tmp_id = $_GET['id'];
	switch($tmp_id) {
		case 'agreement':
		case 'minutes':
			$out = ['page_id' => $tmp_id];

			$tmp_num = intval($_GET['num']);
			if ($tmp_num != 0) {
				$out['num'] = $tmp_num;
			}

			return $out;
	}

	return [];
}

/**
 * Get safe GET parameter parsing.
 */
function getWordParam($params, $name, $default = '')
{
    if (!isset($params[$name])) {
        return $default;
    }

    if (preg_match('/^(\w+)$/', $params[$name], $matches)) {
        return $matches[1];
    }

    return $default;
}

/**
 * Get the Page ID
 */
function getPageId($params) {
    $id = getWordParam($params, 'id', 'recent');
    if (!is_authenticated() && ($id !== 'login') && ($id !== 'logout')) {
        return empty($id) ? 'agreement' : $id;
    }
    return $id;
}

