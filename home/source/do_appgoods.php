<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 14:01
 */
if(!defined('IN_UCHOME')) {
    exit('Access Denied');
}

include_once(S_ROOT.'./source/function_cp.php');
$uid = empty($_POST['uid'])?0:intval($_POST['uid']);
$op = empty($_POST['op'])?'':$_POST['op'];
$lat = empty($_GET['lat'])?'':$_GET['lat'];
$lng = empty($_GET['lng'])?'':$_GET['lng'];
$token=empty($_POST['token'])?'':$_POST['token'];
$deviceId = empty($_POST['deviceid'])?'':$_POST['deviceid'];
//writeLogKang("------------ deviceid: ".$deviceId);
$result = array(
    'errcode' => 0,
    'errmsg' => 'success'
);

$_SGLOBAL['supe_uid'] = $uid;
$channel = $_POST['channel'];
if ($op=="getnav")
{
    $queryLog = $_SGLOBAL['db']->query("SELECT categoryid,categorytype,categoryimg,categoryname,shopurl,categorychannel,sort FROM `".tname('goodscategory')."` where available=1 order by sort desc limit 8");
    while ($valueLog = $_SGLOBAL['db']->fetch_array($queryLog)) {
        $fillerchannel = explode(",", $valueLog['categorychannel']);
        if (!empty($fillerchannel[0]) && in_array($channel, $fillerchannel)) continue;
        if ($valueLog['categoryname'] == '我的订单')
        {
            $valueLog['categorytype'] = 3;   // 添加3->我的订单分类
        }
       $nav[] = $valueLog;
    }
    $result['nav']=$nav;
    echo json_encode($result);
}
elseif($op=="message")
{
    $message = array();
    $queryLog = $_SGLOBAL['db']->query("SELECT goodsid,returnamount,uid,platform FROM `".tname('goodsorder')."` where uid>0 and available=3 order by dateline limit 10");
    while ($valueLog = $_SGLOBAL['db']->fetch_array($queryLog)) {
        $query = $_SGLOBAL['db']->query("SELECT `name` FROM " . tname('space') . "  where uid=" . $valueLog['uid'] . " limit 1");
        $user = $_SGLOBAL['db']->fetch_array($query);
        if (empty($user)) continue;
        $query = $_SGLOBAL['db']->query("SELECT * FROM " . tname('goodsstore') . "  where goodsid='" .$valueLog['goodsid']."' and platformid='".$valueLog['platform']."' limit 1");
        $goodsLog = $_SGLOBAL['db']->fetch_array($query);
       // print_r("SELECT * FROM " . tname('goodsstore') . "  where goodsid=" .$valueLog['goodsid']." and platformid=".$valueLog['platform']." limit 1");
        if (empty($goodsLog)) continue;
        $time = time();
        $valueLog['name'] = $user['name'];
        $valueLog['userphoto'] = "http://app.sihemob.com/data/avatar/" . avatar_file($valueLog['uid'], 'middle');
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('goodsplatform')." where id=".$valueLog['platform']." limit 1");
        $platform = $_SGLOBAL['db']->fetch_array($query);
        $valueLog['returnurl'] = $platform['returnurl'];
        $valueLog['paramsurl'] = $platform['paramsurl'];
        $valueLog['returnrate'] = $platform['returnrate']*$goodsLog['goodsrate']/100 ;
        $valueLog['goodsamount'] = $goodsLog['goodsamount'];
        $valueLog['goodsname'] = $goodsLog['goodsname'];
        $valueLog['goodsimgurl'] = $goodsLog['goodsimgurl'];
        if (!empty($valueLog['coupontamountvalue']) && $time>$valueLog['couponstarttime'] && $time<$valueLog['couponendtime'])
        {
            $valueLog['goodsurl'] = $goodsLog['coupontuiguangurl'];
        }
        else
        {
            $valueLog['goodsurl'] = $goodsLog['goodstaobaokeurl'];
        }
        $valueLog['returnamount'] = round($valueLog['returnamount'], 2);
        $valueLog['platformid'] =$valueLog['platform'];
        unset($valueLog['platform']);
        if ($platform['id'] == 1)
        {
            $valueLog['platformtitle'] ="淘宝网";
            $valueLog['platformurl'] ="www.taobao.com";
        }
        elseif ($platform['id'] == 2)
        {
            $valueLog['platformtitle'] ="天猫商城";
            $valueLog['platformurl'] ="www.taobao.com";
        }
        $message[] = $valueLog;
    }
    $result['message']=$message;
    echo json_encode($result);
}
elseif($op == "fine")
{
    $queryLog = $_SGLOBAL['db']->query("SELECT id, goodsid,goodsname, platformid, goodsimgurl,goodsname,goodstaobaokeurl,goodsamount,goodsrate,coupontamount,couponstarttime,couponendtime,coupontuiguangurl,coupontamountvalue FROM `".tname('goodsstore')."` where status=1 and fine=1 order by top desc,goodsmonthcount desc limit 11");
    while ($valueLog = $_SGLOBAL['db']->fetch_array($queryLog)) {
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('goodsplatform')." where id=".$valueLog['platformid']." limit 1");
        $platform = $_SGLOBAL['db']->fetch_array($query);
        $goods['id'] = $valueLog['id'];
        $goods['platformid'] = $valueLog['platformid'];
        $goods['goodsid'] = $valueLog['goodsid'];
        $goods['goodsname'] = $valueLog['goodsname'];
        $goods['goodsimgurl'] = $valueLog['goodsimgurl'];
        $goods['returnurl'] = $platform['returnurl'];
        $goods['paramsurl'] = $platform['paramsurl'];
        $goods['returnrate'] = $platform['returnrate']*$valueLog['goodsrate']/100 ;
        $goods['coupontamount'] = $valueLog['coupontamountvalue'];
        $goods['goodsamount'] = $valueLog['goodsamount'];
        $time = time();
        if (!empty($valueLog['coupontamountvalue']) && $time>$valueLog['couponstarttime'] && $time<$valueLog['couponendtime'] )
        {
            $goods['goodsurl'] = $valueLog['coupontuiguangurl'];
        }
        else
        {
            $goods['goodsurl'] = $valueLog['goodstaobaokeurl'];
        }
        $goods['returnamount'] = round($goods['goodsamount']*$goods['returnrate']/100, 2);
        if ($platform['id'] == 1)
        {
            $goods['platformid'] = 1;
            $goods['platformtitle'] ="淘宝网";
            $goods['platformurl'] ="www.taobao.com";
        }
        elseif ($platform['id'] == 2)
        {
            $goods['platformid'] = 2;
            $goods['platformtitle'] ="天猫商城";
            $goods['platformurl'] ="www.taobao.com";
        }
        $fine[] = $goods;
    }
    $result['title']="每天精选十个最受欢迎商品";
    $result['fine']=$fine;
    echo json_encode($result);
}
elseif($op == "getgoodsbycategoryid")
{
    $categoryid = empty($_POST['categoryid'])?'-1':$_POST['categoryid'];
    $queryLog = $_SGLOBAL['db']->query("SELECT * FROM `".tname('goodscategory')."` where categoryid=$categoryid  limit 1");
    $valueLog = $_SGLOBAL['db']->fetch_array($queryLog);
    $search = $goods =array();
    $time = time();
    if (!empty($valueLog))
    {
        $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
        $id = empty($_POST['id'])?'0':$_POST['id'];
        $goodsmonthcount = empty($_POST['goodsmonthcount'])?'0':$_POST['goodsmonthcount'];
        $top = empty($_POST['top'])?'0':$_POST['top'];

        $result['category']['categoryid'] = $valueLog['categoryid'];
        $result['category']['categoryname'] = $valueLog['categoryname'];
        $result['category']['showtype'] = $valueLog['showtype'];
        $result['category']['goodscategory'] = explode(",",$valueLog['goodscategory']);
        if (count( $result['category']['goodscategory'])>=2)
        {
            foreach ($result['category']['goodscategory'] as &$value)
            {
                ($category = strchr($value, '/', true)) && $value=$category ;
            }
            $goodscategory = empty($_POST['goodscategory'])?'':$_POST['goodscategory'];
            !empty($goodscategory) && $search[]="goodscategoryid like '".$goodscategory."%' ";
        }
        else
        {
            !empty($result['category']['goodscategory'][0]) && $search[]="goodscategoryid = '".$result['category']['goodscategory'][0]."' ";
        }
        !empty($valueLog['goodsmonthcount1']) && $search[]="goodsmonthcount>=".$valueLog['goodsmonthcount1'];
        !empty($valueLog['goodsmonthcount2']) && $search[]="goodsmonthcount<=".$valueLog['goodsmonthcount2'];
        !empty($valueLog['goodsamount1']) && $search[]="goodsamount>=".$valueLog['goodsamount1'];
        !empty($valueLog['goodsamount2']) && $search[]="goodsamount<=".$valueLog['goodsamount2'];
        !empty($valueLog['coupontamount1']) && $search[]="coupontamountvalue>=".$valueLog['coupontamount1'];
        !empty($valueLog['coupontamount2']) && $search[]="coupontamountvalue<=".$valueLog['coupontamount2'];
        (!empty($valueLog['coupontamount1']) || !empty($valueLog['coupontamount2'])) && ($search[]="couponstarttime<".$time) && ($search[]="couponendtime>".$time);
        !empty($valueLog['goodsrate1']) && $search[]="goodsrate>=".$valueLog['goodsrate1'];
        !empty($valueLog['goodsrate2']) && $search[]="goodsrate<=".$valueLog['goodsrate2'];
        !empty($valueLog['goodscommission1']) && $search[]="goodscommission>=".$valueLog['goodscommission1'];
        !empty($valueLog['goodscommission2']) && $search[]="goodscommission<=".$valueLog['goodscommission2'];

        if($isRefresh == "no" && !empty($id)) {
            // $search[] = " id <" . $id;
            $search[] = " goodsmonthcount <=" . $goodsmonthcount;
            $search[] = " top <=" . $top;
            $search[]= " id not in (SELECT id FROM `".tname('goodsstore')."` where goodsmonthcount=".$goodsmonthcount." and id>=".$id.") ";
        }
    }

    $where = implode(' and ', $search);
    $queryLog = $_SGLOBAL['db']->query("SELECT * FROM `".tname('goodsstore')."` where ". $where ." and status=1 order by top desc, goodsmonthcount desc,id desc limit 10");
  // print_r("SELECT * FROM `".tname('goodsstore')."` where status=1 and ". $where ." order by id desc limit 10");
    while ($valueLog = $_SGLOBAL['db']->fetch_array($queryLog)) {
        $goods['id'] = $valueLog['id'];
        $goods['platformid'] = $valueLog['platformid'];
        $goods['goodsid'] = $valueLog['goodsid'];
        $goods['goodsname'] = $valueLog['goodsname'];
        $goods['goodsimgurl'] = $valueLog['goodsimgurl'];
        $goods['goodsmonthcount'] = $valueLog['goodsmonthcount'];
        $goods['shopname'] = $valueLog['shopname'];
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('goodsplatform')." where id=".$valueLog['platformid']." limit 1");
        $platform = $_SGLOBAL['db']->fetch_array($query);
        $goods['returnurl'] = $platform['returnurl'];
        $goods['paramsurl'] = $platform['paramsurl'];
        $goods['returnrate'] = $platform['returnrate']*$valueLog['goodsrate']/100 ;
        $goods['coupontamount'] = $valueLog['coupontamountvalue'];
        $goods['goodsamount'] = $valueLog['goodsamount'];
        $goods['top'] = $valueLog['top'];
        if (!empty($valueLog['coupontamountvalue']) && $time>$valueLog['couponstarttime'] && $time<$valueLog['couponendtime'])
        {
            $goods['goodsurl'] = $valueLog['coupontuiguangurl'];
        }
        else
        {
            $goods['goodsurl'] = $valueLog['goodstaobaokeurl'];
            $goods['coupontamount'] = '';
        }
        $goods['returnamount'] = round($goods['goodsamount']*$goods['returnrate']/100, 2);
        if ($platform['id'] == 1)
        {
            $goods['platformid'] = 1;
            $goods['platformtitle'] ="淘宝网";
            $goods['platformurl'] ="www.taobao.com";
        }
        elseif ($platform['id'] == 2)
        {
            $goods['platformid'] = 2;
            $goods['platformtitle'] ="天猫商城";
            $goods['platformurl'] ="www.taobao.com";
        }
        $result['category']['showtype'] ==1 && $goods['showmessage'] = "月销：".$goods['goodsmonthcount']."件";
        $result['category']['showtype'] ==2 && $goods['showmessage'] = "返利：".$goods['returnrate']."%";
        $result['category']['showtype'] ==3 && $goods['showmessage'] = $goods['returnamount'];
        $result['category']['showtype'] ==4 && $goods['showmessage'] = round($goods['coupontamount']);
        $result['category']['showtype'] ==5 && $goods['showmessage'] = $goods['shopname'];

        $result['category']['goods'][]  = $goods;
    }
    echo json_encode($result);
}
elseif ($op == "paysuccess")
{
    $result['token']=check_token($uid, $token);
    $paysuccurl=empty($_POST['paysuccurl'])?'':$_POST['paysuccurl'];
    $goodsid=empty($_POST['goodsid'])?'':$_POST['goodsid'];
    $platformid=empty($_POST['platformid'])?'':$_POST['platformid'];
    $goodsname=empty($_POST['goodsname'])?'':$_POST['goodsname'];
    $goodsamount=empty($_POST['goodsamount'])?'':$_POST['goodsamount'];
    $returnamount=empty($_POST['returnamount'])?'':$_POST['returnamount'];
    if (empty($paysuccurl) || empty($goodsid) || empty($platformid) || empty($goodsamount) || empty($returnamount) || empty($uid)) {
        $result['errcode'] = 1001;
        $result['errmsg'] = "参数错误，请稍候再试";
        echo json_encode($result);
        return;
    }
    $uid = $_SGLOBAL['supe_uid'];
    $orderurl=parse_url($paysuccurl);
    $orderid = convertUrlQuery($orderurl['query']);

    $query = $_SGLOBAL['db']->query("SELECT * FROM `".tname('goodsplatform')."` where id=". $platformid ."  limit 1");
    $platform = $_SGLOBAL['db']->fetch_array($query);
    $param = $platform['params'];
    if (!isset($orderid[$param]))
    {
        $result['errcode'] = 1003;
        $result['errmsg'] = "参数没有匹配成功";
        echo json_encode($result);
        return;
    }
    $id = trim($orderid[$param]);
    $queryLog = $_SGLOBAL['db']->query("SELECT * FROM `".tname('goodsorder')."` where orderid='". $id ."' and platform='".$platformid."'  limit 1");
    $order = $_SGLOBAL['db']->fetch_array($queryLog);
    if (!empty($order)) {
        $result['errcode'] = 1003;
        $result['errmsg'] = "订单已存在";
        echo json_encode($result);
        return;
    }
    $money = $returnamount*100;
    $setarr = array(
        'moneylogamount' => $money,
        'moneylogamounttype' => 0,  //0+,1-
        'moneyloguid' => $_SGLOBAL['supe_uid'],
        'moneylogusername' => $_SGLOBAL['supe_username'],
        'moneyloguserphoto' => "http://app.sihemob.com/data/avatar/".avatar_file($uid, 'middle'),
        'moneylogtypecategory' => 61,
        'moneylogstatus' => 1,
        'moneylogtid' => 0,
        'moneylogtip' => '返利-'.$goodsname,
        'dateline' => $_SGLOBAL['timestamp'],
    );
    $moneylogTableNames = getMoneylogTableName();
    $moneylogid = inserttable($moneylogTableNames[0],$setarr, 1);
    if ($moneylogid < 1) {
        $result['errcode'] = 1003;
        $result['errmsg'] = "领取失败，请稍候再试";
        echo json_encode($result);
        return;
    }
    $setarrs = array();
    $setarrs['totalmoney'] = "totalmoney=totalmoney+$money";
    $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarrs)." WHERE uid='$uid'");

    $orderurl=parse_url($paysuccurl);
    $orderid = convertUrlQuery($orderurl['query']);
    $setarr = array(
        'uid'=> $_SGLOBAL['supe_uid'],
        'goodsid'=> $goodsid,
        'goodsname'=> $goodsname,
        'orderid'=> $id,
        'goodscount'=>1,
        'payamount'=> $goodsamount,
        'returnamount'=> $returnamount,
        'platform'=> $platformid,
        'available'=> 1,
        'moneylogid'=> $moneylogid,
        'dateline' => $_SGLOBAL['timestamp']
    );
    $order = inserttable('goodsorder',$setarr, 1);
    if ($order < 1) {
        $result['errcode'] = 1003;
        $result['errmsg'] = "创建订单失败";
        echo json_encode($result);
        return;
    }
    echo json_encode($result);
}
elseif ($op == "orderlist")
{
    if (empty($uid)) {
        $result['errcode'] = 1001;
        $result['errmsg'] = "参数错误，请稍候再试";
        echo json_encode($result);
        return;
    }
    $where = "uid=".$uid;
    $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
    $dateline = empty($_POST['dateline'])?'0':$_POST['dateline'];
    if($isRefresh == "no" && !empty($dateline)) {
        $where .= " and dateline <" . $dateline;
    }
    $orderlist = array();
    $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('goodsorder')." where ".$where." order by dateline desc limit 10");
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        $queryGoods = $_SGLOBAL['db']->query("SELECT goodsimgurl FROM ".tname('goodsstore')." where goodsid=".$value['goodsid']." limit 1");
        $valueGoods = $_SGLOBAL['db']->fetch_array($queryGoods);
        if (!empty($valueGoods))
        {
            $value['pic'] = $valueGoods['goodsimgurl'];
        }
        else
        {
            $value['pic'] = 'http://dload.ququyx.com/upload/goodnopic.jpeg';
        }
        $orderlist[] = $value;
    }
    $result['category']['goods']  = $orderlist;
    echo json_encode($result);
}
elseif ($op == "search")
{

    $result['order'] = array(
        array('key'=> '0', 'value'=> "综合"),
        array('key'=> 'goodsrate', 'value'=> "返利"),
        array('key'=> 'goodsmonthcount', 'value'=> "月销量"),
        array('key'=> 'goodsamount', 'value'=> "价格"),
    );
    $query = empty($_POST['query'])?'':$_POST['query'];
    $order = empty($_POST['order'])?'':$_POST['order'];
    $sort = empty($_POST['sort'])?'desc':$_POST['sort'];
    if($order == "goodsamount")
    {
        $sort = "asc";
    }
    $query = empty($query)? 1:"goodsname like '%".$query."%' ";
    $order = empty($order)?  "":" order by ".$order." ".$sort;
    $isRefresh = empty($_POST['refresh'])?'':$_POST['refresh'];
    $page = empty($_POST['page'])?'1':$_POST['page'];
    $start = ($page-1)*20;
    $queryLog = $_SGLOBAL['db']->query("SELECT * FROM ".tname('goodsstore')." where  ".$query.$order." limit ".$start.",20" );
    while ($valueLog = $_SGLOBAL['db']->fetch_array($queryLog)) {
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('goodsplatform')." where id=".$valueLog['platformid']." limit 1");
        $platform = $_SGLOBAL['db']->fetch_array($query);
        $goods['goodsid'] = $valueLog['goodsid'];
        $goods['platformid'] = $valueLog['platformid'];
        $goods['goodsname'] = $valueLog['goodsname'];
        $goods['goodsimgurl'] = $valueLog['goodsimgurl'];
        $goods['goodsmonthcount'] = $valueLog['goodsmonthcount'];
        $goods['returnurl'] = $platform['returnurl'];
        $goods['paramsurl'] = $platform['paramsurl'];
        $goods['returnrate'] = $platform['returnrate']*$valueLog['goodsrate']/100 ;
        $goods['coupontamount'] = $valueLog['coupontamountvalue'];
        $goods['goodsamount'] = $valueLog['goodsamount'];
        $time = time();
        if (!empty($valueLog['coupontamountvalue']) && $time>$valueLog['couponstarttime'] && $time<$valueLog['couponendtime'] )
        {
            $goods['goodsurl'] = $valueLog['coupontuiguangurl'];
        }
        else
        {
            $goods['goodsurl'] = $valueLog['goodstaobaokeurl'];
        }
        $goods['returnamount'] = round($goods['goodsamount']*$goods['returnrate']/100, 2);
        if ($platform['id'] == 1)
        {
            $goods['platformtitle'] ="淘宝网";
            $goods['platformurl'] ="www.taobao.com";
        }
        elseif ($platform['id'] == 2)
        {
            $goods['platformtitle'] ="天猫商城";
            $goods['platformurl'] ="www.taobao.com";
        }
        $result['goods'][]= $goods;
    }
    echo json_encode($result);

}


function convertUrlQuery($query)
{
    $queryParts = explode('&', $query);

    $params = array();
    foreach ($queryParts as $param)
    {
        $item = explode('=', $param);
        $params[$item[0]] = $item[1];
    }
    return $params;
}






























