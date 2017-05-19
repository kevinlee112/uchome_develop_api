<?php
/*
 * Created on Dec 10, 2014
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
if(!defined('IN_UCHOME')) {
        exit('Access Denied');
}

include_once(S_ROOT.'./source/function_cp.php');

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
$spacea = getspace($uid);
$_SGLOBAL['supe_username'] = $spacea['name'];
writeLog("do_appfeed.php op: ".$op."  start: ".time()."----------uid:".$uid."::".$_SGLOBAL['supe_username']);
if ($op == "getfeedlist") {

	$wherearr = array();
	$wherearr[] = " (f.idtype='doid' OR f.idtype='albumid') ";
	$isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
        $dateline = empty($_POST['dateline'])?'0':$_POST['dateline'];
	$sid = empty($_POST['sid'])?'0':$_POST['sid'];
	

	if (empty($_POST['appversion'])) $isRefresh = '';
	if ($isRefresh == "yes") {
            $wherearr[] = " f.dateline > ".$dateline;
	    if (empty($dateline)) {
	        writeLog("**************************************** isRefresh: ".$isRefresh."---uid:".$uid);
		$rewardtmp = getrewardapp('daylogin', 1, $uid);
		if($rewardtmp['credit'] > 0) {
		    $result['gongxi'] = "恭喜获得每日登录奖励：积分+".$rewardtmp['credit'];    
		}
	    }
        } else if($isRefresh == "no"){
            $wherearr[] = " f.dateline < ".$dateline;
        } else {
	    //getreward('daylogin', 1, $uid);
	}	
	writeLog("**************************************** isRefresh: ".$isRefresh);
	$feedlist = array();

	$fromsql = tname("feed")." f ";

	$sex = empty($_POST['sex'])?'':$_POST['sex'];
	if (!empty($sex)) {
                $joinsql = ", " . tname('spacefield') . " sf ";
                $wherearr[] = " sf.sex = " . $sex;
                $wherearr[] = " sf.uid = f.uid";
        }

        $game = empty($_POST['game'])?'':$_POST['game'];
        if (!empty($game)) {
		$joinsql = ", " . tname('spacefield') . " sf ";
		$wherearr[] = " sf.uid = f.uid ";
                $wherearr[] = " sf.event like \"%," . $game . ",%\" ";
        }

	

	
//	$query = $_SGLOBAL['db']->query("SELECT feedid, uid,username,id,idtype,dateline FROM ".tname('feed')." WHERE idtype='doid'" .
//			"OR idtype='albumid' ORDER BY dateline DESC LIMIT $start,$perpage");

      $query = $_SGLOBAL['db']->query("SELECT f.feedid, f.uid,f.username,f.id,f.idtype,f.hide, f.dateline FROM $fromsql $joinsql WHERE ".implode(" AND ", $wherearr) ." ORDER BY f.dateline DESC LIMIT $start,$perpage");

	writeLog("SELECT feedid, uid,username,id,idtype,hide,dateline FROM ".tname('feed')." WHERE ".implode(" AND ", $wherearr) ." ORDER BY dateline DESC LIMIT $start,$perpage");

	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$idtype = $value['idtype'];
		if ($value['hide'] == 1) {
		    if ($value['uid'] != $uid) continue;
		}

		$value['isTop'] = 0;
		if ($value['dateline'] > $_SGLOBAL['timestamp']) {
		    $value['isTop'] = 1;
		}
		if ($idtype == 'doid') {
			$queryDoing = $_SGLOBAL['db']->query("SELECT * FROM ".tname('doing')." WHERE doid = ".$value['id'].
				"  ORDER BY dateline DESC LIMIT 0, 1");
			$value['doing'] = $_SGLOBAL['db']->fetch_array($queryDoing);	
			$value['isnew']	= 0;		

			$queryFirst = $_SGLOBAL['db']->query("SELECT feedid FROM ".tname('feed')." WHERE idtype='doid' AND uid = ".$value['uid']."  ORDER BY dateline ASC LIMIT 1");
			while ($valueFirst = $_SGLOBAL['db']->fetch_array($queryFirst)) {
			    if ($valueFirst['feedid'] == $value['feedid']) {
			    	$value['isnew'] = 1;
			    }
			}
		} else if ($idtype == 'albumid') {
		    $value['album'] = getalbumsbyuid($value['id'], $value['uid'], $uid);
		    if(empty($value['album'])) continue;		
		}
//		writeLog("do_appfeed.php  start getspace ".time());		
		$space = getspace($value['uid']);
		$avatar_exists = ckavatar($value['uid']);
		$space['avatarfile'] = "";
                if ($avatar_exists ==1) {
                        $avatarfile = avatar_file($value['uid'], 'middle');
                        $space["avatarfile"] = $avatarfile;
                }

//		writeLog("do_appfeed.php  end getspace ".time());
		$queryContact = $_SGLOBAL['db']->query('SELECT qq, msn, sex, mobile, email,event,tags FROM '.tname('spacefield')." WHERE uid =".$value['uid']);
        	$valueContact = $_SGLOBAL['db']->fetch_array($queryContact);

        	$space['qq'] = $valueContact['qq'];
        	$space['weixin'] = $valueContact['msn'];
        	$space['sex'] = $valueContact['sex'];
        	$space['mobile'] = $valueContact['mobile'];
        	$space['email'] = $valueContact['email'];
        	$space['event'] = $valueContact['event'];
        	$space['tags'] = $valueContact['tags'];
		$value['user'] = $space;
//		writeLog("do_appfeed.php  end get spacefiled tags ".$valueContact['tags']." uid: ".$value['uid']);

		$feedlist[] = $value;
	}
	
	$result['feedlist'] = $feedlist;

	writeLog("feed list count" . count($feedlist));
	echo json_encode($result);
	fastcgi_finish_request(); 
	
	writeLog("|||||||||||||||||| update space table uid:".$uid."   lat:".$lat."  lng:".$lng);
        $random = rand(0, 8);	
	if ($lat > 2 && $lng > 2 && $random > 5) {
	    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET lat='$lat', lng='$lng' WHERE uid='$uid'");
	}
} else if ($op == "getshaishai") {
	$feedlist = array();

        $query = $_SGLOBAL['db']->query("SELECT *  FROM ".tname('album')." as a inner join ".tname('pic')." as p on a.albumid=p.albumid WHERE p.uid='$uid' and a.albumname <> 'photoalbumsystem' ORDER BY p.dateline DESC LIMIT 6");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                $feedlist[] = $value;
	}

        $result['piclist'] = $feedlist;

        writeLog("getfeedlist end ".time());
        echo json_encode($result);

} elseif ($op == "getalbumlist"){
	$ouid = empty($_POST['ouid'])?'':$_POST['ouid'];
	if (empty($ouid) || empty($uid)) {
		$result['errcode'] = 1001;
		$result['errmsg'] = "uid is empty";
		echo json_encode($result);
		return;
	}
	$albumlist = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE albumname not like '%photoalbums%' and  uid='$ouid'");
	while($album = $_SGLOBAL['db']->fetch_array($query)) {
		$albumTmp = getalbumsbyuid($album['albumid'], $album['uid'], $uid, 2);
		//var_dump($albumTmp);
		$albumlist[] = $albumTmp;
	}
	
	$result['albumlist'] = $albumlist;
	echo json_encode($result);
	
}

?>

