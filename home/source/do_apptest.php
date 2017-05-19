<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_ajax.php 12535 2009-07-06 06:22:34Z zhengqingpeng $
*/


if(!defined('IN_UCHOME')) {
        exit('Access Denied');
}
$op = empty($_POST['op'])?'':$_POST['op'];
$lat = empty($_GET['lat'])?'':$_GET['lat'];
$lng = empty($_GET['lng'])?'':$_GET['lng'];
$myId = empty($_POST['myid'])?'':$_POST['myid'];
$isfront = empty($_POST['isfront'])?"1":$_POST["isfront"];
$result = array(
                        'errcode' => 1001,
                        'errmsg' => 'error username or password'
                );
                //header('Content-type: text/json'); 
$result = array(
                                                'errcode' => 0,
                                                'errmsg' => ''
                                        );

//echo json_encode($result);

/*if(!checkperm('manageusergroup')) {
        cpmessage('no_authority_management_operation');
}*/

if ($op == 'request') {

$today = sstrtotime(sgmdate('Y-m-d'));
        $redis = getRedis();
        //$redis->setex("getmoney"."24".$today, 3600*2, 200);
         //       var_dump($valueMoneyType['moneytypedaylimit']."|".$redis->get("getmoney"."24".$today));
        echo json_encode($result);

/*$moneytypeid = 24;
$queryMoneyType = $_SGLOBAL['db']->query('SELECT * FROM '.tname('moneytype')." WHERE moneytypeid = '$moneytypeid'");
    $valueMoneyType = $_SGLOBAL['db']->fetch_array($queryMoneyType);


	$today = sstrtotime(sgmdate('Y-m-d'));
	$redis = getRedis();
	$redis->setex("getmoney"."24".$today, 3600*2, 200);
		var_dump($valueMoneyType['moneytypedaylimit']."|".$redis->get("getmoney"."24".$today));
        echo json_encode($result);

        fastcgi_finish_request();*/
} elseif($op = 'cache') {

        include_once(S_ROOT.'./source/function_cp.php');
        include_once(S_ROOT.'./source/function_cache.php');

        //ÏµÍ³»º´æ
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

        //Ä£°å±àÒë»º´æ
               tpl_cache();

        //Ä£¿é»º´æ
                block_data_cache();

} elseif($op == 'test') {
    //echo "result: ".(6%3);
	//$index = 0
echo strtotime("last month");

//moneylogstatus
$queryLog = $_SGLOBAL['db']->query("SELECT dateline,moneyloguid,qq,deviceid, moneylogid FROM ".tname('moneylog')." where moneylogtypecategory=11 and dateline > 1456761600 and dateline <1464710100 and moneylogstatus=1 ORDER BY dateline DESC limit 10000");
while ($valueLog = $_SGLOBAL['db']->fetch_array($queryLog)) {
        $uid = $valueLog['moneyloguid'];
        $moneylogid = $valueLog['moneylogid'];
	$dateline = $valueLog['dateline'];
	$deviceId = $valueLog['deviceid'];
	$qq = $valueLog['qq'];
	$index = $index + 1;
	writeLogKang("=========================>>>>>>>>>>>>>>>>>>>>>>>".$index);
	$log = "";
                    $lastMonth = $dateline-25056002505600;
                    if (!empty($deviceId)) {
                        $queryDeviceId = $_SGLOBAL['db']->query('SELECT moneyloguid FROM '.tname('moneylog')." WHERE moneylogtypecategory=11 and deviceid = '$deviceId' and dateline > $lastMonth order by dateline asc limit 1");
                        $valueDeviceIdCount = $_SGLOBAL['db']->fetch_array($queryDeviceId);
                        if ($valueDeviceIdCount && $valueDeviceIdCount['moneyloguid'] != $uid) {
                            $_SGLOBAL['db']->query("UPDATE ".tname('moneylog')." SET devicecount =1  WHERE moneylogid='$moneylogid'");
                  	    $log = $log."uid:".$uid." logid:".$moneylogid." >>>>>> device:".$deviceId."  !=  last uid:".$valueDeviceIdCount['moneyloguid']."  #####   ";
		        }
                    }
                    //$qq = $space['qq'];
                    if (!empty($qq)) {
                        $queryQQ = $_SGLOBAL['db']->query('SELECT moneyloguid FROM '.tname('moneylog')." WHERE moneylogtypecategory=11 and  qq = '$qq' and dateline > $lastMonth order by dateline asc limit 1");
                        $valueQQ = $_SGLOBAL['db']->fetch_array($queryQQ);
                        if ($valueQQ && $valueQQ['moneyloguid'] != $uid) {

                            $_SGLOBAL['db']->query("UPDATE ".tname('moneylog')." SET qqcount =1  WHERE moneylogid='$moneylogid'");
			    $log = $log."uid:".$uid." logid:".$moneylogid." >>>>>>> qq:".$qq."  !=  last uid:".$valueQQ['moneyloguid'];
                        }
                    }

	if (!empty($log)) {
	     writeLogBack($log);
	}





}
writeLogKang("=======================".$index);


if (true) return;
$queryLog = $_SGLOBAL['db']->query("SELECT moneyloguid,qq,deviceid, moneylogid FROM ".tname('moneylog')." where moneylogtypecategory=11 and dateline > 1456761600 and qq='' ORDER BY dateline DESC limit 1000");
    $display_order = array();
    while ($valueLog = $_SGLOBAL['db']->fetch_array($queryLog)) {
            $uid = $valueLog['moneyloguid'];
	    $logid = $valueLog['moneylogid'];
	    //sleep(1);
	   $queryContact = $_SGLOBAL['db']->query('SELECT qq,deviceid FROM '.tname('spacefield')." WHERE uid = '$uid'");
    $valueMoney = $_SGLOBAL['db']->fetch_array($queryContact); 
    	    if ($valueMoney && $index<1) {
		if (!empty($valueMoney['qq'])) {
//		    $_SGLOBAL['db']->query("UPDATE ".tname('moneylog')." SET qq='$valueMoney[qq]' WHERE moneyloguid='$uid'");	
		    $display_order[$logid] = $valueMoney['qq'];
		    $index = $index + 1;
		    writeLogKang(count($display_order)."=======================".$index." | logid:".$logid."  | qq:".$valueMoney['qq']);
		}
	    } else { break; }
//	    $index = $index + 1;


	}
	$ids =  implode(',', array_keys($display_order)); 
	$sql = "UPDATE ".tname('moneylog')." SET qq = CASE moneylogid "; 
	foreach ($display_order as $id => $ordinal) { 
    	    $sql .= sprintf("WHEN %d THEN %d ", $id, $ordinal); 
	} 
	$sql .= "END WHERE moneylogid IN ($ids)"; 
	echo $sql;
	//$_SGLOBAL['db']->query($sql);
writeLogKang("+++++++++++++++++++++++++++".$index);

echo $index."========";

}



?>
