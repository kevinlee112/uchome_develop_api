<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_ajax.php 12535 2009-07-06 06:22:34Z zhengqingpeng $
*/

/*if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}*/
include_once(S_ROOT.'./source/function_cp.php');
$op = empty($_POST['op'])?'':$_POST['op'];
$lat = empty($_GET['lat'])?'':$_GET['lat'];
$lng = empty($_GET['lng'])?'':$_GET['lng'];

$result = array(
			'errcode' => 0,
			'errmsg' => 'error username or password'
		);
		//header('Content-type: text/json'); 
//writeLogError("-------------- do_app.php op is -> ".$op."    version:".$_POST['appversion'] );
if($op == 'doing') {
        //¼ÇÂ¼
        $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
        $doid = empty($_POST['doid'])?'0':$_POST['doid'];
        $where = "";
        if ($isRefresh == "yes") {
            $where = " WHERE doid > ".$doid;
        } else if($isRefresh == "no"){
            $where = " WHERE doid < ".$doid;
        }
        $dolist = array();
        $query = $_SGLOBAL['db']->query("SELECT *
                FROM ".tname('doing'). $where ." 
                ORDER BY dateline DESC LIMIT 0,10");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                realname_set($value['uid'], $value['username']);
                $value['title'] = getstr($value['message'], 0, 0, 0, 0, 0, -1);
                $dolist[] = $value;
        }

        $result = array(
                                                'errcode' => 0,
                                                'errmsg' => '',
                                                'doing' => $dolist
                                        );

        echo json_encode($result);
        return;
} else if($op == 'info') {


	
	include_once(S_ROOT.'./source/function_cp.php');

if(!@include_once(S_ROOT.'./data/data_eventclass.php')) {
        include_once(S_ROOT.'./source/function_cache.php');
        eventclass_cache();
}

	
	$uid = empty($_POST['uid'])?'':$_POST['uid'];
	$myId = empty($_POST['myid'])?'':$_POST['myid'];


	$wherearr[] = "(uid='$uid')";
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." WHERE ".implode(' OR ', $wherearr)." LIMIT 1");
	
	$space = $_SGLOBAL['db']->fetch_array($query);
	$space['errcode'] = 0;

	if ($lat < 2 ||$lng < 2 || $space['lat'] < 2 || $space['lng'] < 2) {
            $space['distance'] = -1;
        } else {
            $space['distance'] = round(getDistance($lat, $lng, $space['lat'], $space['lng']), 2);
        }

	writeLog(" ------------------------------------ ^^^^^^^^^^^^^^ distance is: ".$space['distance']." lat ".$lat."  lng ".$lng."  ^lat ".$space['lat']. " ^lng ".$space['lng']);

       writeLog("SELECT *  FROM ".tname('album')." as a inner join ".tname('pic')." as p on a.albumid=p.albumid WHERE p.uid='$uid' and a.albumname <> 'photoalbumsystem' ORDER BY dateline DESC LIMIT 6");


	$feedlist = array();

        $queryPic = $_SGLOBAL['db']->query("SELECT *  FROM ".tname('album')." as a inner join ".tname('pic')." as p on a.albumid=p.albumid WHERE p.uid='$uid' and a.albumname not like '%photoalbums%' ORDER BY p.dateline DESC LIMIT 6");
	writeLog("SELECT *  FROM ".tname('album')." as a inner join ".tname('pic')." as p on a.albumid=p.albumid WHERE p.uid='$uid' and a.albumname <> 'photoalbumsystem' ORDER BY dateline DESC LIMIT 6");
        while ($valuePic = $_SGLOBAL['db']->fetch_array($queryPic)) {
                $feedlist[] = $valuePic;
        }

        $space['piclist'] = $feedlist;
	
	$status = getfriendstatus($myId, $uid);
	$status2 = getfriendstatus($uid, $myId);

	writeLog("********  info status-".$status."    status2-".$status2);
	$space['isfriendstr'] = "加为好友";
	if ($status == 0 && $status2 == -1) {
	    $space['isfriendstr'] = "等待对方同意";
	} elseif ($status == -1 && $status2 == 0) {
            $space['isfriendstr'] = "同意加为好友";
        }
	$space['isfriend'] = $status;
	$space['isfriendb'] = $status2;
	writeLog("getinfo: isfriend->".$status."  myid".$myId." uid:".$uid);

        $queryContact = $_SGLOBAL['db']->query('SELECT qq, msn, sex, email, mobile,event,tags,resideprovince,residecity FROM '.tname('spacefield')." WHERE uid = '$uid'");
        $valueContact = $_SGLOBAL['db']->fetch_array($queryContact);


	$eventCat = array();
        $queryEvent = $_SGLOBAL['db']->query("SELECT distinct ec.classid, ec.classname FROM ".tname("event") . " as e inner join ".tname("eventclass") . " as ec on e.classid=ec.classid where e.uid=". $uid );
        while($value=$_SGLOBAL['db']->fetch_array($queryEvent)) {
                $value['pic'] = $_SGLOBAL['eventclass'][$value['classid']]['poster'];
                $eventCat[] = $value;
        }
	$space['games'] = $eventCat;

	$eventList = array();
	$queryEventList = $_SGLOBAL['db']->query("SELECT e.eventid, e.uid, e.username, e.dateline, e.title, e.classid, ec.classname FROM " . tname("event")." as e inner join ".tname("eventclass") . " as ec on e.classid=ec.classid where e.uid =".$uid." ORDER BY e.eventid DESC LIMIT 0, 5");
        while($event = $_SGLOBAL['db']->fetch_array($queryEventList)){
		$eventList[] = $event;
	}
	$space['events'] = $eventList;

        $space['qq'] = $valueContact['qq'];
        $space['weixin'] = $valueContact['msn'];
	$space['sex'] = $valueContact['sex'];
	$space['mobile'] = $valueContact['mobile'];
	$space['email'] = $valueContact['email'];
        $space['event'] = $valueContact['event'];
        $space['tags'] = $valueContact['tags'];
	$space['province'] = $valueContact['resideprovince'];
	$space['city'] = $valueContact['residecity'];
	//$space['district'] = "通州区";

	$space['isblack'] = 0;
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('blacklist')." WHERE uid='$myId' AND buid='$uid'");
	if($tospace = $_SGLOBAL['db']->fetch_array($query)) {
	    $space['isblack'] = 1;
	}

	$avatar_exists = ckavatar($uid);
	$avatarfile = avatar_file($uid, 'middle');
	$space["avatarfile"] = $avatarfile;


	$today = sstrtotime(sgmdate('Y-m-d'));
	writeLog("..................................".$today);
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('notification')." WHERE authorid='$myId' AND type='rp' AND dateline > $today"), 0);
	if ($count > 0) {
	    $space["rplimit"] = 0;
	} else {
      	    $space["rplimit"] = 1;
	}
	writeLog("..................................".today."---".$space["rplimit"]);
	$space["hasgame"] = 0;
    $gameSame = "";
    $wherearrSame = array();

