<?php
/*
 author:xuzheng 2016/9/20
 页面用来给用户发送短信召回用户，方便运营通过筛选短信召回概率进行分析数据
 */
if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
//权限
if(!checkperm('managereport')) {
    cpmessage('no_authority_management_operation');
}
$mpurl = 'admincp.php?ac=noteanalyze';
//处理搜索
$intkeys = array('uid');
$strkeys = array();
$randkeys = array(array('sstrtotime','lastlogin'), array('sstrtotime','taskdate'),array('sstrtotime','dateline'));
$likekeys = array();
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
$wherearr = $results['wherearr'];
$moneylogsql="";
$spacesql="";
$notemanagesql="";
foreach($wherearr as $key=>$value){
    if(strstr($value,"taskdate")){
        $moneylogsql.=$value." AND ";
    }elseif(strstr($value,"lastlogin")){
        $spacesql.="c.".$value." AND ";
    }else{
        $notemanagesql="a.".$value." AND ";
    }
}
if(!empty($moneylogsql)){
    $moneylogsql=substr($moneylogsql,0,-5);
}else{
    $moneylogsql=1;
}
if(!empty($spacesql)){
    $spacesql=substr($spacesql,0,-5);
}else{
    $spacesql=1;
}
if(!empty($notemanagesql)){
    $notemanagesql=substr($notemanagesql,0,-5);
}else{
    $notemanagesql=1;
}
$mpurl .= '&'.implode('&', $results['urls']);
//排序
$orders = getorders(array('lastlogin','taskdate','dateline'), 'dateline');
$ordersql = $orders['sql'];
if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
$orderby = array($_GET['orderby']=>' selected');
$ordersc = array($_GET['ordersc']=>' selected');

//显示分页
$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
if(!in_array($perpage, array(20,50,100,200,2000))) $perpage = 20;
$mpurl .= '&perpage='.$perpage;
$perpages = array($perpage => ' selected');

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;
//检查开始数
ckstart($start, $perpage);
$managebatch = checkperm('managebatch');
$allowbatch = true;
$noteList = array();
$multi = '';
$count = 0;
$moneylogTableNames = getMoneylogTableName();
foreach ($moneylogTableNames as $moneylogTableName)
{
    $count +=$_SGLOBAL['db_slave']->result($_SGLOBAL['db_slave']->query("SELECT count(*) FROM (".tname('notemanage')." AS a LEFT JOIN ".tname('space')." AS c ON a.uid=c.uid) LEFT JOIN ".tname('spacefield')." AS d ON a.uid=d.uid LEFT JOIN (SELECT MAX(dateline) as taskdate,moneylogtypecategory,moneyloguid FROM `".tname($moneylogTableName)."` WHERE moneylogtypecategory=21 GROUP BY moneyloguid HAVING $moneylogsql) AS b ON a.uid=b.moneyloguid WHERE $spacesql AND $notemanagesql"), 0);
}
if($count) {
    /*$query = $_SGLOBAL['db_slave']->query("SELECT a.nid,a.uid,d.mobile,d.qq,a.dateline,c.lastlogin,taskdate FROM (".tname('notemanage')." AS a LEFT JOIN ".tname('space')." AS c ON a.uid=c.uid) LEFT JOIN ".tname('spacefield')." AS d ON a.uid=d.uid LEFT JOIN (SELECT MAX(dateline) as taskdate,moneylogtypecategory,moneyloguid FROM ".tname('moneylog')." WHERE moneylogtypecategory=21 GROUP BY moneyloguid HAVING $moneylogsql) AS b ON a.uid=b.moneyloguid WHERE $spacesql AND $notemanagesql $ordersql LIMIT $start,$perpage");
    while ($value = $_SGLOBAL['db_slave']->fetch_array($query)) {
        $value['dateline']=date("Y-m-d H:i",$value['dateline']);
        $value['lastlogin']=date("Y-m-d H:i",$value['lastlogin']);
        $value['taskdate']=date("Y-m-d H:i",$value['taskdate']);
        $noteList[] = $value;
    }*/
    $query = $_SGLOBAL['db_slave']->query("SELECT a.nid,a.uid,d.mobile,d.qq,a.dateline,c.lastlogin FROM (".tname('notemanage')." AS a LEFT JOIN ".tname('space')." AS c ON a.uid=c.uid) LEFT JOIN ".tname('spacefield')." AS d ON a.uid=d.uid WHERE $spacesql AND $notemanagesql $ordersql LIMIT $start,$perpage");
    $uidArr=array();
    while($value=$_SGLOBAL['db_slave']->fetch_array($query)){
        //$uidArr[]=$value['uid'];
        $value['taskdate']=$_SGLOBAL['db_slave']->result($_SGLOBAL['db_slave']->query("SELECT MAX(dateline) as taskdate,moneylogtypecategory,moneyloguid FROM `".tname($moneylogTableNames[0])."` WHERE moneylogtypecategory=21 AND moneyloguid=".$value['uid']." GROUP BY moneyloguid HAVING $moneylogsql"), 0);
        $value['taskdate']=date("Y-m-d H:i",$value['taskdate']);
		$value['dateline']=date("Y-m-d H:i",$value['dateline']);
        $value['lastlogin']=date("Y-m-d H:i",$value['lastlogin']);
        $noteList[] = $value;
    }
    //$taskDateQuery=$_SGLOBAL['db_slave']->query("SELECT MAX(dateline) as taskdate,moneylogtypecategory,moneyloguid FROM ".tname('moneylog')." WHERE moneylogtypecategory=21 GROUP BY moneyloguid HAVING $moneylogsql");
    $multi = multi($count, $perpage, $page, $mpurl);
}
?>