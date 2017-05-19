<?php if(!defined('IN_UCHOME')) exit('Access Denied');?><?php subtplcheck('admin/tpl/goodsstore|admin/tpl/header|admin/tpl/side|admin/tpl/footer|template/default/header|template/default/footer', '1495161277', 'admin/tpl/goodsstore');?><?php $_TPL['menunames'] = array(
		'index' => '管理首页',
		'config' => '站点设置',
		'privacy' => '隐私设置',
		'usergroup' => '用户组',
		'credit' => '积分规则',
		'profilefield' => '用户栏目',
		'profield' => '群组栏目',
		'eventclass' => '活动分类',
		'magic' => '道具设置',
		'task' => '有奖任务',
		'spam' => '防灌水设置',
		'censor' => '词语屏蔽',
		'ad' => '广告设置',
		'userapp' => 'MYOP应用',
		'app' => 'UCenter应用',
		'network' => '随便看看',
		'cache' => '缓存更新',
		'log' => '系统log记录',
		'space' => '用户管理',
		'feed' => '动态(feed)',
		'share' => '分享',
		'blog' => '日志',
		'album' => '相册',
		'pic' => '图片',
		'comment' => '评论/留言',
		'thread' => '话题',
		'post' => '回帖',
		'doing' => '记录',
		'tag' => '标签',
		'mtag' => '群组',
		'poll' => '投票',
		'event' => '活动',
		'magiclog' => '道具记录',
		'report' => '举报',
		'block' => '数据调用',
		'template' => '模板编辑',
		'backup' => '数据备份',
		'stat' => '统计更新',
		'cron' => '系统计划任务',
		'click' => '表态动作',
		'ip' => '访问IP设置',
		'hotuser' => '推荐成员设置',
		'defaultuser' => '默认好友设置',
	); ?>
