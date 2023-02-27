<?php
//////////////////////////////////////////////////////////////////////////////
// A伝状況の照会 条件選択フォーム  更新元 UKWLIB/W#MIADIMDE                 //
// Copyright (C) 2016-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2016/03/25 Created  aden_details_form.php                                //
// 2017/08/10 計画完了済・未完了の条件を追加                                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);     // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK 全共通 MVC Controller Class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 99);                    // site_index=40(生産メニュー) site_id=10(買掛実績)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('Ａ 伝 状 況 の 照 会 (条件選択)');
//////////// 表題の設定
$menu->set_caption('下記の必要な条件を入力又は選択して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('A伝状況表示',     INDUST . 'aden_details/aden_details_view.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// セッションのインスタンスを生成
$session = new Session();

/////////////// 受け渡し変数の初期化
if ($session->get('paya_parts_no') != '') {
    $parts_no = $session->get('paya_parts_no');
} else {
    $parts_no = '';              // 初期化
}
if ($session->get('paya_kamoku') != '') {
    $kamoku = $session->get('paya_kamoku');
} else {
    $kamoku = '';              // 初期化
}
if ( isset($_SESSION['payable_div']) ) {
    $div = $_SESSION['payable_div'];
} else {
    $div = '';
    $_SESSION['payable_div'] = $div;
}
if ( isset($_SESSION['payable_finishdel']) ) {
    $finish_del = $_SESSION['payable_finishdel'];
} else {
    $finish_del = ' ';
    $_SESSION['payable_finishdel'] = $finish_del;
}
if ( isset($_SESSION['payable_delicom']) ) {
    $deli_com = $_SESSION['payable_delicom'];
} else {
    $deli_com = ' ';
    $_SESSION['payable_delicom'] = $deli_com;
}
if ( isset($_SESSION['payable_answer']) ) {
    $answer = $_SESSION['payable_answer'];
} else {
    $answer = ' ';
    $_SESSION['payable_answer'] = $answer;
}
if ( isset($_SESSION['payable_finish']) ) {
    $finish = $_SESSION['payable_finish'];
} else {
    $finish = ' ';
    $_SESSION['payable_finish'] = $finish;
}
if ( isset($_SESSION['payable_koujino']) ) {
    $kouji_no = $_SESSION['payable_koujino'];
} else {
    $kouji_no = ' ';
    $_SESSION['payable_koujino'] = $kouji_no;
}
if ( isset($_SESSION['payable_order']) ) {
    $order = $_SESSION['payable_order'];
} else {
    $order = ' ';
    $_SESSION['payable_order'] = $order;
}
if ( isset($_SESSION['paya_vendor']) ) {
    $vendor = $_SESSION['paya_vendor'];
} else {
    $vendor = '';
    $_SESSION['paya_vendor'] = $vendor;
}
if ($session->get_local('paya_ltstrdate') != '') {
    $lt_str_date = $session->get_local('paya_ltstrdate');
    $lt_str_date = $_SESSION['paya_ltstrdate'];
} elseif(isset($_SESSION['paya_ltstrdate'])) {
    $lt_str_date = $_SESSION['paya_ltstrdate'];
} else {
    $lt_str_date = '';  // 初期化
    $session->add_local('paya_ltstrdate', $lt_str_date);
}
if ($session->get_local('paya_ltenddate') != '') {
    $lt_end_date = $session->get_local('paya_ltenddate');
    $lt_end_date = $_SESSION['paya_ltenddate'];
} elseif(isset($_SESSION['paya_ltenddate'])) {
    $lt_end_date = $_SESSION['paya_ltenddate'];
} else {
    $lt_end_date = '';     // 初期化
    $session->add_local('paya_ltenddate', $lt_end_date);
}

if ($session->get_local('paya_strdate') != '') {
    $str_date = $session->get_local('paya_strdate');
    $str_date = $_SESSION['paya_strdate'];
} elseif(isset($_SESSION['paya_strdate'])) {
    $str_date = $_SESSION['paya_strdate'];
} else {
    /*************************************
    $year  = date('Y');
    $month = date('m') - 1; // １ヶ月前からをコメント
    if ($month == 0) {
        $month = 12;
        $year -= 1;
    } else {
        $month = sprintf('%02d', $month);
    }
    *************************************/
    $year  = date('Y') - 5; // ５年前から
    $month = date('m');
    $str_date = $year . $month . '01';  // 初期化 (前月の１日からに変更)
    $session->add_local('paya_strdate', $str_date);
}
if ($session->get_local('paya_enddate') != '') {
    $end_date = $session->get_local('paya_enddate');
    $end_date = $_SESSION['paya_enddate'];
} else {
    $end_date = '99999999';     // 初期化
    $session->add_local('paya_enddate', $end_date);
}

if ( isset($_SESSION['payable_page']) ) {   // １ページ表示行数設定
    $paya_page = $_SESSION['payable_page'];
} else {
    $paya_page = 25;             // Default 25
    $_SESSION['payable_page'] = $paya_page;
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<!--    ファイル指定の場合 -->
<script language='JavaScript' src='./aden_details_form.js?<?= $uniq ?>'>
</script>

<script language="JavaScript">
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
//    document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
//    document.form_name.element_name.select();
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
<body onLoad='document.payable_form.parts_no.focus(); document.payable_form.parts_no.select()'>
    <center>
<?=$menu->out_title_border()?>
        
        <form name='payable_form' action='<?=$menu->out_action('A伝状況表示')?>' method='get' onSubmit='return chk_payable_form(this)'>
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
                        ASSY No.の指定
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='parts_no' class='pt12b' size='9' value='<?= $parts_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        日付(A伝受注日)を指定して下さい(必須)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='str_date' size='9' value='<?php echo($str_date); ?>' maxlength='8' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                        ～
                        <input type='text' name='end_date' size='9' value='<?php echo($end_date); ?>' maxlength='8' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        A伝回答状況を選択して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <select name='answer' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($answer==' ') echo 'selected' ?>>すべて</option>
                            <option value='Y'<?php if($answer=='Y') echo 'selected' ?>>回答済</option>
                            <option value='N'<?php if($answer=='N') echo 'selected' ?>>未回答</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        計画完了状況を選択して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <select name='finish' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($finish==' ') echo 'selected' ?>>すべて</option>
                            <option value='Y'<?php if($finish=='Y') echo 'selected' ?>>完了済</option>
                            <option value='N'<?php if($finish=='N') echo 'selected' ?>>未完了</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        納期コメントを選択して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <select name='deli_com' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($deli_com==' ') echo 'selected' ?>>すべて</option>
                            <option value='Y'<?php if($deli_com=='Y') echo 'selected' ?>>希望通り</option>
                            <option value=1<?php if($deli_com==1) echo 'selected' ?>>部品遅れ</option>
                            <option value=2<?php if($deli_com==2) echo 'selected' ?>>設計変更</option>
                            <option value=3<?php if($deli_com==3) echo 'selected' ?>>L/T不足</option>
                            <option value=4<?php if($deli_com==4) echo 'selected' ?>>伝送遅れ</option>
                            <option value=5<?php if($deli_com==5) echo 'selected' ?>>その他</option>
                            <option value='N'<?php if($deli_com=='N') echo 'selected' ?>>未入力</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        工番を選択して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <select name='kouji_no' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($kouji_no==' ') echo 'selected' ?>>すべて</option>
                            <option value='S'<?php if($kouji_no=='S') echo 'selected' ?>>SCのみ</option>
                            <option value='C'<?php if($kouji_no=='C') echo 'selected' ?>>CQのみ</option>
                            <option value='SCQ'<?php if($kouji_no=='SCQ') echo 'selected' ?>>SC+CQ</option>
                            <option value='N'<?php if($kouji_no=='N') echo 'selected' ?>>工番なし</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        納期L/T差を指定してください
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='lt_str_date' size='5' value='<?php echo($lt_str_date); ?>' maxlength='4' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                        ～
                        <input type='text' name='lt_end_date' size='5' value='<?php echo($lt_end_date); ?>' maxlength='4' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        完成遅れを選択して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <select name='finish_del' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($finish_del==' ') echo 'selected' ?>>すべて</option>
                            <option value='D'<?php if($finish_del=='D') echo 'selected' ?>>納期遅れ</option>
                            <option value='Y'<?php if($finish_del=='Y') echo 'selected' ?>>納期通り</option>
                            <option value='A'<?php if($finish_del=='A') echo 'selected' ?>>納期前倒</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        検索順を選択して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <select name='order' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($order==' ') echo 'selected' ?>>受注日順</option>
                            <option value='1'<?php if($order=='1') echo 'selected' ?>>希望納期順</option>
                            <option value='2'<?php if($order=='2') echo 'selected' ?>>L/T差順</option>
                            <option value='3'<?php if($order=='3') echo 'selected' ?>>完成遅れ順</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        １ページの表示行数を指定して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='paya_page' size='4' value='<?= $paya_page ?>' maxlength='4' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
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
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
