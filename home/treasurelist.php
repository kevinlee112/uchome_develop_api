<?php
    include_once('./common.php');
    $code=empty($_GET['code'])?'':addslashes(htmlspecialchars(ltrim($_GET['code'])));
    if(empty($code)){
        echo "<script>alert('操作违规!')</script>";
    }
    $active1=$active2=$active3="";

    $moneylogTableNames = getMoneylogTableName(strtotime("2016-05-02"));
    if($_GET[op]=='record'){
        $status=2;
        $active2="  pro-tabs-tab-item-selected";
        $recordList=array();
        $sql = 'SELECT a.gid,a.icon,a.title,a.fileurl,a.pkgname,a.subtips,a.thirdtips,b.moneylogusername,b.moneylogid,b.moneyloguid FROM '.tname('creditgood')." AS a LEFT JOIN `".tname('moneylog_union')."` AS b ON a.gid=b.moneylogtaskid WHERE a.category=3 AND a.available=1 AND b.moneylogtypecategory=51 AND b.moneylogstatus=0  ORDER BY moneylogid DESC";
        $recordQuery=$_SGLOBAL['db_slave']->query($sql);
        while($recordValue=$_SGLOBAL['db_slave']->fetch_array($recordQuery)){
            $recordValue['number']=$_SGLOBAL['db_slave']->result($_SGLOBAL['db_slave']->query('SELECT count(moneylogid) FROM '.tname('moneylog_union').' WHERE moneylogtypecategory=51 AND moneylogtaskid='.$recordValue['gid'].' AND moneylogid<='.$recordValue['moneylogid']), 0);
            $time = 0;
            foreach ($moneylogTableNames as $moneylogTableName)
            {
               $time =  $_SGLOBAL['db_slave']->result($_SGLOBAL['db_slave']->query('SELECT dateline FROM `'.tname($moneylogTableName).'` WHERE moneylogtypecategory=51 AND moneylogtaskid='.$recordValue['gid'].' ORDER BY moneylogid DESC LIMIT 1'),0);
                if (!empty($time)) break;
            }
            $recordValue['time']=date('Y-m-d',$time);
            $recordValue['mycounted']=$_SGLOBAL['db_slave']->result($_SGLOBAL['db_slave']->query("SELECT count(a.moneylogid) FROM ".tname('moneylog_union')." AS a LEFT JOIN ".tname('space')." AS b ON a.moneyloguid=b.uid WHERE a.moneylogtypecategory=51 AND a.moneylogtaskid=".$recordValue['gid']." AND b.invitecode='".$code."'"), 0);
            $recordValue['wincounted']=$_SGLOBAL['db_slave']->result($_SGLOBAL['db_slave']->query('SELECT count(moneylogid) FROM '.tname('moneylog_union').' WHERE moneylogtypecategory=51 AND moneylogtaskid='.$recordValue['gid'].' AND moneyloguid='.$recordValue['moneyloguid']), 0);
            if(!empty($recordValue['fileurl'])&!empty($recordValue['pkgname'])&!empty($recordValue['subtips'])&!empty($recordValue['thirdtips'])){
                $recordValue['imgStatus']=1;
            }else{
                $recordValue['imgStatus']=0;
            }
            $recordList[]=$recordValue;
        }
        if(empty($recordList)){
            $recordStatus=0;
        }else{
            $recordStatus=1;
        }
    }elseif($_GET[op]=='myrecord'){
        $status=3;
        $active3="  pro-tabs-tab-item-selected";
        $myrecordList=array();
        $myrecordQuery=$_SGLOBAL['db_slave']->query("SELECT b.gid,b.icon,b.title,b.price,count(a.moneylogid) AS mycounted FROM ".tname('moneylog_union')." AS a LEFT JOIN ".tname('creditgood')." AS b ON a.moneylogtaskid=b.gid LEFT JOIN ".tname('space')." AS c ON a.moneyloguid=c.uid WHERE b.category=3 AND b.available=1 AND a.moneylogtypecategory=51 AND c.invitecode='".$code."' GROUP BY a.moneylogtaskid ORDER BY b.dateline DESC");
        while($myrecordValue=$_SGLOBAL['db_slave']->fetch_array($myrecordQuery)){
            $myrecordValue['counted']=$_SGLOBAL['db_slave']->result($_SGLOBAL['db_slave']->query('SELECT count(moneylogid) as counted FROM '.tname('moneylog_union').' WHERE moneylogtaskid='.$myrecordValue['gid']));
            if($myrecordValue['price']/100==$myrecordValue['counted']){
                $moneylogStatus=$_SGLOBAL['db_slave']->result($_SGLOBAL['db_slave']->query('SELECT count(moneylogid) AS moneylogStatus FROM '.tname('moneylog_union').' WHERE moneylogtypecategory=51 AND moneylogtaskid='.$myrecordValue['gid'].' AND moneylogstatus=0'));
                if($moneylogStatus){
                    $myrecordValue['treasureStatus']=2;
                }else{
                    $myrecordValue['treasureStatus']=1;
                }
            }else{
                $myrecordValue['treasureStatus']=0;
            }
            $myrecordList[]=$myrecordValue;
        }
        if(empty($myrecordList)){
            $myrecordStatus=0;
        }else{
            $myrecordStatus=1;
        }
    }elseif($_GET[op]=='mylist'){
        $status=4;
        $gid=empty($_GET['gid'])?'':intval($_GET['gid']);
        if(empty($gid)){
            echo "<script>alert('操作违规!')</script>";
        }
        $mylistArr=array();
        $mylistQuery=$_SGLOBAL['db_slave']->query("SELECT a.moneylogid,a.dateline,a.moneylogstatus FROM ".tname('moneylog_union')." AS a LEFT JOIN ".tname('space')." AS b ON a.moneyloguid=b.uid WHERE a.moneylogtypecategory=51 AND a.moneylogtaskid=".$gid." AND b.invitecode='".$code."' order by moneylogid");
        while($mylistValue=$_SGLOBAL['db_slave']->fetch_array($mylistQuery)){
            $mylistValue['dateline']=date('Y-m-d H:i:s',$mylistValue['dateline']);
            $mylistValue['moneylogid']=$_SGLOBAL['db_slave']->result($_SGLOBAL['db_slave']->query('SELECT count(moneylogid) FROM '.tname('moneylog_union').' WHERE moneylogtypecategory=51 AND moneylogtaskid='.$gid.' AND moneylogid<='.$mylistValue['moneylogid']));
            $mylistArr[]=$mylistValue;
        }
        $trueGoodUserQuery=$_SGLOBAL['db_slave']->query("SELECT moneyloguid FROM ".tname('moneylog_union')." WHERE moneylogtypecategory=51 AND moneylogtaskid=".$gid." AND moneylogstatus=0");
        $trueGoodUser=$_SGLOBAL['db_slave']->fetch_array($trueGoodUserQuery);
        if(empty($trueGoodUser)){
            $treasureTrueStatus=0;
        }else{
            $treasureTrueStatus=1;
        }
        $mycounted=count($mylistArr);
    }else{
        $status=1;
        $active1="  pro-tabs-tab-item-selected";
        $treasureList=array();
        $treasureQuery=$_SGLOBAL['db_slave']->query('SELECT a.gid,a.icon,a.title,a.price,count(b.moneylogid) as counted FROM '.tname('creditgood').' AS a LEFT JOIN '.tname('moneylog_union').' AS b ON a.gid=b.moneylogtaskid WHERE a.category=3 AND a.available=1 GROUP BY a.gid ORDER BY a.dateline DESC LIMIT 10');
        while($treasureValue=$_SGLOBAL['db_slave']->fetch_array($treasureQuery)){
            if($treasureValue['price']/100<=$treasureValue['counted']){
                $moneylogStatus=$_SGLOBAL['db_slave']->result($_SGLOBAL['db_slave']->query('SELECT count(moneylogid) AS moneylogStatus FROM '.tname('moneylog_union').' WHERE moneylogtypecategory=51 AND moneylogtaskid='.$treasureValue['gid'].' AND moneylogstatus=0'), 0);
                if($moneylogStatus){
                    continue;
                }
                $treasureValue['treasureStatus']=1;
            }else{
                $treasureValue['treasureStatus']=0;
            }
            $treasureValue['price']=$treasureValue['price']/100;
            $treasureValue['progress']=number_format($treasureValue['counted']/$treasureValue['price'],2)*100;
            $treasureValue['uncounted']=$treasureValue['price']-$treasureValue['counted'];
            $treasureList[]=$treasureValue;
        }
        if(empty($treasureList)){
            $treasureStatus=0;
        }else{
            $treasureStatus=1;
        }
    }
    include template('admin/tpl/treasurelist');
?>