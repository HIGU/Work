<?php
//////////////////////////////////////////////////////////////////////////////
// 買掛ヒストリの照会 カプラ標準品 外注毎の月計表 内作及び諸口を含む        //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/12/07 Created   payable_cstd_vendor_summary2.php                    //
//            原材料(1)と部品仕掛Ｃ(2-5) 科目(6)- の合計金額 諸口を除外     //
//            リニアの原材料1 を除外 01111 00222 99999を含む                //
//            $_SESSION['act_ym'] → $_SESSION['ind_ym'] へ変更             //
// 2005/02/10 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/05/20 db_connect() → funcConnect() へ変更 pgsql.phpで統一のため    //
// 2007/09/05 協力工場毎に件数を表示 count(vendor), phpの標準タグへ変更     //
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
$menu->set_site(30, 10);                    // site_index=30(生産メニュー) site_id=10(買掛実績照会のグループ)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('買 掛 実 績 の 照 会');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('総材料費明細',   INDUST . 'material/materialCost_view.php');

//////////// 一頁の行数
define('PAGE', '200');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

/***************
//////////// 対象年月日を取得
$act_ymd = $_SESSION['ind_ymd'];
if ($act_ymd == '') {
    $act_ymd = date_offset(2);
}
***************/

// $_SESSION['act_ym'] = 200312;   // テスト用
//////////// 対象年月を取得 (年月のみに注意)
if ( isset($_SESSION['ind_ym']) ) {
    $act_ym = $_SESSION['ind_ym'];
    $s_ymd  = $act_ym . '01';   // 開始日
    $e_ymd  = $act_ym . '99';   // 終了日
} else {
    $_SESSION['s_sysmsg'] = '対象年月が指定されていません!';
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
$search = sprintf("where act_date>=%d and act_date<=%d", $s_ymd, $e_ymd);

// カプラ 特注
//////////// SQL 文の where 句を 共用する
$search_kin = sprintf("%s and kamoku<=5 and paya.div='C' and kouji_no NOT like 'SC%%'", $search);

//////////// 内作を除く合計金額 (科目1～5)科目6以上を除く
$query = sprintf("SELECT
                            count(*)
                    FROM
                            (act_payable AS paya LEFT OUTER JOIN vendor_master USING(vendor))
                    LEFT OUTER JOIN
                            order_plan
                    USING(sei_no)
                    %s
                    GROUP BY vendor, name
                    ", $search_kin);
if (($maxrows = getResultTrs($con, $query, $paya_ctoku)) <= 0) {
    $_SESSION['s_sysmsg'] .= 'カプラ標準品の買掛 金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    // $sum_kin = $paya_ctoku[0][0];
    // $maxrows = $paya_ctoku[0][1];    // GROUP BY の時は集約関数は使えない
}

$query = sprintf("SELECT
                        sum(Uround(order_price * siharai,0))
                    FROM
                            (act_payable AS paya LEFT OUTER JOIN vendor_master USING(vendor))
                    LEFT OUTER JOIN
                            order_plan
                    USING(sei_no)
                    %s
                    ", $search_kin);
if (getUniResTrs($con, $query, $sum_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= 'カプラ標準の買掛合計金額の取得に失敗';      // .= メッセージを追加する
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
            vendor              AS 発注先                       -- 00
            ,
            name                AS 発注先名                     -- 01
            ,
            SUM(Uround(order_price * siharai,0)) AS 発注金額    -- 02
            ,
            count(vendor)       AS 件数                         -- 03
        FROM
            (act_payable AS paya LEFT OUTER JOIN vendor_master USING(vendor))
        LEFT OUTER JOIN
                order_plan
        USING(sei_no)
        %s 
        GROUP BY vendor, name
        ORDER BY vendor ASC
        offset %d limit %d
    ", $search_kin, $offset, PAGE);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResWithFieldTrs($con, $query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("買掛金の計上日:%s で<br>データがありません。", $act_ym );
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    query_affected_trans($con, 'commit');         // transaction commit
    $num = count($field);       // フィールド数取得
}

//////////// 表題の設定
$caption = $act_ym . '　合計金額：' . number_format($sum_kin) . '　合計件数：' . number_format($maxrows);
$menu->set_caption("カプラ標準品(外注別)　内作及び諸口を含む　　$caption");

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

<!--    ファイル指定の場合
<script type='text/javascript' language='JavaScript' src='template.js?<?php echo $uniq ?>'></script>
-->

<script type='text/javascript' language='JavaScript'>
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
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
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
                        <?php echo $menu->out_caption(), "\n" ?>
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
                        case 1:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 2:
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
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();     // 出力バッファーをgzip圧縮 END
?>
