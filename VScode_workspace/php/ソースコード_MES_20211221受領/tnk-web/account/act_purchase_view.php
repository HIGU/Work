<?php
//////////////////////////////////////////////////////////////////////////////
// 仕入計上の照会 ＆ チェック用  条件指定 (買掛金 - 有償支給金額)           //
// Copyright (C) 2003-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/11/25 Created   act_purchase_view.php                               //
// 2003/11/19 自動仕訳確認リストと突合せが出来る様に以下のロジックを追加    //
//            原材料(1)と部品仕掛Ｃ(2-5) 科目(6)- の合計金額 諸口を除外     //
//            リニアの原材料1 を除外                                        //
// 2003/12/09 テーブルを act_purchase_header へ変更して高速化               //
// 2004/01/14 $_SESSION['act_ym'] → $_SESSION['ind_ym'] へ変更             //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2005/02/09 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/02/27 表のデザイン変更bgcolor='black'cellspacing='1'を<table>に追加 //
// 2005/05/20 db_connect() → funcConnect() へ変更 pgsql.phpで統一のため    //
// 2005/08/20 set_focus()の機能は MenuHeader で実装しているので無効化した   //
// 2013/01/28 バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま 大谷//
// 2013/02/15 当期の累計金額を追加                                      大谷//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(20, 31);                    // site_index=30(生産メニュー) site_id=31(仕入金額照会)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('仕入計上金額 照会');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// 対象年月を取得 (年月のみに注意)
if ( isset($_SESSION['ind_ym']) ) {
    $act_ym = $_SESSION['ind_ym'];
    $s_ymd  = $act_ym . '01';   // 開始日
    $e_ymd  = $act_ym . '99';   // 終了日
} else {
    $_SESSION['s_sysmsg'] = '月次対象年月が指定されていません!';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}
//////////// 表題の設定
$menu->set_caption($act_ym . '　' . $menu->out_title());

$menu->set_caption2('当期累計　' . $menu->out_title());

//////////// 一頁の行数
define('PAGE', '25');

/////////// begin トランザクション開始
if ($con = funcConnect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'funcConnect() error';
    exit();
}

$act_yy = substr($act_ym, 0, 4);
$act_mm = substr($act_ym, 4, 2);
if ($act_mm >= 4 && $act_mm < 13) {
    $act_mm = '04';
} else {
    $act_yy -= 1;
    $act_mm  = '04';
}

$str_ym = $act_yy . $act_mm;

//////////// データをヘッダーファイルから読込む
// 全体 単月
$query = "select sum_payable, sum_provide, cnt_payable, cnt_provide
            from act_purchase_header
            where purchase_ym={$act_ym} and item='全体'";
$res = array();     // 初期化
if ( getResultTrs($con, $query, $res) <= 0) {
    $_SESSION['s_sysmsg'] .= '全体の金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $paya_sum_kin = $res[0][0];         // 買掛
    $prov_sum_kin = $res[0][1];         // 有償支給
}
// 全体 当期累計
$query_t = "select sum(sum_payable), sum(sum_provide), sum(cnt_payable), sum(cnt_provide)
              from act_purchase_header
              where purchase_ym>={$str_ym} and purchase_ym<={$act_ym} and item='全体'";
$res_t = array();     // 初期化
if ( getResultTrs($con, $query_t, $res_t) <= 0) {
    $_SESSION['s_sysmsg'] .= '全体の金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $paya_sum_kin_t = $res_t[0][0];         // 買掛
    $prov_sum_kin_t = $res_t[0][1];         // 有償支給
}
// カプラ
$query = "select sum_payable, sum_provide, cnt_payable, cnt_provide
            from act_purchase_header
            where purchase_ym={$act_ym} and item='カプラ'";
