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
if(submitcheck('thevaluesubmit')) {
    $perms = array_keys($_POST['set']);
    $nones = array('id');
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
    if(empty($thevalue['id'])) {
        //添加
        inserttable('goodsplatform', $setarr);
    } else {
        //更新
        updatetable('goodsplatform', $setarr, array('id'=>$thevalue['id']));
    }

    //更新缓存
    include_once(S_ROOT.'./source/function_cache.php');
    usergroup_cache();
    cpmessage('do_success', 'admincp.php?ac=goodsplatform');
}
include_once(S_ROOT.'./source/function_cp.php');
if ($_GET['op'] == 'add') {
    //添加
    $thevalue = array(
        'platformname' => '', 'returnrate' => '', 'returnurl' => '', 'params'=>'','paramsurl'=>'',
    );
    //include_once(S_ROOT . "./data/data_magic.php");
}

elseif ($_GET['op'] == 'edit')
{
    $_GET['id'] = empty($_GET['id'])?0:intval($_GET['id']);
    if($_GET['id']) {
        $query = $_SGLOBAL['db']->query("SELECT *  FROM ".tname('goodsplatform')." WHERE id='$_GET[id]'");
        if(!$thevalue = $_SGLOBAL['db']->fetch_array($query)) {
            cpmessage('user_credit_good_does_not_exist');
        }
    }
    //include_once(S_ROOT . "./data/data_magic.php");
}
else {
    $mpurl = 'admincp.php?ac=goodsplateform';
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
    $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM `" . tname('goodsplatform') . "` WHERE 1"), 0);
    if ($count) {
        $query = $_SGLOBAL['db']->query("SELECT * FROM `" . tname('goodsplatform') . "` WHERE 1 LIMIT $start,$perpage");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            $list[] = $value;
        }
    }
    $multi = multi($count, $perpage, $page, $mpurl);

}