<?php
//////////////////////////////////////////////////////////////////////////////
// 検査日数の照会 検査日数毎のサマリー照会(年月による期間指定)              //
// Copyright (C) 2016-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2016/01/29 Created inspection_date_summary.php                           //
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
require_once ('../../../ControllerHTTP_Class.php');    // TNK 全共通 MVC Controller Class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 10);                    // site_index=30(生産メニュー) site_id=10(買掛実績照会のグループ)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('検 査 日 数 の 照 会 （ 集 計 ）');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('総材料費明細',   INDUST . 'material/materialCost_view.php');

$menu->set_action('買掛実績照会',   INDUST . 'order/inspection_date/inspection_date_view.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// セッションのインスタンスを生成
$session = new Session();

//////////// 条件選択フォームからのPOSTデータ取得
if (isset($_REQUEST['parts_no'])) {
    $parts_no = $_REQUEST['parts_no'];
    $_SESSION['paya_parts_no'] = $parts_no;
} else {
    if (isset($_SESSION['paya_parts_no'])) {
        $parts_no = $_SESSION['paya_parts_no'];
    } else {
        $parts_no = '';
    }
}
if (isset($_REQUEST['div'])) {
    $div = $_REQUEST['div'];
    $_SESSION['payable_div'] = $div;
} else {
    if (isset($_SESSION['payable_div'])) {
        $div = $_SESSION['payable_div'];
    } else {
        $div = ' ';
    }
}
if (isset($_REQUEST['vendor'])) {
    $vendor = $_REQUEST['vendor'];
    $_SESSION['paya_vendor'] = $vendor;
} else {
    if (isset($_SESSION['paya_vendor'])) {
        $vendor = $_SESSION['paya_vendor'];
    } else {
        $vendor = '';
    }
}
if (isset($_REQUEST['kamoku'])) {
    $kamoku = $_REQUEST['kamoku'];
    $_SESSION['paya_kamoku'] = $kamoku;
} else {
    if (isset($_SESSION['paya_kamoku'])) {
        $kamoku = $_SESSION['paya_kamoku'];
    } else {
        $kamoku = '';
    }
}
if (isset($_REQUEST['ken_num'])) {
    $ken_num = $_REQUEST['ken_num'];
    $_SESSION['paya_ken_num'] = $ken_num;
} else {
    if (isset($_SESSION['paya_ken_num'])) {
        $ken_num = $_SESSION['paya_ken_num'];
    } else {
        $ken_num = '';
    }
}
if (isset($_REQUEST['str_date'])) {
    $str_date = $_REQUEST['str_date'];
    $_SESSION['paya_strdate'] = $str_date;
} elseif (isset($_SESSION['paya_strdate'])) {
    $str_date = $_SESSION['paya_strdate'];
} else {
    $year  = date('Y') - 5; // ５年前から
    $month = date('m');
    $str_date = $year . $month . '01';
}
if (isset($_REQUEST['end_date'])) {
    $end_date = $_REQUEST['end_date'];
    $_SESSION['paya_enddate'] = $end_date;
} elseif (isset($_SESSION['paya_enddate'])) {
    $end_date = $_SESSION['paya_enddate'];
} else {
    $end_date = '99999999';
}
if (isset($_REQUEST['paya_page'])) {
    $paya_page = $_REQUEST['paya_page'];
    $_SESSION['paya_page'] = $paya_page;
} else {
    if (isset($_SESSION['paya_page'])) {
        $paya_page = $_SESSION['paya_page'];
    } else {
        $paya_page = '';
    }
}
if ($session->get('str_date') != '') {
    $str_date = $session->get('str_date');
    $_SESSION['str_date'] = $str_date;
    $_SESSION['paya_strdate'] = $str_date;
}
if ($session->get('end_date') != '') {
    $end_date = $session->get('end_date');
    $_SESSION['end_date'] = $end_date;
    $_SESSION['paya_enddate'] = $end_date;
}
//////////// 一頁の行数
define('PAGE', $paya_page);

/////////// begin トランザクション開始
if ($con = funcConnect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'funcConnect() error';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
}

//////////// SQL 文の where 句を 共用する 01111=栃木日東工器 00222=製造課特注 99999=諸口
$search = sprintf("where act_date>=%d and act_date<=%d", $str_date, $end_date);

//////////// SQL 文の where 句を 共用する
if ($parts_no != '') {
    $search_kin = sprintf("%s and paya.parts_no='%s'", $search, $parts_no);
    $query = "select trim(substr(midsc,1,18)) from miitem where mipn='{$parts_no}' limit 1";
    if (getUniResult($query, $name) <= 0) {
        $name = 'マスター未登録';
    }
    $caption_title = "部品番号：{$parts_no}　<font color='blue'>部品名：{$name}</font>　年月：" . format_date($str_date) . '～' . format_date($end_date);
} elseif ($div != ' ') {
    if ($vendor != '') {
        if($div == 'D') {
            $search_kin = sprintf("%s and vendor='%s' and paya.div='C' and kouji_no NOT like 'SC%%'", $search, $vendor);
            $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
            if (getUniResult($query, $name) <= 0) {
                $name = 'マスター未登録';
            }
            $caption_title = "事業部：カプラ標準　年月：" . format_date($str_date) . '～' . format_date($end_date) . "発注先：" . $name;
        } elseif($div == 'S') {
            $search_kin = sprintf("%s and vendor='%s' and paya.div='C' and kouji_no like 'SC%%'", $search, $vendor);
            $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
            if (getUniResult($query, $name) <= 0) {
                $name = 'マスター未登録';
            }
            $caption_title = "事業部：カプラ特注　年月：" . format_date($str_date) . '～' . format_date($end_date) . "発注先：" . $name;
        } else {
            $search_kin = sprintf("%s and vendor='%s' and paya.div='%s'", $search, $vendor, $div);
            $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
            if (getUniResult($query, $name) <= 0) {
                $name = 'マスター未登録';
            }
            $caption_title = "事業部：{$div}　年月：" . format_date($str_date) . '～' . format_date($end_date) . "発注先：" . $name;
        }
    } else {
        if($div == 'D') {
            $search_kin = sprintf("%s and paya.div='C' and kouji_no NOT like 'SC%%'", $search);
            $caption_title = "事業部：カプラ標準　年月：" . format_date($str_date) . '～' . format_date($end_date);
        } elseif($div == 'S') {
            $search_kin = sprintf("%s and paya.div='C' and kouji_no like 'SC%%'", $search);
            $caption_title = "事業部：カプラ特注　年月：" . format_date($str_date) . '～' . format_date($end_date);
        } else {
            $search_kin = sprintf("%s and paya.div='%s'", $search, $div);
            $caption_title = "事業部：{$div}　年月：" . format_date($str_date) . '～' . format_date($end_date);
        }
    }
} else {
    if ($vendor != '') {
        $search_kin = sprintf("%s and vendor='%s'", $search, $vendor);
        $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
        if (getUniResult($query, $name) <= 0) {
            $name = 'マスター未登録';
        }
        $caption_title = "事業部：全部門　年月：" . format_date($str_date) . '～' . format_date($end_date) . "発注先：" . $name;
    } else {
        $search_kin = $search;
        $caption_title = "事業部：全部門　年月：" . format_date($str_date) . '～' . format_date($end_date);
    }
}

//////////// 内作を除く合計金額 (科目1～5)科目6以上を除く
$query = sprintf("select
                            count(*)
                    from
                            (act_payable as paya left outer join vendor_master using(vendor))
                    left outer join
                            order_plan
                    using(sei_no)
                    %s
                    ", $search_kin);
if (getResultTrs($con, $query, $paya_ctoku) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("買掛金の計上日:%s～%sで<br>データがありません。", $str_date, $end_date );
    $_SESSION['s_sysmsg'] .= '買掛 件数の取得が出来ません。';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    $maxrows = $paya_ctoku[0][0];
    // $sum_kin = $paya_ctoku[0][0];
    // $maxrows = $paya_ctoku[0][1];    // GROUP BY の時は集約関数は使えない
}

$query = sprintf("select
                        sum(Uround(order_price * siharai,0))
                    from
                            (act_payable as paya left outer join vendor_master using(vendor))
                    left outer join
                            order_plan
                    using(sei_no)
                    %s
                    ", $search_kin);
if (getUniResTrs($con, $query, $sum_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= '買掛合計金額の取得が出来ません。';      // .= メッセージを追加する
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
        SELECT
            (to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f')            as 検査日数,      -- 01
            COUNT((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))     as 件数,          -- 02
            ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f')) 
            * (COUNT((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))) as 日数           -- 03
        FROM
            act_payable AS paya
        LEFT OUTER JOIN
            vendor_master USING(vendor)
        left outer join
            order_plan using(sei_no)
        %s
        GROUP BY 検査日数
        ORDER BY 検査日数 DESC
    ", $search_kin);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResWithFieldTrs($con, $query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("買掛金の計上日:%s～%sで<br>データがありません。", $str_date, $end_date );
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    query_affected_trans($con, 'commit');         // transaction commit
    $num = count($field);       // フィールド数取得
}

//////////// 表題の設定
$menu->set_caption("{$caption_title}");

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
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
.pt12b {
    font:           12pt;
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
                $ken_total = 0;
                $day_total = 0;
                for ($r=0; $r<$rows; $r++) {
                ?>
                    <tr>
                        <td class='winbox' nowrap align='right'><div class='pt12b'><?= ($r + $offset + 1) ?></div></td>    <!-- 行ナンバーの表示 -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        switch ($i) {
                        case 0:
                            echo "<td class='winbox' nowrap align='right'><div class='pt10b'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 1:
                            echo "<td class='winbox' nowrap align='right'><div class='pt10b'><a href='", $menu->out_action('買掛実績照会'), "?parts_no={$parts_no}&div={$div}&str_date={$str_date}&end_date={$end_date}&div={$div}&vendor={$vendor}&ken_num={$res[$r][0]}&paya_page={$paya_page}#mark'>" . number_format($res[$r][$i]) . "</a></div></td>\n";
                            $ken_total += $res[$r][$i];
                            break;
                        case 2:
                            echo "<td class='winbox' nowrap align='right'><div class='pt10b'>", number_format($res[$r][$i]), "</div></td>\n";
                            $day_total += $res[$r][$i];
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='right'><div class='pt10b'>{$res[$r][$i]}</div></td>\n";
                        }
                    }
                    ?>
                    </tr>
                <?php
                
                }
                ?>
                
                 <tr>
                    <td class='winbox' nowrap align='right'><div class='pt12b'>　</div></td>    <!-- 行ナンバーの表示 -->
                    <td class='winbox' nowrap align='right'><div class='pt12b'>合計</div></td>    <!-- 行ナンバーの表示 -->
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10b'><a href='", $menu->out_action('買掛実績照会'), "?parts_no={$parts_no}&div={$div}&str_date={$str_date}&end_date={$end_date}&div={$div}&vendor={$vendor}&ken_num=&paya_page={$paya_page}#mark'>" . number_format($ken_total) . "</a></div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10b'>", number_format($day_total), "</div></td>\n";
                    ?>
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
ob_end_flush();     // 出力バッファーをgzip圧縮 END
?>