$queryTEvent = $_SGLOBAL['db']->query('SELECT qq, msn, sex, email, mobile,event,tags FROM '.tname('spacefield')." WHERE uid = '$myId'");
            $valueTEvent = $_SGLOBAL['db']->fetch_array($queryTEvent);
            $tEvent = $valueTEvent["event"];
$arrSame = explode(",",$tEvent);
$tEvent = $space['event'];
    foreach($arrSame as $u){
        if ($u > 0) {
            writeLog("......................... : ".$u);
            writeLog("222......................... : ".$tEvent);
            if (strstr($tEvent, ",".$u.",")) {
		$space["hasgame"] = 1;
                $wherearrSame[] = " classid=".$u." ";
                writeLog("3333......................... : ".$tEvent);
            }
        }
    }
    writeLog("444......................... : ".implode(" AND ", $wherearrSame));
    if ($space["hasgame"] == 1) {
        $queryEventGame = $_SGLOBAL['db']->query("SELECT classid, classname FROM ".tname("eventclass") . " as ec WHERE " .implode(" OR ", $wherearrSame)  );
        while($valueEventGame=$_SGLOBAL['db']->fetch_array($queryEventGame)) {
                $gameSame = $gameSame . $valueEventGame['classname'] . ",";
        }

	writeLog("^^^^^^^^^^^^^^^^^^^^^^^^^^^^".$gameSame);
        $gameSame = trim($gameSame, ",");
	$gameSameB = explode(",", $gameSame);
        $tmpIndex = mt_rand(0, count($gameSameB)-1);
	$radomGame = $gameSameB[$tmpIndex];
	$space["hinotes"] = array("你也在玩".$radomGame,$radomGame."互加不","hi，一起玩".$radomGame);
	writeLog($radomGame."&&&&&&&&&&&&&&&&&&&&&".$tmpIndex."--".(count($gameSameB)-1));
    }
