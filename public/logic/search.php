<?php
require_once __DIR__ . '/class_search.php';

$Search = new Search();
$Search->parseGetVars();
$Search->toString();
?>
