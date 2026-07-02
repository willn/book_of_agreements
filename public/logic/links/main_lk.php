<?php
require_once dirname(__DIR__) . '/utils.php';

$MainNav['home'] = 'Home';

$Cmtys = getCommitteesList();
$SubCmtys = getSubCommitteesList();

?>
