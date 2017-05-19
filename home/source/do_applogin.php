<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_login.php 13210 2009-08-20 07:09:06Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./source/function_cp.php');
include_once(S_ROOT.'./source/function_common.php');

$refer = empty($_GET['refer'])?rawurldecode($_SCOOKIE['_refer']):$_GET['refer'];

preg_match("/(admincp|do|cp)\.php\?ac\=([a-z]+)/i", $refer, $ms);
if($ms) {
	if($ms[1] != 'cp' || $ms[2] != 'sendmail') $refer = '';
}
if(empty($refer)) {
	$refer = 'space.php?do=home';
}

//好友邀请
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
$code = empty($_GET['code'])?'':$_GET['code'];
$app = empty($_GET['app'])?'':intval($_GET['app']);
$invite = empty($_GET['invite'])?'':$_GET['invite'];
$invitearr = array();
$reward = getreward('invitecode', 0);
if($uid && $code && !$reward['credit']) {
	$m_space = getspace($uid);
	if($code == space_key($m_space, $app)) {//验证通过
		$invitearr['uid'] = $uid;
		$invitearr['username'] = $m_space['username'];
	}
	$url_plus = "uid=$uid&app=$app&code=$code";
} elseif($uid && $invite) {
	include_once(S_ROOT.'./source/function_cp.php');
	$invitearr = invite_get($uid, $invite);
	$url_plus = "uid=$uid&invite=$invite";
}

//没有登录表单
$_SGLOBAL['nologinform'] = 1;



if(!submitcheck('loginsubmit')) {

	$password = $_POST['password'];
	$username = trim($_POST['username']);
	$cookietime = intval($_POST['cookietime']);
	
	$cookiecheck = $cookietime?' checked':'';
	$membername = $username;
	
	//if(empty($_POST['username'])) {
	//	showmessage('users_were_not_empty_please_re_login', 'do.php?ac='.$_SCONFIG['login_action']);
	//}
	
	//header('Content-type: text/json');
	writeLog("............................ login   username : ".$username);	
	//同步获取用户源
	if(!$passport = getpassport($username, $password)) {
		$result = array(
		
       		    'errcode' => 1001,
                    'errmsg' => 'error username or password'
		);
		echo json_encode($result);
		//var_dump($result);
		return;
		//showmessage('login_failure_please_re_login', 'do.php?ac='.$_SCONFIG['login_action']);
	}
	
	$setarr = array(
		'uid' => $passport['uid'],
		'username' => addslashes($passport['username']),
		'password' => md5("$passport[uid]|$_SGLOBAL[timestamp]")//本地密码随机生成
	);

	/*$impassword = md5(($passport['uid']."qingyijiu"));
	registerToken($passport['uid'], $impassword);*/
	
	include_once(S_ROOT.'./source/function_space.php');
	//开通空间
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." WHERE uid='$setarr[uid]'");
	if(!$space = $_SGLOBAL['db']->fetch_array($query)) {
		$space = space_open($setarr['uid'], $setarr['username'], 0, $passport['email']);
	}
	
	$queryContact = $_SGLOBAL['db']->query('SELECT qq, msn, sex, mobile, email,event,tags FROM '.tname('spacefield')." WHERE uid = '$setarr[uid]'");
	$valueContact = $_SGLOBAL['db']->fetch_array($queryContact);

	$space['pwd'] = $impassword;
	$space['event'] = "";
	$space['tags'] = "";
	
	$space['qq'] = $valueContact['qq'];
	$space['weixin'] = $valueContact['msn'];
	$space['sex'] = $valueContact['sex'];
        $space['mobile'] = $valueContact['mobile'];
        $space['email'] = $valueContact['email'];
	$space['event'] = $valueContact['event'];
	$space['tags'] = $valueContact['tags'];
	
	$_SGLOBAL['member'] = $space;
	
	//实名
	realname_set($space['uid'], $space['username'], $space['name'], $space['namestatus']);
	
	//检索当前用户
	$query = $_SGLOBAL['db']->query("SELECT password FROM ".tname('member')." WHERE uid='$setarr[uid]'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		$setarr['password'] = addslashes($value['password']);
	} else {
		//更新本地用户库
		inserttable('member', $setarr, 0, true);
	}

	//清理在线session
	insertsession($setarr);
	
	//设置cookie
	ssetcookie('auth', authcode("$setarr[password]\t$setarr[uid]", 'ENCODE'), $cookietime);
	ssetcookie('loginuser', $passport['username'], 31536000);
	ssetcookie('_refer', '');
	
	//同步登录
	if($_SCONFIG['uc_status']) {
		include_once S_ROOT.'./uc_client/client.php';
		$ucsynlogin = uc_user_synlogin($setarr['uid']);
	} else {
		$ucsynlogin = '';
	}
	
	//好友邀请
	if($invitearr) {
		//成为好友
		invite_update($invitearr['id'], $setarr['uid'], $setarr['username'], $invitearr['uid'], $invitearr['username'], $app);
	}
	$_SGLOBAL['supe_uid'] = $space['uid'];
	//判断用户是否设置了头像
	$reward = $setarr = array();
	$experience = $credit = 0;
	$avatar_exists = ckavatar($space['uid']);
	if($avatar_exists) {
                                $avatarfile = avatar_file($space['uid'], 'middle');
                                $space["avatarfile"] = $avatarfile;
                        

		if(!$space['avatar']) {
			//奖励积分
			$reward = getreward('setavatar', 0);
			$credit = $reward['credit'];
			$experience = $reward['experience'];
			if($credit) {
				$setarr['credit'] = "credit=credit+$credit";
			}
			if($experience) {
				$setarr['experience'] = "experience=experience+$experience";
			}
			$setarr['avatar'] = 'avatar=1';
			$setarr['updatetime'] = "updatetime=$_SGLOBAL[timestamp]";
		}
	} else {
		if($space['avatar']) {
			$setarr['avatar'] = 'avatar=0';
		}
	}
	
	if($setarr) {
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarr)." WHERE uid='$space[uid]'");
	}
	
	//$arr = Array('oneww', 'two', 'three');
	$space['errcode'] = 0;
	echo json_encode($space);
	
	//var_dump($passport);
	return;
	
	//showmessage('login_success', $app?"userapp.php?id=$app":$_POST['refer'], 1, array($ucsynlogin));
}

$membername = empty($_SCOOKIE['loginuser'])?'':sstripslashes($_SCOOKIE['loginuser']);
$cookiecheck = ' checked';

//include template('do_login');






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
        //var_dump($tokenResult);
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
