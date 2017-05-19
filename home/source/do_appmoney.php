<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_ajax.php 12535 2009-07-06 06:22:34Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
        exit('Access Denied');
}

include_once(S_ROOT.'./source/function_cp.php');
$uid = empty($_POST['uid'])?0:intval($_POST['uid']);
$op = empty($_POST['op'])?'':$_POST['op'];
$lat = empty($_GET['lat'])?'':$_GET['lat'];
$lng = empty($_GET['lng'])?'':$_GET['lng'];
$token=empty($_POST['token'])?'':$_POST['token'];
$deviceId = empty($_POST['deviceid'])?'':$_POST['deviceid'];
//writeLogKang("------------ deviceid: ".$deviceId);
$result = array(
    'errcode' => 0,
    'errmsg' => 'success'
);
$_SGLOBAL['supe_uid'] = $uid;
$channel = $_POST['channel'];
//writeLogKang("--------------- do app money op:".$op."-------- uid:".$uid."------channel:".$channel);
if ($op == "getmoneyrolllist") {
  //  writeLogKang(">>>>>>>>>>>>>>> getmoneyrolllist");
    $userlist = array();
    $usera['uid'] = 2;
    $usera['name'] = "测试1";
    $usera['avatarurl'] = "http://app.sihemob.com/data/avatar/000/16/72/59_avatar_middle.jpg";
    $usera['moneytips'] = "领取了5元钱";

    $userb['uid'] = 155798;
    $userb['name'] = "测试2";
    $userb['avatarurl'] = "http://app.sihemob.com/data/avatar/000/15/57/98_avatar_middle.jpg";
    $userb['moneytips'] = "做了个任务，领取了10元钱";

    $userc['uid'] = 170371;
    $userc['name'] = "测试2";
    $userc['avatarurl'] = "http://app.sihemob.com/data/avatar/000/17/03/71_avatar_middle.jpg";
    $userc['moneytips'] = "签到，领取了1元钱";

    $moneylogTableNames = getMoneylogTableName(strtotime("2016-05-02"));
    foreach ($moneylogTableNames as $moneylogTableName)
    {
        $queryLog = $_SGLOBAL['db']->query("SELECT * FROM `".tname($moneylogTableName)."` where moneylogtypecategory=11 ORDER BY moneylogid DESC limit 3");
        if (!empty($queryLog)) break;
    }
    while ($valueLog = $_SGLOBAL['db']->fetch_array($queryLog)) {
    	$notice['uid'] = $valueLog['moneyloguid'];
    	$notice['name'] = $valueLog['moneylogusername'];
    	$notice['avatarurl'] = $valueLog['moneyloguserphoto'];
    	if ($valueLog['moneylogtypecategory'] != 100) {
    	    $notice['moneytips'] = $valueLog['moneylogtip'];
    	} else {
            $notice['moneytips'] = $valueLog['moneylogtip']." 赚取了".($valueLog['moneylogamount']/100)."元";
    	}
    	$userlist[] = $notice;
    }
    $result['userlist'] = $userlist;

    $queryContact = $_SGLOBAL['db']->query('SELECT totalmoney, realmoney FROM '.tname('space')." WHERE uid = '$uid'");
    $valueMoney = $_SGLOBAL['db']->fetch_array($queryContact);
    $result['totalmoney'] = $valueMoney['totalmoney'];
    $result['realmoney'] = $valueMoney['realmoney'];
    $result['verifyingmoney'] = $result['totalmoney'] - $result['realmoney'];

    $getmoney['tip'] = "赚奖金";
    $getmoneytype = array();
    $query = $_SGLOBAL['db']->query("SELECT a.*,b.firstrange,b.secondrange FROM ".tname('moneytype')." a LEFT JOIN ".tname('moneyrange')." b ON a.moneyrange=b.mrid WHERE a.moneytypeavailable=1 and a.moneytypecategory in(0,1,2,3) ORDER BY a.dateline DESC LIMIT 15");
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
    	if (!empty($value['moneytypefilter']) && strstr($value['moneytypefilter'], $channel)) {
    	    continue;
    	}
    	if ($_POST['appversion'] < "2.3.0" && $value['moneytypecategory'] == 3) {
    	    continue;
    	}
    	/*if (!empty($value['moneytypestarttime']) && $_POST['appversion'] < "2.3.0"){
    	    continue;
    	}*/
    	if($value['moneytypecategory'] == 2&&$value["taskcategory"]!=0){
    	    continue;
    	}
    	$appvalue['moneytypeid'] = $value['moneytypeid'];
    	$appvalue['moneytypecategory'] = $value['moneytypecategory'];
    	$appvalue['moneytypename'] = $value['moneytypename'];
    	$appvalue['moneytypestatustip'] = "";
    	$appvalue['moneytypedes'] = $value['moneytypedes'];
    	$appvalue['moneytypebtn'] = $value['moneytypebtn'];
    	$appvalue['moneytypeiconurl'] = $value['moneytypeiconurl'];
        $appvalue['moneytypestarttime'] = $value['moneytypestarttime'];
        $appvalue['moneytypeendtime'] = $value['moneytypeendtime'];
        if(!empty($value['moneyrange'])){
            continue;
        }
    	/*if ($appvalue['moneytypeid'] == 14) {
    	    if ($uid == 20515 || $uid == 1303) {} else {
    		continue;
    	    }
    	
    	}*/
    
    	if ($value['moneytypecategory'] == 0 || $value['moneytypecategory'] == 3) {
    	    $typeid = $value['moneytypeid'];
            $valueMoneyLog = '';
    	    foreach ($moneylogTableNames as $moneylogTableName)
            {
                $queryMoneyLog = $_SGLOBAL['db']->query("SELECT dateline,moneylogtypecategory FROM `".tname($moneylogTableName)."` WHERE moneylogtypeid=$typeid and moneyloguid=$uid order by moneylogid desc  LIMIT 1");
                $valueMoneyLog=$_SGLOBAL['db']->fetch_array($queryMoneyLog);
                if (!empty($valueMoneyLog)) break;
            }
    	    if ($valueMoneyLog) {
        		if ($valueMoneyLog['moneylogtypecategory'] == 41) {
        		    continue;
        		}
            	$dateline = $valueMoneyLog['dateline'];
            	$today = strtotime(sgmdate('Y-m-d'));
            	if ($dateline > $today) {
    		         $appvalue['moneytypestatustip'] = "已".$value['moneytypebtn'];
    		    }
    	    }
    	}
    	$getmoneytype[] = $appvalue;
    }
    $getmoney['moneytypelist'] = $getmoneytype;
    $result['getmoney'] = $getmoney;
    echo json_encode($result);
} else if ($op == "getmoneylogbyid"){
    //moneylogtypeid
    $moneytypeid = empty($_POST['moneytypeid'])?'':$_POST['moneytypeid'];
    if (empty($uid) || empty($moneytypeid) ) {
        $result['errcode'] = 1001;
        $result['errmsg'] = "参数错误，请稍候再试";
        echo json_encode($result);
        return;
    }


        $wherearr = array();
        $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
        $dateline = empty($_POST['dateline'])?'0':$_POST['dateline'];
        if($isRefresh == "no"){
            $wherearr[] = " dateline < ".$dateline;
        }
	$wherearr[] = "moneylogtypeid=$moneytypeid";
	$wherearr[] = "moneylogtypecategory=0";
	$moneylogTableNames = getMoneylogTableName(strtotime("2016-05-02"));
	$count = 0;
	foreach ($moneylogTableNames as $moneylogTableName)
    {
        $queryLog = $_SGLOBAL['db']->query("SELECT * FROM `".tname($moneylogTableName)."` where  ".implode(" AND ", $wherearr) ."  ORDER BY moneylogid DESC limit 10");
        while ($valueLog = $_SGLOBAL['db']->fetch_array($queryLog)) {
            $notice['uid'] = $valueLog['moneyloguid'];
            $notice['name'] = $valueLog['moneylogusername'];
            $notice['avatarurl'] = $valueLog['moneyloguserphoto'];
            $notice['dateline'] = $valueLog['dateline'];
            $notice['moneytips'] = "+".($valueLog['moneylogamount']/100)."元";
            $userlist[] = $notice;
            $count++;
        }

        if ($count>=10) break;
    }
    $result['userlist'] = array_slice($userlist,0,10);
echo json_encode($result);
        return;
} else if($op == "getmoneybycategory") {
    $category = empty($_POST['category'])?'-1':$_POST['category'];
    $moneytypeid = empty($_POST['moneytypeid'])?'':$_POST['moneytypeid'];
    if (empty($uid) || empty($moneytypeid)) {
    	$result['errcode'] = 1001;
        $result['errmsg'] = "参数错误，请稍候再试";
    	echo json_encode($result);
    	return;
    }
    $msg = "成功领取0.5元";
    $redis = getRedis();
    writeLogKang("aaaaaaaaaaaa".$uid."=====".($redis->get("getmoney".$uid)));
    if ($redis->get("getmoney".$uid)) {
	    writeLogKang("aaaaaaaaaaaabbbbbbbbb".$uid);
        $result['errcode'] = 1004;
        $result['errmsg'] = "领取太频繁，请稍后再试！";
        echo json_encode($result);
        return;
    }
    $redis->setex("getmoney".$uid, 5, "getmoney".$uid);
    writeLogKang("bbbbbbbbbbbbbbb".$uid);
    $moneylogTableNames = getMoneylogTableName(strtotime("2016-05-02"));
    $value = '';
    foreach ($moneylogTableNames as $moneylogTableName)
    {
        $query = $_SGLOBAL['db']->query("SELECT dateline,moneylogtypecategory FROM `".tname($moneylogTableName)."` WHERE moneylogtypeid=$moneytypeid and moneyloguid=$uid order by moneylogid desc LIMIT 1");
        $value=$_SGLOBAL['db']->fetch_array($query);
        if (!empty($value)) break;
    }
    writeLogKang("cccccccccccc".$uid);
    $canget = true;
    if ($value) {
    	if ($value['moneylogtypecategory'] == 41) {
    	    $redis->del("getmoney".$uid);
    	    $result['errcode'] = 1002;
            $result['errmsg'] = "这是一次性红包，你已经领取过了哦";
            echo json_encode($result);
            return;
    	}
    	$dateline = $value['dateline'];
    	$today = sstrtotime(sgmdate('Y-m-d'));
    	if ($dateline > $today) $canget = false;
    }
    if (!$canget) {
    	$redis->del("getmoney".$uid);
    	$result['errcode'] = 1002;
        $result['errmsg'] = "已经领取了，请明天再来吧";
        echo json_encode($result);
        return;
    }

    $queryMoneyType = $_SGLOBAL['db']->query('SELECT * FROM '.tname('moneytype')." WHERE moneytypeid = '$moneytypeid'");
    $valueMoneyType = $_SGLOBAL['db']->fetch_array($queryMoneyType);
   
    if (!$valueMoneyType) {
	//moneytypestarttime
    	$redis->del("getmoney".$uid);
    	$result['errcode'] = 1002;
        $result['errmsg'] = "红包领取错误，请稍后再试";
        echo json_encode($result);
        return;
    } 
   $startTime = $valueMoneyType['moneytypestarttime'];
   $endTime = $valueMoneyType['moneytypeendtime'];
   if (!empty($startTime) && !empty($endTime)) {
    	$currenttime = strtotime(date("H:i"));
    	$startTimemills = strtotime($startTime);
    	$endTimemills = strtotime($endTime);
    	if ($currenttime < $startTimemills || $currenttime > $endTimemills) {
    	    $redis->del("getmoney".$uid);
    	    $result['errcode'] = 1002;
            $result['errmsg'] = "请在规定的时间领取红包哦";
            echo json_encode($result);
            return;
    	}	
   }
    $isDayLimist = $valueMoneyType['moneytypedaylimit'];
    $today = sstrtotime(sgmdate('Y-m-d'));
    //$currentCount = $redis->get("getmoney".$moneytypeid.$today);

    if ($isDayLimist > 0 || $moneytypeid == 24) {
        $currentCount = $redis->incr("getmoney".$moneytypeid.$today);
    	if($currentCount == 1){
            $redis->expire("getmoney".$moneytypeid.$today, 86400);
    	}
    
    	if ($valueMoneyType['moneytypedaylimit'] > 0 && ($currentCount-0) > $valueMoneyType['moneytypedaylimit']) {
        	    //$today = sstrtotime(sgmdate('Y-m-d'));
        	    //$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('moneylog')." main WHERE moneylogtypeid=$moneytypeid AND dateline > $today "), 0);
        	    //if (($count-1) > $valueMoneyType['moneytypedaylimit']) {
                    $redis->del("getmoney".$uid);
                    $result['errcode'] = 1002;
                    $result['errmsg'] = "来晚了，红包已经领完了";
                    echo json_encode($result);
                    return;
        	    //}
    	}
    }
    $money = $valueMoneyType['moneytypemin'];
    $moneytypecategory = $valueMoneyType['moneytypecategory'];
    if ($moneytypecategory == 3) {
	   $moneytypecategory = 41;
    } else {
	   $moneytypecategory = 0;
    }
    $index = 0;
    $isGetChance = false;
    while($index < $valueMoneyType['moneytypeloop']) {
	    $random = rand(0, 100);
    	if ($random <  $valueMoneyType['moneytypechance']) {
    	    $isGetChance = true;
    	    break;
    	}
    	$money = $money + $valueMoneyType['moneytypeinterval'];
    	$index = $index +1;
    }
    if (!$isGetChance) {
	   $money = $valueMoneyType['moneytypemin'];
    }
    $queryContact = $_SGLOBAL['db']->query('SELECT name FROM '.tname('space')." WHERE uid = '$uid'");
    $valueMoney = $_SGLOBAL['db']->fetch_array($queryContact);

    $setarr = array(
    	'moneylogamount' => $money,
    	'moneyloguid' => $uid,
    	'moneylogusername' => $valueMoney['name'],
    	'moneyloguserphoto' => "http://app.sihemob.com/data/avatar/".avatar_file($uid, 'middle'),
    	'moneylogtypeid' => $moneytypeid,
    	'moneylogtypecategory' => $moneytypecategory,
    	'moneylogstatus' => 0,
    	'moneylogtaskid' => 0,
    	'moneylogtid' => 0,
    	'moneylogtip' => $valueMoneyType['moneytypename'],
        'dateline' => $_SGLOBAL['timestamp']
    );
    $moneylogTableNames = getMoneylogTableName();
    $moneylogid = inserttable($moneylogTableNames[0], $setarr, 1);
    if ($moneylogid < 1) {
	    $redis->del("getmoney".$uid);
        $result['errcode'] = 1003;
        $result['errmsg'] = "领取失败，请稍候再试";
        echo json_encode($result);
        return;
    }

    $setarrs = array();
    $setarrs['totalmoney'] = "totalmoney=totalmoney+$money";
    $setarrs['realmoney'] = "realmoney=realmoney+$money";
    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarrs)." WHERE uid='$uid'");
	

    if ($moneytypeid == 6) {
	include_once(S_ROOT.'./source/function_cp.php');	
	updatestat('moneysign');
    }
    //$redis->del("getmoney".$uid);
    $result['errmsg'] = "".($money/100)."";
    echo json_encode($result);


    fastcgi_finish_request();
    sendMsgByCategory(4, $uid, $moneytypeid);
} else if($op == "getmoneytypebyid"){
    $id = empty($_POST['moneytypeid'])?'0':$_POST['moneytypeid'];
    $queryContact = $_SGLOBAL['db']->query('SELECT * FROM '.tname('moneytype')." WHERE  moneytypeid = '$id'");
    $valueMoney = $_SGLOBAL['db']->fetch_array($queryContact);
    $today = sstrtotime(sgmdate('Y-m-d'));
    $moneylogTableNames = getMoneylogTableName($today);
    $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM `".tname($moneylogTableNames[0])."` main WHERE moneylogtypeid=$id AND dateline > $today "), 0);
    $valueMoney['moneytypedaycount'] = $count;
    if ($count >= $valueMoney['moneytypedaylimit']) {
	   $valueMoney['moneytypedone'] = 1;
    }
    
    if (!empty($valueMoney['moneytypetaskids'])) {
        $tmpTaskids = $valueMoney['moneytypetaskids'];
        $arrids = explode("|",$tmpTaskids);
        $idscount = count($arrids);
        $valueMoney['moneytypetaskid'] = $arrids[$uid%$idscount];
    }
    $result['moneytype_detail'] = $valueMoney;
    $result['invite_share_text'] = "邀请好友加入，最高同时获取{2}元";
	if (!empty($valueMoney['moneytypetaskid'])) {
            $result['task_detail'] = getTaskDetail($valueMoney['moneytypetaskid'], $uid);
        }
    $result['timestamp'] = time();
    echo json_encode($result); 

} else if($op == "getmoneylog") {
    $wherearr = array();
    $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
    $dateline = empty($_POST['dateline'])?'0':$_POST['dateline'];
    $wherearr[] = " moneyloguid = ".$uid;
    if($isRefresh == "no"){
        $wherearr[] = " dateline < ".$dateline;
    }else{
        $nowdaytime = time();//sgmdate('Ymd', time());
        $sepdateline = $nowdaytime - 30*24*3600;
        $wherearr[] = " dateline > $sepdateline ";
    }
    /*if (true) {
        $result['errcode'] = 1003;
        $result['errmsg'] = "查询人数过多，请稍候再试";
        echo json_encode($result);
        return;
    }*/
    $getmoneytype = array();
    $moneylogTableNames = getMoneylogTableName(strtotime("2016-05-02"));
    $count = 0;
    foreach ($moneylogTableNames as $moneylogTableName)
    {
        $query = $_SGLOBAL['db_slave']->query("SELECT moneylogid,moneylogamount,moneylogamounttype,moneylogtypecategory,moneylogstatus,moneylogtip,dateline FROM `".tname($moneylogTableName)."` WHERE ".implode(" AND ", $wherearr) ." ORDER BY moneylogid DESC limit 8");

        while ($value = $_SGLOBAL['db_slave']->fetch_array($query)) {
            if($value['moneylogtypecategory']!=51){
                if ($value['moneylogstatus'] == 0) {
                    $value['moneylogstatustip'] = "已发放";
                } else if ($value['moneylogstatus'] == 1) {
                    $value['moneylogstatustip'] = "处理中";
                } else if ($value['moneylogstatus'] == 2) {
                    $value['moneylogstatustip'] = "不合格";
                }
            }else{
                if ($value['moneylogstatus'] == 0) {
                    $value['moneylogstatustip'] = "已中奖";
                } else if ($value['moneylogstatus'] == 1) {
                    $value['moneylogstatustip'] = "未开奖";
                } else if ($value['moneylogstatus'] == 2) {
                    $value['moneylogstatustip'] = "未中奖";
                }
            }
            $value['moneylogadminuid'] = "92";
            $value['moneylogadminurl'] = "https://jinshuju.net/f/K2Dcrm";
            $getmoneytype[] = $value;
            $count++;
        }
        if ($count>=8) break;
    }
	writeLogKang("SELECT * FROM ".tname('moneylog')." WHERE ".implode(" AND ", $wherearr) ." ORDER BY dateline DESC limit 8");
    $result['getmoneylog'] = array_slice($getmoneytype, 0, 8);
    echo json_encode($result);
} else if($op == 'gettasklist') {
    $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
    $wherearr = array();
    $creditarr=array();
    $creditarr[]=$wherearr[] = "category = 21";
    $creditarr[]=$wherearr[] = "available = 1";
    $creditarr[]=$wherearr[] = "(count=0 OR count > counted)";
    $dateline = empty($_POST['dateline'])?'0':$_POST['dateline'];
    if($isRefresh == "no"){
        $wherearr[] = " dateline > ".$dateline;
    } 
    if($_POST['appversion'] >= "2.6.0"&&!empty($_POST["taskcategory"])){
        $wherearr[] = "taskcategory=".$_POST["taskcategory"];
    }
    $creditarr[]="taskrate=2";
    $conditionQuery=$_SGLOBAL['db']->query("SELECT gid FROM ".tname('creditgood')." WHERE ".implode(" AND ",$creditarr));
    $redis=getRedis();
    while($conditionResult=$_SGLOBAL['db']->fetch_array($conditionQuery)){
        $time=$redis->get("lastFinishTime".$uid.$conditionResult['gid']);
        if(!$time){
            $gidarr[]=$conditionResult['gid'];
        }
    }
    foreach($wherearr as $key => &$value){
        if($key!=2){
            $value='a.'.$value;
        }else{
            $value="(a.count=0 OR a.count > a.counted)";
        }
    }
    if(empty($gidarr)){
        $sql=") ";
    }else{
        $sql="AND moneylogtaskid not in(".implode(",",$gidarr).")) ";
    }
    $moneylogTableNames = getMoneylogTableName(strtotime("2016-05-02"));
    $query = $_SGLOBAL['db']->query("SELECT a.gid,a.moneyrange,a.submoney,a.showlabel,a.showlabelcolor,a.thirdmoney, a.dateline, a.title,a.category,a.chance,a.price,a.icon,a.image, a.fileurl, a.tasktype, a.count,a.counted,a.rate,a.available,b.firstrange,b.secondrange FROM ".tname('creditgood')." a LEFT JOIN ".tname('moneyrange')." b ON a.moneyrange=b.mrid WHERE ".implode(" AND ", $wherearr) . " AND a.gid not in( select moneylogtaskid from ".tname('moneylog_union')." where moneyloguid=$uid ".$sql."ORDER BY a.dateline ASC LIMIT 0, 14");
    writeLogError("SELECT gid, dateline, title,category,chance,price,icon,image, fileurl, count,counted,available FROM ".tname('creditgood')." WHERE  ".implode(" AND ", $wherearr) . "  ORDER BY dateline DESC LIMIT 0, 8");
    $loglist = array();
    $realmoney=$_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT realmoney FROM '.tname('space').' WHERE uid='.$uid));
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        //writeLogError("---------gettasklist:".$value['gid']."  uid:".$uid);
        if(!empty($value['moneyrange'])){
            if($realmoney<$value['firstrange']||$realmoney>$value['secondrange']){
                continue;
            }            
        }
        if ($value['gid'] == 44 || $value['gid'] == 45) {
             if ($uid != 2) {
                continue;
            }
        }
    	if($_POST['appversion'] < "2.6.0"&&$value[tasktype]==4){
    	    continue;
    	}
        $value['pricetips'] = (($value['price']+$value['submoney']+$value['thirdmoney'])/100)."元";
        $value['statustips'] = 1;
        $dayCount=$redis->get("totalCount".$value['gid']);
        if($dayCount==false){
            $dayCount=0;
        }
        if(!empty($value['count']) && $value['count'] > 0){
            if ($dayCount >= $value['count']) {
                $gid = $value['gid'];
                $goodCount = $value['count'];
                $_SGLOBAL['db']->query("UPDATE ".tname('creditgood')." SET counted=$goodCount WHERE gid='$gid'");
                continue;
            }
        }
	    if(!empty($value['rate'])){
	        $dayCountOther=$redis->get("rate".$value['gid']);
	        if($dayCountOther==false){
	            $dayCountOther=0;
	        }
	        if($value['rate']<=$dayCountOther){
	            continue;
	        }
	    }
        $value['tips'] = "已发放".($dayCount*10)."份奖金";
        writeLogError("************ gid:".$value['gid']);
        $loglist[] = $value;

    }

    $result['list'] = $loglist;
    echo json_encode($result);
}  else if($op == 'getexttasklist') {
    $wherearr=array();
    $wherearr[] = "a.category = 21";
    $wherearr[] = "a.available = 1";
    if($_POST['appversion'] >= "2.6.0"&&!empty($_POST["taskcategory"])){
        $wherearr[] = "a.taskcategory=".$_POST["taskcategory"];
    }
    $query = $_SGLOBAL['db']->query("SELECT a.gid,a.title,a.category,a.chance,a.price,a.icon,a.image, a.fileurl, a.count,a.counted,a.available,a.submills,a.thirdmills,a.submoney,a.thirdmoney,b.firstrange,b.secondrange FROM ".tname('creditgood')." a LEFT JOIN ".tname('moneyrange')." b ON a.moneyrange=b.mrid WHERE ".implode(" AND ", $wherearr)." ORDER BY a.dateline DESC LIMIT 0, 100");
    $loglist = array();
    $realmoney=$_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT realmoney FROM '.tname('space').' WHERE uid='.$uid));
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        if(!empty($value['moneyrange'])){
            if($realmoney<$value['firstrange']||$realmoney>$value['secondrange']){
                continue;
            }
        }
    	//writeLogError("---------gettasklist:".$value['gid']."  uid:".$uid);
        $moneylogTableNames = getMoneylogTableName(strtotime("2016-05-02"));
        $count = 0;
        foreach ($moneylogTableNames as $moneylogTableName)
        {
            $count += getcount($moneylogTableName, array('moneyloguid'=>$uid, 'moneylogtaskid'=>$value['gid']));
        }
    	writeLogError("????????????? count:".$count);
    	if ($count == 0) continue;	

        if (($value['submills'] > 0 && $count < 2) || ($value['thirdmills'] > 0 && $count < 3)) {
	    $value['pricetips'] = "+".(($value['submoney']+$value['thirdmoney'])/100)."元";
	    $value['statustips'] = 1;
	    //$count = getcount('moneylog', array('moneylogtaskid'=>$value['gid']));
	    //$value['tips'] = "已发放".($count*10)."份奖金";
	    $value['tips'] = "继续体验，获得额外奖励";
	    writeLogError("************ gid:".$value['gid']);
            $loglist[] = $value;
        }

    }

    $result['list'] = $loglist;
    echo json_encode($result);
} else if($op == 'getmoneygoods') {
    $query = $_SGLOBAL['db']->query("SELECT gid,title,category,chance,price,icon,count,counted,rate,available FROM ".tname('creditgood')." WHERE category=11 ORDER BY dateline DESC LIMIT 0, 20");
    $loglist = array();
    $redis=getRedis();
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        if ($value['available'] == 1) {
            if($_POST['appversion']>='2.6.2'){
                $dayCount=$redis->get('buy'.$value['gid'].'today');
                if($dayCount){
                    $value['goodCount']=$dayCount;
                }else{
                    $value['goodCount']=0;
                }
            }
            $loglist[] = $value;
        }
    }
    $result['list'] = $loglist;
    echo json_encode($result);
} else if($op == "gettaskbyid") {
    $cid = empty($_POST['gid'])?'0':$_POST['gid'];
    $valueMoney = getTaskDetail($cid, $uid);
    $result['detail'] = $valueMoney;
    echo json_encode($result);
}  else if($op == "candotask") {

        $taskid = empty($_POST['taskid'])?'0':$_POST['taskid'];
        $taskstep = empty($_POST['step'])?'0':$_POST['step'];

        $queryContact = $_SGLOBAL['db']->query('SELECT * FROM '.tname('creditgood')." WHERE gid = '$taskid'");
        $valueMoney = $_SGLOBAL['db']->fetch_array($queryContact);
        if (!$valueMoney) {
                    $result['errcode'] = 1003;
                    $result['errmsg'] = "任务不存在";
                    echo json_encode($result);
                    return;

        } else {
	    if ($taskstep == 2) {
		if ($valueMoney['submills'] <= 0) {
		    $result['errcode'] = 1003;
                    $result['errmsg'] = "此任务不存在存在";
                    echo json_encode($result);
                    return;
		}
	    }
            if ($taskstep == 3) {
                if ($valueMoney['thirdmills'] <= 0) {
                    $result['errcode'] = 1003;
                    $result['errmsg'] = "此任务不存在存在";
                    echo json_encode($result);
                    return;
                }
            }

	}
	$subinterval = $queryContact['subinterval'];//86400;
	$thirdinterval = $queryContact['thirdinterval'];//86400;
	if ($subinterval < 1) {
	    $subinterval = 86400;
	}
	if ($thirdinterval < 1) {
	    $thirdinterval = 86400;
	}
	writeLogError("*****************************%%%%%%%%%%%%%%%%%%%");
	if ($taskstep == 2) {
	    writeLogError("===================");
	    $moneylogTableNames = getMoneylogTableName(strtotime("2016-05-02"));
        $valueMoneyLog = '';
	    foreach ($moneylogTableNames as $moneylogTableName)
        {
            $queryMoneyLog = $_SGLOBAL['db']->query("SELECT dateline FROM `".tname($moneylogTableName)."` WHERE moneylogtaskid=$taskid and moneyloguid=$uid ORDER BY moneylogid ASC  LIMIT 1");
            $valueMoneyLog=$_SGLOBAL['db']->fetch_array($queryMoneyLog);
            if (!empty($valueMoneyLog)) break;
        }

            
	    writeLogError("=================== 22222222 ");
	    if ($valueMoneyLog) { 
		writeLogError("=================== 333333333 ");
		$timeA = time() - intval($valueMoneyLog["dateline"]);
		writeLogError("==============||||||||||||||| time:".$timeA);
		if ($timeA < $subinterval) {
		    $result['errcode'] = 1003;
                    $result['errmsg'] = "还没达到做任务时间";
                    echo json_encode($result);
                    return;
		}
	    } else {
		writeLogError("|||||||||||||||++++++++ time:".$time);
                    $result['errcode'] = 1003;
                    $result['errmsg'] = "请先完成上一步任务";
                    echo json_encode($result);
                    return;
	    }
	}

        if ($taskstep == 3) {
            $moneylogTableNames = getMoneylogTableName(strtotime("2016-05-02"));
            $valueMoneyLog = '';
            foreach ($moneylogTableNames as $moneylogTableName)
            {
                $queryMoneyLog = $_SGLOBAL['db']->query("SELECT dateline FROM `".tname($moneylogTableName)."`  WHERE moneylogtaskid=$taskid and moneyloguid=$uid and moneylogstep=2 ORDER BY moneylogid ASC  LIMIT 1");
                $valueMoneyLog=$_SGLOBAL['db']->fetch_array($queryMoneyLog);
                if (!empty($valueMoneyLog)) break;
            }
            if ($valueMoneyLog) {
                $time = time() - $valueMoneyLog["dateline"];
                if ($time < $thirdinterval) {
                    $result['errcode'] = 1003;
                    $result['errmsg'] = "还没达到做任务时间";
                    echo json_encode($result);
                    return;
                }
            } else {
                    $result['errcode'] = 1003;
                    $result['errmsg'] = "请先完成上一步任务";
                    echo json_encode($result);
                    return;
            }
        }
                    $result['errcode'] = 0;
                    $result['errmsg'] = "Success";
                    echo json_encode($result);

} else if($op == "dotaskbyid") {
    $result['token']=check_token($uid, $token);
	//writeLogKang("--------------- dotaskbyid--------------- uid:".$uid);
	$taskid = empty($_POST['taskid'])?'0':$_POST['taskid'];
	$taskstep = empty($_POST['step'])?'0':intval($_POST['step']);
	$pkg = empty($_POST['pkg'])?'':$_POST['pkg'];    
	if ($taskstep <0 || $taskstep > 3) {
        $result['errcode'] = 1003;
        $result['errmsg'] = "任务不存在";
        echo json_encode($result);
        return;
    }
    $queryContact = $_SGLOBAL['db']->query('SELECT * FROM '.tname('creditgood')." WHERE gid = '$taskid'");
    $valueMoney = $_SGLOBAL['db']->fetch_array($queryContact);
	if (!$valueMoney) {
        $result['errcode'] = 1003;
        $result['errmsg'] = "任务不存在";
        echo json_encode($result);
        return;
	}
	//writeLogKang(">>> taskid:".$taskid." --- pkg:".$pkg." --- taskstep:".$taskstep);
	writeLogError("^^^^^^^^^^^^^^^^^11111111111 ".$uid."|".$taskid."|".$pkg);
	if (!empty($pkg)) {
		writeLogError("^^^^^^^^^^^^^^^^^222222222222");
        $setarr = array(
            'uid' => $uid,
            'gid' => $taskid,
            'lotteryfail' => 1,
            'dateline' => $_SGLOBAL['timestamp']
        );
		writeLogError("^^^^^^^^^^^^^^^^^.....2222222222......... ");
        $insertT =  inserttable('creditgoodlog', $setarr);
		writeLogError("^^^^^^^^^^^^^^^^^3333333333333333333 : ".$insertT);
		$result['errcode'] = 0;
        $result['errmsg'] = "success";
        echo json_encode($result);
        return;
	}
	$money = $valueMoney['price'];
	$title = "任务奖励-".$valueMoney['title'];
	if ($taskstep == 2) {
	    $title = "任务奖励-".$valueMoney['title']."-额外奖励".($taskstep-1);
        $money = $valueMoney['submoney'];
	} else if($taskstep == 3) {
	    $title = "任务奖励-".$valueMoney['title']."-额外奖励".($taskstep-1);
	    $money = $valueMoney['thirdmoney'];
	}
	//$random = rand(0, 65);
	//usleep($random * 100 * 1000);
	$redis = getRedis();
	$currentCount = $redis->incr("dotaskbyid".$taskid.$uid.$taskstep);
    if($currentCount == 1){
        $redis->expire("dotaskbyid".$taskid.$uid.$taskstep, 3600);
    }
    $moneylogTableNames = getMoneylogTableName(strtotime("2016-05-02"));
    $countOld = getcount('moneylog_union', array('moneyloguid'=>$uid, 'moneylogtaskid'=>$taskid, 'moneylogstep'=>$taskstep));

	/* $query=$_SGLOBAL['db']->query("SELECT taskrate FROM ".tname('creditgood')." WHERE gid=".$taskid);
	$value=$_SGLOBAL['db']->fetch_row($query); */
    if($valueMoney['taskrate']!=2){
        if ($countOld > 0 ||  $currentCount > 1) {
             $result['errcode'] = 0;
             $result['errmsg'] = "success";
             echo json_encode($result);
             return;
    	}
    }else{
        $logvalue = '';
        foreach ($moneylogTableNames as $moneylogTableName)
        {
            $logquery=$_SGLOBAL['db']->query("SELECT dateline FROM `".tname($moneylogTableName)."` WHERE moneylogtaskid=".$taskid." AND moneyloguid=".$uid." AND moneylogstep=".$taskstep." ORDER BY dateline desc LIMIT 1");
            $logvalue=$_SGLOBAL['db']->fetch_row($logquery);
            if (!empty($logvalue)) break;
        }

        if($logvalue[0]>=strtotime(date("Y-m-d"))){
            $result['errcode'] = 0;
            $result['errmsg'] = "success";
            echo json_encode($result);
            return;
        }
    }
    if($taskstep==0||$taskstep==1){
        $redis->set("lastFinishTime".$uid.$taskid,time());
        $redis->expire("lastFinishTime".$uid.$taskid,strtotime(date("Y-m-d")."+1 days")-time());
    }
    if($taskstep!=2&&$taskstep!=3){
        $count=$redis->incr("rate".$taskid);
        if($count==1){
            $redis->expire("rate".$taskid,strtotime(date("Y-m-d")."+1 days")-time());
        }
        $redis->incr("totalCount".$taskid);
    }
    $uid = $_SGLOBAL['supe_uid'];
    $setarr = array(
        'moneylogamount' => $money,
        'moneylogamounttype' => 0,
        'moneyloguid' => $_SGLOBAL['supe_uid'],
        'moneylogusername' => $_SGLOBAL['supe_username'],
        'moneyloguserphoto' => "http://app.sihemob.com/data/avatar/".avatar_file($uid, 'middle'),
        'moneylogtypeid' => 9,
        'moneylogtypecategory' => 21,
        'moneylogstatus' => 0,
        'moneylogtaskid' => $taskid,
		'moneylogstep' => $taskstep,
        'moneylogtid' => 0,
        'moneylogtip' => $title,
        'dateline' => $_SGLOBAL['timestamp']
    );
    $moneylogTableNames = getMoneylogTableName();
    $moneylogid = inserttable($moneylogTableNames[0], $setarr, 1);
    $setarrs = array();
    $setarrs['totalmoney'] = "totalmoney=totalmoney+$money";
	$setarrs['realmoney'] = "realmoney=realmoney+$money";
    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarrs)." WHERE uid='$uid'");
    include_once(S_ROOT.'./source/function_cp.php');
    updatestat('moneytask');
    $result['errcode'] = 0;
    $result['errmsg'] = "success";
    echo json_encode($result);
    return;
} else if($op == "getgoodbyid") {
    $cid = empty($_POST['gid'])?'0':$_POST['gid'];
    $queryContact = $_SGLOBAL['db']->query('SELECT * FROM '.tname('creditgood')." WHERE gid = '$cid'");
    $valueMoney = $_SGLOBAL['db']->fetch_array($queryContact);
    $result['detail'] = $valueMoney;
    echo json_encode($result);
} else if($op == "buygood") {
        $result['token']=check_token($uid, $token);
        $random = rand(0, 65);
        usleep($random * 100 * 1000);
        $result['errcode'] = 1001;
        $gid = empty($_POST['gid'])?0:intval($_POST['gid']);
        $space = getspace($uid);
        if (empty($space)) {
            $result['errcode'] = 1003;
            $result['errmsg'] = "平台核算中，请稍后再试";
            echo json_encode($result);
            return;
        }
    	if (empty($space['mobile'])) {
    	    $result['errcode'] = 1003;
            $result['errmsg'] = "您未完成手机验证，请升级至最新版后兑换。";
            echo json_encode($result);
            return;
    	}
    	$redis = getRedis();
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditgood')." WHERE gid = $gid ORDER BY dateline DESC LIMIT 1");
        if ($value = $_SGLOBAL['db']->fetch_array($query)) {
            if ($value['available'] == 1) {
        		if ($redis->get("buy".$uid)) {
        			$result['errcode'] = 1004;
                    $result['errmsg'] = "每分钟限定兑换1次，请稍后再试！";
                    echo json_encode($result);
                    return;
        		}
		        $buymoneyhistory = $redis->incr("buymoney".$uid);
            	if($buymoneyhistory == 1){
                    $redis->expire("buymoney".$uid, 1600);
            	}
		        if ($buymoneyhistory > 1) {
                    $result['errcode'] = 1004;
                    $result['errmsg'] = "兑换太频繁，请稍后再试！";
                    echo json_encode($result);
                    return;
                }
		        $redis->setex("buy".$uid, 60, "buy".$uid);
                $_SGLOBAL['db']->query("UPDATE ".tname('creditgood')." SET uncounted=uncounted+1 WHERE gid='$gid'");
                if($value['count'] < ($value['counted'] + 1)) {
                    $result['errmsg'] = "来晚了，奖品已经被领光了，请等待管理员补仓！";
                    echo json_encode($result);
                    return;
                } else {
                    $rateDay=$redis->get('buy'.$gid.'today');
                    if($rateDay){
                        if(!empty($value['chance'])){//2.6.2版本新上，会有很多商品还没日限量
                            if($value['chance']<$rateDay){
                               $redis->del("buy".$uid);
                               $redis->del("buymoney".$uid);
                               $result['errcode'] = 1004;
                               $result['errmsg'] = "来晚了，奖品今日限量已经被领光了！";
                               echo json_encode($result);
                               return;
                            }
                        }   
                    }
                    if($value['price'] > $space['realmoney']) {
            			$redis->del("buy".$uid);
            			$redis->del("buymoney".$uid);
                        $result['errcode'] = 1010;
                        $result['errmsg'] = "余额不足，快去做做任务吧！";
                        echo json_encode($result);
                        return;
                    }
		            if ($value['rate'] > 0) {
        		        $today = sstrtotime(sgmdate('Y-m-d'));
        		        $moneylogTableNames = getMoneylogTableName();
            		    $countDay = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM `".tname($moneylogTableNames[0])."` main WHERE moneylogtypeid=$gid AND moneyloguid=$uid AND dateline > $today "), 0);
            			if (($countDay+1) > $value['rate']) {
                            $redis->del("buy".$uid);
			                $redis->del("buymoney".$uid);
                            $result['errcode'] = 1004;
                            $result['errmsg'] = "兑换太频繁，该商品每天限兑换".$value['rate']."次！";
                            echo json_encode($result);
                            return;
            			}
		            }
                    $money = -$value['price'];
        		    $queryContact = $_SGLOBAL['db']->query('SELECT name FROM '.tname('space')." WHERE uid = '$uid'");
        		    $valueMoney = $_SGLOBAL['db']->fetch_array($queryContact);
		            writeLogKang("============================================".$deviceId);
		            $setarrs = array();
		            $setarrs['totalmoney'] = "totalmoney=totalmoney+$money";
		            $setarrs['realmoney'] = "realmoney=realmoney+$money";
		            $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarrs)." WHERE uid='$uid' AND realmoney>=-$money");
		            if($_SGLOBAL['db']->affected_rows()){
    		            $_SGLOBAL['db']->query("UPDATE ".tname('creditgood')." SET counted=counted+1 WHERE gid='$gid'");
            		    $setarr = array(
                		'moneylogamount' => $money,
        			    'moneylogamounttype' => 1,
                		'moneyloguid' => $uid,
                		'moneylogusername' => $valueMoney['name'],
                		'moneyloguserphoto' => "http://app.sihemob.com/data/avatar/".avatar_file($uid, 'middle'),
        			    'moneylogtypeid' => $gid,
                		'moneylogtypecategory' => 11,
                		'moneylogstatus' => 1,
                		'moneylogtaskid' => 0,
                		'moneylogtid' => 0,
                		'moneylogtip' => "兑换 ".$value['title'],
            			'deviceid' => $deviceId,
            			'qq' => $space['qq'],
                		'dateline' => $_SGLOBAL['timestamp']
            		    );
                        $moneylogTableNames = getMoneylogTableName();
            		    $moneylogid = inserttable($moneylogTableNames[0], $setarr, 1);
            		    if ($moneylogid < 1) {
            		        $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET totalmoney=totalmoney-$money,realmoney=realmoney-$money WHERE uid='$uid'");
            		        $_SGLOBAL['db']->query("UPDATE ".tname('creditgood')." SET counted=counted-1 WHERE gid='$gid'");
            		        $redis->del("buy".$uid);
                			$redis->del("buymoney".$uid);
                    		$result['errcode'] = 1003;
                    		$result['errmsg'] = "兑换失败，请稍候再试";
                    		echo json_encode($result);
                    		return;
            		    }
            		    $goodRate=$redis->incr('buy'.$gid.'today');
            		    if($goodRate==1){
            		        $redis->expire('buy'.$gid.'today',strtotime(date('Y-m-d')."+1 days")-time());
            		    }
            		    $redis->del("buymoney".$uid);
            		    $result['errcode'] = 0;
                        $result['errmsg'] = "恭喜你，兑换成功！\n 奖品将直接发放至您的QQ，请注意查收。";
            		    echo json_encode($result);
            		    fastcgi_finish_request();
            		    $lastMonth = strtotime("last month");
            		    $log = "";
            		    $moneylogTableNames = getMoneylogTableName($lastMonth);
            		    if (!empty($deviceId)) {
                            $valueDeviceIdCount = '';
            		        foreach ($moneylogTableNames as $moneylogTableName)
                            {
                                $queryDeviceId = $_SGLOBAL['db']->query('SELECT moneyloguid FROM `'.tname($moneylogTableName)."` WHERE moneylogtypecategory=11 and deviceid = '$deviceId' and dateline > $lastMonth order by moneylogid asc limit 1");
                                $valueDeviceIdCount = $_SGLOBAL['db']->fetch_array($queryDeviceId);
                                if ($valueDeviceIdCount && $valueDeviceIdCount['moneyloguid'] != $uid) {
                                    $_SGLOBAL['db']->query("UPDATE `".tname($moneylogTableName)."` SET devicecount =1  WHERE moneylogid='$moneylogid'");
                                    $log = $log."uid:".$uid." logid:".$moneylogid." >>>>>> device:".$deviceId."  !=  last uid:".$valueDeviceIdCount['moneyloguid']."  #####   ";
                                }
                                if (!empty($valueDeviceIdCount)) break;
                            }
            		    }
            		    $qq = $space['qq'];
                        if (!empty($qq)) {
                            foreach ($moneylogTableNames as $moneylogTableName)
                            {
                                $queryQQ = $_SGLOBAL['db']->query('SELECT moneyloguid FROM `'.tname($moneylogTableName)."` WHERE moneylogtypecategory=11 and  qq = '$qq' and dateline > $lastMonth order by moneylogid asc limit 1");
                                $valueQQ = $_SGLOBAL['db']->fetch_array($queryQQ);
                                if ($valueQQ && $valueQQ['moneyloguid'] != $uid) {
                                    $_SGLOBAL['db']->query("UPDATE `".tname($moneylogTableName)."` SET qqcount =1  WHERE moneylogid='$moneylogid'");
                                    $log = $log."uid:".$uid." logid:".$moneylogid." >>>>>>> qq:".$qq."  !=  last uid:".$valueQQ['moneyloguid'];
                                }
                            }
                        }
                        $maxquery=$_SGLOBAL['db']->query("SELECT MAX(moneylogtypecategory) AS max FROM `".tname($moneylogTableNames[0])."` WHERE moneyloguid=".$uid." AND moneylogtypecategory!=11 GROUP BY moneyloguid ORDER BY dateline desc LIMIT 20");
                        $maxresult=$_SGLOBAL['db']->fetch_array($maxquery);
                        $minquery=$_SGLOBAL['db']->query("SELECT MIN(moneylogtypecategory) AS min FROM `".tname($moneylogTableNames[0])."` WHERE moneyloguid=".$uid." AND moneylogtypecategory!=11 GROUP BY moneyloguid ORDER BY dateline desc LIMIT 20");
                        $minresult=$_SGLOBAL['db']->fetch_array($minquery);
                        if($maxresult['max']!=$minresult['min']){
                            if($handle=fopen(ISAUTO,"r")){
                                $isAuto=fread($handle,filesize(ISAUTO));
                                fclose($handle);
                            }
                            foreach ($moneylogTableNames as $moneylogTableName)
                            {
                                $autoquery=$_SGLOBAL['db']->query("SELECT devicecount,qqcount FROM `".tname($moneylogTableName)."`WHERE moneylogid=".$moneylogid);
                                $autoresult=$_SGLOBAL['db']->fetch_row($autoquery);
                                if (!empty($autoresult)) break;

                            }
                            if($isAuto=='1'&&empty($autoresult[0])&&empty($autoresult[1])){
                                $sexquery=$_SGLOBAL['db']->query("SELECT sex FROM ".tname('spacefield')." WHERE uid=".$uid);
                                $sexresult=$_SGLOBAL['db']->fetch_array($sexquery);
                                foreach ($moneylogTableNames as $moneylogTableName)
                                {
                                    $moneylogquery=$_SGLOBAL['db']->query("SELECT * FROM `".tname($moneylogTableName)."` WHERE moneylogid=".$moneylogid);
                                    $moneylogresult=$_SGLOBAL['db']->fetch_array($moneylogquery);
                                    if(!empty($moneylogresult)) break;
                                }

                                if($sexresult['sex']!=0&&!empty($moneylogresult)){
                                    $insertsql="INSERT INTO ".tname("autoaward")."(autoawardmid,autoawardstatus,dateline) values ('";
                                    if($handle=fopen(AUTO,"r")){
                                        $autoarr=explode("&",fread($handle,filesize(AUTO)));
                                        fclose($handle);
                                    }
                                    $autocount=count($autoarr);
                                    $autostatus=0;
                                    for($j=0;$j<$autocount;$j++){
                                        $autotype=explode("|",$autoarr[$j]);
                                        if($gid==$autotype[0]){
                                            $cardid=$autotype[1];
                                            $autostatus=1;
                                            break;
                                        }
                                    }
                                    if($autostatus==1){
                                        $orderid=md5($moneylogid);
                                        $sign=md5(OPENID.APPKEY.$cardid."1".$orderid.$space['qq']);
                                        $params = array(
                                        "key" => APPKEY,//应用APPKEY(应用详细页查询)
                                        "cardid" => $cardid,//商品编码，对应接口3的cardid
                                        "cardnum" => 1,//购买数量
                                        "orderid" => $orderid,//订单号，8-32位数字字母组合
                                        "game_userid" => $space['qq'],//游戏玩家账号(game_userid=xxx@162.com$xxx001 xxx@162.com是通行证xxx001是玩家账号)
                                        "sign" => $sign,//校验值，md5(<b>OpenID</b>+key+cardid+cardnum+orderid+game_userid+game_area+game_srv)
                                        );
                                        $paramstring = http_build_query($params);
                                        $url=HICKEYURL."?".$paramstring;
                                        ini_set('date.timezone','Asia/Shanghai');//矫正服务器时间
                                        writeLogZheng(date("Y-m-d H:i:s",time())."\n调用聚合接口充值：".$url);
                                        $content = file_get_contents($url);
                                        $payresult = json_decode($content,true);
                                        if(!empty($payresult)){
                                            if($payresult['error_code']!='0'){
                                                $insertsql.=$moneylogid."','9','".time()."')";
                                            }else{
                                                $insertsql.=$moneylogid."','1','".time()."')";
                                            }
                                        }else{
                                            $insertsql.=$moneylogid."','9','".time()."')";
                                        }
                                        foreach ($moneylogTableNames as $moneylogTableName)
                                        {
                                            $query = $_SGLOBAL['db']->query("UPDATE `".tname($moneylogTableName)."` SET moneylogstatus='0' WHERE moneylogid=".$moneylogid);
                                            if (!empty($query)) break;
                                        }

                                        $_SGLOBAL['db']->query($insertsql);
                                        sendMsgByCategory(5, $uid);
                                    }
                                }else{
                                    if($handle=fopen($_SERVER['DOCUMENT_ROOT']."/home/moneylog.txt","a+")){
                                        $content="用户uid：$uid,用户moneylogid：$moneylogid,用户兑换商品id：$gid,用户ip：".get_real_ip().",用户操作时间：".date("Y-m-d H:i:s",time())."\n";
                                        fwrite($handle,$content);
                                        fclose($handle);
                                    }
                                }
                            }
                        }
		            }else{
		                $redis->del("buy".$uid);
		                $redis->del("buymoney".$uid);
		                $result['errcode'] = 1010;
		                $result['errmsg'] = "您的余额不足";
		                echo json_encode($result);
		                return;
		            }
		            if (!empty($log)) {
         		         writeLogBack($log);
        	        }
		      }
	    }
	} else {	
	    $redis->del("buymoney".$uid);
	    echo json_encode($result);
        return;
	}
}elseif($op=="moneylog"){
    $userlist = array();
    $moneylogTableNames = getMoneylogTableName(strtotime("2016-05-02"));
    $count = 0;
    foreach ($moneylogTableNames as $moneylogTableName)
    {
        $queryLog = $_SGLOBAL['db_slave']->query("SELECT * FROM `".tname($moneylogTableName)."` where moneylogtypecategory=11 ORDER BY moneylogid DESC limit 3");
        while ($valueLog = $_SGLOBAL['db_slave']->fetch_array($queryLog)) {
            $notice['uid'] = $valueLog['moneyloguid'];
            $notice['name'] = $valueLog['moneylogusername'];
            $notice['avatarurl'] = $valueLog['moneyloguserphoto'];
            if ($valueLog['moneylogtypecategory'] != 100) {
                $notice['moneytips'] = $valueLog['moneylogtip'];
            } else {
                $notice['moneytips'] = $valueLog['moneylogtip']." 赚取了".($valueLog['moneylogamount']/100)."元";
            }
            $userlist[] = $notice;
            $count++;
        }
        if ($count>=3) break;
    }
    $result['userlist'] = array_slice($userlist,0,3);
    echo json_encode($result);
}elseif($op=="spacemoney"){
    $queryContact = $_SGLOBAL['db']->query('SELECT totalmoney, realmoney FROM '.tname('space')." WHERE uid = '$uid'");
    $valueMoney = $_SGLOBAL['db']->fetch_array($queryContact);
    $result['totalmoney'] = $valueMoney['totalmoney'];
    $result['realmoney'] = $valueMoney['realmoney'];
    $result['verifyingmoney'] = $result['totalmoney'] - $result['realmoney'];
    echo json_encode($result);
}elseif($op=="task"){
    $getmoney['tip'] = "赚奖金";
    $getmoneytype = array();
    $creditarr=array();
    $redis=getRedis();
    $creditarr[]= "category = 21";
    $creditarr[]= "available = 1";
    $creditarr[]= "(count=0 OR count > counted)";
    $creditarr[]="taskrate=2";
    $conditionQuery=$_SGLOBAL['db']->query("SELECT gid FROM ".tname('creditgood')." WHERE ".implode(" AND ",$creditarr));
    while($conditionResult=$_SGLOBAL['db']->fetch_array($conditionQuery)){
        $time=$redis->get("lastFinishTime".$uid.$conditionResult['gid']);
        if(!$time){
            $gidarr[]=$conditionResult['gid'];
        }
    }
    if(empty($gidarr)){
        $sql=") ";
    }else{
        $sql="AND moneylogtaskid not in(".implode(",",$gidarr).")) ";
    }
    $query = $_SGLOBAL['db']->query("SELECT moneytypeid,moneytypecategory,moneytypename,moneytypedes,moneytypebtn,moneytypeiconurl,moneytypestarttime,moneytypeendtime,moneytypefilter,taskcategory FROM ".tname('moneytype')." WHERE moneytypeavailable=1 ORDER BY dateline DESC LIMIT 30");
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        if (!empty($value['moneytypefilter']) && strstr($value['moneytypefilter'], $channel)) {
            continue;
        }
        if ($_POST['appversion'] < "2.3.0" && $value['moneytypecategory'] == 3) {
            continue;
        }
        if($value["moneytypecategory"]==0){
            continue;
        }
        if ($value['moneytypecategory'] == 3) {
            $typeid = $value['moneytypeid'];
            $moneylogTableNames = getMoneylogTableName(strtotime('2016-05-02'));
            $valueMoneyLog = '';
            foreach ($moneylogTableNames as $moneylogTableName)
            {
                $queryMoneyLog = $_SGLOBAL['db']->query("SELECT dateline,moneylogtypecategory FROM `".tname($moneylogTableName)."` WHERE moneylogtypeid=$typeid and moneyloguid=$uid order by moneylogid desc  LIMIT 1");

                $valueMoneyLog=$_SGLOBAL['db']->fetch_array($queryMoneyLog);
                if (!empty($valueMoneyLog)) break;
            }
            if ($valueMoneyLog) {
                if ($valueMoneyLog['moneylogtypecategory'] == 41) {
                    continue;
                }
            }
        }
        $appvalue['moneytypeid'] = $value['moneytypeid'];
        $appvalue['moneytypecategory'] = $value['moneytypecategory'];
        $appvalue['moneytypename'] = $value['moneytypename'];
        $appvalue['moneytypestatustip'] = "";
        $appvalue['moneytypedes'] = $value['moneytypedes'];
        $appvalue['moneytypebtn'] = $value['moneytypebtn'];
        $appvalue['moneytypeiconurl'] = $value['moneytypeiconurl'];
        $appvalue['moneytypestarttime'] = $value['moneytypestarttime'];
        $appvalue['moneytypeendtime'] = $value['moneytypeendtime'];
        if($value["moneytypecategory"]==2&&$value["taskcategory"]!=0){
            $appvalue["taskcategory"]=$value["taskcategory"];
            $wherearr[] = "category = 21";
            $wherearr[] = "available = 1";
            $wherearr[] = "(count=0 OR count > counted)";
            $wherearr[] = "taskcategory=".$value["taskcategory"];
            $moneylogTableNames = getMoneylogTableName(strtotime("2016-05-02"));
            $thequery = $_SGLOBAL['db']->query("SELECT gid,count,rate FROM ".tname('creditgood')." WHERE  ".implode(" AND ", $wherearr) . " AND gid not in( select moneylogtaskid from ".tname('moneylog_union')." where moneyloguid=$uid ".$sql."ORDER BY dateline ASC");
            $count=0;
            while($theresult=$_SGLOBAL['db']->fetch_array($thequery)){
                if($redis->get("totalCount".$theresult['gid'])==false){
                    foreach ($moneylogTableNames as $moneylogTableName)
                    {
                        $redisquery=$_SGLOBAL['db']->query("SELECT count(*) FROM `".tname($moneylogTableName)."` WHERE moneylogtaskid=".$theresult['gid']." AND moneylogstep not in(2,3)");
                        $redisresult=$_SGLOBAL['db']->fetch_row($redisquery);
                    }
                    $redis->set("totalCount".$theresult['gid'],$redisresult[0]);
                }
                if(!empty($theresult['rate'])){
                    $dayCount=$redis->get("rate".$theresult['gid']);
                    if($dayCount==false){
                        $dayCount=0;
                    }
                    if($theresult['rate']<=$dayCount){
                        continue;
                    }
                }
                $count++;
            }
            $appvalue["count"]=$count;
			unset($wherearr);
        }else{
            unset($appvalue["count"]);
            unset($appvalue["taskcategory"]);
        }
        $getmoneytype[] = $appvalue;
    }
    $getmoney['moneytypelist'] = $getmoneytype;
    $result['getmoney'] = $getmoney;
    echo json_encode($result);
}elseif($op=="dailyget"){
    $moneyTypeQuery=$_SGLOBAL['db']->query("SELECT a.*,b.firstrange,b.secondrange FROM ".tname("moneytype")." a LEFT JOIN ".tname('moneyrange')." b ON a.moneyrange=b.mrid WHERE a.moneytypecategory=0 AND a.moneytypeavailable=1");
    $realmoney=$_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT realmoney FROM '.tname('space').' WHERE uid='.$uid));
    while($value=$_SGLOBAL['db']->fetch_array($moneyTypeQuery)){
        if (!empty($value['moneytypefilter']) && strstr($value['moneytypefilter'], $channel)) {
            continue;
        }
        $btn=explode('&',$value['moneytypebtn']);
        $appvalue['moneytypeid'] = $value['moneytypeid'];
        $appvalue['moneytypecategory'] = $value['moneytypecategory'];
        $appvalue['moneytypename'] = $value['moneytypename'];
        $appvalue['moneytypestatustip'] = "";
        $appvalue['moneytypedes'] = $value['moneytypedes'];
        $appvalue['moneytypebtn'] = $btn[0];
        $appvalue['moneytypeiconurl'] = $value['moneytypeiconurl'];
        $appvalue['moneytypestarttime'] = $value['moneytypestarttime'];
        $appvalue['moneytypeendtime'] = $value['moneytypeendtime'];
        if(!empty($value['moneyrange'])){
            if($_POST['appversion']>='2.6.2'){
                if($realmoney<$value['firstrange']||$realmoney>$value['secondrange']){
                    $appvalue['moneytypestatustip'] = "未达到要求";
                    $appvalue['moneytypebtn']=$btn[1];
                }
            }else{
                continue;
            }
        }
        $moneylogTableNames = getMoneylogTableName(strtotime("2016-05-02"));

        $queryMoneyLog = $_SGLOBAL['db']->query("SELECT dateline,moneylogtypecategory FROM `".tname($moneylogTableNames[0])."` WHERE moneylogtypeid=".$value['moneytypeid']." and moneyloguid=$uid order by moneylogid desc  LIMIT 1");
        $valueMoneyLog=$_SGLOBAL['db']->fetch_array($queryMoneyLog);
        if ($valueMoneyLog) {
            $dateline = $valueMoneyLog['dateline'];
            $today = strtotime(sgmdate('Y-m-d'));
            if ($dateline > $today) {
                $appvalue['moneytypestatustip'] = "已".$value['moneytypebtn'];
            }
        }
        $result["dailyget"][]=$appvalue;
    }
    echo json_encode($result);
}elseif($op=="newgetmoneytypebyid"){
	$id = empty($_POST['moneytypeid'])?'0':$_POST['moneytypeid'];
    $queryContact = $_SGLOBAL['db']->query('SELECT * FROM '.tname('moneytype')." WHERE  moneytypeid = '$id'");
    $valueMoney = $_SGLOBAL['db']->fetch_array($queryContact);
    $today = sstrtotime(sgmdate('Y-m-d'));
    $moneylogTableNames = getMoneylogTableName();
    $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM `".tname($moneylogTableNames[0])."` main WHERE moneylogtypeid=$id AND moneylogtypecategory=0 AND dateline > $today "), 0);
    $valueMoney['moneytypedaycount'] = $count;
    if ($count >= $valueMoney['moneytypedaylimit']) {
	   $valueMoney['moneytypedone'] = 1;
    }
    $result['moneytype_detail'] = $valueMoney;
    if($_POST['appversion']>='2.6.2'){
        $bannerQuery=$_SGLOBAL['db']->query("SELECT * FROM ".tname('banner')." WHERE available=1 AND category=6 ORDER BY dateline DESC LIMIT 1");
        $bannerValue=$_SGLOBAL['db']->fetch_array($bannerQuery);
        if(!empty($bannerValue)){
            $result['invite_share_text']=$bannerValue;
        }else{
            $result['invite_share_text'] = "邀请好友加入，最高同时获取{2}元";
        }
    }else{
        $result['invite_share_text'] = "邀请好友加入，最高同时获取{2}元";
    }
    if (!empty($valueMoney['moneytypetaskids'])) {
        $tmpTaskids = $valueMoney['moneytypetaskids'];
        $arrids = explode("|",$tmpTaskids);
        $idscount = count($arrids);
        for($i=0;$i<$idscount;$i++){
            $gettaskarr=getTaskDetail($arrids[$i], $uid);
            $result['task_detail'][]=$gettaskarr;
            $result['moneytype_detail']['moneytypetaskid']=$gettaskarr['gid'];
        }
    }else{
        $result['moneytype_detail']['moneytypetaskid']=0;
    }
    $result['timestamp'] = time();
    echo json_encode($result);
}

