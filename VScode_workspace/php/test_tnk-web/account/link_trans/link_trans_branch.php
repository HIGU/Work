<?php
//////////////////////////////////////////////////////////////////////////////
// 各種データ入力の Branch (分岐)処理 メニュー                              //
// Copyright (C) 2006-2007 Norihisa.Ohya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2006/09/13 Created   various_data_branch.php                             //
// 2007/10/05 フォルダooyaを削除した為アドレスを変更                        //
// 2007/10/19 E_ALLをE_STRICTへ→コメント化                                 //
// 2007/10/24 プログラムの最後に改行を追加                                  //
// 2007/12/13 対象年月の受け渡し用に$requestを設定                          //
// 2007/12/29 各種メニューを新プログラムへリンク変更                        //
// 2008/01/09 呼び出し元の保存を$sessionに変更                              //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK 全共通 MVC Controller Class
access_log();             

if (isset($_POST['wage_ym'])) {
    $_SESSION['wage_ym'] = $_POST['wage_ym'];                   // 対象年月をセッションに保存
}
if (isset($_POST['customer'])) {
    $_SESSION['customer'] = $_POST['customer'];                   // 対象年月をセッションに保存
}

////// 呼出元の保存
$_SESSION['link_referer'] = $_SERVER['HTTP_REFERER'];     // 呼出もとのURLをセッションに保存

switch ($_POST['service_name']) {
    case '債権債務照会' : $script_name = 'link_trans/link_trans_obligation_view.php'; break;
    case '取引高照会' : $script_name = 'link_trans/link_trans_transaction_view.php' ; break;
    
    default: $script_name = 'link_trans_menu.php';          // 呼出もとへ帰る
              $url_name    = $_SESSION['link_referer'];        // 呼出もとのURL 別メニューから呼び出された時の対応
}
?>
<!DOCTYPE html>
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>組立賃率 分岐処理</TITLE>
<style type="text/css">
<!--
body        {margin:20%;font-size:24pt;}
-->
</style>
</head>
<body>
    <center>
        処理中です。お待ち下さい。<br>
        <img src='../../img/tnk-turbine.gif' width=68 height=72>
    </center>

    <script language="JavaScript">
    <!--
    <?php
        if (isset($url_name)) {
            echo "location = '$url_name'";
        } else {
            echo "location = '" . H_WEB_HOST . ACT . "$script_name'";
        }
    ?>
    // -->
    </script>
</body>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>

