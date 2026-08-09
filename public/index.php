<?php
ini_set('display_errors', '0');
header("Content-Security-Policy: script-src 'self'");
header("Content-Security-Policy: script-src-elem 'self'");

require_once('config.php');
require_once('logic/mysql_api.php');
require_once('logic/utils.php');

session_start();
require_once( 'main.php' );
?>
