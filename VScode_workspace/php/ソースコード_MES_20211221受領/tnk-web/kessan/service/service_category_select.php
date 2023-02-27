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

$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
$url_referer     = $_SESSION['service_referer'];    // 分岐処理前に保存されている呼出元をセットする

//////////// Submit先スクリプト名の取得
if (isset($_GET['exec'])) {
    $_GET['exec'] = stripslashes($_GET['exec']);
} else {
    $_GET['exec'] = 'view';     // Default
}
if ($_GET['exec'] == 'entry') {
    $script_name = 'service_percentage_input.php';
} elseif ($_GET['exec'] == 'view') {
    $script_name = 'service_percentage_view.php';
} else {
    $script_name = 'service_percentage_view.php';
}

//////////// 対象年月のセッションデータ取得
if (isset($_SESSION['service_ym'])) {
    $service_ym = $_SESSION['service_ym']; 
} else {
    $service_ym = date("Ym");        // セッションデータがない場合の初期値(前月)
    if (substr($service_ym,4,2) != 01) {
        $service_ym--;
    } else {
        $service_ym = $service_ym - 100;
        $service_ym = $service_ym + 11;   // 前年の12月にセット
    }
}

//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:i:s");
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if (substr($service_ym,6,2) == '32') {
    $view_ym = substr($service_ym,0,6) . '決算';
} else {
    $view_ym = $service_ym;
}
if ($_GET['exec'] == 'entry') {
    $menu_title = "$view_ym サービス割合入力 部門選択";
} elseif ($_GET['exec'] == 'view') {
    $menu_title = "$view_ym サービス割合照会 部門選択";
} else {
    $menu_title = "$view_ym サービス割合照会 部門選択";
}
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title($menu_title);
//////////// 表題の設定
$menu->set_caption('部門を選択して下さい。');

//////////// 部門の抜出し
$query = "select trim(section_name), sid from section_master left outer join cate_allocation on sid=group_id
            left outer join act_table on dest_id=act_id
            where orign_id=0 and
                    act_flg='f'
            group by section_name, sid
            order by sid";
$res = array();
if ( ($rows=getResult2($query, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = '間接部門が取得できません！';
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
} else {
    for ($i=0; $i<$rows; $i++) {
        $section_name[$i] = $res[$i][0];
        $section_id[$i]   = $res[$i][1];
    }
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
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
            for ($i=0; $i<$rows; $i++) {
                echo "<form action='$script_name' method='post'>\n";
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
