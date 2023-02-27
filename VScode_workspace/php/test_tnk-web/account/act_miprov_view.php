<?php
//////////////////////////////////////////////////////////////////////////////
// 支給更新データの照会 ＆ チェック用  更新元 UKWLIB/W#MIPROV               //
// Copyright(C) 2003-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2003/11/20 Created   act_miprov_view.php (provide = 供給する)            //
//            miitem を left outer join するとかなり遅くなる 検討中         //
//            key fieldはchar(9)等で作成する事(原因はこれ) ::char(9)回避    //
// 2003/11/21 where 句に delete='' を追加  削除区分のチェック抜け対応       //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2005/02/08 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/08/20 set_focus()の機能は MenuHeader で実装しているので無効化した   //
// 2015/10/19 八下田派遣科目0を集計しようとしたが、そもそもWebに取り込んで  //
//            いない為、元に戻した。                                   大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(20, 11);                    // site_index=30(生産メニュー)20=(経理メニュー) site_id=11(支給実績)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('支 給 実 績 の 照 会');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   INDUST . 'log_view/php_error_log.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// 対象年月日を取得
$act_ymd = 202211;    // ind_branch.phpで設定されている
if ($act_ymd == '') {
    $act_ymd = date_offset(2);
}
//////////// 表題の設定
$menu->set_caption(format_date($act_ymd) . '　' . $menu->out_title());

//////////// 一頁の行数
define('PAGE', '20');

/////////////// HTML Header を出力してキャッシュを制御
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
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt9 {
    font:normal 9pt;
    font-family: monospace;
}
.pt10b {
    font:bold 10pt;
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
}
th {
    background-color:yellow;
    color:blue;
    font:bold 10pt;
    font-family: monospace;
}
.winbox {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
-->
</style>
</head>
<body onLoad='set_focus()' style='overflow-y:hidden;'>
    <center>
<?=$menu->out_title_border()?>
        
        <table width='250' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    合　計　金　額
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= "200" ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    原 材 料 1
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= "200" ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    部品仕掛Ｃ2～5
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= "200" ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    その他 6～
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= "200" ?>
                </td>
            </tr>
        </table>
        
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
                    <td nowrap align='center' class='caption_font'>
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
                $field = array(
                    "更新日",
                    "支給番号",
                    "発注先",
                    "発注先名",
                    "計画番号",
                    "部品番号",
                    "部品名",
                    "条件",
                    "必要数",
                    "支給数",
                    "単価",
                    "金額",
                    "発行番号",
                );
                for ($i=0; $i<count($field); $i++) {             // フィールド数分繰返し
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
                for ($r=0; $r<5; $r++) {
                ?>
                    <tr>
                        <td class='winbox' nowrap align='right'><div class='pt10b'><?= ($r + $offset + 1) ?></div></td>    <!-- 行ナンバーの表示 -->
                    <?php
                    for ($i=0; $i<count($field); $i++) {         // レコード数分繰返し
                        switch ($i) {
                        case 3:
                        case 6:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>test</div></td>\n";
                            break;
                        case 8:
                        case 9:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", "200";
                            break;
                        case 10:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", "200";
                            break;
                        case 11:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", "200";
                            break;
                        default:
                            if ($res[$r][$i] != '         ') {  // 部品番号を想定
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>200</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>&nbsp;</div></td>\n";
                            }
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
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
