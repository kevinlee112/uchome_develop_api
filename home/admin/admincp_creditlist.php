<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_credit.php 12304 2009-06-03 07:29:34Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//х╗оч
if(!checkperm('managecredit')) {
	cpmessage('no_authority_management_operation');
}

writeLog("^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^");

$list = array();
$multi = '';
$mpurl = 'admincp.php?ac=creditlist';

if($_GET['op']=='edit') {
	
	$list = array();
	$uid = intval($_GET['uid']);
	if($uid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditlog')." a, ".tname('creditrule')." b  WHERE a.rid=b.rid and a.uid='$uid'  and a.credit > 0 order by a.dateline desc ");
		while($rule = $_SGLOBAL['db']->fetch_array($query)) {
		    $list[] = $rule;
		}
	}
	if(empty($list)) {
		cpmessage('rules_do_not_exist_points', 'admincp.php?ac=creditlist');
	}

} else {
	//$_GET['rewardtype'] = isset($_GET['rewardtype']) ? intval($_GET['rewardtype']) : 1;
	//$actives = array($_GET['rewardtype'] => ' class="active"');
	
	//$intkeys = array('rewardtype', 'cycletype');
	//$strkeys = array();
	//$randkeys = array(array('intval','credit'), array('intval', 'experience'));
	//$likekeys = array('rulename');
	//$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys, '');
	//$wherearr = $results['wherearr'];
	//$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);

	//if($_GET['rewardtype'] || $_GET['rewardtype']=='0') {
	//} else {
	//	$actives = array('-1' => ' class="active"');
	//}
	$wherearr = array();
	$wherearr[] = " credit>0 ";
	$wheresql = implode(' AND ', $wherearr);
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." WHERE $wheresql"." order by credit desc limit 50");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}

}

?>
