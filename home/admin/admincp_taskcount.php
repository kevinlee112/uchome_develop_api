<?php
/*
	author:xuzheng 2016/7/11
	页面用来处理分发平台的数据统计
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managereport')) {
	cpmessage('no_authority_management_operation');
}
$channellist=array();
$channelquery=$_SGLOBAL['db']->query("SELECT channelid,channelname FROM ".tname('channel')." WHERE channelstatus=1");
while($value=$_SGLOBAL['db']->fetch_array($channelquery)){
    $channellist[]=$value;
}
//处理搜索
    $intkeys = array('channelid');
    $mpurl = 'admincp.php?ac=taskcount';
    $strkeys = array();
    $randkeys = array(array('intval','taskmanagedaytime'));
    $likekeys = array();
    $results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
    $wherearr = $results['wherearr'];
    $wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
    $mpurl .= '&'.implode('&', $results['urls']);
    $actives = array($_GET['status'] => ' class="active"');
    $ordersql = " order by taskmanagedaytime desc ";
    
    //分组
    $groupsql="GROUP BY taskmanagegid,channelid";
    
    //显示分页
    $perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
    if(!in_array($perpage, array(20,50,100,1000))) $perpage = 20;
    
    $page = empty($_GET['page'])?1:intval($_GET['page']);
    if($page<1) $page = 1;
    $start = ($page-1)*$perpage;
    //检查开始数
    ckstart($start, $perpage);
    //显示分页
    $query = $_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('taskmanage')." WHERE $wheresql $groupsql");
    $count=$_SGLOBAL['db']->num_rows($query);
    $selectsql = 'taskmanageid,channelid,taskmanagegid,taskmanagename,sum(taskmanagedownload) as taskmanagedownload,sum(taskmanagesucce) as taskmanagesucce,sum(taskmanagemoney) as taskmanagemoney,sum(taskmanagefeed) as taskmanagefeed,sum(taskmanagecount) as taskmanagecount,dateline';
    $mpurl .= '&perpage='.$perpage;
    $perpages = array($perpage => ' selected');
    $list = array();
    $multi = '';
    $reports = $users = array();
    if($count) {
        $emptyids = $readids = array();
        $posts = $comments = $ids = $blogids = $picids = $albumids = $spaceids = $pollids = $mtagids = $threadids = $shareids = $eventids = $shareids = array();
        $query = $_SGLOBAL['db']->query("SELECT $selectsql FROM ".tname('taskmanage')." WHERE $wheresql $groupsql $ordersql LIMIT $start,$perpage");
        $taskmanagecount=array("download"=>0,"succe"=>0,"feed"=>0,"count"=>0,"money"=>0,"feedsucce","countsucce");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            $taskfeed=0;
            $taskcount=0;
            $taskquery=$_SGLOBAL['db']->query("SELECT taskmanagefeed,taskmanagecount FROM ".tname('taskmanage')." WHERE taskmanagegid=".$value['taskmanagegid']." AND channelid=".$value['channelid']." AND $wheresql");
            while($taskvalue=$_SGLOBAL['db']->fetch_array($taskquery)){
                if($taskvalue['taskmanagefeed']<0){
                    $taskfeed++;
                }
                if($taskvalue['taskmanagecount']<0){
                    $taskcount++;
                }
            }
            if(empty($_GET['taskmanagedaytime1'])||empty($_GET['taskmanagedaytime2'])){
                if(empty($_GET['taskmanagedaytime1'])){
                    if(empty($_GET['taskmanagedaytime2'])){
                        $value['taskmanagedaytime']=" -- ";
                    }else{
                        $value['taskmanagedaytime']=" --".$_GET['taskmanagedaytime2'];
                    }
                }
                if(empty($_GET['taskmanagedaytime2'])){
                    if(!empty($_GET['taskmanagedaytime1'])){
                        $value['taskmanagedaytime']=$_GET['taskmanagedaytime1']."-- ";
                    }
                }
            }else{
                $value['taskmanagedaytime']=$_GET['taskmanagedaytime1']."--".$_GET['taskmanagedaytime2'];
            }
            $taskid = $value['taskmanageid'];
            $value['taskmanagecount']+=$taskcount;
            $value['taskmanagefeed']+=$taskfeed;
            $value['taskmanageday']=$taskfeed;
            $value['taskmanagemoney']=$value['taskmanagemoney']/100;
            $channelquery=$_SGLOBAL['db']->query("SELECT channelname FROM ".tname("channel")." WHERE channelid=".$value['channelid']);
            $channelresult=$_SGLOBAL['db']->fetch_row($channelquery);
            $value['channelname']=$channelresult[0];
            $value['taskmanagefeedsucce'] = round(($value['taskmanagefeed'] / $value['taskmanagesucce'])*100, 2)."%";
            $value['taskmanagecountsucce'] = round(($value['taskmanagecount'] / $value['taskmanagesucce'])*100, 2)."%";
            $value['taskmanagedaytime1']=$_GET['taskmanagedaytime1'];
            $value['taskmanagedaytime2']=$_GET['taskmanagedaytime2'];
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