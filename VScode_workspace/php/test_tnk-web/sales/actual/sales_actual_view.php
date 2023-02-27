<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 実績 照会  new version  salse_actual_view.php                       //
// Copyright (C) 2020-2020 Ryota.Waki tnksys@nitto-kohki.co.jp              //
// Changed history                                                          //
// 2020/12/17 Created   sales_view.php -> salse_actual_view.php             //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors', '1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1'); // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');            // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);         // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                       // 出力バッファをgzip圧縮
session_start();                                // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');            // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');            // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');          // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
require_once ('sales_actual_func.php');
////////// セッションのインスタンスを登録
$session = new Session();

access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////// サイト設定
$menu->set_site( 1, 11);                    // site_index=01(売上メニュー) site_id=11(売上実績明細)
////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('売 上 実 績 照 会');

////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

////////// 表示用（件数・台数）に使用する値
define('OEM', 1);
define('HOYOU', 2);
define('OTHER', 3);
define('TOTAL', 4);
define('VIEW_RECORD_MAX', 5);
define('VIEW_FIELD_MAX', 10);

//////////// 初回時のセッションデータ保存
if (! isset($_REQUEST['lineNo']) ) {
    $_SESSION['s_div']          = $_REQUEST['div'];
    $_SESSION['s_target_ym']    = $_REQUEST['target_ym'];
    $_SESSION['s_lineNo']       = 0;    // 初期値
} else {
    $_SESSION['s_lineNo'] = $_REQUEST['lineNo'];
}

$div        = $_SESSION['s_div'];
$target_ym  = $_SESSION['s_target_ym'];
$lineNo     = $_SESSION['s_lineNo'];
$d_start    = substr($target_ym,0,4) . substr($target_ym,5,2) . "01";
$d_end      = substr($target_ym,0,4) . substr($target_ym,5,2) . "99";

