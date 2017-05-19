<?php
    /*
     * author@xuzheng 2016/9/23/ 根据用户手机号在数据库中搜索相关信息并输出进文件
     * */
    $phonearr=array();
    $path=$_SERVER['DOCUMENT_ROOT']."/home";
    if($handle=fopen($path."/phone.txt","r")){
        while(!feof($handle)){
            $phonearr[]=fgets($handle);
        }
    }
    fclose($handle);
    $query=$_SGLOBAL['db']->query("SELECT a.uid,a.name,b.mobile,b.deviceid FROM ".tname('space')." AS a LEFT JOIN ".tname('spacefield')." AS b ON a.uid=b.uid WHERE b.mobile IN(".implode(",",$phonearr).")");
    if($handle=fopen($path."/result.txt","w+")){
        while($result=$_SGLOBAL['db']->fetch_array($query)){
            $str=implode(",",$result);
            fwrite($handle,$str."\r\n");
        }
    }
    fclose($handle);
?>