<?php
include_once('./common.php');
$code=empty($_GET['code'])?'':htmlspecialchars(trim($_GET['code']));
$adContent=saddslashes(unserialize(stripslashes($_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT adcode FROM ".tname('ad')." WHERE available=1 AND adid=8")))));
$gid=empty($_GET['gid'])?$adContent['html']:intval($_GET['gid']);
$fileName="treasure2";
$page=empty($_GET['page'])?1:intval($_GET['page']);
$trueGoodUserQuery=$_SGLOBAL['db']->query("SELECT a.moneyloguid,b.resideprovince,b.residecity,c.name FROM ".tname('moneylog_union')." AS a LEFT JOIN ".tname('spacefield')." AS b ON a.moneyloguid=b.uid LEFT JOIN ".tname('space')." AS c ON a.moneyloguid=c.uid WHERE a.moneylogtypecategory=51 AND a.moneylogtaskid=".$gid." AND a.moneylogstatus=0");
$trueGoodUser=$_SGLOBAL['db']->fetch_array($trueGoodUserQuery);
$treasureQuery=$_SGLOBAL['db']->query("SELECT * FROM ".tname('creditgood')." WHERE gid=".$gid." AND available=1 AND category=3");
$treasure=$_SGLOBAL['db']->fetch_array($treasureQuery);
$treasure['note']=htmlspecialchars_decode($treasure['note']);
$goodCounts=$treasure['price']/100;
$redis=getRedis();
if(empty($trueGoodUser)){
    $userQuery=$_SGLOBAL['db']->query("SELECT a.uid,a.name,b.resideprovince,b.residecity,b.qq,b.deviceid FROM ".tname('space')." AS a LEFT JOIN ".tname('spacefield')." AS b ON a.uid=b.uid WHERE a.invitecode='".$code."'");
    $user=$_SGLOBAL['db']->fetch_array($userQuery);
    $moneylogUser=$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT moneylogid FROM ".tname('moneylog_union')." WHERE moneylogtypecategory=51 AND moneylogtaskid=".$gid." AND moneyloguid=".$user['uid']));
    if(empty($moneylogUser)){
        $goodCounted=$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(*) FROM ".tname('moneylog_union')." WHERE moneylogtypecategory=51 AND moneylogtaskid=".$gid));
        if(($treasure['price']/100)>$goodCounted){
            $moneyQuery=$_SGLOBAL['db']->query("SELECT realmoney,totalmoney FROM ".tname('space')." WHERE uid=".$user['uid']);
            $money=$_SGLOBAL['db']->fetch_array($moneyQuery);
            if($money['realmoney']>=100&&$money['totalmoney']>=100){
                $num=empty($_GET['num'])?'':intval($_GET['num']);
                if(!empty($num)){
                    $treasureStatus=$redis->get('treasure'.$user['uid'].$gid);
                    if($treasureStatus){
                        header("Location:".$_SERVER['DOCUMENT_ROOT'].'/home/treasure.php?code='.$code.'&gid='.$gid);
                        exit;
                    }
                    $treasureStatus=$redis->incr('treasure'.$user['uid'].$gid);
                    if($treasureStatus==1){
                        $redis->expire('treasure'.$user['uid'].$gid,60);
                    }
                    $status=2;//抽奖成功，显示抽奖号码
                    $setarr = array(
                        'moneylogamount' => -100,
                        'moneylogamounttype' => 1,
                        'moneyloguid' => $user['uid'],
                        'moneylogusername' => $user['name'],
                        'moneyloguserphoto' => "http://app.sihemob.com/data/avatar/".avatar_file($user['uid'], 'middle'),
                        'moneylogtypeid' => 0,
                        'moneylogtypecategory' => 51,
                        'moneylogstatus' => 1,
                        'moneylogtaskid' => $gid,
                        'moneylogstep' => 0,
                        'moneylogtid' => 0,
                        'moneylogtip' => '1元夺宝'.$treasure['title'],
                        'deviceid' => $user['deviceid'],
                        'qq' => $user['qq'],
                        'dateline' => time()
                    );
                    $moneylogTableNames = getMoneylogTableName();
                    $userId=inserttable($moneylogTableNames[0], $setarr, 1);
                    $money['realmoney']-=100;
                    $money['totalmoney']-=100;
                    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET totalmoney=".$money['totalmoney'].",realmoney=".$money['realmoney']." WHERE uid=".$user['uid']);
                    $buyGoodID=$_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT count(*) FROM '.tname('moneylog_union')." WHERE moneylogtypecategory=51 AND moneylogtaskid=".$gid." AND moneylogid<=".$userId));
                }else{
                    $status=0;//显示立即参与按钮
                }
            }else{
                $status=4;//显示余额不足
                if($_GET['appversion']>='2.6.2'){
                    $moneystatus=1;
                }
            }
        }else{
            $status=3;//显示参与人数已满
        }    
    }else{
        $status=2;//显示自己抽奖号码
        $buyGoodID=$_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT count(*) FROM '.tname('moneylog_union')." WHERE moneylogtypecategory=51 AND moneylogtaskid=".$gid." AND moneylogid<=".$moneylogUser));
    }
}else{
    $status=1;//显示谁中奖
}
$moneylog=array();
$totalMoneyLog=$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(*) FROM ".tname('moneylog_union')." WHERE moneylogtypecategory=51 AND moneylogtaskid=".$gid));
$perPage=20;
$totalPage=ceil($totalMoneyLog/$perPage);
if($page<=$totalPage){
    $offset=$perPage*($page-1);
}else{
	$offset=0;
}
if($page!=1){
    $multi="<a href='treasure.php?code=".$code."&gid=".$gid."&page=".($page-1)."'>".上一页."</a>&nbsp;";
}else{
    $multi="";
}
$availPage=5;
if(($page-2)<=0){
    for($i=1;$i<=($totalPage>$availPage?$availPage:$totalPage);$i++){
        if($i!=$page){
            $multi.="<a href='treasure.php?code=".$code."&gid=".$gid."&page=".$i."'>-".$i."-</a>&nbsp;";
        }else{
            $multi.="<span style='color:#000000;'>-".$i."-</span>&nbsp;";
        }
    }
}elseif(($page+2)>$totalPage){
    for($i=($totalPage-4);$i<=$totalPage;$i++){
        if($i!=$page){
            $multi.="<a href='treasure.php?code=".$code."&gid=".$gid."&page=".$i."'>-".$i."-</a>&nbsp;";
        }else{
            $multi.="<span style='color:#000000;'>-".$i."-</span>&nbsp;";
        }
    }
}else{
    for($i=($page-2);$i<=($page+2);$i++){
        if($i!=$page){
            $multi.="<a href='treasure.php?code=".$code."&gid=".$gid."&page=".$i."'>-".$i."-</a>&nbsp;";
        }else{
            $multi.="<span style='color:#000000;'>-".$i."-</span>&nbsp;";
        }
    }
}
if($page!=$totalPage){
    $multi.="<a href='treasure.php?code=".$code."&gid=".$gid."&page=".($page+1)."'>".下一页."</a>&nbsp;";
}
$moneylogQuery=$_SGLOBAL['db']->query("SELECT a.dateline,a.moneyloguserphoto,b.resideprovince,b.residecity,c.name FROM ".tname('moneylog_union')." AS a LEFT JOIN ".tname('spacefield')." AS b ON a.moneyloguid=b.uid LEFT JOIN ".tname('space')." AS c ON a.moneyloguid=c.uid WHERE moneylogtypecategory=51 AND moneylogtaskid=".$gid." ORDER BY moneylogid desc LIMIT ".$offset.",".$perPage);
while($moneylogResult=$_SGLOBAL['db']->fetch_array($moneylogQuery)){
    $moneylogResult['date']=date("Y-m-d H:i:s",$moneylogResult['dateline']);
    $moneylog[]=$moneylogResult;
}
$count=$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(*) FROM ".tname('moneylog_union')." WHERE moneylogtypecategory=51 AND moneylogtaskid=".$gid));
if(!empty($treasure['price'])){
    $schedule=number_format($count/($treasure['price']/100),3);
    $schedule=$schedule*100;
    $counted=($treasure['price']/100)-$count;
}
if($count==0){
    $moneylogStatus=0;
}else{
    $moneylogStatus=1;
}
include template('admin/tpl/treasure');
?>