////////// 初回時各SQL処理を行い、必要情報をセッションへ保存
if( ! $lineNo ) {

    ////////// 表示用（件数・台数）初期化
    $view_tbl = array(); // [0]空[1～5]分類・[0～9]各台数と件数
    for($r=0; $r<VIEW_RECORD_MAX; $r++) { 
        for($f=0; $f<VIEW_FIELD_MAX; $f++) { 
            $view_tbl[$r][$f] = 0;
        }
    }

    ////////// 月初予定データ取得
    $target = substr($target_ym,0,4) . substr($target_ym,5,2);
    $search_f = "WHERE m.kanryou LIKE '{$target}%'";
    if( $div == "S" ) {
        $search_f .=  " AND plan_no LIKE 'C%' AND SUBSTRING(a.note15, 1, 2) = 'SC'";
    } else if( $div == "D" ) {
        $search_f .=  " AND plan_no LIKE 'C%' AND SUBSTRING(a.note15, 1, 2) != 'SC'";
    } else {
        $search_f .=  " AND plan_no LIKE 'L%'";
    }
    $rows_first = getFirstPlan($res_first, $field_first, $search_f);
    if( $rows_first <= 0 ) {
        $_SESSION['s_sysmsg'] .= sprintf("月初予定の取得に失敗しました。%s～%s", format_date($d_start), format_date($d_end) );
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    }

    // 月初予定の件数・台数 集計
    for( $r=0; $r<$rows_first; $r++ ) {
        if( trim($res_first[$r][7]) == "" ) {
            $res_first[$r][7] = getLineNo($res_first[$r][1], $res_first[$r][2]);
        }
        if( IsOem($res_first[$r][7]) ) { // OEM
            $rec = OEM;
        } else if( IsHoyou($res_first[$r][7]) ) {  // 補用
            $rec = HOYOU;
        } else {    // その他
            $rec = OTHER;
        }
        $view_tbl[$rec][0]++;
        $view_tbl[$rec][1] += $res_first[$r][4];
    }

    ////////// 売上予定データ取得
    $search_p = "WHERE a.kanryou>=$d_start AND a.kanryou<=$d_end AND (a.plan -a.cut_plan) > 0 AND assy_site='01111' AND a.nyuuko!=30 AND p_kubun='F' AND (a.plan -a.cut_plan - kansei) > 0";
    if ($div == 'S') {          // Ｃ特注なら
        $search_p .= " and a.dept='C' and a.note15 like 'SC%%'";
        $search_p .= " and (a.parts_no not like 'NKB%%') and (a.parts_no not like 'SS%%')";
        $search_p .= " and CASE WHEN a.kanryou<20130501 THEN groupm.support_group_code IS NULL ELSE a.dept='C' END";
    } elseif ($div == 'D') {    // Ｃ標準なら
        $search_p .= " and a.dept='C' and (a.note15 NOT like 'SC%%' OR a.note15 IS NULL)";    // 部品売りを標準へする
        $search_p .= " and (a.parts_no not like 'NKB%%') and (a.parts_no not like 'SS%%')";
        $search_p .= " and (CASE WHEN a.kanryou<20130501 THEN groupm.support_group_code IS NULL ELSE a.dept='C' END)";
    } elseif ($div == "L") {
        $search_p .= " and a.dept='$div'";
        $search_p .= " and (a.parts_no not like 'NKB%%') and (a.parts_no not like 'SS%%')";
    }
    $rows_plan = getSalsePlan($res_plan, $search_p);

    ////////// 売上明細データ取得
    $search_m = "where 計上日>=$d_start and 計上日<=$d_end";
    if ($div == 'S') {          // Ｃ特注なら
        $search_m .= " and 事業部='C' and note15 like 'SC%%'";
        $search_m .= " and CASE WHEN 計上日>=20111101 and 計上日<20130501 THEN groupm.support_group_code IS NULL ELSE 事業部='C' END";
    } elseif ($div == 'D') {    // Ｃ標準なら
        $search_m .= " and 事業部='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // 部品売りを標準へする
        $search_m .= " and (CASE WHEN 計上日>=20111101 and 計上日<20130501 THEN groupm.support_group_code IS NULL ELSE 事業部='C' END)";
    } elseif ($div == "L") {
        $search_m .= " and 事業部='$div'";
    }
    $search_m .= " and (assyno not like 'NKB%%') and (assyno not like 'SS%%') and datatype='1'";
    $rows_details = getSalesDetails($res_details, $div, $search_m);

    ////////// 未検収データ取得
    $rows_miken = getMikenSlectData($res_miken, $div, (substr($target_ym,0,4) . substr($target_ym,5,2)));

    ////////// 月初予定と明細を比較
    DiffFirstDetails($res_first, $rows_first, $res_details, $rows_details);

    ////////// 月初予定と未検収を比較
    DiffFirstMiken($res_first, $rows_first, $res_miken, $rows_miken);

    ////////// 月初予定と現在の予定を比較
    DiffFirstPlan($res_first, $rows_first, $res_plan, $rows_plan);

    ////////// 表示用（件数・台数）集計
    $total_ken = $total_kaz = $total_kin = 0;
    for( $r=0; $r<$rows_first; $r++ ) {
        if( $res_first[$r][9] ) {
            $total_ken++;                      // 合計件数
            $total_kaz += $res_first[$r][9];    // 合計完成数
            $total_kin += $res_first[$r][10];   // 合計完成金額
        }

        if( IsOem($res_first[$r][7]) ) { // OEM
            $rec = OEM;
        } else if( IsHoyou($res_first[$r][7]) ) {  // 補用
            $rec = HOYOU;
        } else {    // その他
            $rec = OTHER;
        }

        if( $res_first[$r][8] == "未検収" || $res_first[$r][8] == "完了" ) {
            $view_tbl[$rec][2]++;
            $view_tbl[$rec][3] += $res_first[$r][9];
            if( $res_first[$r][4] > 0 && $res_first[$r][4] < $res_first[$r][9] ) {
//$_SESSION['s_sysmsg'] .= "変更前：{$view_tbl[$rec][3]} / {$view_tbl[$rec][7]}";
//                $view_tbl[$rec][7] += ($res_first[$r][9] - $res_first[$r][4]);  // 予定数より完成数が多い時追加に足す。
//                $view_tbl[$rec][3] -= ($res_first[$r][9] - $res_first[$r][4]);
//$_SESSION['s_sysmsg'] .= "変更後：{$view_tbl[$rec][3]} / {$view_tbl[$rec][7]}";
            }
        } else if( $res_first[$r][8] == "予定あり") {
            $view_tbl[$rec][4]++;
            $view_tbl[$rec][5] += $res_first[$r][4];
            if( $res_first[$r][9] ) {   // 分納している分は、加算
                $view_tbl[$rec][2]++;
                $view_tbl[$rec][3] += $res_first[$r][9];
            }
        } else if( $res_first[$r][8] == "追加" ) {
            $view_tbl[$rec][6]++;
            $view_tbl[$rec][7] += $res_first[$r][9];
        } else {
            $view_tbl[$rec][8]++;
            $view_tbl[$rec][9] += $res_first[$r][4];
        }
    }

    // 表示用（件数・台数）合計セット
    for($r=0; $r<VIEW_RECORD_MAX-1; $r++) { 
        for($f=0; $f<VIEW_FIELD_MAX; $f++) { 
            $view_tbl[TOTAL][$f] += $view_tbl[$r][$f];
        }
    }

    // 前期売上取得
    $zenki = getPreviousSeasonSales($target_ym, $div);
    if( $zenki > 0 ) {
        // 前期比 ＝（当月売上÷前年度同月売上）× 100
        $zenkihi_ken = number_format((($total_ken / $zenki[0]['t_ken']) * 100), 2) . " ％";
        $zenkihi_kaz = number_format((($total_kaz / $zenki[0]['t_kazu']) * 100), 2) . " ％";
        $zenkihi_kin = number_format((($total_kin / $zenki[0]['t_kingaku']) * 100), 2) . " ％";
    } else {
        $zenkihi = "前年度同月売上の取得に失敗!!";
    }
//$_SESSION['s_sysmsg'] = "TEST 件数：" . $zenki[0]['t_ken'] . ":" . $zenki[0]['t_kazu'];

    // 各情報をセッションへ保存
    $_SESSION['s_view_tbl[]']       = $view_tbl;        // 表示用（件数・台数）
    $_SESSION['s_res_first[]']      = $res_first;       // 明細リスト
    $_SESSION['s_rows_first']       = $rows_first;      // 明細リストの件数
    $_SESSION['s_field_first[]']    = $field_first;     // 明細リストの項目
    $_SESSION['s_total_ken']        = $total_ken;       // 合計件数
    $_SESSION['s_total_kaz']        = $total_kaz;       // 合計完成数
    $_SESSION['s_total_kin']        = $total_kin;       // 合計完成金額
    $_SESSION['s_zenkihi_ken']      = $zenkihi_ken;     // 前期比（件数）
    $_SESSION['s_zenkihi_kaz']      = $zenkihi_kaz;     // 前期比（数量）
    $_SESSION['s_zenkihi_kin']      = $zenkihi_kin;     // 前期比（金額）
} else {    // 切替
    // 各情報をセッションより取得
    $view_tbl       = $_SESSION['s_view_tbl[]'];        // 表示用（件数・台数）
    $res_first      = $_SESSION['s_res_first[]'];       // 明細リスト
    $rows_first     = $_SESSION['s_rows_first'];        // 明細リストの件数
    $field_first    = $_SESSION['s_field_first[]'];     // 明細リストの項目
    $total_ken      = $_SESSION['s_total_ken'];         // 合計件数
    $total_kaz      = $_SESSION['s_total_kaz'];         // 合計完成数
    $total_kin      = $_SESSION['s_total_kin'];         // 合計完成金額
    $zenkihi_ken    = $_SESSION['s_zenkihi_ken'];       // 前期比（件数）
    $zenkihi_kaz    = $_SESSION['s_zenkihi_kaz'];       // 前期比（数量）
    $zenkihi_kin    = $_SESSION['s_zenkihi_kin'];       // 前期比（金額）
}

