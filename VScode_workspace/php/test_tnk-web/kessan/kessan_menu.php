<?php
//////////////////////////////////////////////////////////////////////////
// 月次・中間・決算処理 メニュー                                        //
// 2002/03/22 Copyright(C) 2003 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// 変更経歴                                                             //
// 2002/08/09 register_globals = Off 対応                               //
// 2002/08/27 フレーム 対応                                             //
// 2002/09/20 サイトメニューに下位メニューを追加                        //
// 2003/01/15 動的メニューアイコンに変更  menu_bar()                    //
// 2003/02/14 売上関係ニュー のフォントを style で指定に変更            //
//                              ブラウザーによる変更が出来ない様にした  //
// 2003/10/17 サービス割合処理メニューを追加                            //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
// require_once ("../define.php");    // function.php で require されている
require_once ("../tnk_func.php");   // menu_bar() で使用
access_log();                       // Script Name は自動取得
// $sysmsg = $_SESSION["s_sysmsg"];
// $_SESSION["s_sysmsg"] = NULL;
$_SESSION["site_index"] = 10;       // とりあえずは１０番目のメニューにしておく
$_SESSION["site_id"] = 999;     // とりあえず下位メニュー無し (0 < であり) 999 は下位メニュー全て表示
// $_SESSION["dev_req_menu"] = date("H:i");

/////////// 認証チェック
if ( !isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"]) ) {
    $_SESSION["s_sysmsg"] = "認証されていないか認証期限が切れました。Login し直して下さい。";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}
unset($_SESSION['act_offset']);     // 部門コードテーブルで使用するoffset値を削除
unset($_SESSION['cd_offset']);      // コードテーブルで使用するoffset値を削除

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<title>月次・中間・決算処理 メニュー</title>

<style type="text/css">
<!--
.top-font {
    font-size: 12pt;
    font-family: monospace;
    font-weight: bold;
}
select {
    background-color: teal;
    color: white;
}
textarea {
    background-color: black;
    color: white;
}
input.sousin {
    background-color: red;
}
input.text {
    background-color: black;
    color: white;
}
.pt11 {
    font-size: 11pt;
}
-->
</style>

<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';
// -->
</script>

</head>

<body bgcolor="#ffffff" text="#000000">
<table width="100%" height="100%"><tr>

    <!-- right view -->

    <td valign="top">
        <script language="Javascript">
        <!--
            str=navigator.appName.toUpperCase();
            if(str.indexOf("NETSCAPE")>=0) document.write("<table width='100%' height=585 bgcolor='#ffffff' cellpadding=10>");
            if(str.indexOf("EXPLORER")>=0) document.write("<table width='100%' height='100%' bgcolor='#ffffff' cellpadding=10>");
        //-->
        </script>
        <noscript><table width="100%" height="100%" bgcolor="#ffffff" cellpadding=10></noscript>
        <tr><td valign="top">
            <table width="100%">
                <tr><td><p><img src="../img/t_nitto_logo3.gif" width=348 height=83></p></td></tr>
                <tr><td align="center" class='top-font'>月次・中間・決算処理 メニュー</td></tr>
            </table>

            <table width="100%">
                <tr><td align="center">
                <img src='../img/tnk-turbine.gif'>
                </td></tr>
            </table>

            <table width="100%">
                <tr>
                    <td align="center">
                        <form method="post" action="act_table_mnt.php">
                            <input type='image' alt='旧部門コード表メンテナンス' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_act_table_mnt.png","旧部門コード表 保守",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                    <td align="center">
                        <form method="post" action="act_table_mnt_new.php">
                            <input type='image' alt='新部門コード表メンテナンス' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_act_table_mnt_new.png","新部門コード表 保守",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                </tr>

                <tr>
                    <td align="center">
                        <form method="post" action="category_mnt.php">
                            <input type='image' alt='大分類配賦率の保守(損益関係)' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_category_mnt.png","大分類配賦率 保守",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                    <td align="center">
                        <form method="post" action="allocation_mnt.php">
                            <input type='image' alt='小分類配賦率の保守(損益と原価関係)' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_allocation_mnt.png","小分類配賦率 保守",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                </tr>

                <tr>
                    <td align="center">
                        <form method="post" action="cd_table_mnt.php">
                            <input type='image' alt='経理・組織・人事コードテーブルの保守' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_cd_table_mnt.png","コードテーブル 保守",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                    <td align="center">
                        <form method="post" action="machine_labor_rate_mnt.php">
                            <input type='image' alt='製造の機械賃率計算表の作成・照会' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_machine_labor_rate.png","機械賃率 作成・照会",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                </tr>

                <tr>
                    <td align="center">
                        <form method="post" action="service/service_percentage_menu.php">
                            <input type='image' alt='直接部門へのサービス割合の入力' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_servis.png","サービス割合の入力",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                    <td align="center">
                        <form method="post" action="wage_rate.php">
                            <input type='image' alt='組立賃率計算表の作成・照会' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_wage_rate.png","組立賃率の作成・照会",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                </tr>

                <tr>
                    <td align="center">
                        <form method="post" action="kessan_menu.php">
                            <input type='image' alt='作業応援月報の入力' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_aid.png","作業応援月報の入力",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                    <td align="center">
                        <form method="post" action="profit_loss_select.php">
                            <input type='image' alt='月次損益関係 作成・照会' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_profit_loss_select.png","月次損益 作成・照会",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                </tr>

                <tr>
                    <td align="center">
                        <form method="post" action="kessan_menu.php">
                            <input type='image' alt='空のアイテム' border=0 src='../img/menu_item.gif'>
                        </form>
                    </td>
                    <td align="center">
                        <form method="post" action="kessan_menu.php">
                            <input type='image' alt='空のアイテム' border=0 src='../img/menu_item.gif'>
                        </form>
                    </td>
                </tr>
            </table>

        </td></tr>
        <!--
        <tr><td valign="bottom">
            <img src="../img/php4.gif" width=64 height=32>
            <img src="../img/linux.gif" width=74 height=32>  
            <img src="../img/redhat.gif" width=96 height=32>   
            <img src="../img/apache.gif" width=259 height=32> 
            <img src="../img/pgsql.gif" width=160 height=32>
        </td></tr>
        -->
    </td>
</tr></table>
</body>
</html>
