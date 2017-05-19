<?php
/*
 author:xuzheng 2016/7/11
 页面用来显示签到用户的未做和已做产品数量分析
 */
if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
//权限
if(!checkperm('managereport')) {
    cpmessage('no_authority_management_operation');
}
$mpurl = 'admincp.php?ac=productanalyze';
//处理搜索
$intkeys = array();
$strkeys = array();
$randkeys = array(array('sstrtotime','dateline'));
$likekeys = array();
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
$wherearr = $results['wherearr'];
$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
$mpurl .= '&'.implode('&', $results['urls']);
//显示分页
$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
if(!in_array($perpage, array(20,50,100,200,2000))) $perpage = 20;
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
$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(DISTINCT moneyloguid) FROM `".tname($moneylogTableNames[0])."` WHERE $wheresql AND moneylogtypecategory=0"),0);
if($count) {
    $query = $_SGLOBAL['db']->query("SELECT DISTINCT moneyloguid FROM `".tname($moneylogTableNames[0])."` WHERE $wheresql AND moneylogtypecategory=0 LIMIT $start,$perpage");
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        $value['counted']=$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(moneylogid) FROM `".tname($moneylogTableNames[0])."` WHERE $wheresql AND moneylogtypecategory=21 AND moneylogstep not in(2,3) AND moneyloguid=".$value['moneyloguid']),0);
        $value['uncounted']=$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(gid) FROM ".tname('creditgood')." WHERE available=1 AND (count=0 OR count > counted) AND gid not in(SELECT moneylogtaskid FROM `".tname($moneylogTableNames[0])."` WHERE moneylogtypecategory=21 AND moneylogstep not in(2,3) AND moneyloguid=".$value['moneyloguid'].")"),0);
        $list[] = $value;
    }
    $multi = multi($count, $perpage, $page, $mpurl);
}
?>