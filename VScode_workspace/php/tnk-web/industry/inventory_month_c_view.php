<?php
//////////////////////////////////////////////////////////////////////////////
// 棚卸 金額 の照会 Ｃ(全体)  更新元 UKWLIB/W#MVTNPT                        //
// Copyright(C) 2003-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// 変更経歴                                                                 //
// 2003/11/26 新規作成  inventory_month_ctoku_view.php                      //
//            SERIAL型のインデックスを作成し SQLを高速化した                //
// 2003/12/04 テーブルをmonth_end→inventory_monthlyへ変更 金額順表示へ     //
// 2003/12/24 ob_gzhandlerをＸ 使用すると１頁１００件の時にGETが戻らないため//
// 2004/01/14 $_SESSION['act_ym'] → $_SESSION['ind_ym'] へ変更             //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
ini_set('display_errors','1');          // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分 CLI CGI版
// ob_start('ob_gzhandler');               // 出力バッファをgzip圧縮
session_start();                        // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');       // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');       // TNK に依存する部分の関数を require_once している
access_log();                           // Script Name は自動取得

////////////// サイトメニュー設定
$_SESSION['site_index'] = 30;           // 経理日報関係=20 最後のメニュー = 99   システム管理用は９９番
$_SESSION['site_id']    = 35;           // 下位メニュー無し <= 0    テンプレートファイルは６０番

////////////// リターンアドレス設定
$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
// $url_referer     = $_SERVER['HTTP_REFERER'];    // 呼出もとのURLを保存 前のスクリプトで分岐処理をしている場合は使用しない
$url_referer     = $_SESSION['act_referer'];     // 分岐処理前に保存されている呼出元をセットする

//////////////// 認証チェック
if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION['Auth'] <= 2) {                // 権限レベルが２以下は拒否
// if (account_group_check() == FALSE) {        // 特定のグループ以外は拒否
    $_SESSION['s_sysmsg'] = "認証されていないか認証期限が切れました。ログインからお願いします。";
    // header("Location: http:" . WEB_HOST . "menu.php");   // 固定呼出元へ戻る
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}

/********** Logic Start **********/
//////////// タイトルの日付・時間設定
$today = date('Y/m/d H:i:s');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// システムメッセージ変数 初期化
// $_SESSION['s_sysmsg'] = "";      // menu_site.php で使用するためここで初期化は不可

//////////// 対象年月を取得 (年月のみに注意)
if ( isset($_SESSION['ind_ym']) ) {
    $act_ym = $_SESSION['ind_ym'];
    $s_ymd  = $act_ym . '01';   // 開始日
    $e_ymd  = $act_ym . '99';   // 終了日
} else {
    $_SESSION['s_sysmsg'] = '対象年月が指定されていません！';
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu_title = "カプラ 全体 棚卸金額の照会";

//////////// 一頁の行数
define('PAGE', '100');

//////////// SQL 文の where 句を 共用する
// $search = "where (parts_no like 'LR%' or parts_no like 'LC%')";     // num_div 1=機工 3=リニア 5=カプラ
// $search = "where num_div='5' and tou_zai > 0 ";     // num_div 1=機工 3=リニア 5=カプラ
$search = "where invent_ym={$act_ym} and num_div='5'";     // num_div 1=機工 3=リニア 5=カプラ

//////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
$query = sprintf('select
                    count(*),
                    sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as 金額
                  from inventory_monthly as inv %s', $search);
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
$query = sprintf("
        select
            parts_no      as 部品番号,                  -- 0
            substr(m.midsc,1,12) as 部品名,             -- 1
            par_code      as 親製品,                    -- 2
            zen_zai       as 前月在庫,                  -- 3
            tou_zai       as 当月在庫,                  -- 4
            gai_tan       as 外注単価,                  -- 5
            Uround(tou_zai * gai_tan, 0) as 外注金額,   -- 6
            nai_tan       as 内作単価,                  -- 7
            Uround(tou_zai * nai_tan, 0) as 内作金額,   -- 8
            Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0) as 金額,    -- 9
            num_div       as 事業部                     -- 10
        from
            inventory_monthly as inv
        left outer join
            miitem as m
        on inv.parts_no = m.mipn
        %s 
        order by 金額 DESC
        offset %d limit %d
    ", $search, $offset, PAGE);       // 共用 $search は使用しない
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= '棚卸のデータが取得できません!';
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
}

/********** Logic End   **********/
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");               // 日付が過去
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // 常に修正されている
header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                                     // HTTP/1.0

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE><?= $menu_title ?></TITLE>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';
// -->
</script>

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
.pt8 {
    font:normal 8pt;
    font-family: monospace;
}
.pt9 {
    font:normal 9pt;
    font-family: monospace;
}
.pt10 {
    font:normal 10pt;
    font-family: monospace;
}
.pt10b {
    font:bold 10pt;
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
.title_font {
    font:bold 13.5pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.corporate_name {
    font:bold 10pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
th {
    background-color:yellow;
    color:blue;
    font:bold 10pt;
    font-family: monospace;
}
-->
</style>
</HEAD>
<BODY class='margin0' onLoad='set_focus()'>
    <center>
        <!----------------- ここは タイトルを表示する ------------------->
        <table width='100%' bgcolor='#d6d3ce'  cellspacing='0' cellpadding='1' border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' cellspacing='0' cellpadding='1' border='1'>
            <tr>
                <form name='return_form' method='post' action='<?= $url_referer ?>'>
                    <td width='60' bgcolor='blue' align='center' valign='center'>
                        <input class='pt12b' type='submit' name='return' value='戻る'>
                    </td>
                </form>
                <?= menu_OnOff($current_script . '?page_keep=1') ?>
                <td class='title_font' colspan='1' bgcolor='#d6d3ce' align='center'>
                    <?= $menu_title . "\n" ?>
                </td>
                <td class='today_font' colspan='1' bgcolor='#d6d3ce' align='center' width='140'>
                    <?= $today . "\n" ?>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <br>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?= $current_script ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='pt11b'>
                        <?= "{$act_ym}　{$menu_title}　合計=" . number_format($sum_kin) . '円　金額順　' . number_format($maxrows) . "点 \n" ?>
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
        <table width='100%' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
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
                        <td nowrap class='pt10b' align='right'><?= ($r + $offset + 1) ?></td>    <!-- 行ナンバーの表示 -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        switch ($i) {
                        case 1:
                            echo "<td nowrap align='left' class='pt9'>{$res[$r][$i]}</td>\n";
                            break;
                        case 3:
                        case 4:
                        case 6:
                        case 8:
                            echo "<td nowrap align='right' class='pt9'>" . number_format($res[$r][$i], 0) . "</td>\n";
                            break;
                        case 9:
                            echo "<td nowrap width='60' align='right' class='pt9'>" . number_format($res[$r][$i], 0) . "</td>\n";
                            break;
                        case 5:
                        case 7:
                            echo "<td nowrap align='right' class='pt9'>" . number_format($res[$r][$i], 2) . "</td>\n";
                            break;
                        default:
                            echo "<td nowrap align='center' class='pt9'>{$res[$r][$i]}</td>\n";
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
</BODY>
</HTML>
<?php
// ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
