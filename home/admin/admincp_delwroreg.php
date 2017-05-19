<?php
    $user=array();
    $dateline1=strtotime($_GET['dateline1']);
    $dateline2=strtotime($_GET['dateline2']);
    $query=$_SGLOBAL['db']->query("SELECT a.uid FROM ".tname('space')." AS a LEFT JOIN ".tname('spacefield')." AS b ON a.uid=b.uid WHERE b.sex=0 AND b.deviceid='' AND resideprovince='' AND residecity='' AND a.dateline>=".$dateline1." AND a.dateline<=".$dateline2);
    while($value=$_SGLOBAL['db']->fetch_array($query)){
        $user[]=$value['uid'];
    }
    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET realmoney=0,totalmoney=0 WHERE uid IN (".implode(",",$user).")");
    $moenylogTableNames = getMoneylogTableName($dateline1, $dateline2);
    foreach ($moenylogTableNames as $moenylogTableName)
    {
        $_SGLOBAL['db']->query("DELETE FROM `".tname($moenylogTableName)."` WHERE moneyloguid IN (".implode(",",$user).")");
    }
?>