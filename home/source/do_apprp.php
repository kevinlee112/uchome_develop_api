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

writeLog("do_appsubject.php");

$op = empty($_POST['op'])?'':$_POST['op'];
$lat = empty($_GET['lat'])?'':$_GET['lat'];
$lng = empty($_GET['lng'])?'':$_GET['lng'];
$uid = empty($_POST['uid'])?'':$_POST['uid'];
$start = !empty($_GET['start']) ? intval($_GET['start']) : 0;
$perpage = !empty($_GET['perpage']) ? intval($_GET['perpage']) : 15;

$result = array(
    'errcode' => 0,
    'errmsg' => 'error'
);
$_SGLOBAL['supe_uid'] = $uid;
$space = getspace($uid);
$_SGLOBAL['supe_username'] = $space['name'];

writeLog("----------------------------------------uid:".$uid." op:".$op);
if($op == 'getnewmsg') {

        $noticeCountB = getnoticecount('notification', array('uid'=>$space['uid'], 'new'=>1, 'type'=>'clickpic'));
        $result['notice'] = $noticeCountB;
	if ($result['notice'] > 0) {
                $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('notification')." WHERE uid='$uid' and type='clickpic' and new='1' ORDER BY dateline DESC LIMIT 1");
            if ($value = $_SGLOBAL['db']->fetch_array($query)) {
                $result['noticedateline'] = $value['dateline'];
                $result['noticenote'] = $value['note'];
            }

	}

        $noticeCountC = getnoticecount('notification', array('uid'=>$space['uid'], 'new'=>1, 'type'=>'rp'));
        $result['rp'] = $noticeCountC;
        if ($result['rp'] > 0) {
            $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('notification')." WHERE uid='$uid' and type='rp' and new='1' ORDER BY dateline DESC LIMIT 1");
            if ($value = $_SGLOBAL['db']->fetch_array($query)) {
        	$result['rpdateline'] = $value['dateline'];
        	$result['rpnote'] = $value['note'];
    	    }

        }



    echo json_encode($result);


} elseif($op == "updaterp") {
    $tid = empty($_POST['tid'])?0:intval($_POST['tid']);
    $type = empty($_POST['type'])?1:$_POST['type'];


//        $ouid = empty($_POST['ouid'])?'':$_POST['ouid'];
        if (empty($tid) || empty($uid)) {
                $result['errcode'] = 1001;
                $result['errmsg'] = "uid is empty";
                echo json_encode($result);
                return;
        }

        $today = sstrtotime(sgmdate('Y-m-d'));
        writeLog("..................................".$today."---------type:".$_POST['type']);
        $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('notification')." WHERE authorid='$uid' AND type='rp' AND dateline > $today"), 0);
	if ($count>0) {
                $result['errcode'] = 1002;
                $result['errmsg'] = "今天已经不能加减人品了";
                echo json_encode($result);
                return;
	}

    $note = "";
    if ($type == 1) {
	$note = $_SGLOBAL['supe_username']."给我加了1点人品";
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET rp=rp+1 WHERE uid='$tid'");
    } else if ($type == -1) {

	$query = $_SGLOBAL['db']->query("SELECT uid,rp FROM ".tname('space')." WHERE uid='$tid' LIMIT 1");
        if ($value = $_SGLOBAL['db']->fetch_array($query)) {
	    /*if ($value['rp'] > 1) {
	        $note = $_SGLOBAL['supe_username']."给我减了1点人品";
	        $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET rp=rp-1 WHERE uid='$tid'");
	    } else {
                $result['errcode'] = 1003;
                $result['errmsg'] = "TA的人品已经不能再减了";
                echo json_encode($result);
                return;
	    }*/
	}
	$note = $_SGLOBAL['supe_username']."给我减了1点人品";
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET rp=rp-1 WHERE uid='$tid'");
    } else {
        $result['errcode'] = 1001;
        $result['errmsg'] = "type is empty";
        echo json_encode($result);
	return;
    }
	
    notification_app_add_ext($tid, 0, 'rp', $type, $note); 
	writeLog("*****************************************:****** album size".count($albumlist)." type:".$type);
        
        echo json_encode($result);


} elseif($op == 'getrplist') {
    $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('notification')." WHERE type='rp' AND uid=$uid ORDER BY dateline DESC LIMIT 0, 100");

    $loglist = array();
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	    $authorid = $value['authorid'];
            $avatar_exists = ckavatar($authorid);
            if ($avatar_exists ==1) {
                $avatarfile = avatar_file($authorid, 'middle');
                $value["avatarfile"] = $avatarfile;
            }

            $loglist[] = $value;
    }
    updatetable('notification', array('new'=>'0'), array('new'=>'1', 'uid'=>$uid, 'type'=>'rp'));
    $result['list'] = $loglist;
    echo json_encode($result);


}




?>
