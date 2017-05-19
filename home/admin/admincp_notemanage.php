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
$mpurl = 'admincp.php?ac=notemanage';
if(submitcheck('opsubmit')) {
    //验证是否有批量操作的权限
    if(!empty($_POST['ids'])){
        $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
        $sucSend=array();
        $insertsql="";
        $time=time();
        $query=$_SGLOBAL['db']->query("SELECT a.mobile,a.uid,b.realmoney/100 AS realmoney FROM ".tname('spacefield')." AS a LEFT JOIN ".tname('space')." AS b ON a.uid=b.uid WHERE a.uid in(".implode(",",$_POST['ids']).")");
        while($mobileResult=mysql_fetch_array($query)){
            $mobileResult["realmoney"]=number_format($mobileResult["realmoney"],2);
            $smsConf = array(
                'key'   => '07551bb1c343f230c68e3bd54ae8f0d7', //您申请的APPKEY
                'mobile'    => $mobileResult['mobile'], //接受短信的用户手机号码
                'tpl_id'    => $_POST['tplid'], //您申请的短信模板ID，根据实际情况修改
                "tpl_value" =>'#code#='.$mobileResult["realmoney"] //您设置的模板变量，根据实际情况修改
            );
            $urlStr=http_build_query($smsConf);
            $content = file_get_contents($sendUrl."?".$urlStr); //请求发送短信
            if($content){
                $result = json_decode($content,true);
                $error_code = $result['error_code'];
                if($error_code == 0){
                    $sucSend[]=$mobileResult['uid'];
                    $insertsql.=$mobileResult['uid'].",".$time."),(";
                }
            }
        }
        if(!empty($sucSend)){
            $insertsql=substr($insertsql,0,-3);
            $_SGLOBAL['db']->query("INSERT INTO ".tname('notemanage')." (uid,dateline) VALUES ($insertsql)");
        }
        $count=count($sucSend);
        cpmessage("发送成功数量为$count!", $mpurl);
    }
}
//处理搜索
$intkeys = array();
$strkeys = array();
$randkeys = array(array('sstrtotime','lastlogin'), array('doubleval','moneyamounts'),array('doubleval','realmoney'));
$likekeys = array();
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
$wherearr = $results['wherearr'];
$moneylogsql="";
$spacesql="";
foreach($wherearr as $key=>$value){
    if(strstr($value,"moneyamounts")){
        $moneylogsql.=$value." AND ";
    }else{
        $spacesql.="a.".$value." AND ";
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
$mpurl .= '&'.implode('&', $results['urls']);

//排序
$orders = getorders(array('lastlogin','moneyamounts','realmoney'), 'lastlogin');
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
$list = array();
$multi = '';
$moneylogsql=str_replace("moneyamounts","SUM(moneylogamount)/(-100)",$moneylogsql);
$spacesql=str_replace("realmoney","realmoney/100",$spacesql);
$moneylogTableNames = getMoneylogTableName();
$count = 0;
foreach ($moneylogTableNames as $moneylogTableName)
{
    $count += $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(*) FROM (".tname('space')." AS a LEFT JOIN ".tname('spacefield')." AS c ON a.uid=c.uid) INNER JOIN (SELECT SUM(moneylogamount)/(-100) AS moneyamounts,moneyloguid,moneylogtypecategory FROM `".tname($moneylogTableName)."` WHERE moneylogtypecategory=11 AND moneylogstatus=0 GROUP BY moneyloguid HAVING $moneylogsql) AS b ON a.uid=b.moneyloguid WHERE $spacesql"), 0);
}
if($count) {
    $query = $_SGLOBAL['db']->query("SELECT a.uid,a.name,c.mobile,c.qq,a.realmoney/100 AS realmoneys,a.dateline,a.lastlogin,moneyamounts FROM (".tname('space')." AS a LEFT JOIN ".tname('spacefield')." AS c ON a.uid=c.uid) INNER JOIN (SELECT SUM(moneylogamount)/(-100) AS moneyamounts,moneyloguid,moneylogtypecategory FROM `".tname($moneylogTableNames[0])."` WHERE moneylogtypecategory=11 AND moneylogstatus=0 GROUP BY moneyloguid HAVING $moneylogsql) AS b ON a.uid=b.moneyloguid WHERE $spacesql $ordersql LIMIT $start,$perpage");
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        $value['realmoneys']=number_format($value['realmoneys'],2);
        $value['moneyamounts']=number_format($value['moneyamounts'],2);
        $value['dateline']=date("Y-m-d H:i",$value['dateline']);
        $value['lastlogin']=date("Y-m-d H:i",$value['lastlogin']);
        $list[] = $value;
    }
    $multi = multi($count, $perpage, $page, $mpurl);
}
?>