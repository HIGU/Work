<?php
//////////////////////////////////////////////////////////////////////////////
// 納期遅れ部品の照会 協力工場毎の合計金額のサマリー照会(年月による期間指定)//
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/11/09 Created delivery_late_summary.php                             //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 52);                    // site_index=30(生産メニュー) site_id=52(納期遅れ部品照会のグループ)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('納期遅れ部品 の 照 会');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('総材料費明細',   INDUST . 'material/materialCost_view.php');
$menu->set_action('納期遅れ部品表示',   INDUST . 'order/delivery_late/delivery_late_view.php');

//////////// 一頁の行数
define('PAGE', '200');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// 対象年月を取得 (年月～年月に注意)
if ( isset($_SESSION['payable_s_ym']) && isset($_SESSION['payable_e_ym']) ) {
    $s_ymd = $_SESSION['payable_s_ym'] . '01';   // 開始日
    // $e_ymd = $_SESSION['payable_e_ym'] . '99';   // 終了日
    $e_ym = $_SESSION['payable_e_ym'] + 1;   // 次の月を求める
    $Y4 = substr($e_ym, 0, 4);
    $M2 = substr($e_ym, 4, 2);
    if ($M2 > 12) {
        $Y4 += 1;
        $M2  = 1;
    }
    $e_ymd = date('Ymd', (mktime(0, 0, 0, $M2, 1, $Y4) - 1));   // 終了年月日
    $_SESSION['test_date'] = $e_ymd;
} else {
    $_SESSION['s_sysmsg'] = '対象年月が指定されていません！';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
}

//////////// 対象 事業部
if ( isset($_SESSION['payable_div']) ) {
    $paya_div = $_SESSION['payable_div'];
} else {
    $_SESSION['s_sysmsg'] = '対象製品グループが指定されていません！';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
}

/////////// begin トランザクション開始
if ($con = funcConnect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'funcConnect() error';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
}

//////////// SQL 文の where 句を 共用する 01111=栃木日東工器 00222=製造課特注 99999=諸口
$search = "where proc.delivery <= {$e_ymd} and proc.delivery >= {$s_ymd} and uke_date >= 0 and uke_date > proc.delivery and data.sei_no > 0 and (data.order_q - data.cut_genpin) > 0";

