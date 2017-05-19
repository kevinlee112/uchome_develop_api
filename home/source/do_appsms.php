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

$op = empty($_POST['op'])?'':$_POST['op'];
$lat = empty($_GET['lat'])?'':$_GET['lat'];
$lng = empty($_GET['lng'])?'':$_GET['lng'];


$result = array(
    'errcode' => 0,
    'errmsg' => 'success'
);
$_SGLOBAL['supe_uid'] = $uid;
$channel = $_POST['channel'];
//writeLogDebug("--------------- do app money op:".$op."-------- uid:".$uid."------channel:".$channel);
if ($op == "smsverify") {
$phone = empty($_POST['phone'])?'':$_POST['phone'];
$code = empty($_POST['code'])?'':$_POST['code'];
// 配置项
$api = 'https://webapi.sms.mob.com';
$ostype = empty($_POST['ostype'])?'1':$_POST['ostype'];
$appkey = $ostype==2 ? "14a664ccd5d7c" : "fe0d9ac58bd4";

//$phone = "18616626972"; 
// 发送验证码
$response = postRequest($api."/sms/verify", array("appkey"=>$appkey, "phone"=>$phone,"zone"=>"86","code"=>$code));

writeLogKang("smsverify: "."ostype:".$ostype." appkey:".$appkey." uid-".$uid." phone:".$phone." code:".$code." response->".$response);
//var_dump($response);
    $response = json_decode($response, TRUE);
    $status = $response['status'];
    if ($status == 200) {
	if (!empty($phone)){
            $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET mobile='$phone' WHERE uid='$uid'");
        }
	$result['errcode'] = 0;
    } else {
	$result['errcode'] = $status;
	$result['errmsg'] = "验证短信失败，请稍后再试!";
    }
    $currenttime = strtotime(date("H:i"));
    $result['time'] = $currenttime;
    writeLogDebug("&&&&&&&&&&&&&&&&&&&&&&&&& phone:".$phone."  code:".$code."  status:".$status);
    echo json_encode($result);
} else if ($op == "cansms"){
    $phone = empty($_POST['phone'])?'':$_POST['phone'];
    if (empty($phone)) {
	                $result['errcode'] = 1001;
        $result['errmsg'] = "手机号不能为空!";
            echo json_encode($result);
    return;
    }

    /* if (startWith($_POST['channel'], "10") || startWith($_POST['channel'], "11") || startWith($_POST['channel'], "12")
	|| startWith($_POST['channel'], "20") || startWith($_POST['channel'], "40")) {

    } else {
	                        $result['errcode'] = 1001;
        $result['errmsg'] = "抱歉，因运营商故障，暂无法系统自动验证，请通过下方反馈入口提交，客服将在24小时内为您完成验证!";
            echo json_encode($result);
    return;

    }*/

    $queryContact = $_SGLOBAL['db']->query('SELECT uid, mobile,event,tags FROM '.tname('spacefield')." WHERE mobile = '$phone'");
    $valueContact = $_SGLOBAL['db']->fetch_array($queryContact);
    writeLogDebug('SELECT uid, mobile,event,tags FROM '.tname('spacefield')." WHERE mobile = '$phone'");
    if ($valueContact && $valueContact['uid'] != $uid) {
	        $result['errcode'] = 1001;
        $result['errmsg'] = "该手机号已绑定到其他账号!";


	    echo json_encode($result);
    return;
    }
    //$result['userlist'] = $userlist;
    echo json_encode($result);
    return;
} else if($op == "cache") {

include_once(S_ROOT.'./source/function_cache.php');

        //ÏµÍ³»º´æ
        /*if(empty($_POST['cachetype']) || in_array('database', $_POST['cachetype'])) {
                config_cache();
                usergroup_cache();
                profilefield_cache();
                profield_cache();
                censor_cache();
                block_cache();
                eventclass_cache();
                magic_cache();
                click_cache();
                task_cache();
                ad_cache();
                creditrule_cache();
                userapp_cache();
                app_cache();
                network_cache();
        }*/

        //Ä£°å±àÒë»º´æ
        //if(empty($_POST['cachetype']) || in_array('tpl', $_POST['cachetype'])) {
                tpl_cache();
        //}

        //Ä£¿é»º´æ
        //if(empty($_POST['cachetype']) || in_array('block', $_POST['cachetype'])) {
                block_data_cache();
        //}

        //Ëæ±ã¿´¿´»º´æ
        //if(empty($_POST['cachetype']) || in_array('network', $_POST['cachetype'])) {

                $fiels = sreaddir(S_ROOT.'./data', array('txt'));
                foreach ($fiels as $value) {
                        @unlink(S_ROOT.'./data/'.$value);
                }
        //}

	var_dump("1111111111");

} 

