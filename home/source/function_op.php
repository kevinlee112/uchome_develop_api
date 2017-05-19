<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_op.php 12754 2009-07-17 08:57:12Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//合并tag
function mergetag($tagids, $newtagid) {
	global $_SGLOBAL;
	
	if(!checkperm('managetag')) return false;
	
	//清空
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tag')." WHERE tagid IN (".simplode($tagids).") AND tagid <> '$newtagid'");

	$tagids[] = $newtagid;
	$tagids = array_unique($tagids);
	
	//更新关联表
	$blogids = array();
	$query = $_SGLOBAL['db']->query("SELECT blogid FROM ".tname('tagblog')." WHERE tagid IN (".simplode($tagids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($blogids[$value['blogid']])) $blogids[$value['blogid']] = $value;
	}
	if(empty($blogids)) return true;
	
	//关联
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tagblog')." WHERE tagid IN (".simplode($tagids).")");
	//插入
	$inserts = array();
	foreach ($blogids as $blogid => $value) {
		$inserts[]= "('$newtagid', '$blogid')";
	}
	$_SGLOBAL['db']->query("INSERT INTO ".tname('tagblog')." (tagid, blogid) VALUES ".implode(',', $inserts));
	//更新统计
	updatetable('tag', array('blognum'=>count($blogids)), array('tagid'=>$newtagid));
	
	return true;
}

//锁定/开放tag
function closetag($tagids, $optype) {
	global $_SGLOBAL;
	
	if(!checkperm('managetag')) return false;
	
	$newtagids = array();
	if($optype == 'close') {
		$close = 0;
	} else {
		$close = 1;
	}
	$query = $_SGLOBAL['db']->query("SELECT tagid FROM ".tname('tag')." WHERE tagid IN (".simplode($tagids).") AND close='$close'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newtagids[] = $value['tagid'];
	}
	if(empty($newtagids)) return false;

	//更新状态
	if($optype == 'close') {
		//关联
		$_SGLOBAL['db']->query("DELETE FROM ".tname('tagblog')." WHERE tagid IN (".simplode($newtagids).")");
		$_SGLOBAL['db']->query("UPDATE ".tname('tag')." SET blognum='0', close='1' WHERE tagid IN (".simplode($newtagids).")");
	} else {
		$_SGLOBAL['db']->query("UPDATE ".tname('tag')." SET close='0' WHERE tagid IN (".simplode($newtagids).")");
	}
	
	return true;
}

//合并mtag
function mergemtag($tagids, $newtagid) {
	global $_SGLOBAL;
	
	if(!checkperm('managemtag')) return false;
	
	//重新组合
	$cktagids = array();
	foreach ($tagids as $value) {
		if($value && $value != $newtagid) {
			$cktagids[$value] = $value;
		}
	}
	if(empty($cktagids)) return false;
	
	$tagids = $cktagids;
	
	//清空
	$_SGLOBAL['db']->query("DELETE FROM ".tname('mtag')." WHERE tagid IN (".simplode($tagids).")");
	//更新话题/回复
	$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET tagid='$newtagid' WHERE tagid IN (".simplode($tagids).")");
	$_SGLOBAL['db']->query("UPDATE ".tname('post')." SET tagid='$newtagid' WHERE tagid IN (".simplode($tagids).")");
	
	//已有的成员
	$olduids = $newuids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid='$newtagid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$olduids[$value['uid']] = $value;
	}
	
	//更新关联表
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid IN (".simplode($tagids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($olduids[$value['uid']])) $newuids[$value['uid']] = $value;
	}
	
	//关联
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tagspace')." WHERE tagid IN (".simplode($tagids).")");
	//插入
	$inserts = array();
	foreach ($newuids as $uid => $value) {
		$inserts[]= "('$newtagid', '$uid', '".addslashes($value['username'])."')";
	}
	if($inserts) {
		$_SGLOBAL['db']->query("REPLACE INTO ".tname('tagspace')." (tagid,uid,username) VALUES ".implode(',', $inserts));
	}

	//更新统计
	$setarr = array(
		'membernum' => getcount('tagspace', array('tagid'=>$newtagid)),
		'threadnum' => getcount('thread', array('tagid'=>$newtagid)),
		'postnum' => getcount('post', array('tagid'=>$newtagid, 'isthread'=>'0'))
	);
	updatetable('mtag', $setarr, array('tagid'=>$newtagid));
	
	return true;
}


