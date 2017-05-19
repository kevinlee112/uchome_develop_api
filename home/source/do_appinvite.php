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

writeLog("do_appinvite.php");
$op = empty($_POST['op'])?'':$_POST['op'];
$lat = empty($_GET['lat'])?'':$_GET['lat'];
$lng = empty($_GET['lng'])?'':$_GET['lng'];
$result = array(
    'errcode' => 0,
    'errmsg' => 'error'
);
$_SGLOBAL['supe_uid'] = $uid;
$spacea = getspace($uid);
$_SGLOBAL['supe_username'] = $spacea['name'];

writeLog("----------------------------------------uid:".$uid);
if($op == 'getinvite') {
    $space = getspace($uid);
    $thequery=$_SGLOBAL['db']->query("SELECT invitecode FROM ".tname('space')." WHERE uid=".$uid);
    $theresult=$_SGLOBAL['db']->fetch_row($thequery);
    $invite_code = $theresult[0];
    $result['credit'] = $space['credit'];
    $result['invite'] = $invite_code;
    $avatar_exists = ckavatar($uid);
    if ($avatar_exists ==1) {
        $avatarfile = avatar_file($uid, 'middle');
        $result["avatarfile"] = $avatarfile;
    }
    $result['sharecontent'] = "可快速充Q币、话费，每天签到都送钱！";
    $result['home_url'] = "http://invite1.sihemob.com/home/xinjia.php?code=".$result['invite']."&uid=".$uid; 
    echo json_encode($result); 
} elseif($op == "creditlog") {
    $query = $_SGLOBAL['db']->query("SELECT r.rulename,r.rewardtype, c.* FROM ".tname('creditlog')." c LEFT JOIN ".tname('creditrule')." r ON r.rid=c.rid WHERE c.uid='$uid' ORDER BY dateline DESC LIMIT 0, 100");
    
    $loglist = array();
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        if ($value['credit'] > 0) {
	    $loglist[] = $value;
	}

    }

    $result['creditlog'] = $loglist;
    

    echo json_encode($result);
} elseif($op == "getinvitelist") {
    $query = $_SGLOBAL['db']->query("SELECT c.* FROM ".tname('creditlog')." c  WHERE c.rid = 4 and  c.uid='$uid' ORDER BY dateline DESC LIMIT 0, 100");

    $loglist = array();
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$tid = $value['tid'];
	if ($tid<1) continue;
	$tspace = getspace($tid);
	$avatar_exists = ckavatar($tid);
                        if ($avatar_exists ==1) {
                                $avatarfile = avatar_file($tid, 'middle');
                                $tspace["avatarfile"] = $avatarfile;
                        }

        $loglist[] = $tspace;
    }

    $result['list'] = $loglist;


    echo json_encode($result);
} elseif($op == 'creditrule') {
        $wherearr = array();
        $wheresql = '';

        /*$theurl = 'cp.php?ac=credit&op=rule&perpage='.$perpage;
        $perpages = array($perpage => ' selected');
        if($_GET['rid']) {
                $rid = intval($_GET['rid']);
                $wherearr[] = "rid='$rid'";
        }

        if(isset($_GET['rewardtype'])) {
                $rewardtype = intval($_GET['rewardtype']);
                $wherearr[] = "rewardtype='$rewardtype'";
                $theurl .= '&rewardtype='.$rewardtype;
        }*/

        if($wherearr) {
                $wheresql = ' WHERE '.implode(' AND ', $wherearr);
        }

        $list = $list2 = array();

        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditrule')." $wheresql ORDER BY rid DESC");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                if($value['rewardtype'] && $value['credit'] > 0) {
                        $list[] = $value;
                }/* else {
                        $list2[] = $value;
                }*/
        }

        $result['creditrule1'] = $list;
        //$result['creditrule2'] = $list2;

        echo json_encode($result);

} elseif($op == 'invitecredit'){

	if (true) {
			$result['errcode'] = 1003;
				$result['errmsg'] = "新版本发布咯，请更新心加客户端！";
				echo json_encode($result);
				return;

	}

    $code = empty($_POST['code'])?'':$_POST['code'];
    writeLog("SELECT uid,invitecode FROM ".tname('space')." where invitecode=\"$code\" ORDER BY uid desc limit 1".$code);
    $query = $_SGLOBAL['db']->query("SELECT uid,invitecode FROM ".tname('space')." where invitecode=\"$code\" limit 1");
    if ($value = $_SGLOBAL['db']->fetch_array($query)) {
	if ($uid == $value['uid']) {
            $result['errcode'] = 1003;
            $result['errmsg'] = "输入的邀请码有误,不能输入自己的邀请码";
            echo json_encode($result);
            return;

	}
	$space = getspace($uid);
        $invite_code = space_key($space, '');
        $tmp = getrewardapp('friendinvited', 1, $uid);
	if ($tmp['credit'] == 0) {
            $result['errcode'] = 1002;
            $result['errmsg'] = "已经兑换过邀请码，不能重复兑换";
            echo json_encode($result);
            return;

	}
	writeLog("aaaaaaaaaaaaaaaaaaaaaaa");
	getrewardappplus('invitefriend', 1, $value['uid'], $uid);
	writeLog("aaaaaaaaaaaaaaaaaaaaaaazzzzzzzzzzzzzzzzzzzzz");
        $result['credit'] = $space['credit'];
        $result['invite'] = $invite_code;
        $result['currentcredit'] = $tmp['credit'];
	$result['errmsg'] = "兑换成功，增长积分：".$tmp['credit'];

        echo json_encode($result);
	return;
    } else {
	$result['errcode'] = 1001;
	$result['errmsg'] = "输入的邀请码有误，请重新输入";
	echo json_encode($result);
	return;
    }
    $space = getspace($uid);
    $invite_code = space_key($space, '');
    getrewardapp('friendinvited', 1, $uid);

    $result['credit'] = $space['credit'];
    $result['invite'] = $invite_code;
    $result['currentcredit'] = 10;

    echo json_encode($result);


}




?>
