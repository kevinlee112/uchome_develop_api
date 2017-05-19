<?php
// Test CVS
require_once 'Excel/reader.php';


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


for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
//	for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
//		echo "\"".$data->sheets[0]['cells'][$i][$j]."\",";
//	}
//	echo "\n";
    $sql = "INSERT INTO uchome_goodstore VALUES('".
        $i."',1,'".
        $data->sheets[0]['cells'][$i][14]."','".
        $data->sheets[0]['cells'][$i][1]."','".
        $data->sheets[0]['cells'][$i][2]."','".
        $data->sheets[0]['cells'][$i][3]."','".
        $data->sheets[0]['cells'][$i][4]."','".
        $data->sheets[0]['cells'][$i][6]."','".
        $data->sheets[0]['cells'][$i][5]."','".
        $data->sheets[0]['cells'][$i][7]."','".
        $data->sheets[0]['cells'][$i][8]."','".
        $data->sheets[0]['cells'][$i][9]."','".
        $data->sheets[0]['cells'][$i][10]."','".
        $data->sheets[0]['cells'][$i][12]."','".
        $data->sheets[0]['cells'][$i][11]."','".
        $data->sheets[0]['cells'][$i][13]."','".
        $data->sheets[0]['cells'][$i][15]."','".
        $data->sheets[0]['cells'][$i][16]."','".
        $data->sheets[0]['cells'][$i][17]."','".
        $data->sheets[0]['cells'][$i][18]."','".
        $data->sheets[0]['cells'][$i][19]."','".
        $data->sheets[0]['cells'][$i][20]."','".
        $data->sheets[0]['cells'][$i][21]."','".
        $data->sheets[0]['cells'][$i][22]."')";
    echo $sql.'< br />';
    $res = mysql_query($sql);
}


//print_r($data);
//print_r($data->formatRecords);
?>