//锁定/开放tag
function closemtag($tagids, $optype) {
	global $_SGLOBAL;
	
	if(!checkperm('managemtag')) return false;
	
	$newtagids = array();
	if($optype == 'close') {
		$close = 0;
	} else {
		$close = 1;
	}
	$query = $_SGLOBAL['db']->query("SELECT tagid FROM ".tname('mtag')." WHERE tagid IN (".simplode($tagids).") AND close='$close'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newtagids[] = $value['tagid'];
	}
	if(empty($newtagids)) return false;

	//更新状态
	if($optype == 'close') {
		//关联
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET close='1' WHERE tagid IN (".simplode($newtagids).")");
	} else {
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET close='0' WHERE tagid IN (".simplode($newtagids).")");
	}
	
	return true;
}


//推荐/取消tag
function recommendmtag($tagids, $optype) {
	global $_SGLOBAL;
	
	if(!checkperm('managemtag')) return false;
	
	$newtagids = array();
	if($optype == 'recommend') {
		$recommend = 0;
	} else {
		$recommend = 1;
	}
	$query = $_SGLOBAL['db']->query("SELECT tagid FROM ".tname('mtag')." WHERE tagid IN (".simplode($tagids).") AND recommend='$recommend'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newtagids[] = $value['tagid'];
	}
	if(empty($newtagids)) return false;

	//更新状态
	if($optype == 'recommend') {
		//关联
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET recommend='1' WHERE tagid IN (".simplode($newtagids).")");
	} else {
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET recommend='0' WHERE tagid IN (".simplode($newtagids).")");
	}
	
	return true;
}

//话题精华
function digestthreads($tagid, $tids, $v) {
	global $_SGLOBAL;
	
	$mtag = getmtag($tagid);
	if($mtag['grade']<8) {
		return array();
	}
	
	if(empty($v)) {
		$wheresql = " AND t.digest='1'";
		$v = 0;
	} else {
		$wheresql = " AND t.digest='0'";
		$v = 1;
	}
	$newtids = $threads = array();
	$allowmanage = checkperm('managethread');
	$query = $_SGLOBAL['db']->query("SELECT t.* FROM ".tname('thread')." t WHERE t.tagid='$tagid' AND t.tid IN (".simplode($tids).") $wheresql");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newtids[] = $value['tid'];
		$threads[] = $value;
	}
	
	//数据
	if($newtids) {
		$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET digest='$v' WHERE tid IN (".simplode($newtids).")");
	}

	return $threads;
}

//话题置顶
function topthreads($tagid, $tids, $v) {
	global $_SGLOBAL;
	
	$mtag = getmtag($tagid);
	if($mtag['grade']<8) {
		return array();
	}
	
	if(empty($v)) {
		$wheresql = " AND t.displayorder='1'";
		$v = 0;
	} else {
		$wheresql = " AND t.displayorder='0'";
		$v = 1;
	}
	$newtids = $threads = array();
	$query = $_SGLOBAL['db']->query("SELECT t.* FROM ".tname('thread')." t WHERE t.tagid='$tagid' AND t.tid IN (".simplode($tids).") $wheresql");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newtids[] = $value['tid'];
		$threads[] = $value;
	}
	
	//数据
	if($newtids) {
		$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET displayorder='$v' WHERE tid IN (".simplode($newtids).")");
	}

	return $threads;
}

