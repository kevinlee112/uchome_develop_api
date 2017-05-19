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
if ($op == "debug") {


                /*$money = 538;
                    $setarrs = array();
                    $setarrs['totalmoney'] = "totalmoney=totalmoney+$money";
                    $setarrs['realmoney'] = "realmoney=realmoney+$money";
                    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarrs)." WHERE uid='192082'");
*/
if (true) return;


    $yesdayTime = strtotime("-1 day");
    $todayTime= strtotime(date('Y-m-d',time()));
    $query = $_SGLOBAL['db']->query('select * from '.tname('moneylog')." where moneylogtypeid = 102 and dateline >  $todayTime ");
    var_dump('select * from '.tname('moneylog')." where moneylogtypeid = 14 and dateline > $yesdayTime and dateline < $todayTime ");
    $test = array();
    $testmoney = array();
    $testlogid = array();
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$uid = $value['moneyloguid'];
	if (empty($test[$uid])) {
	    $test[$uid] = 1;
	    $testmoney[$uid] = $value['moneylogamount'];
	    $testlogid[$uid] = $value['moneylogid'];
	} else {
	    $test[$uid] = $test[$uid] + 1;
	    $testmoney[$uid] = $testmoney[$uid] + $value['moneylogamount'];
	}
//	var_dump($value);
    }

$indexj = 0;
    foreach($test as $key=>$value) {
	if ($value != 2) continue;
$indexj = $indexj + 1;


	$queryContacta = $_SGLOBAL['db']->query('SELECT uid, realmoney,totalmoney FROM '.tname('space')." WHERE uid = '$key'");
    $valueContacta = $_SGLOBAL['db']->fetch_array($queryContacta);
	//if ($valueContacta['realmoney'] < $testmoney[$key]) {

$uid = $key;
        $queryContactb = $_SGLOBAL['db']->query('SELECT moneyloguid FROM '.tname('moneylog')." WHERE moneylogtypeid=102 and  moneyloguid = '$key'");
    $valueContactb = $_SGLOBAL['db']->fetch_array($queryContactb);
	//if ($valueContactb) continue;

	var_dump("index:".$indexj." uid: ".$key." count:".$value. " money:".$testmoney[$key]." totalmoney:".$valueContacta['totalmoney']." 余额：".$valueContacta['realmoney'].'   logid:'.$testlogid[$key]);

		$money = abs($testmoney[$key] / 2);
                    $setarrs = array();
                    $setarrs['totalmoney'] = "totalmoney=totalmoney+$money";
                    $setarrs['realmoney'] = "realmoney=realmoney+$money";
                    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarrs)." WHERE uid='$uid'");
                    var_dump("UPDATE ".tname('space')." SET ".implode(',', $setarrs)." WHERE uid='$uid'");
                    $_SGLOBAL['db']->query("UPDATE ".tname('creditgood')." SET counted=counted-1 WHERE gid='102'");


	$_SGLOBAL['db']->query("DELETE FROM ".tname('moneylog')." WHERE moneylogid = $testlogid[$key] ");	


/*	
	    $uid = $key;
	                    $money = -$valueContacta['realmoney'];
                    $queryContact = $_SGLOBAL['db']->query('SELECT name FROM '.tname('space')." WHERE uid = '$uid'");
                    $valueMoney = $_SGLOBAL['db']->fetch_array($queryContact);

                    $setarr = array(
                        'moneylogamount' => $money,
                        'moneylogamounttype' => 1,
                        'moneyloguid' => $uid,
                        'moneylogusername' => $valueMoney['name'],
                        'moneyloguserphoto' => "http://app.sihemob.com/data/avatar/".avatar_file($uid, 'middle'),
                        'moneylogtypeid' => 102,
                        'moneylogtypecategory' => 11,
                        'moneylogstatus' => 1,
                        'moneylogtaskid' => 0,
                        'moneylogtid' => 0,
                        'moneylogtip' => "重复签到奖励清除",
                        'dateline' => $_SGLOBAL['timestamp']
                    );
                    $moneylogid = inserttable('moneylog', $setarr, 1);
                    if ($moneylogid < 1) {
                    }
                    $setarrs = array();
                    $setarrs['totalmoney'] = "totalmoney=totalmoney+$money";
                    $setarrs['realmoney'] = "realmoney=realmoney+$money";
                    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarrs)." WHERE uid='$uid'");
		    var_dump("UPDATE ".tname('space')." SET ".implode(',', $setarrs)." WHERE uid='$uid'");
                    $_SGLOBAL['db']->query("UPDATE ".tname('creditgood')." SET counted=counted+1 WHERE gid='102'");
}
*/
    }

$phone = empty($_POST['phone'])?'':$_POST['phone'];
$code = empty($_POST['code'])?'':$_POST['code'];
// 配置项
    $currenttime = strtotime(date("H:i"));
    $result['time'] = $currenttime;
    writeLogDebug("&&&&&&&&&&&&&&&&&&&&&&&&& phone:".$phone."  code:".$code."  status:".$status);
    echo json_encode($result);
} else if ($op == "cansms"){

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
} else if($op == "getmoneybycategory") {
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

    $queryTaskLog = $_SGLOBAL['db']->query('SELECT * FROM '.tname('moneylog')." WHERE  moneylogtaskid = '$cid' and moneyloguid = '$uid' and moneylogstep < 2");
    $valueTaskLog = $_SGLOBAL['db']->fetch_array($queryTaskLog);
    if ($valueTaskLog) {
        $count = getcount('moneylog', array('moneylogtaskid'=>$cid));
        $valueMoney['tips'] = "已发放".($count*10)."份奖金";
        $valueMoney['pricetip'] = ($valueMoney['price']/100)."元";
        $valueMoney['taskimgurl'] = $valueTaskLog['moneylogtaskimgurl'];
        $valueMoney['taskstatus'] = $valueTaskLog['moneylogstatus'];
        $valueMoney['mainstatus'] = $valueTaskLog['moneylogstatus'];
    } else {
        $valueMoney['mainstatus'] = -1;
    }

    if ($valueMoney['submills'] > 0) {
        $queryTaskSub = $_SGLOBAL['db']->query('SELECT * FROM '.tname('moneylog')." WHERE  moneylogtaskid = '$cid' and moneyloguid = '$uid' and moneylogstep = 2");
        $valueTaskSub = $_SGLOBAL['db']->fetch_array($queryTaskSub);
        if ($valueTaskSub) {
            $valueMoney['substatus'] = $valueTaskSub['moneylogstatus'];
        } else {
            $valueMoney['substatus'] = -1;
        }
    }


    if ($valueMoney['thirdmills'] > 0) {
        $queryTaskThird = $_SGLOBAL['db']->query('SELECT * FROM '.tname('moneylog')." WHERE  moneylogtaskid = '$cid' and moneyloguid = '$uid' and moneylogstep = 3");
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
