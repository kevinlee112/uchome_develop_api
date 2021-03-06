<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_login.php 13210 2009-08-20 07:09:06Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./source/function_cp.php');
// 活动分类
if(!@include_once(S_ROOT.'./data/data_eventclass.php')) {
	include_once(S_ROOT.'./source/function_cache.php');
	eventclass_cache();
}
$uid = empty($_POST['uid'])?0:intval($_POST['uid']);
$_SGLOBAL['supe_uid'] = $uid;
$op = empty($_POST['op'])?'':$_POST['op'];
$lat = empty($_GET['lat'])?'':$_GET['lat'];
$lng = empty($_GET['lng'])?'':$_GET['lng'];
$result = array(
			'errcode' => 0,
			'errmsg' => 'error'
		);

if ($op == "listcat") {
	$client = "";
	foreach ($_REQUEST as $key => $value) 
	{
    		$client = $client. "Key: $key; Value: $value ";
	}

	writeLog("listcat: ". json_encode(getAroundLatLng($lat, $lng, 3000)));
	$eventCat = array();
	$queryEvent = $_SGLOBAL['db']->query("SELECT * FROM ".tname("eventclass")." order by displayorder asc");
	while($value=$_SGLOBAL['db']->fetch_array($queryEvent)) {
		$value['pic'] = $_SGLOBAL['eventclass'][$value['classid']]['poster'];
		$eventCat[] = $value;
	}
	$result['eventcat'] = $eventCat;
	echo json_encode($result);
} else if ($op == "listeventtype") {
        $eventType = array();
        $queryEvent = $_SGLOBAL['db']->query("SELECT * FROM ".tname("eventtype")." where typelimit != 1");
        while($value=$_SGLOBAL['db']->fetch_array($queryEvent)) {
		if ($queryEvent['typeid'] == 25) continue;
                $eventType[] = $value;
        }
        $result['eventtype'] = $eventType;
        echo json_encode($result);
}  else if ($op == "addeventtype") {
                $setarr = array();
                $setarr['typename'] = "招募";
                $setarr['typeicon'] = "http://dload.ququyx.com/upload/pic_event_recruit.png";
                $albumid = inserttable('eventtype', $setarr, 1);

        $result['eventtype'] = $albumid;
        echo json_encode($result);
} else if ($op == "addevent") {
	writeLogDebug(",,,,,,,,,,,,,,,,,,,,,,,,,,,,,,, addevent-groupid".$_POST['groupid']);
	$queryEvent = $_SGLOBAL['db']->query("SELECT e.*, ef.* FROM ".tname("event")." e LEFT JOIN ".tname("eventfield")." ef ON e.eventid=ef.eventid Order by e.dateline desc limit 1");
        $eventOld = $_SGLOBAL['db']->fetch_array($queryEvent);
        if($eventOld){
	    writeLogDebug("...................... 111111 ".$_SGLOBAL['timestamp']." endtime".$eventOld['endtime']." grade".$eventOld['grade']." classid".$eventOld['classid']."|".$_POST['classid']."...typeid".$eventOld['typeid']."|".$_POST['typeid']);
	    if ($_SGLOBAL['timestamp'] < $eventOld['endtime'] && $eventOld['grade'] != -2
		&& $eventOld['classid']==$_POST['classid'] && $eventOld['typeid']==$_POST['typeid']) {
			writeLogDebug(",,,,,,,,,,,,,,,,,,,,,,,,,,,,,, ||||||||||||||||||||| end");
                        //showmessage("event_does_not_exist"); // 活动不存在或者已被删除
                        $result['errcode'] = -1;
                        $result['errmsg'] = "同游戏同类别的活动还未结束";
                        echo json_encode($result);
                        return;
		}

        }
	writeLogDebug("?????????????????????????????");
        $space = getspace($uid);
        $_SGLOBAL['supe_uid'] = $uid;
        $_SGLOBAL['supe_username'] = $space['name'];
	$deadtime = empty($_POST['deadtime'])?86400:intval($_POST['deadtime'])/1000;	
	// 基本信息
	$arr1 = array(
		"title" => getstr($_POST['title'], 80, 1, 1, 1),
		"classid" => intval($_POST['classid']),
		"typeid" => intval($_POST['typeid']),
		"groupid" => $_POST['groupid'],
		"server" => intval($_POST['server']),
		"province" => getstr('北京', 20, 1, 1),
		"city" => getstr('朝阳', 20, 1, 1),
		"location" => getstr('', 80, 1, 1, 1),
		"starttime" => $_SGLOBAL['timestamp'],//sstrtotime($_SGLOBAL['timestamp']),
		"endtime" => $_SGLOBAL['timestamp']+$deadtime,//1000000,//sstrtotime($_SGLOBAL['timestamp'] + 1000000),
		"deadline" => $_SGLOBAL['timestamp'] + $deadtime,//0000,//sstrtotime($_SGLOBAL['timestamp'] + 1000000),
		"public" => 2,//intval($_POST['public'])
		"lat" => $lat,
		"lng" => $lng
	);
	$appversion= empty($_GET['appversion'])?"":$_GET['appversion'];
        $limit = 10;
	if ($appversion > "2.0.1") {
	    $limit = 0;
	}
	// 扩展信息
	$arr2 = array(
		"detail" => getstr($_POST['detail'], '', 1, 1, 1, 0, 1),
		"limitnum" => $limit, //intval($_POST['limitnum']),
		"verify" => 0, //intval($_POST['verify']),
		"allowpost" => 1, //intval($_POST['allowpost']),
		"allowpic" => 1, //intval($_POST['allowpic']),
		"allowfellow" => 0, //intval($_POST['allowfellow']),
		"allowinvite" => 1, //intval($_POST['allowinvite']),
		"template" => getstr(0, 255, 1, 1, 1) //$_POST['template']
	);
	
	//echo json_encode($arr1);
	
	//检查输入
	if(empty($arr1['title'])){
		$result['errcode'] = 40001;
		$result['errmsg'] = 'event_title_empty';
	} elseif(empty($arr1['classid'])){
		$result['errcode'] = 40001;
		$result['errmsg'] = 'event_classid_empty';
	} elseif(empty($arr1['city'])) {
		$result['errcode'] = 40001;
		$result['errmsg'] = 'event_city_empty';
	}/* elseif(empty($arr2['detail'])) {
		$result['errcode'] = 40001;
		$result['errmsg'] = 'event_detail_empty';
	}*/ elseif($arr1['endtime']-$arr1['starttime']>360 * 24 * 3600) {
		$result['errcode'] = 40001;
		$result['errmsg'] = 'event_bad_time_range'. "   " . $arr1['endtime']-$arr1['starttime'];		
	} elseif($arr1['endtime']<$arr1['starttime']) {
		$result['errcode'] = 40001;
		$result['errmsg'] = 'event_bad_endtime';
	} elseif($arr1['deadline']>$arr1['endtime']) {
		$result['errcode'] = 40001;
		$result['errmsg'] = 'event_bad_deadline';
	} elseif(!$eventid && $arr1['starttime']<$_SGLOBAL['timestamp']) {
                  $result['errcode'] = 40001;
                  $result['errmsg'] = 'event_bad_starttime+'. $arr1['starttime'] . ":". $_SGLOBAL['timestamp'];
          } 	
	
	$arr1['topicid'] = 0;//$_POST['topicid'];
	
	// 创建者
	$arr1['uid'] = $uid;
	$arr1['username'] = $_SGLOBAL['supe_username'];
	// 创建时间
	$arr1['dateline'] = $_SGLOBAL['timestamp'];
	$arr1['updatetime'] = $_SGLOBAL['timestamp'];
	
	//人数
	$arr1['membernum'] = 1;
	
	// 是否需要审核
	$arr1['grade'] = 1;//checkperm("verifyevent") ? 0 : 1;
	
	// 插入 活动（event） 表
	$eventid = inserttable("event", $arr1, 1);


	if (! $eventid){
		$result['errcode'] = 40002;
		$result['errmsg'] = "event_create_failed"; // 创建活动失败，请检查你输入的内容
	}
	// 活动信息
	$arr2['eventid'] = $eventid;
	inserttable("eventfield", $arr2);


	$arr3 = array(
			"eventid" => $eventid,
			"uid" => $arr1['uid'],
			"username" => $arr1['username'],
			"status" => 4,  // 发起者
			"fellow" => 0,
			"template" => $arr1['template'],
			"dateline" => $_SGLOBAL['timestamp']
		   );
	// 插入 用户活动（userevent） 表
	inserttable("userevent", $arr3);
	
	//统计
	updatestat('event');
	
	//include_once(S_ROOT.'./source/function_cp.php');
	
	//更新用户统计
	if(empty($space['eventnum'])) {
		$space['eventnum'] = getcount('event', array('uid'=>$space['uid']));
		$eventnumsql = "eventnum=".$space['eventnum'];
	} else {
		$eventnumsql = 'eventnum=eventnum+1';
	}
	writeLogDebug(".....................end......................");	
	//积分
	/*$reward = getreward('createevent', 0);
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET {$eventnumsql}, lastpost='$_SGLOBAL[timestamp]', updatetime='$_SGLOBAL[timestamp]', credit=credit+$reward[credit], experience=experience+$reward[experience] WHERE uid=$arr1[uid]");*/
	echo json_encode($result);

	fastcgi_finish_request();
	if ($arr1['typeid'] == 27 || $arr1['typeid'] == 28) {
	if (!empty($_POST['groupid'])) {
	    writeLogDebug("--------create event, and groupid is set:".$_POST['groupid']);
	    return;
	}
	sleep(1);
	writeLogDebug("*********************************************************");
	                $queryTypeIcon = $_SGLOBAL['db']->query("SELECT typename,typeicon FROM ".tname("eventtype"). " where typeid=".$arr1['typeid']);
                $eventTypeIconValue = $_SGLOBAL['db']->fetch_array($queryTypeIcon);
	$groupid = createGroup($eventTypeIconValue['typename'].":".$arr1['title'], $eventTypeIconValue['typename'].":".$arr1['title']."_".$uid."_".$eventid, $uid);
	writeLogDebug("**************************************************************** groupid: ".$groupid);
	if (!empty($groupid)) {
	    $_SGLOBAL['db']->query("UPDATE ".tname('event')." SET groupid='$groupid' WHERE eventid='$eventid'"); 
	    //sendGroupMsg(98, $groupid, "test msg");
	    //sendMsg(98, 2, "test");

	}
	}

} else if ($op == "listevent") {
	
	$view = 'all';
	$type = 'going';  // going 
	$wherearr = array();
	$fromsql = $joinsql = $orderby = '';
	$needquery = true;
	
	$fromsql = tname("event")." e";//, ".tname("eventtype")." et ";
	$wherearr[] = "e.grade!=-2";
	$wherearr[] = "e.grade!=-1";
	//$wherearr[] = "e.typeid=et.typeid";
	$orderby = " e.updatetime DESC";
	$theurl .= "&type=going";
	$selectsql = "";
	$isJoin = empty($_POST['isjoin'])?'':$_POST['isjoin'];
	$isMy = empty($_POST['ismy'])?'':$_POST['ismy'];
	if ($isMy == "yes") {
	    $wherearr[] = " e.uid = ".$uid;
	} else {
	    if ($isJoin != "yes") {
	        $wherearr[] = "e.endtime >= '$_SGLOBAL[timestamp]'";
	    }
	}

	if ($isJoin == "yes") {
	    $fromsql = tname("event")." e, ".tname("userevent")." ue ";
	    $wherearr[] = "e.eventid=ue.eventid";
	    $wherearr[] = "ue.uid=$uid";
	    $wherearr[] = "e.uid!=$uid";
	    $selectsql = ",ue.uid,ue.eventid";
	}

	$isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
        $dateline = empty($_POST['dateline'])?'0':$_POST['dateline'];
        $where = "";
        if ($isRefresh == "yes") {
            $wherearr[] = " e.dateline > ".$dateline;
	    $orderby = " e.updatetime DESC";
        } else if($isRefresh == "no"){
            $wherearr[] = " e.dateline < ".$dateline;
	    $wherearr[] = " e.updatetime < '$_SGLOBAL[timestamp]'";
            $orderby = " e.dateline DESC";
        }
	if (empty($dateline)) {
                $rewardtmp = getrewardapp('daylogin', 1, $uid);
                if($rewardtmp['credit'] > 0) {
                    $result['gongxi'] = "恭喜获得每日登录奖励：积分+".$rewardtmp['credit'];
                }

        }
        $typeidFilter = empty($_POST['type'])?'':$_POST['type'];
        if (!empty($typeidFilter)) {
            $wherearr[] = " e.typeid = " . $typeidFilter;
        }
        $game = empty($_POST['game'])?'':$_POST['game'];
        if (!empty($game)) {
            $queryContact = $_SGLOBAL['db']->query('SELECT qq, msn, sex, email, mobile,event,tags FROM '.tname('spacefield')." WHERE uid = '$uid'");
            $valueContact = $_SGLOBAL['db']->fetch_array($queryContact);
            $mygames = $valueContact['event'];
            $arr = explode(",",$mygames);

            $wherearrb = array();
            foreach($arr as $u){
                if ($u > 0) {
                    $wherearrb[] = " e.classid = " . $u . " ";
                }
            }
            $sqlb = " ( ".implode(" OR ", $wherearrb)." ) ";
	    if (strlen($sqlb) > 8) {
                $wherearr[] = $sqlb;
	    }
        }

	
	$start = 0;
	$perpage = 12;

	
	$sort = empty($_POST['sort'])?'':$_POST['sort'];
	$sql = "SELECT e.eventid,e.updatetime, e.typeid,e.uid,e.username,e.title,e.dateline, e.endtime, e.classid,e.membernum,e.follownum,e.viewnum,e.grade $selectsql FROM $fromsql $joinsql WHERE ".implode(" AND ", $wherearr) ." ORDER BY $orderby LIMIT $start, $perpage";	

	writeLogDebug($sql);
	$query = $_SGLOBAL['db']->query($sql);
	
	while($event = $_SGLOBAL['db']->fetch_array($query)){
		/*if($event['poster']){
			$event['pic'] = pic_get($event['poster'], $event['thumb'], $event['remote']);
		} else {
			$event['pic'] = $_SGLOBAL['eventclass'][$event['classid']]['poster'];
		}*/
		realname_set($event['uid'], $event['username']);

		$avatar_exists = ckavatar($event['uid']);
		if ($avatar_exists ==1) {
			$avatarfile = avatar_file($event['uid'], 'middle');
	                $event["avatarfile"] = $avatarfile;
		}
		$event['lefttime'] = $event['endtime'] - $_SGLOBAL['timestamp'];
	        $queryCredit = $_SGLOBAL['db']->query("SELECT sex FROM ".tname('spacefield')." WHERE uid='$event[uid]'");
        	$valueCredi = $_SGLOBAL['db']->fetch_array($queryCredit);
        	$event['sex'] = $valueCredi['sex'];

		$queryTypeIcon = $_SGLOBAL['db']->query("SELECT typename,typeicon FROM ".tname("eventtype"). " where typeid=".$event['typeid']);
                $eventTypeIconValue = $_SGLOBAL['db']->fetch_array($queryTypeIcon);
                $event["typeicon"] = $eventTypeIconValue['typeicon'];
		$event["typename"] = $eventTypeIconValue['typename'];

		$queryEvent = $_SGLOBAL['db']->query("SELECT * FROM ".tname("eventclass"). " where classid=".$event['classid']);
		$eventCValue = $_SGLOBAL['db']->fetch_array($queryEvent);
		$event["classname"] = $eventCValue['classname'];
		$eventid = $event['eventid']; 
		$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(*) FROM ".tname('comment')." WHERE id='$eventid' AND idtype='eventid' ORDER BY dateline DESC"),0);
		$event['follownum'] = $count;
		if ($event['grade'] == 2) {
		    $event['istop'] = 1;
		}
		$eventlist[] = $event;
	}
	$result['eventlist'] = $eventlist;
	echo json_encode($result);
} else if ($op == "event") {
	
	$eventid = isset($_POST['id']) ? intval($_POST['id']) : 0;
	$myid = isset($_POST['uid']) ? intval($_POST['uid']) : 0;
	$view = isset($_GET['view']) ? $_GET['view'] : "all";
	
		// 活动信息
	$query = $_SGLOBAL['db']->query("SELECT e.eventid,e.groupid,e.server,e.reward, e.typeid,e.uid,e.username,e.title, e.endtime, e.dateline,e.classid,e.membernum,e.follownum,e.viewnum, ef.limitnum FROM ".tname("event")." e LEFT JOIN ".tname("eventfield")." ef ON e.eventid=ef.eventid WHERE e.eventid='$eventid'");
	$event = $_SGLOBAL['db']->fetch_array($query);
	if(! $event){
		$result['errmsg'] = "event_does_not_exist"; // 活动不存在或者已被删除
	}

	$event['lefttime'] = $event['endtime'] - $_SGLOBAL['timestamp']; 
	$tmpId = $event['uid'];
	$queryContact = $_SGLOBAL['db']->query('SELECT qq, msn,sex FROM '.tname('spacefield')." WHERE uid = '$tmpId'");
        $valueContact = $_SGLOBAL['db']->fetch_array($queryContact);

        $event['qq'] = $valueContact['qq'];
        $event['weixin'] = $valueContact['msn'];
	$event['sex'] = $valueContact['sex'];

	$avatar_exists = ckavatar($tmpId);
        if($avatar_exists) {
            $avatarfile = avatar_file($tmpId, 'middle');
            $event["avatarfile"] = $avatarfile;
	}

        $queryTypeIcon = $_SGLOBAL['db']->query("SELECT typename,typeicon FROM ".tname("eventtype"). " where typeid=".$event['typeid']);
        $eventTypeIconValue = $_SGLOBAL['db']->fetch_array($queryTypeIcon);
        $event["typeicon"] = $eventTypeIconValue['typeicon'];
	$event["typename"] = $eventTypeIconValue['typename'];
	$event["typecate"] = $eventTypeIconValue['typecate'];


	$queryEvent = $_SGLOBAL['db']->query("SELECT * FROM ".tname("eventclass"). " where classid=".$event['classid']);
        $eventCValue = $_SGLOBAL['db']->fetch_array($queryEvent);
        $event["classname"] = $eventCValue['classname'];
	$event['classpic'] = $_SGLOBAL['eventclass'][$event['classid']]['poster'];

	//hebinbin
	$event['isfriend'] = getfriendstatus($myid, $event['uid']);


	$queryCredit = $_SGLOBAL['db']->query("SELECT credit, name FROM ".tname('space')." WHERE uid='$event[uid]'");
        $valueCredi = $_SGLOBAL['db']->fetch_array($queryCredit);
        $event['credit'] = $valueCredi['credit'];
	$event['name'] = $valueCredi['name'];
	
		// 查看活动综合
		// 处理活动介绍
		include_once(S_ROOT.'./source/function_blog.php');
		
		$event['detail'] = blog_bbcode($event['detail']);
		// 海报
		/*if($event['poster']){
			$event['pic'] = pic_get($event['poster'], $event['thumb'], $event['remote'], 0);
		} else {
			$event['pic'] = $_SGLOBAL['eventclass'][$event['classid']]['poster'];
		}*/
		$event['isjoin'] = 0;	
		// 活动成员
		$members = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("userevent")." WHERE eventid = '$eventid' AND status=2 ORDER BY dateline DESC LIMIT 14");
		while($value = $_SGLOBAL['db']->fetch_array($query)){
			realname_set($value['uid'], $value['username']);
			if ($myid == $value['uid']) {
			    $event['isjoin'] = 1;
			}
			$avatar_exists = ckavatar($value['uid']);
		        if($avatar_exists) {
            		    $avatarfile = avatar_file($value['uid'], 'middle');
            		    $value["avatarfile"] = $avatarfile;
        		}

			$members[] = $value;
			$relateduids[] = $value['uid'];
		}
	$event['members'] = $members;
