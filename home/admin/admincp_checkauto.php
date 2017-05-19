<?php
/*
 [UCenter Home] (C) 2007-2008 Comsenz Inc.
 $Id: admincp_checkauto.php 12568 2016-10-25 18:03:01Z xuzheng $
 这个页面一旦执行过后处于一直执行的状态，用于检测每半小时之间间隔的自动发奖和用户注册次数，超过指定倍数则关闭自动发奖和注册
 */
ignore_user_abort(); //即使Client断开(如关掉浏览器)，PHP脚本也可以继续执行.
set_time_limit(0); // 执行时间为无限制，php默认执行时间是30秒，可以让程序无限制的执行下去
$interval=30*60; // 每隔半小时运行一次
$time=time();
$monthTime = mktime(0,0,0,date('m'),1,date('Y'));
$moneylogTableNames= getMoneylogTableName();
fastcgi_finish_request();
do{
    sleep($interval); // 按设置的时间等待半小时循环执行
    $afterquery=$_SGLOBAL['db']->query("SELECT count(*) FROM `".tname($moneylogTableNames[0])."` WHERE moneylogtypecategory=11 and moneylogstatus=0 and dateline>=$time and dateline<=($time+$interval) GROUP BY moneylogid");
    $beforequery=$_SGLOBAL['db']->query("SELECT count(*) FROM `".tname($moneylogTableNames[0])."` WHERE moneylogtypecategory=11 and moneylogstatus=0 and dateline<=$time and dateline>=($time-$interval) GROUP BY moneylogid");
    $afterresult=$_SGLOBAL['db']->fetch_row($afterquery);
    $beforeresult=$_SGLOBAL['db']->fetch_row($beforequery);
    $afterregquery=$_SGLOBAL['db']->query("SELECT count(*) FROM ".tname('space')." WHERE dateline>=$time and dateline<=($time+$interval) GROUP BY uid");
    $beforeregquery=$_SGLOBAL['db']->query("SELECT count(*) FROM ".tname('space')." WHERE dateline<=$time and dateline>=($time-$interval) GROUP BY uid");
    $afterregresult=$_SGLOBAL['db']->fetch_row($afterregquery);
    $beforeregresult=$_SGLOBAL['db']->fetch_row($beforeregquery);
    if($beforeresult[0]!=0 && ($time-$interval)>$monthTime ){
        if(floor($afterresult[0]/$beforeresult[0])>=2){
            if($handle=fopen(ISAUTO,"r")){
                $isAuto=fread($handle,filesize(ISAUTO));
                fclose($handle);
            }
            if($isAuto==1){
                if($handle=fopen(ISAUTO,"w")){
                    fwrite($handle,0);
                    fclose($handle);
                }
            } 
        }
    }
    if($beforeregresult[0]!=0 && ($time-$interval)>$monthTime ){
        if(floor($afterregresult[0]/$beforeregresult[0])>=10){
            if($handle=fopen($_SERVER['DOCUMENT_ROOT']."/home/isreg.txt","r")){
                $isReg=fread($handle,filesize($_SERVER['DOCUMENT_ROOT']."/home/isreg.txt"));
                fclose($handle);
            }
            if($isReg==1){
                if($handle=fopen($_SERVER['DOCUMENT_ROOT']."/home/isreg.txt","w")){
                    fwrite($handle,0);
                    fclose($handle);
                }
            }
        }
    }
    $time+=$interval;
}while(true);
?>