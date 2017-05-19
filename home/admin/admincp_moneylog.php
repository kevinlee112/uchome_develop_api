<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_tag.php 12568 2009-07-08 07:38:01Z zhengqingpeng $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$dateline = empty($_GET['dateline1']) ? '': strtotime($_GET['dateline1']);
if (empty($dateline))
{
    $dateline = empty($_POST['dateline3']) ? '': strtotime($_POST['dateline3']);
}
$moneylogTableNames = getMoneylogTableName($dateline, $dateline);
//权限
if(!checkperm('managetag')) {
	cpmessage('no_authority_management_operation');
}
include_once(S_ROOT.'./source/function_cp.php');
if(submitcheck('formhash')) {
	
	//验证是否有批量操作的权限
	$allowmanage = checkperm('managetag');
	$managebatch = checkperm('managebatch');
	$newids = array();
	$opnum = 0;
	writeLogDebug("SELECT * FROM `".tname($moneylogTableNames[0])."` WHERE moneylogid IN (".simplode($_POST['ids']).")");
	$query = $_SGLOBAL['db']->query("SELECT * FROM `".tname($moneylogTableNames[0])."` WHERE moneylogid IN (".simplode($_POST['ids']).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		writeLogDebug("11111111111");
		if($allowmanage || $value['moneyloguid'] == $_SGLOBAL['supe_uid']) {
			writeLogDebug("22222222222222");
			$newids[] = $value['moneylogid'];
			$opnum++;
		}
	}
	writeLogDebug("44444444444444--".$opnum);
	$_POST['ids'] = $newids;
	writeLogDebug("555555555---".$_POST['optype']);	
	if($_POST['optype'] == 'delete') {
		include_once(S_ROOT.'./source/function_delete.php');
		if(!empty($_POST['ids']) && deletetags($_POST['ids'])) {
			cpmessage('do_success', $_POST['mpurl']);
		} else {
			cpmessage('choose_to_delete_the_tag', $_POST['mpurl']);
		}
		
	} elseif($_POST['optype'] == 'pass' || $_POST['optype'] == 'fail') {
		include_once(S_ROOT.'./source/function_op.php');
		if(!empty($_POST['ids'])) {
		    $message=passtask($_POST['ids'], $_POST['optype'],$_POST['autohidden'], $dateline);
		    switch($message){
		        case 1:$message="权限不够";break;
		        case 2:$message="相关数据表没有找到对应操作";break;
		        case 3:$message="部分充值失败";break;
		        case 4:$message="全部充值失败";break;
		        default:$message="操作成功";
		    }
			cpmessage($message, $_POST['mpurl']);
		} else {
			cpmessage('未收到上传的用户标识', $_POST['mpurl']);
		}
	}
}

$mpurl = 'admincp.php?ac=moneylog';

//判断是否自动发奖
if(!empty($_GET['auto']))
{
    $autofile=AUTO;
    if($handle=fopen($autofile,"r"))
    {
        $autoarr=explode("&",fread($handle,filesize($autofile)));
        fclose($handle);
    }
    $elearr=array();
    foreach($autoarr as $key => $arrelement)
    {
        $element=explode('|',$arrelement);
	    $elearr[] = $element[0];
    }
    if($_GET['auto']==1)
    {
        $countsql=$wheresql ="moneylogtypeid IN (".implode(",",$elearr).")";
    }else{
        $countsql=$wheresql ="moneylogtypeid NOT IN (".implode(",",$elearr).")";
    }
    $wheresql="a.".$wheresql;
}
//处理搜索
$intkeys = array('moneylogtypecategory', 'moneylogstatus','moneyloguid',"moneylogtaskid","moneylogstep");
$strkeys = array();
$randkeys = array(array('sstrtotime','dateline'), array('intval','blognum'));
$likekeys = array('tagname');
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
$countarr=$wherearr = $results['wherearr'];
foreach($wherearr as $key => &$value){
    $value="a.".$value;
}
if(empty($wheresql))
{
    $countsql=empty($countarr)?1:implode(' AND ', $countarr);
    $wheresql=empty($wherearr)?1:implode(' AND ', $wherearr);
}else{
    $countsql.=empty($countarr)?'':" AND ".implode(' AND ', $countarr);
    $wheresql.=empty($wherearr)?'':" AND ".implode(' AND ', $wherearr);
}
$mpurl .= '&'.implode('&', $results['urls']);
$mpurl .= '&auto='.$_GET['auto'];
//排序
$orders = getorders(array('a.dateline'), 'a.moneylogid');
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
$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(moneylogid) FROM `".tname($moneylogTableNames[0])."` WHERE $countsql"), 0);
//print_r("SELECT COUNT(moneylogid) FROM `".tname($moneylogTableNames[0])."` WHERE $countsql");
if($count) {
	$query = $_SGLOBAL['db']->query("SELECT a.moneylogid,a.moneylogtaskid,a.moneylogtypecategory,a.moneylogstep,a.moneylogtaskimgurl, a.moneyloguserphoto,a.moneylogtip,a.moneylogamount,a.moneyloguid,a.moneylogusername,a.moneylogstatus,a.dateline, a.devicecount, a.qqcount,b.qq moneylogqq,b.sex,c.username FROM `".tname($moneylogTableNames[0])."` a LEFT JOIN ".tname('spacefield')." b ON a.moneyloguid=b.uid LEFT JOIN ".tname('space')." c ON b.uid=c.uid WHERE $wheresql $ordersql LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	    $maxresult=array();
	    $minresult=array();
        /* $queryMoneyType = $_SGLOBAL['db']->query('SELECT a.qq,a.sex,b.username FROM '.tname('spacefield')." AS a LEFT JOIN ".tname('space')." AS b ON a.uid=b.uid WHERE a.uid = '$value[moneyloguid]'");
        $valueMoneyType = $_SGLOBAL['db']->fetch_array($queryMoneyType); 
		$value['moneylogqq'] = $valueMoneyType['qq'];
		$value['username']=$valueMoneyType['username'];
		$value['sex']=$valueMoneyType['sex'];*/
		$value['moneylogamounttip'] = $value['moneylogamount']/100;
		if($value['moneylogtypecategory']==11){
		    $maxquery=$_SGLOBAL['db']->query("SELECT MAX(moneylogtypecategory) AS max FROM `".tname($moneylogTableNames[0])."` WHERE moneyloguid=".$value['moneyloguid']." AND moneylogtypecategory!=11 GROUP BY moneyloguid ORDER BY dateline desc LIMIT 20");
		    $maxresult=$_SGLOBAL['db']->fetch_array($maxquery);
		    $minquery=$_SGLOBAL['db']->query("SELECT MIN(moneylogtypecategory) AS min FROM `".tname($moneylogTableNames[0])."` WHERE moneyloguid=".$value['moneyloguid']." AND moneylogtypecategory!=11 GROUP BY moneyloguid ORDER BY dateline desc LIMIT 20");
		    $minresult=$_SGLOBAL['db']->fetch_array($minquery);
		    if($maxresult['max']==$minresult['min']){
		        $value['machine']=1;
		    }else{
		        $value['machine']=0;
		    }
		}else{
		    $value['machine']="";
		}
		$list[] = $value;
		if(!$managebatch && $value['uid'] != $_SGLOBAL['supe_uid']) {
			$allowbatch = false;
		}
	}
	$multi = multi($count, $perpage, $page, $mpurl);
}
?>
