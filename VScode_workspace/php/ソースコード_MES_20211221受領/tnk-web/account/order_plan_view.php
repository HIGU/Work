<?php
//////////////////////////////////////////////////////////////////////////////
// 発注計画の照会 ＆ チェック用  更新元 UKWLIB/W#MIOPLN                     //
// Copyright(C) 2003-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/11/20 Created   order_plan_view.php                                 //
// 2003/12/11 工事番号がブランクの時に'--------'表示になるように変更        //
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
$menu->set_site(20, 12);                    // site_index=20(経理メニュー) site_id=12(発注計画の更新チェック)
////////////// リターンアドレス設定(絶対指定する場合)
// $menu->set_RetUrl(ACT_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('発注計画データの更新データ チェックリスト');
//////////// 表題の設定
$menu->set_caption('発注計画データの更新データ チェックリスト');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// 対象年月日を取得
$act_ymd = $_SESSION['act_ymd'];
if ($act_ymd == '') {
    $act_ymd = date_offset(2);
}

//////////// 一頁の行数
define('PAGE', '25');

//////////// SQL 文の where 句を 共用する
// $search = sprintf('where sei_no=%d', $act_ymd);

//////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
/********
// $query = sprintf('select count(*) from act_payable %s', $search);
$query = 'select count(*) from act_payable';
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
}
**********/
$maxrows = 117000;  // 2003/11/20 現在のレコード数 処理速度を上げるためリテラルで指定

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

//////////// 表形式のデータ表示用のサンプル Query & 初期化
$query = sprintf("
        select
            o.sei_no                as 製造番号,            -- 0
            o.order5                as 注文番号,            -- 1
            o.parts_no              as 部品番号,            -- 2
            substr(m.midsc,1,12)    as 部品名,              -- 3
            CASE
                WHEN trim(o.kouji_no) = ''  THEN '--------'
                ELSE o.kouji_no
            END                     as 工事,                -- 4
            o.order_q               as 発注数,              -- 5
            o.utikiri               as 打切数,              -- 6
            o.nyuko                 as 納入数,              -- 7
            o.zan_q                 as 残数,                -- 8
            o.plan_date             as 発注予定,            -- 9
            o.last_delv             as 最終納期,            --10
            o.plan_cond             as 区分,                --11
            o.locate                as 入庫,                --12
            o.div                   as 事,                  --13
            o.org_delv              as 元納期               --14
        from
            order_plan as o left outer join miitem as m on o.parts_no = m.mipn
        -- where sei_no = 1482716
        order by sei_no DESC
        offset %d limit %d
    ", $offset, PAGE);       // 共用 $search は使用しない
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= '発注計画のデータがありません!';
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
<meta http-equip="Content-Script-Type" content="text/javascript">
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
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
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
                        <?php echo format_date($act_ymd) . '　' . $menu->out_caption() . "\n" ?>
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
                        echo "<td nowrap align='left' class='pt9'>{$res[$r][$i]}</td>\n";
                        break;
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                        echo "<td nowrap align='right' class='pt9'>" . number_format($res[$r][$i], 0) . "</td>\n";
                        break;
                    case  9:
                    case 10:
                    case 14:
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
