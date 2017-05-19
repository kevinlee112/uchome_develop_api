<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_ajax.php 12535 2009-07-06 06:22:34Z zhengqingpeng $
*/


if(!defined('IN_UCHOME')) {
        exit('Access Denied');
}
$op = empty($_POST['op'])?'':$_POST['op'];
$lat = empty($_GET['lat'])?'':$_GET['lat'];
$lng = empty($_GET['lng'])?'':$_GET['lng'];
$myId = empty($_POST['myid'])?'':$_POST['myid'];
$isfront = empty($_POST['isfront'])?"1":$_POST["isfront"];
$result = array(
                        'errcode' => 1001,
                        'errmsg' => 'error username or password'
                );
                //header('Content-type: text/json'); 
$result = array(
                                                'errcode' => 0,
                                                'errmsg' => ''
                                        );


//echo json_encode($result);
$randomInt = rand(0, 1000);
//writeLogKang("-------- random: ".$randomInt);
if ($randomInt < 10) {

echo json_encode($result);
return;
}

//writeLogKang("-------- random: ".$randomInt);

if ($op == 'request') {
        include_once(S_ROOT.'./source/function_cp.php');

        //$myId = empty($_POST['myid'])?'':$_POST['myid'];
        //writeLog("========== appmsg request uid : ".$myId);
        $space = getspace($myId);
        $uid = $myId;

        //好友请求
        $perpage = 20;
        $page = empty($_GET['page'])?0:intval($_GET['page']);
        if($page<1) $page = 1;
        $start = ($page-1)*$perpage;
       $friend1 = $space['friends'];
        $list = array();

        $count = getrequestcount('friend', array('fuid'=>$space['uid'], 'status'=>0));
        $result['request'] = $count;


        $friendRequestCount = getnoticecount('notification', array('uid'=>$space['uid'], 'new'=>1, 'type'=>'friendrequest'));
        $result['friendrequest'] = $friendRequestCount;
        if ($result['friendrequest'] > 0) {
                $query = $_SGLOBAL['db_slave']->query("SELECT * FROM ".tname('notification')." WHERE uid='$uid' and type='friendrequest' ORDER BY dateline DESC LIMIT 1");
            if ($value = $_SGLOBAL['db_slave']->fetch_array($query)) {
                $result['friendrequestdateline'] = $value['dateline'];
                $result['friendrequestnote'] = $value['author'].$value['note'];
            }        
	}



        $noticeCount = getnoticecount('notification', array('uid'=>$space['uid'], 'new'=>1, 'type'=>'friend'));
        $result['notice'] = $noticeCount;

        $noticeCountB = getnoticecount('notification', array('uid'=>$space['uid'], 'new'=>1, 'type'=>'clickpic', 'ext'=>'0'));
        $result['albumclick'] = $noticeCountB;
        if ($result['albumclick'] > 0) {
                $query = $_SGLOBAL['db_slave']->query("SELECT * FROM ".tname('notification')." WHERE uid='$uid' and type='clickpic' and new='1' ORDER BY dateline DESC LIMIT 1");
            if ($value = $_SGLOBAL['db_slave']->fetch_array($query)) {
                $result['albumdateline'] = $value['dateline'];
                $result['albumenote'] = $value['author']."点赞了您的晒晒";
            }        }

        $noticeCountP = getnoticecount('notification', array('uid'=>$space['uid'], 'new'=>1, 'type'=>'clickpic', 'ext'=>'14'));
        $result['albumpass'] = $noticeCountP;
        if ($result['albumpass'] > 0) {
                $query = $_SGLOBAL['db_slave']->query("SELECT * FROM ".tname('notification')." WHERE uid='$uid' and type='clickpic' and new='1' and ext='14' ORDER BY dateline DESC LIMIT 1");
            if ($value = $_SGLOBAL['db_slave']->fetch_array($query)) {
                $result['albumpassdateline'] = $value['dateline'];
                $result['albumpassnote'] = $value['author']."通过了您的大神鉴定";
            }        }
        $noticeCountC = getnoticecount('notification', array('uid'=>$space['uid'], 'new'=>1, 'type'=>'rp'));
        $result['rp'] = $noticeCountC;
        if ($result['rp'] > 0) {
            $query = $_SGLOBAL['db_slave']->query("SELECT * FROM ".tname('notification')." WHERE uid='$uid' and type='rp' and new='1' ORDER BY dateline DESC LIMIT 1");
            if ($value = $_SGLOBAL['db_slave']->fetch_array($query)) {
                $result['rpdateline'] = $value['dateline'];
                $result['rpnote'] = $value['note'];
            }

        }


        $noticeCountReply = getnoticecount('notification', array('uid'=>$space['uid'], 'new'=>1, 'type'=>'piccomment'));
        $result['reply'] = $noticeCountReply;
        if ($result['reply'] > 0) {
            $query = $_SGLOBAL['db_slave']->query("SELECT * FROM ".tname('notification')." WHERE uid='$uid' and type='piccomment' and new='1' ORDER BY dateline DESC LIMIT 1");
            if ($value = $_SGLOBAL['db_slave']->fetch_array($query)) {
                $result['replydateline'] = $value['dateline'];
                $result['replynote'] = $value['note'];
		$result['replyeid'] = $value['eid'];
            }

        }



        $noticeCountFeed = getnoticecount('notification', array('uid'=>$space['uid'], 'new'=>1, 'type'=>'uploadalbum'));
        $result['feed'] = $noticeCountFeed;
        if ($result['feed'] > 0) {
            $query = $_SGLOBAL['db_slave']->query("SELECT * FROM ".tname('notification')." WHERE uid='$uid' and type='uploadalbum' and new='1' ORDER BY dateline DESC LIMIT 1");
            if ($value = $_SGLOBAL['db_slave']->fetch_array($query)) {
                $result['feeddateline'] = $value['dateline'];
                $result['feednote'] = $value['note'];
            }

        }


        echo json_encode($result);

        fastcgi_finish_request();

        //writeLog("||||||||||||||||||****** update space table uid:".$myId."   lat:".$lat."  lng:".$lng);

        if ($lat > 2 && $lng > 2) {
            $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET lat='$lat', lng='$lng' WHERE uid='$myId'");
        }
	if ($isfront == "1") {
            $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET lastlogin='$_SGLOBAL[timestamp]' WHERE uid='$myId'");
	}

} elseif($op == 'addavatar') {

}



?>
