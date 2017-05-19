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

$result = array(
    'errcode' => 0,
    'errmsg' => 'error'
);
$_SGLOBAL['supe_uid'] = $uid;
writeLogDebug("--------------- do app config uid:".$uid);
updatestat('access');
if($uid>0){
	$result['poll_minutes'] = "25";
	//uid为true，返回1
	$res = $_SGLOBAL['db_slave']->query('select s.sendheart,sf.mobile, sf.event from '.tname('spacefield').' sf, '.tname('space').' s  where s.uid=sf.uid and  s.uid='.$uid);
	$row = $_SGLOBAL['db_slave']->fetch_array($res);
	
	if($row){
		$result['phone'] = $row['mobile'];
		$result['sendheart'] = $row['sendheart'];
		$count = getcount('space', array('sendheart'=>'1'));
		$result['sendhearttips'] = $count."位玩家正在互送";
		writeLogDebug("--------------- do app config uid:".$uid."|event: ".$row['event']);		
		if(strstr($row['event'],",14,") || strstr($row['event'],",21,") || strstr($row['event'],",22,")){
			
			$result["hidedashenlist"] = 0;
			$result["errmsg"] = "success";
			echo json_encode($result);
			
		}else{
			
			$result["hidedashenlist"] = 1;
			$result["errmsg"] = '该用户无14类型数据';
			echo json_encode($result);
		}
		
	}

}else{
	$result['poll_minutes'] = "25";
	$result['sendheart'] = 0;
	//uid为空，返回0
	$result["hidedashenlist"] = 0;
	$result["errcode"] = 2;
	$result['errmsg'] = "uid为空";
	echo json_encode($result);
	
}

?>