/*
    $queryMyContact = $_SGLOBAL['db']->query('SELECT qq, msn, sex, email, mobile,event,tags FROM '.tname('spacefield')." WHERE uid = '$myId'");
    $valueMyContact = $_SGLOBAL['db']->fetch_array($queryMyContact);
    $myGames = $valueMyContact['event'];
    $myarr = explode(",",$myGames);
    foreach($myarr as $u){
	writeLog($space['event']."***************************".",".$u.",");
        if ($u > 0 && strstr($space['event'], ",".$u.",")) {
            $space['hasgame'] = 1;
        }
    }
*/


	$space["rank"] = getRank($uid, 14);
	$countpknum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(distinct uid) FROM ".tname('album')." WHERE classid>0 AND uid=$uid"), 0);
	if ($countpknum > 0) {
	    $result['ishowcrown'] = 1;
	} else {
	    $result['ishowcrown'] = 0;
	}
        
	
	echo json_encode($space);//$avatarfile);//($space);
	
	return;
} else if ($op == 'friend') {
	include_once(S_ROOT.'./source/function_cp.php');	
	//´¦Àí²éÑ¯
	
	$_GET['view'] = 'me';

	$uid = empty($_POST['uid'])?'':$_POST['uid'];
        $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
        $dateline = empty($_POST['dateline'])?'0':$_POST['dateline'];
        $where = "";
        if ($isRefresh == "yes") {
            $where = " AND main.dateline > ".$dateline;
        } else if($isRefresh == "no"){
            $where = " AND main.dateline < ".$dateline;
        }

	$start = 0;
	$perpage = 10;
	$list = $ols = $fuids = array();
	
	$wheresql = $where;
	$query = $_SGLOBAL['db']->query("SELECT s.uid, s.username,s.name,main.dateline, f.resideprovince, f.residecity, f.note, f.spacenote, f.sex, main.gid, main.num
			FROM ".tname('friend')." main
			LEFT JOIN ".tname('space')." s ON s.uid=main.fuid
			LEFT JOIN ".tname('spacefield')." f ON f.uid=main.fuid
			WHERE main.uid='$uid' AND main.status='1' $wheresql
			ORDER BY  main.dateline DESC
			LIMIT $start,$perpage"); //ORDER BY main.num DESC, main.dateline DESC
			
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
			$value['p'] = rawurlencode($value['resideprovince']);
			$value['c'] = rawurlencode($value['residecity']);
			$value['group'] = $groups[$value['gid']];
			$value['isfriend'] = 1;
			$fuids[] = $value['uid'];
			$value['note'] = getstr($value['note'], 28, 0, 0, 0, 0, -1);
			$avatar_exists = ckavatar($value['uid']);
                	if ($avatar_exists ==1) {
                        	$avatarfile = avatar_file($value['uid'], 'middle');
                        	$value["avatarfile"] = $avatarfile;
                	}
			if ($value['uid'] != 0) {
			//	$list[] = $value;
				$vuid = $value['uid'];
			       $queryContact = $_SGLOBAL['db']->query('SELECT qq, msn, sex, email, mobile,event,tags FROM '.tname('spacefield')." WHERE uid = '$vuid'");
			        $valueContact = $_SGLOBAL['db']->fetch_array($queryContact);
			        $value['qq'] = $valueContact['qq'];
			        $value['weixin'] = $valueContact['msn'];
			        $value['event'] = $valueContact['event'];
			        $value['tags'] = $valueContact['tags'];

				$list[] = $value;



			}
		}
	
	$result = array(
						'errcode' => 0,
						'errmsg' => '',
						'friend' => $list
					);
	
	echo json_encode($result);
	
	return;
		

	if($space['friendnum']) {
		if($wheresql) {
			$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('friend')." main WHERE main.uid='$space[uid]' AND main.status='1' $wheresql"), 0);
		} else {
			$count = $space['friendnum'];
		}
		if($count) {
			$query = $_SGLOBAL['db']->query("SELECT s.*, f.resideprovince, f.residecity, f.note, f.spacenote, f.sex, main.gid, main.num
				FROM ".tname('friend')." main
				LEFT JOIN ".tname('space')." s ON s.uid=main.fuid
				LEFT JOIN ".tname('spacefield')." f ON f.uid=main.fuid
				WHERE main.uid='$space[uid]' AND main.status='1' $wheresql
				ORDER BY main.num DESC, main.dateline DESC
				LIMIT $start,$perpage");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
				$value['p'] = rawurlencode($value['resideprovince']);
				$value['c'] = rawurlencode($value['residecity']);
				$value['group'] = $groups[$value['gid']];
				$value['isfriend'] = 1;
				$fuids[] = $value['uid'];
				$value['note'] = getstr($value['note'], 28, 0, 0, 0, 0, -1);
				$list[$value['uid']] = $value;
			}
		}

		//·ÖÒ³
		$multi = multi($count, $perpage, $page, $theurl);
		$friends = array();
		//È¡100ºÃÓÑÓÃ»§Ãû
		$query = $_SGLOBAL['db']->query("SELECT f.fusername, s.name, s.namestatus, s.groupid FROM ".tname('friend')." f
			LEFT JOIN ".tname('space')." s ON s.uid=f.fuid
			WHERE f.uid=$_SGLOBAL[supe_uid] AND f.status='1' ORDER BY f.num DESC, f.dateline DESC LIMIT 0,100");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$fusername = ($_SCONFIG['realname'] && $value['name'] && $value['namestatus'])?$value['name']:$value['fusername'];
			$friends[] = addslashes($fusername);
		}
		$friendstr = implode(',', $friends);
	}

	if($space['self']) {
		$groupselect = array($group => ' class="current"');

		//ºÃÓÑ¸öÊý
		$maxfriendnum = checkperm('maxfriendnum');
		if($maxfriendnum) {
			$maxfriendnum = checkperm('maxfriendnum') + $space['addfriend'];
		}
	}
}  else if ($op == 'friendids') {
        include_once(S_ROOT.'./source/function_cp.php');
        $list = $ols = $fuids = array();
	$uid = empty($_POST['uid'])?'':$_POST['uid'];
        $wheresql = $where;
        $query = $_SGLOBAL['db']->query("SELECT s.uid, s.username,s.name,main.dateline, f.resideprovince, f.residecity, f.note, f.spacenote, f.sex, main.gid, main.num
                        FROM ".tname('friend')." main
                        LEFT JOIN ".tname('space')." s ON s.uid=main.fuid
                        LEFT JOIN ".tname('spacefield')." f ON f.uid=main.fuid
                        WHERE main.uid='$uid' AND main.status='1' $wheresql
                        ORDER BY  main.dateline DESC
                        LIMIT 30"); //ORDER BY main.num DESC, main.dateline DESC
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                        if ($value['uid'] != 0) {
                                $vuid = $value['uid'];
                                $list[] = $vuid;
                        }
                }
        $result = array(
                                                'errcode' => 0,
                                                'errmsg' => '',
                                                'friend' => $list
                                        );

        echo json_encode($result);

        return;

} else if ($op == 'addfriend') {
	
	include_once(S_ROOT.'./source/function_cp.php');
	
	$uid = empty($_POST['uid'])?'':$_POST['uid'];
	$myId = empty($_POST['myid'])?'':$_POST['myid'];

	$_SGLOBAL['supe_uid'] = $myId;
	//¼ì²âÓÃ»§
	if($uid == $_SGLOBAL['supe_uid']) {
		$result = array(
			'errcode' => 30001,
			'errmsg' => 'friend_self_error'
		);
		echo json_encode($result);
		return;
	}
	
	$space = getspace($myId);

	$_SGLOBAL['supe_nickname'] = $space['name'];

	writeLog("^^^^^^^^^^^^^^^^ nickname:".$_SGLOBAL['supe_nickname']." add friend");
	
	if($space['friends'] && in_array($uid, $space['friends'])) {
		$result = array(
			'errcode' => 30002,
			'errmsg' => '你们已经是好友了'
		);
		echo json_encode($result);
		return;
	}
	
	$tospace = getspace($uid);
	
	if(empty($tospace) || empty($space)) {
		$result = array(
			'errcode' => 30002,
			'errmsg' => 'space_does_not_exist'
		);
		echo json_encode($result);
		return;
	}
	
	//ÓÃ»§×é
	$groups = getfriendgroup();
	
	//¼ì²âÏÖÔÚ×´Ì¬
	$status = getfriendstatus($_SGLOBAL['supe_uid'], $uid);
	
	if($status == 1) {
		$result = array(
			'errcode' => 30003,
			'errmsg' => '你们已经是好友了'
		);
		echo json_encode($result);
		return;
	} else {
		/*//¼ì²éÊýÄ¿
		$maxfriendnum = checkperm('maxfriendnum');
		if($maxfriendnum && $space['friendnum'] >= $maxfriendnum + $space['addfriend']) {
			if($_SGLOBAL['magic']['friendnum']) {
				showmessage('enough_of_the_number_of_friends_with_magic');
			} else {
				showmessage('enough_of_the_number_of_friends');
			}
		}*/
				
		//¶Ô·½ÊÇ·ñ°Ñ×Ô¼º¼ÓÎªÁËºÃÓÑ
		$fstatus = getfriendstatus($uid, $_SGLOBAL['supe_uid']);
		
		if($fstatus == -1) {
			//¶Ô·½Ã»ÓÐ¼ÓºÃÓÑ£¬ÎÒ¼Ó±ðÈË
			if($status == -1) {
				
				//Ìí¼Óµ¥ÏòºÃÓÑ
				
					$setarr = array(
						'uid' => $_SGLOBAL['supe_uid'],
						'fuid' => $uid,
						'fusername' => addslashes($tospace['username']),
						'gid' => intval($_POST['gid']),
						'note' => getstr($_POST['note'], 50, 1, 1),
						'dateline' => $_SGLOBAL['timestamp'],
						'eid' => intval($_POST['eid']),
					);
					inserttable('friend', $setarr);
					
					//·¢ËÍÓÊ¼þÍ¨Öª
					//smail($uid, '', cplang('friend_subject',array($_SN[$space['uid']], getsiteurl().'cp.php?ac=friend&amp;op=request')), '', 'friend_add');

					//Ôö¼Ó¶Ô·½ºÃÓÑÉêÇëÊý
					$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET addfriendnum=addfriendnum+1 WHERE uid='$uid'");
					
					$result = array(
						'errcode' => 0,
						'errmsg' => '好友添加请求发送成功,等待对方确认'
					);
				$status1 = getfriendstatus($myId, $uid);
        			$status2 = getfriendstatus($uid, $myId);

				notification_app_add($uid, $myId, 'friendrequest', cplang('note_friend_request'));

        			writeLog("********  addfriend status-".$status1."    status2-".$status2);
        			$result['isfriendstr'] = "加为好友";
        			if ($status1 == 0 && $status2 == -1) {
            			    $result['isfriendstr'] = "等待对方同意";
        			} elseif ($status1 == -1 && $status2 == 0) {
            			    $result['isfriendstr'] = "同意加为好友";
        			}
        			$result['isfriend'] = $status1;

				echo json_encode($result);
				return;
				
			} else {
				$result = array(
						'errcode' => 3004,
						'errmsg' => '等待对方同意'
					);
                                $status1 = getfriendstatus($myId, $uid);
                                $status2 = getfriendstatus($uid, $myId);

                                writeLog("********  addfriend status-".$status1."    status2-".$status2);
                                $result['isfriendstr'] = "加为好友";
                                if ($status1 == 0 && $status2 == -1) {
                                    $result['isfriendstr'] = "等待对方同意";
                                } elseif ($status1 == -1 && $status2 == 0) {
                                    $result['isfriendstr'] = "同意加为好友";
                                }
                                $result['isfriend'] = $status1;

				echo json_encode($result);
				return;
			}
		} else {
			//¶Ô·½¼ÓÁËÎÒÎªºÃÓÑ£¬ÎÒÉóºËÍ¨¹ý
			
			
				//³ÉÎªºÃÓÑ
				$gid = intval($_POST['gid']);

				friend_update($space['uid'], $space['username'], $tospace['uid'], $tospace['username'], 'add', $gid);

				//ÊÂ¼þ·¢²¼
				//¼ÓºÃÓÑ²»·¢²¼ÊÂ¼þ
				if(ckprivacy('friend', 1)) {
					$fs = array();
					$fs['icon'] = 'friend';
	
					$fs['title_template'] = cplang('feed_friend_title');
					$fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$tospace[uid]\">".$_SN[$tospace['uid']]."</a>");
	
					$fs['body_template'] = '';
					$fs['body_data'] = array();
					$fs['body_general'] = '';

					feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general']);
				}
				
				//ÎÒµÄºÃÓÑÉêÇëÊý½øÐÐ±ä»¯
				$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET addfriendnum=addfriendnum-1 WHERE uid='$space[uid]' AND addfriendnum>0");
				
				$spaceF = getspace($_SGLOBAL['supe_uid']);
				$_SGLOBAL['supe_username'] = $spaceF['username'];
				$_SGLOBAL['supe_nickname'] = $spaceF['name'];
				//Í¨Öª
				///notification_app_add($uid, intval($_POST['eid']), 'friend', cplang('note_friend_add'));

				$result = array(
						'errcode' => 0,
						'errmsg' => '已同意TA的好友请求'
					);

                                $status1 = getfriendstatus($myId, $uid);
                                $status2 = getfriendstatus($uid, $myId);

                                writeLog("********  addfriend status-".$status1."    status2-".$status2);
                                $result['isfriendstr'] = "加为好友";
                                if ($status1 == 0 && $status2 == -1) {
                                    $result['isfriendstr'] = "等待对方同意";
                                } elseif ($status1 == -1 && $status2 == 0) {
                                    $result['isfriendstr'] = "同意加为好友";
                                }
                                $result['isfriend'] = $status1;

				sendMsg($myId, $uid, "我通过了你的好友请求，快戳右上角“我的主页”，可以添加我的游戏账号哦。");
				echo json_encode($result);
				return;
			
		}
	}
} else if ($op == 'requestfriend') {
	

	include_once(S_ROOT.'./source/function_cp.php');
	
	$myId = empty($_POST['myid'])?'':$_POST['myid'];
	$space = getspace($myId);
	
	//ºÃÓÑÇëÇó
	$perpage = 20;
	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	
	$friend1 = $space['friends'];
	$list = array();
	
	$count = getrequestcount('friend', array('fuid'=>$space['uid'], 'status'=>0));
	
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT s.*, sf.friend, f.* FROM ".tname('friend')." f
			LEFT JOIN ".tname('space')." s ON s.uid=f.uid
			LEFT JOIN ".tname('spacefield')." sf ON sf.uid=f.uid
			WHERE f.fuid='$space[uid]' AND f.status='0'
			ORDER BY f.dateline DESC
			LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
			//¹²ÓÐµÄºÃÓÑ
			$cfriend = array();
			$friend2 = empty($value['friend'])?array():explode(',',$value['friend']);
			if($friend1 && $friend2) {
				$cfriend = array_intersect($friend1, $friend2);
			}
			$value['cfriend'] = implode(',', $cfriend);
			$value['cfcount'] = count($cfriend);
			$value['isrequest'] = 1;

			$avatar_exists = ckavatar($value['uid']);
                        if ($avatar_exists ==1) {
                                $avatarfile = avatar_file($value['uid'], 'middle');
                                $value["avatarfile"] = $avatarfile;
                        }


			if ($value['uid'] != 0){// && !empty($value['username'])) {

                               $vuid = $value['uid'];
                               $queryContact = $_SGLOBAL['db']->query('SELECT qq, msn, sex, email, mobile,event,tags FROM '.tname('spacefield')." WHERE uid = '$vuid'");
                                $valueContact = $_SGLOBAL['db']->fetch_array($queryContact);
                                $value['qq'] = $valueContact['qq'];
                                $value['weixin'] = $valueContact['msn'];
                                $value['event'] = $valueContact['event'];
                                $value['tags'] = $valueContact['tags'];
				$list[] = $value;

			}
		}
	}
	
	//Í³¼Æ¸üÐÂ
	if($count != $space['addfriendnum']) {
		updatetable('space', array('addfriendnum'=>$count), array('uid'=>$space['uid']));
	}


	updatetable('notification', array('new'=>'0'), array('new'=>'1', 'uid'=>$myId, 'type'=>'friendrequest'));
	
	$result = array(
						'errcode' => 0,
						'errmsg' => '',
						'friend' => $list
					);
				echo json_encode($result);
				return;
		
	//·ÖÒ³
	/*$multi = multi($count, $perpage, $page, "cp.php?ac=friend&op=request");
	
	realname_get();*/

	
} elseif($op == 'addavatar') {

	writeLogKang("$$$$$$$$$$$$$$$$$$$$$ addavatar---->");
	writeLog("0000000000000");
	@include_once(S_ROOT.'./uc_client/client.php');
	$uid = empty($_POST['uid'])?'':$_POST['uid'];
	writeLogKang("$$$$$$$$$$$$$$$$$$$$$ addavatar----> uid:".$uid);
	list($width, $height, $type, $attr) = getimagesize($_FILES['Filedata']['tmp_name']);
	$imgtype = array(1 => '.gif', 2 => '.jpg', 3 => '.png');
	$filetype = $imgtype[$type];
	if(!$filetype) $filetype = '.jpg';
	$tmpavatar = UC_DATADIR.'./tmp/upload'.$uid.$filetype;

	file_exists($tmpavatar) && @unlink($tmpavatar);
	if(@copy($_FILES['Filedata']['tmp_name'], $tmpavatar) || @move_uploaded_file($_FILES['Filedata']['tmp_name'], $tmpavatar)) {
		@unlink($_FILES['Filedata']['tmp_name']);
		list($width, $height, $type, $attr) = getimagesize($tmpavatar);
		if($width < 10 || $height < 10 || $type == 4) {
			@unlink($tmpavatar);
		}
	} else {
		@unlink($_FILES['Filedata']['tmp_name']);
	}
	$avatarurl = UC_DATAURL.'/tmp/upload'.$uid.$filetype;

                writeLog("11111111111aaaa uid: ".$uid);


	$uidT = sprintf("%09d", $uid);
        $dir1 = substr($uidT, 0, 3);
        $dir2 = substr($uidT, 3, 2);
        $dir3 = substr($uidT, 5, 2);
        $home = $dir1.'/'.$dir2.'/'.$dir3;
	//$home = get_home($uid);
	if(!is_dir(UC_DATADIR.'./avatar/'.$home)) {

		$dir = UC_DATADIR.'./avatar/';
		$uidTT = sprintf("%09d", $uid);
                $dir1 = substr($uidTT, 0, 3);
                $dir2 = substr($uidTT, 3, 2);
                $dir3 = substr($uidTT, 5, 2);
                !is_dir($dir.'/'.$dir1) && mkdir($dir.'/'.$dir1, 0777);
                !is_dir($dir.'/'.$dir1.'/'.$dir2) && mkdir($dir.'/'.$dir1.'/'.$dir2, 0777);
                !is_dir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3) && mkdir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3, 0777);
	}
		$avatartype = 'virtual';//getgpc('avatartype', 'G') == 'real' ? 'real' : 'virtual';
                writeLog("111111111111bbbbbbbbbb");

