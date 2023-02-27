<?php
//////////////////////////////////////////////////////////////////////////////
// 社員メニューの自己情報 経歴 印刷の Branch (分岐)処理                     //
// Copyright(C) 2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp            //
// Changed history                                                          //
// 2007/10/15 Created   print_emp_branch_user.php                           //
// 2010/06/16 暫定的に大渕さん（970268）が印刷できるように変更         大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
// ini_set('display_errors','1');          // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');               // 出力バッファをgzip圧縮
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');
require_once ('../../tnk_func.php');
access_log();                           // Script Name は自動取得

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>社員メニュー自己経歴印刷 分岐処理</title>
<style type='text/css'>
<!--
body {
    margin:     20%;
    font-size:  24pt;
}
-->
</style>
</head>
<body>
    <center>
        印刷イメージ(PDF)生成中です。<br>
        お待ち下さい。<br>
        <img src='../../img/tnk-turbine.gif' width=68 height=72>
    </center>

    <script type='text/javascript' language='JavaScript'>
    <!--
        location = '<?php echo H_WEB_HOST ?>/emp/print/print_emp_history_user.php?targetUser=<?php echo $_REQUEST['targetUser']?>';
    // -->
    </script>
</body>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
