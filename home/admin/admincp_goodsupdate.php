<?php

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
// Test CVS
require_once $ROOT.'Excel/reader.php';

// ExcelFile($filename, $encoding);
$data = new Spreadsheet_Excel_Reader();


// Set output Encoding.
$data->setOutputEncoding('UTF-8');

/***
* if you want you can change 'iconv' to mb_convert_encoding:
* $data->setUTFEncoder('mb');
*
**/

/***
* By default rows & cols indeces start with 1
* For change initial index use:
* $data->setRowColOffset(0);
*
**/



/***
*  Some function for formatting output.
* $data->setDefaultFormat('%.2f');
* setDefaultFormat - set format for columns with unknown formatting
*
* $data->setColumnFormat(4, '%.3f');
* setColumnFormat - set format for column (apply only to number fields)
*
**/

$data->read('eee.xls');

/*


 $data->sheets[0]['numRows'] - count rows
 $data->sheets[0]['numCols'] - count columns
 $data->sheets[0]['cells'][$i][$j] - data from $i-row $j-column

 $data->sheets[0]['cellsInfo'][$i][$j] - extended info about cell
    
    $data->sheets[0]['cellsInfo'][$i][$j]['type'] = "date" | "number" | "unknown"
        if 'type' == "unknown" - use 'raw' value, because  cell contain value with format '0.00';
    $data->sheets[0]['cellsInfo'][$i][$j]['raw'] = value if cell without format 
    $data->sheets[0]['cellsInfo'][$i][$j]['colspan'] 
    $data->sheets[0]['cellsInfo'][$i][$j]['rowspan'] 
*/

error_reporting(E_ALL ^ E_NOTICE);
//var_dump($data->sheets);
//die;
$db = mysql_connect('localhost', 'root', 'root') or die("Could not connect to database.");//连接数据库
mysql_query("set names 'utf8'");//输出中文
mysql_select_db('uchome'); //选择数据库


for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
//	for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
//		echo "\"".$data->sheets[0]['cells'][$i][$j]."\",";
//	}
//	echo "\n";
//    $sql = "INSERT INTO uchome_goodstore VALUES('".
//        $data->sheets[0]['cells'][$i][1]."','".
//        $data->sheets[0]['cells'][$i][2]."','".
//        $data->sheets[0]['cells'][$i][3]."')";
	$sql = "Insert into uchome_goodsstore VALUES (1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1)";
    echo $sql.'< br />';
    $res = mysql_query($sql);
    die;
}


//print_r($data);
//print_r($data->formatRecords);
?>
