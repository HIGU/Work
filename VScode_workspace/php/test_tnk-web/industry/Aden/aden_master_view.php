<?php
//////////////////////////////////////////////////////////////////////////////
// Ａ伝情報ファイルの照会 ＆ チェック用  更新元 UKWLIB/W#MIADIM             //
// Copyright(C) 2003-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2003/11/27 Created   aden_master_view.php                                //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2004/10/21 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2004/10/22 画面情報(menuOnOff)を取得して生産製品名の表示制御でレイアウト //
//            ＳＣ工番と製品番号assy_no(parts_no)で検索できる機能追加       //
// 2004/10/23 SC工番は８桁だが kouji_noは９桁なので trim(kouji_no)を追加    //
//            組立日程計画データの表示機能追加(ボタンで表示・非表示可)      //
// 2004/10/28 組立計画on/off時に現在のページを維持それに伴い１頁=13行へ変更 //
// 2005/01/18 栃木日東工器のロゴを右下に表示追加 background-image           //
// 2010/02/02 石崎さん依頼により、検索対象年月日を無制限から9年前までに     //
//            変更。$ken_dateと$ken_date_viewを設定                    大谷 //
// 2011/01/07 石崎さん依頼により、検索対象年月日を無制限から8年前までに     //
//            変更。$ken_dateと$ken_date_viewを設定                    大谷 //
// 2015/02/06 A伝未回答の照会を追加aden_mikan                          大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数(date_offset()を使用)
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

//////////// 現在の最新情報は１日前(稼働日での前日)
$yesterday = format_date(date_offset(1));
//////////// 検索対象年数の計算
$ken_date      = date_offset(1) - 80000;                // 検索用年月日
$ken_date_view = format_date(date_offset(1) - 80000);   // 表示用検索年月日
//////////// パラメーターの取得
if (isset($_REQUEST['aden_no'])) {
    $aden_no = $_REQUEST['aden_no'];
    $_SESSION['aden_no'] = $aden_no;
    $_SESSION['aden_select'] = 'aden_no';
    $aden_select = $_SESSION['aden_select'];
    $aden_assy_no = '';
    $sc_no = '';
} elseif (isset($_REQUEST['aden_assy_no'])) {
    $aden_assy_no = $_REQUEST['aden_assy_no'];
    $_SESSION['aden_assy_no'] = $aden_assy_no;
    $_SESSION['aden_select'] = 'aden_assy_no';
    $aden_select = $_SESSION['aden_select'];
    $aden_no = '';
    $sc_no = '';
} elseif (isset($_REQUEST['sc_no'])) {
    $sc_no = $_REQUEST['sc_no'];
    $_SESSION['sc_no'] = $sc_no;
    $_SESSION['aden_select'] = 'sc_no';
    $aden_select = $_SESSION['aden_select'];
    $aden_no = '';
    $aden_assy_no = '';
} elseif (isset($_REQUEST['aden_mikan'])) {
    $_SESSION['aden_select'] = 'aden_mikan';
    $aden_select = $_SESSION['aden_select'];
    $aden_no = '';
    $aden_assy_no = '';
    $sc_no = '';
} elseif (isset($_REQUEST['aden_mikanc'])) {
    $_SESSION['aden_select'] = 'aden_mikanc';
    $aden_select = $_SESSION['aden_select'];
    $aden_no = '';
    $aden_assy_no = '';
    $sc_no = '';
} elseif (isset($_REQUEST['aden_mikanl'])) {
    $_SESSION['aden_select'] = 'aden_mikanl';
    $aden_select = $_SESSION['aden_select'];
    $aden_no = '';
    $aden_assy_no = '';
    $sc_no = '';
} else {
    $aden_no      = @$_SESSION['aden_no'];
    $aden_assy_no = @$_SESSION['aden_assy_no'];
    $sc_no        = @$_SESSION['sc_no'];
    $aden_select  = @$_SESSION['aden_select'];
}
$aden_no      = str_replace('*', '%', $aden_no);
$aden_assy_no = str_replace('*', '%', $aden_assy_no);
$sc_no        = str_replace('*', '%', $sc_no);

/////////// 画面情報の取得
if ($_SESSION['site_view'] == 'on') {
    $display = 'normal';
} else {
    $display = 'wide';
}
/////////// 組立日程計画の表示制御
if (isset($_REQUEST['aden_schedule'])) {
    if (!isset($_SESSION['aden_schedule'])) {
        $_SESSION['aden_schedule'] = 'on';
    } else {
        unset($_SESSION['aden_schedule']);
    }
    $_REQUEST['page_keep'] = 'on';      // 現在のページを維持
}
$aden_schedule = @$_SESSION['aden_schedule'];

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 13);                    // 生産=20 Ａ伝=13

