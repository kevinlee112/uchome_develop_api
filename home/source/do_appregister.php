<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_ajax.php 12535 2009-07-06 06:22:34Z zhengqingpeng $
	开启自动关闭注册功能
*/
if(!defined('IN_UCHOME')) {
    exit('Access Denied');
}
include_once(S_ROOT.'./source/function_cp.php');
$op = $_GET['op'] ? trim($_GET['op']) : '';
$_SGLOBAL['nologinform'] = 1;
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
$code = empty($_GET['code'])?'':$_GET['code'];
$app = empty($_GET['app'])?'':intval($_GET['app']);
$invite = empty($_GET['invite'])?'':$_GET['invite'];
$invitearr = array();
$deviceId = empty($_POST['deviceid'])?'':$_POST['deviceid'];
//writeZuobi("do_appregister.php start");
writeLogKang("============================ do appregitster deviceid:".$deviceId);
if($handle=fopen($_SERVER['DOCUMENT_ROOT'].'/home/isreg.txt',"r")){
    $status=fread($handle,filesize($_SERVER['DOCUMENT_ROOT'].'/home/isreg.txt'));
    fclose($handle);
}
if($status==0){
    $result['errcode']=1;
    $result['errmsg']="抱歉，注册暂时关闭，请稍后再试！";
    echo json_encode($result);
    return;
}else{
    if(empty($op)) {
    	if(!submitcheck('registersubmit')) {
    		writeLogKang("do_appregister.php   bbbbb ".S_ROOT.'./uc_client/client.php');
    		if(!@include_once S_ROOT.'./uc_client/client.php') {
    			showmessage('system_error');
    		}
    		writeLogKang("1111111111111");
    		if($_POST['password'] != $_POST['password2']) {
    			$result = array(
    				'errcode' => 20009,
    				'errmsg' => 'password_inconsistency'
    			);
    			echo json_encode($result);
    			return;
    		}
    		if(!$_POST['password'] || $_POST['password'] != addslashes($_POST['password'])) {
    			$result = array(
    				'errcode' => 20008,
    				'errmsg' => 'profile_passwd_illegal'
    			);
    			echo json_encode($result);
    			return;
    		}
    		$username = trim($_POST['username']);
    		$password = $_POST['password'];
    		$qq = trim($_POST['qq']);
    		$weixin = trim($_POST['weixin']);
            $redis = getRedis();
            writeLogKang("ccccccccc=====".($redis->get("isregister".$username.$password.$email)));
            if ($redis->get("isregister".$username.$password.$email)) {
                    writeZuobi("register error: "."isregister".$username.$password.$email);
                $result['errcode'] = 1004;
                $result['errmsg'] = "注册太频繁，请稍后再试！";
                echo json_encode($result);
                return;
            }
            $redis->setex("isregister".$username.$password.$email, 10, "isregister".$username.$password.$email);
    		$onlineip = getonlineip();
    		if($onlineip=="182.34.113.182"){
    		    return;
    		}
    		//writeLogKang("**************** register ip:".$onlineip);
    		if(!$_SCONFIG['regipdate']) {
    			$query = $_SGLOBAL['db']->query("SELECT dateline FROM ".tname('space')." WHERE regip='$onlineip' ORDER BY uid DESC LIMIT 1");
    			if($value = $_SGLOBAL['db']->fetch_array($query)) {
    				if($_SGLOBAL['timestamp'] - $value['dateline'] < $_SCONFIG['regipdate']*3600) {
    					writeLogKang("****************** ip: ".$onlineip);
    					$result = array(
    						'errcode' => 200010,
    						'errmsg' => 'regip_has_been_registered', '', 1, array($_SCONFIG['regipdate'])
    					);
    					echo json_encode($result);
    					return;
    				}
    			}
    		}
    		writeLog("do_appregister.php  cccc  uc_user_appregister password: ". $password);
    		$newuid = uc_user_appregister($username, $password, $email);
    //		writeZuobi("app register : ".$newuid);	
            writeZuobi($_SGLOBAL['timestamp']." | ".$newuid." | ".$onlineip." | ".getLog());
    		if($newuid <= 0) {
    			$errorCode = 20001;
    			$errorMsg = 'register fail';
    			if($newuid == -1) {
    				$errorCode = 20001;
    				$errorMsg = 'user_name_is_not_legitimate';
    			} elseif($newuid == -2) {
    				$errorCode = 20002;
    				$errorMsg = 'include_not_registered_words';
    			} elseif($newuid == -3) {
    				$errorCode = 20003;
    				$errorMsg = '用户已存在，请直接登录';
    			} elseif($newuid == -4) {
    				$errorCode = 20004;
    				$errorMsg = 'email_format_is_wrong';
    			} elseif($newuid == -5) {
    				$errorCode = 20005;
    				$errorMsg = 'email_not_registered';
    			} elseif($newuid == -6) {
    				$errorCode = 20006;
    				$errorMsg = 'email_has_been_registered';
    			} else {
    				$errorCode = 20020;
    				$errorMsg = 'register_error';
    			}
    			$result = array(
    				'errcode' => $errorCode,
    				'errmsg' => $errorMsg
    			);
    			if ($errorCode == 20003) {
    			    //$result = getAppSpace($username, $password);
    			}
    			echo json_encode($result);
    			return;
    		} else {
    			$setarr = array(
    				'uid' => $newuid,
    				'username' => $username,
    				'password' => md5("$newuid|$_SGLOBAL[timestamp]")//卤鸥碌脴脙脺脗毛脣忙禄煤脡煤鲁脡
    			);
    			inserttable('member', $setarr, 0, true);
    			include_once(S_ROOT.'./source/function_space.php');
    			$space = space_open($newuid, $username, 0, $email, $qq, $weixin, $deviceId);
    //			writeZuobi($_SGLOBAL['timestamp']." | ".$newuid." | ".getLog());
    			//writeZuobi("-------");
    			$setarr['errcode'] = 0;
                echo json_encode($setarr);
    			fastcgi_finish_request();
    			$flog = $inserts = $fuids = $pokes = array();
    			if(!empty($_SCONFIG['defaultfusername'])) {
    				$query = $_SGLOBAL['db']->query("SELECT uid,username FROM ".tname('space')." WHERE username IN (".simplode(explode(',', $_SCONFIG['defaultfusername'])).")");
    				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
    					$value = saddslashes($value);
    					$fuids[] = $value['uid'];
    					$inserts[] = "('$newuid','$value[uid]','$value[username]','1','$_SGLOBAL[timestamp]')";
    					$inserts[] = "('$value[uid]','$newuid','$username','1','$_SGLOBAL[timestamp]')";
    					$pokes[] = "('$newuid','$value[uid]','$value[username]','".addslashes($_SCONFIG['defaultpoke'])."','$_SGLOBAL[timestamp]')";
    					$flog[] = "('$value[uid]','$newuid','add','$_SGLOBAL[timestamp]')";
    				}
    				if($inserts) {
    					$_SGLOBAL['db']->query("REPLACE INTO ".tname('friend')." (uid,fuid,fusername,status,dateline) VALUES ".implode(',', $inserts));
    					$_SGLOBAL['db']->query("REPLACE INTO ".tname('poke')." (uid,fromuid,fromusername,note,dateline) VALUES ".implode(',', $pokes));
    					$_SGLOBAL['db']->query("REPLACE INTO ".tname('friendlog')." (uid,fuid,action,dateline) VALUES ".implode(',', $flog));
    					$friendstr = empty($fuids)?'':implode(',', $fuids);
    					updatetable('space', array('friendnum'=>count($fuids), 'pokenum'=>count($pokes)), array('uid'=>$newuid));
    					updatetable('spacefield', array('friend'=>$friendstr, 'feedfriend'=>$friendstr), array('uid'=>$newuid));
    					include_once(S_ROOT.'./source/function_cp.php');
    					foreach ($fuids as $fuid) {
    						friend_cache($fuid);
    					}
    				}
    			}
    			insertsession($setarr);
    			ssetcookie('auth', authcode("$setarr[password]\t$setarr[uid]", 'ENCODE'), 2592000);
    			ssetcookie('loginuser', $username, 31536000);
    			ssetcookie('_refer', '');
    			if($invitearr) {
    				include_once(S_ROOT.'./source/function_cp.php');
    				invite_update($invitearr['id'], $setarr['uid'], $setarr['username'], $invitearr['uid'], $invitearr['username'], $app);
    				if($invitearr['email'] == $email) {
    					updatetable('spacefield', array('emailcheck'=>1), array('uid'=>$newuid));
    				}
    				include_once(S_ROOT.'./source/function_cp.php');
    				if($app) {
    					updatestat('appinvite');
    				} else {
    					updatestat('invite');
    				}
    			}
    			if($_SCONFIG['my_status']) inserttable('userlog', array('uid'=>$newuid, 'action'=>'add', 'dateline'=>$_SGLOBAL['timestamp']), 0, true);
    			return;
    		}
    	}
    }
}/* elseif($op == "checkusername") {
	$username = trim($_GET['username']);
	if(empty($username)) {
		showmessage('user_name_is_not_legitimate');
	}
	@include_once (S_ROOT.'./uc_client/client.php');
	$ucresult = uc_user_checkname($username);
	if($ucresult == -1) {
		showmessage('user_name_is_not_legitimate');
	} elseif($ucresult == -2) {
		showmessage('include_not_registered_words');
	} elseif($ucresult == -3) {
		showmessage('user_name_already_exists');
	} else {
		showmessage('succeed');
	}
} elseif($op == "checkseccode") {
	include_once(S_ROOT.'./source/function_cp.php');
	if(ckseccode(trim($_GET['seccode']))) {
		showmessage('succeed');
	} else {
		showmessage('incorrect_code');
	}
}*/
?>
