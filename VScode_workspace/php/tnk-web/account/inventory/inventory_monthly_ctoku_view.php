<?php
//////////////////////////////////////////////////////////////////////////////
// 棚卸 金額 の照会 (カプラ特注)  更新元 UKWLIB/W#MVTNPT                    //
// Copyright (C) 2003-2010      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2003/11/25 Created   inventory_monthly_ctoku_view.php                    //
//            SERIAL型のインデックスを作成し SQLを高速化した                //
// 2003/12/04 テーブルをmonth_end→inventory_monthlyへ変更 金額順表示へ     //
// 2003/12/09 特注の抜出しロジック追加(初回の場合抜出す)とテーブル変更      //
//            テーブル名 inventory_monthly → inventory_monthly_ctoku       //
//   ファイル名変更inventory_month_ctoku_view→inventory_monthly_ctoku_view //
// 2004/01/14 $_SESSION['act_ym'] → $_SESSION['ind_ym'] へ変更             //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2005/02/09 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/08/20 set_focus()の機能は MenuHeader で実装しているので無効化した   //
// 2007/03/08 部品番号クリックで在庫予定照会・経歴照会にリンクを追加        //
// 2007/03/22 parts_stock_view.php → parts_stock_history_Main.php へ変更   //
// 2010/02/24 データ取出し用に表示件数を10000に一時変更                大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(20, 32);                    // site_index=20(経理メニュー) site_id=32(Ｃ特注 棚卸合計金額と明細)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('カプラ特注品 棚卸金額の照会');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('在庫予定',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
$menu->set_action('在庫経歴',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// 対象年月を取得 (年月のみに注意)
if ( isset($_SESSION['ind_ym']) ) {
    $act_ym = $_SESSION['ind_ym'];
    $s_ymd  = $act_ym . '01';   // 開始日
    $e_ymd  = $act_ym . '99';   // 終了日
} else {
    $_SESSION['s_sysmsg'] = '対象年月が指定されていません！';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}

//////////// 一頁の行数
define('PAGE', '100');

//////////// 対象年月でカプラと宇宙のみを抜出したデータがあるかチェックを兼用する
//////////// SQL 文の where 句を 共用しない
$search = "where invent_ym={$act_ym} and item='カプラ特注'";

//////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
$query = sprintf("select sum_money_z, sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // 初期化
if ( getResult($query, $res_sum) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}
$sum_kin_z = $res_sum[0][0];  // 合計 棚卸 金額(前月)
$sum_kin   = $res_sum[0][1];  // 合計 棚卸 金額(当月)
$maxrows   = $res_sum[0][2];  // 合計レコード数

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

//////////// SQL 文の where 句を 共用しない
$search = "where invent_ym={$act_ym}";
//////////// カプラ特注の照会フォームの作表
$query = sprintf("
        select
            parts_no                as 部品番号,        -- 0
            substr(m.midsc,1,12)    as 部品名,          -- 1
            par_code                as 親製品,          -- 2
            tou_zai                 as 当月在庫,        -- 3
            gai_tan                 as 外注単価,        -- 4
            Uround(tou_zai * gai_tan, 0)
                                    as 外注金額,        -- 5
            nai_tan                 as 内作単価,        -- 6
            Uround(tou_zai * nai_tan, 0)
                                    as 内作金額,        -- 7
            Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)
                                    as 金額,            -- 8
            kouji_no                as 工事番号,        -- 9
            num_div                 as 事業部           --10
        from
            inventory_monthly_ctoku as inv
        left outer join
            miitem as m
        on inv.parts_no = m.mipn
        %s 
        order by 金額 DESC
        offset %d limit %d
        ", $search, $offset, PAGE);       // 共用 $search は使用しない
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= '棚卸のデータが取得できません!';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
}

//////////// 表題の設定
$caption = "{$act_ym}　" . $menu->out_title() . "　合計=" . number_format($sum_kin) . '円　金額順　' . number_format($maxrows) . "点 \n";
$menu->set_caption($caption);

////////////// HTML Header を出力してブラウザーのキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java()?>
<?= $menu->out_css()?>

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
function win_open(url) {
    var w = 900;
    var h = 600;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    // window.open(url, 'view_win2', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    window.open(url, '', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
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
    font-family: monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family: monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    color:          blue;
}
th {
    background-color:yellow;
    color:blue;
    font:bold 10pt;
    font-family: monospace;
}
a {
    color: red;
}
a.link {
    color: blue;
}
a:hover {
    background-color: blue;
    color: white;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border()?>
        
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
                        <?= $menu->out_caption() ?>
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
                        case 0:     // 部品番号にリンクを追加
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'><a class='link' href='javascript:void(0)' onClick='win_open(\"{$menu->out_action('在庫経歴')}?targetPartsNo=" . urlencode($res[$r][$i]) . "&noMenu=yes\");' target='_self' style='text-decoration:none;'>{$res[$r][$i]}</a></div></td>\n";
                            break;
                        case 1:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 3:
                        case 5:
                        case 7:
                        case 8:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 4:
                        case 6:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
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
<?= $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