//echo json_encode("aaaaaaaaaaaaaacccccccccccccc"); exit;

		$bigavatarfile = '/data/web/app.sihemob.com/data/'.'./avatar/'.get_avatar($uid, 'big', $avatartype);
		$middleavatarfile = '/data/web/app.sihemob.com/data/'.'./avatar/'.get_avatar($uid, 'middle', $avatartype);
		$smallavatarfile = '/data/web/app.sihemob.com/data/'.'./avatar/'.get_avatar($uid, 'small', $avatartype);
                writeLog("111111111111ccccccc file:".$middleavatarfile."*****".$uid);
		@copy($tmpavatar, $bigavatarfile);
		@copy($tmpavatar, $middleavatarfile);
		@copy($tmpavatar, $smallavatarfile);
		
		writeLogKang("$$$$$$$$$$$$$$$$$$$$$ addavatar----> middleavatarfile:".$middleavatarfile);
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET avatar='1' WHERE uid='$uid'");

                writeLog("111111111111dddddddddd");
//		$bigavatar = flashdata_decode(getgpc('avatar1', 'P'));
//		$middleavatar = flashdata_decode(getgpc('avatar2', 'P'));
//		$smallavatar = flashdata_decode(getgpc('avatar3', 'P'));
		//if(!$bigavatar || !$middleavatar || !$smallavatar) {
		//	echo json_encode(getgpc('avatar1')); exit;
		//}
		writeLog("11111111111eeee");

		$success = 1;
