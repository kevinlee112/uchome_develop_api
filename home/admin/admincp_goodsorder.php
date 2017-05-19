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
$thevalue = $list = array();
$moneylogTableNames = getMoneylogTableName();
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
        !empty($_GET[$value]) && $thevalue[$value] = $_GET[$value];
    }
    if(empty($thevalue['id'])) {
        //添加
        $query = $_SGLOBAL['db']->query("SELECT id FROM `" . tname('goodsplatform') . "` WHERE platformname='".$setarr['platform'] ."' LIMIT 1");
        $platform = $_SGLOBAL['db']->fetch_array($query);
        if(empty($platform))
        {
            cpmessage('平台不存在', 'admincp.php?ac=goodsorder');
        }
        $setarr['platform'] = $platform['id'];
        $status = 1;
        $setarr['available'] == 2 && $status = 0;
        $setarr['available'] == 3 && $status = 2;
        $setarr['dateline'] = time();

        $query = $_SGLOBAL['db']->query("SELECT name  FROM ".tname('space')." WHERE uid='$setarr[uid]'");
        $user = $_SGLOBAL['db']->fetch_array($query);
        if (empty($user))
        {
            cpmessage('此用户不存在', 'admincp.php?ac=goodsorder');
        }
        $name = $user['name'];
        $money = $setarr['returnamount']*100;
        $log = array(
            'moneylogamounttype' => 0,  //0+,1-
            'moneyloguid' => $setarr['uid'],
            'moneylogusername' => $name,
            'moneyloguserphoto' => "http://app.sihemob.com/data/avatar/".avatar_file($setarr['uid'], 'middle'),
            'moneylogamount'=> $money,
            'moneylogtypecategory' => 61,
            'moneylogstatus'=>$status,
            'moneylogtid' => 0,
            'moneylogtip' => '返利-'.$setarr['goodsname'],
            'dateline' => $setarr['dateline'],
        );
        $moneylogid = inserttable($moneylogTableNames[0], $log, 1);
        if ($moneylogid>1)
        {
            //更新space钱数
            if ($status == 0)
            {
                $space = array();
                $space['totalmoney'] = "totalmoney=totalmoney+$money";
                $space['realmoney'] = "realmoney=realmoney+$money";
                $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $space)." WHERE uid=".$setarr['uid']);

            }
            elseif ($status == 1)
            {
                $space = array();
                $space['totalmoney'] = "totalmoney=totalmoney+$money";
               // $space['realmoney'] = "realmoney=realmoney+$money";
                $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $space)." WHERE uid=".$setarr['uid']);
            }
            $setarr['moneylogid'] = $moneylogid;
        }
        else
        {
            cpmessage('插入moneylog失败', 'admincp.php?ac=goodsorder');
        }
        $orderid = inserttable('goodsorder', $setarr, 1);
        if ($orderid>1)
        {
            cpmessage('插入订单成功', 'admincp.php?ac=goodsorder');
        }
        else
        {
            cpmessage('插入订单失败', 'admincp.php?ac=goodsorder');
        }
    } else {
        //更新
        $query = $_SGLOBAL['db']->query("SELECT id FROM `" . tname('goodsplatform') . "` WHERE platformname='".$setarr['platform'] ."' LIMIT 1");
        $plateform = $_SGLOBAL['db']->fetch_array($query);
        $setarr['platform'] = $plateform['id'];
        //用户判断
        $query = $_SGLOBAL['db']->query("SELECT `name`  FROM ".tname('space')." WHERE uid='$setarr[uid]' LIMIT 1");
        $user = $_SGLOBAL['db']->fetch_array($query);
        $name = $user['name'];
        if (empty($user))
        {
            cpmessage('此用户不存在', 'admincp.php?ac=goodsorder');
        }
        if (!empty($setarr['moneylogid'])) {
            foreach ($moneylogTableNames as $moneylogTableName)
            {
                $query = $_SGLOBAL['db']->query("SELECT moneylogid,moneylogamount,moneylogstatus FROM `" . tname($moneylogTableName) . "` WHERE moneylogid=" . $setarr['moneylogid'] . " LIMIT 1");
                $moneylog = $_SGLOBAL['db']->fetch_array($query);
                if (!empty($moneylog) && $moneylog['moneylogstatus'] ==1) {
                    $status = 1;
                    $setarr['available'] == 2 && $status = 0;
                    $setarr['available'] == 3 && $status = 2;
                    $money = $setarr['returnamount']*100-$moneylog['moneylogamount'];
                    $log = array(
                        'moneyloguid' => $setarr['uid'],
                        'moneylogusername' => $name,
                        'moneylogstatus' => $status,
                        'moneylogamount' => $setarr['returnamount']*100,
                        'moneylogtip' => '返利-'.$setarr['goodsname'],
                    );
                    updatetable($moneylogTableName, $log, array('moneylogid' => $setarr['moneylogid']));
                    //更新space钱数
                    if ($status == 0)
                    {
                        $space = array();
                        $space['totalmoney'] = "totalmoney=totalmoney+$money";
                        $space['realmoney'] = "realmoney=realmoney+".$setarr['returnamount']*100;
                        $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $space)." WHERE uid=".$setarr['uid']);

                    }
                    elseif ($status == 1)
                    {
                        $space = array();
                        $space['totalmoney'] = "totalmoney=totalmoney+$money";
                        // $space['realmoney'] = "realmoney=realmoney+$money";
                        $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $space)." WHERE uid=".$setarr['uid']);
                    }
                    elseif ($status == 2)
                    {
                        $space = array();
                        $space['totalmoney'] = "totalmoney=totalmoney-".$moneylog['moneylogamount'];
                        // $space['realmoney'] = "realmoney=realmoney+$money";
                        $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $space)." WHERE uid=".$setarr['uid']);
                    }
                }
            }
        }
        else
        {
            $status = 1;
            $setarr['available'] == 2 && $status = 0;
            $setarr['available'] == 3 && $status = 2;

            $query = $_SGLOBAL['db']->query("SELECT `name`  FROM ".tname('space')." WHERE uid='$setarr[uid]'");
            $user = $_SGLOBAL['db']->fetch_array($query);
            if (empty($user))
            {
                cpmessage('此用户不存在', 'admincp.php?ac=goodsorder');
            }
            $name = $user['name'];
            $money = $setarr['returnamount']*100;
            $log = array(
                'moneylogamounttype' => 0,  //0+,1-
                'moneyloguid' => $setarr['uid'],
                'moneylogusername' => $name,
                'moneyloguserphoto' => "http://app.sihemob.com/data/avatar/".avatar_file($setarr['uid'], 'middle'),
                'moneylogamount'=> $money,
                'moneylogtypecategory' => 61,
                'moneylogstatus'=>$status,
                'moneylogtid' => 0,
                'moneylogtip' => '返利-'.$setarr['goodsname'],
                'dateline' => $setarr['dateline'],
            );
            $moneylogid = inserttable($moneylogTableNames[0], $log, 1);
            if ($moneylogid>1)
            {
                //更新space钱数
                if ($status == 0)
                {
                    $space = array();
                    $space['totalmoney'] = "totalmoney=totalmoney+$money";
                    $space['realmoney'] = "realmoney=realmoney+$money";
                    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $space)." WHERE uid=".$setarr['uid']);

                }
                elseif ($status == 1)
                {
                    $space = array();
                    $space['totalmoney'] = "totalmoney=totalmoney+$money";
                    // $space['realmoney'] = "realmoney=realmoney+$money";
                    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $space)." WHERE uid=".$setarr['uid']);
                }
                $setarr['moneylogid'] = $moneylogid;
            }
            else
            {
                cpmessage('插入moneylog失败', 'admincp.php?ac=goodsorder');
            }

        }
        updatetable('goodsorder', $setarr, array('id'=>$thevalue['id']));
        cpmessage('do_success', 'admincp.php?ac=goodsorder&op=edit&id='.$thevalue['id']);
    }

    //更新缓存
    include_once(S_ROOT.'./source/function_cache.php');
    usergroup_cache();
    cpmessage('do_success', 'admincp.php?ac=goodsorder');
}
include_once(S_ROOT.'./source/function_cp.php');


