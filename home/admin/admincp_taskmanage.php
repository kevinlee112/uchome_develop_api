<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_report.php 12856 2009-07-23 07:16:45Z zhengqingpeng $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
//权限
if(!checkperm('managereport')) {
	cpmessage('no_authority_management_operation');
}
$queryTask = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditgood')." WHERE category=21 order by dateline desc");
while ($value = $_SGLOBAL['db']->fetch_array($queryTask)) {
        $tasklist[] = $value;
}
$time=date('Ymd',time());
if (submitcheck('listsubmit')) {
    if($ac != 'report' && !in_array($_POST['optype'], array(1,2))) {
        $_POST['optype'] = 0;
    }
    if($_POST['ids'] && is_array($_POST['ids']) && $_POST['optype']) {
        $createlog = false;
        $url = "admincp.php?ac=$ac&perpage=$_GET[perpage]&page=$_GET[page]";

        if($_POST['optype'] == 1) {
            //忽略举报
            $_SGLOBAL['db']->query("UPDATE ".tname('report')." SET num='0' WHERE rid IN (".simplode($_POST['ids']).")");
            $createlog = true;

        } else {

            if($_POST['optype'] == 3) {
                deleteinfo($_POST['ids']);
            }
            //删除举报
            $_SGLOBAL['db']->query("DELETE FROM ".tname('report')." WHERE rid IN (".simplode($_POST['ids']).")");
            $createlog = true;
        }
        cpmessage('do_success', $url);
    }
}  elseif(submitcheck("checkupdate")) {// 排序
    if($_GET['op']!="del"){
        if(is_uploaded_file($_FILES['feedFile']['tmp_name'])){
            $feedarr=array();
            if($_FILES['feedFile']['type']!="text/plain"){
                unlink($_FILES['feedFile']['tmpname']);
                cpmessage("请选择txt文件上传", $_SERVER['HTTP_REFERER']);
            }else{
               if($_FILES['feedFile']['error']!=0){
                  switch($_FILES['feedFile']['error']){
                      case 1:$message="上传文件大小超过服务器允许上传的最大值";break;
                      case 2:$message="要上传的文件大小超出浏览器限制";break;
                      case 3:$message="文件仅部分被上传";break;
                      case 4:$message="没有找到要上传的文件";break;
                      case 5:$message="服务器临时文件夹丢失";break;
                      case 6:$message="文件写入到临时文件夹出错";break;
                  }
                  unlink($_FILES['feedFile']['tmpname']);
                  cpmessage($message, $_SERVER['HTTP_REFERER']);
               }else{
                   $filename=time().".txt";
                   if(!move_uploaded_file($_FILES['feedFile']['tmp_name'],UPLOAD.$filename)){
                       unlink(UPLOAD.$filename);
                       cpmessage("移动文件到指定文件夹失败", $_SERVER['HTTP_REFERER']);
                   }else{
                       if(!$handle=fopen(UPLOAD.$filename,r)){
                           unlink(UPLOAD.$filename);
                           cpmessage("打开".$_FILES['feedFile']['name']."文件失败", $_SERVER['HTTP_REFERER']);
                       }else{
                           while(!feof($handle)){
                               $feedarr[]=str_replace(array("\n","\r"),"",fgets($handle));
                           }
                           fclose($handle);
                           $taskfeedid=array();
                           foreach($_POST['taskmanagefeed'] as $id=>$feed){
                               $taskfeedid[] = intval($id);
                           }
                           $feedcount=count($feedarr);
                           for($i=0;$i<$feedcount;$i++){
                               if(!is_numeric($feedarr[$i])){
                                   $feedarr[$i]=-1;
                               }
                               $feedarr[$i] = intval($feedarr[$i]);
                               updatetable("taskmanage", array("taskmanagefeed"=>$feedarr[$i]), array("taskmanageid"=>$taskfeedid[$i]));
                           }
                           unlink(UPLOAD.$filename);
                       }
                   }
               } 
            }
        }else{
            if(is_array($_POST['taskmanagefeed'])){
                foreach($_POST['taskmanagefeed'] as $id=>$feed){
                    $taskmanageid = intval($id);
                    if(!is_numeric($feed)){
                        $feed=-1;
                    }
                    $taskmanagefeed = intval($feed);
                    updatetable("taskmanage", array("taskmanagefeed"=>$taskmanagefeed), array("taskmanageid"=>$taskmanageid));
                }
            }
        }
        if(is_uploaded_file($_FILES['countFile']['tmp_name'])){
            $countarr=array();
            if($_FILES['countFile']['type']!="text/plain"){
                unlink($_FILES['countFile']['tmpname']);
                cpmessage("请选择txt文件上传", $_SERVER['HTTP_REFERER']);
            }else{
                if($_FILES['countFile']['error']!=0){
                    switch($_FILES['countFile']['error']){
                        case 1:$message="上传文件大小超过服务器允许上传的最大值";break;
                        case 2:$message="要上传的文件大小超出浏览器限制";break;
                        case 3:$message="文件仅部分被上传";break;
                        case 4:$message="没有找到要上传的文件";break;
                        case 5:$message="服务器临时文件夹丢失";break;
                        case 6:$message="文件写入到临时文件夹出错";break;
                    }
                    unlink($_FILES['countFile']['tmpname']);
                    cpmessage($message, $_SERVER['HTTP_REFERER']);
                }else{
                    $filename=time().".txt";
                    if(!move_uploaded_file($_FILES['countFile']['tmp_name'],UPLOAD.$filename)){
                        unlink(UPLOAD.$filename);
                        cpmessage("移动文件到指定文件夹失败", $_SERVER['HTTP_REFERER']);
                    }else{
                        if(!$handle=fopen(UPLOAD.$filename,r)){
                            unlink(UPLOAD.$filename);
                            cpmessage("打开".$_FILES['countFile']['name']."文件失败", $_SERVER['HTTP_REFERER']);
                        }else{
                            while(!feof($handle)){
                                $countarr[]=str_replace(array("\n","\r"),"",fgets($handle));
                            }
                            fclose($handle);
                            $taskcountid=array();
                            foreach($_POST['taskmanagecount'] as $id=>$count){
                                $taskcountid[] = intval($id);
                            }
                            $countcount=count($countarr);
                            for($i=0;$i<$countcount;$i++){
                                if(!is_numeric($countarr[$i])){
                                    $countarr[$i]=-1;
                                }
                                $countarr[$i] = intval($countarr[$i]);
                                updatetable("taskmanage", array("taskmanagecount"=>$countarr[$i]), array("taskmanageid"=>$taskcountid[$i]));
                            }
                            unlink(UPLOAD.$filename);
                        }
                    }
                }
            }
        }else{
            if(is_array($_POST['taskmanagecount'])){
                foreach($_POST['taskmanagecount'] as $id=>$count){
                    $taskmanageid = intval($id);
                    if(!is_numeric($count)){
                        $count=-1;
                    }
                    $taskmanagecount = intval($count);
                    updatetable("taskmanage", array("taskmanagecount"=>$taskmanagecount), array("taskmanageid"=>$taskmanageid));
                }
            }
        }
    }else{
        $_SGLOBAL['db']->query("DELETE FROM ".tname("taskmanage")." WHERE taskmanageid in ('".implode("','",$_POST['ids'])."')");
    }
    cpmessage("do_success", $_SERVER['HTTP_REFERER']);
}

