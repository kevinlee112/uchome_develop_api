<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_autoaward.php 12568 2016-06-28 17:56:01Z xuzheng $
	发奖记录页面的显示，通过连接uchome_autoaward表和uchome_moneylog表，将符合条件的聚合接口处理过的账户的详细信息，状态等显示出来
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managetag')) {
	cpmessage('no_authority_management_operation');
}
include_once(S_ROOT.'./source/function_cp.php');
$mpurl = 'admincp.php?ac=autoaward';

//处理搜索
$intkeys = array('autoawardstatus','moneyloguid');
$strkeys = array();
$randkeys = array(array('sstrtotime','dateline'));
$likekeys = array();
$pre="";
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys,$pre);
$wherearr = $results['wherearr'];
foreach($wherearr as $key=>&$value){
    if(strstr($value,$intkeys[1])){
        $value="moneylog.".$value;        
    }else{
        $value="autoaward.".$value;
    }
}
$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
$mpurl .= '&'.implode('&', $results['urls']);

//排序
$orders = getorders(array('dateline'), 'autoawardid');
$ordersql = $orders['sql'];
if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
$orderby = array($_GET['orderby']=>' selected');
$ordersc = array($_GET['ordersc']=>' selected');

//显示分页
$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
if(!in_array($perpage, array(20,50,100,200))) $perpage = 20;
$mpurl .= '&perpage='.$perpage;
$perpages = array($perpage => ' selected');

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;
//检查开始数
ckstart($start, $perpage);
$managebatch = checkperm('managebatch');
$allowbatch = true;
$list = array();
$multi = '';
$dateline = empty($_GET['dateline1']) ? '': strtotime($_GET['dateline1']);
$moneylogTableNames = getMoneylogTableName($dateline, $dateline);
writeLogDebug("SELECT COUNT(*) FROM `".tname('autoaward')."` autoaward LEFT JOIN `".tname($moneylogTableNames[0])."` moneylog ON autoaward.autoawardmid =moneylog.moneylogid $wheresql $ordersql LIMIT $start,$perpage");
$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('autoaward')." autoaward LEFT JOIN `".tname($moneylogTableNames[0])."` moneylog ON autoaward.autoawardmid =moneylog.moneylogid WHERE $wheresql"), 0);
if($count) {
    $query = $_SGLOBAL['db']->query("SELECT autoaward.autoawardid,autoaward.autoawardmid,autoaward.autoawardstatus,autoaward.dateline,moneylog.moneylogid,moneylog.moneylogtip,moneylog.moneyloguid,moneylogusername FROM ".tname('autoaward')." autoaward LEFT JOIN `".tname($moneylogTableNames[0])."` moneylog ON autoaward.autoawardmid =moneylog.moneylogid WHERE $wheresql $ordersql LIMIT $start,$perpage");
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        $queryMoneyType = $_SGLOBAL['db']->query('SELECT qq FROM '.tname('spacefield')." WHERE uid = '$value[moneyloguid]'");
        $valueMoneyType = $_SGLOBAL['db']->fetch_array($queryMoneyType);
        $value['moneylogqq'] = $valueMoneyType['qq'];
        $tiparray=explode(" ",$value['moneylogtip']);
        $value['moneylogtip']=$tiparray[1];
        $list[] = $value;
        if(!$managebatch && $value['uid'] != $_SGLOBAL['supe_uid']) {
            $allowbatch = false;
        }
    }
    $multi = multi($count, $perpage, $page, $mpurl);
}
if(empty($_GET['isAuto'])){
    if($handle=fopen(ISAUTO,"r")){
        $isauto=fread($handle,filesize(ISAUTO));
        fclose($handle);
    }
    if($isauto==1){
        $auto="close";
    }else{
        $auto="open";
    }
}else{
    if($handle=fopen(ISAUTO,"w")){
        if($_GET['isAuto']=="open"){
           fwrite($handle,1);
           $auto="close"; 
        }else{
           fwrite($handle,0);
           $auto="open";
        }
        fclose($handle);
    }
}
?>