////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);          // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('Ａ 伝 情 報 の 照 会');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   INDUST . 'Aden/aden_master_view.php');

//////////// JavaScript Stylesheet File 等のcache防止
$uniq = uniqid('target');

//////////// 一頁の行数
if ($aden_schedule) {
    define('PAGE', '20');
} else {
    define('PAGE', '20');   // old=25
}

//////////// SQL 文の where 句を 共用する
if ($aden_select == 'aden_no') {
    if ($aden_no != '') {
        //$search = "WHERE aden_no LIKE '{$aden_no}'";
        $search = "WHERE aden.aden_no LIKE '{$aden_no}' AND aden.espoir_deli >= '{$ken_date}'";
    } else {
        //$search = '';
        $search = "WHERE aden.espoir_deli >= '{$ken_date}'";
    }
} elseif ($aden_select == 'aden_assy_no') {
    if ($aden_assy_no != '') {
        //$search = "where aden.parts_no like '{$aden_assy_no}'";
        $search = "where aden.parts_no like '{$aden_assy_no}' AND aden.espoir_deli >= '{$ken_date}'";
    } else {
        //$search = "where aden.parts_no != ''";
        $search = "where aden.parts_no != '' AND aden.espoir_deli >= '{$ken_date}'";
    }
} elseif ($aden_select == 'sc_no') {
    if ($sc_no != '') {
        //$search = "where trim(kouji_no) like '{$sc_no}'";
        $search = "where trim(aden.kouji_no) like '{$sc_no}' AND aden.espoir_deli >= '{$ken_date}'";
    } else {
        //$search = "where trim(kouji_no) LIKE 'SC%'";
        $search = "where trim(aden.kouji_no) LIKE 'SC%' AND aden.espoir_deli >= '{$ken_date}'";
    }
} elseif ($aden_select == 'aden_mikan') {
    $search = "where aden.delivery = 0 AND aden.plan_no <> '' AND aden.kouji_no <> '' AND (sche.plan - sche.cut_plan) > 0 AND p_kubun = 'P' AND aden.espoir_deli >= '{$ken_date}'";
} elseif ($aden_select == 'aden_mikanc') {
    $search = "where aden.delivery = 0 AND aden.parts_no LIKE 'C%' AND aden.kouji_no = '' AND sche.assy_site='01111' AND (sche.plan - sche.cut_plan) > 0 AND aden.espoir_deli >= '{$ken_date}'";
} elseif ($aden_select == 'aden_mikanl') {
    $search = "where aden.delivery = 0 AND aden.parts_no LIKE 'L%' AND aden.kouji_no = '' AND sche.assy_site='01111' AND (sche.plan - sche.cut_plan) > 0 AND aden.espoir_deli >= '{$ken_date}'";
} else {
    $search = '';
}