$res = array();     // 初期化
if ( getResultTrs($con, $query, $res) <= 0) {
    $_SESSION['s_sysmsg'] .= 'カプラの金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $paya_c_kin = $res[0][0];         // 買掛
    $prov_c_kin = $res[0][1];         // 有償支給
}
// カプラ 当期累計
$query_t = "select sum(sum_payable), sum(sum_provide), sum(cnt_payable), sum(cnt_provide)
              from act_purchase_header
              where purchase_ym>={$str_ym} and purchase_ym<={$act_ym} and item='カプラ'";
$res_t = array();     // 初期化
if ( getResultTrs($con, $query_t, $res_t) <= 0) {
    $_SESSION['s_sysmsg'] .= '全体の金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $paya_c_kin_t = $res_t[0][0];         // 買掛
    $prov_c_kin_t = $res_t[0][1];         // 有償支給
}

// カプラ特注
$query = "select sum_payable, sum_provide, cnt_payable, cnt_provide
            from act_purchase_header
            where purchase_ym={$act_ym} and item='カプラ特注'";
$res = array();     // 初期化
if ( getResultTrs($con, $query, $res) <= 0) {
    $_SESSION['s_sysmsg'] .= 'カプラ特注の金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $paya_c_toku_kin = $res[0][0];         // 買掛
    $prov_c_toku_kin = $res[0][1];         // 有償支給
}
// カプラ特注 当期累計
$query_t = "select sum(sum_payable), sum(sum_provide), sum(cnt_payable), sum(cnt_provide)
              from act_purchase_header
              where purchase_ym>={$str_ym} and purchase_ym<={$act_ym} and item='カプラ特注'";
$res_t = array();     // 初期化
if ( getResultTrs($con, $query_t, $res_t) <= 0) {
    $_SESSION['s_sysmsg'] .= '全体の金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $paya_c_toku_kin_t = $res_t[0][0];         // 買掛
    $prov_c_toku_kin_t = $res_t[0][1];         // 有償支給
}

// リニア
$query = "select sum_payable, sum_provide, cnt_payable, cnt_provide
            from act_purchase_header
            where purchase_ym={$act_ym} and item='リニア'";
$res = array();     // 初期化
if ( getResultTrs($con, $query, $res) <= 0) {
    $_SESSION['s_sysmsg'] .= 'リニアの金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $paya_l_kin = $res[0][0];         // 買掛
    $prov_l_kin = $res[0][1];         // 有償支給
}
// リニア 当期累計
$query_t = "select sum(sum_payable), sum(sum_provide), sum(cnt_payable), sum(cnt_provide)
              from act_purchase_header
              where purchase_ym>={$str_ym} and purchase_ym<={$act_ym} and item='リニア'";
$res_t = array();     // 初期化
if ( getResultTrs($con, $query_t, $res_t) <= 0) {
    $_SESSION['s_sysmsg'] .= '全体の金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $paya_l_kin_t = $res_t[0][0];         // 買掛
    $prov_l_kin_t = $res_t[0][1];         // 有償支給
}

// バイモル
$query = "select sum_payable, sum_provide, cnt_payable, cnt_provide
            from act_purchase_header
            where purchase_ym={$act_ym} and item='バイモル'";
$res = array();     // 初期化
if ( getResultTrs($con, $query, $res) <= 0) {
    $_SESSION['s_sysmsg'] .= 'バイモルの金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $paya_l_bimor_kin = $res[0][0];         // 買掛
    $prov_l_bimor_kin = $res[0][1];         // 有償支給
}
// バイモル 当期累計
$query_t = "select sum(sum_payable), sum(sum_provide), sum(cnt_payable), sum(cnt_provide)
              from act_purchase_header
              where purchase_ym>={$str_ym} and purchase_ym<={$act_ym} and item='バイモル'";
$res_t = array();     // 初期化
if ( getResultTrs($con, $query_t, $res_t) <= 0) {
    $_SESSION['s_sysmsg'] .= '全体の金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $paya_l_bimor_kin_t = $res_t[0][0];         // 買掛
    $prov_l_bimor_kin_t = $res_t[0][1];         // 有償支給
}

