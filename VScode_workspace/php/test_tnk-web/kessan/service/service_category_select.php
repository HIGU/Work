<?php
//////////////////////////////////////////////////////////////////////////
// サービス割合 部門選択 メニュー                                       //
// Copyright(C) 2003 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp   //
// Changed history                                                      //
// 2003/10/17 Created   service_category_select.php                     //
// 2003/10/24 submit 先のスクリプトを分岐するように変更(入力と照会)     //
// 2007/01/24 MenuHeaderクラス対応                                      //
//////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ("../../function.php");
require_once ("../../tnk_func.php");
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(10,  5);                    // site_index=10(損益メニュー) site_id=5(サービス割合メニュー)
////////////// リターンアドレス設定(絶対指定する場合)
$menu->set_RetUrl($_SESSION['service_referer']);    // 分岐処理前に保存されている呼出元をセットする
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//if ($_GET['exec'] == 'entry') {
//    $menu_title = "$view_ym サービス割合入力 部門選択";
//} elseif ($_GET['exec'] == 'view') {
    $menu_title = "$view_ym サービス割合照会 部門選択";
//} else {
//    $menu_title = "$view_ym サービス割合照会 部門選択";
//}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title($menu_title);
//////////// 表題の設定
$menu->set_caption('部門を選択して下さい。');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<style type="text/css">
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt10b {
    font-size:   0.8em;
    font-weight: bold;
    font-family: monospace;
}
.pt11b {
    font-size:   0.9em;
    font-weight: bold;
    font-family: monospace;
}
.pt12b {
    font-size:   1.0em;
    font-weight: bold;
    font-family: monospace;
}
.explain_font {
    font-size: 8.5pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
.menuButton {
    width:          200px;
    font-size:      1.0em; /* 12pt */
    font-weight:    bold;
}
-->
</style>
</head>
<body style='overflow-y:hidden;'>
    <center>
<?php echo $menu->out_title_border() ?>
        <br>
        <br>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
            <tr><td> <!-- ダミー(デザイン用) -->
            <table bgcolor='#d6d3ce' cellspacing='1' cellpadding='5' border='1' bordercolordark='white' bordercolorlight='#bdaa90'>
                <tr>
                    <td align='center' colspan='1' class='pt11b'>
                        <?php echo $menu->out_caption(), "\n" ?>
                    </td>
                </tr>
                <?php
                $section_name = array(
                    "test1",
                    "test2",
                    "test3",
                    "test4",
                    "test5",
                    "test6",
                    "test7",
                    "test8",
                    "test9",
                    "test10",
                );
                $section_id = array(
                    1,
                    2,
                    3,
                    4,
                    5,
                    6,
                    7,
                    8,
                    9,
                    10,
                );
            for ($i=0; $i<10; $i++) {
                echo "<form action='service_percentage_input.php' method='post'>\n";
                echo "<tr>\n";
                echo "    <td align='center' bgcolor='#ceffce'>\n";
                echo "        <input class='menuButton' type='submit' name='section_name' value='{$section_name[$i]}'>\n";
                echo "        <input type='hidden' name='section_id' value='{$section_id[$i]}'>\n";
                echo "    </td>\n";
                echo "</tr>\n";
                echo "</form>\n";
            }
                ?>
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
    </center>
</body>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