<?php $_TPL['nosidebar'] = 1; ?>
<?php if(empty($_SGLOBAL['inajax'])) { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$_SC['charset']?>" />
<meta http-equiv="x-ua-compatible" content="ie=7" />
<title><?php if($_TPL['titles']) { ?><?php if(is_array($_TPL['titles'])) { foreach($_TPL['titles'] as $value) { ?><?php if($value) { ?><?=$value?> - <?php } ?><?php } } ?><?php } ?><?php if($_SN[$space['uid']]) { ?><?=$_SN[$space['uid']]?> - <?php } ?><?=$_SCONFIG['sitename']?> - Powered by UCenter Home</title>
<script language="javascript" type="text/javascript" src="source/script_cookie.js"></script>
<script language="javascript" type="text/javascript" src="source/script_common.js"></script>
<script language="javascript" type="text/javascript" src="source/script_menu.js"></script>
<script language="javascript" type="text/javascript" src="source/script_ajax.js"></script>
<script language="javascript" type="text/javascript" src="source/script_face.js"></script>
<script language="javascript" type="text/javascript" src="source/script_manage.js"></script>
<style type="text/css">
@import url(template/default/style.css);
<?php if($_TPL['css']) { ?>
@import url(template/default/<?=$_TPL['css']?>.css);
<?php } ?>
<?php if(!empty($_SGLOBAL['space_theme'])) { ?>
@import url(theme/<?=$_SGLOBAL['space_theme']?>/style.css);
<?php } elseif($_SCONFIG['template'] != 'default') { ?>
@import url(template/<?=$_SCONFIG['template']?>/style.css);
<?php } ?>
<?php if(!empty($_SGLOBAL['space_css'])) { ?>
<?=$_SGLOBAL['space_css']?>
<?php } ?>
</style>
<link type="text/css" rel="stylesheet" href="template/default/uploadfile.css"></link>
<link rel="shortcut icon" href="image/favicon.ico" />
<link rel="edituri" type="application/rsd+xml" title="rsd" href="xmlrpc.php?rsd=<?=$space['uid']?>" />
</head>
<body>

<div id="append_parent"></div>
<div id="ajaxwaitid"></div>
<div id="header">
<?php if($_SGLOBAL['ad']['header']) { ?><div id="ad_header"><?php adshow('header'); ?></div><?php } ?>
<div class="headerwarp">
<h1 class="logo"><a href="index.php"><img src="template/<?=$_SCONFIG['template']?>/image/logo.gif" alt="<?=$_SCONFIG['sitename']?>" /></a></h1>
<ul class="menu">
<?php if($_SGLOBAL['supe_uid']) { ?>
<li><a href="space.php?do=home">首页</a></li>
<li><a href="space.php">个人主页</a></li>
<li><a href="space.php?do=friend">好友</a></li>
<li><a href="network.php">随便看看</a></li>
<?php } else { ?>
<li><a href="index.php">首页</a></li>
<?php } ?>

<?php if($_SGLOBAL['appmenu']) { ?>
<?php if($_SGLOBAL['appmenus']) { ?>
<li class="dropmenu" id="ucappmenu" onclick="showMenu(this.id)">
<a href="javascript:;">站内导航</a>
</li>
<?php } else { ?>
<li><a target="_blank" href="<?=$_SGLOBAL['appmenu']['url']?>" title="<?=$_SGLOBAL['appmenu']['name']?>"><?=$_SGLOBAL['appmenu']['name']?></a></li>
<?php } ?>
<?php } ?>
</ul>

<div class="nav_account">
<?php if($_SGLOBAL['supe_uid']) { ?>
<a href="space.php?uid=<?=$_SGLOBAL['supe_uid']?>" class="login_thumb"><?php echo avatar($_SGLOBAL[supe_uid]); ?></a>
<a href="space.php?uid=<?=$_SGLOBAL['supe_uid']?>" class="loginName"><?=$_SN[$_SGLOBAL['supe_uid']]?></a>
<?php if($_SGLOBAL['member']['credit']) { ?>
<a href="cp.php?ac=credit" style="font-size:11px;padding:0 0 0 5px;"><img src="image/credit.gif"><?=$_SGLOBAL['member']['credit']?></a>
<?php } ?>
<br />
<?php if(empty($_SCONFIG['closeinvite'])) { ?>
<a href="cp.php?ac=invite">邀请</a> 
<?php } ?>
<a href="cp.php?ac=task">任务</a> 
<a href="cp.php?ac=magic">道具</a>
<a href="cp.php">设置</a> 
<a href="cp.php?ac=common&op=logout&uhash=<?=$_SGLOBAL['uhash']?>">退出</a>
<?php } else { ?>
<a href="do.php?ac=<?=$_SCONFIG['register_action']?>" class="login_thumb"><?php echo avatar($_SGLOBAL[supe_uid]); ?></a>
欢迎您<br>
<a href="do.php?ac=<?=$_SCONFIG['login_action']?>">登录</a> | 
<a href="do.php?ac=<?=$_SCONFIG['register_action']?>">注册</a>
<?php } ?>
</div>
</div>
</div>

<div id="wrap">

<?php if(empty($_TPL['nosidebar'])) { ?>
<div id="main">
<div id="app_sidebar">
<?php if($_SGLOBAL['supe_uid']) { ?>
<ul class="app_list" id="default_userapp">
<li><img src="image/app/doing.gif"><a href="space.php?do=doing">记录</a></li>
<li><img src="image/app/album.gif"><a href="space.php?do=album">相册</a><em><a href="cp.php?ac=upload" class="gray">上传</a></em></li>
<li><img src="image/app/blog.gif"><a href="space.php?do=blog">日志</a><em><a href="cp.php?ac=blog" class="gray">发表</a></em></li>
<li><img src="image/app/poll.gif"/><a href="space.php?do=poll">投票</a><em><a href="cp.php?ac=poll" class="gray">发起</a></em></li>
<li><img src="image/app/mtag.gif"><a href="space.php?do=mtag">群组</a><em><a href="cp.php?ac=thread" class="gray">话题</a></em></li>
<li><img src="image/app/event.gif"/><a href="space.php?do=event">活动</a><em><a href="cp.php?ac=event" class="gray">发起</a></em></li>
<li><img src="image/app/share.gif"><a href="space.php?do=share">分享</a></li>
<li><img src="image/app/topic.gif"><a href="space.php?do=topic">热闹</a></li>
</ul>

<ul class="app_list topline" id="my_defaultapp">
<?php if($_SCONFIG['my_status']) { ?>
<?php if(is_array($_SGLOBAL['userapp'])) { foreach($_SGLOBAL['userapp'] as $value) { ?>
<li><img src="http://appicon.manyou.com/icons/<?=$value['appid']?>"><a href="userapp.php?id=<?=$value['appid']?>"><?=$value['appname']?></a></li>
<?php } } ?>
<?php } ?>
</ul>

<?php if($_SCONFIG['my_status']) { ?>
<ul class="app_list topline" id="my_userapp">
<?php if(is_array($_SGLOBAL['my_menu'])) { foreach($_SGLOBAL['my_menu'] as $value) { ?>
<li id="userapp_li_<?=$value['appid']?>"><img src="http://appicon.manyou.com/icons/<?=$value['appid']?>"><a href="userapp.php?id=<?=$value['appid']?>" title="<?=$value['appname']?>"><?=$value['appname']?></a></li>
<?php } } ?>
</ul>
<?php } ?>

<?php if($_SGLOBAL['my_menu_more']) { ?>
<p class="app_more"><a href="javascript:;" id="a_app_more" onclick="userapp_open();" class="off">展开</a></p>
<?php } ?>

<?php if($_SCONFIG['my_status']) { ?>
<div class="app_m">
<ul>
<li><img src="image/app_add.gif"><a href="cp.php?ac=userapp&my_suffix=%2Fapp%2Flist" class="addApp">添加应用</a></li>
<li><img src="image/app_set.gif"><a href="cp.php?ac=userapp&op=menu" class="myApp">管理应用</a></li>
</ul>
</div>
<?php } ?>

<?php } else { ?>
<div class="bar_text">
<form id="loginform" name="loginform" action="do.php?ac=<?=$_SCONFIG['login_action']?>&ref" method="post">
<input type="hidden" name="formhash" value="<?php echo formhash(); ?>" />
<p class="title">登录站点</p>
<p>用户名</p>
<p><input type="text" name="username" id="username" class="t_input" size="15" value="" /></p>
<p>密码</p>
<p><input type="password" name="password" id="password" class="t_input" size="15" value="" /></p>
<p><input type="checkbox" id="cookietime" name="cookietime" value="315360000" checked /><label for="cookietime">记住我</label></p>
<p>
<input type="submit" id="loginsubmit" name="loginsubmit" value="登录" class="submit" />
<input type="button" name="regbutton" value="注册" class="button" onclick="urlto('do.php?ac=<?=$_SCONFIG['register_action']?>');">
</p>
</form>
</div>
<?php } ?>
</div>

<div id="mainarea">

<?php if($_SGLOBAL['ad']['contenttop']) { ?><div id="ad_contenttop"><?php adshow('contenttop'); ?></div><?php } ?>
<?php } ?>

<?php } ?>


