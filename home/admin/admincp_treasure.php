<?php
$code=empty($_GET['code'])?'':htmlspecialchars(trim($_GET['code']));
$gid=empty($_GET['gid'])?'':intval($_GET['gid']);
$page=empty($_GET['page'])?1:intval($_GET['page']);
$trueGoodUserQuery=$_SGLOBAL['db']->query("SELECT a.moneylogusername,a.moneyloguid,b.resideprovince,b.residecity FROM ".tname('moneylog_union')." AS a LEFT JOIN ".tname('spacefield')." AS b ON a.moneyloguid=b.uid WHERE a.moneylogtypecategory=51 AND a.moneylogtypeid=".$gid." AND a.moneylogstatus=0");
$trueGoodUser=$_SGLOBAL['db']->fetch_array($trueGoodUserQuery);
$treasureQuery=$_SGLOBAL['db']->query("SELECT * FROM ".tname('creditgood')." WHERE gid=".$gid." AND available=1 AND category=3");
$treasure=$_SGLOBAL['db']->fetch_array($treasureQuery);
$treasure['note']=htmlspecialchars_decode($treasure['note']);
$goodCounts=$treasure['price']/100;
if(empty($trueGoodUser)){
    $userQuery=$_SGLOBAL['db']->query("SELECT a.uid,a.username,b.resideprovince,b.residecity,b.qq,b.deviceid FROM ".tname('space')." AS a LEFT JOIN ".tname('spacefield')." AS b ON a.uid=b.uid WHERE a.invitecode='".$code."'");
    $user=$_SGLOBAL['db']->fetch_array($userQuery);
    $moneylogUser=$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT moneylogid FROM ".tname('moneylog_union')." WHERE moneylogtypecategory=51 AND moneylogtypeid=".$gid." AND moneyloguid=".$user['uid']));
    if(empty($moneylogUser)){
        $goodCounted=$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(*) FROM ".tname('moneylog_union')." WHERE moneylogtypecategory=51 AND moneylogtypeid=".$gid));
        if(($treasure['price']/100)>$goodCounted){
            $num=empty($_GET['num'])?'':intval($_GET['num']);
            if(!empty($num)){
                $moneyQuery=$_SGLOBAL['db']->query("SELECT realmoney,totalmoney FROM ".tname('space')." WHERE uid=".$user['uid']);
                $money=$_SGLOBAL['db']->fetch_array($moneyQuery);
                if($money['realmoney']>=1&&$money['totalmoney']>=1){
                    $status=2;//抽奖成功，显示抽奖号码
                    $setarr = array(
                        'moneylogamount' => -$treasure['price'],
                        'moneylogamounttype' => 1,
                        'moneyloguid' => $user['uid'],
                        'moneylogusername' => $user['username'],
                        'moneyloguserphoto' => "http://app.sihemob.com/data/avatar/".avatar_file($user['uid'], 'middle'),
                        'moneylogtypeid' => $gid,
                        'moneylogtypecategory' => 51,
                        'moneylogstatus' => 1,
                        'moneylogtaskid' => 0,
                        'moneylogstep' => 0,
                        'moneylogtid' => 0,
                        'moneylogtip' => '1元夺宝'.$treasure['title'],
                        'deviceid' => $user['deviceid'],
                        'qq' => $user['qq'],
                        'dateline' => time()
                    );
                    $moneylogTableNames = getMoneylogTableName();
                    $userId=inserttable($moneylogTableNames[0], $setarr, 1);
                    $money['realmoney']--;
                    $money['totalmoney']--;
                    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET totalmoney=".$money['totalmoney'].",realmoney=".$money['realmoney']." WHERE uid=".$user['uid']);
                    $buyGoodID=$_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT count(*) FROM '.tname('moneylog_union')." WHERE moneylogtypecategory=51 AND moneylogtypeid=".$gid." AND moneylogid<=".$userId));
                }else{
                    $status=4;//显示余额不足
                }
            }else{
                $status=0;//显示立即参与按钮
            }
        }else{
            $status=3;//显示参与人数已满
        }    
    }else{
        $status=2;//显示自己抽奖号码
        $buyGoodID=$_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT count(*) FROM '.tname('moneylog_union')." WHERE moneylogtypecategory=51 AND moneylogtypeid=".$gid." AND moneylogid<=".$moneylogUser));
    }
}else{
    $status=1;//显示谁中奖
}
$moneylog=array();
$totalMoneyLog=$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(*) FROM ".tname('moneylog_union')." WHERE moneylogtypecategory=51 AND moneylogtypeid=".$gid));
$perPage=20;
$totalPage=ceil($totalMoneyLog/$perPage);
if(($page-1)<=$totalPage){
    $offset=$perPage*($page-1);
}else{
    $offset=0;
}
$multi="";
for($i=1;$i<=$totalPage;$i++){
    if($i!=$page){
        $multi.="<a href='admincp.php?ac=treasure&code=".$code."&gid=".$gid."&page=".$i."'>-".$i."-</a>";
    }else{
        $multi.="<span style='color:#000000;'>-".$i."-</span>";
    }
}
$moneylogQuery=$_SGLOBAL['db']->query("SELECT a.dateline,a.moneylogusername,b.resideprovince,b.residecity FROM ".tname('moneylog_union')." AS a LEFT JOIN ".tname('spacefield')." AS b ON a.moneyloguid=b.uid WHERE moneylogtypecategory=51 AND moneylogtypeid=".$gid." ORDER BY moneylogid desc LIMIT ".$offset.",".$perPage);
while($moneylogResult=$_SGLOBAL['db']->fetch_array($moneylogQuery)){
    $moneylogResult['date']=date("Y-m-d H:i:s",$moneylogResult['dateline']);
    $moneylog[]=$moneylogResult;
}
if(!empty($treasure['price'])){
    $count=count($moneylog);
    $schedule=number_format($count/($treasure['price']/100),3);
    $schedule=$schedule*100;
    $counted=($treasure['price']/100)-$count;
}
if(empty(count($moneylog))){
    $moneylogStatus=0;
}else{
    $count=count($moneylog);
    $moneylogStatus=1;
    $time=date("Y-m-d H:i:s",$moneylog[$count-1]['dateline']);
}
?>