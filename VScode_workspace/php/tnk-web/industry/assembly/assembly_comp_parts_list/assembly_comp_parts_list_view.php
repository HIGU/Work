<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 実績 照会  new version  salse_actual_view.php                       //
// Copyright (C) 2020-2020 Ryota.Waki tnksys@nitto-kohki.co.jp              //
// Changed history                                                          //
// 2020/12/24 Created   sales_view.php -> assembly_comp_parts_list_view.php //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors', '1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1'); // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');            // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);         // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                       // 出力バッファをgzip圧縮
session_start();                                // ini_set()の次に指定すること Script 最上行

require_once ('../../../function.php');            // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');            // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');          // TNK 全共通 menu class
require_once ('../../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
require_once ('assembly_comp_parts_list_func.php');
////////// セッションのインスタンスを登録
$session = new Session();

access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////// サイト設定
$menu->set_site( 30, 11);                    // site_index=01(売上メニュー) site_id=11(売上実績明細)
////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('組立 完成 部品 一覧');

////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

////////// 初回時のセッションデータ保存
if( ! (isset($_REQUEST['backward']) || isset($_REQUEST['forward'])) ) {
    $_SESSION['s_div']          = $_REQUEST['div'];
    $_SESSION['s_d_start']      = $_REQUEST['d_start'];
    $_SESSION['s_d_end']        = $_REQUEST['d_end'];
    $_SESSION['s_sales_page']   = $_REQUEST['sales_page'];
    $_SESSION['s_maxrows']      = 0;    // 
    unset($_SESSION['s_limitrows']);    // 限界値解放（前回が残っている為）
}

$div        = $_SESSION['s_div'];
$d_start    = $_SESSION['s_d_start'];
$d_end      = $_SESSION['s_d_end'];

////////// 表題の設定 1
// 製品グループ(事業部)名の設定
if ($div == "A") $div_name = "すべて";
if ($div == "C") $div_name = "カプラ全体";
if ($div == "D") $div_name = "カプラ標準";
if ($div == "S") $div_name = "カプラ特注";
if ($div == "L") $div_name = "リニア";

$f_d_start  = format_date($d_start);        // 日付を / でフォーマット
$f_d_end    = format_date($d_end);          // 日付を / でフォーマット
$menu->set_caption("<u>部門=<font color='red'>{$div_name}</font>：{$f_d_start}～{$f_d_end}<u>");

////////// ページ制御の処理 1
// 一頁の行数
define('PAGE', $_SESSION['s_sales_page']);
// 合計レコード数取得 (対象テーブルの最大数をページ制御に使用)
if( isset($_SESSION['s_limitrows']) ) {
    $maxrows = $_SESSION['s_limitrows'];
} else {
    $maxrows = $_SESSION['s_maxrows'];
}
// ページオフセット設定
if ( isset($_REQUEST['forward']) ) {                    // 次頁が押された
    $_SESSION['sales_offset'] += PAGE;
    if ($_SESSION['sales_offset'] >= $maxrows) {
        $_SESSION['sales_offset'] -= PAGE;
        $_SESSION['s_limitrows'] = $maxrows;
        $_SESSION['s_sysmsg'] .= "次頁はありません。";
    }
} elseif ( isset($_REQUEST['backward']) ) {             // 前頁が押された
    $_SESSION['sales_offset'] -= PAGE;
    if ($_SESSION['sales_offset'] < 0) {
        $_SESSION['sales_offset'] = 0;
        $_SESSION['s_sysmsg'] .= "前頁はありません。";
    }
} else {
    $_SESSION['s_maxrows'] += PAGE + 1; // 初回の場合は、PAGE+1で初期化
    $_SESSION['sales_offset'] = 0;      // 初回の場合は、０で初期化
}
$offset = $_SESSION['sales_offset'];

////////// 完了明細情報取得処理
// SQL WHERE 句 作成
$search = sprintf("WHERE a.comp_date>=%d AND a.comp_date<=%d", $d_start, $d_end);
if( $div == "A" ) {
    $search .= " AND SUBSTRING(a.plan_no, 1, 1) != '@'";
} else if( $div == "C" ) {
    $search .= " AND SUBSTRING(a.plan_no, 1, 1) = 'C'";
} else if( $div == "S" ) {
    $search .= " AND SUBSTRING(a.plan_no, 1, 1) = 'C' AND SUBSTRING(sche.note15, 1, 2) = 'SC'";
} else if( $div == "D" ) {
    $search .= " AND SUBSTRING(a.plan_no, 1, 1) = 'C' AND SUBSTRING(sche.note15, 1, 2) != 'SC'";
} else if( $div == "L" ) {
    $search .= " AND SUBSTRING(a.plan_no, 1, 1) = 'L'";
}

// 完了明細情報取得
$rows = getKanryouDateView($res, $field, $search, $offset, PAGE);
if( $rows <= 0 ) {
    $_SESSION['s_sysmsg'] .= sprintf("%s～%s 完了データがありません。\t\t\tまたは、完了データの取得に失敗しています!!", $f_d_start, $f_d_end );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}

////////// 表題の設定 2
if( getKanryouAggregate($cap_res, $search) <= 0 ) {
    $menu->set_caption2("<font color='red'>計画数 ： 完成数 ： 数量合計 の取得に失敗しました。</font>");
} else {
    $plan_ken = number_format($cap_res[0][0], 0);
    $comp_ken = number_format($cap_res[0][1], 0);
    $suryou   = trim(number_format($cap_res[0][2], 4), 0);
    $suryou   = trim($suryou, '.');
    $menu->set_caption2("計画数 = {$plan_ken} 件 ： 完成数 = {$comp_ken} 台 ： 数量合計 = {$suryou} 個");
}

////////// ページ制御の処理 2
// 限界値未設定のとき
if( ! isset($_SESSION['s_limitrows']) ) {
    // 次項 分のデータがあるかチェック
    $next_rows = getKanryouDateView($next_res, $next_field, $search, $offset+$rows, 1);
    if( $next_rows <= 0 ) {
        $_SESSION['s_limitrows'] = $maxrows + $rows;
    }

    if( isset($_REQUEST['forward']) ) {         // 次頁が押された
        $_SESSION['s_maxrows'] = $maxrows + $rows;
    } else if( isset($_REQUEST['backward']) ) { // 前頁が押された
        if( $offset == 0 ) {
            $_SESSION['s_maxrows'] = PAGE + 1;  // $offset 初期値の為、初期値セット
        } else {
            $_SESSION['s_maxrows'] = $maxrows - $rows;
        }
    }
}

////////// CSV出力用の準備作業
// ファイル名に日本語をつけると受け渡しでエラーになるので一時英字に変更
if ($div == "A") $act_name = "ALL";
if ($div == "C") $act_name = "C-all";
if ($div == "S") $act_name = "C-toku";
if ($div == "D") $act_name = "C-hyou";
if ($div == "L") $act_name = "L-all";

// SQLのサーチ部 'もエラーになるので/に一時変更
$csv_search = str_replace('\'','/',$search);

// CSVファイル名を作成（開始年月-終了年月-事業部）
$outputFile = $d_start . '-' . $d_end . '-' . $act_name;

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
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
                    <td nowrap align='center' class='caption_font'>
                        <BR>
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
        <font class='pt11b'><BR><?php echo $menu->out_caption2() ?><BR><BR></font>
        <a href='assembly_comp_parts_list_csv.php?csvname=<?php echo $outputFile ?>&actname=<?php echo $act_name ?>&csvsearch=<?php echo $csv_search ?>'>CSV出力</a>
        <BR><BR>
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- 行ナンバーの表示 -->
                <?php
                $num = count($field);
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                ?>
                    <th class='winbox' nowrap><?php echo $field[$i] ?></th>
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
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>" . ($r + $offset + 1) . "</div></td>    <!-- 行ナンバーの表示 -->\n";
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        switch ($i) {
                        case 0:     // 製品番号
//                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
/**/
                            if( $r == 0 || $res[$r][$i] != $res[$r-1][$i] ) {
                                echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='left'><div class='pt9'>　</div></td>\n";
                            }
/**/
                            break;
                        case 1:     // 製品名
//                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
/**/
                            if( $r == 0 || $res[$r][0] != $res[$r-1][0] ) {
                                echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='left'><div class='pt9'>　</div></td>\n";
                            }
/**/
                            break;
                        case 2:     // 計画番号
//                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
/**/
                            if( $r == 0 || $res[$r][$i] != $res[$r-1][$i] ) {
                                echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='left'><div class='pt9'>　</div></td>\n";
                            }
/**/
                            break;
                        case 3:     // 組立完了日
//                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
/**/
                            if( $r == 0 || $res[$r][$i] != $res[$r-1][$i] || $res[$r][$i-1] != $res[$r-1][$i-1]) {
                                echo "<td class='winbox' nowrap align='left'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='left'><div class='pt9'>　</div></td>\n";
                            }
/**/
                            break;
                        case 4:     // 部品番号
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 5:     // 部品名
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 6:     // 使用数
                            echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>" . number_format($res[$r][$i], 4) . "</div></td>\n";
                            break;
                        case 7:     // 完成数
                            echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                            break;
                        case 8:     // 数量（使用数×完成数）
                            $res[$r][$i] = number_format($res[$r][$i], 4);
                            $res[$r][$i] = rtrim($res[$r][$i], 0);
                            $res[$r][$i] = rtrim($res[$r][$i], '.');
                            echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        default:    // その他
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        }
                    }
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>

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
        <br>

    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
