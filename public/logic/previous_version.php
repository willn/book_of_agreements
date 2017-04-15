<?php
$agr_id = intval($_REQUEST['agr_id']);
$version = intval($_REQUEST['prev_id']);

require_once 'logic/lib_boa.php';

$Agr = new Agreement();
$Agr->setId($agr_id);
print $Agr->getDiff($version);
?>