/*		// 感兴趣的
		$follows = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("userevent")." WHERE eventid='$eventid' AND status=1 ORDER BY dateline DESC LIMIT 12");
		while($value = $_SGLOBAL['db']->fetch_array($query)){
			realname_set($value['uid'], $value['username']);
			$follows[] = $value;
		}
	$result['follows'] = $follows;
		// 待审核人数
		$verifynum = 0;
		if($_SGLOBAL['supe_userevent']['status'] >= 3){
			$verifynum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(*) FROM ".tname("userevent")." WHERE eventid = '$eventid' AND status=0"),0);
		}

		// 参加这个活动的人也参加了那些活动
		$relatedevents = array();
		if($relateduids){
			$query = $_SGLOBAL['db']->query("SELECT e.*, ue.* FROM ".tname("userevent")." ue LEFT JOIN ".tname("event")." e ON ue.eventid=e.eventid WHERE ue.uid IN (".simplode($relateduids).") ORDER BY ue.dateline DESC LIMIT 0,8");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$relatedevents[$value['eventid']] = $value;
			}
		}*/

		// 活动留言，取20条
		$comments = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE id='$eventid' AND idtype='eventid' ORDER BY dateline DESC LIMIT 20");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['authorid'], $value['author']);
			$avatar_exists = ckavatar($value['authorid']);
                        if ($avatar_exists ==1) {
                                $avatarfile = avatar_file($value['authorid'], 'middle');
                                $value["avatarfile"] = $avatarfile;
                        }
			$comments[] = $value;
		}
		$event['comments'] = $comments;
		// 活动照片
		$photolist = $badpicids = array();
		$albumname = "photoalbumssystem_".$uid."_".$eventid;
		//$query = $_SGLOBAL['db']->query("SELECT pic.*, ep.* FROM ".tname("eventpic")." ep LEFT JOIN ".tname("pic")." pic ON ep.picid = pic.picid WHERE ep.eventid='$eventid' ORDER BY ep.picid DESC LIMIT 10");
		$query = $_SGLOBAL['db']->query("SELECT pic.*, a.albumid,a.albumname FROM ".tname("album")." a, ".tname("pic")." pic  WHERE a.albumid=pic.albumid and a.albumname='$albumname' ORDER BY pic.dateline DESC LIMIT 10");
		
		while($value = $_SGLOBAL['db']->fetch_array($query)){
			if(!$value['filepath']){//照片已经被删除
				$badpicids[] = $value['picid'];
				continue;
			}
			realname_set($value['uid'], $value['username']);
			$value['pic'] = pic_get($value['filepath'], $value['thumb'], $value['remote']);
			$photolist[] = $value;
		}

		if($badpicids) {
			$_SGLOBAL['db']->query("DELETE FROM ".tname("eventpic")." WHERE eventid='$eventid' AND picid IN (".simplode($badpicids).")");
		}
		$event['photos'] = $photolist;

		//活动话题
		$threadlist = array();
		if($event['tagid']) {
			$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('thread')." WHERE eventid='$eventid'"),0);
			if($count) {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread')." WHERE eventid='$eventid' ORDER BY lastpost DESC LIMIT 10");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					realname_set($value['uid'], $value['username']);
					$threadlist[] = $value;
				}
			}
		}

		// 活动查看数加 1
		if(true){//$event['uid'] != $_SGLOBAL['supe_uid']){
			$_SGLOBAL['db']->query("UPDATE ".tname("event")." SET viewnum=viewnum+1 WHERE eventid='$eventid'");
			$event['viewnum'] += 1;
		}

		//活动开始倒计时
		if($event['starttime'] > $_SGLOBAL['timestamp']) {
			$countdown = intval((mktime(0,0,0,gmdate('m',$event['starttime']),gmdate('d',$event['starttime']),gmdate('Y',$event['starttime'])) -
						mktime(0,0,0,gmdate('m',$_SGLOBAL['timestamp']),gmdate('d',$_SGLOBAL['timestamp']),gmdate('Y',$_SGLOBAL['timestamp']))) / 86400);
		}
		$result['event'] = $event;
		echo json_encode($result);
	
} else if($op == "join") {

	$eventid = isset($_POST['id']) ? intval($_POST['id']) : 0;
	$groupid = "";
	if($eventid){
        	$query = $_SGLOBAL['db']->query("SELECT e.*, ef.* FROM ".tname("event")." e LEFT JOIN ".tname("eventfield")." ef ON e.eventid=ef.eventid WHERE e.eventid='$eventid'");
        	$event = $_SGLOBAL['db']->fetch_array($query);
        	if(! $event){
                	//showmessage("event_does_not_exist"); // 活动不存在或者已被删除
	                $result['errcode'] = -1;
	                $result['errmsg'] = "活动不存在或者已被删除";
        	        echo json_encode($result);
                	return;

        	}
        	if($event['grade']==-1 || $event['grade'] == 0 ){
                	//showmessage('event_under_verify');// 活动正在审核中
                        $result['errcode'] = -1;
                        $result['errmsg'] = "活动不存在或者已被删除";
                        echo json_encode($result);
                        return;

        	}
		$groupid = $event['groupid'];
        	$query = $_SGLOBAL['db']->query("SELECT * FROM " . tname("userevent") . " WHERE eventid='$eventid' AND uid='$_SGLOBAL[supe_uid]'");
        	$value = $_SGLOBAL['db']->fetch_array($query);
        	$_SGLOBAL['supe_userevent'] = $value ? $value : array();
	} else {
		$result['errcode'] = -1;
		$result['errmsg'] = "活动不存在或者已被删除";
                echo json_encode($result);
		return;

	}

        if(isblacklist($event['uid'])) {
                $_GET['popupmenu_box'] = true;//开启关闭
//                showmessage('is_blacklist');//黑名单
                $result['errcode'] = -1;
                $result['errmsg'] = "活动不存在或者已被删除";
                echo json_encode($result);
                return;

        }
        //新成员加入，检查加入条件
        if(empty($_SGLOBAL['supe_userevent'])){
                $_GET['popupmenu_box'] = true;//开启关闭
                if($_SGLOBAL['timestamp'] > $event['endtime']){
                        //showmessage('event_is_over');// 活动已经结束
	                $result['errcode'] = -1;
        	        $result['errmsg'] = "活动已经结束";
                	echo json_encode($result);
                	return;

                }

                if($_SGLOBAL['timestamp'] > $event['deadline']){
                        //showmessage("event_meet_deadline"); // 活动已经截止报名
                        $result['errcode'] = -1;
                        $result['errmsg'] = "活动已经截止报名";
                        echo json_encode($result);
                        return;

                }

                if($event['limitnum']>0 && $event['membernum']>=$event['limitnum']){
//                        showmessage('event_already_full');//活动人数已满
                        $result['errcode'] = -1;
                        $result['errmsg'] = "活动人数已满";
                        echo json_encode($result);
                        return;

                }

        } else {
                        $result['errcode'] = -1;
                        $result['errmsg'] = "已参加过了";
                        echo json_encode($result);
                        return;

	}

        $space = getspace($uid);
        $_SGLOBAL['supe_uid'] = $uid;
        $_SGLOBAL['supe_username'] = $space['name'];

        if(true){
                // 审核状态的人修改报名信息
                /*if(!empty($_SGLOBAL['supe_userevent']) && $_SGLOBAL['supe_userevent']['status'] == 0){
                        $arr = array();

                        if(isset($_POST['fellow'])){
                                $arr['fellow'] = intval($_POST['fellow']);// 修改携带人数
                        }
                        if($_POST['template']){// 报名信息
                                $arr['template'] = getstr($_POST['template'], 255, 1, 1, 1);
                        }
                        if($arr){
                                updatetable("userevent", $arr, array("eventid"=>$eventid, "uid"=>$_SGLOBAL['supe_uid']));
                        }
                        showmessage("do_success", "space.php?do=event&id=$eventid", 2);
                }

                // 已经参加活动的人，修改报名信息
                if(!empty($_SGLOBAL['supe_userevent']) && $_SGLOBAL['supe_userevent']['status'] > 1){
                        $arr = array();
                        $num = 0; // 活动参与人数变化
                        if(isset($_POST['fellow'])){// 修改携带人数
                                $_POST['fellow'] = intval($_POST['fellow']);
                                $arr['fellow'] = $_POST['fellow'];// 修改参加人数
                                $num = $_POST['fellow'] - $_SGLOBAL['supe_userevent']['fellow'];
                                // 检查人数
                                if ($event['limitnum'] > 0 && $num + $event['membernum'] > $event['limitnum']){
                                        showmessage("event_already_full");
                                }
                        }
                        if($_POST['template']){// 报名信息
                                $arr['template'] = $_POST['template'];
                        }
                        if($arr){
                                updatetable("userevent", $arr, array("eventid"=>$eventid, "uid"=>$_SGLOBAL['supe_uid']));
                        }
                        if($num){
                                $_SGLOBAL['db']->query("UPDATE " . tname("event") . " SET membernum = membernum + ($num) WHERE eventid=$eventid");
                        }
                        showmessage("do_success", "space.php?do=event&id=$eventid", 0);
                }*/

                // 用户活动信息
                $arr = array(
                        "eventid" => $eventid,
                        "uid" => $_SGLOBAL['supe_uid'],
                        "username" => $_SGLOBAL['supe_username'],
                        "status" => 2,
                        "template" => $event['template'],
                        "fellow" => 0,
                        "dateline" => $_SGLOBAL['timestamp']
                   );
                // 活动人数变化
                $num = 1;
                $numsql = "";
                if($_POST['fellow']){
                        $arr['fellow'] = intval($_POST['fellow']);
                        $num += $arr['fellow'];
                }
                if($_POST['template']){// 报名信息
                        $arr['template'] = getstr($_POST['template'], 255, 1, 1, 1);
                }

                if ($event['limitnum'] > 0 && $num + $event['membernum'] > $event['limitnum']){
                        //showmessage("event_will_full");
	                        $result['errcode'] = -1;
                       $result['errmsg'] = "event_will_full";
                        echo json_encode($result);
                        return;

                }
                $numsql = " membernum = membernum + ($num) ";

                /*// 检查是否有活动邀请
                $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("eventinvite")." WHERE eventid='$eventid' AND touid='$_SGLOBAL[supe_uid]'");
                $eventinvite = $_SGLOBAL['db']->fetch_array($query);
                // 需要审核
                if($event['verify'] && !$eventinvite){
                        $arr['status'] = 0; // 待审核
                }*/

                // 插入 用户活动（userevent） 表                
                if($_SGLOBAL['supe_userevent']['status'] == 1){
                        // 关注者参加，关注人数减1
                        updatetable("userevent", $arr, array("uid"=>$_SGLOBAL['supe_uid'], "eventid"=>$eventid));
                        $numsql .= ",follownum = follownum - 1 ";
                } else {
                        // 直接参加
                        inserttable("userevent", $arr, 0);
                }
                // 活动人数（参加/关注）修改
                if($arr['status'] == 2){
                        $_SGLOBAL['db']->query("UPDATE " . tname("event") . " SET $numsql WHERE eventid = '$eventid'");
                        if(ckprivacy('join')){
                                realname_set($event['uid'], $event['username']);
                                realname_get();
                                feed_add('event', cplang('event_join'), array('title'=>$event['title'], "eventid"=>$event['eventid'], "uid"=>$event['uid'], "username"=>$_SN[$event['uid']]));
                        }
                } elseif($arr['status'] == 0){
                        if($_SGLOBAL['supe_userevent']['status'] == 1){
                                //关注人数减1
                                $_SGLOBAL['db']->query("UPDATE " . tname("event") . " SET follownum = follownum - 1 WHERE eventid = '$eventid'");
                        }
                        //给活动组织者发送审核通知
                        $note_inserts = array();
                        $note_ids = array();
                        $note_msg = cplang('event_join_verify', array("space.php?do=event&id=$event[eventid]", $event['title'], "cp.php?ac=event&id=$event[eventid]&op=members&status=0&key=$arr[username]"));
                        $query = $_SGLOBAL['db']->query("SELECT ue.*, sf.* FROM ".tname("userevent")." ue LEFT JOIN ".tname("spacefield")." sf ON ue.uid=sf.uid WHERE ue.eventid='$eventid' AND ue.status >= 3");
                        while($value=$_SGLOBAL['db']->fetch_array($query)){
                                $value['privacy'] = empty($value['privacy']) ? array() : unserialize($value['privacy']);
                                $filter = empty($value['privacy']['filter_note'])?array():array_keys($value['privacy']['filter_note']);
                                if(cknote_uid(array("type"=>"eventmember","authorid"=>$_SGLOBAL['supe_uid']),$filter)){
                                        $note_ids[] = $value['uid'];
                                        $note_inserts[] = "('$value[uid]', 'eventmember', '1', '$_SGLOBAL[supe_uid]', '$_SGLOBAL[supe_username]', '".addslashes($note_msg)."', '$_SGLOBAL[timestamp]')";
                                }
                        }
                        if($note_inserts){
                                $_SGLOBAL['db']->query("INSERT INTO ".tname('notification')." (`uid`, `type`, `new`, `authorid`, `author`, `note`, `dateline`) VALUES ".implode(',', $note_inserts));
                                $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET notenum=notenum+1 WHERE uid IN (".simplode($note_ids).")");
                        }
                        //邮件提醒
                        smail($event['uid'], '', $note_msg, 'event');
                }

                //奖励积分
                getreward('joinevent', 1, 0, $eventid);

                //统计
                updatestat('eventjoin');

                //处理活动邀请
                if($eventinvite){
                        $_SGLOBAL['db']->query("DELETE FROM ".tname("eventinvite")." WHERE eventid='$eventid' AND touid='$_SGLOBAL[supe_uid]'");
                        $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET eventinvitenum=eventinvitenum-1 WHERE uid = '$_SGLOBAL[supe_uid]' AND eventinvitenum>0");
                }

                //showmessage("do_success", "space.php?do=event&id=$eventid", 0); // 加入活动成功
                        $result['errcode'] = 0;
                        $result['errmsg'] = "success";
                        echo json_encode($result);
	//	return;
		fastcgi_finish_request();
		if (!empty($groupid)) {
		     addToGroup($groupid, $uid);
		    sendGroupMsg($uid, $groupid, $space['name']."参加了群组");
		}
                       // return;

        }
          /*              $result['errcode'] = -1;
                        $result['errmsg'] = "error";
                        echo json_encode($result);
                        return;*/


} else if($op == "comment") {

        $_SGLOBAL['supe_uid'] = $uid;
        $spacea = getspace($uid);
        $_SGLOBAL['supe_username'] = $spacea['name'];


        $message = empty($_POST['message'])?'':$_POST['message'];
        //摘要
        $summay = getstr($message, 150, 1, 1, 0, 0, -1);

        $id = intval($_POST['id']);
        $albumid="0";
        $hotarr = array();
        $stattype = '';

        //引用评论
        $cid = empty($_POST['cid'])?0:intval($_POST['cid']);
        $comment = array();
        if($cid) {
                $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE cid='$cid' AND id='$id' AND idtype='eventid'");
                writeLogDebug("SELECT * FROM ".tname('comment')." WHERE cid='$cid' AND id='$id' AND idtype='eventid'");
                $comment = $_SGLOBAL['db']->fetch_array($query);
                if($comment && $comment['authorid'] != $_SGLOBAL['supe_uid']) {
                        //实名
                        if($comment['author'] == '') {
                                $_SN[$comment['authorid']] = lang('hidden_username');
                        } else {
                                realname_set($comment['authorid'], $comment['author']);
                                realname_get();
                        }
                        //$comment['message'] = preg_replace("/\<div class=\"quote\"\>\<span class=\"q\"\>.*?\<\/span\>\<\/div\>/is", '', $comment['message']);
                        //bbcode转换
                        //$comment['message'] = html2bbcode($comment['message']);
                } else {
                        $comment = array();
                }
        }

                    // 读取活动
                    $query = $_SGLOBAL['db']->query("SELECT e.*, ef.* FROM ".tname('event')." e LEFT JOIN ".tname("eventfield")." ef ON e.eventid=ef.eventid WHERE e.eventid='$id'");
                        $event = $_SGLOBAL['db']->fetch_array($query);

                        if(empty($event)) {
                                //showmessage('event_does_not_exist');
				$result['errcode'] = -1;
				$result['errmsg'] = "活动不存在或者已被删除";
				echo json_encode($result);
				return;

                        }

                        if($event['grade'] < -1){
                                //showmessage('event_is_closed');//活动已经关闭
                		$result['errcode'] = -1;
               		  	$result['errmsg'] = "活动已经关闭";
                		echo json_encode($result);
                		return;

                        } elseif($event['grade'] <= 0){
                                //showmessage('event_under_verify');//活动未通过审核
                		$result['errcode'] = -1;
               		  	$result['errmsg'] = "活动未通过审核";
                		echo json_encode($result);
                		return;
                        }

                        if(!$event['allowpost']){
                                $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("userevent")." WHERE eventid='$id' AND uid='$_SGLOBAL[supe_uid]' LIMIT 1");
                                $value = $_SGLOBAL['db']->fetch_array($query);
                                if(empty($value) || $value['status'] < 2){
                                        //showmessage('event_only_allows_members_to_comment');//只有活动成员允许发表留言
                			$result['errcode'] = -1;
               		  		$result['errmsg'] = "只有活动成员允许发表留言";
                			echo json_encode($result);
                			return;
                                }
                        }

                        //检索空间
                        $tospace = getspace($event['uid']);

                        $hotarr = array('eventid', $event['eventid'], $event['hotuser']);
                        $stattype = 'eventcomment';//统计


        //事件
        $fs = array();
        $fs['icon'] = 'comment';
        $fs['target_ids'] = $fs['friend'] = '';

        // 活动
        $fs['title_template'] = cplang('feed_comment_event');
        $fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$tospace[uid]\">".$_SN[$tospace['uid']]."</a>", 'event'=>'<a href="space.php?do=event&id='.$event['eventid'].'">'.$event['title'].'</a>');
        $fs['body_template'] = '';
        $fs['body_data'] = array();
        $fs['body_general'] = '';

        $setarr = array(
                'uid' => $tospace['uid'],
                'id' => $id,
                'idtype' => "eventid",
                'authorid' => $_SGLOBAL['supe_uid'],
                'author' => $_SGLOBAL['supe_username'],
                'dateline' => $_SGLOBAL['timestamp'],
                'message' => $message,
                'ip' => getonlineip()
        );

        if(!empty($comment)) {
            $setarr['replyid'] = $comment['authorid'];
            $setarr['replyname'] = $comment['author'];
        }

        //入库
        $cid = inserttable('comment', $setarr, 1);
        //writeLogDebug("************* pid:".$pid."--albumid:".$albumid."----uid:".$comment['authorid']);
        /*$note_type = 'piccomment';
        $note = $_SGLOBAL['supe_username'].'评论了我的晒晒';
        $q_note = $_SGLOBAL['supe_username'].'回复了我';
        if(empty($comment)) {
                //非引用评论
                if($tospace['uid'] != $_SGLOBAL['supe_uid']) {
                        //发送通知
                        notification_app_add_ext($tospace['uid'], $albumid, $note_type, $summay, $note);
                }

        } elseif($comment['authorid'] != $_SGLOBAL['supe_uid']) {
                notification_app_add_ext($comment['authorid'], $albumid, $note_type, $summay, $q_note);

        }*/


        //统计
        if($stattype) {
                updatestat($stattype);
        }

    echo json_encode($result);


} else if($op == 'getcomment') {

        $eid = intval($_POST['id']);

        $cid = empty($_GET['cid'])?0:intval($_GET['cid']);
        $csql = $cid?"cid='$cid' AND":'';

        $list = array();
        $wherearr = array();
        $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
        $dateline = empty($_POST['dateline'])?'0':$_POST['dateline'];
        if ($isRefresh == "yes") {
            $wherearr[] = " dateline > ".$dateline;
        } else if($isRefresh == "no"){
            $wherearr[] = " dateline < ".$dateline;
        }
	if ($cid) {
            $wherearr[] = "cid='$cid'";
	}
        $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('comment')." WHERE ".implode(" AND ", $wherearr) ." AND id='$eid' AND idtype='eventid' order by dateline desc"),0);
	
        if($count) {
                $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE ".implode(" AND ", $wherearr) ." AND id='$eid' AND idtype='eventid' ORDER BY dateline DESC LIMIT 0,20");
                writeLogDebug("SELECT * FROM ".tname('comment')." WHERE $csql id='$pid' AND idtype='picid' ORDER BY dateline LIMIT 0,32");
                while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                        realname_set($value['authorid'], $value['author']);
                        $avatar_exists = ckavatar($value['authorid']);
                        if ($avatar_exists ==1) {
                                $avatarfile = avatar_file($value['authorid'], 'middle');
                                $value["avatarfile"] = $avatarfile;
                        }

                        $list[] = $value;
                }
        }

    $result['list'] = $list;
    echo json_encode($result);


} else if($op == "members") {

		$eventid = isset($_POST['id']) ? intval($_POST['id']) : 0;
           
	    $queryEvent = $_SGLOBAL['db']->query("SELECT e.uid FROM ".tname("event")." e WHERE e.eventid='$eventid'");
            $event = $_SGLOBAL['db']->fetch_array($queryEvent);
	    $adminuid = $event['uid'];
	    if ($uid == $adminuid) {
		$result['canadmin'] = 1;
	    }

	        // 活动成员
                $members = array();
                $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("userevent")." WHERE eventid = '$eventid' AND status=2 ORDER BY dateline DESC LIMIT 14");
                while($value = $_SGLOBAL['db']->fetch_array($query)){
                        realname_set($value['uid'], $value['username']);
                        if ($adminuid == $value['uid']) {
                            $value['isadmin'] = 1;
                        }
                        $avatar_exists = ckavatar($value['uid']);
                        if($avatar_exists) {
                            $avatarfile = avatar_file($value['uid'], 'middle');
                            $value["avatarfile"] = $avatarfile;
                        }

			$tmpId = $value['uid'];
		        $queryContact = $_SGLOBAL['db']->query('SELECT qq, msn FROM '.tname('spacefield')." WHERE uid = '$tmpId'");
        		$valueContact = $_SGLOBAL['db']->fetch_array($queryContact);
        		$value['qq'] = $valueContact['qq'];
        		$value['weixin'] = $valueContact['msn'];


                        $members[] = $value;
                        $relateduids[] = $value['uid'];
                }