//锁定/开放tag
function passtask($tagids, $optype, $auto=0, $dateline) {
    writeLogDebug("--------------- passtask ----------------");
    global $_SGLOBAL;
    $status=0;
    $insertsql="INSERT INTO ".tname("autoaward")."(autoawardmid,autoawardstatus,dateline) values ('";
    if(!checkperm('managemtag')) return 1;

    $newtagids = array();
    if($optype == 'pass') {
        $close = 1;
    } else {
        $close = 1;
    }
    $moneylogTableNames = getMoneylogTableName($dateline, $dateline);
    $query = $_SGLOBAL['db']->query("SELECT moneylogid FROM `".tname($moneylogTableNames[0])."` WHERE moneylogid IN (".simplode($tagids).") AND moneylogstatus='$close'");
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        $newtagids[] = $value['moneylogid'];
    }
    if(empty($newtagids)) return 2;
    //更新状态
    if($optype == 'pass') {
        //关联
        $count = count($newtagids);
        for($i=0; $i<$count;$i++) {
            $logid = $newtagids[$i];
            $queryTaskLog = $_SGLOBAL['db']->query("SELECT * FROM `".tname($moneylogTableNames[0])."` WHERE  moneylogid =". $logid ." ");
            $valueTaskLog = $_SGLOBAL['db']->fetch_array($queryTaskLog);
            $uid = $valueTaskLog['moneyloguid'];
            if($auto==1)
            {
                if($handle=fopen(AUTO,"r"))
                {
                    $autoarr=explode("&",fread($handle,filesize(AUTO)));
                    fclose($handle);
                }
                $autocount=count($autoarr);
                for($j=0;$j<$autocount;$j++)
                {
                    $autotype=explode("|",$autoarr[$j]);
                    if($valueTaskLog['moneylogtypeid']==$autotype[0])
                    {
                        $cardid=$autotype[1];
                        break;
                    }
                }
                $orderid=md5($valueTaskLog['moneylogid']);
                $sign=md5(OPENID.APPKEY.$cardid."1".$orderid.$valueTaskLog['qq']);
                $params = array(
                    "key" => APPKEY,//应用APPKEY(应用详细页查询)
                    "cardid" => $cardid,//商品编码，对应接口3的cardid
                    "cardnum" => 1,//购买数量
                    "orderid" => $orderid,//订单号，8-32位数字字母组合
                    "game_userid" => $valueTaskLog['qq'],//游戏玩家账号(game_userid=xxx@162.com$xxx001 xxx@162.com是通行证xxx001是玩家账号)
                    "sign" => $sign,//校验值，md5(<b>OpenID</b>+key+cardid+cardnum+orderid+game_userid+game_area+game_srv)
                );
                $paramstring = http_build_query($params);
                $url=HICKEYURL."?".$paramstring;
                ini_set('date.timezone','Asia/Shanghai');//矫正服务器时间
                writeLogZheng(date("Y-m-d H:i:s",time())."\n调用聚合接口充值：".$url);
                $content = file_get_contents($url);
                $result = json_decode($content,true);
                if(!empty($result)){
                    if($result['error_code']!='0'){
                        //writeLogZheng("调用聚合充值接口error_code非0写入数据库：INSERT INTO ".tname('autoaward')."(autoawardmid,autoawardstatus,dateline) values ('".$valueTaskLog['moneylogid']."','9','".time()."')");
                        $insertsql.=$valueTaskLog['moneylogid']."','9','".time()."'),('";
                        //$insertsqlarr=array("autoawardmid"=>$valueTaskLog['moneylogid'],"autoawardstatus"=>9,"dateline"=>time());
                        //inserttable("autoaward", $insertsqlarr);
                        $status++;
                        continue;
                    }else{
                        /*if($result['result']['game_state']=='9'){
                            writeLogZheng("调用聚合充值接口失败写入数据库：INSERT INTO ".tname('autoaward')."(autoawardmid,autoawardstatus,dateline) values ('".$valueTaskLog['moneylogid']."','9','".time()."')");
                            $_SGLOBAL['db']->query("INSERT INTO ".tname('autoaward')."(autoawardmid,autoawardstatus,dateline) values ('".$valueTaskLog['moneylogid']."','9','".time()."')");
                            $status++;
                            continue;
                        }else{*/
                        //writeLogZheng("接口返回值写入数据库：INSERT INTO ".tname('autoaward')."(autoawardmid,autoawardstatus,dateline) values ('".$valueTaskLog['moneylogid']."','1','".time()."')");
                        $insertsql.=$valueTaskLog['moneylogid']."','1','".time()."'),('";
                        //$insertsqlarr=array("autoawardmid"=>$valueTaskLog['moneylogid'],"autoawardstatus"=>1,"dateline"=>time());
                        //inserttable("autoaward", $insertsqlarr);
                        //}
                        /*if($result['result']['game_state']=='0')
                        {
                            $selectparams=array(
                                "key" => APPKEY,//应用APPKEY(应用详细页查询)
                                "orderid" => $orderid,//订单号，8-32位数字字母组合
                            );
                            $selectparamsstring=http_build_query($selectparams);
                            $url=HICKEYSELECT."?".$selectparamsstring;
                            do{
                                writeLogZheng(date("Y-m-d H:i:s",time())."\n调用聚合接口查询状态：".$url);
                                $selectcontent = file_get_contents($url);
                                $selectresult = json_decode($selectcontent,true);
                                if(!empty($selectresult)){
                                    if($selectresult['result']['game_state']=='9'){
                                        writeLogZheng("调用接口失败写入数据库：INSERT INTO ".tname('autoaward')."(autoawardmid,autoawardstatus,dateline) values ('".$valueTaskLog['moneylogid']."','9','".time()."')");
                                        $_SGLOBAL['db']->query("INSERT INTO ".tname('autoaward')."(autoawardmid,autoawardstatus,dateline) values ('".$valueTaskLog['moneylogid']."','9','".time()."')");
                                        $status++;
                                        continue 2;
                                    }else if($selectresult['result']['game_state']=='1'){
                                        writeLogZheng("修改数据库接口返回值：UPDATE ".tname('autoaward')." SET autoawardstatus=1 WHERE autoawardmid=".$valueTaskLog['moneylogid']);
                                        $_SGLOBAL['db']->query("UPDATE ".tname('autoaward')." SET autoawardstatus=1 WHERE autoawardmid=".$valueTaskLog['moneylogid']);
                                    }
                                }else{
                                    if($result['result']['game_state']=='0')
                                        continue;
                                }
                            }while($selectresult['result']['game_state']=='0');
                        }else if($result['result']['game_state']=='9'){
                            writeLogZheng("调用聚合充值接口失败写入数据库：INSERT INTO ".tname('autoaward')."(autoawardmid,autoawardstatus,dateline) values ('".$valueTaskLog['moneylogid']."','9','".time()."')");
                            $_SGLOBAL['db']->query("INSERT INTO ".tname('autoaward')."(autoawardmid,autoawardstatus,dateline) values ('".$valueTaskLog['moneylogid']."','9','".time()."')");
                            $status++;
                            continue;
                        }*/
                    }
                }else{
                    //writeLogZheng("调用聚合充值接口没返回值写入数据库：INSERT INTO ".tname('autoaward')."(autoawardmid,autoawardstatus,dateline) values ('".$valueTaskLog['moneylogid']."','9','".time()."')");
                    $insertsql.=$valueTaskLog['moneylogid']."','9','".time()."'),('";
                    //$insertsqlarr=array("autoawardmid"=>$valueTaskLog['moneylogid'],"autoawardstatus"=>9,"dateline"=>time());
                    //inserttable("autoaward", $insertsqlarr);
                    $status++;
                    continue;
                }
            }

            $_SGLOBAL['db']->query("UPDATE `".tname($moneylogTableNames[0])."` SET moneylogstatus='0' WHERE moneylogid IN (".simplode($newtagids).")");

            if ($valueTaskLog && $valueTaskLog['moneylogtypecategory']==21) {
                $money = $valueTaskLog['moneylogamount'];
                $uid = $valueTaskLog['moneyloguid'];
                $setarrs = array();
                $setarrs['realmoney'] = "realmoney=realmoney+$money";
                $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarrs)." WHERE uid='$uid'");
                writeLogDebug("*************************");
            }
            include_once(S_ROOT.'./source/function_common.php');
            writeLogDebug("*************************".$uid."*****moneylogtypecategory: ".$valueTaskLog['moneylogtypecategory']);
            $to = $uid;//explode(',', $uid);
            if ($valueTaskLog && $valueTaskLog['moneylogtypecategory']==11) {
                sendMsgByCategory(5, $to);
            } else if ($valueTaskLog && $valueTaskLog['moneylogtypecategory']==21) {
                sendMsgByCategory(6, $to);
            }
            //sendIMMsg(98, $to, "test is test");
        }
        if($auto==1){
            $insertsql=substr($insertsql,0,strlen($insertsql)-3);
            $_SGLOBAL['db']->query($insertsql);
        }
    } else {

        $_SGLOBAL['db']->query("UPDATE `".tname($moneylogTableNames[0])."` SET moneylogstatus='2' WHERE moneylogid IN (".simplode($newtagids).")");
        $count = count($newtagids);
        for($i=0; $i<$count;$i++) {
            $logid = $newtagids[$i];
            $queryTaskLog = $_SGLOBAL['db']->query('SELECT * FROM `'.tname($moneylogTableNames[0])."` WHERE  moneylogid = '$logid'");
            $valueTaskLog = $_SGLOBAL['db']->fetch_array($queryTaskLog);
            if ($valueTaskLog && $valueTaskLog['moneylogtypecategory']==21) {
                $money = -$valueTaskLog['moneylogamount'];
                $uid = $valueTaskLog['moneyloguid'];
                $setarrs = array();
                $setarrs['totalmoney'] = "totalmoney=totalmoney+$money";
                $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarrs)." WHERE uid='$uid'");
            }
        }

    }
    if($status==0)
    {
        return 0;
    }else if($status!=$count){
        return 3;
    }else{
        return 4;
    }
}


?>
