<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/5
 * Time: 11:53
 */

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
//权限
if(!checkperm('managetag')) {
    cpmessage('no_authority_management_operation');
}
$thevalue = $list = array();
if (!empty($_POST['formhash']) && !empty($_POST['updatesubmit']))
{
    unset($_POST['formhash'],$_POST['updatesubmit']);
    $categoryids=$_POST;
    foreach ($categoryids as $id => $sort)
    {
        updatetable('goodscategory', array('sort'=>$sort), array('categoryid'=>$id));
    }
}
if(submitcheck('thevaluesubmit')) {
    $perms = array_keys($_POST['set']);
    $nones = array('categoryid');
    foreach ($perms as $value) {
        $_POST['set'][$value] = trim($_POST['set'][$value]);
        if($thevalue[$value] != $_POST['set'][$value]) {
            $setarr[$value] = $_POST['set'][$value];
        }
    }
    foreach ($nones as $value)
    {
        !empty($_POST[$value]) && $thevalue[$value] = $_POST[$value];
    }
    if(empty($thevalue['categoryid'])) {
        //添加
        inserttable('goodscategory', $setarr);
    } else {
        //更新
        updatetable('goodscategory', $setarr, array('categoryid'=>$thevalue['categoryid']));
    }

    //更新缓存
    include_once(S_ROOT.'./source/function_cache.php');
    usergroup_cache();
    cpmessage('do_success', 'admincp.php?ac=goodscategory');
}
include_once(S_ROOT.'./source/function_cp.php');
if ($_GET['op'] == 'add') {
    //添加
    $thevalue = array(
        'categoryname' => '', 'categorytype' =>  '', 'categorychannel' =>  '', 'categoryimg' =>  '', 'goodsamount1'=> '',
        'goodsamount2' =>  '', 'goodsmonthcount1' =>  '', 'goodsmonthcount2' =>  '', 'goodscategory'=> '', 'coupontamount1'=> '',
        'coupontamount2'=> '', 'goodsrate'=> '', 'goodscommission1'=> '', 'goodscommission2'=> '', 'goodsrate1'=> '',
        'goodsrate2'=> '', 'showtype'=> '', 'shopurl'=> ''
    );
    //include_once(S_ROOT . "./data/data_magic.php");
}

elseif ($_GET['op'] == 'edit')
{
    $_GET['categoryid'] = empty($_GET['categoryid'])?0:intval($_GET['categoryid']);
    if($_GET['categoryid']) {
        $query = $_SGLOBAL['db']->query("SELECT *  FROM ".tname('goodscategory')." WHERE categoryid='$_GET[categoryid]'");
        $thevalue = $_SGLOBAL['db']->fetch_array($query);
        $query = $_SGLOBAL['db']->query("SELECT DISTINCT goodscategoryid FROM `" . tname('goodsstore') . "` WHERE 1");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            $thevalue['category'][] = $value['goodscategoryid'];
        }
    }
    //include_once(S_ROOT . "./data/data_magic.php");
}
elseif ($_GET['op'] == 'delete' && !empty($_GET['categoryid']))
{
    $query = $_SGLOBAL['db']->query("DELETE  FROM ".tname('goodscategory')." WHERE categoryid='$_GET[categoryid]'");
    cpmessage('do_success', 'admincp.php?ac=goodscategory');
}
elseif ($_GET['op'] == 'editavailable') {
    //更新用户权限
updatetable('goodscategory', array('available'=>1), array('categoryid'=>$_GET['categoryid']));
echo '11111';
cpmessage('do_success', 'admincp.php?ac=goodscategory');
} elseif ($_GET['op'] == 'editdisavailable') {
    //更新用户权限
updatetable('goodscategory', array('available'=>0), array('categoryid'=>$_GET['categoryid']));
cpmessage('do_success', 'admincp.php?ac=goodscategory');
}
else {
    $mpurl = 'admincp.php?ac=goodscategoty';
//显示分页
    $perpage = empty($_GET['perpage']) ? 0 : intval($_GET['perpage']);
    if (!in_array($perpage, array(20, 50, 100, 200))) $perpage = 20;
    $mpurl .= '&perpage=' . $perpage;
    $perpages = array($perpage => ' selected');

    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);
    if ($page < 1) $page = 1;
    $start = ($page - 1) * $perpage;
//检查开始数
    ckstart($start, $perpage);
    $managebatch = checkperm('managebatch');
    $allowbatch = true;
    $list = array();
    $multi = '';
    $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM `" . tname('goodscategory') . "` WHERE 1"), 0);
    if ($count) {
        $query = $_SGLOBAL['db']->query("SELECT * FROM `" . tname('goodscategory') . "` WHERE 1 order by available desc,sort desc LIMIT $start,$perpage");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            $list[] = $value;
        }
    }
    $multi = multi($count, $perpage, $page, $mpurl);

}