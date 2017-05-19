<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_tag.php 12568 2009-07-08 07:38:01Z zhengqingpeng $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managetag')) {
	cpmessage('no_authority_management_operation');
}
include_once(S_ROOT.'./source/function_cp.php');

//moneylog
$dateline = empty($_GET['dateline1']) ? '': strtotime($_GET['dateline1']);
if (empty($dateline))
{
	$dateline = empty($_POST['dateline3']) ? '': strtotime($_POST['dateline3']);
}
$moneylogTableNames = getMoneylogTableName($dateline, $dateline);
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
		if($allowmanage || $value['moneyloguid'] == $_SGLOBAL['supe_uid']){
			writeLogDebug("22222222222222");
			$newids[] = $value['moneylogid'];
			$opnum++;
		}
	}
	writeLogDebug("44444444444444--".$opnum);
	/*if($opnum > 1) {
		cpmessage('choose_to_delete_the_tag', $_POST['mpurl']);
	}*/
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
		        default:$message="充值成功";
		    }
			cpmessage($message, $_POST['mpurl']);
		} else {
			cpmessage('未收到上传的用户标识', $_POST['mpurl']);
		}
	}
}

$mpurl = 'admincp.php?ac=moneylogb';

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
        $wheresql ="moneylogtypeid IN (".implode(",",$elearr).")";
    }else{
        $wheresql ="moneylogtypeid NOT IN (".implode(",",$elearr).")";
    }
}

//处理搜索
$intkeys = array('moneylogtypecategory', 'moneylogstatus','moneyloguid');
$strkeys = array();
$randkeys = array(array('sstrtotime','dateline'), array('intval','blognum'));
$likekeys = array('tagname');
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
$wherearr = $results['wherearr'];
if(empty($wheresql))
{
    $wheresql = empty($wherearr)?1:implode(' AND ', $wherearr);
}else{
    $wheresql .= empty($wherearr)?'':" AND ".implode(' AND ', $wherearr);
}
$mpurl .= '&'.implode('&', $results['urls']);
$mpurl .= '&auto='.$_GET['auto'];
//排序
$orders = getorders(array('dateline'), 'moneylogid');
$ordersql = $orders['sql'];
if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
$orderby = array($_GET['orderby']=>' selected');
$ordersc = array($_GET['ordersc']=>' selected');

//显示分页
$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
if(!in_array($perpage, array(20,50,100,1000,2000))) $perpage = 20;
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

writeLogDebug("SELECT * FROM ".tname('moneylog').", `".tname($moneylogTableNames[0])."` WHERE $wheresql $ordersql LIMIT $start,$perpage");
$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(moneylogid) FROM `".tname($moneylogTableNames[0])."` WHERE $wheresql"), 0);
if($count) {
	$query = $_SGLOBAL['db']->query("SELECT moneylogid,moneylogtip,moneylogamount,moneyloguid,moneylogusername,moneylogstatus,dateline FROM `".tname($moneylogTableNames[0])."` WHERE $wheresql $ordersql LIMIT $start,$perpage");
	writeLogKang("SELECT * FROM ".tname('moneylog')." WHERE $wheresql $ordersql LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
    $queryMoneyType = $_SGLOBAL['db']->query('SELECT qq FROM '.tname('spacefield')." WHERE uid = '$value[moneyloguid]'");
    $valueMoneyType = $_SGLOBAL['db']->fetch_array($queryMoneyType);
		$value['moneylogqq'] = $valueMoneyType['qq'];
		$value['moneylogamounttip'] = $value['moneylogamount']/100;
		$list[] = $value;
		if(!$managebatch && $value['uid'] != $_SGLOBAL['supe_uid']) {
			$allowbatch = false;
		}
	}
	$multi = multi($count, $perpage, $page, $mpurl);
}

?>