////////// 明細リストの準備
$res = array();
$rec = 0;
$num = count($field_first) - 1;  // フィールド数取得 (最後の備考は除く)

for( $r=0; $r<$rows_first; $r++ ) {
    if( $lineNo ) {
        if( $lineNo == OEM ) {
            if( ! IsOem($res_first[$r][7]) ) continue;
        } else if( $lineNo == HOYOU ) {
            if( ! IsHoyou($res_first[$r][7]) ) continue;
        } else if( $lineNo == OTHER ) {
            if( IsOem($res_first[$r][7]) || IsHoyou($res_first[$r][7]) ) continue;
        }
    }

    for( $f=0; $f<$num; $f++ ) {
        $res[$rec][$f] = $res_first[$r][$f];
    }
    $rec++;
}
$rows= $rec;    // 明細リスト表示件数セット

////////// 表題の設定
///// 製品グループ(事業部)名の設定
if ($div == "D") $div_name = "カプラ標準";
if ($div == "S") $div_name = "カプラ特注";
if ($div == "L") $div_name = "リニア";

$f_d_start  = format_date($d_start);        // 日付を / でフォーマット
$f_d_end    = format_date($d_end);          // 日付を / でフォーマット
$menu->set_caption("<u>部門=<font color='red'>{$div_name}</font>：{$f_d_start}～{$f_d_end}<u>");