//////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
if ($aden_select == 'aden_mikan') {
    $query = sprintf('select count(*) from aden_master AS aden LEFT OUTER JOIN assembly_schedule AS sche using(plan_no) %s', $search);
    if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows の取得
        $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
    }
} elseif ($aden_select == 'aden_mikanc') {
    $query = sprintf('select count(*) from aden_master AS aden LEFT OUTER JOIN assembly_schedule AS sche using(plan_no) %s', $search);
    if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows の取得
        $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
    }
} elseif ($aden_select == 'aden_mikanl') {
    $query = sprintf('select count(*) from aden_master AS aden LEFT OUTER JOIN assembly_schedule AS sche using(plan_no) %s', $search);
    if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows の取得
        $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
    }
} else {
    $query = sprintf('select count(*) from aden_master AS aden %s', $search);
    if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows の取得
        $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
    }
}
$total = number_format($maxrows);
//////////// 表題の設定とソート順の設定
if ($aden_select == 'aden_no') {
    if ($aden_no == '') {
        $menu->set_caption("<font color='blue'>最新のＡ伝番号順</font>&nbsp;&nbsp;&nbsp;{$ken_date_view}～{$yesterday}現在の情報です。&nbsp;&nbsp;合計件数={$total}");
        $sort = 'aden.aden_no DESC';     // eda_no ASC これを付けると遅くなる
    } else {
        $menu->set_caption("「<font color='red'>{$_SESSION['aden_no']}</font>」で検索した結果&nbsp;&nbsp;&nbsp;{$ken_date_view}～{$yesterday}現在の情報です。&nbsp;&nbsp;合計件数={$total}");
        $sort = 'aden.aden_no DESC';
    }
} elseif ($aden_select =='aden_assy_no') {
    if ($aden_assy_no == '') {
        $menu->set_caption("<font color='blue'>製品・部品番号順</font>&nbsp;&nbsp;&nbsp;{$ken_date_view}～{$yesterday}現在の情報です。&nbsp;&nbsp;合計件数={$total}");
        $sort = 'aden.parts_no DESC, aden.delivery DESC';
    } else {
        $menu->set_caption("「<font color='red'>{$_SESSION['aden_assy_no']}</font>」で検索した結果&nbsp;&nbsp;&nbsp;{$ken_date_view}～{$yesterday}現在の情報です。&nbsp;&nbsp;合計件数={$total}");
        $sort = 'aden.parts_no DESC, aden.delivery DESC';
    }
} elseif ($aden_select == 'sc_no') {
    if ($sc_no == '') {
        $menu->set_caption("<font color='blue'>最新のＳＣ工番順</font>&nbsp;&nbsp;&nbsp;{$ken_date_view}～{$yesterday}現在の情報です。&nbsp;&nbsp;合計件数={$total}");
        $sort = 'aden.kouji_no DESC';
    } else {
        $menu->set_caption("「<font color='red'>{$_SESSION['sc_no']}</font>」で検索した結果&nbsp;&nbsp;&nbsp;{$ken_date_view}～{$yesterday}現在の情報です。&nbsp;&nbsp;合計件数={$total}");
        $sort = 'aden.kouji_no DESC';
    }
} elseif ($aden_select == 'aden_mikan') {
    $menu->set_caption("<font color='blue'>日程計画作成日順</font>&nbsp;&nbsp;&nbsp;{$ken_date_view}～{$yesterday}現在の情報です。&nbsp;&nbsp;合計件数={$total}");
    $sort = 'sche.crt_date ASC';
} elseif ($aden_select == 'aden_mikanc') {
    $menu->set_caption("<font color='blue'>日程計画作成日順</font>&nbsp;&nbsp;&nbsp;{$ken_date_view}～{$yesterday}現在の情報です。&nbsp;&nbsp;合計件数={$total}");
    $sort = 'sche.crt_date ASC';
} elseif ($aden_select == 'aden_mikanl') {
    $menu->set_caption("<font color='blue'>日程計画作成日順</font>&nbsp;&nbsp;&nbsp;{$ken_date_view}～{$yesterday}現在の情報です。&nbsp;&nbsp;合計件数={$total}");
    $sort = 'sche.crt_date ASC';
} else {
    $menu->set_caption("<font color='blue'>最新のＡ伝番号順</font>&nbsp;&nbsp;&nbsp;{$ken_date_view}～{$yesterday}現在の情報です。&nbsp;&nbsp;合計件数={$total}");
    $sort = 'aden.aden_no DESC';
}

