<?php
//////////////////////////////////////////////////////////////////////////////
// NK無償支給品・客先支給品の照会 ＆ チェック用  更新元 UKWLIB/W#PROVID     //
// Copyright(C) 2003-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/12/08 Created   provide_month_view.php                              //
// 2003/12/11 部品番号の'9%%'と'F%%'を対象から除外 9=テスト用 F=FA          //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2007/09/07 旧メニューロジックを新メニューロジックへ php標準タグ(推奨値)へ//
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
$menu->set_site(20, 37);                    // site_index=20(経理メニュー) site_id=37(客先支給品チェック)
////////////// リターンアドレス設定(絶対指定する場合)
// $menu->set_RetUrl(ACT_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('NK無償支給品・客先支給品のチェックリスト');
//////////// 表題の設定
$menu->set_caption('NK無償支給品・客先支給品のチェックリスト');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// 対象年月日を取得
if (isset($_SESSION['act_ym'])) {
    $act_ym = $_SESSION['act_ym'];
} else {
    $_SESSION['s_sysmsg'] = '対象年月が指定されていません！';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}

//////////// 一頁の行数
define('PAGE', '25');

//////////// SQL 文の WHERE 句を 共用する
$search = "WHERE reg_ym = $act_ym AND parts_no NOT LIKE '9%%' AND parts_no NOT LIKE 'F%%'";     // 対象年月

//////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
$query = sprintf('SELECT count(*) from provide_item %s', $search);
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

//////////// チェックリストの作表
$query = sprintf("
        SELECT
            parts_no                AS 部品番号,            -- 1
            substr(m.midsc,1,30)    AS 部品名,              -- 2
            CASE
                WHEN trim(type) = ''  THEN '-'
                WHEN trim(type) = 'M' THEN '日東無償'
                WHEN trim(type) = 'P' THEN '客先支給'
                ELSE type
            END                     AS 支給区分             -- 3
        FROM
            provide_item AS pro LEFT OUTER JOIN miitem AS m ON pro.parts_no = m.mipn
        -- WHERE句
        %s
        ORDER by parts_no ASC
        OFFSET %d LIMIT %d
    ", $search, $offset, PAGE);       // 共用 $search は使用
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= '無償支給品のデータが取得できません!';
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
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
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

<style type='text/css'>
<!--
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
                        <?php echo $act_ym . '　' . $menu->out_caption() . "\n" ?>
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
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
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
            <!-- 現在はフッターは何もない -->
                    <!--  bgcolor='#ffffc6' 薄い黄色 -->
                    <!-- サンプル<td class='winbox pt10b' rowspan='2' colspan='3' width='200' align='center' bgcolor='#ffffc6'>  </td> -->
            <?php
            for ($r=0; $r<$rows; $r++) {
            ?>
                <tr>
                    <td nowrap class='winbox pt10b' align='right'><?php echo ($r + $offset + 1) ?></td>    <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                    switch ($i) {
                    case 1:
                        if ($res[$r][$i] != '') {
                            echo "<td class='winbox pt10' nowrap width='350' align='left'>{$res[$r][$i]}</td>\n";
                        } else {
                            echo "<td class='winbox pt10' nowrap width='350' align='center'>---</td>\n";
                        }
                        break;
                    default:
                        echo "<td class='winbox pt10' nowrap align='center'>{$res[$r][$i]}</td>\n";
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
