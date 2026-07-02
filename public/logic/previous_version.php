<?php
require_once __DIR__ . '/class_agreement.php';
require_once __DIR__ . '/lib_boa.php';

$agr_id = intval($_REQUEST['agr_id']);
$version = intval($_REQUEST['prev_id']);

$Agr = new Agreement();
$Agr->setId($agr_id);
print $Agr->getDiff($version);
?>
