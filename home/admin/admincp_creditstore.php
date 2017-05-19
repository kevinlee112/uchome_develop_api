<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_usergroup.php 12592 2009-07-09 07:49:22Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
//权限
if(!checkperm('manageusergroup')) {
	cpmessage('no_authority_management_operation');
}
//取得单个数据
$thevalue = $list = array();
$_GET['gid'] = empty($_GET['gid'])?0:intval($_GET['gid']);
if($_GET['gid']) {
	$query = $_SGLOBAL['db']->query("SELECT a.*,b.firstrange,b.secondrange FROM ".tname('creditgood')." a LEFT JOIN ".tname('moneyrange')." b ON a.moneyrange=b.mrid WHERE gid='$_GET[gid]'");
	if(!$thevalue = $_SGLOBAL['db']->fetch_array($query)) {
		cpmessage('user_credit_good_does_not_exist');
	}
}
if(!empty($_GET['op'])){
    $selchannelquery=$_SGLOBAL['db']->query("SELECT channelid,channelname FROM ".tname("channel")." WHERE channelstatus=1");
    $channel=array();
    while($value=$_SGLOBAL['db']->fetch_array($selchannelquery)){
        $channel[]=$value;
    }
    $rangeQuery=$_SGLOBAL['db']->query('SELECT mrid,firstrange,secondrange FROM '.tname('moneyrange').' WHERE status=1');
    $rangeValue=array();
    while($value=$_SGLOBAL['db']->fetch_array($rangeQuery)){
        $rangeValue[]=$value;
    }
}
$mpurl = 'admincp.php?ac=creditstore';
if(submitcheck('thevaluesubmit')) {
    //用户组名
    $_POST['set']['title'] = shtmlspecialchars($_POST['set']['title']);
    if(empty($_POST['set']['title'])) cpmessage('user_credit_store_does_not_exist');
    $setarr = array('title' => $_POST['set']['title']);

    if(empty($_POST['set']['icon'])) cpmessage('user_credit_store_does_not_exist');
    if(empty($_POST['set']['image'])) cpmessage('user_credit_store_does_not_exist');

    $_POST['set']['note'] = shtmlspecialchars($_POST['set']['note']);
    if(empty($_POST['set']['note'])) cpmessage('user_credit_store_does_not_exist');
    $setarr['note'] = $_POST['set']['note'];

	$setarr['dateline'] = $_SGLOBAL['timestamp'];
	$setarr['channelid']=$_POST['channelid'];
    //详细权限
    $perms = array_keys($_POST['set']);
    $nones = array('gid', 'title','note','channelid');
    foreach ($perms as $value) {
        if(!in_array($value, $nones)) {
            $_POST['set'][$value] = trim($_POST['set'][$value]);
            if($thevalue[$value] != $_POST['set'][$value]) {
                    $setarr[$value] = $_POST['set'][$value];
            }
        }
    }
	if(empty($thevalue['gid'])) {
		//添加
		inserttable('creditgood', $setarr);
	} else {
		//更新
		updatetable('creditgood', $setarr, array('gid'=>$thevalue['gid']));
	}

	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	usergroup_cache();
	cpmessage('do_success', 'admincp.php?ac=creditstore&available='.$_POST['available'].'&op='.$_POST['op']);
}

