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

$new = empty($_POST['new'])?'':$_POST['new'];
$result = array(
    'errcode' => 0,
    'errmsg' => 'success'
);
$_SGLOBAL['supe_uid'] = $uid;
//$spacea = getspace($uid);
//$_SGLOBAL['supe_username'] = $spacea['name'];

writeLogDebug("----------------------------------------uid:".$uid." op:".$op);
if($op == 'getcreditgoods') {
    
    $query = $_SGLOBAL['db_slave']->query("SELECT gid,title,category,chance,price,icon,count,counted,rate,available FROM ".tname('creditgood')." WHERE category<10 ORDER BY dateline DESC LIMIT 0, 100");

    $loglist = array();
    while ($value = $_SGLOBAL['db_slave']->fetch_array($query)) {
        if ($value['available'] == 1) {
	    if ($value['category'] == 0 || $value['category'] == 1) {
		$loglist[] = $value;
	    } else if ($new == 1 && $value['category'] == 2) {
		$loglist[] = $value;
	    }
        }

    }

    $result['list'] = $loglist;
    echo json_encode($result);
} elseif($op == "getgoodbyid") {
    $gid = empty($_POST['gid'])?0:intval($_POST['gid']);
    $uid = empty($_POST['uid'])?0:intval($_POST['uid']);
    $query = $_SGLOBAL['db_slave']->query("SELECT * FROM ".tname('creditgood')." WHERE gid = $gid ORDER BY dateline DESC LIMIT 1");

    $loglist = array();
    while ($value = $_SGLOBAL['db_slave']->fetch_array($query)) {
        if ($value['available'] == 1) {
	    if($value['count'] < ($value['counted'] + 1)) {
		$value['goodcode'] = 1001;
                $value['goodmsg'] = "来晚了，奖品已经被领光了";
            } else {
		    $space = getspace($uid);  
                    if($value['price'] > $space['credit']) {
                        $value['goodcode'] = 1002;
                        $value['goodmsg'] = "积分不足，快去邀请好友攒积分吧";
                    } else {
			$today = sstrtotime(sgmdate('Y-m-d'));
                        writeLog("*************** today:".$today." rate:".$value['rate']);
                        if ($value['rate'] > 0) {
                            writeLog("......................");
                            $queryRows = $_SGLOBAL['db_slave']->query("SELECT count(cglid) as count FROM ".tname('creditgoodlog')." WHERE gid = $gid and dateline > $today and uid=$uid ORDER BY dateline DESC");
                            writeLog("............22222222222..........");
                            $valueRows = $_SGLOBAL['db_slave']->fetch_array($queryRows);
                            writeLog("............33333333333333..........");
                            writeLog("=================== rate:".$value['rate']."====row:".$valueRows['count']);
                            if (($valueRows['count']+1) > $value['rate']) {
                                $value['goodcode'] = 1006;
				if($value['category'] == 0) {
				    $value['goodmsg'] = "兑换次数已达上限，明天再来吧";
				} else {$value['goodmsg'] = "抽奖次数已达上限，明天再来吧";}
                            }

                        }
		    }

	    }
            $result[] = $value;
        }

    }

    $queryRows = $_SGLOBAL['db_slave']->query("SELECT count(clid) as count FROM ".tname('creditlog')." WHERE uid = $uid and rid=4");
    writeLog("............22222222222..........");
    $valueRows = $_SGLOBAL['db_slave']->fetch_array($queryRows);
    $result['invited'] = $valueRows['count'];
    $result['invitedscore'] = round( $valueRows['count']/10 * 100 , 2) . "％";
    $result['invitedstr'] = "每成功邀请1人，爆率加成提升10%，积分+50";



    $result['errcode'] = 0;
    $result['errmsg'] = "success";
    

    echo json_encode($result);
} elseif($op == 'buygood') {
	$result['errcode'] = 1001;
	$gid = empty($_POST['gid'])?0:intval($_POST['gid']);
	$uid = empty($_POST['uid'])?0:intval($_POST['uid']);

	$space = getspace($uid);
	if (empty($space)) {
                    $result['errcode'] = 1003;
                    $result['errmsg'] = "用户不存在";
		    echo json_encode($result);
		    return;
	}

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditgood')." WHERE gid = $gid ORDER BY dateline DESC LIMIT 1");
    	if ($value = $_SGLOBAL['db']->fetch_array($query)) {
            if ($value['available'] == 1) {
		$_SGLOBAL['db']->query("UPDATE ".tname('creditgood')." SET uncounted=uncounted+1 WHERE gid='$gid'");
               	if($value['count'] < ($value['counted'] + 1)) {
		    $result['errmsg'] = "来晚了，奖品已经被领光了";
		} else {
		    if($value['price'] > $space['credit']) {
			$result['errcode'] = 1004;
                        $result['errmsg'] = "积分不足，快去邀请好友攒积分吧";
                        echo json_encode($result);
                        return;
		    }

		    $today = sstrtotime(sgmdate('Y-m-d'));
		    writeLogDebug("111111*************** today:".$today." rate:".$value['rate']);
		   
		    if ($value['rate'] > 0) {
			writeLog("......................");
		        $queryRows = $_SGLOBAL['db']->query("SELECT count(cglid) as count FROM ".tname('creditgoodlog')." WHERE gid = $gid and dateline > $today and uid = $uid ORDER BY dateline DESC");
			writeLog("............22222222222.........."); 
			$valueRows = $_SGLOBAL['db']->fetch_array($queryRows);
			writeLog("............33333333333333..........");	
			writeLog("=================== rate:".$value['rate']."====row:".$valueRows['count']);
			if (($valueRows['count']+1) > $value['rate']) {
			    $result['errcode'] = 1006;
                                if($value['category'] == 0) {
                                    $result['errmsg'] = "兑换次数已达上限，明天再来吧";
                                } elseif($value['category'] == 2) {
    				    $result['errmsg'] = '今天没机会喽，明天继续吧';                                
				} else {
				    $result['errmsg'] = '抽奖次数已达上限，明天再来吧';
				}
	
			    echo json_encode($result);
                            return;
			}

		    }		    
		    writeLogDebug("1111111111---------------------- ".$today." uid:".$space['uid']);
		    //return;
		    if ($value['category'] == 1 || $value['category']==2) {
			$tmpIndex = rand(0, 1000);
			writeLogDebug("2222222????????????????: random ".$tmpIndex."   --  chance:".$value['chance']);
			    if ($tmpIndex > $value['chance']) {


	                    $price = $value['price'];
        	            $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit-$price WHERE uid='$space[uid]'");

	                    $setarrb = array(
     	                   	'uid' => $uid,
       	                 	'gid' => $gid,
       	                	 'ginfo' => "积分消费：".$value['title'],
       	                	 'credit' => $value['price'],
       	                	 'dateline' => $_SGLOBAL['timestamp']
       	        	     );
       		             inserttable('creditlog', $setarrb);
			    

	                    $setarr = array(
        	                'uid' => $uid,
                	        'gid' => $gid,
				'lotteryfail' => 1,
                        	'dateline' => $_SGLOBAL['timestamp']
                    		);
                   	     inserttable('creditgoodlog', $setarr);


                            $result['errcode'] = 1005;
                            $result['errmsg'] = "哎呀，大奖与你擦肩而过，下次再来试试手气吧";
                            echo json_encode($result);
                            return;
			}

		    }

		    $price = $value['price'];
		    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit-$price WHERE uid='$space[uid]'");
		    $setarr = array(
                        'uid' => $uid,
                        'gid' => $gid,
                        'dateline' => $_SGLOBAL['timestamp']
                    );
                    inserttable('creditgoodlog', $setarr);

                    $setarrb = array(
                        'uid' => $uid,
                        'gid' => $gid,
			'ginfo' => "积分消费：".$value['title'],
			'credit' => $value['price'],
                        'dateline' => $_SGLOBAL['timestamp']
                    );
                    inserttable('creditlog', $setarrb);
		    $_SGLOBAL['db']->query("UPDATE ".tname('creditgood')." SET counted=counted+1 WHERE gid='$gid'");
		    $result['errcode'] = 0;
		    $result['errmsg'] = "恭喜你，兑换成功！\n 奖品将直接发放至您的QQ，请注意查收。";

		}
            } else {

	    }
        } else {

	}
        echo json_encode($result);

} elseif($op == 'invitecredit'){
    $code = empty($_POST['code'])?'':$_POST['code'];
    writeLog("SELECT uid,invitecode FROM ".tname('space')." where invitecode=\"$code\" ORDER BY uid desc limit 1".$code);
    $query = $_SGLOBAL['db']->query("SELECT uid,invitecode FROM ".tname('space')." where invitecode=\"$code\" ORDER BY uid desc limit 1");
    if ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$space = getspace($uid);
        $invite_code = space_key($space, '');
        $tmp = getrewardapp('friendinvited', 1, $uid);
	if ($tmp['credit'] == 0) {
            $result['errcode'] = 1002;
            $result['errmsg'] = "已经兑换过邀请码，不能重复兑换";
            echo json_encode($result);
            return;

	}
	writeLog("aaaaaaaaaaaaaaaaaaaaaaa");
	getrewardapp('invitefriend', 1, $value['uid']);
	writeLog("aaaaaaaaaaaaaaaaaaaaaaazzzzzzzzzzzzzzzzzzzzz");
        $result['credit'] = $space['credit'];
        $result['invite'] = $invite_code;
        $result['currentcredit'] = $tmp['credit'];
	$result['errmsg'] = "兑换成功，增长积分：".$tmp['credit'];

        echo json_encode($result);
	return;
    } else {
	$result['errcode'] = 1001;
	$result['errmsg'] = "输入的邀请码有误，请重新输入";
	echo json_encode($result);
	return;
    }
    $space = getspace($uid);
    $invite_code = space_key($space, '');
    getrewardapp('friendinvited', 1, $uid);

    $result['credit'] = $space['credit'];
    $result['invite'] = $invite_code;
    $result['currentcredit'] = 10;

    echo json_encode($result);


}elseif($op=="tasktype"){
    $gid=intval($_POST['gid']);
    $taskTypeQuery=$_SGLOBAL['db']->query("SELECT image,tasktype FROM ".tname("creditgood")." WHERE gid=".$gid);
    $taskTypeContent=$_SGLOBAL['db']->fetch_row($taskTypeQuery);
    if(!empty($taskTypeContent)){
        $result['image']=$taskTypeContent[0];
        $result['tasktype']=$taskTypeContent[1];
    }else{
        $result["errormsg"]="error";
        $result["errorcode"]="1";
    }
    echo json_encode($result);
}elseif($op=="taskcategory"){
    $gid=intval($_POST['gid']);
    if($_POST['category']==21){
        $taskCategoryQuery=$_SGLOBAL['db']->query("SELECT taskcategory FROM ".tname("creditgood")." WHERE gid=".$gid);
        $taskCategoryContent=$_SGLOBAL['db']->fetch_row($taskCategoryQuery);
        if(!empty($taskCategoryContent)){
            $result['taskcategory']=$taskCategoryContent[0];
        }else{
            $result["errormsg"]="error";
            $result["errorcode"]="1";
        }
    }else{
        $result["errormsg"]="error";
        $result["errorcode"]="2";
    }
    echo json_encode($result);
}
?>
