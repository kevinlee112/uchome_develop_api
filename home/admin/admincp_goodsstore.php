<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/5
 * Time: 11:53
 */

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
//权限
if(!checkperm('managetag')) {
    cpmessage('no_authority_management_operation');
}

if(submitcheck('formhash')) {
    //验证是否有批量操作的权限
    $allowmanage = checkperm('managetag');
    $managebatch = checkperm('managebatch');
    $newids = array();
    $opnum = 0;

    if ($_POST['optype'] == 'fine')
    {
        foreach ($_POST['ids'] as $fineid)
        {
            updatetable('goodsstore', array('fine'=>1), array('id'=>$fineid));
        }
    }
    elseif ($_POST['optype'] == 'top')
    {
        foreach ($_POST['ids'] as $topid)
        {
            updatetable('goodsstore', array('top'=>1), array('id'=>$topid));
        }
    }
    elseif ($_POST['optype'] == 'nofine')
    {
        foreach ($_POST['ids'] as $fineid)
        {
            updatetable('goodsstore', array('fine'=>0), array('id'=>$fineid));
        }
    }
    elseif ($_POST['optype'] == 'notop')
    {
        foreach ($_POST['ids'] as $topid)
        {
            updatetable('goodsstore', array('top'=>0), array('id'=>$topid));
        }
    }
    elseif ($_POST['optype'] == 'available')
    {
        foreach ($_POST['ids'] as $availableid)
        {
            updatetable('goodsstore', array('status'=>1), array('id'=>$availableid));
        }

    }
    elseif ($_POST['optype'] == 'unavailable')
    {
        foreach ($_POST['ids'] as $unavailable)
        {
            updatetable('goodsstore', array('status'=>0), array('id'=>$unavailable));
        }

    }
    cpmessage('do_success',$_POST['mpurl']."&page=".$_POST['page']);
}

$thevalue = $list = array();
if(submitcheck('thevaluesubmit')) {
    $perms = array_keys($_POST['set']);
    $nones = array('id');
    foreach ($perms as $value) {
        $_POST['set'][$value] = trim($_POST['set'][$value]);
        if($thevalue[$value] != $_POST['set'][$value]) {
            $setarr[$value] = $_POST['set'][$value];
        }
    }
    foreach ($nones as $value)
    {
        !empty($_POST[$value]) && $thevalue[$value] = $_POST[$value];
    }
    if(empty($thevalue['id'])) {
        //添加
        $setarr['couponstarttime'] = strtotime($setarr['couponstarttime']);
        $setarr['couponendtime'] = strtotime($setarr['couponendtime']);
        inserttable('goodsstore', $setarr);
    } else {
        //更新
        $setarr['couponstarttime'] = strtotime($setarr['couponstarttime']);
        $setarr['couponendtime'] = strtotime($setarr['couponendtime']);
        updatetable('goodsstore', $setarr, array('id'=>$thevalue['id']));
    }

    //更新缓存
    include_once(S_ROOT.'./source/function_cache.php');
    usergroup_cache();
    cpmessage('do_success', 'admincp.php?ac=goodsstore&op=edit&id='.$thevalue['id']);
}
include_once(S_ROOT.'./source/function_cp.php');