//        $event['members'] = $members;
    $result['list'] = $members;
    echo json_encode($result);


} elseif ($op == "delete") {
    include_once(S_ROOT.'./source/function_delete.php');
    $eventid = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $_SGLOBAL['supe_uid'] = $uid;
    if (!empty($eventid)) {
	writeLogDebug(".........................delete");
        deleteevents(array($eventid));
        echo json_encode($result);
        return;
    }

    $result['errcode'] = -1;
    echo json_encode($result);
    return;

} elseif($op == "deletemember") {
    $eventid = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $muid = isset($_POST['mid']) ? intval($_POST['mid']) : 0;
    if (!empty($eventid) && !empty($muid)) {
        $_SGLOBAL['db']->query("DELETE FROM ".tname("userevent")." WHERE eventid = '$eventid' and  uid='$muid' ");    
        echo json_encode($result);
        return;
    }

    $result['errcode'] = -1;
    echo json_encode($result);
    return;

} elseif($op == "reward") {
    $eventid = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if (!empty($eventid) && !empty($uid)) {
	$_SGLOBAL['db']->query("UPDATE ".tname('event')." SET reward=1 WHERE eventid='$eventid'");
        echo json_encode($result);
        return;
    }

    $result['errcode'] = -1;
    echo json_encode($result);
    return;

} 

