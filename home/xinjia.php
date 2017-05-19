<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: rss.php 12766 2009-07-20 04:26:21Z liguode $
*/

include_once('./common.php');
//@header("Content-type: application/xml");
$pagenum = 10;
$tag = '<?';
$rssdateformat = 'D, d M Y H:i:s T';

$siteurl = getsiteurl();
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
$list = array();

    $code = empty($_GET['code'])?'':$_GET['code'];
    if (!empty($code)) {
        $query = $_SGLOBAL['db']->query("SELECT uid,invitecode FROM ".tname('space')." where invitecode=\"$code\" ORDER BY uid desc limit 1");
        if ($value = $_SGLOBAL['db']->fetch_array($query)) {
	       $uid = $value['uid'];
        }
    }


if(!empty($uid)) {
	$space = getspace($uid);
}
if(empty($space)) {
	//Õ¾µã¸üÐÂrss
	$space['username'] = $_SCONFIG['sitename'];
	$space['name'] = $_SCONFIG['sitename'];
	$space['email'] = $_SCONFIG['adminemail'];
	$space['space_url'] = $siteurl;
	$space['lastupdate'] = sgmdate($rssdateformat);
	$space['privacy']['blog'] = 1;
} else {
	$space['username'] = $space['username'].'@'.$_SCONFIG['sitename'];
	$space['space_url'] = $siteurl."space.php?uid=$space[uid]";
	$space['lastupdate'] = sgmdate($rssdateformat, $space['lastupdate']);
}
if(!empty($code)){
    $space['invitecode'] =$code;
}
        $eventCat = array();
        $queryEvent = $_SGLOBAL['db']->query("SELECT distinct ec.classid, ec.classname FROM ".tname("event") . " as e inner join ".tname("eventclass") . " as ec on e.classid=ec.classid where e.uid=". $uid );
        while($value=$_SGLOBAL['db']->fetch_array($queryEvent)) {
                $value['pic'] = $_SGLOBAL['eventclass'][$value['classid']]['poster'];
                $eventCat[] = $value;
        }
        $space['games'] = $eventCat;

	$tEvent = explode(",",$space['event']);
	$userad="";
    	foreach($tEvent as $u){
            if ($u > 0) {
        	$userad=$u;
		break;
	    }
        }
	$space['userad'] = "http://117.121.25.139/upload/xinplusShare.jpg";
	if (!empty($userad)) {

	    $queryEventClass = $_SGLOBAL['db']->query("SELECT classid,eventad FROM ".tname("eventclass") . "  where classid=". $userad );
	    if ($value=$_SGLOBAL['db']->fetch_array($queryEventClass)) {
		if (!empty($value['eventad'])) {
		    $space['userad'] = $value['eventad'];
		}
	    }
	}


	//echo $space['userad'];
	//$space('tags')
	$tags = split("#", str_replace("&", "", $space['tags']));

$space['qq'] = substr($space['qq'], 0, 5)."***";
$space['msn'] = substr($space['msn'], 0, 5)."***";
realname_get();
include template('space_xinplus');

?>
