<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_cache.php 12720 2009-07-16 02:23:15Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限

$turl = 'admincp.php?ac=batch';

if(!@include_once(S_ROOT.'./data/data_eventclass.php')) {
        include_once(S_ROOT.'./source/function_cache.php');
        eventclass_cache();
}


//更新缓存
if(submitcheck('batchsubmit')) {
	include_once(S_ROOT.'./source/function_cp.php');
	include_once(S_ROOT.'./source/function_cache.php');
	include_once (S_ROOT.'./uc_client/client.php');
	include_once(S_ROOT.'./source/function_space.php');
	
	for($i=0;$i<8;$i++){
            $name = $_POST["addname"][$i];
	    $qq = $_POST["addqq"][$i];
	    $wx = $_POST["addwx"][$i];
	    $classid = $_POST["classid"][$i];
	    $title = $_POST["addtag"][$i];
	    $sex = $_POST["addsex"][$i];
	    $lnglat = explode(',', $_POST["addlocation"][$i]);

	    $filePostName = 'file'.$i;
            $filePost = $_FILES[$filePostName];

	    if (empty($title)) continue;
	    $time = time() . $i;
	    $username = "tb" . $time;
	    $newuid = uc_user_appregister($username, $time, "");

            $setarr = array(
                'uid' => $newuid,
                'username' => $username,
                'password' => md5("$newuid|$_SGLOBAL[timestamp]")//本地密码随机生成
            );
            //更新本地用户库
            inserttable('member', $setarr, 0, true);
            //开通空间
            
            $space = space_open($newuid, $username, 0, "", "", "");

        if (!empty($name)) {
                $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET name='$name' WHERE uid='$newuid'");
        } 
	
	if (!empty($qq)) {
                $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET qq='$qq' WHERE uid='$newuid'");
        }

	if (!empty($wx)) {
                $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET msn='$wx' WHERE uid='$newuid'");
        }

	if (!empty($sex)) {
                $_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET sex='1' WHERE uid='$newuid'");
        } else {
		$_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET sex='2' WHERE uid='$newuid'");
	}











	list($width, $height, $type, $attr) = getimagesize($filePost['tmp_name']);
	$imgtype = array(1 => '.gif', 2 => '.jpg', 3 => '.png');
	$filetype = $imgtype[$type];
	if(!$filetype) $filetype = '.jpg';
	$tmpavatar = UC_DATADIR.'./tmp/upload'.$newuid.$filetype;

	file_exists($tmpavatar) && @unlink($tmpavatar);
	if(@copy($filePost['tmp_name'], $tmpavatar) || @move_uploaded_file($filePost['tmp_name'], $tmpavatar)) {
		@unlink($filePost['tmp_name']);
		list($width, $height, $type, $attr) = getimagesize($tmpavatar);
		if($width < 10 || $height < 10 || $type == 4) {
			@unlink($tmpavatar);
		}
	} else {
		@unlink($filePost['tmp_name']);
	}
	$avatarurl = UC_DATAURL.'/tmp/upload'.$newuid.$filetype;



	$uidT = sprintf("%09d", $newuid);
        $dir1 = substr($uidT, 0, 3);
        $dir2 = substr($uidT, 3, 2);
        $dir3 = substr($uidT, 5, 2);
        $home = $dir1.'/'.$dir2.'/'.$dir3;
	//$home = get_home($uid);
	if(!is_dir(UC_DATADIR.'./avatar/'.$home)) {

		$dir = UC_DATADIR.'./avatar/';
		$uidTT = sprintf("%09d", $newuid);
                $dir1 = substr($uidTT, 0, 3);
                $dir2 = substr($uidTT, 3, 2);
                $dir3 = substr($uidTT, 5, 2);
                !is_dir($dir.'/'.$dir1) && mkdir($dir.'/'.$dir1, 0777);
                !is_dir($dir.'/'.$dir1.'/'.$dir2) && mkdir($dir.'/'.$dir1.'/'.$dir2, 0777);
                !is_dir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3) && mkdir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3, 0777);
	}
		$avatartype = 'virtual';//getgpc('avatartype', 'G') == 'real' ? 'real' : 'virtual';



		$bigavatarfile = '/opt/app.sihemob.com/data/'.'./avatar/'.get_avatar($newuid, 'big', $avatartype);
		$middleavatarfile = '/opt/app.sihemob.com/data/'.'./avatar/'.get_avatar($newuid, 'middle', $avatartype);
		$smallavatarfile = '/opt/app.sihemob.com/data/'.'./avatar/'.get_avatar($newuid, 'small', $avatartype);

		@copy($tmpavatar, $bigavatarfile);
		@copy($tmpavatar, $middleavatarfile);
		@copy($tmpavatar, $smallavatarfile);
		
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET avatar='1' WHERE uid='$newuid'");

		$success = 1;
		/*$bigavatar = flashdata_decode(getgpc('avatar1', 'P'));
		$middleavatar = flashdata_decode(getgpc('avatar2', 'P'));
		$smallavatar = flashdata_decode(getgpc('avatar3', 'P'));
		if(!$bigavatar || !$middleavatar || !$smallavatar) {
			echo "11111111111111111"; exit;
		}


		$success = 1;
		$fp = @fopen($bigavatarfile, 'wb');
		@fwrite($fp, $bigavatar);
		@fclose($fp);

		$fp = @fopen($middleavatarfile, 'wb');
		@fwrite($fp, $middleavatar);
		@fclose($fp);

		$fp = @fopen($smallavatarfile, 'wb');
		@fwrite($fp, $smallavatar);
		@fclose($fp);*/

		$biginfo = @getimagesize($bigavatarfile);
		$middleinfo = @getimagesize($middleavatarfile);
		$smallinfo = @getimagesize($smallavatarfile);
		if(!$biginfo || !$middleinfo || !$smallinfo || $biginfo[2] == 4 || $middleinfo[2] == 4 || $smallinfo[2] == 4) {
			file_exists($bigavatarfile) && unlink($bigavatarfile);
			file_exists($middleavatarfile) && unlink($middleavatarfile);
			file_exists($smallavatarfile) && unlink($smallavatarfile);
			$success = 0;
		}

		$filetype = '.jpg';
		@unlink(UC_DATADIR.'./tmp/upload'.$newuid.$filetype);



	$resultTime = $_SGLOBAL['timestamp'] - rand(1,100);


	// 基本信息
        $arr1 = array(
                "title" => getstr($title, 80, 1, 1, 1),
                "classid" => intval($classid),
                "province" => getstr('朝阳', 20, 1, 1),
                "city" => getstr('北京', 20, 1, 1),
                "location" => getstr('', 80, 1, 1, 1),
                "starttime" => $_SGLOBAL['timestamp'],//sstrtotime($_SGLOBAL['timestamp']),
                "endtime" => $_SGLOBAL['timestamp']+1000000,//sstrtotime($_SGLOBAL['timestamp'] + 1000000),
                "deadline" => $_SGLOBAL['timestamp'] + 1000000,//sstrtotime($_SGLOBAL['timestamp'] + 1000000),
                "public" => 2,//intval($_POST['public'])
		"dateline" => $resultTime,
                "lat" => $lnglat[1],
                "lng" => $lnglat[0]
        );
        // 扩展信息
        $arr2 = array(
                "detail" => getstr($detail, '', 1, 1, 1, 0, 1),
                "limitnum" => 0, //intval($_POST['limitnum']),
                "verify" => 0, //intval($_POST['verify']),
                "allowpost" => 1, //intval($_POST['allowpost']),
                "allowpic" => 1, //intval($_POST['allowpic']),
                "allowfellow" => 0, //intval($_POST['allowfellow']),
                "allowinvite" => 1, //intval($_POST['allowinvite']),
                "template" => getstr(0, 255, 1, 1, 1) //$_POST['template']
        );
        $arr1['topicid'] = 0;//$_POST['topicid'];

        // 创建者
        $arr1['uid'] = $newuid;
        $arr1['username'] = $name;
        // 创建时间
        $arr1['dateline'] = $resultTime;//$_SGLOBAL['timestamp'] - rand(1, 200);
        $arr1['updatetime'] = $resultTime;//$_SGLOBAL['timestamp'];

        //人数
        $arr1['membernum'] = 1;

        // 是否需要审核
        $arr1['grade'] = 0;//checkperm("verifyevent") ? 0 : 1;

        // 插入 活动（event） 表
        $eventid = inserttable("event", $arr1, 1);


        // 活动信息
        $arr2['eventid'] = $eventid;
        inserttable("eventfield", $arr2);


        $arr3 = array(
                        "eventid" => $eventid,
                        "uid" => $arr1['uid'],
                        "username" => $arr1['username'],
                        "status" => 4,  // 发起者
                        "fellow" => 0,
                        "template" => $arr1['template'],
                        "dateline" => $_SGLOBAL['timestamp']
                   );
        // 插入 用户活动（userevent） 表
        inserttable("userevent", $arr3);

        //统计
        updatestat('event');

        //include_once(S_ROOT.'./source/function_cp.php');

        $space = getspace($myId);

        //更新用户统计
        if(empty($space['eventnum'])) {
                $space['eventnum'] = getcount('event', array('uid'=>$space['uid']));
                $eventnumsql = "eventnum=".$space['eventnum'];
        } else {
                $eventnumsql = 'eventnum=eventnum+1';
        }

        //积分
        $reward = getreward('createevent', 0);
        $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET {$eventnumsql}, lastpost='$_SGLOBAL[timestamp]', updatetime='$_SGLOBAL[timestamp]', credit=credit+$reward[credit], experience=experience+$reward[experience] WHERE uid=$arr1[uid]");



	}


	cpmessage('do_success', $turl);

}

?>