if($_GET['op'] == 'delete') {

    $rid = isset($_GET['rid'])?intval($_GET['rid']):0;
    if(!$rid) {
        cpmessage('the_right_to_report_the_specified_id', 'admincp.php?ac=report');
    }
    if($_GET['subop'] == 'delinfo') {
        deleteinfo(array($rid));
    }
    //删除举报
    $_SGLOBAL['db']->query("DELETE FROM ".tname('report')." WHERE rid='$rid'");
    cpmessage('do_success', 'admincp.php?ac=report');

} elseif($_GET['op'] == 'ignore') {

    $rid = isset($_GET['rid'])?intval($_GET['rid']):0;
    if(!$rid) {
        cpmessage('the_right_to_report_the_specified_id', 'admincp.php?ac=report');
    }
    $_SGLOBAL['db']->query("UPDATE ".tname('report')." SET num='0' WHERE rid='$rid'");
    cpmessage('do_success', 'admincp.php?ac=report');
}
    
//处理搜索
$intkeys = array('taskmanagegid');
$mpurl = 'admincp.php?ac=taskmanage';
$strkeys = array('');
$randkeys = array(array('intval','taskmanagedaytime'));
$likekeys = array();
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
$wherearr = $results['wherearr'];
$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
$mpurl .= '&'.implode('&', $results['urls']);
$actives = array($_GET['status'] => ' class="active"');
$ordersql = " order by taskmanagedaytime desc ";