$total_ken = number_format($total_ken);     // ３桁ごとのカンマを付加
$total_kin = number_format($total_kin);     // ３桁ごとのカンマを付加
$total_kaz = number_format($total_kaz);     // ３桁ごとのカンマを付加
$menu->set_caption2("<u>合計件数={$total_ken}：合計金額={$total_kin}：合計数量={$total_kaz}<u>");

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<?php echo $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // document.body.focus();                          // F2/F12キーで戻るための対応
    // document.form_name.element_name.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-family:    monospace;
}
.pt10 {
    font-size:l     10pt;
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
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
.winboxy {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:   yellow;
    color:              blue;
    font-size:          12pt;
    font-weight:        bold;
    font-family:        monospace;
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                <tr>
                    <td nowrap align='center' class='caption_font'>
                        <?php echo $menu->out_caption(), "\n" ?>
                        <BR>
                        <?php echo $menu->out_caption2(), "\n" ?>
                    </td>
                </tr>
        </table>
        <br>

            <!----------------- ここは 本文を表示する ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
                <caption class='caption_font' align='right'><?php echo "【前期比】件数：{$zenkihi_ken} / 数量：{$zenkihi_kaz}" ?></caption>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr class='winbox' style='background-color:yellow; color:blue;' align='center'>
                    <div class='caption_font'>
                        <td rowspan="2" style='color:red;'><?php echo $div_name ?></td>
                        <td colspan="2">月初予定</td>

                        <td colspan="2">完了</td>

                        <td colspan="2">残り</td>

                        <td colspan="2">追加完了</td>

                        <td colspan="2">予定なし</td>

                    </div>
                </tr>
                <tr class='winbox' style='background-color:yellow; color:blue;' align='center'>
                    <div class='caption_font'>
                        <td>件数</td>
                        <td>台数</td>
                        <td>件数</td>
                        <td>台数</td>
                        <td>件数</td>
                        <td>台数</td>
                        <td>件数</td>
                        <td>台数</td>
                        <td>件数</td>
                        <td>台数</td>
                    </div>
                </tr>
                <?php
                if( $div == "L" ) {
                ?>
                <tr align='right'>
                    <form name='oem_form' action='<?php echo $menu->out_self() . "?lineNo=1" ?>' method='post'>
                    <td style='background-color:yellow;'><a href="javascript:oem_form.submit()">ＯＥＭ</a></td>
                    </form>
                    <?php
                    for( $r=0; $r<VIEW_FIELD_MAX; $r=$r+2 ) {
                    ?>
                    <td><?php echo number_format($view_tbl[OEM][$r],0) ?> 件</td>
                    <td><?php echo number_format($view_tbl[OEM][$r+1],0) ?> 台</td>
                    <?php
                    }
                    ?>
                </tr>
                <tr align='right'>
                    <form name='hoyou_form' action='<?php echo $menu->out_self() . "?lineNo=2" ?>' method='post'>
                    <td style='background-color:yellow;'><a href="javascript:hoyou_form.submit()">ホヨウ</a></td>
                    </form>
                    <?php
                    for( $r=0; $r<VIEW_FIELD_MAX; $r=$r+2 ) {
                    ?>
                    <td><?php echo number_format($view_tbl[HOYOU][$r],0) ?> 件</td>
                    <td><?php echo number_format($view_tbl[HOYOU][$r+1],0) ?> 台</td>
                    <?php
                    }
                    ?>
                <?php
                if( $view_tbl[OTHER][0] != 0 || $view_tbl[OTHER][2] != 0 || $view_tbl[OTHER][4] != 0 || $view_tbl[OTHER][6] != 0 || $view_tbl[OTHER][8] != 0) {
                ?>
                </tr>
                <tr align='right'>
                    <form name='other_form' action='<?php echo $menu->out_self() . "?lineNo=3" ?>' method='post'>
                    <td style='background-color:yellow;'><a href="javascript:other_form.submit()">不明</a></td>
                    </form>
                    <?php
                    for( $r=0; $r<VIEW_FIELD_MAX; $r=$r+2 ) {
                    ?>
                    <td><?php echo number_format($view_tbl[OTHER][$r],0) ?> 件</td>
                    <td><?php echo number_format($view_tbl[OTHER][$r+1],0) ?> 台</td>
                    <?php
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                }
                ?>
                <tr align='right'>
                    <form name='all_form' action='<?php echo $menu->out_self() . "?lineNo=-1" ?>' method='post'>
                    <td style='background-color:yellow;'><a href="javascript:all_form.submit()">すべて</a></td>
                    </form>
                    <?php
                    for( $r=0; $r<VIEW_FIELD_MAX; $r=$r+2 ) {
                    ?>
                    <td><?php echo number_format($view_tbl[TOTAL][$r],0) ?> 件</td>
                    <td><?php echo number_format($view_tbl[TOTAL][$r+1],0) ?> 台</td>
                    <?php
                    }
                    ?>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        <BR>
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <caption class='caption_font' align='left' style='color:Red;'>※完了予定日が赤字のものは、追加分の【完成日】</caption>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                ?>
                    <th class='winbox' nowrap><?php echo $field_first[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>" . ($r + 1) . "</div></td>    <!-- 行ナンバーの表示 -->\n";
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        // <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                            switch ($i) {
                            case 0:     // 計上日
                                if( $res[$r][8] == "追加" ) {
                                    echo "<td class='winbox' nowrap align='center'><div class='pt9' style='color:Red;'>" . format_date($res[$r][$i]) . "</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                                }
                                break;
                            case 1:
                                if( $res[$r][8] == "追加" ) {
                                    echo "<td class='winbox' nowrap align='center' style='background-color:LightPink;'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                                } else if( ($res[$r][8] == "完了" || $res[$r][8] == "未検収" ) && $res[$r][4] != $res[$r][9] ) {
                                    echo "<td class='winbox' nowrap align='center' style='background-color:SkyBlue;'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
                                }
                                break;
                            case 2:     // 製品番号
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
                                break;
                            case 3:     // 製品名
                                echo "<td class='winbox' nowrap width='270' align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                                break;
                            case 4:     // 数量
                                echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 5:     // 仕切単価
                                if ($res[$r][$i] == 0) {
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                }
                                break;
                            case 6:     // 金額
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 7:     // ラインNo.
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                                break;
                            case 8:    // 区分
                                if( $res[$r][8] == "" ) {
                                    $res[$r][8] = "---";
                                }
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                                break;
                            case 9:    // 備考（数量に変更があった場合）
                                if( $res[$r][9] == "" ) {
                                    $res[$r][9] = "　";
                                    $res[$r][10] = "　";
                                }
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 10:     // 金額
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            default:    // その他
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
                            }
                        // <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                    }
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>

    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
