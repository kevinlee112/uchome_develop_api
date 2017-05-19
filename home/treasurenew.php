<?php
    include_once('./common.php');
    include_once(S_ROOT.'./source/function_cp.php');
    $code=empty($_GET['code'])?'':addslashes(htmlspecialchars(ltrim($_GET['code'])));
    if(empty($code)){
        echo "<script>alert('操作违规!');</script>";
    }
    $gid=empty($_GET['gid'])?'':intval($_GET['gid']);
    if(empty($gid)){
        echo "<script>alert('操作违规!')</script>";
    }
    $page=empty($_GET['page'])?1:intval($_GET['page']);
    $num=empty($_GET['num'])?'':intval($_GET['num']);
    $trueGoodUserQuery=$_SGLOBAL['db']->query("SELECT a.moneyloguid,b.resideprovince,b.residecity,c.name FROM ".tname('moneylog_union')." AS a LEFT JOIN ".tname('spacefield')." AS b ON a.moneyloguid=b.uid LEFT JOIN ".tname('space')." AS c ON a.moneyloguid=c.uid WHERE a.moneylogtypecategory=51 AND a.moneylogtaskid=".$gid." AND a.moneylogstatus=0");
    $trueGoodUser=$_SGLOBAL['db']->fetch_array($trueGoodUserQuery);
    $treasureQuery=$_SGLOBAL['db']->query("SELECT * FROM ".tname('creditgood')." WHERE gid=".$gid." AND available=1 AND category=3");
    $treasure=$_SGLOBAL['db']->fetch_array($treasureQuery);
    if(empty($treasure)){
        echo "<script>alert('操作违规!')</script>";
    }
    $treasure['note']=htmlspecialchars_decode($treasure['note']);
    $goodCounts=floor($treasure['price']/100);
    $moneyQuery=$_SGLOBAL['db']->query("SELECT b.uid,b.name,b.realmoney,a.qq,a.deviceid FROM ".tname('spacefield')." AS a LEFT JOIN ".tname('space')." AS b ON a.uid=b.uid WHERE b.invitecode='".$code."'");
    $money=$_SGLOBAL['db']->fetch_array($moneyQuery);
    if(empty($money)){
        echo "<script>alert('操作违规!')</script>";
    }
    $time=time();
    $moneylogTableNames = getMoneylogTableName();
    $insertSql="INSERT INTO `".tname($moneylogTableNames[0])."` (moneylogamount,moneylogamounttype,moneyloguid,moneylogusername,moneyloguserphoto,moneylogtypeid,moneylogtypecategory,moneylogstatus,moneylogtaskid,moneylogstep,moneylogrelatedid,moneylogtid,moneylogtip,dateline) VALUES (";
    $setarr = array(
        'moneylogamount' => -100,
        'moneylogamounttype' => 1,
        'moneyloguid' => $money['uid'],
        'moneylogusername' => '\''.$money['name'].'\'',
        'moneyloguserphoto' => "'http://app.sihemob.com/data/avatar/".avatar_file($money['uid'], 'middle')."'",
        'moneylogtypeid' => 0,
        'moneylogtypecategory' => 51,
        'moneylogstatus' => 1,
        'moneylogtaskid' => $gid,
        'moneylogstep' => 0,
        'moneylogrelatedid' => 0,
        'moneylogtid' => 0,
        'moneylogtip' => '\'1元夺宝'.$treasure['title'].'\'',
        'dateline' => $time
    );
    for($i=1;$i<=$num;$i++){
        if($i==$num){
            $insertSql.=implode(',',$setarr).')';
        }else{
            $insertSql.=implode(',',$setarr).'),(';
        }
    }
    $redis=getRedis();
    if(empty($trueGoodUser)){
        $treasureCount=$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(*) FROM ".tname('moneylog_union')." WHERE moneylogtypecategory=51 AND moneylogtaskid=".$gid));
        if($treasureCount>=$goodCounts){
            $status=4;//显示夺宝参与人数已满            
        }else{
            if($money['realmoney']>=100){
                $status=1;//显示立即购买
            }else{
                $status=2;//显示余额不足，前往赚钱
            }    
        }
    }else{
        $status=3;//显示谁中奖
    }
    if(!empty($num)&&$status=1){
        $treasureStatus=$redis->get('treasure'.$money['uid'].$gid);
        if($treasureStatus){
            header('Location:treasurenew.php?code='.$code.'&gid='.$gid);
            exit;
        }
        $treasureStatus=$redis->incr('treasure'.$money['uid'].$gid);
        if($treasureStatus==1){
            $redis->expire('treasure'.$money['uid'].$gid,60);
        }
        $redis->lpush('treasurelist'.$gid,$money['uid'].'&'.$num);
        $treasureInfo=$redis->rPop('treasurelist'.$gid);
        if($treasureInfo!='nil'){
            $treasureArr=explode('&',$treasureInfo);
            $treasureCount=$redis->incrBy('treasureCount'.$gid,$treasureArr[1]);
            if($treasureCount>$goodCounts){
                $redis->incrBy('treasureCount'.$gid,-$treasureArr[1]);
                $redis->del('treasure'.$money['uid'].$gid);
                echo "<script>alert('您的购买量超过商品剩余总量！')</script>";
            }else{
                $_SGLOBAL['db']->query("UPDATE ".tname("space")." SET realmoney=realmoney-".($num*100).",totalmoney=totalmoney-".($num*100)." WHERE invitecode='".$code."' AND realmoney>=".($num*100));
                $updateStatus=$_SGLOBAL['db']->affected_rows();
                if($updateStatus>0){
                    $_SGLOBAL['db']->query($insertSql);
                    $insertStatus=$_SGLOBAL['db']->affected_rows();
                    if($insertStatus>0){
                        header("Location:treasurelistnew.php?op=myrecord&code=".$code);
                        exit;
                    }else{
                        $redis->incrBy('treasureCount'.$gid,-$treasureArr[1]);
                        $redis->del('treasure'.$money['uid'].$gid);
                        $_SGLOBAL['db']->query("UPDATE ".tname("space")." SET realmoney=realmoney+".($num*100).",totalmoney=totalmoney+".($num*100)." WHERE invitecode='".$code."'");
                        echo "<script>alert('当前参与人数较多，请稍候再试！')</script>";
                    }
                }elseif($updateStatus==0){
                    $redis->incrBy('treasureCount'.$gid,-$treasureArr[1]);
                    $redis->del('treasure'.$money['uid'].$gid);
                    echo "<script>alert('您的余额不足！')</script>";
                }else{
                    $redis->incrBy('treasureCount'.$gid,-$treasureArr[1]);
                    $redis->del('treasure'.$money['uid'].$gid);
                    echo "<script>alert('当前参与人数较多，请稍候再试！')</script>";
                }
            }
        }
    }else{
        if($status!=1&&!empty($num)){
            echo "<script>alert('操作违规')</script>";
        }    
    }
    if($status==3){
        if(!empty($treasure['fileurl'])&!empty($treasure['pkgname'])&!empty($treasure['subtips'])&!empty($treasure['thirdtips'])){
            $treasure['imgStatus']=1;
        }else{
            $treasure['imgStatus']=0;
        }    
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
        $multi="<a href='treasurenew.php?code=".$code."&gid=".$gid."&page=".($page-1)."'>".上一页."</a>&nbsp;";
    }else{
        $multi="";
    }
    $availPage=5;
    if(($page-2)<=0){
        for($i=1;$i<=($totalPage>$availPage?$availPage:$totalPage);$i++){
            if($i!=$page){
                $multi.="<a href='treasurenew.php?code=".$code."&gid=".$gid."&page=".$i."'>-".$i."-</a>&nbsp;";
            }else{
                $multi.="<span style='color:#000000;'>-".$i."-</span>&nbsp;";
            }
        }
    }elseif(($page+2)>$totalPage){
        for($i=($totalPage-4);$i<=$totalPage;$i++){
            if($i!=$page){
                $multi.="<a href='treasurenew.php?code=".$code."&gid=".$gid."&page=".$i."'>-".$i."-</a>&nbsp;";
            }else{
                $multi.="<span style='color:#000000;'>-".$i."-</span>&nbsp;";
            }
        }
    }else{
        for($i=($page-2);$i<=($page+2);$i++){
            if($i!=$page){
                $multi.="<a href='treasurenew.php?code=".$code."&gid=".$gid."&page=".$i."'>-".$i."-</a>&nbsp;";
            }else{
                $multi.="<span style='color:#000000;'>-".$i."-</span>&nbsp;";
            }
        }
    }
    if($page!=$totalPage){
        $multi.="<a href='treasurenew.php?code=".$code."&gid=".$gid."&page=".($page+1)."'>".下一页."</a>&nbsp;";
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
    include template('admin/tpl/treasurenew');
?>