//////////// SQL 文の where 句を 共用する
switch ($paya_div) {
case ' ';   // 全体
    $search_kin = $search . " and (data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%') and proc.locate != '52   '";
    $caption_div = '部門：全体(外注別)';
    break;
case 'C';   // カプラ 全体
    $search_kin = $search . " and data.parts_no like 'C%' and proc.locate != '52   '";
    $caption_div = '部門：カプラ全体(外注別)';
    break;
case 'CS';  // カプラ 標準
    $search_kin = $search . " and data.parts_no like 'C%' and data.kouji_no not like '%SC%' and proc.locate != '52   '";
    $caption_div = '部門：カプラ標準(外注別)';
    break;
case 'SC';  // カプラ 特注
    $search_kin = $search . " and data.parts_no like 'C%' and data.kouji_no like '%SC%' and proc.locate != '52   '";
    $caption_div = '部門：カプラ特注(外注別)';
    break;
case 'L';   // リニア 全体
    $search_kin = $search . " and data.parts_no like 'L%' and proc.locate != '52   '";
    $caption_div = '部門：リニア全体(外注別)';
    break;
case 'LN';  // リニア のみ
    $search_kin = $search . " and (data.parts_no like 'L%' and data.parts_no NOT like 'LC%%' AND data.parts_no NOT like 'LR%%') and proc.locate != '52   '";
    $caption_div = '部門：リニアのみ(外注別)';
    break;
case 'B';   // バイモル
    $search_kin = $search . " and (data.parts_no like 'LC%%' OR data.parts_no like 'LR%%') and proc.locate != '52   '";
    $caption_div = '部門：バイモル(外注別)';
    break;
case 'T';   // ツール他
    $search_kin = $search . " and (data.parts_no NOT like 'C%%' AND data.parts_no NOT like 'L%%') and proc.locate != '52   '";
    $caption_div = '部門：ツール他(外注別)';
    break;
}
//////////// 内作を除く合計金額 (科目1～5)科目6以上を除く
$query = sprintf("select count(*) 
                    from order_data      AS data
                    left outer join
                        order_process   AS proc
                                        using(sei_no, order_no, vendor)
                    LEFT OUTER JOIN
                        order_plan      AS plan     USING (sei_no)
                    left outer join
                        vendor_master   AS mast
                                        on(data.vendor = mast.vendor)
                    left outer join
                        miitem          AS item
                                        on(data.parts_no = item.mipn)
                    %s
                    ", $search_kin);
if (getUniResTrs($con, $query, $maxrows) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("納期:%s～%sで<br>データがありません。", $s_ymd, $e_ymd );
    $_SESSION['s_sysmsg'] .= '納期遅れ件数の取得が出来ません。';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    // $sum_kin = $paya_ctoku[0][0];
    // $maxrows = $paya_ctoku[0][1];    // GROUP BY の時は集約関数は使えない
}

$query = sprintf("select
                        sum(Uround(data.order_q * data.order_price,0))
                    from
                            order_data      AS data
                        left outer join
                            order_process   AS proc
                                        using(sei_no, order_no, vendor)
                        LEFT OUTER JOIN
                            order_plan      AS plan     USING (sei_no)
                        left outer join
                            vendor_master   AS mast
                                        on(data.vendor = mast.vendor)
                        left outer join
                            miitem          AS item
                                        on(data.parts_no = item.mipn)
                    %s
                    ", $search_kin);
if (getUniResTrs($con, $query, $sum_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= '納期遅れ合計金額の取得が出来ません。';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
}

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
            data.vendor              as 発注先,                            -- 00
            mast.name                as 発注先名,                          -- 01
            count(*)                 as 納期遅れ件数,                      -- 02
            sum(Uround(data.order_q * data.order_price,0)) as 納期遅れ金額 -- 03
        from
                order_data      AS data
            left outer join
                order_process   AS proc
                                        using(sei_no, order_no, vendor)
            LEFT OUTER JOIN
                order_plan      AS plan     USING (sei_no)
            left outer join
                vendor_master   AS mast
                                        on(data.vendor = mast.vendor)
            left outer join
                miitem          AS item
                                        on(data.parts_no = item.mipn)
        %s 
        GROUP BY data.vendor, mast.name
        ORDER BY 納期遅れ件数 DESC, 納期遅れ金額 DESC, data.vendor ASC
        offset %d limit %d
    ", $search_kin, $offset, PAGE);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResWithFieldTrs($con, $query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("納期:%s～%sで<br>データがありません。", $s_ymd, $e_ymd );
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    query_affected_trans($con, 'commit');         // transaction commit
    $num = count($field);       // フィールド数取得
}

//////////// 表題の設定
$caption = "年月：" . format_date($s_ymd) . '～' . format_date($e_ymd) . '　合計金額：' . number_format($sum_kin) . '　合計件数：' . number_format($maxrows);
$menu->set_caption("{$caption_div}　　{$caption}");

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
function set_focus(){
    // document.body.focus();   // F2/F12キーを有効化する対応
    document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
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
.pt9b {
    font-size:      9pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt10b {
    font:           10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font:           11pt;
    font-weight:    bold;
    color:          blue;
}
th {
    background-color:   yellow;
    color:              blue;
    font:               10pt;
    font-weight:        bold;
    font-family:        monospace;
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:              blue;
    text-decoration:    none;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?=$menu->out_title_border()?>
        
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
                        <?= $menu->out_caption(), "\n" ?>
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
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
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
                        <td class='winbox' nowrap align='right'><div class='pt10b'><?= ($r + $offset + 1) ?></div></td>    <!-- 行ナンバーの表示 -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        switch ($i) {
                        case 1:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 2:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9b'><a href='", $menu->out_action('納期遅れ部品表示'), "?str_date={$s_ymd}&end_date={$e_ymd}&parts_no=&div={$paya_div}&vendor=", urlencode("{$res[$r][0]}"), "'>", number_format($res[$r][$i]), "</a></div></td>\n";
                            break;
                        case 3:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i]), "</div></td>\n";
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
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
ob_end_flush();     // 出力バッファーをgzip圧縮 END
?>