//////////// ページオフセット設定
if ( isset($_REQUEST['forward']) ) {                    // 次頁が押された
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {             // 前頁が押された
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {            // 現在のページを維持する
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // 初回の場合は０で初期化
}
$offset = $_SESSION['offset'];

//////////// Ａ伝情報のリスト作成 Query & 初期化
$query = sprintf("
        SELECT
            aden.aden_no     as Ａ伝,                        -- 0
            aden.eda_no      as 枝,                          -- 1
            CASE
                WHEN trim(aden.parts_no) = '' THEN '---'
                ELSE aden.parts_no
            END         as 製品番号,                    -- 2
            CASE
                WHEN trim(aden.sale_name) = '' THEN '&nbsp;'
                ELSE trim(aden.sale_name)
            END         as 販売商品名,                  -- 3
            CASE
                WHEN trim(midsc) IS NULL THEN '---'
                ELSE substr(midsc, 1, 12)
            END         as 生産製品名,                  -- 4
            CASE
                WHEN trim(aden.plan_no) = '' THEN '---'
                ELSE aden.plan_no
            END         as 計画番号,                    -- 5
            CASE
                WHEN trim(aden.approval) = '' THEN '---'
                ELSE aden.approval
            END         as 承認図,                      -- 6
            CASE
                WHEN trim(aden.ropes_no) = '' THEN '---'
                ELSE aden.ropes_no
            END         as 要領書,                      -- 7
            CASE
                WHEN trim(aden.kouji_no) = '' THEN '---'
                ELSE aden.kouji_no
            END         as 工事番号,                    -- 8
            aden.order_q     as 受注数量,                    -- 9
            aden.order_price as 受注単価,                    --10
            Uround(aden.order_q * aden.order_price, 0) as 金額,   --11
            aden.espoir_deli as 希望納期,                    --12
            aden.delivery    as 回答納期,                    --13
            aden.publish_day    AS  発行日,                  --14
            
            sche.syuka      AS  集荷日,                 --15
            sche.chaku      AS  着手日,                 --16
            sche.kanryou    AS  完了日,                 --17
            (sche.plan - sche.cut_plan - sche.kansei)
                            AS  計画残,                 --18
            sche.line_no    AS  ライン                  --19
        FROM
            aden_master             AS aden
        LEFT OUTER JOIN
            miitem                              ON aden.parts_no=mipn
        LEFT OUTER JOIN
            assembly_schedule       AS sche     using(plan_no)
        %s 
        ORDER BY
            {$sort}
        OFFSET %d LIMIT %d
        
    ", $search, $offset, PAGE);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= 'Ａ伝情報のデータが取得できません！';
    header('Location: ' . $menu->out_retUrl());                   // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
}

// SQLのサーチ部も日本語を英字に変更。'もエラーになるので/に一時変更
$csv_search = str_replace('\'','/',$search);
$csv_sort = str_replace('\'','/',$sort);

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

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?= $uniq ?>'>
</script>
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
//    document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
//    document.form_name.element_name.select();
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
}
th {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:yellow;
    color:          blue;
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
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
.winbox_mark {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#e6e6e6;
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
body {
    background-image:url(<?= IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='get' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='pt11b'>
                        <?= $menu->out_caption() . "\n" ?>
                        <?php if ($aden_schedule) { ?>
                        <input class='pt10b' type='submit' name='aden_schedule' value='組立計画OFF' style='color:blue;'>
                        <?php } else { ?>
                        <input class='pt10b' type='submit' name='aden_schedule' value='組立計画ON' style='color:blue;'>
                        <?php } ?>
                    </td>
                    <td nowrap align='center' class='pt11b'>
                    <a href='aden_master_csv.php?csvsearch=<?php echo $csv_search ?>&csvsort=<?php echo $csv_sort ?>'>
                        CSV出力
                    </a>
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
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                    if ( ($i == 4) && ($display == 'normal') ) continue;
                    if ($i >= 15) break;
                ?>
                    <th nowrap><?= $field[$i] ?></th>
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
                        <td class='winbox' nowrap style='font-size:10pt; font-weight:bold;' align='right'><?= ($r + $offset + 1) ?></td>    <!-- 行ナンバーの表示 -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        switch ($i) {
                        case 3:
                        case 4:
                            if ( ($i == 4) && ($display == 'normal') ) continue;
                            echo "<td class='winbox' nowrap align='left' style='font-size:9pt;'>{$res[$r][$i]}</td>\n";
                            break;
                        case  9:
                        case 10:
                        case 11:
                            echo "<td class='winbox' nowrap align='right' style='font-size:9pt;'>" . number_format($res[$r][$i], 0) . "</td>\n";
                            break;
                        case 12:
                        case 13:
                            echo "<td class='winbox' nowrap align='center' style='font-size:9pt;'>" . format_date($res[$r][$i]) . "</td>\n";
                            break;
                        case 14:
                            echo "<td class='winbox' nowrap align='center' style='font-size:9pt;'>" . format_date($res[$r][$i]) . "</td>\n";
                            break;
                        case 15:
                        case 16:
                        case 17:
                        case 18:
                        case 19:
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='center' style='font-size:9pt;'>{$res[$r][$i]}</td>\n";
                        }
                    }
                    if ( ($res[$r][15]) && ($aden_schedule) ) {
                        echo "                    </tr>\n";
                        echo "                    <tr>\n";
                        if ($display == 'normal') echo "<td colspan='4' class='winbox' nowrap align='right' style='font-size:9pt;'>&nbsp;</td>\n"; else echo "<td colspan='5' class='winbox' nowrap align='right' style='font-size:9pt;'>&nbsp;</td>\n";
                        echo "<td class='winbox_mark' nowrap align='right' style='font-size:9pt;'>組立計画 Line</td>\n";
                        echo "<td class='winbox_mark' nowrap align='left' style='font-size:9pt;'>{$res[$r][19]}</td>\n";
                        echo "<td class='winbox_mark' nowrap align='right' style='font-size:9pt;'>計画残</td>\n";
                        echo "<td class='winbox_mark' nowrap align='left' style='font-size:9pt;'>", number_format($res[$r][18]), "</td>\n";
                        echo "<td class='winbox_mark' nowrap align='right' style='font-size:9pt;'>集荷日</td>\n";
                        echo "<td class='winbox_mark' nowrap align='right' style='font-size:9pt;'>", substr(format_date($res[$r][15]), 2), "</td>\n";
                        echo "<td class='winbox_mark' nowrap align='right' style='font-size:9pt;'>着手日</td>\n";
                        echo "<td class='winbox_mark' nowrap align='right' style='font-size:9pt;'>", substr(format_date($res[$r][16]), 2), "</td>\n";
                        echo "<td class='winbox_mark' nowrap align='right' style='font-size:9pt;'>完了日</td>\n";
                        echo "<td class='winbox_mark' nowrap align='right' style='font-size:9pt;'>", substr(format_date($res[$r][17]), 2), "</td>\n";
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
</html>
