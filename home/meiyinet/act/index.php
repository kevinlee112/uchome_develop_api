<?php
    $jumpUrl="./index.html";
	$arr=array();
	if(preg_match("/^[1][0-9]{10}$/",$_POST['phone'])){
		if($handle=fopen("data.txt","a+")){
			if(flock($handle,LOCK_EX)){
				$arr['phone']=$_POST['phone'];
				$arr['date']=date('Y-m-d H:i:s',time());
				$arr['ip']=$_SERVER['REMOTE_ADDR'];
				$content=implode(',',$arr);
				fwrite($handle,$content);
			    fwrite($handle,"\n");
			    flock($handle,LOCK_UN);
			    fclose($handle);
				header('content-type:text/html;charset=utf-8;');
				echo "<script>alert('提交成功，客服将尽快联系您，请保持手机畅通！');</script>";
				echo "<script>window.location.href='".$jumpUrl."'</script>";
				exit;
			}
		}
	}else{
		header('content-type:text/html;charset=utf-8;');
		echo "<script>alert('手机格式不正确！');</script>";
		echo "<script>window.location.href='".$jumpUrl."'</script>";
		exit;
	}
?>