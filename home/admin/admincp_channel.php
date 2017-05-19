<?php
/*
 author:xuzheng 2016/7/11
 页面用来显示渠道
 */
if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
//权限
if(!checkperm('managereport')) {
    cpmessage('no_authority_management_operation');
}
if(submitcheck('addchannelsubmit')){
    if(!empty($_POST['channelname'])){
        $_POST['channelname']=htmlspecialchars($_POST['channelname'],ENT_QUOTES);
        $addquery=$_SGLOBAL['db']->query("INSERT INTO ".tname('channel')."(channelname,channelstatus,dateline) VALUES ('".$_POST['channelname']."',1,'".time()."')");
        if($addquery){
            cpmessage('添加渠道成功！',$_POST['mpurl']);
        }else{
            cpmessage('添加渠道失败！',$_POST['mpurl']);
        }
    }else{
        cpmessage('请填写添加的渠道！',$_POST['mpurl']);
    }
}
$mpurl = 'admincp.php?ac=channel';
if(!empty($_GET['op'])&&$_GET['op']=="add"){
    include_once template("admin/tpl/".$_GET['ac']);
}else{
    if(submitcheck('formhash')) {
        //验证是否有批量操作的权限
        if($_POST['optype'] == 'lock') {
            if(!empty($_POST['ids'])){
                $updatequery=$_SGLOBAL['db']->query("UPDATE ".tname('channel')." SET channelstatus=2 WHERE channelid IN(".simplode($_POST['ids']).")");
                if(!empty($updatequery)){
                    cpmessage('do_success', $_POST['mpurl']);
                }else{
                    cpmessage('锁定失败', $_POST['mpurl']);
                }
            }
        } elseif($_POST['optype'] == 'unlock') {
            if(!empty($_POST['ids'])){
                $updatequery=$_SGLOBAL['db']->query("UPDATE ".tname('channel')." SET channelstatus=1 WHERE channelid IN(".simplode($_POST['ids']).")");
                if(!empty($updatequery)){
                    cpmessage('do_success', $_POST['mpurl']);
                }else{
                    cpmessage('解锁失败', $_POST['mpurl']);
                }
            }
        }
    }
    //处理搜索
    $intkeys = array('channelid', 'channelstatus');
    $strkeys = array();
    $randkeys = array(array('sstrtotime','dateline'), array('intval','blognum'));
    $likekeys = array('tagname');
    $results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
    $wherearr = $results['wherearr'];
    $wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
    $mpurl .= '&'.implode('&', $results['urls']);
    
    //排序
    $orders = getorders(array('dateline'), 'channelid');
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
    $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(channelid) FROM ".tname('channel')." WHERE $wheresql"), 0);
    if($count) {
        $query = $_SGLOBAL['db']->query("SELECT channelid,channelname,channelstatus,dateline FROM ".tname('channel')." WHERE $wheresql $ordersql LIMIT $start,$perpage");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            $value['channelname']=html_entity_decode($value['channelname'],ENT_QUOTES);
            $list[] = $value;
        }
        $multi = multi($count, $perpage, $page, $mpurl);
    }
}
?>