<style type="text/css">
@import url(admin/tpl/style.css);
</style>

<div id="cp_content">


<div class="mainarea">
<div class="maininner">

<div class="tabs_header">
<ul class="tabs">
<li <?=$actives['view']?>><a href="admincp.php?ac=goodsstore"><span>商品库</span></a></li>
<li class="null"><a href="admincp.php?ac=goodsstore&op=add">添加新商品</a></li>
<li style="padding-left: 10px">
<form action="admincp.php?ac=goodsstore&op=upload" method="post" enctype="multipart/form-data">
<input type="file" name="file" id="file" class="t_input"/>
<input type="submit" name="uploadsubmit" value="导入商品表" class="submit">
</form>
</li>
</ul>
</div>
<?php if($list ) { ?>

<?php $_TPL['type'] = array(
                        '0' => '无动作',
                        '1' => '指定页面',
                        '2' => '指定网页',
                '3' => 'QQ群'
                ); ?>
<form method="get" action="admincp.php" name="searchcondition">
<div class="block style4">
<table cellspacing="3" cellpadding="2">
<tr><th>商品ＩＤ</th><td><input type="text" name="goodsid" value="<?=$_GET['goodsid']?>"></td>
　　　　<th>平台ＩＤ</th><td><input type="text" name="id" value="<?=$_GET['id']?>"></td>
　　<th>平台类型</th><td><input type="text" name="platformname" value="<?=$_GET['platformname']?>"></td>
</tr>
<tr><th>店铺名称</th><td><input type="text" name="shopname" value="<?=$_GET['shopname']?>"></td>
　　　　<th>商品名称</th><td><input type="text" name="goodsname" value="<?=$_GET['goodsname']?>"></td>
　　<th>商品分类</th><td><input type="text" name="goodscategoryid" value="<?=$_GET['goodscategoryid']?>"></td>
</tr>
<tr><th>商品价格</th>
<td colspan="3">
<input type="text" name="goodsamount1" value="<?=$_GET['goodsamount1']?>" size="10"> ~
<input type="text" name="goodsamount2" value="<?=$_GET['goodsamount2']?>" size="10"> (元)
</td>
<th>精选</th><td >
<select name="fine">
<option value="" selected="selected">不限制</option>
<option value="0"<?php if($_GET['fine']==='0') { ?> selected<?php } ?>>否</option>
<option value="1"<?php if($_GET['fine']==1) { ?> selected<?php } ?>>是</option>
</select>
</td>
</tr>
<tr><th>月销量</th>
<td colspan="3">
<input type="text" name="goodsmonthcount1" value="<?=$_GET['goodsmonthcount1']?>" size="10"> ~
<input type="text" name="goodsmonthcount2" value="<?=$_GET['goodsmonthcount2']?>" size="10"> (件)
</td>
<th>置顶</th><td >
<select name="top">
<option value="" selected="selected">不限制</option>
<option value="0"<?php if($_GET['top']==='0') { ?> selected<?php } ?>>否</option>
<option value="1"<?php if($_GET['top']==1) { ?> selected<?php } ?>>是</option>
</select>
</td>
</tr>
<tr><th>优惠券金额</th>
<td colspan="3">
<input type="text" name="coupontamountvalue1" value="<?=$_GET['coupontamountvalue1']?>" size="10"> ~
<input type="text" name="coupontamountvalue2" value="<?=$_GET['coupontamountvalue2']?>" size="10">
</td>
<th>优惠券可用</th><td >
<select name="coupon">
<option value="" selected="selected">不限制</option>
<option value="1"<?php if($_GET['coupon']==1) { ?> selected<?php } ?>>否</option>
<option value="2"<?php if($_GET['coupon']==2) { ?> selected<?php } ?>>是</option>
</select>
</td>
</tr>
<tr><th>佣金</th>
<td colspan="3">
<input type="text" name="goodscommission1" value="<?=$_GET['goodscommission1']?>" size="10"> ~
<input type="text" name="goodscommission2" value="<?=$_GET['goodscommission2']?>" size="10">
</td>
<th>是否上架</th><td >
<select name="status">
<option value="" selected="selected">不限制</option>
<option value="0"<?php if($_GET['status']==='0') { ?> selected<?php } ?>>否</option>
<option value="1"<?php if($_GET['status']==1) { ?> selected<?php } ?>>是</option>
</select>
</td>
</tr>
<tr><th>收入比率</th>
<td colspan="3">
<input type="text" name="goodsrate1" value="<?=$_GET['goodsrate1']?>" size="10"> ~
<input type="text" name="goodsrate2" value="<?=$_GET['goodsrate2']?>" size="10">
</td>
</tr>
<tr><th>自销量</th>
<td colspan="3">
<input type="text" name="sellcount1" value="<?=$_GET['sellcount1']?>" size="10"> ~
<input type="text" name="sellcount2" value="<?=$_GET['sellcount2']?>" size="10">
</td>
</tr>
<tr><th>结果排序</th>
<td colspan="3">
<select name="orderby">
<option value="">默认排序</option>
<option value="goodsamount"<?=$orderby['goodsamount']?>>商品价格</option>
<option value="coupontamountvalue"<?=$orderby['coupontamountvalue']?>>优惠券面额</option>
<option value="goodscommission"<?=$orderby['goodscommission']?>>佣金</option>
<option value="goodsrate"<?=$orderby['goodsrate']?>>收入比率</option>
<option value="goodsmonthcount"<?=$orderby['goodsmonthcount']?>>月销量</option>
</select>
<select name="ordersc">
<option value="desc"<?=$ordersc['desc']?>>递减</option>
<option value="asc"<?=$ordersc['asc']?>>递增</option>
</select>
<select name="perpage">
<option value="20"<?=$perpages['20']?>>每页显示20个</option>
<option value="50"<?=$perpages['50']?>>每页显示50个</option>
<option value="100"<?=$perpages['100']?>>每页显示100个</option>
<option value="200"<?=$perpages['200']?>>每页显示200个</option>
<!--<option value="2000"<?=$perpages['2000']?>>每页显示2000个</option>-->
</select>
<input type="hidden" name="ac" value="goodsstore" />
<input type="submit" name="searchsubmit" value="搜索" class="submit" />
</td>
</tr>
</table>
</div>
</form>

<form method="post" action="admincp.php?ac=goodsstore">
<input type="hidden" name="formhash" value="<?php echo formhash(); ?>" />
<div class="footactions" style="border-top:1px solid #ff8e00;">
<div class="pages"><?=$multi?></div>
</div>
<div class="bdrcontent">

<div class="title">
<h3>商品库</h3>
</div>

<table cellspacing="0" cellpadding="0" class="formtable">
<tr>
<th width="40" style="text-align:center">平台ID</th>
<th width="120">商品信息</th>
<th width="30">商品ID</th>
<th width="20"  style="text-align:center">单价</th>
    <th width="20"  style="text-align:center">佣金</th>
    <th width="30"  style="text-align:center">收入比率</th>
    <th width="30"  style="text-align:center">优惠券</th>
<th width="30"  style="text-align:center">月销量</th>
    <th width="20"  style="text-align:center">自销量</th>
    <th width="40"  style="text-align:center">标签</th>
<th width="40"  style="text-align:center">操作</th>
</tr>
<?php if(is_array($list)) { foreach($list as $value) { ?>
<tr>
<td  style="text-align:center"><input type="<?php if($allowbatch) { ?>checkbox<?php } else { ?>radio<?php } ?>" name="ids[]" value="<?=$value['id']?>"><?=$value['id']?></td>
<td>
<a href="javascript:;" data-original-title="" title="" style="float: left">
<img src="<?=$value['goodsimgurl']?>" style="width:80px; height:80px">
</a>
<div class="col-md-2 col-sm-4 col-xs-12" style="width:70%; text-align:left; float: left;padding-left: 10px;margin: 0;">
<li style="list-style-type:none;"><small><?=$value['goodsname']?></small></li>
<li style="list-style-type:none; font-style:oblique;"><h5><?=$value['goodscategoryid']?></h5></li>
<li style="list-style-type:none; font-style:oblique;"><h6> <small><?=$value['shopname']?></small></h6><small style="float: right;font-style:normal"><?=$value['platformname']?></small></li>
</div>
</td>
<td> <?=$value['goodsid']?></td>
<td  style="text-align:center">￥  <?=$value['goodsamount']?></td>
<td style="color: #db3652;text-align:center;font-size: small">￥  <?=$value['goodscommission']['0']?>.<small style="font-size: xx-small"><?=$value['goodscommission']['1']?></small></td>
<td  style="text-align:center"> <?=$value['goodsrate']?> %</td>
    <td style="text-align:center"> <?=$value['coupontamount']?></td>
<td  style="text-align:center"> <?=$value['goodsmonthcount']?></td>
    <td  style="text-align:center"> <?=$value['sellcount']?></td>
<td  style="text-align:center">
<?php if($value['fine'] == 1) { ?>精选 <?php } ?>
<?php if($value['top'] == 1) { ?>| 置顶<?php } ?>
</td>
<td style="text-align:center">
<a href="admincp.php?ac=goodsstore&op=edit&id=<?=$value['id']?>">管理</a>
<?php if($value['status'] == 1) { ?>
<a href="admincp.php?ac=goodsstore&op=editdisavailable&id=<?=$value['id']?>">下架</a>
<?php } else { ?>
<a href="admincp.php?ac=goodsstore&op=editavailable&id=<?=$value['id']?>">上架</a>
<?php } ?>
</td>
</tr>
<?php } } ?>
</table>
</div>
<div class="footactions">
<input type="hidden" name="set[id]" value="<?=$thevalue['id']?>">
<input type="hidden" name="mpurl" value="<?=$mpurl?>">
<input type="hidden" name="page" value="<?=$_GET['page']?>">
<input type="checkbox" id="chkall" name="chkall" onclick="checkAll(this.form, 'ids')">全选
<input type="radio"  name="optype" value="fine">精选
<input type="radio"  name="optype" value="top">置顶
<input type="radio"  name="optype" value="available">上架
&nbsp;&nbsp;
<input type="radio"  name="optype" value="nofine">取消精选
<input type="radio"  name="optype" value="notop">取消置顶
<input type="radio"  name="optype" value="unavailable">下架

<input type="submit" name="opsubmit" value="提交" class="submit">
<span name="tips" style="color:red;font-weight:bold;"></span>
<div class="pages"><?=$multi?></div>
</div>
</form>
<?php } else { ?>
<div class="bdrcontent">
<p>指定条件下还没有数据</p>
</div>
<?php } ?>