function createGroup($name, $des, $uid) {

        $formgettoken="https://a1.easemob.com/qingyijiu/comxinplusapp/chatgroups";
        $body=array(
                "groupname"=>$name,
                "desc"=>$des,
                "public"=>true,
                "maxusers"=>20,
		"approval"=>false,
		"owner"=>"$uid",
		"members"=>array(
                    "98","2","$uid"
                )
        );
        $patoken=json_encode($body);
	$token = _get_token();
        $header = array($token);
        $res = _curl_request($formgettoken,$patoken,$header, "POST");

        $arrayResult =  json_decode($res, true);

        writeLogDebug("...........................................................................token: ".$token);
        $arrayTmp = $arrayResult['data'];
        foreach($arrayTmp as $key => $value) {
                writeLogDebug("from".$from." to".$to."   ".$key.":".$value);
        }
        writeLogDebug("error_desc:".$arrayResult['error_description']." to".$to."   ".$arrayResult['data']);
        return $arrayResult['data']['groupid'] ;


}

//授权注册模式 POST /{org_name}/{app_name}/users
function sendMsg($from, $to, $msgvalue)
{
        $formgettoken="https://a1.easemob.com/qingyijiu/comxinplusapp/messages";
        $body=array(
                "target_type"=>"users",
                "target"=>array(
                    $to
                ),
                "msg"=>array(
                    "type"=>"txt",
                    "msg"=>$msgvalue
                ),
                "from"=>$from
        );
        $patoken=json_encode($body);
        $header = array(_get_token());
        $res = _curl_request($formgettoken,$patoken,$header);

        $arrayResult =  json_decode($res, true);

        writeLog("...........................................................................");
        writeLog("from".$from." to".$to."   ".$arrayResult);
        return $arrayResult ;
}
function sendGroupMsg($from, $to, $msgvalue)
{
        $formgettoken="https://a1.easemob.com/qingyijiu/comxinplusapp/messages";
        $body=array(
                "target_type"=>"chatgroups",
                "target"=>array(
                    $to
                ),
                "msg"=>array(
                    "type"=>"txt",
                    "msg"=>$msgvalue
                ),
                "from"=>$from
        );
        $patoken=json_encode($body);
        $header = array(_get_token());
        $res = _curl_request($formgettoken,$patoken,$header);

        $arrayResult =  json_decode($res, true);

        writeLog("...........................................................................");
        writeLog("from".$from." to".$to."   ".$arrayResult);
        return $arrayResult ;
}

