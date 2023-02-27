<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理システムの権限マスター保守                       MVC View部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/14 Created   Account_ViewHeader.php                              //
//            HeaderのscrollBarを出すのにheightを30に設定したため,こちらは26//
//////////////////////////////////////////////////////////////////////////////
require_once ('../../com/define.php');

?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>権限マスターの項目</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<LINK rel='stylesheet' href='<?php echo CONTEXT_PATH?>com/cssConversion.css' type='text/css'>
<style type='text/css'>
body {
    background-image:none;
}
</style>
<script type='text/javascript' src='Account.js'></script>
</head>
<body>
<center>
    <table width='99%' border='1' class='Conversion'>
        <tr class='Conversion'>
            <td class='HED Conversion' width=' 8%' height='26'>No.</th>
            <td class='HED Conversion' width='12%'>&nbsp;</th>
            <td class='HED Conversion' width='35%'>機能コード</th>
            <td class='HED Conversion' width='20%'>社員コード</th>
            <td class='HED Conversion' width='25%'>社員氏名</th>
        </tr>
    </table>
</center>
</body>
</html>