<?php if($thevalue) { ?>
<script type="text/javascript">
            function credisshow(value) {
/*if(value=='0') {
 document.getElementById('tr_credit').style.display = 'none';
 } else {
 document.getElementById('tr_credit').style.display = '';
 }*/
            }
</script>

<?php $_TPL['type'] = array(
                        '0' => '无动作',
                        '1' => '指定页面',
                        '2' => '指定网页',
                '3' => 'QQ群',
                ); ?>

<form method="post" action="admincp.php?ac=goodsstore&id=<?=$thevalue['id']?>">
<input type="hidden" name="formhash" value="<?php echo formhash(); ?>" />

<div class="bdrcontent">

<div class="title">
<h3><?=$thevalue['title']?> 商品信息</h3>
<p>在这里编辑商品库中商品具体信息</p>
</div>

<table cellspacing="0" cellpadding="0" class="formtable">
<tr><th style="width:12em;">商品ＩＤ</th><td><input  size="20" type="text" name="set[goodsid]" value="<?=$thevalue['goodsid']?>"></td></tr>
<tr><th style="width:12em;" >商品名称</th><td><input size="60" type="text" name="set[goodsname]" value="<?=$thevalue['goodsname']?>"></td></tr>
<tr><th style="width:12em;">商品主图</th><td><input size="60" type="text" name="set[goodsimgurl]" value="<?=$thevalue['goodsimgurl']?>"></td></tr>
<tr><th style="width:12em;">商品详情</th><td><input size="60" type="text" name="set[goodsdefaulturl]" value="<?=$thevalue['goodsdefaulturl']?>"></td></tr>
<tr><th style="width:12em;">商品一级分类</th><td><input size="40" type="text" name="set[goodscategoryid]" value="<?=$thevalue['goodscategoryid']?>"></td></tr>
<tr><th style="width:12em;">淘宝客链接</th><td><input size="60" type="text" name="set[goodstaobaokeurl]" value="<?=$thevalue['goodstaobaokeurl']?>"></td></tr>
<tr><th style="width:12em;">商品价格(单位：元)</th><td><input size="20" type="text" name="set[goodsamount]" value="<?=$thevalue['goodsamount']?>"></td></tr>
<tr><th style="width:12em;">商品月销量</th><td><input type="text" name="set[goodsmonthcount]" value="<?=$thevalue['goodsmonthcount']?>"></td></tr>
<tr><th style="width:12em;">收入比率(%)</th><td><input type="text" name="set[goodsrate]" value="<?=$thevalue['goodsrate']?>"></td></tr>
<tr><th style="width:12em;">佣金</th><td><input type="text" name="set[goodscommission]" value="<?=$thevalue['goodscommission']?>"></td></tr>
<tr><th style="width:12em;">卖家旺旺</th><td><input type="text" name="set[sellerwangid]" value="<?=$thevalue['sellerwangid']?>"></td></tr>
<tr><th style="width:12em;">卖家ID</th><td><input type="text" name="set[sellerid]" value="<?=$thevalue['sellerid']?>"></td></tr>
<tr><th style="width:12em;">店铺名称</th><td><input type="text" name="set[shopname]" value="<?=$thevalue['shopname']?>"></td></tr>
<tr><th style="width:12em;">平台类型</th><td><input type="text" name="set[platformname]" value="<?=$thevalue['platformname']?>"></td></tr>
<tr><th style="width:12em;">优惠券ID</th><td><input type="text" name="set[couponid]" value="<?=$thevalue['couponid']?>"></td></tr>
<tr><th style="width:12em;">优惠券剩余</th><td><input type="text" name="set[couponcountuse]" value="<?=$thevalue['couponcountuse']?>"></td></tr>
<tr><th style="width:12em;">优惠券面额</th><td><input type="text" name="set[coupontamount]" value="<?=$thevalue['coupontamount']?>"></td></tr>
<tr><th style="width:12em;">优惠券开始时间</th><td><input type="text" name="set[couponstarttime]" value="<?=$thevalue['couponstarttime']?>"></td></tr>
<tr><th style="width:12em;">优惠券结束时间</th><td><input type="text" name="set[couponendtime]" value="<?=$thevalue['couponendtime']?>"></td></tr>
<tr><th style="width:12em;">优惠券链接</th><td><input size="60" type="text" name="set[couponurl]" value="<?=$thevalue['couponurl']?>"></td></tr>
<tr><th style="width:12em;">优惠券推广链接</th><td><input size="60" type="text" name="set[coupontuiguangurl]" value="<?=$thevalue['coupontuiguangurl']?>"></td></tr>
</table>
<div class="footactions">
<input type="hidden" name="id" value="<?=$thevalue['id']?>">
<input type="submit" name="thevaluesubmit" value="提交" class="submit">
</div>
</div>
</form>
<?php } ?>
</div>
</div>