//显示分页
$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
if(!in_array($perpage, array(20,50,100,1000))) $perpage = 20;

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;
//检查开始数
ckstart($start, $perpage);
//显示分页
$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('taskmanage')." WHERE $wheresql"), 0);
$selectsql = '*';
$mpurl .= '&perpage='.$perpage;
$perpages = array($perpage => ' selected');

$list = array();
$multi = '';
$reports = $users = array();
if($count) {
    $emptyids = $readids = array();
    $posts = $comments = $ids = $blogids = $picids = $albumids = $spaceids = $pollids = $mtagids = $threadids = $shareids = $eventids = $shareids = array();
    $query = $_SGLOBAL['db']->query("SELECT $selectsql FROM ".tname('taskmanage')." WHERE $wheresql $ordersql LIMIT $start,$perpage");
    $taskmanagecount=array("download"=>0,"succe"=>0,"feed"=>0,"count"=>0,"money"=>0,"feedsucce","countsucce");
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        $taskid = $value['taskmanageid'];
        if($value['taskmanagecount']==-1){
            $value['taskmanagecount']='';
            $taskcount=0;
        }else{
            $taskcount=$value['taskmanagecount'];
        }
        if($value['taskmanagefeed']==-1){
            $value['taskmanagefeed']='';
            $taskfeed=0;
        }else{
            $taskfeed=$value['taskmanagefeed'];
        }
        $channelquery=$_SGLOBAL['db']->query("SELECT channelname FROM ".tname("channel")." WHERE channelid=".$value['channelid']);
        $channelresult=$_SGLOBAL['db']->fetch_row($channelquery);
        $value['channelname']=$channelresult[0];
        $value['taskmanagefeedsucce'] = round(($taskfeed / $value['taskmanagesucce'])*100, 2)."%";
        $value['taskmanagecountsucce'] = round(($taskcount / $value['taskmanagesucce'])*100, 2)."%";
        $value['taskmanagemoney']=$value['taskmanagemoney']/100;
        $taskmanagecount['download']+=$value['taskmanagedownload'];
        $taskmanagecount['succe']+=$value['taskmanagesucce'];
        $taskmanagecount['feed']+=$value['taskmanagefeed'];
        $taskmanagecount['count']+=$value['taskmanagecount'];
        $taskmanagecount['money']+=$value['taskmanagemoney'];
        $list[$taskid] = $value;
    }
    $taskmanagecount['feedsucce']=round(($taskmanagecount['feed'] / $taskmanagecount['succe'])*100, 2)."%";
    $taskmanagecount['countsucce']=round(($taskmanagecount['count'] / $taskmanagecount['succe'])*100, 2)."%";
    $multi = multi($count, $perpage, $page, $mpurl);

}
//显示分页
if($perpage > 100) {
    $count = count($list);
}
?>
