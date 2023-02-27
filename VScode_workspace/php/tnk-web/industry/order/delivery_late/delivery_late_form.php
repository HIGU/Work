<?php
//////////////////////////////////////////////////////////////////////////////
// 納期遅れ部品の照会 条件選択フォーム                                      //
// リニアのみ・バイモル・ツール他の検索も対応済。使用の場合は               //
// このプログラムのコメントを解除するだけで使用可能                         //
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/11/09 Created  delivery_late_form.php                               //
// 2011/11/10 focusをJAVAScriptに変更 set_focus()                           //
// 2011/11/22 セッションデータの受け渡しでエラー発生の為issetに変更         //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);     // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../function.php');
require_once ('../../../tnk_func.php');
require_once ('../../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../../ControllerHTTP_Class.php');    // TNK 全共通 MVC Controller Class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 52);                    // site_index=30(生産メニュー) site_id=52(納期遅れ部品の照会)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('納期遅れ部品 の 照 会 (条件選択)');
//////////// 表題の設定
$menu->set_caption('下記の必要な条件を入力又は選択して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('納期遅れ部品表示',   INDUST . 'order/delivery_late/delivery_late_view.php');
$menu->set_action('納期遅れ集計表',   INDUST . 'order/delivery_late/delivery_late_summary.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// セッションのインスタンスを生成
$session = new Session();

/////////////// 受け渡し変数の初期化
if ( isset($_SESSION['paya_parts_no']) ) {
    $parts_no = $_SESSION['paya_parts_no'];
} else {
    $parts_no = '';              // 初期化
    $_SESSION['paya_parts_no'] = $parts_no;
}
if ( isset($_SESSION['payable_div']) ) {
    $div = $_SESSION['payable_div'];
} else {
    $div = '';
    $_SESSION['payable_div'] = $div;
}
if ( isset($_SESSION['paya_vendor']) ) {
    $vendor = $_SESSION['paya_vendor'];
} else {
    $vendor = '';
    $_SESSION['paya_vendor'] = $vendor;
}
if ( isset($_SESSION['paya_strdate']) ) {
    $str_date = $_SESSION['paya_strdate'];
} else {
    $str_date = '';     // 初期化
    $_SESSION['paya_strdate'] = $str_date;
    /*************************************
    $year  = date('Y');
    $month = date('m') - 1; // １ヶ月前から
    if ($month == 0) {
        $month = 12;
        $year -= 1;
    } else {
        $month = sprintf('%02d', $month);
    }
    $year  = date('Y') - 5; // ５年前からをコメント
    $month = date('m');
    $str_date = $year . $month . '01';  // 初期化 (前月の１日からに変更)
    $session->add_local('paya_strdate', $str_date);
    *************************************/
}
if ( isset($_SESSION['paya_enddate']) ) {
    $end_date = $_SESSION['paya_enddate'];
} else {
    $end_date = '';     // 初期化
    $_SESSION['paya_enddate'] = $end_date;
}

if ( isset($_SESSION['payable_page']) ) {   // １ページ表示行数設定
    $paya_page = $_SESSION['payable_page'];
} else {
    $paya_page = 25;             // Default 25
    $_SESSION['payable_page'] = $paya_page;
}

/////////// summary_view で追加されたパラメーター
if ( isset($_SESSION['payable_s_ym']) ) {
    $s_ym = $_SESSION['payable_s_ym'];
} else {
    $s_ym = '';
}
if ( isset($_SESSION['payable_e_ym']) ) {
    $e_ym = $_SESSION['payable_e_ym'];
} else {
    $e_ym = '';
}
if ( isset($_SESSION['payable_div']) ) {
    $sum_div = $_SESSION['payable_div'];
} else {
    $sum_div = ' ';
}

/////////// summary_view が押された
if (isset($_REQUEST['summary_view'])) {
    if (isset($_REQUEST['s_ym'])) {
        $_SESSION['payable_s_ym'] = $_REQUEST['s_ym'];
    }
    if (isset($_REQUEST['e_ym'])) {
        $_SESSION['payable_e_ym'] = $_REQUEST['e_ym'];
    }
    if (isset($_REQUEST['sum_div'])) {
        $_SESSION['payable_div'] = $_REQUEST['sum_div'];
    }
    header('Location: ' . H_WEB_HOST . $menu->out_action('納期遅れ集計表'));
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<!--    ファイル指定の場合 -->
<script language='JavaScript' src='./delivery_late_form.js?<?= $uniq ?>'>
</script>

<script language="JavaScript">
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    document.payable_form.parts_no.focus();
    document.payable_form.parts_no.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10 {
    font-size:      10pt;
    font-weight:    normal;
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
td {
    font-size: 10pt;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
}
.caption_font {
    font-size:          11pt;
    font-weight:        bold;
    font-family:        monospace;
    background-color:   blue;
    color:              white;
}
-->
</style>
</head>
</style>
<body onLoad='set_focus()' style='overflow-y:hidden;'>
    <center>
<?=$menu->out_title_border()?>
        
        <form name='payable_form' action='<?=$menu->out_action('納期遅れ部品表示')?>' method='get' onSubmit='return chk_payable_form(this)'>
            <!----------------- ここは 本文を表示する ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%' class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                    <td class='winbox' bgcolor='yellow' colspan='2' align='center'>
                        <div class='caption_font'><?=$menu->out_caption(), "\n"?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        部品番号の指定(指定しない場合は下の部門を指定して下さい)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='parts_no' class='pt12b' size='9' value='<?= $parts_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        部門を選択して下さい(部品を指定した場合は無視されます)
                    </td>
                    <td class='winbox' align='center'>
                        <select name='div' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($div==' ') echo 'selected' ?>>全部門</option>
                            <option value='C'<?php if($div=='C') echo 'selected' ?>>カプラ全体</option>
                            <option value='SC'<?php if($div=='SC') echo 'selected' ?>>カプラ特注</option>
                            <option value='CS'<?php if($div=='CS') echo 'selected' ?>>カプラ標準</option>
                            <option value='L'<?php if($div=='L') echo 'selected' ?>>リニア全体</option>
                            <!-- <option value='LN'<?php if($div=='LN') echo 'selected' ?>>リニアのみ</option> -->
                            <!-- <option value='B'<?php if($div=='B') echo 'selected' ?>>バイモル</option> -->
                            <!-- <option value='T'<?php if($div=='T') echo 'selected' ?>>ツール他</option> -->
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        発注先のコードを指定(部品を指定した場合は無視されます)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='vendor' class='pt12b' size='5' value='<?= $vendor ?>' maxlength='5'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        納期を指定して下さい(必須)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='str_date' size='9' value='<?php echo($str_date); ?>' maxlength='8' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                        ～
                        <input type='text' name='end_date' size='9' value='<?php echo($end_date); ?>' maxlength='8' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        １ページの表示行数を指定して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='paya_page' size='4' value='<?= $paya_page ?>' maxlength='3' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                        初期値：25
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' name='paya_view' value='実行' >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
        
        <hr>
        
        <form name='summary_form' action='<?=$menu->out_self()?>' method='get'>
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%' class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' bgcolor='yellow' colspan='2' align='center'>
                        <div class='caption_font'>協力工場毎の合計金額の照会</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        開始年月を指定
                    </td>
                    <td class='winbox' align='center'>
                        <select name='s_ym' class='pt11b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if ($s_ym == $ym) {
                                    printf("<option value='%d' selected>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else {
                                    printf("<option value='%d'>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                }
                                if (substr($ym, 4, 2) != '01') {
                                    $ym--;
                                } else {
                                    $ym = $ym - 100;
                                    $ym = $ym + 11;
                                }
                                if ($ym <= 200010) {
                                    break;
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        終了年月を指定
                    </td>
                    <td class='winbox' align='center'>
                        <select name='e_ym' class='pt11b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if ($e_ym == $ym) {
                                    printf("<option value='%d' selected>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else {
                                    printf("<option value='%d'>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                }
                                if ($ym <= 200010) {
                                    break;
                                }
                                if (substr($ym, 4, 2) != '01') {
                                    $ym--;
                                } else {
                                    $ym = $ym - 100;
                                    $ym = $ym + 11;
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        対象部門の指定
                    </td>
                    <td class='winbox' align='center'>
                        <select name='sum_div' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($sum_div==' ') echo 'selected' ?>>全部門</option>
                            <option value='C'<?php if($sum_div=='C') echo 'selected' ?>>カプラ全体</option>
                            <option value='CS'<?php if($sum_div=='CS') echo 'selected' ?>>カプラ標準</option>
                            <option value='SC'<?php if($sum_div=='SC') echo 'selected' ?>>カプラ特注</option>
                            <option value='L'<?php if($sum_div=='L') echo 'selected' ?>>リニア全体</option>
                            <!-- <option value='LN'<?php if($sum_div=='LN') echo 'selected' ?>>リニアのみ</option> -->
                            <!-- <option value='B'<?php if($sum_div=='B') echo 'selected' ?>>バイモル</option> -->
                            <!-- <option value='T'<?php if($sum_div=='T') echo 'selected' ?>>ツール他</option> -->
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' name='summary_view' value='実行' >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