<div class="side">
<?php if($menus['0']) { ?>
<div class="block style1">
<h2>基本设置</h2>
<ul class="folder">
<li class="active"><a href="admincp.php?ac=messagelog">发送信息</a></li>
                <li class="active"><a href="admincp.php?ac=messagerule">触发信息</a></li>
<li class="active"><a href="admincp.php?ac=banner">Banner系统</a></li>
                <li class="active"><a href="admincp.php?ac=subject">话题管理-晒图</a></li>
<li class="active"><a href="admincp.php?ac=dashenbang">大神榜管理-本周</a></li>
<li class="active"><a href="admincp.php?ac=moneytype">福利-赚奖金方式</a></li>
<li class="active"><a href="admincp.php?ac=moneylog">福利-赚取和消耗记录</a></li>
 	<li class="active"><a href="admincp.php?ac=moneylogb">奖金记录统计</a></li>
<li class="active"><a href="admincp.php?ac=creditstore&available=1">商城&任务列表</a></li>
<li class="active"><a href="admincp.php?ac=creditlist">积分记录</a></li>
<li class="active"><a href="admincp.php?ac=moneylist">奖金记录</a></li>
<li class="active"><a href="admincp.php?ac=autoaward">发奖记录</a></li>
        <li class="active"><a href="admincp.php?ac=taskmanage">分发统计管理</a></li>
        <li class="active"><a href="admincp.php?ac=channel">渠道管理</a></li>
        <li class="active"><a href="admincp.php?ac=productanalyze">签到用户数据分析</a></li>
        <li class="active"><a href="admincp.php?ac=notemanage">短信管理</a></li>
        <li class="active"><a href="admincp.php?ac=moneyrange">金额范围限制</a></li>
<li class="active"><a href="admincp.php?ac=goodsstore">商品库</a></li>
<li class="active"><a href="admincp.php?ac=goodscategory">商品分类方式</a></li>
<li class="active"><a href="admincp.php?ac=goodsplatform">平台管理商品</a></li>
<li class="active"><a href="admincp.php?ac=goodsorder">订单管理</a></li>
<?php if(is_array($acs['0'])) { foreach($acs['0'] as $value) { ?>
<?php if($menus['0'][$value]) { ?>
<?php if($ac==$value) { ?><li class="active"><?php } else { ?><li><?php } ?><a href="admincp.php?ac=<?=$value?>"><?=$_TPL['menunames'][$value]?></a></li>
<?php } ?>
<?php } } ?>
</ul>
</div>
<?php } ?>

