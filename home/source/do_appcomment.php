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

writeLog($uid."----------------getnewgameuser------------------------");
if($op == 'comment') {
       
	$_SGLOBAL['supe_uid'] = $uid;
	$spacea = getspace($uid);
	$_SGLOBAL['supe_username'] = $spacea['name'];

 
	$message = empty($_POST['message'])?'':$_POST['message'];
	//摘要
        $summay = getstr($message, 150, 1, 1, 0, 0, -1);

        $id = intval($_POST['pid']);
	$albumid="0";
        $hotarr = array();
        $stattype = '';

        //引用评论
        $cid = empty($_POST['cid'])?0:intval($_POST['cid']);
        $comment = array();
        if($cid) {
                $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE cid='$cid' AND id='$id' AND idtype='picid'");
		writeLogDebug("SELECT * FROM ".tname('comment')." WHERE cid='$cid' AND id='$id' AND idtype='picid'");
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
                        
	//检索图片
        $query = $_SGLOBAL['db']->query("SELECT p.*, pf.hotuser
                                FROM ".tname('pic')." p
                                LEFT JOIN ".tname('picfield')." pf
                                ON pf.picid=p.picid
                                WHERE p.picid='$id'");
        $pic = $_SGLOBAL['db']->fetch_array($query);
        //图片不存在
        if(empty($pic)) {
             showmessage('view_images_do_not_exist');
        }
        //检索空间
        $tospace = getspace($pic['uid']);

        //获取相册
        $album = array();
	$albumid=$pic['albumid'];
        if($pic['albumid']) {
             $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE albumid='$pic[albumid]'");
             if(!$album = $_SGLOBAL['db']->fetch_array($query)) {
                  updatetable('pic', array('albumid'=>0), array('albumid'=>$pic['albumid']));//相册丢失
             }
        }
        //验证隐私
        if(!ckfriend($album['uid'], $album['friend'], $album['target_ids'])) {
             showmessage('no_privilege');
        } elseif(!$tospace['self'] && $album['friend'] == 4) {
             //密码输入问题
	     $cookiename = "view_pwd_album_$album[albumid]";
             $cookievalue = empty($_SCOOKIE[$cookiename])?'':$_SCOOKIE[$cookiename];
             if($cookievalue != md5(md5($album['password']))) {
                      showmessage('no_privilege');
             }
        }

        $hotarr = array('picid', $pic['picid'], $pic['hotuser']);
        $stattype = 'piccomment';//统计

        //事件
        $fs = array();
        $fs['icon'] = 'comment';
        $fs['target_ids'] = $fs['friend'] = '';

        //事件
        $fs['title_template'] = cplang('feed_comment_image');
        $fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$tospace[uid]\">".$_SN[$tospace['uid']]."</a>");
        $fs['body_template'] = '{pic_title}';
        $fs['body_data'] = array('pic_title'=>$pic['title']);
        $fs['body_general'] = $summay;
        $fs['images'] = array(pic_get($pic['filepath'], $pic['thumb'], $pic['remote']));
        $fs['image_links'] = array("space.php?uid=$tospace[uid]&do=album&picid=$pic[picid]");
        $fs['target_ids'] = $album['target_ids'];
        $fs['friend'] = $album['friend'];

        $setarr = array(
                'uid' => $tospace['uid'],
                'id' => $id,
                'idtype' => "picid",
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
	writeLogDebug("************* pid:".$pid."--albumid:".$albumid."----uid:".$comment['authorid']);
	$note_type = 'piccomment';
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

        }


        //统计
        if($stattype) {
                updatestat($stattype);
        }

    echo json_encode($result);
} elseif($op == 'getcomment') {
	$pid = intval($_POST['pid']);
        
        $cid = empty($_GET['cid'])?0:intval($_GET['cid']);
        $csql = $cid?"cid='$cid' AND":'';

	$list = array();
        $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('comment')." WHERE $csql id='$pid' AND idtype='picid'"),0);
        if($count) {
                $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE $csql id='$pid' AND idtype='picid' ORDER BY dateline LIMIT 0,100");
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


} elseif($op == 'ignorefriend') {
}


?>
