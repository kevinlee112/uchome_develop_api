<?php
/*
 * Created on Dec 10, 2014
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
if(!defined('IN_UCHOME')) {
        exit('Access Denied');
}

include_once(S_ROOT.'./source/function_cp.php');

$op = empty($_POST['op'])?'':$_POST['op'];
$lat = empty($_GET['lat'])?'':$_GET['lat'];
$lng = empty($_GET['lng'])?'':$_GET['lng'];
$uid = empty($_POST['uid'])?'':$_POST['uid'];
$start = !empty($_GET['start']) ? intval($_GET['start']) : 0;
$perpage = !empty($_GET['perpage']) ? intval($_GET['perpage']) : 8;
$appversion = empty($_POST['appversion'])?'':$_POST['appversion'];
$maopao = empty($_POST['maopao'])?'':$_POST['maopao'];

$result = array(
    'errcode' => 0,
    'errmsg' => 'error'
);

if ($op == "adddoing") {
	$spaceuser = getspace($uid);
	if (empty($spaceuser)) {
		$result['errcode'] = 1001;
                $result['errmsg'] = '对不起,您的操作失败';
                echo json_encode($result);
                exit;
	}
	writeLog("a doing will add");
	$add_doing = 1;

	
		//¿¿¿¿¿¿¿¿
		/*$waittime = interval_check('post');
		if($waittime > 0) {
			showmessage('operating_too_fast', '', 1, array($waittime));
		}*/
	
	
	//¿¿¿¿
	$mood = 0;
	preg_match("/\[em\:(\d+)\:\]/s", $_POST['message'], $ms);
	$mood = empty($ms[1])?0:intval($ms[1]);

	$message = getstr($_POST['message'], 200, 1, 1, 1);
	if ($maopao == 1) {
	    $msgs=array("看我任性地冒一泡！","然而我早已机智地冒了一泡！","（伸头）有～人～吗？",
			"怒刷存在感！","人工置顶","求加求送心！","按时冒泡，有益身体健康","路过冒个泡！",
			"自顶一下，各种求加");

	    $message = $msgs[rand(0,7)];
	    writeLog("#####  adddoing appverion > 0.2.1, rand ".$message);
	} else if($maopao == 2) {
	    $msgsn=array("噔噔噔噔！新人登场。","我不就是传说中的小鲜肉吗？","新人报道，大神们好！",
                        "嘿嘿！新人请多指教～～ ","我是新人，老司机带带我！","新人登场，求脸熟！","初次见面，大家好！",
                        "新人报道，多多关照！","我是新来的，大家好哦！","我是新人，请多指教！");

            $message = $msgsn[rand(0,9)];
	}
	
	//¿¿¿¿
	$message = preg_replace("/\[em:(\d+):]/is", "<img src=\"image/face/\\1.gif\" class=\"face\">", $message);
	$message = preg_replace("/\<br.*?\>/is", ' ', $message);
	
	if(strlen($message) < 1) {
		$result['errcode'] = 1001;
		$result['errmsg'] = 'should_write_that';
		echo json_encode($result);
		exit;
	}
	
	$_SGLOBAL['supe_uid'] = $_POST['uid'];
	$space =  getspace($_SGLOBAL['supe_uid']);
	
	if($add_doing) {
		$setarr = array(
			'uid' => $_SGLOBAL['supe_uid'],
			'username' => $_SGLOBAL['supe_username'],
			'dateline' => $_SGLOBAL['timestamp'],
			'message' => $message,
			'mood' => $mood,
			'ip' => getonlineip()
		);
		//¿¿
		$newdoid = inserttable('doing', $setarr, 1);
	}
	
	//¿¿¿¿note
	$setarr = array('note'=>$message);
	$credit = $experience = 0;
	//$reward = getreward('doing', 0);
	updatetable('spacefield', $setarr, array('uid'=>$_SGLOBAL['supe_uid']));
	
	if($reward['credit']) {
		$credit = $reward['credit'];
	}
	if($reward['experience']) {
		$experience = $reward['experience'];
	}
	$setarr = array(
		'mood' => "mood='$mood'",
		'updatetime' => "updatetime='$_SGLOBAL[timestamp]'",
		'credit' => "credit=credit+$credit",
		'experience' => "experience=experience+$experience",
		'lastpost' => "lastpost='$_SGLOBAL[timestamp]'"
	);
	if($add_doing) {
		if(empty($space['doingnum'])) {//¿¿¿
			$doingnum = getcount('doing', array('uid'=>$space['uid']));
			$setarr['doingnum'] = "doingnum='$doingnum'";
		} else {
			$setarr['doingnum'] = "doingnum=doingnum+1";
		}
	}
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarr)." WHERE uid='$_SGLOBAL[supe_uid]'");
	
	//¿¿feed
	if($add_doing && ckprivacy('doing', 1)) {
		$feedarr = array(
			'appid' => UC_APPID,
			'icon' => 'doing',
			'uid' => $_SGLOBAL['supe_uid'],
			'username' => $_SGLOBAL['supe_username'],
			'dateline' => $_SGLOBAL['timestamp'],
			'title_template' => cplang('feed_doing_title'),
			'title_data' => saddslashes(serialize(sstripslashes(array('message'=>$message)))),
			'body_template' => '',
			'body_data' => '',
			'id' => $newdoid,
			'idtype' => 'doid',
                	"lat" => $lat,
                	"lng" => $lng
		);
		writeLog("add doing lat:".$lat."  lng:".$lng);
		$feedarr['hash_template'] = md5($feedarr['title_template']."\t".$feedarr['body_template']);//¿¿hash
		$feedarr['hash_data'] = md5($feedarr['title_template']."\t".$feedarr['title_data']."\t".$feedarr['body_template']."\t".$feedarr['body_data']);//¿¿hash
		inserttable('feed', $feedarr);
	}

	$result['message'] = $message;

	//¿¿
	updatestat('doing');
	
	echo json_encode($result);
	
}
?>

