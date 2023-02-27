<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 状況 照会  profit_loss_sales_view.php                               //
// Copyright (C) 2018-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2018/03/30 Created   profit_loss_sales_view.php（sales_view.php引用）    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');            // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');            // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');          // TNK 全共通 menu class
require_once ('../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
//////////// セッションのインスタンスを登録
$session = new Session();

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
//700, 11);                    // site_index=01(売上メニュー) site_id=11(売上実績明細)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
///// 期・月の取得
$menu->set_title("第22期　11月度　売 上 状 況 照 会");

//////////// 呼出先のaction名とアドレス設定

$current_script  = $_SERVER['PHP_SELF'];    // 現在実行中のスクリプト名を保存


/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<?php echo $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // document.body.focus();                          // F2/F12キーで戻るための対応
    // document.form_name.element_name.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-family:    monospace;
}
.pt10 {
    font-size:l     10pt;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
.winboxy {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:   yellow;
    color:              blue;
    font-size:          12pt;
    font-weight:        bold;
    font-family:        monospace;
}
.right{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>
        <BR>
        <!--------------- ここから本文の表を表示する -------------------->
        <form name='invent' action='<?php echo $menu->out_self() ?>' method='post'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <div class='pt11b' align='right'><単位：円></div>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>売上区分</th>
                    <th class='winbox' nowrap>カプラ</th>
                    <th class='winbox' nowrap>リニア</th>
                    <th class='winbox' nowrap>ツール</th>
                    <th class='winbox' nowrap>試験修理</th>
                    <th class='winbox' nowrap>商品管理</th>
                    <th class='winbox' nowrap>合計</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <tr>
                    <th class='winbox' nowrap>完成</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>個別</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>手打</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>調整</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>移動</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>直納</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>売上</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>振替</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>受注</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(600, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <td class='winboxy' nowrap align='center'>合計</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(600, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(600, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(600, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(600, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(600, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(600, 0) . "</td>\n";
                    ?>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <BR>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <div class='pt11b' align='right'><単位：円></div>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>Ｃ修理</th>
                    <th class='winbox' nowrap>新品調整</th>
                    <th class='winbox' nowrap>商管調整</th>
                    <th class='winbox' nowrap>　</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                
                <tr>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo 100 ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo 200 ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo 300 ?>' class='right'>
                    </td>
                    <td colspan='4' align='center'>
                        <input type='submit' name='entry' value='登録' >
                    </td>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <BR>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <div class='pt11b' align='right'><単位：円></div>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>　</th>
                    <th class='winbox' nowrap>カプラ</th>
                    <th class='winbox' nowrap>リニア</th>
                    <th class='winbox' nowrap>ツール</th>
                    <th class='winbox' nowrap>試験修理</th>
                    <th class='winbox' nowrap>商品管理</th>
                    <th class='winbox' nowrap>合計</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <tr>
                    <th class='winbox' nowrap>実手打</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>その他売上</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>部品売上</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>総売上</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    ?>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <BR>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <div class='pt11b' align='right'><単位：千円></div>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>カプラ目標</th>
                    <th class='winbox' nowrap>リニア目標</th>
                    <th class='winbox' nowrap>ツール目標</th>
                    <th class='winbox' nowrap>試修目標</th>
                    <th class='winbox' nowrap>商管目標</th>
                    <th class='winbox' nowrap>全体目標</th>
                    <th class='winbox' nowrap>　</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                
                <tr>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo 500 ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo 600 ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo 700 ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo 800 ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo 900 ?>' class='right'>
                    </td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(3500, 0) . "</div></td>\n";
                    ?>
                    <td colspan='4' align='center'>
                        <input type='submit' name='entry' value='登録' >
                    </td>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <BR>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <div class='pt11b' align='right'><単位：千円></div>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>　</th>
                    <th class='winbox' nowrap>カプラ</th>
                    <th class='winbox' nowrap>リニア</th>
                    <th class='winbox' nowrap>ツール</th>
                    <th class='winbox' nowrap>試験修理</th>
                    <th class='winbox' nowrap>商品管理</th>
                    <th class='winbox' nowrap>合計</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <tr>
                    <th class='winbox' nowrap>標準品</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>特注品</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>製品計</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>　</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>　</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>　</td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>部品</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>その他</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format(700, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>部品・その他計</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>　</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>　</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>　</td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>実績</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(700, 0) . "</td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>達成度％</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(60, 1) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(60, 1) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(60, 1) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(60, 1) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(60, 1) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format(60, 1) . "</td>\n";
                    ?>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        </form>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
