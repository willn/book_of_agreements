<?php

$MainNav['home'] = 'Home';

global $mysql_api;

$sql = 'select cid, cmty from committees where parent=cid or parent=0 order by parent';
$CInfo = $mysql_api->get($sql, 'cid');
$Cmtys = array( );
foreach( $CInfo as $i=>$Info )
{ $Cmtys[$Info['cid']] = $Info['cmty']; }

$sql = 'select * from committees where cid!=parent order by cid';
$SubInfo = $mysql_api->get($sql);
$SubCmtys = array( );
foreach( $SubInfo as $i=>$Info )
{ $SubCmtys[$Info['parent']][$Info['cid']] = $Info['cmty']; }

?>