/*		$fp = @fopen($bigavatarfile, 'wb');
		@fwrite($fp, $bigavatar);
		@fclose($fp);

		$fp = @fopen($middleavatarfile, 'wb');
		@fwrite($fp, $middleavatar);
		@fclose($fp);

		$fp = @fopen($smallavatarfile, 'wb');
		@fwrite($fp, $smallavatar);
		@fclose($fp);
                writeLog("11111111111122222");
		$biginfo = @getimagesize($bigavatarfile);
		$middleinfo = @getimagesize($middleavatarfile);
		$smallinfo = @getimagesize($smallavatarfile);
		if(!$biginfo || !$middleinfo || !$smallinfo || $biginfo[2] == 4 || $middleinfo[2] == 4 || $smallinfo[2] == 4) {
			file_exists($bigavatarfile) && unlink($bigavatarfile);
			file_exists($middleavatarfile) && unlink($middleavatarfile);
			file_exists($smallavatarfile) && unlink($smallavatarfile);
			$success = 0;
		}
*/
		$filetype = '.jpg';
		@unlink(UC_DATADIR.'./tmp/upload'.$uid.$filetype);
                writeLog("1111111111113333333333333");
		if($success) {
			$result['photo'] = get_avatar($uid, 'middle', $avatartype);
			writeLogKang("$$$$$$$$$$$$$$$$$$$$$$$$$$".$result['photo']);

			echo json_encode($result);
		} else {
			echo json_encode("222222222222222222");
		}

} else if ($op == 'addinfo') {
	$uid = empty($_POST['uid'])?'':$_POST['uid'];
	$key = empty($_POST['key'])?'':$_POST['key'];
	$value = empty($_POST['value'])?'':$_POST['value'];
	if (empty($uid) || empty($key) || empty($value)) {
	    $result['errcode'] = 10001;
	    $result['errmsg'] = "data is empty";
	    echo json_encode($result);
	    return;
	}

	$result['errcode'] = 0;


	if ($key == "name") {
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET $key='$value' WHERE uid='$uid'");
	} elseif ($key == "location") {
            if (!empty($_POST['value'])) {
                $location = $_POST['value'];
                if (strpos($location, "&")) {
                    $locationArray = explode("&", $location);
                    $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET resideprovince='$locationArray[0]' WHERE uid='$uid'");
                    $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET residecity='$locationArray[1]' WHERE uid='$uid'");
                } else if (strpos($location, " ")) {
                    $locationArray = explode(" ", $location);
                    $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET resideprovince='$locationArray[0]' WHERE uid='$uid'");
                    $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET residecity='$locationArray[1]' WHERE uid='$uid'");
                }
            }


	} else { 
	    if ($key == "mobile") {
		if ($_POST['appversion'] < "2.3.0") {
                    $result['errcode'] = 1003;
                    $result['errmsg'] = "请升级至最新版后，再进行手机验证。";
                    echo json_encode($result);
                    return;
                }
	    }
		$_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET $key='$value' WHERE uid='$uid'");
	}
	echo json_encode($result);
}  else if ($op == 'addinfoplus') {
        $uid = empty($_POST['uid'])?'':$_POST['uid'];
        $key = empty($_POST['key'])?'':$_POST['key'];
        $value = empty($_POST['value'])?'':$_POST['value'];
        if (empty($uid)) {
            $result['errcode'] = 10001;
            $result['errmsg'] = "data is empty";
            echo json_encode($result);
            return;
        }
        $result['errcode'] = 0;

    	if (!empty($_POST['invitecode']) && !empty($_POST['appversion']) && $_POST['appversion'] < "2.2.0") {
                        $result['errcode'] = 1003;
                        $result['errmsg'] = "奖金系统升级了，请更新到最新版本才能兑换邀请码";
                        echo json_encode($result);
                        return;
    
    	}

        if (!empty($_POST['invitecode'])) {

	        $redis = getRedis();
            $code = $_POST['invitecode'];
    	    if ($redis->get("invitecode".$uid)) {
    	                $result['errcode'] = 1004;
                            $result['errmsg'] = "邀请兑换太频繁，请稍后再试！";
                            echo json_encode($result);
                            return;
    	    }
	        $redis->setex("invitecode".$uid, 40, "invitecode".$uid);
            writeLog("SELECT uid,invitecode FROM ".tname('space')." where invitecode=\"$code\" ORDER BY uid desc limit 1".$code);
            $query = $_SGLOBAL['db']->query("SELECT uid,invitecode FROM ".tname('space')." where invitecode=\"$code\"  limit 1");
            if ($value = $_SGLOBAL['db']->fetch_array($query)) {
                if ($uid == $value['uid']) {
		            $redis->del("invitecode".$uid);
                    $result['errcode'] = 1003;
                    $result['errmsg'] = "输入的邀请码有误,不能输入自己的邀请码";
                    echo json_encode($result);
                    return;
                }
		
		        $tid = $value['uid'];
                $moneylogTableNames = getMoneylogTableName(strtotime("2015-05-02"));
                $valueMoneyLog = '';
                foreach ($moneylogTableNames as $moneylogTableName)
				{
                    $queryMoneyLog = $_SGLOBAL['db']->query('SELECT moneylogid FROM `'.tname($moneylogTableName)."` WHERE moneyloguid = '$uid' AND moneyloginvite=1 limit 1");
                    $valueMoneyLog = $_SGLOBAL['db']->fetch_array($queryMoneyLog);
                    if (!empty($valueMoneyLog)) break;
				}
        		if ($valueMoneyLog) {
        		    $result['errcode'] = 1002;
                    $result['errmsg'] = "已经兑换过邀请码，不能重复兑换";
                    echo json_encode($result);
                    return;
        		}

                $space = getspace($uid);
                $invite_code = space_key($space, '');
        		if (!strstr($space['username'], "qd+")) {
        		    $result['errcode'] = 1002;
                    $result['errmsg'] = "通过QQ登录，才能领取口令红包！";
                    echo json_encode($result);
                    return;
        		}

        		$moneytypeid = 7;
        		$queryMoneyType = $_SGLOBAL['db']->query('SELECT * FROM '.tname('moneytype')." WHERE moneytypeid = '$moneytypeid'");
        		$valueMoneyType = $_SGLOBAL['db']->fetch_array($queryMoneyType);
        		$money = $valueMoneyType['moneytypemin'];
        		$index = 0;
        		$isGetChance = false;
        		writeLogDebug("******************b ->".$money);
        		while($index < $valueMoneyType['moneytypeloop']) {
            	    $random = rand(0, 100);
            	    if ($random <  $valueMoneyType['moneytypechance']) {
                	        $isGetChance = true;
                	        break;
            	    }
            	    $money = $money + $valueMoneyType['moneytypeinterval'];
            	    writeLogDebug("******************c ->".$money);
            	    $index = $index +1;
        		}
        		if (!$isGetChance) {
            	    $money = $valueMoneyType['moneytypemin'];
        		}
        		writeLogDebug("******************d ->".$money);
		        $spaceT = getspace($tid);
    		    $setarrMoneyLog = array(
        	    'moneylogamount' => $money,
		        'moneyloginvite' => 1,
        	    'moneyloguid' => $uid,
        	    'moneylogusername' => $space['name'],
        	    'moneyloguserphoto' => "http://app.sihemob.com/data/avatar/".avatar_file($uid, 'middle'),
        	    'moneylogtypeid' => $moneytypeid,
        	    'moneylogtypecategory' => 31,
        	    'moneylogstatus' => 0,
        	    'moneylogtaskid' => 0,
        	    'moneylogtid' => $tid,
        	    'moneylogtip' => "被(".$spaceT['name'].")邀请奖励",
        	    'dateline' => $_SGLOBAL['timestamp']
    		    );
    		    $moneylogid = inserttable($moneylogTableNames[0], $setarrMoneyLog, 1);
        		$setarrsMoneyA = array();
        		$setarrsMoneyA['totalmoney'] = "totalmoney=totalmoney+$money";
        		$setarrsMoneyA['realmoney'] = "realmoney=realmoney+$money";
        		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarrsMoneyA)." WHERE uid='$uid'");

		        $spaceT = getspace($tid);
                $setarrMoneyLogT = array(
                    'moneylogamount' => $money,
                    'moneyloguid' => $tid,
                    'moneylogusername' => $spaceT['name'],
                    'moneyloguserphoto' => "http://app.sihemob.com/data/avatar/".avatar_file($tid, 'middle'),
                    'moneylogtypeid' => $moneytypeid,
                    'moneylogtypecategory' => 31,
                    'moneylogstatus' => 0,
                    'moneylogtaskid' => 0,
                    'moneylogtid' => $uid,
                    'moneylogtip' => "邀请好友(".$space['name'].")",
                    'dateline' => $_SGLOBAL['timestamp']
                );
		        sendMsgByCategory(2, $tid);
                $moneylogidB = inserttable($moneylogTableNames[0], $setarrMoneyLogT, 1);
                $setarrsMoneyB = array();
                $setarrsMoneyB['totalmoney'] = "totalmoney=totalmoney+$money";
                $setarrsMoneyB['realmoney'] = "realmoney=realmoney+$money";
                $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarrsMoneyB)." WHERE uid='$tid'");
                $result['invite_status'] = 1;
                echo json_encode($result);

                /*$tmp = getrewardapp('friendinvited', 1, $uid);
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
		$result['invite_status'] = 1;*/