if ($_GET['op'] == 'add') {
    //添加
    $thevalue = array(
        'uid'=>'', 'goodsid' => '', 'goodsname' => '', 'orderid' => '', 'goodscount' => '', 'payamount'=>'',
        'returnamount' => '', 'platform'=>'', 'available'=>''
    );
    //include_once(S_ROOT . "./data/data_magic.php");
}
elseif ($_GET['op'] == 'edit')
{
    $_GET['id'] = empty($_GET['id'])?0:intval($_GET['id']);
    if($_GET['id']) {
        $query = $_SGLOBAL['db']->query("SELECT *  FROM ".tname('goodsorder')." WHERE id='$_GET[id]'");
        if($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
            $query = $_SGLOBAL['db']->query("SELECT platformname FROM `" . tname('goodsplatform') . "` WHERE id=".$thevalue['platform'] ." LIMIT 1");
            $plateform = $_SGLOBAL['db']->fetch_array($query);
            $thevalue['platform'] = $plateform['platformname'];
        }
        else
        {
            cpmessage('user_credit_good_does_not_exist');
        }
    }
    //include_once(S_ROOT . "./data/data_magic.php");
}
elseif ($_GET['op'] == 'upload') {
    if ($_FILES["file"]["size"] < 1000000000) {
        $filename = 'orderlist.xls';
        error_reporting(E_ALL);
        set_time_limit(0);
        $result = move_uploaded_file($_FILES['file']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . "/home/upload/" . $filename);//上传到upload目录下
        if ($result)  //如果上传文件成功，就执行导入excel操作
        {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/home/uc_client/lib/phpExcelReader/Excel/reader.php';
            $data = new Spreadsheet_Excel_Reader();
            $data->setOutputEncoding('UTF-8');
            $data->read($_SERVER['DOCUMENT_ROOT'] . "/home/upload/" . $filename);
            $style = 1;
            $data->sheets[0]['cells'][1][4] != "商品ID" && $style =0;
            $data->sheets[0]['cells'][1][3] != "商品信息" && $style =0;
            $data->sheets[0]['cells'][1][7] != "商品数" && $style =0;
            $data->sheets[0]['cells'][1][25] != "订单编号" && $style =0;
            $data->sheets[0]['cells'][1][13] != "付款金额" && $style =0;
            $data->sheets[0]['cells'][1][14] != "效果预估" && $style =0;
            $data->sheets[0]['cells'][1][10] != "订单类型" && $style =0;
            $data->sheets[0]['cells'][1][9] != "订单状态" && $style =0;
            $data->sheets[0]['cells'][1][1] != "创建时间" && $style =0;
            if (empty($style))
            {
                cpmessage('订单文件格式不正确', 'admincp.php?ac=goodsorder');
            }
            for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
                $setarr = array(
                    'goodsid' => $data->sheets[0]['cells'][$i][4],
                    'goodsname' => addslashes($data->sheets[0]['cells'][$i][3]),
                    'orderid' => $data->sheets[0]['cells'][$i][25],
                    'goodscount' => $data->sheets[0]['cells'][$i][7],
                    'payamount' => $data->sheets[0]['cells'][$i][13],
                    'returnamount' => $data->sheets[0]['cells'][$i][14],
                    'platform' => $data->sheets[0]['cells'][$i][10],
                    'available' => $data->sheets[0]['cells'][$i][9],
                    'dateline' => strtotime($data->sheets[0]['cells'][$i][1]),
                );
                $platformname = trim($setarr['platform']);
                $query = $_SGLOBAL['db']->query("SELECT id,returnrate FROM `" . tname('goodsplatform') . "` WHERE platformname = '" . $platformname . "' limit 1");
                $platformid = $_SGLOBAL['db']->fetch_array($query);
                if (!empty($platformid)) {
                    $setarr['available'] == "订单失效" && $setarr['available'] = 3;
                    $setarr['available'] == "订单付款" && $setarr['available'] = 1;
                    $setarr['available'] == "订单结算" && $setarr['available'] = 2;
                    $setarr['platform'] = $platformid['id'];
                    $setarr['returnamount'] = $setarr['returnamount']*$platformid['returnrate']/100;
                    $query = $_SGLOBAL['db']->query("SELECT id,uid,available,moneylogid FROM `" . tname('goodsorder') . "` WHERE orderid = '" . $setarr['orderid'] . "' limit 1");
                    $order = $_SGLOBAL['db']->fetch_array($query);
                    if (!empty($order)) {
                        if ($order['available']!=1 && !empty($order['moneylogid'])) {
                            unset($setarr['available']);
                        } elseif($order['moneylogid']>1 && !empty($order['uid'])) {
                            foreach ($moneylogTableNames as $moneylogTableName)
                            {
                                $query = $_SGLOBAL['db']->query("SELECT moneylogid,moneylogamount,moneylogstatus FROM `" . tname($moneylogTableName) . "` WHERE moneylogid = '" . $order['moneylogid'] . "' limit 1");
                                $moneylog = $_SGLOBAL['db']->fetch_array($query);
                                if (!empty($moneylog) && $moneylog['moneylogstatus'] ==1) {
                                    $status = 1;
                                    $setarr['available'] == 2 && $status = 0;
                                    $setarr['available'] == 3 && $status = 2;
                                    $money = $setarr['returnamount']*100-$moneylog['moneylogamount'];
                                    $log = array(
                                        'moneylogstatus' => $status,
                                        'moneylogamount' => $setarr['returnamount']*100,
                                    );
                                    updatetable($moneylogTableName, $log, array('moneylogid' => $moneylog['moneylogid']));
                                    //更新space钱数
                                    if ($status == 0)
                                    {
                                        $space = array();
                                        $space['totalmoney'] = "totalmoney=totalmoney+$money";
                                        $space['realmoney'] = "realmoney=realmoney+".$setarr['returnamount']*100;
                                        $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $space)." WHERE uid=".$order['uid']);

                                    }
                                    elseif ($status == 1)
                                    {
                                        $space = array();
                                        $space['totalmoney'] = "totalmoney=totalmoney+$money";
                                        // $space['realmoney'] = "realmoney=realmoney+$money";
                                        $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $space)." WHERE uid=".$order['uid']);
                                    }
                                    elseif ($status == 2)
                                    {
                                        $space = array();
                                        $space['totalmoney'] = "totalmoney=totalmoney-".$moneylog['moneylogamount'];
                                        // $space['realmoney'] = "realmoney=realmoney+$money";
                                        $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $space)." WHERE uid=".$order['uid']);
                                    }
                                }
                            }
                        }
                        if ($order['available']==1)
                        {
                            updatetable('goodsorder', $setarr, array('id' => $order['id']));
                        }
                    } else {
                        inserttable('goodsorder', $setarr);
                        //die;
                    }
                }
            }
        }
        unset($data);
        cpmessage('do_success', 'admincp.php?ac=goodsorder');
    }
    else
    {
        cpmessage('文件格式不正确/文件过大', $_POST['mpurl']);
    }
}
else {
    $mpurl = 'admincp.php?ac=goodsorder';
    //处理搜索
    $intkeys = array('available');
    $strkeys = array('uid', 'goodsid', 'orderid');
    $randkeys = array(array('strtotime','dateline'));
    $likekeys = array('tagname');
    $results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
    $countarr=$wherearr = $results['wherearr'];
    $countsql=empty($countarr)?1:implode(' AND ', $countarr);
    $wheresql=empty($wherearr)?1:implode(' AND ', $wherearr);
    $mpurl .= '&'.implode('&', $results['urls']);

//排序
    $orders = getorders(array('payamount', 'returnamount'), 'dateline');
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
    $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM `" . tname('goodsorder') . "` WHERE $countsql"), 0);
    if ($count) {
        $query = $_SGLOBAL['db']->query("SELECT * FROM `" . tname('goodsorder') . "` WHERE $wheresql $ordersql LIMIT $start,$perpage");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            $queryplatform = $_SGLOBAL['db']->query("SELECT platformname FROM `" . tname('goodsplatform') . "` WHERE id=".$value['platform'] ." LIMIT 1");
            $plateform = $_SGLOBAL['db']->fetch_array($queryplatform);
            $value['platform'] = $plateform['platformname'];
            $value['dateline'] = date('Y-m-d H:i:s',$value['dateline']);
            $value['available'] == 3 && $value['available'] = "订单失效"  ;
            $value['available'] == 1 && $value['available'] = "订单付款"  ;
            $value['available'] == 2 && $value['available'] = "订单结算"  ;
            $list[] = $value;
        }
    }
    $multi = multi($count, $perpage, $page, $mpurl);
}