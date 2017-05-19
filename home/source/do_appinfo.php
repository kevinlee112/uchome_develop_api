<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_ajax.php 12535 2009-07-06 06:22:34Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$op = empty($_POST['op'])?'':$_POST['op'];

$result = array(
			'errcode' => 0,
			'errmsg' => 'error username or password'
		);
		//header('Content-type: text/json'); 

writeLog("-------------- do_appim.php op is -> ".$op);

	
if($op == 'getsimpleinfo') {
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

} elseif($op == 'loadingim') {
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