//                echo json_encode($result);
                //return;
           } else {
                $result['errcode'] = 1001;
                $result['errmsg'] = "输入的邀请码有误，请重新输入";
                echo json_encode($result);
                return;
            }

        }


	$checkavatar = empty($_POST['checkavatar'])?'':$_POST['checkavatar'];
	//writeLogKang($uid.">>>>>>>>>>>>> addinfoplus checkavatar: ".$checkavatar);
	if (!empty($checkavatar)) {
	    //writeLogKang($uid.">>>>>>>>>>>>> addinfoplus checkavatar: ".$checkavatar);
	    $query = $_SGLOBAL['db']->query("SELECT avatar FROM ".tname('space')." where uid=\"$uid\"  limit 1");
            if ($value = $_SGLOBAL['db']->fetch_array($query)) {
		//writeLogKang($uid.">>>>>>>>>>>>> addinfoplus value: ".$value['avatar']);
	        if ($value['avatar'] != 1) {
                    $result['errcode'] = 1008;
                    $result['errmsg'] = "请上传头像";
                    echo json_encode($result);
                    return;
		}
	    }

	}

        //writeLogKang("..................... appversion".$_POST['appversion']);
        if (!empty($_POST['mobile'])){
                if ($_POST['appversion'] < "2.3.0") {             
                    $result['errcode'] = 1003;
                    $result['errmsg'] = "请升级至最新版后，再进行手机验证。";
                    echo json_encode($result);
                    return;
                }
                $sex = $_POST['mobile'];
                $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET mobile='$sex' WHERE uid='$uid'");
        }

        if (!empty($_POST['name'])) {
		include_once(S_ROOT.'./source/emoji.php');
		$name = $_POST['name'];
$tmpStr = json_encode($name); //暴露出unicode
$tmpStr = preg_replace("#(\\\ud[0-9a-f]{3})#ie","addslashes('\\2')",$tmpStr); //将emoji的unicode留下，其他不动
$name = json_decode($tmpStr);
		$result['name'] = $name;
                $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET name='$name' WHERE uid='$uid'");
		writeLog("123456123456-".$_POST['name']."  "."UPDATE ".tname('space')." SET name='$name' WHERE uid='$uid'");
        }
	if (!empty($_POST['sex'])){
		$sex = $_POST['sex'];
		$result['sex'] = $sex;
                $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET sex='$sex' WHERE uid='$uid'");
        }

        if (!empty($_POST['event'])){
                $sex = $_POST['event'];
                $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET event='$sex' WHERE uid='$uid'");
        }

        if (!empty($_POST['tags'])){
                $sex = $_POST['tags'];
                $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET tags='$sex' WHERE uid='$uid'");
        }

        if (!empty($_POST['qq'])){
                $sex = $_POST['qq'];
                $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET qq='$sex' WHERE uid='$uid'");
        }

        if (!empty($_POST['msn'])){
                $sex = $_POST['msn'];
                $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET msn='$sex' WHERE uid='$uid'");
        }

	if (!empty($_POST['location'])) {
	    $location = $_POST['location'];
	    if (strpos($location, "&")) {
		$locationArray = explode("&", $location);
		$_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET resideprovince='$locationArray[0]' WHERE uid='$uid'");
		$_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET residecity='$locationArray[1]' WHERE uid='$uid'");
	    } else if (strpos($location, " ")) {
                $locationArray = explode(" ", $location);
                $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET resideprovince='$locationArray[0]' WHERE uid='$uid'");
                $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET residecity='$locationArray[1]' WHERE uid='$uid'");
	    }
	}

