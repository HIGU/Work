<?php
//////////////////////////////////////////////////////////////////////////////
// 買掛ヒストリの照会 ＆ チェック用  更新元 UKWLIB/W#HIBCTR                 //
// Copyright (C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/11/18 Created   act_payable_view.php                                //
// 2003/11/19 自動仕訳確認リストと突合せが出来る様に以下のロジックを追加    //
//            原材料(1)と部品仕掛Ｃ(2-5) 科目(6)- の合計金額 諸口を除外     //
//            リニアの原材料1 を除外                                        //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2005/02/15 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/08/20 set_focus()の機能は MenuHeader で実装しているので無効化した   //
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
$menu->set_site(20, 10);                    // site_index=20(経理メニュー) site_id=10(買掛金の日報チェックリスト)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('買掛金計上処理 チェックリスト');
//////////// 表題の設定
// $menu->set_caption('サンプルでアイテムマスターを表示しています');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// 対象年月日を取得
$act_ymd = $_SESSION['act_ymd'];
if ($act_ymd == '') {
    $act_ymd = date_offset(2);
}

//////////// 一頁の行数
define('PAGE', '22');

//////////// SQL 文の where 句を 共用する
$search = sprintf('where act_date=%d', $act_ymd);

//////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
$query = sprintf('select count(*) from act_payable %s', $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
}

//////////// SQL 文の where 句を 共用する 01111=栃木日東工器 00222=製造課特注 99999=諸口
$search_kin = sprintf("where act_date=%d and vendor !='01111' and vendor !='00222' and vendor !='99999'", $act_ymd);

//////////// 内作を除く合計金額
$query = sprintf("select sum(Uround(order_price * siharai,0)) from act_payable %s", $search_kin);
if ( getUniResult($query, $sum_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= '合計金額の取得に失敗';      // .= メッセージを追加する
}

//////////// 内作を除く合計金額 科目1
$query = sprintf("select sum(Uround(order_price * siharai,0)) from act_payable %s and kamoku=1", $search_kin);
getUniResult($query, $kamoku1_kin);

//////////// 内作を除く合計金額 科目1でリニア  これを科目2-5へ振分ける
$query = sprintf("select sum(Uround(order_price * siharai,0)) from act_payable %s and kamoku=1 and div='L'", $search_kin);
getUniResult($query, $kamoku1L_kin);
$kamoku1_kin = ($kamoku1_kin - $kamoku1L_kin);

//////////// 内作を除く合計金額 科目2-5
$query = sprintf("select sum(Uround(order_price * siharai,0)) from act_payable %s 
                  and kamoku>=2 and kamoku<=5", $search_kin);
getUniResult($query, $kamoku2_5_kin);
$kamoku2_5_kin = ($kamoku2_5_kin + $kamoku1L_kin);

//////////// 内作を除く合計金額 科目6以上
$query = sprintf("select sum(Uround(order_price * siharai,0)) from act_payable %s and kamoku>=6", $search_kin);
getUniResult($query, $kamoku6__kin);

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

//////////// 表形式のデータ表示用のサンプル Query & 初期化
$query = sprintf("
        select
            act_date    as 処理日,
            type_no     as \"T\",
            uke_no      as 受付番号,
            uke_date    as 受付日,
            ken_date    as 検収日,
            vendor      as 発注先,
            name        as 発注先名,
            parts_no    as 部品番号,
            order_no    as 注文番号,
            koutei      as 工程記号,
            mtl_cond    as 条件,
            order_price as 発注単価,
            genpin      as 現品数,
            siharai     as 支払数,
            Uround(order_price * siharai,0) as 発注金額,
            div         as 事業部,
            kamoku      as 科目,
            sei_no      as 製造番号
        from
            act_payable left outer join vendor_master using(vendor)
        %s 
        ORDER BY vendor, uke_no, type_no, seq ASC
        offset %d limit %d
    ", $search, $offset, PAGE);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("買掛金の計上日:%s で<br>データがありません。", format_date($act_ymd) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
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
function set_focus(){
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
.pt9 {
    font-size:      9pt;
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
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
-->
</style>
</head>
<body onLoad='set_focus()' style='overflow-y:hidden;'>
    <center>
<?=$menu->out_title_border()?>
        
        <table width='250' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    合　計　金　額
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= number_format($sum_kin) . "\n" ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    部品仕掛Ｃ2～5
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= number_format($kamoku2_5_kin) . "\n" ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    原 材 料 1
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= number_format($kamoku1_kin) . "\n" ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    買掛科目 6～
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= number_format($kamoku6__kin) . "\n" ?>
                </td>
            </tr>
        </table>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='pt11b'>
                        <?= format_date($act_ymd) . '　' . $menu->out_title() . "\n" ?>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='次頁'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                ?>
                    <th class='winbox' nowrap><?= $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                        <!--  bgcolor='#ffffc6' 薄い黄色 -->
                        <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                <?php
                for ($r=0; $r<$rows; $r++) {
                ?>
                    <tr>
                        <td class='winbox' nowrap align='right'><span class='pt10b'><?= ($r + $offset + 1) ?></span></td>    <!-- 行ナンバーの表示 -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        switch ($i) {
                        case 6:
                            echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case 11:
                        case 12:
                        case 13:
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 2) . "</span></td>\n";
                            break;
                        case 14:
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i]) . "</span></td>\n";
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='center'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                        }
                    }
                    ?>
                    </tr>
                <?php
                }
                ?>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();     // gzip圧縮 END
?>
