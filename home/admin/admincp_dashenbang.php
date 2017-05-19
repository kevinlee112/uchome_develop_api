<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_album.php 12568 2009-07-08 07:38:01Z zhengqingpeng $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!$allowmanage = checkperm('managealbum')) {
	$_GET['uid'] = $_SGLOBAL['supe_uid'];//只能操作本人的
	$_GET['username'] = '';
}

if(submitcheck('deletesubmit')) {
	include_once(S_ROOT.'./source/function_delete.php');
	if(!empty($_POST['ids']) && deletealbums($_POST['ids'])) {
		cpmessage('do_success', $_POST['mpurl']);
	} else {
		cpmessage('at_least_one_option_to_delete_albums', $_POST['mpurl']);
	}
}

$mpurl = 'admincp.php?ac=album';

//处理搜索
$intkeys = array('uid', 'friend', 'albumid','sid','classid');
$strkeys = array('username');
$randkeys = array(array('sstrtotime','dateline'));
$likekeys = array('albumname');
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
$wherearr = $results['wherearr'];

$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
$mpurl .= '&'.implode('&', $results['urls']);

//排序
$orders = getorders(array('dateline', 'updatetime', 'picnum'), 'albumid');
$ordersql = $orders['sql'];
if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
$orderby = array($_GET['orderby']=>' selected');
$ordersc = array($_GET['ordersc']=>' selected');

//显示分页
$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
if(!in_array($perpage, array(20,50,100,1000))) $perpage = 20;

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;
//检查开始数
ckstart($start, $perpage);

//显示分页
if($perpage > 100) {
	$count = 1;
	$selectsql = 'albumid';
} else {
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('album')." WHERE $wheresql"), 0);
	$selectsql = '*';
}
$mpurl .= '&perpage='.$perpage;
$perpages = array($perpage => ' selected');
$managebatch = checkperm('managebatch');
$allowbatch = true;
$list = array();
$multi = '';

if($count) {

    $sqlRank = "select a.albumid,a.uid,a.picid,a.hot,(@i:=@i+1) as i from(select  a.albumid,a.uid,p.picid,p.hot from uchome_album a, uchome_pic p where a.albumid=p.albumid and a.albumname != 'photoalbumsystem' AND  a.classid=14 group by a.albumid order by p.hot desc) a, (select @i:=0) as it";
    $queryRank = $_SGLOBAL['db']->query($sqlRank);
    while($rank = $_SGLOBAL['db']->fetch_array($queryRank)) {
        $index =  $rank['i'];
        $hot = $rank['hot'];
        $albumid = $rank['albumid'];
        $_SGLOBAL['db']->query("UPDATE ".tname('album')." SET preweekindex=$index, preweekhot=$hot WHERE albumid=$albumid");

    }



	//$query = $_SGLOBAL['db']->query("SELECT $selectsql FROM ".tname('album')." WHERE $wheresql $ordersql LIMIT $start,$perpage");
	$query = $_SGLOBAL['db']->query("select * from uchome_album a, uchome_space s where a.uid=s.uid and a.preweekhot>0 and a.classid=14 group by a.uid order by a.preweekhot desc limit 10");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
		if(!$managebatch && $value['uid'] != $_SGLOBAL['supe_uid']) {
			$allowbatch = false;
		}

                if ($value['sid'] > 0) {
                        $sid = $value['sid'];
                        $querySub = $_SGLOBAL['db']->query("SELECT title FROM ".tname('subject')." WHERE sid='$sid'");
                        while($valueSub = $_SGLOBAL['db']->fetch_array($querySub)) {
                                $value['sname'] = $valueSub['title'];
                        }

                }


		$list[] = $value;
	}
	$multi = multi($count, $perpage, $page, $mpurl);
}

//显示分页
if($perpage > 100) {
	$count = count($list);
}

?>