if ($_GET['op'] == 'add') {
    //添加
    $thevalue = array(
        'goodsid' => '', 'goodsname' => '', 'goodsimgurl' => '', 'goodsdefaulturl' => '', 'couponurl'=>'',
        'goodscategoryid' => '', 'goodstaobaokeurl' => '', 'goodsamount' => '', 'goodsmonthcount'=>'', 'coupontuiguangurl'=>'',
        'goodsrate'=>'', 'goodscommission'=>'', 'sellerwangid'=>'', 'sellerid'=>'', 'shopname'=>'', 'couponendtime'=>'',
        'platformname'=>'', 'couponid'=>'', 'couponcountuse'=>'', 'coupontamount'=>'', 'couponstarttime'=>''
    );
    //include_once(S_ROOT . "./data/data_magic.php");
}
elseif ($_GET['op'] == 'edit')
{
    $_GET['id'] = empty($_GET['id'])?0:intval($_GET['id']);
    if($_GET['id']) {
        $query = $_SGLOBAL['db']->query("SELECT *  FROM ".tname('goodsstore')." WHERE id='$_GET[id]'");
        if($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
            $thevalue['couponstarttime'] = date('Y-m-d', $thevalue['couponstarttime']);
            $thevalue['couponendtime'] = date('Y-m-d', $thevalue['couponendtime']);
        }
        else
        {
            cpmessage('user_credit_good_does_not_exist');
        }
    }
    //include_once(S_ROOT . "./data/data_magic.php");
}
elseif ($_GET['op'] == 'editavailable' && $_GET['id']) {
    //更新用户权限
    updatetable('goodsstore', array('status'=>1), array('id'=>$_GET['id']));
    cpmessage('do_success', 'admincp.php?ac=goodsstore');
} elseif ($_GET['op'] == 'editdisavailable' && $_GET['id']) {
    //更新用户权限
    updatetable('goodsstore', array('status'=>0), array('id'=>$_GET['id']));
    cpmessage('do_success', 'admincp.php?ac=goodsstore');
}
elseif ($_GET['op'] == 'upload')
{
    if ($_FILES["file"]["size"] < 1000000000)
    {
        $filename='goodslist.xls';
        error_reporting(E_ALL);
        set_time_limit(0);
        $result=move_uploaded_file($_FILES['file']['tmp_name'],$_SERVER['DOCUMENT_ROOT']."/home/upload/".$filename);//假如上传到当前目录下
        if($result)  //如果上传文件成功，就执行导入excel操作
        {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/home/uc_client/lib/phpExcelReader/Excel/reader.php';
            $data = new Spreadsheet_Excel_Reader();
            $data->setOutputEncoding('UTF-8');
            $data->read($_SERVER['DOCUMENT_ROOT']."/home/upload/".$filename);

            $style = 1;
            $data->sheets[0]['cells'][1][1] != "商品id" && $style =0;
            $data->sheets[0]['cells'][1][2] != "商品名称" && $style =0;
            $data->sheets[0]['cells'][1][3] != "商品主图" && $style =0;
            $data->sheets[0]['cells'][1][4] != "商品详情页链接地址" && $style =0;
            $data->sheets[0]['cells'][1][5] != "商品一级类目" && $style =0;
            $data->sheets[0]['cells'][1][6] != "淘宝客链接" && $style =0;
            $data->sheets[0]['cells'][1][7] != "商品价格(单位：元)" && $style =0;
            $data->sheets[0]['cells'][1][8] != "商品月销量" && $style =0;
            $data->sheets[0]['cells'][1][9] != "收入比率(%)" && $style =0;
            $data->sheets[0]['cells'][1][10] != "佣金" && $style =0;
            $data->sheets[0]['cells'][1][11] != "卖家旺旺" && $style =0;
            $data->sheets[0]['cells'][1][12] != "卖家id" && $style =0;
            $data->sheets[0]['cells'][1][13] != "店铺名称" && $style =0;
            $data->sheets[0]['cells'][1][14] != "平台类型" && $style =0;
            $data->sheets[0]['cells'][1][15] != "优惠券id" && $style =0;
            $data->sheets[0]['cells'][1][16] != "优惠券总量" && $style =0;
            $data->sheets[0]['cells'][1][17] != "优惠券剩余量" && $style =0;
            $data->sheets[0]['cells'][1][18] != "优惠券面额" && $style =0;
            $data->sheets[0]['cells'][1][19] != "优惠券开始时间" && $style =0;
            $data->sheets[0]['cells'][1][20] != "优惠券结束时间" && $style =0;
            $data->sheets[0]['cells'][1][21] != "优惠券链接" && $style =0;
            $data->sheets[0]['cells'][1][22] != "商品优惠券推广链接" && $style =0;
            if (empty($style))
            {
                cpmessage('订单文件格式不正确', 'admincp.php?ac=goodsorder');
            }
            for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
                $setarr = array(
                    'goodsid'=> $data->sheets[0]['cells'][$i][1],
                    'goodsname'=>addslashes($data->sheets[0]['cells'][$i][2]),
                    'goodsimgurl'=>$data->sheets[0]['cells'][$i][3]."_310x310.jpg_.webp",
                    'goodsdefaulturl'=>$data->sheets[0]['cells'][$i][4],
                    'goodscategoryid'=>$data->sheets[0]['cells'][$i][5],
                    'goodstaobaokeurl'=>$data->sheets[0]['cells'][$i][6],
                    'goodsamount'=>$data->sheets[0]['cells'][$i][7],
                    'goodsmonthcount'=>$data->sheets[0]['cells'][$i][8],
                    'goodsrate'=>$data->sheets[0]['cells'][$i][9],
                    'goodscommission'=>$data->sheets[0]['cells'][$i][10],
                    'sellerwangid'=>$data->sheets[0]['cells'][$i][11],
                    'sellerid'=>$data->sheets[0]['cells'][$i][12],
                    'shopname'=>addslashes($data->sheets[0]['cells'][$i][13]),
                    'platformname'=>$data->sheets[0]['cells'][$i][14],
                    'couponid'=>$data->sheets[0]['cells'][$i][15],
                    'couponcounttatal'=>$data->sheets[0]['cells'][$i][16],
                    'couponcountuse'=>$data->sheets[0]['cells'][$i][17],
                    'coupontamount'=>$data->sheets[0]['cells'][$i][18],
                    'couponstarttime'=>strtotime($data->sheets[0]['cells'][$i][19]),
                    'couponendtime'=>strtotime($data->sheets[0]['cells'][$i][20]),
                    'couponurl'=>$data->sheets[0]['cells'][$i][21],
                    'coupontuiguangurl'=>$data->sheets[0]['cells'][$i][22],
                );
                $platformname = trim($setarr['platformname']);
                $query = $_SGLOBAL['db']->query("SELECT id FROM `".tname('goodsplatform')."` WHERE platformname = '".$platformname."' limit 1");
                $platformid = $_SGLOBAL['db']->fetch_array($query);
                $setarr['platformid'] = $platformid['id'];
                preg_match_all("/\d+/",$setarr['coupontamount'],$arr);
                $coupontamount = array_pop($arr[0]);
                $setarr['coupontamountvalue'] = $coupontamount;
                $query = $_SGLOBAL['db']->query("SELECT id FROM `".tname('goodsstore')."` WHERE goodsid = '".$setarr['goodsid']."' limit 1");
                $goods = $_SGLOBAL['db']->fetch_array($query);
                if (!empty($goods))
                {
                    updatetable('goodsstore', $setarr, array('goodsid'=>$setarr['goodsid']));
                }
                else
                {
                    inserttable('goodsstore', $setarr);
                    //die;
                }
            }
        }
        unset($data);
        cpmessage('do_success', 'admincp.php?ac=goodsstore');
    }
    else
    {
        cpmessage('文件格式不正确/文件过大', $_POST['mpurl']);
    }
}
else {
    $mpurl = 'admincp.php?ac=goodsstore';
   //处理搜索
    $intkeys = array('id', 'top', 'fine','status');
    $strkeys = array('goodsid', 'platformname');
    $randkeys = array(array('intval','goodsamount'),array('intval','goodsmonthcount'),array('floatval','coupontamountvalue'),array('floatval','goodscommission'), array('floatval','goodsrate'), array('intval','sellcount'));
    $likekeys = array('shopname',"goodsname","goodscategoryid");
    $results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
    $countarr=$wherearr = $results['wherearr'];
    $countsql=empty($countarr)?1:implode(' AND ', $countarr);
    $wheresql=empty($wherearr)?1:implode(' AND ', $wherearr);
    $time = time();


    if (isset($_GET['coupon'])&&$_GET['coupon']==2 )
    {
        $countsql.= " and $time> couponstarttime and $time< couponendtime";
        $wheresql.=" and $time> couponstarttime and $time< couponendtime";
        $mpurl.= "&coupon=".$_GET['coupon'];

    }
    elseif (isset($_GET['coupon'])&&$_GET['coupon']==1)
    {
        $countsql.= " and ($time< couponstarttime or $time> couponendtime)";
        $wheresql.=" and ($time< couponstarttime or $time> couponendtime)";
        $mpurl.= "&coupon=".$_GET['coupon'];
    }





    $mpurl .= '&'.implode('&', $results['urls']);

//排序
    $orders = getorders(array('goodsamount', 'coupontamountvalue', 'goodscommission', 'goodsrate', 'goodsmonthcount'), 'id');
    $ordersql = $orders['sql'];
    if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
    $orderby = array($_GET['orderby']=>' selected');
    $ordersc = array($_GET['ordersc']=>' selected');
//显示分页
    $perpage = empty($_GET['perpage']) ? 0 : intval($_GET['perpage']);
    if (!in_array($perpage, array(20, 50, 100, 200))) $perpage = 20;
    $mpurl .= '&perpage=' . $perpage;
    $perpages = array($perpage => ' selected');

    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);
    if ($page < 1) $page = 1;
    $start = ($page - 1) * $perpage;
//检查开始数
    ckstart($start, $perpage);
    $managebatch = checkperm('managebatch');
    $allowbatch = true;
    $list = array();
    $multi = '';
    $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM `" . tname('goodsstore') . "` WHERE $countsql"), 0);
    if ($count) {
        $query = $_SGLOBAL['db']->query("SELECT * FROM `" . tname('goodsstore') . "` WHERE $wheresql $ordersql LIMIT $start,$perpage");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            $value['goodscommission'] = explode('.',$value['goodscommission']);
            if ($time<$value['couponstarttime'] || $time>$value['couponendtime'])
            {
                $value['coupontamount'] = "无效";
            }
            $list[] = $value;
        }
    }
    $multi = multi($count, $perpage, $page, $mpurl);
}