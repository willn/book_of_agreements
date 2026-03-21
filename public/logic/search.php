<?php
require_once('logic/class_search.php');

$Search = new Search();
$Search->parseGetVars();
$Search->runSearches();
$Search->toString();
?>
