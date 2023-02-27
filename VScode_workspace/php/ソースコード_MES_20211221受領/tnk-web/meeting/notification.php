<?php
//////////////////////////////////////////////////////////////////////////////
// ��ãȯ�������Ȳ� ɽ����                                                  //
//   php-excel-reader/excel_reader2.php�����                               //
//   Ʊ���ե������example.php����ɡ�Excel�ե�����λ�����ѹ���           //
//   HTML�Υإå��������ѹ� charset��UTF-8�ˤ��ʤ��Ȳ�����                  //
//   Excel�ե���������ܸ�̾�Բġ��ե����뤬fs1�Ǥ⤤���뤫�׸�Ƥ��         //
//   �ץ���༫�Τ�EUC��LF�ˤ���OK                                        //
//   dump()����False,False��Excel�����ɽ����̵����                         //
// Copyright (C) 2020-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2020/09/11 Created   notidication.php                                    //
//////////////////////////////////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE);
require_once '../php-excel-reader/excel_reader2.php';
$data = new Spreadsheet_Excel_Reader('notification.xls');
//$data = new Spreadsheet_Excel_Reader('\\\10.1.3.248\temp\test.xlsx');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/php;charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<style>
* {
    <!--
    ���Τ˱ƶ���Ϳ���롣
    font-size���礭����Ĵ����transform-origin��̵���ȸ����ʤ���
    -->
    transform-origin: top left;
    font-size: 135%;
}

table.excel {
    <!--    �����Υܡ����� border-style:none; ���ɲä�����ɽ����
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
<?php echo $data->dump(false,false); ?>
</body>
</html>