<div class="block style1">
<h2>批量管理</h2>
<ul class="folder">
<?php if(is_array($acs['3'])) { foreach($acs['3'] as $value) { ?>
<?php if($ac==$value) { ?><li class="active"><?php } else { ?><li><?php } ?><a href="admincp.php?ac=<?=$value?>"><?=$_TPL['menunames'][$value]?></a></li>
<?php } } ?>
<?php if(is_array($acs['1'])) { foreach($acs['1'] as $value) { ?>
<?php if($menus['1'][$value]) { ?>
<?php if($ac==$value) { ?><li class="active"><?php } else { ?><li><?php } ?><a href="admincp.php?ac=<?=$value?>"><?=$_TPL['menunames'][$value]?></a></li>
<?php } ?>
<?php } } ?>
</ul>
</div>

<?php if($menus['2']) { ?>
<div class="block style1">
<h2>高级设置</h2>
<ul class="folder">
<li class="active"><a href="admincp.php?ac=batch">批量招贴</a></li>
<?php if(is_array($acs['2'])) { foreach($acs['2'] as $value) { ?>
<?php if($menus['2'][$value]) { ?>
<?php if($ac==$value) { ?><li class="active"><?php } else { ?><li><?php } ?><a href="admincp.php?ac=<?=$value?>"><?=$_TPL['menunames'][$value]?></a></li>
<?php } ?>
<?php } } ?>
<?php if($menus['0']['config']) { ?><li><a href="<?=UC_API?>" target="_blank">UCenter</a></li><?php } ?>
</ul>
</div>
<?php } ?>

