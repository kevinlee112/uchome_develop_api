<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_ajax.php 12535 2009-07-06 06:22:34Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
        exit('Access Denied');
}

include_once(S_ROOT.'./source/function_cp.php');


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
writeLogDebug("&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&".$uid);
$_SGLOBAL['supe_uid'] = $uid;
$spacea = getspace($uid);
$_SGLOBAL['supe_username'] = $spacea['name'];

writeLogDebug("----------------------------------------uid:".$uid." op:".$op);
if($op == 'getallsubject') {



    $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('subject')." where available=1  ORDER BY dateline DESC LIMIT 0, 100");

    $loglist = array();
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
//        if ($value['available'] == 1) {
            $loglist[] = $value;
//        }

    }

    $result['list'] = $loglist;
    echo json_encode($result);


} elseif($op == "getfriendalbums") {
    $uid = empty($_POST['uid'])?0:intval($_POST['uid']);
    if (empty($uid)) {
        $result['errcode'] = 1001;
        $result['errmsg'] = "uid is empty";
        echo json_encode($result);
        return;
    }
    $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
    $wherearr = array();
    $sql = "";

    $dateline = empty($_POST['dateline'])?'0':$_POST['dateline'];
    if ($isRefresh == "yes") {
        $wherearr[] = " a.dateline > ".$dateline;
    } else if($isRefresh == "no"){
        $wherearr[] = " a.dateline < ".$dateline;
    } else {
        //getreward('daylogin', 1, $uid);
    }
    $wherearr[] = "a.albumname not like '%photoalbums%'";
    $wherearr[] = "f.uid = $uid";
    $sql = "SELECT * FROM ".tname('album')." a WHERE ".implode(" AND ", $wherearr) . " order by a.dateline desc limit 0,10";   
    //$sql = "select a.albumid, a.uid, a.dateline, a.albumname from ".tname('friend')." f, ".tname('album')." a where ".implode(" AND ", $wherearr) . " and ((a.uid=f.fuid and f.status=1) or (a.uid=$uid))  group by a.albumid order by a.dateline desc limit 12"; 
    $sql = "select a.albumid, a.uid, a.dateline, a.albumname from ".tname('friend')." f, ".tname('album')." a where ".implode(" AND ", $wherearr) . " and ((a.uid=f.fuid and f.status=1) or (a.uid=$uid))  order by a.dateline desc limit 12";

    writeLogError($sql);
    $albumlist = array();
    $query = $_SGLOBAL['db']->query($sql);
    while($album = $_SGLOBAL['db']->fetch_array($query)) {
        $tmpValue = getalbumsbyuid($album['albumid'], $album['uid'], $uid);
        $tmpValue['hot'] = $album['hot'];
        $albumlist[] = $tmpValue;
    }
    writeLog("*********************************************** album size".count($albumlist));
    $result['albumlist'] = $albumlist;
    updatetable('notification', array('new'=>'0'), array('new'=>'1', 'uid'=>$uid, 'type'=>'uploadalbum'));
    echo json_encode($result);

} elseif($op == "getgamealbums") {
    $uid = empty($_POST['uid'])?0:intval($_POST['uid']);
    if (empty($uid)) {
        $result['errcode'] = 1001;
        $result['errmsg'] = "uid is empty";
        echo json_encode($result);
        return;
    }
    $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
    $wherearr = array();
    $sql = "";

    $dateline = empty($_POST['hot'])?'0':$_POST['hot'];
    if ($isRefresh == "yes") {
        $wherearr[] = " p.click_9 > ".$dateline;
    } else if($isRefresh == "no"){
        $wherearr[] = " p.click_9 < ".$dateline;
    } else {
        //getreward('daylogin', 1, $uid);
    }
    $wherearr[] = "a.albumname != 'photoalbumsystem'";
    $wherearr[] = "a.uid = sf.uid";
    $wherearr[] = "a.albumid = p.albumid";
    $lastWeek = strtotime("-7 day");
    $wherearr[] = "a.dateline > $lastWeek";
    $wherearrSame = array();

    $queryTEvent = $_SGLOBAL['db']->query('SELECT qq, msn, sex, email, mobile,event,tags FROM '.tname('spacefield')." WHERE uid = '$uid'");
    $valueTEvent = $_SGLOBAL['db']->fetch_array($queryTEvent);
    $tEvent = $valueTEvent["event"];
    $arrSame = explode(",",$tEvent);
    foreach($arrSame as $u){
        if ($u > 0) {
            writeLog("......................... : ".$u);
	    $wherearrSame[] = " sf.event  like '%,$u,%' ";
        }
    }
    writeLog("444......................... : ".implode(" OR ", $wherearrSame));
    $sql = "select a.albumid, a.dateline, sf.event from uchome_album a, uchome_spacefield sf where ".implode(" AND ", $wherearr) . "  order by a.dateline desc limit 12";
    if (!empty($wherearrSame)) {
	$sql = "select a.albumid, a.dateline, sf.event, p.click_9 from uchome_album a, uchome_spacefield sf, uchome_pic p where ".implode(" AND ", $wherearr) . " AND  ( ".implode(" OR ", $wherearrSame) . "  ) order by p.click_9 desc limit 12";
    }
    
    writeLogDebug($sql);

    $albumlist = array();
    $query = $_SGLOBAL['db']->query($sql);
    while($album = $_SGLOBAL['db']->fetch_array($query)) {
        $tmpValue = getalbumsbyuid($album['albumid'], $album['uid'], $uid);
        $tmpValue['hot'] = $album['hot'];
        $albumlist[] = $tmpValue;
    }
    writeLog("*********************************************** album size".count($albumlist));
    $result['albumlist'] = $albumlist;
    echo json_encode($result);
} elseif($op == "getalbumsbysid") {
    $sid = empty($_POST['sid'])?0:intval($_POST['sid']);
    $uid = empty($_POST['uid'])?0:intval($_POST['uid']);
    $type = empty($_POST['type'])?0:intval($_POST['type']);


//        $ouid = empty($_POST['ouid'])?'':$_POST['ouid'];
        if (empty($sid) || empty($uid)) {
                $result['errcode'] = 1001;
                $result['errmsg'] = "uid is empty";
                echo json_encode($result);
                return;
        }
    $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
    $wherearr = array();
    $sql = "";
    if ($type == 1) {
	$wherearr[] = "a.albumname != 'photoalbumsystem'";
        $wherearr[] = " a.sid='$sid' ";
	$wherearr[] = " a.sid=s.sid ";
	$wherearr[] = "p.albumid=a.albumid ";
	$hot = empty($_POST['hot'])?'0':$_POST['hot'];
        if ($isRefresh == "yes") {
            $wherearr[] = " p.hot > ".$hot;
        } else if($isRefresh == "no"){
            $wherearr[] = " p.hot < ".$hot;
        } else {
            //getreward('daylogin', 1, $uid);
        }
	$lastWeek = strtotime("-7 day");
        $wherearr[] = " p.dateline > $lastWeek";
        $sql = "select distinct a.albumid,a.uid,a.sid,p.dateline,p.picid,p.hot from uchome_album a, uchome_pic p, uchome_subject s where ".implode(" AND ", $wherearr) ." order by p.hot desc limit 0,10";
    } else {
	$dateline = empty($_POST['dateline'])?'0':$_POST['dateline'];
	if ($isRefresh == "yes") {
            $wherearr[] = " a.dateline > ".$dateline;
        } else if($isRefresh == "no"){
            $wherearr[] = " a.dateline < ".$dateline;
        } else {
            //getreward('daylogin', 1, $uid);
        }
	$wherearr[] = "a.albumname != 'photoalbumsystem'";
	$wherearr[] = "a.sid='$sid'";
	$sql = "SELECT * FROM ".tname('album')." a WHERE ".implode(" AND ", $wherearr) . " order by a.dateline desc limit 0,10";
    }
        $albumlist = array();
        $query = $_SGLOBAL['db']->query($sql);
        while($album = $_SGLOBAL['db']->fetch_array($query)) {
                $tmpValue = getalbumsbyuid($album['albumid'], $album['uid'], $uid);
		$tmpValue['hot'] = $album['hot'];
		$albumlist[] = $tmpValue;
        }
	writeLog("*********************************************** album size".count($albumlist));
        $result['albumlist'] = $albumlist;
        echo json_encode($result);


}  elseif($op == "getalbumsbyclassid") {
    writeLogDebug("aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa".$uid);
    $uid = empty($_POST['uid'])?0:intval($_POST['uid']);
    $preclassid = getEventByUid($uid);//empty($_POST['classid'])?0:intval($_POST['classid']);
    $classid = getClassid($preclassid);
    $uid = empty($_POST['uid'])?0:intval($_POST['uid']);
    $type = empty($_POST['type'])?0:intval($_POST['type']);
    

//        $ouid = empty($_POST['ouid'])?'':$_POST['ouid'];
        if (empty($classid) || empty($uid)) {
                $result['errcode'] = 1001;
                $result['errmsg'] = "uid is empty";
                echo json_encode($result);
                return;
        }
    writeLogDebug("ccccccccccccccccccccccccccccccc".$uid."   --------- preclassid".$preclassid);
    $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
    $wherearr = array();
    $sql = $sqlcount = "";
    if ($type == 1) {
        $wherearr[] = "a.albumname != 'photoalbumsystem'";
        $wherearr[] = " a.classid='$classid' ";
        $wherearr[] = " a.classid=s.classid ";
        $wherearr[] = "p.albumid=a.albumid ";
        $hot = empty($_POST['hot'])?'0':$_POST['hot'];
        if ($isRefresh == "yes") {
            $wherearr[] = " p.hot > ".$hot;
        } else if($isRefresh == "no"){
            $wherearr[] = " p.hot < ".$hot;
        } else {
            //getreward('daylogin', 1, $uid);
        }

        $sql = "select distinct a.albumid,a.uid,a.classid,p.picid,p.hot from uchome_album a, uchome_pic p, uchome_subject s where ".implode(" AND ", $wherearr) ." order by p.hot desc limit 0,10";
	$sqlcount = "select count(distinct a.albumid) from uchome_album a, uchome_pic p, uchome_subject s where ".implode(" AND ", $wherearr) ." order by p.hot desc limit 0,10";
    } else {
        $dateline = empty($_POST['dateline'])?'0':$_POST['dateline'];
        if ($isRefresh == "yes") {
            $wherearr[] = " a.dateline > ".$dateline;
        } else if($isRefresh == "no"){
            $wherearr[] = " a.dateline < ".$dateline;
        } else {
            //getreward('daylogin', 1, $uid);
        }
        $wherearr[] = "a.albumname != 'photoalbumsystem'";
        $wherearr[] = "a.classid='$classid'";
	$wherearr[] = "a.picnum>0";
        $sql = "SELECT * FROM ".tname('album')." a WHERE ".implode(" AND ", $wherearr) . " order by a.dateline desc limit 0,10";
	$sqlcount = "SELECT count(a.albumid) FROM ".tname('album')." a WHERE ".implode(" AND ", $wherearr) . " order by a.dateline desc limit 0,10";
    }
	writeLogDebug($sql);
        $albumlist = array();
        $query = $_SGLOBAL['db']->query($sql);
	writeLogDebug("eeeeeeeeeeeeeeeeeeeeeeeeee".$uid);
        while($album = $_SGLOBAL['db']->fetch_array($query)) {
		writeLogDebug("fffffffffffffffffffff".$uid);
                $tmpValue = getalbumsbyuid($album['albumid'], $album['uid'], $uid);
		writeLogDebug("gggggggggggggggggggggggggg".$uid);
		if ($tmpValue["isalbumclick"] == 1) continue;
                $tmpValue['hot'] = $album['hot'];
                $albumlist[] = $tmpValue;
        }
	writeLogDebug("GGGGGGGGGGGGGGGGGGGGGGGG".$uid);
        $result['albumlist'] = $albumlist;
	$result['currentgame'] = getEventClass($preclassid);

        $countnum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query($sqlcount), 0);
        $result['totalnum'] = $countnum;

	
        echo json_encode($result);

}   elseif($op == "getmyspecailalbum") {
    //$classid = empty($_POST['classid'])?0:intval($_POST['classid']);
    
    $classid = getEventByUid($uid);//empty($_POST['classid'])?0:intval($_POST['classid']);
    $classid = getClassid($classid);
    $uid = empty($_POST['uid'])?0:intval($_POST['uid']);
    $myid = empty($_POST['myid'])?0:intval($_POST['myid']);
//        $ouid = empty($_POST['ouid'])?'':$_POST['ouid'];
        if (empty($uid)) {
                $result['errcode'] = 1001;
                $result['errmsg'] = "uid is empty";
                echo json_encode($result);
                return;
        }
        
    $specialResult = array();

    $temp00003 = getSpecialAlbum($uid, $classid,  $myid);//$classid);
    if (!empty($temp00003['albumlist'])) {
        $specialResult[] = $temp00003;
        //$specialResult[] = $temp1;
    }


    $temp00002 = getSpecialAlbum($uid, 1409,  $myid);//$classid);
    if (!empty($temp00002['albumlist'])) {
        $specialResult[] = $temp00002;
        //$specialResult[] = $temp1;
    }


    $temp00001 = getSpecialAlbum($uid, 1408,  $myid);//$classid);
    if (!empty($temp00001['albumlist'])) {
        $specialResult[] = $temp00001;
        //$specialResult[] = $temp1;
    }

    $temp0000 = getSpecialAlbum($uid, 1407,  $myid);//$classid);
    if (!empty($temp0000['albumlist'])) {
        $specialResult[] = $temp0000;
        //$specialResult[] = $temp1;
    }


    $temp000 = getSpecialAlbum($uid, 1406, $myid);//$classid);
    if (!empty($temp000['albumlist'])) {
        $specialResult[] = $temp000;
        //$specialResult[] = $temp1;
    }


    $temp00 = getSpecialAlbum($uid, 1405, $myid);//$classid);
    if (!empty($temp00['albumlist'])) {
        $specialResult[] = $temp00;
        //$specialResult[] = $temp1;
    }


    $temp0 = getSpecialAlbum($uid, 1404, $myid);//$classid);
    if (!empty($temp0['albumlist'])) {
        $specialResult[] = $temp0;
        //$specialResult[] = $temp1;
    }


    $temp1 = getSpecialAlbum($uid, 1403, $myid);//$classid);
    if (!empty($temp1['albumlist'])) {
        $specialResult[] = $temp1;
	//$specialResult[] = $temp1;
    }

    $temp2 = getSpecialAlbum($uid, 1402, $myid);//$classid);
    if (!empty($temp2['albumlist'])) {
        $specialResult[] = $temp2;
        //$specialResult[] = $temp1;
    }

    $temp3 = getSpecialAlbum($uid, 14, $myid);//$classid);
    if (!empty($temp3['albumlist'])) {
        $specialResult[] = $temp3;
        //$specialResult[] = $temp1;
    }

    //$specialResult[] = getSpecialAlbum($uid, 14);
    //$specialResult[] = getSpecialAlbum($uid, 14);
    $result['specialalbum'] = $specialResult;
    echo json_encode($result);

}  elseif($op == "getmyrank") {
    $uid = empty($_POST['uid'])?0:intval($_POST['uid']);
    $classid = getEventByUid($uid);//empty($_POST['classid'])?0:intval($_POST['classid']);
    $classid = getClassid($classid);
    writeLogDebug("IIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIII  classid:".$classid."|uid:".$uid);
    $type = empty($_POST['type'])?0:intval($_POST['type']);


        if (empty($classid) || empty($uid)) {
                $result['errcode'] = 1001;
                $result['errmsg'] = "uid is empty";
                echo json_encode($result);
                return;
        }
    $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
    $wherearr = array();
    $sql = "";
 
        //$wherearr[] = "a.albumname != 'photoalbumsystem'";
        $wherearr[] = " a.classid='$classid' ";
        //$wherearr[] = " a.classid=s.classid ";
        //$wherearr[] = "p.albumid=a.albumid ";
        $hot = empty($_POST['hot'])?'0':$_POST['hot'];
        if ($isRefresh == "yes") {
            $wherearr[] = " pc.hot > ".$hot;
        } else if($isRefresh == "no"){
            $wherearr[] = " pc.hot < ".$hot;
        } else {
            //getreward('daylogin', 1, $uid);
        }
        $lastWeek = strtotime("-7 day");
        $wherearr[] = " a.dateline > $lastWeek";

	$wherearr[] = "a.picnum>0";
	//$wherearr[] = " a.uid = ".$uid;
        //$sql = "select distinct uid, albumid, classid, picid, hot from(select distinct a.albumid,a.uid,a.classid,p.picid,p.hot from uchome_album a, uchome_pic p, uchome_subject s where ".implode(" AND ", $wherearr) ." order by p.hot desc limit 0,50) as t group by t.uid order by t.hot desc limit 0,20";
	$sql = "select a.albumid,a.uid,a.classid,p.picid,p.hot from uchome_album a, uchome_pic p  where ".implode(" AND ", $wherearr) ." group by a.uid order by p.hot desc limit 0,20";
	$sql = "select  a.albumid, a.uid, a.classid, a.picnum, pc.hot from uchome_album a , (select p1.albumid, p1.hot from uchome_pic p1 where p1.hot = (select max(hot) from uchome_pic where albumid = p1.albumid) order by p1.hot desc ) as pc where ".implode(" AND ", $wherearr) ." and a.albumid = pc.albumid group by a.uid order by pc.hot desc limit 0, 26";
	writeLogDebug($sql);
        $albumlist = array();
        $query = $_SGLOBAL['db']->query($sql);
	$result['rank'] = getRank($uid, $classid);
        while($album = $_SGLOBAL['db']->fetch_array($query)) {
                $tmpValue = getalbumsbyuid($album['albumid'], $album['uid'], $uid);
                $tmpValue['hot'] = $album['hot'];
                $albumlist[] = $tmpValue;
        }
        $result['albumlist'] = $albumlist;
        $result['currentgame'] = getEventClass(14);
        echo json_encode($result);

} elseif($op == "getweekrank") {
    writeLogDebug("000000000000000000000000000000000".$uid);
    $classid = empty($_POST['classid'])?0:intval($_POST['classid']);
    $uid = empty($_POST['uid'])?0:intval($_POST['uid']);
    $type = empty($_POST['type'])?0:intval($_POST['type']);


        if (empty($classid) || empty($uid)) {
                $result['errcode'] = 1001;
                $result['errmsg'] = "uid is empty";
                echo json_encode($result);
                return;
        }
    $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
    $wherearr = array();
    $sql = "";

        $wherearr[] = "a.albumname != 'photoalbumsystem'";
        $wherearr[] = " a.classid='$classid' ";
        //$wherearr[] = " a.classid=s.classid ";
        $wherearr[] = "p.albumid=a.albumid ";
        $weekindex = empty($_POST['weekindex'])?'0':$_POST['weekindex'];
        if ($isRefresh == "yes") {
            $wherearr[] = " a.weekindex > ".$weekindex;
        } else if($isRefresh == "no"){
            $wherearr[] = " a.weekinex < ".$weekindex;
        } else {
            //getreward('daylogin', 1, $uid);
        }
        $lastWeek = strtotime("-7 day");
    	$wherearr[] = " a.dateline > $lastWeek";
	$wherearr[] = " a.weekhot > 0";	
        //$wherearr[] = " a.uid = ".$uid;

        $sql = "select t.albumid, t.weekindex, t.weekhot, t.uid, t.classid, t.picid, t.hot from(select distinct a.albumid,a.weekindex,a.weekhot, a.uid,a.classid,p.picid,p.hot from uchome_album a, uchome_pic p, uchome_subject s where ".implode(" AND ", $wherearr) ." order by a.weekhot desc limit 0,50) as t group by t.uid order by t.weekhot desc limit 0,10";
	$sql = "select a.albumid,a.uid,a.weekhot from uchome_album a where a.classid=1409 and a.weekhot>0 and a.picnum>0  group by a.uid order by a.weekhot desc limit 10";
	writeLogDebug("2222222222222222222222222222222222222222222");
        $albumlist = array();
        $query = $_SGLOBAL['db']->query($sql);
        $result['rank'] = 1;
        while($album = $_SGLOBAL['db']->fetch_array($query)) {
                $tmpValue = getalbumsbyuid($album['albumid'], $album['uid'], $uid);
                $tmpValue['weekhot'] = $album['weekhot'];
                $albumlist[] = $tmpValue;
        }
        writeLogDebug("*********************************************** album size".count($albumlist));
        $result['albumlist'] = $albumlist;
	$currentgame = getEventClass(14);
	$currentgame['activitystopmsg'] = "9月14日至9月20日";
        $result['currentgame'] = $currentgame;
	$result['rank'] = "12";
        echo json_encode($result);

}  elseif($op == "getweekrankhome") {
    $uid = empty($_POST['uid'])?0:intval($_POST['uid']);
    $classid = getEventByUid($uid);//empty($_POST['classid'])?0:intval($_POST['classid']);
     $classid = getClassid($classid);
    $type = empty($_POST['type'])?0:intval($_POST['type']);


        if (empty($classid) || empty($uid)) {
                $result['errcode'] = 1001;
                $result['errmsg'] = "uid is empty";
                echo json_encode($result);
                return;
        }
    $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
    $wherearr = array();
    $sql = "";

        $wherearr[] = "a.albumname != 'photoalbumsystem'";
        $wherearr[] = " a.classid=$classid ";
        //$wherearr[] = " a.classid=s.classid ";
        //$wherearr[] = "p.albumid=a.albumid ";
        $weekindex = empty($_POST['weekindex'])?'0':$_POST['weekindex'];
        if ($isRefresh == "yes") {
            $wherearr[] = " a.weekindex > ".$weekindex;
        } else if($isRefresh == "no"){
            $wherearr[] = " a.weekinex < ".$weekindex;
        } else {
            //getreward('daylogin', 1, $uid);
        }
        //$wherearr[] = " a.uid = ".$uid;
    $lastWeek = strtotime("-7 day");
        $wherearr[] = " a.dateline > $lastWeek";

        $wherearr[] = "a.picnum>0";
        $sql = "select distinct a.albumid,a.dateline, a.picnum, a.weekindex,a.weekhot, a.uid,a.classid,p.picid,p.hot from uchome_album a, uchome_pic p, uchome_subject s where ".implode(" AND ", $wherearr) ." order by a.weekhot desc limit 0,3";
	
	$sql = "select  a.albumid, a.uid, a.classid, a.picnum, pc.hot from uchome_album a , (select p1.albumid, p1.hot from uchome_pic p1 where p1.hot = (select max(hot) from uchome_pic where albumid = p1.albumid) order by p1.hot desc ) as pc where ".implode(" AND ", $wherearr) ." and a.albumid = pc.albumid group by a.uid order by pc.hot desc limit 0, 3";

	writeLogDebug($sql);
        $albumlist = array();
        $query = $_SGLOBAL['db']->query($sql);
        $result['rank'] = 1;
        while($album = $_SGLOBAL['db']->fetch_array($query)) {
                $tmpValue = getalbumsbyuid($album['albumid'], $album['uid'], $uid);
                $tmpValue['weekhot'] = $album['weekhot'];
                $albumlist[] = $tmpValue;
        }
        writeLog("*********************************************** album size".count($albumlist));
        $result['albumlist'] = $albumlist;
        $result['currentgame'] = getEventClass(14);
        $result['rank'] = getRank($uid, $classid);

        $countpknum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(distinct uid) FROM ".tname('album')." WHERE classid>0"), 0);
	$result['totalpknum'] = $countpknum;

        $sqlcount = "SELECT count(a.albumid) FROM ".tname('album')." a WHERE a.classid=$classid  order by a.dateline desc limit 0,10";
        $countnum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query($sqlcount), 0);
        $result['totalnum'] = $countnum;	

        echo json_encode($result);

}  elseif($op == 'buygood') {


}




?>