/////////// commit トランザクション終了
query_affected_trans($con, 'commit');

//////////// 仕入金額の計算 (買掛金 - 有償支給金額)
// 全体
$sum_kin         = ($paya_sum_kin - $prov_sum_kin);
// カプラ
$c_sum_kin       = ($paya_c_kin - $prov_c_kin);
// カプラ特注
$c_toku_sum_kin  = ($paya_c_toku_kin - $prov_c_toku_kin);
// リニア
$l_sum_kin       = ($paya_l_kin - $prov_l_kin);
// リニアBIMOR
$l_bimor_sum_kin = ($paya_l_bimor_kin - $prov_l_bimor_kin);

// 全体 当期累計
$sum_kin_t         = ($paya_sum_kin_t - $prov_sum_kin_t);
// カプラ 当期累計
$c_sum_kin_t       = ($paya_c_kin_t - $prov_c_kin_t);
// カプラ特注 当期累計
$c_toku_sum_kin_t  = ($paya_c_toku_kin_t - $prov_c_toku_kin_t);
// リニア 当期累計
$l_sum_kin_t       = ($paya_l_kin_t - $prov_l_kin_t);
// リニアBIMOR 当期累計
$l_bimor_sum_kin_t = ($paya_l_bimor_kin_t - $prov_l_bimor_kin_t);

//////////// ページオフセット設定
if ( isset($_POST['forward']) ) {                       // 次頁が押された
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // 次頁が押された
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
    }
} elseif ( isset($_GET['page_keep']) ) {               // 現在のページを維持する
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // 初回の場合は０で初期化
}
$offset = $_SESSION['offset'];

///////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?= $uniq ?>'></script>
-->

<script language="JavaScript">
<!--
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt10b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          teal;
}
.pt11b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font:bold           12pt;
    font-family:        monospace;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?=$menu->out_title_border()?>
        
        <div class='pt10b'><?=$menu->out_caption()?></div>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' border='1' cellspacing='1' cellpadding='15'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>項　目</th>
                    <th class='winbox' nowrap>買掛金額</th>
                    <th class='winbox' nowrap>有償支給</th>
                    <th class='winbox' nowrap>仕入金額</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>合計金額(科目1〜5)</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($paya_sum_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($prov_sum_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>　<?= number_format($sum_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>カプラ(科目1〜5)</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($paya_c_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($prov_c_kin) . "\n" ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>　<?= number_format($c_sum_kin) ?></div.
                    </td>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>カプラ特注</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($paya_c_toku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($prov_c_toku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>　<?= number_format($c_toku_sum_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>リニア(科目1〜5)</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($paya_l_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($prov_l_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>　<?= number_format($l_sum_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>リニア 液体ポンプ</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($paya_l_bimor_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($prov_l_bimor_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>　<?= number_format($l_bimor_sum_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR><BR>
        
        <div class='pt10b'><?=$menu->out_caption2()?></div>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' border='1' cellspacing='1' cellpadding='15'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>項　目</th>
                    <th class='winbox' nowrap>買掛金額</th>
                    <th class='winbox' nowrap>有償支給</th>
                    <th class='winbox' nowrap>仕入金額</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>合計金額(科目1〜5)</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($paya_sum_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($prov_sum_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>　<?= number_format($sum_kin_t) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>カプラ(科目1〜5)</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($paya_c_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($prov_c_kin_t) . "\n" ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>　<?= number_format($c_sum_kin_t) ?></div.
                    </td>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>カプラ特注</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($paya_c_toku_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($prov_c_toku_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>　<?= number_format($c_toku_sum_kin_t) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>リニア(科目1〜5)</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($paya_l_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($prov_l_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>　<?= number_format($l_sum_kin_t) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>リニア 液体ポンプ</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($paya_l_bimor_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>　<?= number_format($prov_l_bimor_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>　<?= number_format($l_bimor_sum_kin_t) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();             // 出力バッファーをgzip圧縮 END
?>