function startWith($str, $needle) {
    return strpos($str, $needle) === 0;

}

/**
 * 发起一个post请求到指定接口
 * 
 * @param string $api 请求的接口
 * @param array $params post参数
 * @param int $timeout 超时时间
 * @return string 请求结果
 */
function postRequest( $api, array $params = array(), $timeout = 30 ) {
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $api );
    // 以返回的形式接收信息
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    // 设置为POST方式
    curl_setopt( $ch, CURLOPT_POST, 1 );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );
    // 不验证https证书
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
    curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
        'Accept: application/json',
    ) ); 
    // 发送数据
    $response = curl_exec( $ch );
    // 不要忘记释放资源
    curl_close( $ch );
    return $response;
}


function getTaskDetail($cid, $uid) {
    global $_SGLOBAL;
    //$cid = empty($_POST['gid'])?'0':$_POST['gid'];
    $queryContact = $_SGLOBAL['db']->query('SELECT * FROM '.tname('creditgood')." WHERE  gid = '$cid'");
    $valueMoney = $_SGLOBAL['db']->fetch_array($queryContact);
    $moneylogTableNames = getMoneylogTableName(strtotime("2016-05-02"));
    $valueTaskLog = '';
    foreach ($moneylogTableNames as $moneylogTableName)
    {
        $queryTaskLog = $_SGLOBAL['db']->query('SELECT * FROM `'.tname($moneylogTableName)."` WHERE  moneylogtaskid = '$cid' and moneyloguid = '$uid' and moneylogstep < 2");
        $valueTaskLog = $_SGLOBAL['db']->fetch_array($queryTaskLog);
        if(!empty($valueTaskLog)) break;

    }
    if ($valueTaskLog) {
        $count = 0;
        foreach ($moneylogTableNames as $moneylogTableName)
        {
            $count += getcount($moneylogTableName, array('moneylogtaskid'=>$cid));
        }

        $valueMoney['tips'] = "已发放".($count*10)."份奖金";
        $valueMoney['pricetip'] = ($valueMoney['price']/100)."元";
        $valueMoney['taskimgurl'] = $valueTaskLog['moneylogtaskimgurl'];
        $valueMoney['taskstatus'] = $valueTaskLog['moneylogstatus'];
        $valueMoney['mainstatus'] = $valueTaskLog['moneylogstatus'];
    } else {
        $valueMoney['mainstatus'] = -1;
    }

    if ($valueMoney['submills'] > 0) {
        $valueTaskSub = '';
        foreach ($moneylogTableNames as $moneylogTableName)
        {
            $queryTaskSub = $_SGLOBAL['db']->query('SELECT * FROM `'.tname($moneylogTableName)."` WHERE  moneylogtaskid = '$cid' and moneyloguid = '$uid' and moneylogstep = 2");
            $valueTaskSub = $_SGLOBAL['db']->fetch_array($queryTaskSub);
            if (!empty($valueTaskSub)) break;
        }
        if ($valueTaskSub) {
            $valueMoney['substatus'] = $valueTaskSub['moneylogstatus'];
        } else {
            $valueMoney['substatus'] = -1;
        }
    }


    if ($valueMoney['thirdmills'] > 0) {
        $queryTaskThird = '';
        foreach ($moneylogTableNames as $moneylogTableName)
        {
            $queryTaskThird = $_SGLOBAL['db']->query('SELECT * FROM `'.tname($moneylogTableName)."` WHERE  moneylogtaskid = '$cid' and moneyloguid = '$uid' and moneylogstep = 3");
            $queryTaskThird = $_SGLOBAL['db']->fetch_array($queryTaskThird);
            if (!empty($queryTaskThird)) break;
        }
        $valueTaskThird = $_SGLOBAL['db']->fetch_array($queryTaskThird);
        if ($valueTaskThird) {
            $valueMoney['thirdstatus'] = $valueTaskSub['moneylogstatus'];
        } else {
            $valueMoney['thirdstatus'] = -1;
        }
    }

    $count = getcount('creditgoodlog', array('uid'=>$uid, 'gid'=>$cid));
    if ($count > 0) {
        $valueMoney['isdownload'] = 1;
    }
    $valueMoney['totalpricetip'] = "+".($valueMoney['price']/100)."元";
    //$result['detail'] = $valueMoney;
    return $valueMoney;
}

?>
