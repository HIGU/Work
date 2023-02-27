<?php
//////////////////////////////////////////////////////////////////////////////
// Ａ伝情報ファイルの照会 ＆ チェック用  更新元 UKWLIB/W#MIADIM             //
// Copyright(C) 2003-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// 変更経歴                                                                 //
// 2003/11/27 新規作成  aden_master_view.php                                //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2007/09/10 旧メニューロジックを新メニューロジックへ php標準タグ(推奨値)へ//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 60);          // 最大実行時間=1分 CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(20, 13);                    // site_index=20(経理メニュー) site_id=13(Ａ伝情報の更新チェック)
////////////// リターンアドレス設定(絶対指定する場合)
// $menu->set_RetUrl(ACT_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('Ａ伝情報の更新 チェックリスト');
//////////// 表題の設定
$menu->set_caption('Ａ伝情報の更新 チェックリスト');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// 対象年月日を取得
$act_ymd = $_SESSION['act_ymd'];    // Ａ伝情報では必要ない！
if ($act_ymd == '') {
    $act_ymd = date_offset(2);
}

//////////// 一頁の行数
define('PAGE', '25');

//////////// SQL 文の where 句を 共用する
// $search = sprintf("where aden_no>='%s'", $act_ymd);
$search = '';

//////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
$query = sprintf('SELECT count(*) FROM aden_master %s', $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
}

//////////// ページオフセット設定
if ( isset($_REQUEST['forward']) ) {                       // 次頁が押された
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                // 次頁が押された
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {               // 現在のページを維持する
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // 初回の場合は０で初期化
}
$offset = $_SESSION['offset'];

//////////// Ａ伝情報のチェックリスト作成 Query & 初期化
$query = sprintf("
        SELECT
            aden_no     AS Ａ伝,                        -- 0
            eda_no      AS 枝,                          -- 1
            CASE
                WHEN trim(parts_no) = '' THEN '---'
                ELSE parts_no
            END         AS 製品番号,                    -- 2
            sale_name   AS 販売商品名,                  -- 3
            CASE
                WHEN trim(midsc) IS NULL THEN '---'
                ELSE substr(midsc, 1, 12)
            END         AS 生産製品名,                  -- 4
            CASE
                WHEN trim(plan_no) = '' THEN '---'
                ELSE plan_no
            END         AS 計画番号,                    -- 5
            CASE
                WHEN trim(approval) = '' THEN '---'
                ELSE approval
            END         AS 承認図,                      -- 6
            CASE
                WHEN trim(ropes_no) = '' THEN '---'
                ELSE ropes_no
            END         AS 要領書,                      -- 7
            CASE
                WHEN trim(kouji_no) = '' THEN '---'
                ELSE kouji_no
            END         AS 工事番号,                    -- 8
            order_q     AS 受注数量,                    -- 9
            order_price AS 受注単価,                    --10
            Uround(order_q * order_price, 0) AS 金額,   --11
            espoir_deli AS 希望納期,                    --12
            delivery    AS 回答納期                     --13
        FROM
            aden_master
        LEFT OUTER JOIN
             miitem ON parts_no=mipn
        %s 
        ORDER BY aden_no DESC OFFSET %d LIMIT %d
        
    ", $search, $offset, PAGE);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= 'Ａ伝情報のデータが取得できません！';
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
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>

<!--    ファイル指定の場合
<script type='text/javascript' language='JavaScript' src='template.js?<?php echo $uniq ?>'>
</script>
-->

<script type='text/javascript' language='JavaScript'>
<!--
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意 -->
<link rel='stylesheet' href='act_menu.css?<?php echo $uniq ?>' type='text/css' media='screen'>

<style type="text/css">
<!--
.winbox_field th {
    background-color:   yellow;
    color:              blue;
    font-weight:        bold;
    font-size:          0.80em;
    font-family:        monospace;
}
-->
</style>
</head>
<body style='overflow-y:hidden;'>
    <center>
<?php echo $menu->out_title_border() ?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
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
                        <?php echo format_date($act_ymd) . "  {$menu->out_title()}\n" ?>
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
        <table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <!-- テーブル ヘッダーの表示 -->
            <tr>
                <th nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->
            <?php
            for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
            ?>
                <th nowrap><?php echo $field[$i] ?></th>
            <?php
            }
            ?>
            </tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 -->
                    <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
            <?php
            for ($r=0; $r<$rows; $r++) {
            ?>
                <tr>
                    <td nowrap class='pt10b' align='right'><?php echo ($r + $offset + 1) ?></td>    <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                    switch ($i) {
                    case 3:
                    case 4:
                        echo "<td nowrap align='left' class='pt9'>{$res[$r][$i]}</td>\n";
                        break;
                    case  9:
                    case 10:
                    case 11:
                        echo "<td nowrap align='right' class='pt9'>" . number_format($res[$r][$i], 0) . "</td>\n";
                        break;
                    case 12:
                    case 13:
                        echo "<td nowrap align='center' class='pt9'>" . format_date($res[$r][$i]) . "</td>\n";
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