function sendMsgImage($from, $to)
{
        $formgettoken="https://a1.easemob.com/qingyijiu/comxinplusapp/messages";
        $body=array(
                "target_type"=>"users",
                "target"=>array(
                    $to
                ),
                "msg"=>array(
                    "type"=>"img",
                    "url"=>"http://app.sihemob.com/home/image/new_tip.jpg",
                    "filename"=>"新手秘籍",
                    "secret"=>"YXA6JOhKGqeFatIdeN9UwiVj3MpXd_E"
                ),
                "from"=>$from
        );
        $patoken=json_encode($body);
        $header = array(_get_token());
        $res = _curl_request($formgettoken,$patoken,$header, "POST");

        $arrayResult =  json_decode($res, true);

        writeLog("...........................................................................");
        $arrayTmp = $arrayResult['data'];
        foreach($arrayTmp as $key => $value) {
                writeLog("from".$from." to".$to."   ".$key.":".$value);
        }
        writeLog("from".$from." to".$to."   ".$arrayResult['data']);
        return $arrayResult ;
}

function addToGroup($groupid, $uid)
{
	writeLogDebug(".........................................addToGroup");
        $formgettoken="https://a1.easemob.com/qingyijiu/comxinplusapp/chatgroups/$groupid/users/$uid";
        /*$body=array(
                "target_type"=>"users",
                "target"=>array(
                    $to
                ),
                "msg"=>array(
                    "type"=>"img",
                    "url"=>"http://app.sihemob.com/home/image/new_tip.jpg",
                    "filename"=>"新手秘籍",
                    "secret"=>"YXA6JOhKGqeFatIdeN9UwiVj3MpXd_E"
                ),
                "from"=>$from
        );*/
        $patoken="";//json_encode($body);
        $header = array(_get_token());
        $res = _curl_request($formgettoken,$patoken,$header, "POST");

        $arrayResult =  json_decode($res, true);

        writeLog("...........................................................................");
        $arrayTmp = $arrayResult['data'];
        foreach($arrayTmp as $key => $value) {
                writeLog("from".$from." to".$to."   ".$key.":".$value);
        }
        writeLog("from".$from." to".$to."   ".$arrayResult['data']);
        return $arrayResult ;
}

