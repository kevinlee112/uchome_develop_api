<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_ajax.php 12535 2009-07-06 06:22:34Z zhengqingpeng $
*/

writeLogLoop("@@@@@@@@@@@@@@@@@@ apploop start");





$result = array(
			'errcode' => 0,
			'errmsg' => 'error username or password'
		);
    $list = array();
    include_once(S_ROOT.'./source/function_cp.php');
    include_once(S_ROOT.'./source/function_common.php');
      /*$tmpStr = "\ud83d\ude05 \u4f55\u658c\u658c\ud83d\ude05";
      $tmpStr = preg_replace("#(\\\ud[0-9a-f]{3})#ie","addslashes('\\2')",$tmpStr); //将emoji的unicode留下，其他不动
      $name = json_decode($tmpStr); //#(\\\ud[0-9a-f]{3})#ie
	writeLog("*****************".$tmpStr);*/





        //获取举报记录
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('report')." where  idtype='uid' order by dateline desc ");
        while($report = $_SGLOBAL['db']->fetch_array($query)) {
                $uidarr = unserialize($report['uids']);
		$reportid = $report['id'];
		writeLog("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ has report: report id->".$reportid);
		while (list($key, $val) = each($uidarr))
  		{
		    writeLog("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ has report: user id->".$key);
        	    $queryUser = $_SGLOBAL['db']->query("SELECT s.uid,s.groupid,s.username,s.name FROM ".tname('space')." s
                	LEFT JOIN ".tname('spacefield')." sf ON sf.uid=s.uid
                	WHERE s.uid='$key'");
        	    if($member = $_SGLOBAL['db']->fetch_array($queryUser)) {
			writeLog("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ has report: user id->".$key." groupid->".$member['groupid']);
		        if($member['groupid'] == 1) {
			    include_once(S_ROOT.'./source/function_delete.php');
			    $_SGLOBAL['supe_uid'] = $key;
			    if(!empty($reportid) && deletespace($reportid, 1)) {
				$_SGLOBAL['db']->query("DELETE FROM ".tname('report')." WHERE rid='$report[rid]' ");
          		        writeLog("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ delete success uid:".$key."-reportid:".$reportid);    
			    }
			}	                	
        	    }

  		}
        	//var_dump($uidarr);

	}



	$queryMsg = $_SGLOBAL['db']->query("SELECT * FROM ".tname('messagelog')." where status=0 order by dateline asc ");
        while($msg = $_SGLOBAL['db']->fetch_array($queryMsg)) {
	    $id = $msg['mlid'];
	    writeLogLoop("send msg: id|".$msg['mlid']." type|".$msg['type']." txtContent|".$msg['txtcontent']);
	    if ($msg['type'] == 0 || $msg['type'] == 1) {
		$from = $msg['from'];
		$to = explode(',',$msg['to']); 
		$txtContent = $msg['txtcontent'];
		$ext = array();
		if (!empty($msg['imgcontent'])) {
		    $ext["image"] = $msg['imgcontent'];
		}
		if (!empty($msg['action'])) {
                    $ext["action"] = $msg['action'];
                }
		$result = sendIMMsg($from, $to, $txtContent, $ext);
		$arrayTmp = $result['data'];
		$statusDetail="";
		$index = 0;
        	foreach($arrayTmp as $key => $value) {
		    if ($value != "success") {
			$index = $index + 1;
		        $statusDetail = $statusDetail . "|" . $key.":".$value;
                    }
		    writeLogLoop("from".$from." to".$to."   ".$key.":".$value);
        	}
        	writeLogLoop("from".$from." to".$to."   ".$result['data']);
	    } else {


	    }
	    if (empty($statusDetail)) {
		$statusDetail = "全部发送成功";
	    } else {
		$statusDetail = $index."个发送失败，失败者为 ".$statusDetail;
	    }
	    $_SGLOBAL['db']->query("UPDATE ".tname('messagelog')." SET status=1, statusdetail='$statusDetail' WHERE mlid='$id'");
	}

if(true) {
    return;
}





    $query = $_SGLOBAL['db']->query("SELECT uid, username FROM ".tname('space')." WHERE username like 'tb141%' ORDER BY dateline DESC limit 0,100");
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        if($value['uid'] != 0) {
	    $count = getrequestcount('friend', array('fuid'=>$value['uid'], 'status'=>0));
	    $value['friendrequest'] = $count;
            
	    if($count) {
                $queryF = $_SGLOBAL['db']->query("SELECT s.*, sf.friend, f.* FROM ".tname('friend')." f
                        LEFT JOIN ".tname('space')." s ON s.uid=f.uid
                        LEFT JOIN ".tname('spacefield')." sf ON sf.uid=f.uid
                        WHERE f.fuid='$value[uid]' AND f.status='0'
                        ORDER BY f.dateline DESC
                        LIMIT 0,100");
                while ($valueF = $_SGLOBAL['db']->fetch_array($queryF)) {
                        realname_set($value['uid'], $value['username']);
                        
                        $cfriend = array();
                        $friend2 = empty($valueF['friend'])?array():explode(',',$valueF['friend']);
                        if($friend1 && $friend2) {
                                $cfriend = array_intersect($friend1, $friend2);
                        }
                        $valueF['cfriend'] = implode(',', $cfriend);
                        $valueF['cfcount'] = count($cfriend);
                        $valueF['isrequest'] = 1;

                        if ($valueF['uid'] != 0) {
			    //$list[] = $valueF;
			    $uid = $valueF['uid'];
			    $myId = $valueF['fuid'];
			    $eid = $valueF['eid'];
			 

			    $_SGLOBAL['supe_uid'] = $myId;

			    if($uid == $_SGLOBAL['supe_uid']) {
				$result = array(
				    'errcode' => 30001,
			    	    'errmsg' => 'friend_self_error'
			        );
				$list[] = $result;
				continue;
			    }
	
			    $space = getspace($myId);
	
			    if($space['friends'] && in_array($uid, $space['friends'])) {
				$result = array(
				    'errcode' => 30002,
				    'errmsg' => 'you_have_friends'
				);
				$list[] = $result;
				continue;
			    }
	
			    $tospace = getspace($uid);
	
			    if(empty($tospace)) {
				$result = array(
				    'errcode' => 30002,
				    'errmsg' => 'space_does_not_exist'
				);
				$list[] = $result;
				continue;
			    }
	
	
			    $groups = getfriendgroup();
	
			    $status = getfriendstatus($_SGLOBAL['supe_uid'], $uid);
	
			    if($status == 1) {
				$result = array(
				    'errcode' => 30003,
			    	    'errmsg' => 'you_have_friends'
				);
				$list[] = $result;
				continue;
			    } else {
				
				$fstatus = getfriendstatus($uid, $_SGLOBAL['supe_uid']);
		
				if($fstatus == -1) {
				    if($status == -1) {
				
					$setarr = array(
						'uid' => $_SGLOBAL['supe_uid'],
						'fuid' => $uid,
						'fusername' => addslashes($tospace['username']),
						'gid' => intval($_POST['gid']),
						'note' => getstr($_POST['note'], 50, 1, 1),
						'dateline' => $_SGLOBAL['timestamp'],
						'eid' => intval($eid),
					);
					inserttable('friend', $setarr);
					
					$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET addfriendnum=addfriendnum+1 WHERE uid='$uid'");
					
					$result = array(
						'errcode' => 0,
						'errmsg' => 'request_has_been_sent'
					);
					$list[] = $result;
					break;	
				    } else {
					$result = array(
						'errcode' => 3004,
						'errmsg' => 'waiting_for_the_other_test'
					);
					$list[] = $result;
                                	continue;
				    }
				} else {
			
				    $gid = intval($_POST['gid']);

				    friend_update($space['uid'], $space['username'], $tospace['uid'], $tospace['username'], 'add', $gid);

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
				
				    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET addfriendnum=addfriendnum-1 WHERE uid='$space[uid]' AND addfriendnum>0");
				
				    $spaceF = getspace($_SGLOBAL['supe_uid']);
				    $_SGLOBAL['supe_username'] = $spaceF['username'];
				    $_SGLOBAL['supe_nickname'] = $spaceF['name'];
				    notification_app_add($uid, intval($eid), 'friend', cplang('note_friend_add'));

				    $result = array(
						'errcode' => 0,
						'errmsg' => 'request_has_been_sent'
				    );
				    $list[] = $result;
                                    continue;
			
				}
			    }
		    
			}
                }
            }

        }
    }
    echo json_encode($list);
?>
