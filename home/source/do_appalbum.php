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
$token=empty($_POST['token'])?'':$_POST['token'];
$op = empty($_POST['op'])?'':$_POST['op'];
$lat = empty($_GET['lat'])?'':$_GET['lat'];
$lng = empty($_GET['lng'])?'':$_GET['lng'];
$result = array(
    'errcode' => 0,
    'errmsg' => 'error'
);
$uid = empty($_POST['uid'])?0:intval($_POST['uid']);

if ($op == "uploadalbum") {
    writeLogKang($uid.":"."upload album start-------------");
    writeLogKang($uid.":"."upload pic zip name: ".$_FILES['Filedata']['name']);
    global $_SGLOBAL, $_SCONFIG, $_SC;

    writeLogKang($uid.":"."upload album start-------------  222222 ".$_POST['uid']);    
    $_SGLOBAL['supe_uid'] = $_POST['uid'];
    $sid = $_POST['sid'];
    $classid=0;
    if (!empty($_POST['classid'])) {
        $classid = getEventByUid($_SGLOBAL['supe_uid']);//$_POST['classid'];
        $classid = getClassid($classid);
    }
    writeLogKang($uid.":"."start create album: space ".$_SGLOBAL['supe_uid']);

    $space =  getspace($_SGLOBAL['supe_uid']); 

        if (empty($space)) {
                $result['errcode'] = 1001;
                $result['errmsg'] = '对不起,您的操作失败';
                echo json_encode($result);
                exit;
        }

	writeLogKang(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");

    writeLogKang($uid.":"."start create album: space ".$space['uid']."..............".$_POST['albumname']); 
		$_POST['albumname'] = empty($_POST['albumname'])?'':getstr($_POST['albumname'], 50, 1, 1);
		
		writeLogKang($uid.":"."................. ".$_POST['albumname']);

		if(empty($_POST['albumname'])) $_POST['albumname'] = "晒晒";//gmdate('Ymd');

		if (!empty($_POST['sid'])) {
		        /*$querySubject = $_SGLOBAL['db']->query("SELECT sid,title FROM ".tname('subject')." WHERE sid=$sid LIMIT 1");
		        if ($valueSubject = $_SGLOBAL['db']->fetch_array($querySubject)) {
                	    if (!empty($valueSubject['title'])) {
				$_POST['albumname'] = $valueSubject['title'];
			    }
        		}*/
		}

		$_POST['friend'] = intval($_POST['friend']);

		//靠
		$_POST['target_ids'] = '';

		//靠靠
		$setarr = array();
		$setarr['albumname'] = $_POST['albumname'];
		$setarr['uid'] = $_SGLOBAL['supe_uid'];
		$setarr['sid'] = $sid;
                $setarr['classid'] = $classid;
		$setarr['username'] = $_SGLOBAL['supe_username'];
		$setarr['dateline'] = $setarr['updatetime'] = $_SGLOBAL['timestamp'];
		$setarr['friend'] = $_POST['friend'];
		$setarr['password'] = $_POST['password'];
		$setarr['target_ids'] = $_POST['target_ids'];
		writeLogKang($uid.":".">>>>>>>>>>>>>> upload album uid:".$$_SGLOBAL['supe_uid']." --- sid:".$sid);
		$albumid = inserttable('album', $setarr, 1);
		
		//靠靠靠
		if(empty($space['albumnum'])) {
			$space['albumnum'] = getcount('album', array('uid'=>$space['uid']));
			$albumnumsql = "albumnum=".$space['albumnum'];
		} else {
			$albumnumsql = 'albumnum=albumnum+1';
		}
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET {$albumnumsql}, updatetime='$_SGLOBAL[timestamp]' WHERE uid='$_SGLOBAL[supe_uid]'");
	
    writeLogKang($uid.":"."start unzip album aaaaaaaaaaaaaa albumname: ".$setarr['albumname']);    

    unzip_album($_FILES['Filedata'], time(), $albumid, $setarr['albumname']); 
    writeLogKang($uid.":"."sc attachtfile : ".$_SC['attachdir']);

    include_once(S_ROOT.'./source/function_feed.php');
    feed_publish($albumid, 'albumid', 0, $lat, $lng);


    //notification_app_add(uid,$albumid, "uploadalbum", $setarr['albumname']);

    $zip = zip_open($_FILES['Filedata']); 
    if ($zip) {
	writeLogKang($uid.":"."111111111111");
    }
    echo json_encode($result);
    fastcgi_finish_request();

    $queryPic = $_SGLOBAL['db']->query("SELECT *  FROM ".tname('album')." WHERE albumid='$albumid' ORDER BY dateline DESC LIMIT 1");
    $picpath="";
    if ($valuePic = $_SGLOBAL['db']->fetch_array($queryPic)) {
        //$value['albumid'] = $valuePic['albumid'];
        //$value['picid'] = $valuePic['picid'];
        $picpath = $valuePic['pic'];
    }
 
    $myuid=$space['uid'];
    $query = $_SGLOBAL['db']->query("SELECT s.uid, s.username,s.name,main.dateline, f.resideprovince, f.residecity, f.note, f.spacenote, f.sex, main.gid, main.num FROM ".tname('friend')." main LEFT JOIN ".tname('space')." s ON s.uid=main.fuid LEFT JOIN ".tname('spacefield')." f ON f.uid=main.fuid WHERE main.uid='$myuid' AND main.status='1'  ORDER BY  main.dateline DESC LIMIT 100"); 
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
    if ($value['uid'] != 0) {
	$vuid = $value['uid'];
        notification_app_add($vuid,$albumid, "uploadalbum", $picpath);	
        }
    }


}  else if($op == "clickalbum") {

		//点击器
		include_once(S_ROOT.'./data/data_click.php');
	
		$uid = empty($_POST['uid'])?'':$_POST['uid'];
		$albumid = empty($_POST['albumid'])?'':$_POST['albumid'];
		$space = getspace($uid);
	

	$_SGLOBAL['supe_uid'] = $space['uid'];
        $_SGLOBAL['supe_username'] = $space['name'];
		
	
		$idtype = "picid";
		$tablename = tname('pic');
		$clickid = 9;
	

	$clicks = empty($_SGLOBAL['click'][$idtype])?array():$_SGLOBAL['click'][$idtype];
	$click = $clicks[$clickid];
	
	    $piclist = array();
        $queryPic = $_SGLOBAL['db']->query("SELECT picid  FROM ".tname('pic')." WHERE albumid='$albumid' ORDER BY dateline DESC LIMIT 1");
	writeLog("*******clickalbum SELECT picid  FROM ".tname('pic')." WHERE albumid='$albumid' ORDER BY dateline DESC LIMIT 1");
        while ($valuePic = $_SGLOBAL['db']->fetch_array($queryPic)) {
   
            $picid = $valuePic['picid'];

	    writeLog("*********click album-".$albumid." picid-".$picid);
                
                $sql = "SELECT p.*, s.username, a.friend, pf.hotuser FROM ".tname('pic')." p
                        LEFT JOIN ".tname('picfield')." pf ON pf.picid=p.picid
                        LEFT JOIN ".tname('album')." a ON a.albumid=p.albumid
                        LEFT JOIN ".tname('space')." s ON s.uid=p.uid
                        WHERE p.picid='$picid'";
                $queryTmp = $_SGLOBAL['db']->query($sql);
				if(!$item = $_SGLOBAL['db']->fetch_array($queryTmp)) {
        			$result['errcode'] = 1000;
        			$result['errmsg'] = "click_item_error";
        			echo json_encode($result);
        			return;
				}
                
                
                //检查是否点击过了
        		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('clickuser')." WHERE uid='$space[uid]' AND id='$picid' AND idtype='picid'");
        		if($value = $_SGLOBAL['db']->fetch_array($query)) {
        			$result['errcode'] = 1001;
        			$result['errmsg'] = "click_have";
        			echo json_encode($result);
        			return;
        		}
        		
        		//参与
        		$setarr = array(
                	'uid' => $space['uid'],
                	'username' => $space['name'],//$_SGLOBAL['supe_username'],
                	'id' => $picid,
                	'idtype' => $idtype,
                	'clickid' => $clickid,
                	'dateline' => $_SGLOBAL['timestamp']
        		);
        		inserttable('clickuser', $setarr);
        		
        		//更新数量
        			
        		$_SGLOBAL['db']->query("UPDATE $tablename SET click_{$clickid}=click_{$clickid}+1 WHERE $idtype='$picid'");
        		//更新热度
        		hot_update($idtype, $picid, $item['hotuser']);
        		//实名
        		realname_set($item['uid'], $item['username']);
        		realname_get();
        		
        		//动态
        		$fs = array();
        		$fs['title_template'] = cplang('feed_click_pic');
                $fs['title_data'] = array(
                    'touser' => "<a href=\"space.php?uid=$item[uid]\">{$_SN[$item['uid']]}</a>",
                    'click' => $click['name']
                );
                $fs['images'] = array(pic_get($item['filepath'], $item['thumb'], $item['remote']));
                $fs['image_links'] = array("space.php?uid=$item[uid]&do=album&picid=$item[picid]");
                $fs['body_general'] = $item['title'];
                $note_type = 'clickpic';
                $q_note = cplang('note_click_pic', array("space.php?uid=$item[uid]&do=album&picid=$item[picid]"));
        		
                //事件发布
        		if(empty($item['friend']) && ckprivacy('click', 1)) {
                	feed_add('click', $fs['title_template'], $fs['title_data'], '', array(), $fs['body_general'],$fs['images'], $fs['image_links']);
        		}

        		//奖励访客
        		getreward('click', 1, 0, $idtype.$picid);
	
        		//统计
        		updatestat('click');
        		//通知
        		notification_app_add($item['uid'],$albumid, $note_type, $q_note);
                
                echo json_encode($result);
                return;
        }
}   else if($op == "passalbum") {

                //点击器
                include_once(S_ROOT.'./data/data_click.php');

                $uid = empty($_POST['uid'])?'':$_POST['uid'];
		$classid = empty($_POST['classid'])?'':$_POST['classid'];
                $albumid = empty($_POST['albumid'])?'':$_POST['albumid'];
                $space = getspace($uid);


        $_SGLOBAL['supe_uid'] = $space['uid'];
        $_SGLOBAL['supe_username'] = $space['name'];


                $idtype = "picid";
                $tablename = tname('pic');
                $clickid = 10;


        $clicks = empty($_SGLOBAL['click'][$idtype])?array():$_SGLOBAL['click'][$idtype];
        $click = $clicks[$clickid];

            $piclist = array();
        $queryPic = $_SGLOBAL['db']->query("SELECT picid  FROM ".tname('pic')." WHERE albumid='$albumid' ORDER BY dateline DESC LIMIT 1");
        writeLog("*******clickalbum SELECT picid  FROM ".tname('pic')." WHERE albumid='$albumid' ORDER BY dateline DESC LIMIT 1");
        while ($valuePic = $_SGLOBAL['db']->fetch_array($queryPic)) {

            $picid = $valuePic['picid'];

            writeLog("*********click album-".$albumid." picid-".$picid);

                $sql = "SELECT p.*, s.username, a.friend, pf.hotuser FROM ".tname('pic')." p
                        LEFT JOIN ".tname('picfield')." pf ON pf.picid=p.picid
                        LEFT JOIN ".tname('album')." a ON a.albumid=p.albumid
                        LEFT JOIN ".tname('space')." s ON s.uid=p.uid
                        WHERE p.picid='$picid'";
                $queryTmp = $_SGLOBAL['db']->query($sql);
                                if(!$item = $_SGLOBAL['db']->fetch_array($queryTmp)) {
                                $result['errcode'] = 1000;
                                $result['errmsg'] = "click_item_error";
                                echo json_encode($result);
                                return;
                                }


                //检查是否点击过了
                        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('clickuser')." WHERE uid='$space[uid]' AND id='$picid' AND idtype='picid'");
                        if($value = $_SGLOBAL['db']->fetch_array($query)) {
                                $result['errcode'] = 1001;
                                $result['errmsg'] = "click_have";
                                echo json_encode($result);
                                return;
                        }

                        //参与
                        $setarr = array(
                        'uid' => $space['uid'],
                        'username' => $space['name'],//$_SGLOBAL['supe_username'],
                        'id' => $picid,
                        'idtype' => $idtype,
                        'clickid' => $clickid,
                        'dateline' => $_SGLOBAL['timestamp']
                        );
                        inserttable('clickuser', $setarr);

                        //更新数量

                        $_SGLOBAL['db']->query("UPDATE $tablename SET click_{$clickid}=click_{$clickid}+1 WHERE $idtype='$picid'");
                        //更新热度
                        hot_update($idtype, $picid, $item['hotuser']);
                        //实名
                        realname_set($item['uid'], $item['username']);
                        realname_get();

                        //动态
                        $fs = array();
                        $fs['title_template'] = cplang('feed_click_pic');
                $fs['title_data'] = array(
                    'touser' => "<a href=\"space.php?uid=$item[uid]\">{$_SN[$item['uid']]}</a>",
                    'click' => $click['name']
                );
                $fs['images'] = array(pic_get($item['filepath'], $item['thumb'], $item['remote']));
                $fs['image_links'] = array("space.php?uid=$item[uid]&do=album&picid=$item[picid]");
                $fs['body_general'] = $item['title'];
                $note_type = 'clickpic';
                $q_note = cplang('note_click_pic', array("space.php?uid=$item[uid]&do=album&picid=$item[picid]"));

                //事件发布
                        if(empty($item['friend']) && ckprivacy('click', 1)) {
                        feed_add('click', $fs['title_template'], $fs['title_data'], '', array(), $fs['body_general'],$fs['images'], $fs['image_links']);
                        }

                        //奖励访客
                        getreward('click', 1, 0, $idtype.$picid);

                        //统计
                        updatestat('click');
                        //通知
                        notification_app_add_ext($item['uid'],$albumid, $note_type, $classid, $q_note);

                echo json_encode($result);
                return;
        }
}  else if($op == "ignorealbum") {
    //点击器
    include_once(S_ROOT.'./data/data_click.php');
    $uid = empty($_POST['uid'])?'':$_POST['uid'];
    $albumid = empty($_POST['albumid'])?'':$_POST['albumid'];
    $space = getspace($uid);

    $_SGLOBAL['supe_uid'] = $space['uid'];
    $_SGLOBAL['supe_username'] = $space['name'];

    $idtype = "picid";
    $tablename = tname('pic');
    $clickid = 8;

    $clicks = empty($_SGLOBAL['click'][$idtype])?array():$_SGLOBAL['click'][$idtype];
    $click = $clicks[$clickid];

    $piclist = array();
    $queryPic = $_SGLOBAL['db']->query("SELECT picid  FROM ".tname('pic')." WHERE albumid='$albumid' ORDER BY dateline DESC LIMIT 1");
    //var_dump("*******clickalbum SELECT picid  FROM ".tname('pic')." WHERE albumid='$albumid' ORDER BY dateline DESC LIMIT 1");
    while ($valuePic = $_SGLOBAL['db']->fetch_array($queryPic)) {

            $picid = $valuePic['picid'];

            writeLog("*********click album-".$albumid." picid-".$picid);

                $sql = "SELECT p.*, s.username, a.friend, pf.hotuser FROM ".tname('pic')." p
                        LEFT JOIN ".tname('picfield')." pf ON pf.picid=p.picid
                        LEFT JOIN ".tname('album')." a ON a.albumid=p.albumid
                        LEFT JOIN ".tname('space')." s ON s.uid=p.uid
                        WHERE p.picid='$picid'";
                $queryTmp = $_SGLOBAL['db']->query($sql);
                                if(!$item = $_SGLOBAL['db']->fetch_array($queryTmp)) {
                                $result['errcode'] = 1000;
                                $result['errmsg'] = "click_item_error";
                                echo json_encode($result);
                                return;
                                }


                //检查是否点击过了
                        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('clickuser')." WHERE uid='$space[uid]' AND id='$picid' AND idtype='picid'");
                        if($value = $_SGLOBAL['db']->fetch_array($query)) {
                                $result['errcode'] = 1001;
                                $result['errmsg'] = "click_have";
                                echo json_encode($result);
                                return;
                        }

                        //参与
                        $setarr = array(
                        'uid' => $space['uid'],
                        'username' => $space['name'],//$_SGLOBAL['supe_username'],
                        'id' => $picid,
                        'idtype' => $idtype,
                        'clickid' => $clickid,
                        'dateline' => $_SGLOBAL['timestamp']
                        );
                        inserttable('clickuser', $setarr);

                        //更新数量

                        $_SGLOBAL['db']->query("UPDATE $tablename SET click_{$clickid}=click_{$clickid}+1 WHERE $idtype='$picid'");
                        writeLog("UPDATE $tablename SET click_{$clickid}=click_{$clickid}+1 WHERE $idtype='$picid'");
                        //更新热度
                        //ignore hot_update($idtype, $picid, $item['hotuser']);
                        //实名
                        realname_set($item['uid'], $item['username']);
                        realname_get();

                        //动态
               /*ignore         $fs = array();
                        $fs['title_template'] = cplang('feed_click_pic');
                $fs['title_data'] = array(
                    'touser' => "<a href=\"space.php?uid=$item[uid]\">{$_SN[$item['uid']]}</a>",
                    'click' => $click['name']
                );
                $fs['images'] = array(pic_get($item['filepath'], $item['thumb'], $item['remote']));
                $fs['image_links'] = array("space.php?uid=$item[uid]&do=album&picid=$item[picid]");
                $fs['body_general'] = $item['title'];
                $note_type = 'clickpic';
                $q_note = cplang('note_click_pic', array("space.php?uid=$item[uid]&do=album&picid=$item[picid]"));

                //事件发布
                        if(empty($item['friend']) && ckprivacy('click', 1)) {
                        feed_add('click', $fs['title_template'], $fs['title_data'], '', array(), $fs['body_general'],$fs['images'], $fs['image_links']);
                        }

                        //奖励访客
                        getreward('click', 1, 0, $idtype.$picid);

                        //统计
                        updatestat('click');
                writeLog("************ uid:".$item['uid']."********** albumid ".$albumid);
                        //通知
                        notification_app_add($item['uid'],$albumid, $note_type, $q_note);
		*/
                echo json_encode($result);
                return;
        }
} else if ($op == "getalbumclick") {
	$albumid = empty($_POST['albumid'])?'':$_POST['albumid'];
	$queryPic = $_SGLOBAL['db']->query("SELECT picid  FROM ".tname('pic')." WHERE albumid='$albumid' ORDER BY dateline DESC LIMIT 1");
    while ($valuePic = $_SGLOBAL['db']->fetch_array($queryPic)) {
    	$picid = $valuePic['picid'];
    	$clicklist = array();
    	$queryClick = $_SGLOBAL['db']->query("SELECT * FROM ".tname('clickuser')." WHERE id='$picid' AND idtype='picid' ORDER BY dateline DESC");
        while($valueClick = $_SGLOBAL['db']->fetch_array($queryClick)) {
		$spaceTmp = getspace($valueClick['uid']);

		include_once(S_ROOT.'./source/function_cp.php');

                $avatar_exists = ckavatar($spaceTmp['uid']);
                if ($avatar_exists ==1) {
                    $avatarfile = avatar_file($spaceTmp['uid'], 'middle');
                    $spaceTmp["avatarfile"] = $avatarfile;
                }


        	$clicklist[] = $spaceTmp;
        }
    	
    	$result['clicklist'] = $clicklist;
        echo json_encode($result);
        return;
    	
    }
} else if($op == "photoupload") {
    $result['token']=check_token($uid, $token);
    global $_SGLOBAL, $_SCONFIG, $_SC;
    writeLog("upload album start-------------  222222 ".$_POST['uid']);
    $_SGLOBAL['supe_uid'] = $_POST['uid'];
    writeLogKang("----> photoupload uid: ".$_SGLOBAL['supe_uid']);
    writeLog("start create album: space ".$_SGLOBAL['supe_uid']);
    $space =  getspace($_SGLOBAL['supe_uid']);
    $_SGLOBAL['supe_username'] = $space['name'];
    if (empty($space)) {
	writeLogKang("----> photoupload uid: ".$_SGLOBAL['supe_uid']." | error space empty !!!");
            $result['errcode'] = 1001;
            $result['errmsg'] = '对不起,您的操作失败';
            echo json_encode($result);
            exit;
    }
    $albumid = 0;
    $albumname = "photoalbumsystem";
    $isEvent = empty($_POST['eventid'])?'0':$_POST['eventid'];
    $isTask = empty($_POST['taskid'])?'0':$_POST['taskid'];
    if ($isEvent > 0) {
	   writeLogKang("----> photoupload uid: ".$_SGLOBAL['supe_uid']." | isEvent > 0");
       $albumname = "photoalbumssystem_".$_SGLOBAL['supe_uid']."_".$isEvent;
    }
    if ($isTask != 0) {
    	writeLogKang("----> photoupload uid: ".$_SGLOBAL['supe_uid']." | isTask !=0 : ".$isTask);
    	$albumname = "photoalbumssystem_".$_SGLOBAL['supe_uid']."_task_".$isTask;
        $redis = getRedis();
        $currentCount = $redis->incr("dotaskbyidandphoto".$isTask.$_SGLOBAL['supe_uid']);
        if($currentCount == 1){
            $redis->expire("dotaskbyidandphoto".$isTask.$_SGLOBAL['supe_uid'], 180);
        }
        $count=$redis->incr("rate".$isTask);
        if($count==1){
            $redis->expire("rate".$isTask,strtotime(date("Y-m-d")."+1 days")-time());
        }
        $redis->incr("totalCount".$isTask);
    	if ($currentCount > 1) {
    		writeLogKang("----> photoupload uid: ".$_SGLOBAL['supe_uid']." | error currentCount > 1 : ".$currentCount);
	        $result['errcode'] = 1010;
            $result['errmsg'] = '操作太频繁，请稍后再试';
            echo json_encode($result);
            exit;
    	}
    }
    writeLogDebug("............photoupload........ tid:".$isTask."   eid:".$isEvent); 
    $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE uid='$space[uid]' and albumname=\"$albumname\" LIMIT 1");
    if ($value = $_SGLOBAL['db']->fetch_array($query)) {
    	$albumid = $value['albumid'];
	}
	writeLogKang("----> photoupload uid: ".$_SGLOBAL['supe_uid']." | albumid : ".$albumid);
    if(empty($albumid)) {
        $_POST['albumname'] = $albumname;//empty($_POST['albumname'])?'':getstr($_POST['albumname'], 50, 1, 1);
        writeLog("................. ".$_POST['albumname']);
        if(empty($_POST['albumname'])) $_POST['albumname'] = "photoalbumsystem";//gmdate('Ymd');
        $_POST['friend'] = intval($_POST['friend']);
        $_POST['target_ids'] = '';
        $setarr = array();
        $setarr['albumname'] = $_POST['albumname'];
        $setarr['uid'] = $_SGLOBAL['supe_uid'];
        $setarr['username'] = $_SGLOBAL['supe_username'];
        $setarr['dateline'] = $setarr['updatetime'] = $_SGLOBAL['timestamp'];
        $setarr['friend'] = $_POST['friend'];
        $setarr['password'] = $_POST['password'];
        $setarr['target_ids'] = $_POST['target_ids'];
        $albumid = inserttable('album', $setarr, 1);
        if(empty($space['albumnum'])) {
            $space['albumnum'] = getcount('album', array('uid'=>$space['uid']));
            $albumnumsql = "albumnum=".$space['albumnum'];
        } else {
            $albumnumsql = 'albumnum=albumnum+1';
        }
        $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET {$albumnumsql}, updatetime='$_SGLOBAL[timestamp]' WHERE uid='$_SGLOBAL[supe_uid]'");
		writeLogKang("----> photoupload uid: ".$_SGLOBAL['supe_uid']." | albumname : ".$setarr['albumname']);
    	writeLog("start unzip album aaaaaaaaaaaaaa albumname: ".$setarr['albumname']);
    }
    unzip_album($_FILES['Filedata'], time(), $albumid, $albumname);
    if ($isTask != 0) {
        $queryPic = $_SGLOBAL['db']->query('SELECT filepath FROM '.tname('pic')." WHERE albumid = '$albumid'");
        $valuePic = $_SGLOBAL['db']->fetch_array($queryPic);
	    $imageurl = $valuePic['filepath'];
        if (empty($imageurl)) {
            writeLogKang("----> photoupload uid: ".$_SGLOBAL['supe_uid']." | error imageurl is null, albumid: ".$albumid);
        	$redis->del("dotaskbyidandphoto".$isTask.$_SGLOBAL['supe_uid']);
            $result['errcode'] = 1001;
            $result['errmsg'] = "提交失败，请重新提交$albumid";
            echo json_encode($result);
            return;
        }
    	$queryContact = $_SGLOBAL['db']->query('SELECT * FROM '.tname('creditgood')." WHERE gid = '$isTask'");
        $valueMoney = $_SGLOBAL['db']->fetch_array($queryContact);
    	$money = $valueMoney['price'];
    	$uid = $_SGLOBAL['supe_uid'];
    	$setarr = array(
            'moneylogamount' => $money,
            'moneylogamounttype' => 0,
            'moneyloguid' => $_SGLOBAL['supe_uid'],
            'moneylogusername' => $_SGLOBAL['supe_username'],
            'moneyloguserphoto' => "http://app.sihemob.com/data/avatar/".avatar_file($uid, 'middle'),
            'moneylogtypeid' => 9,
            'moneylogtypecategory' => 21,
            'moneylogstatus' => 1,
            'moneylogtaskid' => $isTask,
			'moneylogtaskimgurl' => "http://app.sihemob.com/home/attachment/".$imageurl,
            'moneylogtid' => 0,
            'moneylogtip' => "任务奖励-".$valueMoney['title'],
            'dateline' => $_SGLOBAL['timestamp']
        );
    	$moneylogTableNames = getMoneylogTableName(strtotime("2016-05-02"));
        $moneylogid = inserttable($moneylogTableNames[0], $setarr, 1);
        $setarrs = array();
        $setarrs['totalmoney'] = "totalmoney=totalmoney+$money";
        $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarrs)." WHERE uid='$uid'");
	    writeLogKang("----> photoupload uid: ".$_SGLOBAL['supe_uid']." | insert moneylog id : ".$moneylogid);
	    $result['errcode'] = 0;
        $result['errmsg'] = 'success';
	    echo json_encode($result);
    	fastcgi_finish_request();	
    	include_once(S_ROOT.'./source/function_cp.php');
        updatestat('moneytask');
	    return;
    }
    echo json_encode($result);
} elseif($op == "getphotos") {
    $uid = empty($_POST['uid'])?'':$_POST['uid'];
    if (empty($uid)) {
        $result['errcode'] = 1001;
        $result['errmsg'] = "uid is empty";
        echo json_encode($result);
        return;
    }
	writeLog(",,,,,,,,,,,,,,,,,,,,,,, uid.".$uid);
	$albumname = "photoalbumsystem";
        $isEvent = empty($_POST['eventid'])?'0':$_POST['eventid'];
	$reward = 0;
	$rewardpic = "";
        if ($isEvent > 0) {
            $albumname = "photoalbumssystem_".$uid."_".$isEvent;
	                    $query = $_SGLOBAL['db_slave']->query("SELECT reward FROM ".tname("event")."  WHERE eventid='$isEvent'");
                $event = $_SGLOBAL['db_slave']->fetch_array($query);
                if($event){
                        $reward = $event['reward'];
                }
		$rewardpic = "http://app.sihemob.com/home/attachment/201510/26/98_1445844368l03M.jpg";
        }
        $albumlist = array();
        $query = $_SGLOBAL['db_slave']->query("SELECT * FROM ".tname('album')." WHERE uid='$uid' and albumname=\"$albumname\" ");
        if($album = $_SGLOBAL['db_slave']->fetch_array($query)) {
                //$albumlist[] = getalbumsbyuid($album['albumid'], $album['uid'], $uid);
		$result = getalbumsbyuid($album['albumid'], $album['uid'], $uid);
        }
	$result['reward'] = $reward;
	$result['rewardpic'] = $rewardpic;
	$result['errcode'] = 0;
	$result['errmsg'] = "success";

        //$result['albumlist'] = $albumlist;
        echo json_encode($result);

} elseif($op == "deletealbum") {
	$aid = empty($_POST['aid'])?'':$_POST['aid'];
	$aids[] = $aid;
	$uid = empty($_POST['uid'])?'':$_POST['uid'];
    	$_SGLOBAL['supe_uid'] = $uid;
    	$space =  getspace($_SGLOBAL['supe_uid']);
	if (empty($aids) || empty($uid)) {
	        $result['errcode'] = 1001;
	        $result['errmsg'] = "fail";
		echo json_encode($result);
		return;
	}
	include_once(S_ROOT.'./source/function_delete.php');
	if (deletealbums($aids)) {
                $result['errcode'] = 0;
                $result['errmsg'] = "success";
                echo json_encode($result);
                return;
	}

	$result['errcode'] = 1002;
        $result['errmsg'] = "fail";
        echo json_encode($result);

}  elseif($op == "deletepic") {
        $pid = empty($_POST['pid'])?'':$_POST['pid'];
        $pids[] = $pid;
        $uid = empty($_POST['uid'])?'':$_POST['uid'];
        $_SGLOBAL['supe_uid'] = $uid;
        $space =  getspace($_SGLOBAL['supe_uid']);
        if (empty($pids) || empty($uid)) {
                $result['errcode'] = 1001;
                $result['errmsg'] = "fail";
                echo json_encode($result);
                return;
        }
        include_once(S_ROOT.'./source/function_delete.php');
        if (deletepics($pids)) {
                $result['errcode'] = 0;
                $result['errmsg'] = "success";
                echo json_encode($result); 
                return; 
        } 

        $result['errcode'] = 1002;
        $result['errmsg'] = "fail";
        echo json_encode($result);
    
}  elseif($op == "getalbumbyaid") {

       $uid = empty($_POST['uid'])?'':$_POST['uid'];
	$albumid = empty($_POST['albumid'])?'':$_POST['albumid'];
        if (empty($uid) || empty($albumid)) {
                $result['errcode'] = 1001;
                $result['errmsg'] = "uid is empty";
                echo json_encode($result);
                return;
        }
        writeLogDebug(",,,,,,,,,,,,,,,,,,,,,,, uid.".$uid."  albumid:".$albumid);
	$result['album'] = getalbumsbyuid($albumid, $uid, $uid);
        $result['errcode'] = 0;
        $result['errmsg'] = "success";

        //$result['albumlist'] = $albumlist;
        echo json_encode($result);

} 

?>