</div>

</div>

<?php if(empty($_SGLOBAL['inajax'])) { ?>
<?php if(empty($_TPL['nosidebar'])) { ?>
{if <?=$_SGLOBAL['ad']['contentbottom']?>}<br style="line-height:0;clear:both;"/><div id="ad_contentbottom"><?php adshow('contentbottom'); ?></div><?php } ?>
</div>

<!--/mainarea-->
<div id="bottom"></div>
</div>
<!--/main-->
<?php } ?>

<div id="footer">
<?php if($_TPL['templates']) { ?>
<div class="chostlp" title="切换风格"><img id="chostlp" src="<?=$_TPL['default_template']['icon']?>" onmouseover="showMenu(this.id)" alt="<?=$_TPL['default_template']['name']?>" /></div>
<ul id="chostlp_menu" class="chostlp_drop" style="display: none">
<?php if(is_array($_TPL['templates'])) { foreach($_TPL['templates'] as $value) { ?>
<li><a href="cp.php?ac=common&op=changetpl&name=<?=$value['name']?>" title="<?=$value['name']?>"><img src="<?=$value['icon']?>" alt="<?=$value['name']?>" /></a></li>
<?php } } ?>
</ul>
<?php } ?>

<p class="r_option">
<a href="javascript:;" onclick="window.scrollTo(0,0);" id="a_top" title="TOP"><img src="image/top.gif" alt="" style="padding: 5px 6px 6px;" /></a>
</p>

<?php if($_SGLOBAL['ad']['footer']) { ?>
<p style="padding:5px 0 10px 0;"><?php adshow('footer'); ?></p>
<?php } ?>

