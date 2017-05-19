<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do.php 12354 2009-06-11 10:14:06Z liguode $
*/

include_once('./common.php');
include_once('./source/function_admincp.php');
//获取方法
$ac = empty($_GET['ac'])?'':$_GET['ac'];

//自定义登录
if($ac == $_SCONFIG['login_action']) {
	$ac = 'login';
} elseif($ac == 'login') {
	$ac = '';
}
if($ac == $_SCONFIG['register_action']) {
	$ac = 'register';
} elseif($ac == 'register') {
	$ac = '';
}
//允许的方法
$acs = array('login', 'register', 'lostpasswd', 'swfupload', 'inputpwd',
	'ajax', 'seccode', 'sendmail', 'stat', 'emailcheck', 'applogin','appregister', 'app', 'appb'
	, 'appdoing', 'appevent', 'appmsg','appcheck','apploop','appalbum', 'appfeed', 'apptest','appim',
	'appinfo','appinvite', 'appgame','appcredit','appsubject','appbanner','appuser','apprp','appalbumhot',
	'appcomment','appconfig', 'appmoney','appsms','appdebug','appgoods');
if(empty($ac) || !in_array($ac, $acs)) {
	showmessage('enter_the_space', 'index.php', 0);
}
do_log();
//链接
$theurl = 'do.php?ac='.$ac;
//writeLogKang("do.php ------".$theurl);



$result = array(
                                                'errcode' => 10,
                                                'errmsg' => 'An error has occurred, please try again later [10]'
                                        );
$randomInt = rand(0, 1000);
//writeLogKang("-------- random: ".$randomInt);
/*if ($randomInt < 510) {

echo json_encode($result);
return;
}*/

include_once(S_ROOT.'./source/do_'.$ac.'.php');

?>
