<?php
/*
 author:xuzheng 2017/1/06
 页面用来显示金额限制范围
 */
if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
//权限
if(!checkperm('managereport')) {
    cpmessage('no_authority_management_operation');
}
//取得单个数据
$thevalue = $list = array();
if($_GET['mrid']) {
    $query = $_SGLOBAL['db']->query("SELECT mrid,firstrange,secondrange FROM ".tname('moneyrange')." WHERE mrid='$_GET[mrid]'");
    if(!$thevalue = $_SGLOBAL['db']->fetch_array($query)) {
        cpmessage('user_credit_good_does_not_exist');
    }
}
if(submitcheck('addMoneysubmit')){
    if(!empty($_POST['firstrange'])&&!empty($_POST['secondrange'])){
        if(!empty($_POST['mrid'])){
            $thevalue['mrid']=$_POST['mrid']; 
        }
        $thevalue['firstrange']=intval($_POST['firstrange']);
        $thevalue['secondrange']=intval($_POST['secondrange']);
        if(empty($thevalue['mrid'])) {
            //添加
            inserttable('moneyrange', $thevalue);
        } else {
            //更新
            updatetable('moneyrange', $thevalue, array('mrid'=>$thevalue['mrid']));
        }
    }else{
        cpmessage('请填写添加的金额范围！',$_POST['mpurl']);
    }
}
$mpurl = 'admincp.php?ac=moneyrange';
if(!empty($_GET['op'])){
    include_once template("admin/tpl/".$_GET['ac']);
}else{
    if(submitcheck('formhash')) {
        //验证是否有批量操作的权限
        if($_POST['optype'] == 'lock') {
            if(!empty($_POST['ids'])){
                $updatequery=$_SGLOBAL['db']->query("UPDATE ".tname('moneyrange')." SET status=2 WHERE mrid IN(".simplode($_POST['ids']).")");
                if(!empty($updatequery)){
                    cpmessage('do_success', $_POST['mpurl']);
                }else{
                    cpmessage('锁定失败', $_POST['mpurl']);
                }
            }
        } elseif($_POST['optype'] == 'unlock') {
            if(!empty($_POST['ids'])){
                $updatequery=$_SGLOBAL['db']->query("UPDATE ".tname('moneyrange')." SET status=1 WHERE mrid IN(".simplode($_POST['ids']).")");
                if(!empty($updatequery)){
                    cpmessage('do_success', $_POST['mpurl']);
                }else{
                    cpmessage('解锁失败', $_POST['mpurl']);
                }
            }
        }
    }
    //处理搜索
    $intkeys = array('mrid', 'status');
    $strkeys = array();
    $randkeys = array();
    $likekeys = array();
    $results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
    $wherearr = $results['wherearr'];
    $wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
    $mpurl .= '&'.implode('&', $results['urls']);
    
    //排序
    $orders = getorders('mrid');
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
    $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(mrid) FROM ".tname('moneyrange')." WHERE $wheresql"), 0);
    if($count) {
        $query = $_SGLOBAL['db']->query("SELECT mrid,firstrange,secondrange,status FROM ".tname('moneyrange')." WHERE $wheresql");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            $list[] = $value;
        }
        $multi = multi($count, $perpage, $page, $mpurl);
    }
}
?>