/*
        if (!empty($_POST['tags'])){
                $sex = $_POST['tags'];
                $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET tags='$sex' WHERE uid='$uid'");
        }*/



        echo json_encode($result);
}  elseif($op == 'notice') {
	$uid = empty($_POST['uid'])?'':$_POST['uid'];
	$new = empty($_POST['new'])?'':$_POST['new'];
        $list = array();
	include_once(S_ROOT.'./source/function_cp.php');
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('notification')." WHERE uid='$uid' and new=1 and type='clickpic' ORDER BY dateline DESC LIMIT 0, 100");
	if ($new==1) {
	            $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('notification')." WHERE uid='$uid' and (type='clickpic' or type='piccomment') ORDER BY dateline DESC LIMIT 0, 24");

	}
                while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                        if($value['authorid']) {
                                realname_set($value['authorid'], $value['author']);
                        }
			$avatar_exists = ckavatar($value['authorid']);
                        if ($avatar_exists ==1) {
                                $avatarfile = avatar_file($value['authorid'], 'middle');
                                $value["avatarfile"] = $avatarfile;
                        }       
			$value['username'] = $value['author'] . $value['note'];                
			//$value['note'] = "";

                              $vuid = $value['authorid'];
                               $queryContact = $_SGLOBAL['db']->query('SELECT qq, msn, sex, email, mobile,event,tags FROM '.tname('spacefield')." WHERE uid = '$vuid'");
                                $valueContact = $_SGLOBAL['db']->fetch_array($queryContact);
                                $value['qq'] = $valueContact['qq'];
                                $value['weixin'] = $valueContact['msn'];
                                $value['event'] = $valueContact['event'];
                                $value['tags'] = $valueContact['tags'];
			
			if ($value['type'] == 'clickpic') {
			    $albumid = $value['eid'];
		            $queryPic = $_SGLOBAL['db']->query("SELECT *  FROM ".tname('album')." WHERE albumid='$albumid' ORDER BY dateline DESC LIMIT 1");
        		    if ($valuePic = $_SGLOBAL['db']->fetch_array($queryPic)) {
                	        $value['albumid'] = $valuePic['albumid'];
				//$value['picid'] = $valuePic['picid'];
				$value['picpath'] = $valuePic['pic'];
        		    }

			} else if ($value['type'] == 'piccomment') {
			    $albumid = $value['eid'];
			    $queryPic = $_SGLOBAL['db']->query("SELECT *  FROM ".tname('album')." WHERE albumid='$albumid' ORDER BY dateline DESC LIMIT 1");
                            if ($valuePic = $_SGLOBAL['db']->fetch_array($queryPic)) {
                                $value['albumid'] = $valuePic['albumid'];
                                //$value['picid'] = $valuePic['picid'];
                                $value['picpath'] = $valuePic['pic'];
                            }
			}
 
                        $list[] = $value;
                }

	updatetable('notification', array('new'=>'0'), array('new'=>'1', 'uid'=>$uid, 'type'=>'friend'));
        updatetable('notification', array('new'=>'0'), array('new'=>'1', 'uid'=>$uid, 'type'=>'clickpic'));
	updatetable('notification', array('new'=>'0'), array('new'=>'1', 'uid'=>$uid, 'type'=>'piccomment'));
	$result['friend'] = $list;
	echo json_encode($result);
} elseif($op=='appupdate' || $op=='apupdate')  {
	$result['errcode'] = 0;
	$result['status'] = 0;

	$channel = $_POST['channel'];
	$result['updateurl'] = "http://dload.ququyx.com/upload/XinPlus_V2.6.0_1001_20160812.apk";
	if ($_POST['versionName'] < "2.6.0") {
		writeLog("......... update app, versionName  ".$_POST['versionName']."  channel-".$channel);
	    if ($channel == 1001 || $channel == 1002 || $channel == 1003 || $channel == 1004 || $channel == 1005
                || $channel == 1006 || $channel == 1007 || $channel == 1008 || $channel == 1009 || $channel == 1010
		|| $channel == 1010 || $channel == 1011 || $channel == 1012 || $channel == 1013 || $channel == 1014
		|| $channel == 1015 || $channel == 1016 || $channel == 1017 || $channel == 1018 || $channel == 1019
		|| $channel == 1020 || $channel == 1021) {
            	$result['status'] = 1;
		$result['updateurl'] = "http://dload.ququyx.com/upload/XinPlus_V2.6.0_".$channel."_20160812.apk";
	    }
        }

        if ($_POST['versionName'] < "2.6.0") {
                writeLog("......... update app, versionName  ".$_POST['versionName']."  channel-".$channel);
            if ($channel == 1101 ||
                $channel == 1102 || $channel == 1103 || $channel == 1104 || $channel == 1105
                || $channel == 1106 || $channel == 1107 || $channel == 1108 || $channel == 1109
                || $channel == 1110 || $channel == 1201 || $channel == 1202 || $channel == 1203
                || $channel == 1204 || $channel == 1205 || $channel == 1206 || $channel == 1207 || $channel == 1208 || $channel == 1301
                || $channel == 1302 || $channel == 1303 || $channel == 1304 || $channel == 1501 || $channel == 1502 || $channel == 1503
                || $channel == 1504 || $channel == 1505 ) {
                $result['status'] = 1;
                $result['updateurl'] = "http://dload.ququyx.com/upload/XinPlus_V2.6.0_".$channel."_20160812.apk";
            }
        }

        if ($_POST['versionName'] < "2.6.0") {
                writeLog("......... update app, versionName  ".$_POST['versionName']."  channel-".$channel);
            if ($channel == 4001 ||
                $channel == 4002 || $channel == 4003 || $channel == 4004 || $channel == 4005
                || $channel == 6001 || $channel == 6002 || $channel == 6003) {
                $result['status'] = 1;
                $result['updateurl'] = "http://dload.ququyx.com/upload/XinPlus_V2.6.0_".$channel."_20160812.apk";
            }
        }

        if ($_POST['versionName'] < "2.6.0") {
                writeLogError($result['updateurl']."........r update app, versionName  ".$_POST['versionName']."  channel-".$channel);
            if ($channel == 2001 || $channel == 2002 || $channel == 2003 || $channel == 2004 || $channel == 2005) {
                
		writeLogError("------------------");
		$result['status'] = 1;
		$result['updateurl'] = "http://dload.ququyx.com/upload/XinPlus_V2.6.0_".$channel."_20160812.apk";
            }
        }

	//writeLogError("******************************************* appupdate channel: ".$channel."===".$_POST['versionName'] < "1.0.0");

	$result['updateinfo'] = "1、新红包：分享、问卷也能领。 \n2、任务提醒：不错过限量任务。 \n3、新增专题红包，节日领不停。";
	


	writeLog("0000000000000000000000000000000000000000000000 == ".$result['status']);
	writeLogError("**************** update url:   ".$result['updateurl']);
	echo json_encode($result);	
} elseif ($op=='imlogin') {

	writeLog("imlogin -------------- start");
	$uid = empty($_POST['uid'])?'':$_POST['uid'];
	if (empty($uid)) {
	    $result['errcode'] = 10001;
            $result['errmsg'] = "uid data is empty";
            echo json_encode($result);
	    writeLog("imlogin -------------- end"); 
	    return;   
	}

        $impassword = md5(($uid."qingyijiu"));
        registerToken($uid, $impassword);

	$result['errcode'] = 0;
        $result['errmsg'] = "success";
	$result['pwd'] = $impassword;
	$result['uid'] = $uid;
        writeLog("&&&&&&&&&&&&&&& imlogin  pwd ".$impassword." uid: "+$uid);
        echo json_encode($result);
	
	fastcgi_finish_request();
	sleep(4);
        $queryContact = $_SGLOBAL['db']->query('SELECT uid,isnew FROM '.tname('space')." WHERE isnew=0 AND uid=".$uid);
        if($valueContact = $_SGLOBAL['db']->fetch_array($queryContact)) {
	    $uidb = $valueContact['uid'];
	    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET isnew=1 WHERE uid='$uidb'"); 
	    //sendMsg(98, $uidb, "欢迎来到心加！我是你的NPC小秘书，\n送你一本新手秘籍，点击下面的图片查看，\n看完后，我将会带你装逼带你︿(￣︶￣)︽(￣︶￣)︿飞.飞.飞.\n如果你还有其他疑问，记得直接和我对话哦！");
	    //sendMsgImage(98, $uidb);
	    sendMsgByCategory(1, $uidb);
	}
	writeLog("99999999999999999999999 ======== ".$valueContact['uid']);

}  else if($op == 'report') {
	
	$uid = empty($_POST['uid'])?'':$_POST['uid'];
	$space = getspace($uid);
	$reportuid = empty($_POST['reportuid'])?'':$_POST['reportuid'];
	$idtype = empty($_POST['idtype'])?'':trim($_POST['idtype']);
	
	$uidarr = $report = array();
	
	if(!in_array($idtype, array('picid', 'blogid', 'albumid', 'tagid', 'tid', 'sid', 'uid', 'pid', 'eventid', 'comment', 'post')) || empty($reportuid)) {
		$result = array(
						'errcode' => 10001,
						'errmsg' => 'report_error'
					);
				echo json_encode($result);
				return;
	}
	//获取举报记录
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('report')." WHERE id='$reportuid' AND idtype='$idtype'");
	if($report = $_SGLOBAL['db']->fetch_array($query)) {
		$uidarr = unserialize($report['uids']);
		if($uidarr[$space['uid']]) {
			$result = array(
						'errcode' => 10002,
						'errmsg' => 'repeat_report'
					);
				echo json_encode($result);
				return;
		}
	}

	$reason = getstr($_POST['reason'], 150, 1, 1);

		$reason = "<li><strong><a href=\"space.php?uid=$space[uid]\" target=\"_blank\">$_SGLOBAL[supe_username]</a>:</strong> ".$reason.' ('.sgmdate('m-d H:i').')</li>';

		if($report) {
			$uidarr[$space['uid']] = $space['username'];
			$uids = addslashes(serialize($uidarr));
			$reason = addslashes($report['reason']).$reason;
			$_SGLOBAL['db']->query("UPDATE ".tname('report')." SET num=num+1, reason='$reason', dateline='$_SGLOBAL[timestamp]', uids='$uids' WHERE rid='$report[rid]'");
		} else {
			$uidarr[$space['uid']] = $space['username'];

			$setarr = array(
				'id' => $reportuid,
				'idtype' => $idtype,
				'num' => 1,
				'new' => 1,
				'reason' => $reason,
				'uids' => addslashes(serialize($uidarr)),
				'dateline' => $_SGLOBAL['timestamp']
			);
			inserttable('report', $setarr);
		}
		$result = array(
						'errcode' => 0,
						'errmsg' => 'report_success'
					);
				echo json_encode($result);
				return;


	
} elseif($op == 'blacklist') {
	
		$uid = empty($_POST['uid'])?'':$_POST['uid'];
		$tid = empty($_POST['tid'])?'':$_POST['tid'];
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." WHERE uid='$tid'");
		if(!$tospace = $_SGLOBAL['db']->fetch_array($query)) {
			$result = array(
				'errcode' => 1001,
				'errmsg' => 'space_does_not_exist'
			);
			echo json_encode($result);
			return;
		}
		$space = getspace($uid);
		if($tospace['uid'] == $space['uid']) {
			$result = array(
				'errcode' => 1002,
				'errmsg' => 'unable_to_manage_self'
			);
			echo json_encode($result);
			return;
		}
		include_once(S_ROOT.'./source/function_cp.php');
		//删除好友
		if($space['friends'] && in_array($tospace['uid'], $space['friends'])) {
			friend_update($space['uid'], $space['name'], $tospace['uid'], '', 'ignore');
		}
		inserttable('blacklist', array('uid'=>$space['uid'], 'buid'=>$tospace['uid'], 'dateline'=>$_SGLOBAL['timestamp']), 0, true);

		$result = array(
			'errcode' => 0,
			'errmsg' => 'success'
		);
		echo json_encode($result);
	
} elseif($op == 'getsimpleinfo') {
		$uid = empty($_POST['uid'])?'':$_POST['uid'];
                //$tid = empty($_POST['tid'])?'':$_POST['tid'];
                $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." WHERE uid='$uid'");
                if(!$tospace = $_SGLOBAL['db']->fetch_array($query)) {
                        $result = array(
                                'errcode' => 1001,
                                'errmsg' => 'space_does_not_exist'
                        );
                        echo json_encode($result);
                        return;
                }


		$result = array(
                        'errcode' => 0,
                        'errmsg' => 'success',
			'name' => $tospace['name'],
			'username' => $tospace['username'],
			'uid' => $tospace['uid']
                );

include_once(S_ROOT.'./source/function_cp.php');

                        $avatar_exists = ckavatar($tospace['uid']);
                        if ($avatar_exists ==1) {
                                $avatarfile = avatar_file($tospace['uid'], 'middle');
                                $result["avatarfile"] = $avatarfile;
                        }


                echo json_encode($result);

} elseif($op == 'deleteblack') {
	$uid = empty($_POST['uid'])?'':$_POST['uid'];
	$tid = empty($_POST['tid'])?'':$_POST['tid'];
	$_SGLOBAL['db']->query("DELETE FROM ".tname('blacklist')." WHERE uid='$uid' AND buid='$tid'");
	$result = array(
		'errcode' => 0,
		'errmsg' => 'success'
	);
	echo json_encode($result);
	return;
} elseif($op == 'getinvite') {
    $space = getspace($uid);
    $invite_code = space_key($space, '');

    $result['invite'] = $invite_code;
    echo json_encode($result);

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
        //writeLogDebug("hebinbin ".$tokenResult);
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
