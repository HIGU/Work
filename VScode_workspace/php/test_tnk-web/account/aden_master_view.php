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
$act_ymd = 202211;    // Ａ伝情報では必要ない！
if ($act_ymd == '') {
    $act_ymd = date_offset(2);
}

//////////// 一頁の行数
define('PAGE', '25');

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
            $field = array(
                "Ａ伝",
                "枝",
                "製品番号",
                "販売商品名",
                "生産製品名",
                "計画番号",
                "承認図",
                "要領書",
                "工事番号",
                "受注数量",
                "受注単価",
                "金額",
                "希望納期",
                "回答納期",
            );
            for ($i=0; $i<count($field); $i++) {             // フィールド数分繰返し
            ?>
                <th nowrap><?php echo $field[$i] ?></th>
            <?php
            }
            ?>
            </tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 -->
                    <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
            <?php
            for ($r=0; $r<5; $r++) {
            ?>
                <tr>
                    <td nowrap class='pt10b' align='right'><?php echo ($r + 0 + 1) ?></td>    <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<count($field); $i++) {         // レコード数分繰返し
                    switch ($i) {
                    case 3:
                    case 4:
                        echo "<td nowrap align='left' class='pt9'>test</td>\n";
                        break;
                    case  9:
                    case 10:
                    case 11:
                        echo "<td nowrap align='right' class='pt9'>300</td>\n";
                        break;
                    case 12:
                    case 13:
                        echo "<td nowrap align='center' class='pt9'>2022/11/01</td>\n";
                        break;
                    default:
                        echo "<td nowrap align='center' class='pt9'>300</td>\n";
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
