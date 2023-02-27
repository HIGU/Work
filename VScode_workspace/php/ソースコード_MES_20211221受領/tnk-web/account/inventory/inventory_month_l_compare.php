<?php
//////////////////////////////////////////////////////////////////////////////
// 棚卸データの対前月差額 リニア       UKWLIB/W#MVTNPT                      //
//              総平均単価(仮と本決算) UKFLIB/SGAVE@L or USGAV@LIB/SGAVE@L  //
// Copyright (C) 2010-2012 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/11/10 Created   inventory_month_l_compare.php                       //
// 2011/05/24 前月の棚卸金額との差額や在庫数の増減を追加                    //
// 2012/12/05 差額の小さいものの在庫経歴のリンク先不具合を修正              //
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
$menu->set_site(20, 99);                    // site_index=20(経理メニュー) site_id=36(リニア棚卸合計金額と明細)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('リニア全体 総平均棚卸金額の比較');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('在庫予定',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
$menu->set_action('在庫経歴',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// 対象年月を取得 (年月のみに注意)
if ( isset($_SESSION['indv_ym']) ) {
    $act_ym = $_SESSION['indv_ym'];
    $s_ymd  = $act_ym . '01';   // 開始日
    $e_ymd  = $act_ym . '99';   // 終了日
} else {
    $_SESSION['s_sysmsg'] = '対象年月が指定されていません！';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}

//////////// 一頁の行数
define('PAGE', '50');

//////////// SQL 文の where 句を 共用する
// $search = "where (parts_no like 'LR%' or parts_no like 'LC%')";     // num_div 1=機工 3=リニア 5=カプラ
// $search = "where num_div='3' and tou_zai > 0 ";     // num_div 1=機工 3=リニア 5=カプラ
$search = "where invent_ym={$act_ym} and num_div='3' and pro.type is null";     // num_div 1=機工 3=リニア 5=カプラ

//////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
$query = sprintf('select
                    count(*),
                    sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as 金額
                  from
                    inventory_monthly as inv
                  left outer join
                    provide_item as pro
                  on (inv.invent_ym=pro.reg_ym and inv.parts_no=pro.parts_no)
                  %s', $search);
if ( getResult($query, $res_sum) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
}
$maxrows = $res_sum[0][0];  // 合計レコード数
$sum_kin = $res_sum[0][1];  // 合計 棚卸 金額

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
//////////// 差額計算（差額の大きい順）
$query = sprintf("
        select
            o.parts_no           as 部品番号,               --- 0
            substr(m.midsc,1,12) as 部品名,                 --- 1
            zen_zai              as 前月在庫,               --- 2
            tou_zai              as 当月在庫,               --- 3
            tou_zai - zen_zai    as 在庫増減,               --- 4
            a.average_cost       as 総平均単価,             --- 5
            Uround(zen_zai * average_cost, 0) as 前月金額,  --- 6
            Uround(tou_zai * average_cost, 0) as 当月金額,  --- 7
            Uround(tou_zai * average_cost, 0) - Uround(zen_zai * average_cost, 0) as 差額,  --- 8
            num_div              as 事業部                  --- 9
        from
            inventory_monthly as o left outer join miitem as m on o.parts_no = m.mipn 
            left outer join periodic_average_cost_history2 as a on o.parts_no = a.parts_no and a.period_ym={$act_ym}
            left outer join provide_item as pro on (o.invent_ym=pro.reg_ym and o.parts_no=pro.parts_no)
        %s 
        and a.average_cost > 0.01
        -- and Uround(tou_zai * average_cost, 0) - Uround(zen_zai * average_cost, 0) >= 500000
        -- order by serial_no ASC
        order by 差額 DESC
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
//////////// 差額計算（差額の小さい順）
$query_asc = sprintf("
        select
            o.parts_no           as 部品番号,               --- 0
            substr(m.midsc,1,12) as 部品名,                 --- 1
            zen_zai              as 前月在庫,               --- 2
            tou_zai              as 当月在庫,               --- 3
            tou_zai - zen_zai    as 在庫増減,               --- 4
            a.average_cost       as 総平均単価,             --- 5
            Uround(zen_zai * average_cost, 0) as 前月金額,  --- 6
            Uround(tou_zai * average_cost, 0) as 当月金額,  --- 7
            Uround(tou_zai * average_cost, 0) - Uround(zen_zai * average_cost, 0) as 差額,  --- 8
            num_div              as 事業部                  --- 9
        from
            inventory_monthly as o left outer join miitem as m on o.parts_no = m.mipn 
            left outer join periodic_average_cost_history2 as a on o.parts_no = a.parts_no and a.period_ym={$act_ym}
            left outer join provide_item as pro on (o.invent_ym=pro.reg_ym and o.parts_no=pro.parts_no)
        %s 
        and a.average_cost > 0.01
        -- and Uround(tou_zai * average_cost, 0) - Uround(zen_zai * average_cost, 0) <= -500000
        -- order by serial_no ASC
        order by 差額 ASC
        offset %d limit %d
    ", $search, $offset, PAGE);       // 共用 $search は使用しない
$res_asc   = array();
$field_asc = array();
if (($rows_asc = getResultWithField2($query_asc, $field_asc, $res_asc)) <= 0) {
    $_SESSION['s_sysmsg'] .= '棚卸のデータが取得できません!';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num_asc = count($field_asc);       // フィールド数取得
}
// 合計金額の計算
$query_t = sprintf("
        select
            o.parts_no           as 部品番号,               --- 0
            substr(m.midsc,1,12) as 部品名,                 --- 1
            tou_zai              as 当月在庫,               --- 2
            a.average_cost       as 総平均単価,             --- 3
            a.mate_cost          as 原材料,                 --- 4
            a.out_cost           as 外注,                   --- 5
            a.manu_cost          as 工作,                   --- 6
            a.assem_cost         as 組立,                   --- 7
            a.other_cost         as その他,                 --- 8
            a.indirect_cost      as 間接費,                 --- 9
            Uround(tou_zai * average_cost, 0) as 在庫金額,  --- 10
            num_div              as 事業部                  --- 11
        from
            inventory_monthly as o left outer join miitem as m on o.parts_no = m.mipn 
            left outer join periodic_average_cost_history2 as a on o.parts_no = a.parts_no and a.period_ym={$act_ym}
            left outer join provide_item as pro on (o.invent_ym=pro.reg_ym and o.parts_no=pro.parts_no)
        %s
        -- order by serial_no ASC
        -- order by 金額 DESC
    ", $search);       // 共用 $search
$res_t   = array();
$field_t = array();
if (($rows_t = getResultWithField2($query_t, $field_t, $res_t)) <= 0) {
    $_SESSION['s_sysmsg'] .= '棚卸のデータが取得できません!';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num_t = count($field_t);       // フィールド数取得
}
$sum_kin = 0;
for ($r=0; $r<$rows_t; $r++) {
    $sum_kin += $res_t[$r][10];
}

// 前月の合計金額の計算
$b_year  = substr($act_ym, 0, 4);
$b_month = (substr($act_ym, 4, 2) - 1);
if ($b_month < 1) {
    $b_year -= 1;
    $b_month = 12;
}
$before_ym = sprintf("%d%02d", $b_year, $b_month);
$search_b = "where invent_ym={$before_ym} and num_div='3' and pro.type is null";     // num_div 1=機工 3=リニア 5=カプラ
$query_b = sprintf("
        select
            o.parts_no           as 部品番号,               --- 0
            substr(m.midsc,1,12) as 部品名,                 --- 1
            tou_zai              as 当月在庫,               --- 2
            a.average_cost       as 総平均単価,             --- 3
            a.mate_cost          as 原材料,                 --- 4
            a.out_cost           as 外注,                   --- 5
            a.manu_cost          as 工作,                   --- 6
            a.assem_cost         as 組立,                   --- 7
            a.other_cost         as その他,                 --- 8
            a.indirect_cost      as 間接費,                 --- 9
            Uround(tou_zai * average_cost, 0) as 当月金額,  --- 10
            num_div              as 事業部                  --- 11
        from
            inventory_monthly as o left outer join miitem as m on o.parts_no = m.mipn 
            left outer join periodic_average_cost_history2 as a on o.parts_no = a.parts_no and a.period_ym={$before_ym}
            left outer join provide_item as pro on (o.invent_ym=pro.reg_ym and o.parts_no=pro.parts_no)
        %s
        -- order by serial_no ASC
        -- order by 金額 DESC
    ", $search_b);       // 共用 $search_b
$res_b   = array();
$field_b = array();
if (($rows_b = getResultWithField2($query_b, $field_b, $res_b)) <= 0) {
    $_SESSION['s_sysmsg'] .= '前月の棚卸データが取得できません!';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num_b = count($field_b);       // フィールド数取得
}
$sum_kin_b = 0;
for ($r=0; $r<$rows_b; $r++) {
    $sum_kin_b += $res_b[$r][10];
}

//////////// 前月の合計レコード数取得
$query_b = sprintf('select
                    count(*),
                    sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as 金額
                  from
                    inventory_monthly as inv
                  left outer join
                    provide_item as pro
                  on (inv.invent_ym=pro.reg_ym and inv.parts_no=pro.parts_no)
                  %s', $search_b);
$res_sum_b = array();         // 初期化
if ( getResult($query_b, $res_sum_b) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
}
$maxrows_b   = $res_sum_b[0][0];  // 前月合計レコード数

$dif_kin   = $sum_kin - $sum_kin_b;   // 棚卸差額
$maxrows_d = $maxrows - $maxrows_b;   // 点数差額

//////////// 表題の設定
$caption = "{$act_ym}　" . $menu->out_title() . "<BR>当月　金額=" . number_format($sum_kin) . '円　　' . number_format($maxrows) . "点 <BR>前月　金額=" . number_format($sum_kin_b) . '円　　' . number_format($maxrows_b) . "点 <BR>差額　金額=" . number_format($dif_kin) . '円　　' . number_format($maxrows_d) . "点 \n";
$menu->set_caption($caption);

//////////////// HTML Header を出力してブラウザーのキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'></script>
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
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
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
<?php echo $menu->out_title_border()?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='pt11b'>
                        <?php echo $menu->out_caption() ?>
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
        
        <!--------------- ここから本文の表を表示する --------------------><!--------------- 差額が＋のものを表示 -------------------->
        <br>
        <?php
        $caption = "差額の大きい物 TOP50 \n";
        $menu->set_caption($caption);
        ?>
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td nowrap align='center' class='pt11b'>
                    <?php echo $menu->out_caption() ?>
                </td>
            </tr>
        </table>
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
                    <th class='winbox' nowrap><?php echo $field[$i] ?></th>
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
                        <td class='winbox' nowrap align='right'><div class='pt10b'><?php echo ($r + $offset + 1) ?></div></td>    <!-- 行ナンバーの表示 -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        switch ($i) {
                        case 0:     // 部品番号にリンクを追加
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'><a class='link' href='javascript:void(0)' onClick='win_open(\"{$menu->out_action('在庫経歴')}?targetPartsNo=" . urlencode($res[$r][$i]) . "&noMenu=yes\");' target='_self' style='text-decoration:none;'>{$res[$r][$i]}</a></div></td>\n";
                            break;
                        case 1:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 2:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 3:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 4:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 6:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 7:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 8:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 9:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'><center>{$res[$r][$i]}</center></div></td>\n";
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
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
        
        <!--------------- 差額が−のものを表示 -------------------->
        <br>
        <?php
        $caption = "差額の小さい物 TOP50 \n";
        $menu->set_caption($caption);
        ?>
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td nowrap align='center' class='pt11b'>
                    <?php echo $menu->out_caption() ?>
                </td>
            </tr>
        </table>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num_asc; $i++) {             // フィールド数分繰返し
                ?>
                    <th class='winbox' nowrap><?php echo $field_asc[$i] ?></th>
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
                for ($r=0; $r<$rows_asc; $r++) {
                ?>
                    <tr>
                        <td class='winbox' nowrap align='right'><div class='pt10b'><?php echo ($r + $offset + 1) ?></div></td>    <!-- 行ナンバーの表示 -->
                    <?php
                    for ($i=0; $i<$num_asc; $i++) {         // レコード数分繰返し
                        switch ($i) {
                        case 0:     // 部品番号にリンクを追加
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'><a class='link' href='javascript:void(0)' onClick='win_open(\"{$menu->out_action('在庫経歴')}?targetPartsNo=" . urlencode($res_asc[$r][$i]) . "&noMenu=yes\");' target='_self' style='text-decoration:none;'>{$res_asc[$r][$i]}</a></div></td>\n";
                            break;
                        case 1:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res_asc[$r][$i]}</div></td>\n";
                            break;
                        case 2:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res_asc[$r][$i], 0), "</div></td>\n";
                            break;
                        case 3:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res_asc[$r][$i], 0), "</div></td>\n";
                            break;
                        case 4:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res_asc[$r][$i], 0), "</div></td>\n";
                            break;
                        case 6:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res_asc[$r][$i], 0), "</div></td>\n";
                            break;
                        case 7:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res_asc[$r][$i], 0), "</div></td>\n";
                            break;
                        case 8:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res_asc[$r][$i], 0), "</div></td>\n";
                            break;
                        case 9:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'><center>{$res_asc[$r][$i]}</center></div></td>\n";
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res_asc[$r][$i], 2), "</div></td>\n";
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
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