//授权注册模式 POST /{org_name}/{app_name}/users
function registerToken($nikename,$pwd)
{
        $formgettoken="https://a1.easemob.com/qingyijiu/comxinplusapp/users";
        $body=array(
                "username"=>$nikename,
                "password"=>$pwd,
        );
        $patoken=json_encode($body);
        $header = array(_get_token());
        $res = _curl_request($formgettoken,$patoken,$header);

        $arrayResult =  json_decode($res, true);
        return $arrayResult ;
}

//先获取app管理员token POST /{org_name}/{app_name}/token
function _get_token()
{
        $formgettoken="https://a1.easemob.com/qingyijiu/comxinplusapp/token";
        $body=array(
        "grant_type"=>"client_credentials",
        "client_id"=>"YXA6Q4HL4KU_EeSPDO0Ke1UxNQ",
        "client_secret"=>"YXA6JOhKGqeFatIdeN9UwiVj3MpXd_E"
        );
        $patoken=json_encode($body);
        $res = _curl_request($formgettoken,$patoken);
        $tokenResult = array();

        $tokenResult =  json_decode($res, true);
        //var_dump($tokenResult);
        return "Authorization: Bearer ". $tokenResult["access_token"];
}

function _curl_request($url, $body, $header = array(), $method = "POST")
{
        array_push($header, 'Accept:application/json');
        array_push($header, 'Content-Type:application/json');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, $method, 1);

        switch ($method){
                case "GET" :
                        curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
                case "POST":
                        curl_setopt($ch, CURLOPT_POST,true);
                break;
                case "PUT" :
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                break;
                case "DELETE":
                        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
        }

        curl_setopt($ch, CURLOPT_USERAGENT, 'SSTS Browser/1.0');
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        if (isset($body{3}) > 0) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        if (count($header) > 0) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        $ret = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);

        //clear_object($ch);
        //clear_object($body);
        //clear_object($header);

        if ($err) {
                return $err;
        }

        return $ret;
}


?>
