<?php
error_reporting();
require_once 'excel_reader.php';
$data = new Spreadsheet_Excel_Reader("eee.xls");

for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
    for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
        echo "\"".$data->sheets[0]['cells'][$i][$j]."\",";
    }
    echo "\n";

}
die;
?>
