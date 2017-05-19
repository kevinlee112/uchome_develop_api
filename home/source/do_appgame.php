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
if($op == 'getnewgameuser') {
    
    $queryContact = $_SGLOBAL['db']->query('SELECT qq, msn, sex, email, mobile,event,tags FROM '.tname('spacefield')." WHERE uid = '$uid'");
    $valueContact = $_SGLOBAL['db']->fetch_array($queryContact);
    $mygames = $valueContact['event'];
    $arr = explode(",",$mygames);
    $test = array();
    $whereTmp = "";
    $wherearr = array();

    $wherearr[] = " sf.uid != 0 ";
    $wherearr[] = " sf.uid != $uid ";

    $wherearrb = array();
    foreach($arr as $u){
        if ($u > 0) {
            $wherearrb[] = " sf.event like \"%," . $u . ",%\" ";
        }
    }
    $wherearr[] = " sf.uid = s.uid ";
    $wherearr[] = " s.uid != 98 ";
    if (count($wherearrb) > 0) {
        $sqlb = " ( ".implode(" OR ", $wherearrb)." ) ";
        $wherearr[] = $sqlb;
    }

    $queryGames = $_SGLOBAL['db']->query('SELECT  sf.uid,sf.event,sf.tags,s.lastlogin FROM '.tname('spacefield')." sf, ".tname('space')." s WHERE " .implode(" AND ", $wherearr) .  " order by s.lastlogin  desc limit 0,3"); //distinct sf.uid

    while($valueGame = $_SGLOBAL['db']->fetch_array($queryGames))
    {
	$value = getspace($valueGame['uid']);
	$avatar_exists = ckavatar($value['uid']);
        if ($avatar_exists ==1) {
            $avatarfile = avatar_file($value['uid'], 'middle');
            $value["avatarfile"] = $avatarfile;
        }
        $value['event'] = $valueGame['event'];
	$test[] = $value;

    }

    $result['userlist'] = $test;
    echo json_encode($result);
} elseif($op == 'getxuanyan') {
	$xuanyan=array("0x27280x27280x27280x27280x2728\n0x2728点我0x1F448头像0x2728\n0x2728天天0x1F493送心0x2728\n0x2728诚信0x1F4AF互赞0x2728\n0x2728稳定0x26A1速回0x2728\n0x2728天天0x1F388上线0x2728\n0x27280x27280x27280x27280x2728",
	    "锄禾日当午0x2600\n送心真辛苦0x1F62D\n一部小手机0x1F4F1\n一送一下午0x1F4A4\n人间有真情0x1F64F\n人间有真爱0x1F493\n点点我头像0x1F448\n和我加好友0x1F44F",
	    "秒赞不是吹0x1F60C0x1F60C0x1F60C\n带你装逼带你飞0x1F680带你秒赞到天黑0x1F60D\n谁最牛逼谁最火0x1F525除了雷锋就是我0x1F468\n吹吹牛逼败败火0x1F609小哥秒赞妥不妥0x1F64F\n",
	    "0x27280x27280x27280x27280x27280x2728\n0x2728①\t全群赞我\t(赞)0x2728\n0x2728②\t我赞全群\t(我)0x2728\n0x2728③\t先赞后回\t(=)0x2728\n0x2728④\t再赞不难\t(赞)0x2728\n0x2728⑤\t秒回是假\t(自)0x2728\n0x2728⑥\t必回是真\t(己)0x2728\n0x27280x27280x27280x27280x27280x2728",
	    "0x1F4510x1F4510x1F4510x1F451\n0x2728诚信互送0x2728\n0x2728有送必回0x2728\n0x2728长期稳定0x2728\n0x2728漏回私密0x2728\n0x2728绝无骗心0x2728\n0x1F4510x1F4510x1F4510x1F451\n");
	$index = empty($_POST['index'])? 0 : $_POST['index']; 
	$index = $index + 1;
	if ($index > 4) $index = 0;
	$result['xuanyan'] = $xuanyan[$index];
	$result['index'] = $index;
        echo json_encode($result);

} elseif($op == 'sayhello') {
    $tid = empty($_POST['tid'])?0:intval($_POST['tid']);
    $queryContact = $_SGLOBAL['db']->query('SELECT qq, msn, sex, email, mobile,event,tags FROM '.tname('spacefield')." WHERE uid = '$uid'");
    $valueContact = $_SGLOBAL['db']->fetch_array($queryContact);
    $mygames = $valueContact['event'];
    $arr = explode(",",$mygames);
    $wherearr = array();
    $game = "";

$queryTEvent = $_SGLOBAL['db']->query('SELECT qq, msn, sex, email, mobile,event,tags FROM '.tname('spacefield')." WHERE uid = '$tid'");
            $valueTEvent = $_SGLOBAL['db']->fetch_array($queryTEvent);
            $tEvent = $valueTEvent["event"];

    foreach($arr as $u){
        if ($u > 0) {
	    writeLog("......................... : ".$u);
	    writeLog("222......................... : ".$tEvent);
	    if (strstr($tEvent, ",".$u.",")) {
		$wherearr[] = " classid=".$u." ";
		writeLog("3333......................... : ".$tEvent);	
	    }

//            $wherearr[] = " event like \"%," . $u . ",%\" ";
//	     $queryEvent = $_SGLOBAL['db']->query("SELECT classid, classname FROM ".tname("eventclass") . " as ec where classid=". $u );
  //      while($valueEvent=$_SGLOBAL['db']->fetch_array($queryEvent)) {
    //            $game = $game . $valueEvent['classname'] . ",";
      //  }
        }
    }
    writeLog("444......................... : ".implode(" AND ", $wherearr));
    $queryEvent = $_SGLOBAL['db']->query("SELECT classid, classname FROM ".tname("eventclass") . " as ec WHERE " .implode(" AND ", $wherearr)  );
        while($valueEvent=$_SGLOBAL['db']->fetch_array($queryEvent)) {
                $game = $game . $valueEvent['classname'] . ",";
        }


    $game = trim($game, ",");
    $tmpIndex = rand(0, 2);
    if ($tmpIndex == 0) {
	$result['errmsg'] = "你也在玩".$game."?";
    } else if ($tmpIndex == 1) {
	$result['errmsg'] = $game."互加不?";
    } else {
	$result['errmsg'] = "hi，一起玩".$game."?";
    }
    //sendMsg($uid, $tid, "你也在玩".$game);
    //$result['errmsg'] = "你也在玩".$game;
    writeLog("&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& ".$result['errmsg']);
    echo json_encode($result);
} elseif($op == 'getAllGameUser') {
    $queryContact = $_SGLOBAL['db']->query('SELECT qq, msn, sex, email, mobile,event,tags FROM '.tname('spacefield')." WHERE uid = '$uid'");
    $valueContact = $_SGLOBAL['db']->fetch_array($queryContact);
    $mygames = $valueContact['event'];
    $arr = explode(",",$mygames);
    $test = array();
    $whereTmp = "";
    $wherearr = array();

    $wherearr[] = " sf.uid != $uid ";
        $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
        
        $dateline = empty($_POST['dateline'])?'0':$_POST['dateline'];

        if (empty($_POST['appversion'])) $isRefresh = '';
        if ($isRefresh == "yes") {
            $wherearr[] = " s.updatetime > ".$dateline;
        } else if($isRefresh == "no"){
            $wherearr[] = " s.updatetime < ".$dateline;
        }

	$sex = empty($_POST['sex'])?'':$_POST['sex'];
        if (!empty($sex)) {
                $wherearr[] = " sf.sex = " . $sex;
        }

	$wherearr[] = " sf.uid = s.uid ";
    $wherearr[] = " s.uid != 98 ";
    $wherearrb = array();
    foreach($arr as $u){
        if ($u > 0) {
            $wherearrb[] = " event like \"%," . $u . ",%\" ";
        }
    }
    $sqlb = " ( ".implode(" OR ", $wherearrb)." )";
    $wherearr[] = $sqlb;

    $queryGames = $_SGLOBAL['db']->query('SELECT  distinct sf.uid,sf.event,sf.tags,s.lastlogin FROM '.tname('spacefield')." sf, ".tname('space')." s WHERE " .implode(" AND ", $wherearr) .  " order by s.lastlogin  desc limit 24");
    while($valueGame = $_SGLOBAL['db']->fetch_array($queryGames))
    {
	$status = getfriendstatus($uid, $valueGame['uid']);
	if ($status == 1) continue;
        $value = getspace($valueGame['uid']);
        $avatar_exists = ckavatar($value['uid']);
        if ($avatar_exists ==1) {
            $avatarfile = avatar_file($value['uid'], 'middle');
            $value["avatarfile"] = $avatarfile;
        }

	$value['event'] = $valueGame['event'];
	$test[] = $value;
        /*if ($value['uid'] != 0) {
            $vuid = $value['uid'];
            $queryContact2 = $_SGLOBAL['db']->query('SELECT qq, msn, sex, email, mobile,event,tags FROM '.tname('spacefield')." WHERE uid = '$vuid'");
            $valueContact2 = $_SGLOBAL['db']->fetch_array($queryContact2);
            $value['qq'] = $valueContact2['qq'];
            $value['weixin'] = $valueContact2['msn'];
            $value['event'] = $valueContact2['event'];
            $value['tags'] = $valueContact2['tags'];
            $test[] = $value;
        }*/
    }
    $result['userlist'] = $test;
    echo json_encode($result);


} elseif($op == 'ignorefriend') {
	$space = getspace($uid);
	$_SGLOBAL['supe_uid'] = $uid;
	$_SGLOBAL['supe_username'] = $space['name'];

	$tid = empty($_POST['tid'])?0:intval($_POST['tid']);
	include_once(S_ROOT.'./source/function_cp.php');
        //对方与我的关系
        $fstatus = getfriendstatus($tid, $space['uid']);
        if($fstatus == 1) {
            //取消双向好友关系
            friend_update($_SGLOBAL['supe_uid'], $_SGLOBAL['supe_username'], $tid, '', 'ignore');
        } elseif ($fstatus == 0) {
            request_ignore($tid);
        }
	$result['errmsg'] = "解除好友成功";
	echo json_encode($result);
}

//授权注册模式 POST /{org_name}/{app_name}/users
function sendMsg($from, $to, $msg)
{
	writeLog(":::::::::::::::::::::::::::::::::".$from."::::::".$to."::::".$msg);
        $formgettoken="https://a1.easemob.com/qingyijiu/comxinplusapp/messages";
        $body=array(
                "target_type"=>"users",
                "target"=>array(
                    $to
                ),
                "msg"=>array(
                    "type"=>"txt",
                    "msg"=>$msg
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
