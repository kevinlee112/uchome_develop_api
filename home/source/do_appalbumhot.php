<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_ajax.php 12535 2009-07-06 06:22:34Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}
writeLog("@@@@@@@@@@@@@@@@@@ appcheck start");





$result = array(
			'errcode' => 0,
			'errmsg' => 'error username or password'
		);
    $list = array();
    include_once(S_ROOT.'./source/function_cp.php');

      /*$tmpStr = "\ud83d\ude05 \u4f55\u658c\u658c\ud83d\ude05";
      $tmpStr = preg_replace("#(\\\ud[0-9a-f]{3})#ie","addslashes('\\2')",$tmpStr); //将emoji的unicode留下，其他不动
      $name = json_decode($tmpStr); //#(\\\ud[0-9a-f]{3})#ie
	writeLog("*****************".$tmpStr);*/


    $sqlRank = "select a.albumid,a.uid,a.picid,a.hot,(@i:=@i+1) as i from(select  a.albumid,a.uid,p.picid,p.hot from uchome_album a, uchome_pic p where a.albumid=p.albumid and a.albumname != 'photoalbumsystem' AND  a.classid=1402 group by a.albumid order by p.hot desc) a, (select @i:=0) as it";
    $sqlRank = "select aa.albumid,aa.uid,aa.hot, (@i:=@i+1) as i  from (select  a.albumid, a.uid, a.classid, a.picnum, pc.hot from uchome_album a , (select p1.albumid, p1.hot from uchome_pic p1 where p1.hot = (select max(hot) from uchome_pic where albumid = p1.albumid) order by p1.hot desc ) as pc where a.albumname != 'photoalbumsystem' AND  a.classid=1409 and a.albumid = pc.albumid group by a.uid order by pc.hot desc) aa, (select @i:=0) as it";
    $queryRank = $_SGLOBAL['db']->query($sqlRank);
    while($rank = $_SGLOBAL['db']->fetch_array($queryRank)) {
        $list[] = $rank;
	$index =  $rank['i'];
	$hot = $rank['hot'];
	$albumid = $rank['albumid'];
	$_SGLOBAL['db']->query("UPDATE ".tname('album')." SET weekindex=$index, weekhot=$hot WHERE albumid=$albumid");

    }

var_dump($list);

if (true) return;

        //获取举报记录
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('report')." where  idtype='uid' order by dateline desc ");
        while($report = $_SGLOBAL['db']->fetch_array($query)) {
                $uidarr = unserialize($report['uids']);
		$reportid = $report['id'];
		writeLog("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ has report: report id->".$reportid);
		while (list($key, $val) = each($uidarr))
  		{
		    writeLog("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ has report: user id->".$key);
        	    $queryUser = $_SGLOBAL['db']->query("SELECT s.uid,s.groupid,s.username,s.name FROM ".tname('space')." s
                	LEFT JOIN ".tname('spacefield')." sf ON sf.uid=s.uid
                	WHERE s.uid='$key'");
        	    if($member = $_SGLOBAL['db']->fetch_array($queryUser)) {
			writeLog("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ has report: user id->".$key." groupid->".$member['groupid']);
		        if($member['groupid'] == 1) {
			    include_once(S_ROOT.'./source/function_delete.php');
			    $_SGLOBAL['supe_uid'] = $key;
			    if(!empty($reportid) && deletespace($reportid, 1)) {
				$_SGLOBAL['db']->query("DELETE FROM ".tname('report')." WHERE rid='$report[rid]' ");
          		        writeLog("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ delete success uid:".$key."-reportid:".$reportid);    
			    }
			}	                	
        	    }

  		}
        	//var_dump($uidarr);

	}
?>
