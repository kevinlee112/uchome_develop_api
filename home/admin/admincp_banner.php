<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_usergroup.php 12592 2009-07-09 07:49:22Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageusergroup')) {
	cpmessage('no_authority_management_operation');
}

//取得单个数据
$thevalue = $list = array();
$_GET['bid'] = empty($_GET['bid'])?0:intval($_GET['bid']);
if($_GET['bid']) {
	$query = $_SGLOBAL['db']->query("SELECT a.*,b.firstrange,b.secondrange FROM ".tname('banner')." a LEFT JOIN ".tname('moneyrange')." b ON a.moneyrange=b.mrid WHERE bid='$_GET[bid]'");
	if(!$thevalue = $_SGLOBAL['db']->fetch_array($query)) {
		cpmessage('user_credit_good_does_not_exist');
	}
}
$rangeQuery=$_SGLOBAL['db']->query('SELECT mrid,firstrange,secondrange FROM '.tname('moneyrange').' WHERE status=1');
$rangeValue=array();
while($value=$_SGLOBAL['db']->fetch_array($rangeQuery)){
    $rangeValue[]=$value;
}
if(submitcheck('thevaluesubmit')) {
    //用户组名
    $_POST['set']['title'] = shtmlspecialchars($_POST['set']['title']);
    if(empty($_POST['set']['title'])) cpmessage('user_credit_store_does_not_exist');
    $setarr = array('title' => $_POST['set']['title']);
    if(empty($_POST['set']['thumb'])) cpmessage('user_credit_store_does_not_exist');
    $setarr['dateline'] = $_SGLOBAL['timestamp'];
    //详细权限
    $perms = array_keys($_POST['set']);
    $nones = array('bid', 'title');
    foreach ($perms as $value) {
        if(!in_array($value, $nones)) {
            $_POST['set'][$value] = trim($_POST['set'][$value]);
            if($thevalue[$value] != $_POST['set'][$value]) {
                $setarr[$value] = $_POST['set'][$value];
            }
        }
    }
	if(empty($thevalue['bid'])) {
		//添加
		inserttable('banner', $setarr);
	} else {
		//更新
		updatetable('banner', $setarr, array('bid'=>$thevalue['bid']));
	}

	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	usergroup_cache();
	cpmessage('do_success', 'admincp.php?ac=banner');
}

if(empty($_GET['op'])) {
	//浏览列表
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('banner')." ORDER BY dateline desc");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}
	$actives = array('view' => ' class="active"');	
} elseif($_GET['op'] == 'viewuser') {
    //浏览列表
    $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." as s,  ".tname('creditgoodlog')." as c ,  ".tname('spacefield')." as sf WHERE c.gid = ".$_GET['gid']." and s.uid = c.uid and s.uid = sf.uid  and lotteryfail != 1 ORDER BY c.dateline desc");
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            $listuser[] = $value;
    }
	$thevalue = 0;
    $actives = array('view' => ' class="active"');
} elseif ($_GET['op'] == 'add') {
	//添加
	$thevalue = array('bid' => 0, 'explower'=>0, 'maxattachsize'=>'10', 'maxfriendnum'=>50, 'postinterval'=>60, 'searchinterval'=>60, 'domainlength'=>0);
	//include_once(S_ROOT . "./data/data_magic.php");
} elseif ($_GET['op'] == 'edit') {
	//编辑
	include_once(S_ROOT . "./data/data_magic.php");
} elseif ($_GET['op'] == 'copy') {
	//复制
	$system = $thevalue['system'];
	$from = $thevalue['grouptitle'];
	$gid = $thevalue['gid'];
	$thevalue = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usergroup')." WHERE gid!='$gid' AND system='$system' ORDER BY explower");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$grouparr[] = $value;
	}
} elseif ($_GET['op'] == 'editavailable' && $thevalue) {
	//更新用户权限
	updatetable('banner', array('available'=>1,'dateline'=>$_SGLOBAL['timestamp']), array('bid'=>$_GET['bid']));
	cpmessage('do_success', 'admincp.php?ac=banner');
} elseif ($_GET['op'] == 'editdisavailable' && $thevalue) {
    //更新用户权限
    updatetable('banner', array('available'=>0, 'dateline'=>$_SGLOBAL['timestamp']), array('bid'=>$_GET['bid']));
    cpmessage('do_success', 'admincp.php?ac=banner');
}

function groupcredit_update() {
	global $_SGLOBAL;
	//起始为-999999999
	$lowergid = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT gid FROM ".tname('usergroup')." WHERE system='0' ORDER BY explower LIMIT 1"), 0);
	if($lowergid) updatetable('usergroup', array('explower'=>'-999999999'), array('gid'=>$lowergid));
}

?>
