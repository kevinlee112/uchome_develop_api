<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_ajax.php 12535 2009-07-06 06:22:34Z zhengqingpeng $
*/
if(!defined('IN_UCHOME')) {
        exit('Access Denied');
}
include_once(S_ROOT.'./source/function_cp.php');
include_once(S_ROOT.'./source/function_common.php');
$uid = empty($_POST['uid'])?0:intval($_POST['uid']);
writeLog("do_appsubject.php");
$op = empty($_POST['op'])?'':$_POST['op'];
$lat = empty($_GET['lat'])?'':$_GET['lat'];
$lng = empty($_GET['lng'])?'':$_GET['lng'];
$result = array(
    'errcode' => 0,
    'errmsg' => 'success'
);
$_SGLOBAL['supe_uid'] = $uid;
writeLog("----------------------------------------uid:".$uid." op:".$op);
if($op == 'getallbanner') {
    $queryContact = $_SGLOBAL['db_slave']->query('SELECT b.realmoney,a.qq, a.msn, a.sex, a.email, a.mobile,a.event,a.tags,a.resideprovince,a.residecity FROM '.tname('spacefield')." AS a LEFT JOIN ".tname('space')." AS b ON a.uid=b.uid WHERE a.uid = '$uid'");
    $valueContact = $_SGLOBAL['db_slave']->fetch_array($queryContact);
    writeLogDebug("^^^^^^^^^^^^^^^^^^^".$valueContact['event']); 
    $query = $_SGLOBAL['db_slave']->query("SELECT a.*,b.firstrange,b.secondrange FROM ".tname('banner')." a LEFT JOIN ".tname('moneyrange')." b ON a.moneyrange=b.mrid WHERE a.category!=6 ORDER BY a.dateline DESC LIMIT 0, 100");
    $loglist = array();
    $pricearr=array();
    while ($value = $_SGLOBAL['db_slave']->fetch_array($query)) {
        if ($value['available'] == 1) {
            if(!empty($value['moneyrange'])){
                if($valueContact['realmoney']<$value['firstrange']||$valueContact['realmoney']>$value['secondrange']){
                    continue;
                }
            }
            if($value['category']==5){
                continue;
            }
    		if ($value['gid'] != 0) {
    		    if (strstr($valueContact['event'], ",".$value['gid'].",")) {
    		        $loglist[] = $value;
    		    } 
    		} else {
    		    $loglist[] = $value;
    		}
        }
    }
    $result['list'] = $loglist;
    echo json_encode($result);
}elseif($op=="category"){
    $bid=intval($_POST['bid']);
    $categoryQuery=$_SGLOBAL['db']->query("SELECT action,category FROM ".tname("banner")." WHERE bid=".$bid);
    $categoryContent=$_SGLOBAL['db']->fetch_row($categoryQuery);
    if(!empty($categoryContent)){
        $result['action']=$categoryContent[0];
        $result['category']=$categoryContent[1];
    }else{
        $result["errormsg"]="error";
        $result["errorcode"]="1";
    }
    echo json_encode($result);
}elseif($op=="gettoken"){
    $redis=getRedis();
    $userToken=$redis->get("userToken".$uid);
    if($userToken==false){
        $token=$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT token FROM ".tname('space')." WHERE uid=".$uid));
        if(!empty($token)){
            $rate=rand(1,9);
            $redis->set("userToken".$uid,$token."|".$rate);
        }
    }else{
        $tokenarr=implode("|",$userToken);
        $token=$tokenarr[0];
    }
    if(empty($token)){
        $rand=rand(1,10000);
        $token=md5($uid.time().$rand);
        $rate=rand(1,9);
        $_SGLOBAL['db']->query("UPDATE ".tname("space")." SET token='".$token."' WHERE uid=".$uid);
        $redis->set("userToken".$uid,$token."|".$rate);
    }
    $result['token']=$token;
    echo json_encode($result);
}elseif($op=='gettreasure'){
    $result['action']='http://app.sihemob.com/home/treasurelist.php';
    echo json_encode($result);
}elseif($op=='getredis'){
    $redis=getRedis();
    $gid=$_POST['gid'];
    $result['count']=$redis->get('treasureCount'.$gid);
    echo json_encode($result);
}elseif($op=='setredis'){
    $redis=getRedis();
    $gid=$_POST['gid'];
    $moneylogTableNames = getMoneylogTableName(strtotime("2016-05-02"));
    $initCount = 0;
    foreach ($moneylogTableNames as $moneylogTableName)
    {
        $initCount+=$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(*) FROM ".tname($moneylogTableName)." WHERE moneylogtypecategory=51 AND moneylogtaskid=".$gid));
    }
    $redis->set('treasureCount'.$gid,0);
    $redis->incrBy('treasureCount'.$gid,$initCount);
    $result['count']=$redis->get('treasureCount'.$gid);
    echo json_encode($result);
}
?>
