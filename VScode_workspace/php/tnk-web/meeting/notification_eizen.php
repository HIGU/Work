<?php
//////////////////////////////////////////////////////////////////////////////
// 営繕状況照会 表示用                                                      //
//   php-excel-reader/excel_reader2.phpを使用                               //
//   同じフォルダのexample.phpを改良。Excelファイルの指定の変更と           //
//   HTMLのヘッダー部を変更 charsetはUTF-8にしないと化ける                  //
//   Excelファイルは日本語名不可。ファイルがfs1でもいけるか要検討。         //
//   プログラム自体はEUCでLFにしてOK                                        //
//   dump()引数False,FalseでExcelの列行表示を無くす                         //
// Copyright (C) 2020-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2020/11/27 Created   notidication_eizen.php                              //
//////////////////////////////////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE);
require_once '../php-excel-reader/excel_reader2.php';
$data = new Spreadsheet_Excel_Reader('notification_eizen.xls');
//$data = new Spreadsheet_Excel_Reader('\\\10.1.3.248\temp\test.xls');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/php;charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<style>
* {
    <!--
    全体に影響を与える。
    font-sizeで大きさを調整、transform-originが無いと効かない。
    -->
    transform-origin: top left;
    font-size: 125%;
}

table.excel {
    <!--    外周のボーダー border-style:none; を追加して非表示に
    border-style:ridge;
    border-width:2;
    border-collapse:collapse;
    -->
    border-style:none;
    font-family:sans-serif;
    font-size:12px;
}
table.excel thead th, table.excel tbody th {
    background:#CCCCCC;
    border-style:ridge;
    border-width:1;
    text-align: center;
    vertical-align:bottom;
}
table.excel tbody th {
    text-align:center;
    width:20px;
}
table.excel tbody td {
    vertical-align:bottom;
}
table.excel tbody td {
    padding: 0 3px;
    border: 1px solid #EEEEEE;
}
</style>
</head>

<body>
<!-- -->
<!-- -->
<?php echo $data->dump(false,false); ?>
<!-- -->
&ensp;
<a href='file://10.1.3.252/tnk-web/tnk-web/meeting/'>Dir</a>&ensp;
<a href='file://10.1.3.252/tnk-web/tnk-web/meeting/notification_eizen.xls'>Edit</a>&ensp;
<!-- -->
</body>
</html>