<?php if($_SCONFIG['close']) { ?>
<p style="color:blue;font-weight:bold;">
提醒：当前站点处于关闭状态
</p>
<?php } ?>
<p>
<?=$_SCONFIG['sitename']?> - 
<a href="mailto:<?=$_SCONFIG['adminemail']?>">联系我们</a>
<?php if($_SCONFIG['miibeian']) { ?> - <a  href="http://www.miibeian.gov.cn" target="_blank"><?=$_SCONFIG['miibeian']?></a><?php } ?>
</p>
<p>
Powered by <a  href="http://u.discuz.net" target="_blank"><strong>UCenter Home</strong></a> <span title="<?php echo X_RELEASE; ?>"><?php echo X_VER; ?></span>
<?php if(!empty($_SCONFIG['licensed'])) { ?><a  href="http://license.comsenz.com/?pid=7&host=<?=$_SERVER['HTTP_HOST']?>" target="_blank">Licensed</a><?php } ?>
&copy; 2001-2010 <a  href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>
</p>
<?php if($_SCONFIG['debuginfo']) { ?>
<p><?php echo debuginfo(); ?></p>
<?php } ?>
</div>
</div>
<!--/wrap-->

<?php if($_SGLOBAL['appmenu']) { ?>
<ul id="ucappmenu_menu" class="dropmenu_drop" style="display:none;">
<li><a href="<?=$_SGLOBAL['appmenu']['url']?>" title="<?=$_SGLOBAL['appmenu']['name']?>" target="_blank"><?=$_SGLOBAL['appmenu']['name']?></a></li>
<?php if(is_array($_SGLOBAL['appmenus'])) { foreach($_SGLOBAL['appmenus'] as $value) { ?>
<li><a href="<?=$value['url']?>" title="<?=$value['name']?>" target="_blank"><?=$value['name']?></a></li>
<?php } } ?>
</ul>
<?php } ?>

<?php if($_SGLOBAL['supe_uid']) { ?>
<ul id="membernotemenu_menu" class="dropmenu_drop" style="display:none;">
<?php $member = $_SGLOBAL['member']; ?>
<?php if($member['notenum']) { ?><li><img src="image/icon/notice.gif" width="16" alt="" /> <a href="space.php?do=notice"><strong><?=$member['notenum']?></strong> 个新通知</a></li><?php } ?>
<?php if($member['pokenum']) { ?><li><img src="image/icon/poke.gif" alt="" /> <a href="cp.php?ac=poke"><strong><?=$member['pokenum']?></strong> 个新招呼</a></li><?php } ?>
<?php if($member['addfriendnum']) { ?><li><img src="image/icon/friend.gif" alt="" /> <a href="cp.php?ac=friend&op=request"><strong><?=$member['addfriendnum']?></strong> 个好友请求</a></li><?php } ?>
<?php if($member['mtaginvitenum']) { ?><li><img src="image/icon/mtag.gif" alt="" /> <a href="cp.php?ac=mtag&op=mtaginvite"><strong><?=$member['mtaginvitenum']?></strong> 个群组邀请</a></li><?php } ?>
<?php if($member['eventinvitenum']) { ?><li><img src="image/icon/event.gif" alt="" /> <a href="cp.php?ac=event&op=eventinvite"><strong><?=$member['eventinvitenum']?></strong> 个活动邀请</a></li><?php } ?>
<?php if($member['myinvitenum']) { ?><li><img src="image/icon/userapp.gif" alt="" /> <a href="space.php?do=notice&view=userapp"><strong><?=$member['myinvitenum']?></strong> 个应用消息</a></li><?php } ?>
</ul>
<?php } ?>

<?php if($_SGLOBAL['supe_uid']) { ?>

<?php if($_SGLOBAL['ad']['couplet']) { ?>
<script language="javascript" type="text/javascript" src="source/script_couplet.js"></script>
<div id="uch_couplet" style="z-index: 10; position: absolute; display:none">
<div id="couplet_left" style="position: absolute; left: 2px; top: 60px; overflow: hidden;">
<div style="position: relative; top: 25px; margin:0.5em;" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('uch_couplet');"><img src="image/advclose.gif"></div>
<?php adshow('couplet'); ?>
</div>
<div id="couplet_rigth" style="position: absolute; right: 2px; top: 60px; overflow: hidden;">
<div style="position: relative; top: 25px; margin:0.5em;" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('uch_couplet');"><img src="image/advclose.gif"></div>
<?php adshow('couplet'); ?>
</div>
<script type="text/javascript">
lsfloatdiv('uch_couplet', 0, 0, '', 0).floatIt();
</script>
</div>
<?php } ?>
<?php if($_SCOOKIE['reward_log']) { ?>
<script type="text/javascript">
showreward();
</script>
<?php } ?>
</body>
</html>
<?php } ?>
<?php ob_out();?>