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

writeLogError("do_appsubject.php");

$op = empty($_POST['op'])?'':$_POST['op'];
$lat = empty($_GET['lat'])?'0':$_GET['lat'];
$lng = empty($_GET['lng'])?'0':$_GET['lng'];
$result = array(
    'errcode' => 0,
    'errmsg' => 'error'
);
$_SGLOBAL['supe_uid'] = $uid;
//$spacea = getspace($uid);
//$_SGLOBAL['supe_username'] = $spacea['name'];
$timea = time();
writeLogError("----------------------------------------uid:".$uid." op:".$op."  time:".$timea);
if($op == 'getuserlist') {
    writeLogKang($uid."+++++++++++++++op:getuserlist");
    $wherearr = array();
    $wherearr[] = " sf.uid = s.uid ";
    $wherearr[] = " sf.uid != $uid ";

    $sex = empty($_POST['sex'])?'':$_POST['sex'];
    if (!empty($sex)) {
                $wherearr[] = " sf.sex = " . $sex;
    }
   writeLogKang($uid."+++++++++++++++op:getuserlist  query spaefield start");
    $game = empty($_POST['game'])?'':$_POST['game'];
    if (!empty($game)) {
        $queryContact = $_SGLOBAL['db']->query('SELECT qq, msn, sex, email, mobile,event,tags FROM '.tname('spacefield')." WHERE uid = '$uid'");
        $valueContact = $_SGLOBAL['db']->fetch_array($queryContact);
        $mygames = $valueContact['event'];
        $arr = explode(",",$mygames);

        $wherearrb = array();
        foreach($arr as $u){
            if ($u > 0) {
                $wherearrb[] = " sf.event like \"%," . $u . ",%\" ";
            }
        }
	if (count($wherearrb) > 0) {
            $sqlb = " ( ".implode(" OR ", $wherearrb)." ) ";
            $wherearr[] = $sqlb;
	} else {
	    $wherearr[] = " sf.event='' ";
	}
    }
   writeLogKang($uid."+++++++++++++++op:getuserlist  query spaefield end");
    $dateline = empty($_POST['dateline'])?'0':$_POST['dateline'];
    if ($dateline > 0) {
	$wherearr[] = " s.lastlogin < $dateline";
    }

    $orderby = " order by s.lastlogin desc ";


    $from = "";//empty($_POST['from'])?'':$_POST['from'];
    if (!empty($from) && $from == 'near') {

	if ($lat < 2 || $lng < 2) {
	    echo json_encode($result);
	    return;
	}
	
	$sort = empty($_POST['sort'])?'':$_POST['sort'];	
	if ($sort == 1) {
	    $orderby = " order by distancetmp asc ";
	    $timeFilter = sstrtotime(sgmdate('Y-m-d')) - 3600*24*3;
	    $wherearr[] = " s.lastlogin > $timeFilter";
	} else {
	    $orderby = " order by distancetmp asc  ";
	}

	$wherearr[] = " s.isshowme != 1 ";
	$distance = empty($_POST['distance'])?'0':$_POST['distance'];
	/*if (!empty($distance)) {
	    $wherearr[] = " distancetmp > $distance ";
	#}*/
	$sql = 'select abc.distancetmp, abc.uid, abc.lat, abc.lng,abc.event,abc.tags,abc.lastlogin from (SELECT  distinct sf.uid,s.lat, s.lng,sf.event,sf.tags,s.lastlogin '. ", (6378.138 * 2 * asin(sqrt(pow(sin((s.lat * pi() / 180 - $lat * pi() / 180) / 2),2) + cos(s.lat * pi() / 180) * cos($lat * pi() / 180) * pow(sin((s.lng * pi() / 180 - $lng * pi() / 180) / 2),2))) * 1000)  as distancetmp FROM " .tname('spacefield')." sf, ".tname('space')." s WHERE " .implode(" AND ", $wherearr). " ) as abc where abc.distancetmp > $distance  $orderby   limit 0, 3";
	writeLog($sql); 
        /*$sql = 'SELECT  distinct sf.uid,s.lat, s.lng,sf.event,sf.tags,s.lastlogin '. ", (6378.138 * 2 * asin(sqrt(pow(sin((s.lat * pi() / 180 - $lat * pi() / 180) / 2),2) + cos(s.lat * pi() / 180) * cos($lat * pi() / 180) * pow(sin((s.lng * pi() / 180 - $lng * pi() / 180) / 2),2))) * 1000)  as distancetmp FROM " .tname('spacefield')." sf, ".tname('space')." s WHERE " .implode(" AND ", $wherearr) . $orderby . "    limit 0,30"; */
	writeLog("MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM".$lat."--".$lng."==".$distance);
    } else {
    $wherearr[] = " s.isshowme != 1 ";
    $sql = 'SELECT sf.uid,sf.event,sf.tags,s.lastlogin,s.rp FROM '.tname('spacefield')." sf, ".tname('space')." s WHERE " .implode(" AND ", $wherearr) . " AND (sf.qq!='' or sf.msn!='') AND sf.tags!=''" . $orderby . "   limit 0, 4";
	if (empty($dateline)) {
                $rewardtmp = getrewardapp('daylogin', 1, $uid);
                if($rewardtmp['credit'] > 0) {
                    $result['gongxi'] = "恭喜获得每日登录奖励：积分+".$rewardtmp['credit'];
                }

	}

    }

    writeLogKang($uid."+++++++++++++++op:getuserlist ....... prepare sql ");
    $queryGames = $_SGLOBAL['db']->query($sql);
    writeLogKang($uid."+++++++++++++++op:getuserlist ....... query sql ");
    //writeLogKang($uid."    ***********************       ".$sql);
    while($valueGame = $_SGLOBAL['db']->fetch_array($queryGames))
    {
        $value = getspace($valueGame['uid']);
	    $userId = $valueGame['uid'];

        /*$queryAlbum = $_SGLOBAL['db']->query("SELECT a.albumid,a.albumname,a.dateline,s.sid,s.title FROM ".tname('album')." a,".tname('subject')." s WHERE a.albumname != 'photoalbumsystem' and  a.uid='$userId' and a.sid = s.sid order by a.dateline desc limit 1");
	$albumlist = "";
        while($album = $_SGLOBAL['db']->fetch_array($queryAlbum)) {
                $value['albumid'] = $album['albumid'];
		$value['albumname'] = $album['albumname'];	
		$value['sid'] = $album['sid'];	
		$value['subjecttitle'] = $album['title'];
		$tmpValue = getalbumsbyuid($album['albumid'], $album['uid'], $uid);
        	$tmpValue['hot'] = $album['hot'];
        	$albumlist = $tmpValue;
        }
	$value['albumlist'] = $albumlist;*/

        $avatar_exists = ckavatar($value['uid']);
        if ($avatar_exists ==1) {
            $avatarfile = avatar_file($value['uid'], 'middle');
            $value["avatarfile"] = $avatarfile;
        }
        $value['event'] = $valueGame['event'];
	$value['distance'] = $valueGame['distancetmp'];
	$value['friends'] = '';
	$value['friend'] = '';
	$value['feedfriend'] = '';
	$value['privacy'] = '';

	//if(empty($value['event']) || empty($value['tags'])) continue;
	//if (!empty($value['qq']) || !empty($value['weixin'])) {
       if(empty($value['uid']) || empty($value['uid'])) continue;
	    $user[] = $value;
	//}
        //$test[] = $value;

    }

    $result['userlist'] = $user;
    
    echo json_encode($result);
    writeLogKang($uid."+++++++++++++++op:getuserlist ....... end ");

} elseif($op == "showme") {

    $showme = empty($_POST['showme'])?0:intval($_POST['showme']);
    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET isshowme='$showme' WHERE uid='$uid'");

    echo json_encode($result);

} elseif($op == "setsendheart") {

    $showme = empty($_POST['sendheart'])?0:intval($_POST['sendheart']);
    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET sendheart='$showme' WHERE uid='$uid'");

    echo json_encode($result);

} elseif($op == "getsendheartlist") {
    writeLogDebug("1111");
    $wherearr = array();
    $wherearr[] = " sf.uid = s.uid ";
    $wherearr[] = " sf.uid != $uid ";
    $orderby = " order by s.lastlogin desc ";

    $game = empty($_POST['game'])?'':$_POST['game'];
    if (!empty($game)) {
        $queryContact = $_SGLOBAL['db_slave']->query('SELECT qq, msn, sex, email, mobile,event,tags FROM '.tname('spacefield')." WHERE uid = '$uid'");
        $valueContact = $_SGLOBAL['db_slave']->fetch_array($queryContact);
        $mygames = $valueContact['event'];
        $arr = explode(",",$mygames);

        $wherearrb = array();
        foreach($arr as $u){
            if ($u > 0) {
                $wherearrb[] = " sf.event like \"%," . $u . ",%\" ";
            }
        }
        $sqlb = " ( ".implode(" OR ", $wherearrb)." ) ";
        $wherearr[] = $sqlb;
    }

    $wherearr[] = " sf.uid not in (select sh.tid from ".tname('sendheartlog')." sh where sh.uid=$uid) ";
    writeLogDebug("................sendheart...................");
    $readcount = 10;
    $today = sstrtotime(sgmdate('Y-m-d'));
$queryRows = $_SGLOBAL['db']->query("SELECT count(shid) as count FROM ".tname('sendheartlog')." WHERE uid = $uid and dateline > $today  ORDER BY dateline DESC");
                            writeLog("............22222222222..........");
                            $valueRows = $_SGLOBAL['db']->fetch_array($queryRows);
                            writeLog("............33333333333333..........");
                            writeLog("=======================row:".$valueRows['count']); 
   $readcount = $readcount - $valueRows['count'];
    writeLogDebug("...........".$readcount);
   if ($readcount <= 0) {
    $result['errcode'] = -1;
    $result['errmsg'] = "今天的查看名额已用完，明天再来吧! \n去首页添加玩家为心加好友后，也能互加哦。";
  
    echo json_encode($result);
 }
     $wherearr[] = " s.sendheart = 1 ";
   $sql = 'SELECT  distinct sf.uid,sf.event,sf.tags,s.lastlogin,s.rp FROM '.tname('spacefield')." sf, ".tname('space')." s WHERE " .implode(" AND ", $wherearr) . $orderby . "   limit $readcount";
    writeLogDebug($sql);
    $queryGames = $_SGLOBAL['db_slave']->query($sql);
    writeLogDebug($sql);
    while($valueGame = $_SGLOBAL['db_slave']->fetch_array($queryGames))
    {
        $value = getspace($valueGame['uid']);

        $userId = $valueGame['uid'];

        $queryAlbum = $_SGLOBAL['db_slave']->query("SELECT a.albumid,a.albumname,a.dateline,s.sid,s.title FROM ".tname('album')." a,".tname('subject')." s WHERE a.albumname != 'photoalbumsystem' and  a.uid='$userId' and a.sid = s.sid order by a.dateline desc limit 1");
        $albumlist = "";
        while($album = $_SGLOBAL['db_slave']->fetch_array($queryAlbum)) {
                $value['albumid'] = $album['albumid'];
                $value['albumname'] = $album['albumname'];
                $value['sid'] = $album['sid'];
                $value['subjecttitle'] = $album['title'];
                $tmpValue = getalbumsbyuid($album['albumid'], $album['uid'], $uid);
                $tmpValue['hot'] = $album['hot'];
                $albumlist = $tmpValue;
        }
        $value['albumlist'] = $albumlist;

        $avatar_exists = ckavatar($value['uid']);
        if ($avatar_exists ==1) {
            $avatarfile = avatar_file($value['uid'], 'middle');
            $value["avatarfile"] = $avatarfile;
        }
        $value['event'] = $valueGame['event'];
        $value['distance'] = $valueGame['distancetmp'];
        $value['friends'] = '';
        $value['friend'] = '';
        $value['feedfriend'] = '';
        $value['privacy'] = '';

        if(empty($value['event']) || empty($value['tags'])) continue;
        if (!empty($value['qq']) || !empty($value['weixin'])) {
            $test[] = $value;
        }
        //$test[] = $value;

    }

    $result['userlist'] = $test;
    echo json_encode($result);

} elseif($op == "updatesendheart") {

    $setarr = array(
        'uid' => $uid,     
        'tid' => $tid,
        'dateline' => $_SGLOBAL['timestamp']
    );
    inserttable('sendheartlog', $setarr);
    echo json_encode($result);

}




?>