function getTaskDetail($cid, $uid) {
    global $_SGLOBAL;
    //$cid = empty($_POST['gid'])?'0':$_POST['gid'];
    $queryContact = $_SGLOBAL['db']->query('SELECT * FROM '.tname('creditgood')." WHERE  gid = '$cid'");
    $valueMoney = $_SGLOBAL['db']->fetch_array($queryContact);

    $moneylooTableNames = getMoneylogTableName(strtotime("2016-05-02"));
    $valueTaskLog = '';
    foreach ($moneylooTableNames as $moneylooTableName)
    {
        $queryTaskLog = $_SGLOBAL['db']->query('SELECT moneylogid,moneylogamount,moneylogamounttype,moneyloginvite,moneyloguid,moneylogusername,moneyloguserphoto,moneylogtypeid,moneylogtypecategory,moneylogstatus,moneylogtaskid,moneylogstep,moneylogtaskimgurl,moneylogrelatedid,moneylogtid,moneylogtip,deviceid,qq,devicecount,qqcount,dateline FROM `'.tname($moneylooTableName)."` WHERE  moneylogtaskid = '$cid' and moneyloguid = '$uid' and moneylogstep < 2 ORDER BY dateline DESC LIMIT 1");
        $valueTaskLog = $_SGLOBAL['db']->fetch_array($queryTaskLog);
        if (!empty($valueTaskLog)) break;

    }
    if($valueMoney['taskrate']==2){ 
        if($valueTaskLog['dateline'] < strtotime(date("Y-m-d"))){
            $valueTaskLog=NULL;
        }
    }
    if ($valueTaskLog) {
        $count = getcount('moneylog_union', array('moneylogtaskid'=>$cid));


        $valueMoney['tips'] = "已发放".($count*10)."份奖金";
        $valueMoney['pricetip'] = ($valueMoney['price']/100)."元";
        $valueMoney['taskimgurl'] = $valueTaskLog['moneylogtaskimgurl'];
        $valueMoney['taskstatus'] = $valueTaskLog['moneylogstatus'];
        $valueMoney['mainstatus'] = $valueTaskLog['moneylogstatus'];
    } else {
        $valueMoney['mainstatus'] = -1;
    }

    if ($valueMoney['submills'] > 0) {
        foreach ($moneylooTableNames as $moneylooTableName)
        {
            $queryTaskSub = $_SGLOBAL['db']->query('SELECT * FROM `'.tname($moneylooTableName)."` WHERE  moneylogtaskid = '$cid' and moneyloguid = '$uid' and moneylogstep = 2");
            $valueTaskSub = $_SGLOBAL['db']->fetch_array($queryTaskSub);
            if ($valueTaskSub) {
                $valueMoney['substatus'] = $valueTaskSub['moneylogstatus'];
            } else {
                $valueMoney['substatus'] = -1;
            }
            if(!empty($valueTaskSub)) break;
        }
    }


    if ($valueMoney['thirdmills'] > 0) {
        foreach ($moneylooTableNames as $moneylooTableName)
        {
            $queryTaskThird = $_SGLOBAL['db']->query('SELECT * FROM `'.tname($moneylooTableName)."` WHERE  moneylogtaskid = '$cid' and moneyloguid = '$uid' and moneylogstep = 3");
            $valueTaskThird = $_SGLOBAL['db']->fetch_array($queryTaskThird);
            if ($valueTaskThird) {
                $valueMoney['thirdstatus'] = $valueTaskThird['moneylogstatus'];
            } else {
                $valueMoney['thirdstatus'] = -1;
            }
            if(!empty($valueTaskThird)) break;
        }

    }

    $count = getcount('creditgoodlog', array('uid'=>$uid, 'gid'=>$cid));
    if ($count > 0) {
        $valueMoney['isdownload'] = 1;
    }
    $valueMoney['totalpricetip'] = "+".(($valueMoney['price']+$valueMoney['submoney']+$valueMoney['thirdmoney'])/100)."元";
    $valueMoney['taskfirstprice'] = "+".($valueMoney['price']/100)."元";
    //$result['detail'] = $valueMoney;
    return $valueMoney;
}
//获取用户真实IP
function get_real_ip(){
    $ip=false;
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $ips=explode (', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
        if($ip){ array_unshift($ips, $ip); $ip=FALSE; }
        for ($i=0; $i < count($ips); $i++){
            if(!eregi ('^(10│172.16│192.168).', $ips[$i])){
                $ip=$ips[$i];
                break;
            }
        }
    }
    return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}

?>