if(empty($_GET['op'])) {
    //处理搜索
    $intkeys = array('available');
    $strkeys = array();
    $randkeys = array();
    $likekeys = array();
    $results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
    $wherearr = $results['wherearr'];
    $countsql=$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
    $mpurl .= '&'.implode('&', $results['urls']);
    if($wheresql!=1){
        $wheresql='a.'.$wheresql;
    }
    $redis=getRedis();
    if($_GET['perpage']=='不限'){
        //浏览列表
        $query = $_SGLOBAL['db']->query("SELECT a.*,b.channelname FROM ".tname('creditgood')." a LEFT JOIN ".tname('channel')." b ON a.channelid=b.channelid WHERE a.category=21 AND $wheresql ORDER BY a.dateline desc");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            $value['task']=$redis->get("totalCount".$value['gid']);
            if($value['task']==false){
                $value['task']=0;
            }
            if($value['count']!=0&&$value['count']<=$value['task']){
                $value['limitstatus']=1;
            }else{
                $value['limitstatus']=2;
            }
            if(!empty($_GET['limitstatus'])){
                if($value['limitstatus']!=$_GET['limitstatus']){
                    continue;
                }
            }
            $list[] = $value;
        }    
    }else{
        //显示分页
        $perpage = empty($_GET['perpage'])?100:intval($_GET['perpage']);
        $mpurl .= '&perpage='.$perpage;
        $perpages = array($perpage => ' selected');
        $page = empty($_GET['page'])?1:intval($_GET['page']);
        if($page<1) $page = 1;
        $start = ($page-1)*$perpage;
        //检查开始数
        ckstart($start, $perpage);
        $managebatch = checkperm('managebatch');
        $allowbatch = true;
        $list = array();
        $multi = '';
        $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(gid) FROM ".tname('creditgood')." WHERE category=21 and $countsql"), 0);
        if($count) {
            $query = $_SGLOBAL['db']->query("SELECT a.*,b.channelname FROM ".tname('creditgood')." a LEFT JOIN ".tname('channel')." b ON a.channelid=b.channelid WHERE a.category=21 AND $wheresql ORDER BY a.dateline desc LIMIT $start,$perpage");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                $value['task']=$redis->get("totalCount".$value['gid']);
                if($value['task']==false){
                    $value['task']=0;
                }
                if($value['count']!=0&&$value['count']<=$value['task']){
                    $value['limitstatus']=1;
                }else{
                    $value['limitstatus']=2;
                }
                if(!empty($_GET['limitstatus'])){
                    if($value['limitstatus']!=$_GET['limitstatus']){
                        continue;
                    }
                }
                $list[] = $value;
            }
            $multi = multi($count, $perpage, $page, $mpurl);
        }
    }
	$classId="task";
	$op=$_GET['op'];
}elseif($_GET['op']=='good') {
	
	//浏览列表
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditgood')." WHERE category=11 ORDER BY dateline desc");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	    $channelquery=$_SGLOBAL['db']->query("SELECT channelname FROM ".tname('channel')." WHERE channelid=".$value['channelid']);
	    $channelresult=$_SGLOBAL['db']->fetch_row($channelquery);
	    $value['channelname']=$channelresult[0];
		$list[] = $value;
	}
	$op=$_GET['op'];
	$classId="good";
	//$actives = array('view' => ' class="active"');
	
}elseif($_GET['op']=='treasure'){
    //浏览列表
    $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditgood')." WHERE category=3 ORDER BY dateline desc");
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        $channelquery=$_SGLOBAL['db']->query("SELECT channelname FROM ".tname('channel')." WHERE channelid=".$value['channelid']);
        $channelresult=$_SGLOBAL['db']->fetch_row($channelquery);
        $value['channelname']=$channelresult[0];
        $list[] = $value;
    }
    $op=$_GET['op'];
    $classId="treasure";
} elseif($_GET['op'] == 'viewuser') {
        //浏览列表
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." as s,  ".tname('creditgoodlog')." as c ,  ".tname('spacefield')." as sf WHERE c.gid = ".$_GET['gid']." and s.uid = c.uid and s.uid = sf.uid  and lotteryfail != 1 ORDER BY c.dateline desc");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                $listuser[] = $value;
        }
	$thevalue = 0;
        $actives = array('view' => ' class="active"');
	
} elseif ($_GET['op'] == 'add') {
	//添加
	$thevalue = array('gid' => 0, 'explower'=>0, 'maxattachsize'=>'10', 'maxfriendnum'=>50, 'postinterval'=>60, 'searchinterval'=>60, 'domainlength'=>0);
	//include_once(S_ROOT . "./data/data_magic.php");
	
} elseif ($_GET['op'] == 'edit') {
	//编辑
	include_once(S_ROOT . "./data/data_magic.php");
	
} elseif ($_GET['op'] == 'copy') {
	//复制
	$system = $thevalue['system'];
	$from = $thevalue['grouptitle'];
	$gid = $thevalue['gid'];
	$thevalue = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usergroup')." WHERE gid!='$gid' AND system='$system' ORDER BY explower");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$grouparr[] = $value;
	}
} elseif ($_GET['op'] == 'editavailable' && $thevalue) {


	//更新用户权限
	updatetable('creditgood', array('available'=>1,'dateline'=>$_SGLOBAL['timestamp']), array('gid'=>$_GET['gid']));

	cpmessage('do_success', 'admincp.php?ac=creditstore&available='.$_GET['available']);
} elseif ($_GET['op'] == 'editdisavailable' && $thevalue) {


        //更新用户权限
        updatetable('creditgood', array('available'=>0, 'dateline'=>$_SGLOBAL['timestamp']), array('gid'=>$_GET['gid']));

        cpmessage('do_success', 'admincp.php?ac=creditstore&available='.$_GET['available']);
} else if ( $_GET['op'] == 'taskmanage' && $thevalue ) {
} else if ($_GET['op'] == 'taskmanageall') {
	$nowdaytime = sgmdate('Ymd', time());
	writeLogKang("^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ day----------".$_GET['daytime']);
	$day = empty($_GET['daytime'])? "" : $_GET['daytime'];
	if (empty($day)) return;
    $redis = getRedis();
    $currentCount = $redis->incr("taskmanageall");
    if($currentCount == 1){
        $redis->expire("taskmanageall", 60);
    } else {
        return;
    }
	writeLogKang("^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ day 222---------".$day." ====== ".strtotime($day));
	$queryTask = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditgood')." WHERE category=21 order by dateline desc");
    while ($value = $_SGLOBAL['db']->fetch_array($queryTask)) {
        $setarr = array(
            'taskmanagedaytime' => $day,
            'taskmanagegid' => $value['gid']
        );
        $gid = $value['gid'];
        $title = $value['title'];
        $channelid = $value['channelid'];
        $dateline1 = strtotime($day);
        $dateline2 = $dateline1 + 24*3600;
        $moneylogTableNames = getMoneylogTableName($dateline1, $dateline2);
        $countDay = $countDaySucc =  $countMoney = 0;
        foreach ( $moneylogTableNames as $moneylogTableName)
        {
           $countDay += $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(moneylogtaskid) FROM `".tname($moneylogTableName)."` main WHERE moneylogtaskid=$gid AND dateline > $dateline1 AND dateline < $dateline2 AND moneylogstep!=2 AND moneylogstep!=3"), 0);
            $countDaySucc += $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(moneylogtaskid) FROM `".tname($moneylogTableName)."` main WHERE moneylogtaskid=$gid AND moneylogstatus=0 AND dateline > $dateline1 AND dateline < $dateline2 AND moneylogstep!=2 AND moneylogstep!=3"), 0);
            $countMoney +=$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT SUM(moneylogamount) FROM `".tname($moneylogTableName)."` main WHERE moneylogtaskid=$gid AND moneylogstatus=0 AND dateline > $dateline1 AND dateline < $dateline2 AND moneylogstep!=2 AND moneylogstep!=3 GROUP BY moneylogtaskid"), 0);
        }

       if ($countDay!=0 && $countDaySucc!=0) {
		    writeLogKang("^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^: day-".$day."  gid-".$value['gid']);
            if(getcount('taskmanage', $setarr)) {
                //return false;
            } else {
                inserttable('taskmanage', $setarr);
            }
        	$_SGLOBAL['db']->query("UPDATE ".tname('taskmanage')." SET `taskmanagedownload`=$countDay,`taskmanagesucce`=$countDaySucc, `taskmanagename`='$title', `channelid`='$channelid',`taskmanagemoney`='$countMoney' WHERE taskmanagedaytime=$day AND taskmanagegid=$gid");       
	    } 
    }
	$redis->del("taskmanageall");
	cpmessage('do_success', 'admincp.php?ac=taskmanage');
}

function groupcredit_update() {
	global $_SGLOBAL;
	
	//起始为-999999999
	$lowergid = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT gid FROM ".tname('usergroup')." WHERE system='0' ORDER BY explower LIMIT 1"), 0);
	if($lowergid) updatetable('usergroup', array('explower'=>'-999999999'), array('gid'=>$lowergid));

}

?>
