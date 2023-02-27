<?php
//////////////////////////////////////////////////////////////////////////
// サービス割合 アイテムマスターメンテナンス                            //
// Copyright(C) 2003 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp   //
// Changed history                                                      //
// 2003/10/21 Created   service_item_master_mnt.php                     //
// 2003/10/22 追加・変更・削除 及び コピーボタンをロジックに取込んだ    //
// 2003/10/24 内作間接費(工場間接費)外作間接費(調達部門費)のintextカラム//
// 2003/11/12 div(事業部)section(部門別)order_no(表示順)のカラムを追加  //
// 2007/01/24 MenuHeaderクラス対応                                      //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(10,  5);                    // site_index=10(損益メニュー) site_id=5(サービス割合メニュー)
////////////// リターンアドレス設定(絶対指定する場合)
$menu->set_RetUrl($_SESSION['service_referer']);    // 分岐処理前に保存されている呼出元をセットする
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
$url_referer     = $_SESSION['service_referer'];    // 分岐処理前に保存されている呼出元をセットする

////////////// 認証チェック
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "あなたは許可されていません。<br>管理者に連絡して下さい。";
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}

//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:i:s");
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu_title = "サービス割合 マスターメンテナンス";
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title($menu_title);

//////////// 対象年月のセッションデータ取得
if (isset($_SESSION['service_ym'])) {
    $service_ym = $_SESSION['service_ym']; 
} else {
    $service_ym = date('Ym');        // セッションデータがない場合の初期値(前月)
    if (substr($service_ym,4,2) != 01) {
        $service_ym--;
    } else {
        $service_ym = $service_ym - 100;
        $service_ym = $service_ym + 11;   // 前年の12月にセット
    }
}
//////////// システムメッセージの初期化
$_SESSION['s_sysmsg'] = '';

//////////// 追加ボタンが押された時
if (isset($_POST['add'])) {
    $query = sprintf('select item_no from service_item_master where item_no=%d', $_POST['item_no']);
    $res_chk = array();
    if ( getResult2($query, $res_chk) > 0 ) {
        $_SESSION['s_sysmsg'] .= sprintf('%d :は既に登録済みです！', $_POST['item_no']);    // .= に注意
    } else {
        $query = sprintf("insert into service_item_master (item_no, intext, item, note, div, section, order_no)
                          values (%d, %d, '%s', '%s', '%s', '%s', %d)",
                $_POST['item_no'], $_POST['intext'], $_POST['item'], $_POST['note'], $_POST['div'], $_POST['section'], $_POST['order_no']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf('%d :の登録に失敗！', $_POST['item_no']);    // .= に注意
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>%d: %s: を登録しました！</font>",
                    $_POST['item_no'], $_POST['item']);    // .= に注意
        }
    }
}

//////////// 削除ボタンが押された時
if (isset($_POST['del'])) {
    $query = sprintf('select item_no from service_item_master where item_no=%d', $_POST['item_no']);
    $res_chk = array();
    if ( getResult2($query, $res_chk) <= 0 ) {
        $_SESSION['s_sysmsg'] .= sprintf('%d :は登録されていません！', $_POST['item_no']);    // .= に注意
    } else {
        $query = sprintf("delete from service_item_master where item_no=%d",
                $_POST['item_no']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf('%d :の削除に失敗！', $_POST['item_no']);    // .= に注意
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>%d: %s: を削除しました！</font>",
                    $_POST['item_no'], $_POST['item']);    // .= に注意
        }
    }
}

//////////// 変更ボタンが押された時
if (isset($_POST['chg'])) {
    $query = sprintf('select item_no from service_item_master where item_no=%d', $_POST['item_no']);
    $res_chk = array();
    if ( getResult2($query, $res_chk) <= 0 ) {
        $_SESSION['s_sysmsg'] .= sprintf('%d :は登録されていません！', $_POST['item_no']);    // .= に注意
    } else {
        $query = sprintf("update service_item_master set item_no=%d, intext=%d, item='%s', note='%s',
                          div='%s', section='%s', order_no=%s where item_no=%d",
                          $_POST['item_no'], $_POST['intext'], $_POST['item'], $_POST['note'],
                          $_POST['div'], $_POST['section'], $_POST['order_no'],
                          $_POST['item_no']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf('%d :の変更に失敗！', $_POST['item_no']);    // .= に注意
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>%d: %s: を変更しました！</font>",
                    $_POST['item_no'], $_POST['item']);    // .= に注意
        }
    }
}

//////////// service_item_master からマスターデータ取得
$query = "select item_no as コード, intext as 内外間接費, item as 直接部門, note as 備考,
          div as 事業部, section as 部門別, order_no as 表示順,
          regdate::date as 初回登録, last_date::date as 更新日
          from service_item_master order by order_no ASC";
$res = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
// if ( ($rows=getResult2($query, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = '間接部門明細が無いか取得できません！';
    // header("Location: $url_referer");                   // 直前の呼出元へ戻る
    // exit();
    $num = 0;
} else {
    $num = count($field);       // フィールド数取得
}

//////////// コピーボタンが押された時
if (isset($_POST['cpy'])) {
    $tmp_item_no = $res[$_POST['cpy']-1][0];
    $intext      = $res[$_POST['cpy']-1][1];
    $tmp_item    = $res[$_POST['cpy']-1][2];
    $tmp_note    = $res[$_POST['cpy']-1][3];
    $div         = $res[$_POST['cpy']-1][4];
    $section     = $res[$_POST['cpy']-1][5];
    $order_no    = $res[$_POST['cpy']-1][6];
} else {
    $tmp_item_no = '';
    $intext      = '';
    $tmp_item    = '';
    $tmp_note    = '';
    $div         = '';
    $section     = '';
    $order_no    = '';
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<style type="text/css">
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt10 {
    font-size: 10pt;
}
.pt10b {
    font:bold 10pt;
}
.pt11b {
    font:bold 11pt;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
th {
    font:bold 11pt;
}
.title-font {
    font:bold 13.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:none;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
.today-font {
    font-size: 10.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:1.0pt solid windowtext;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
.explain_font {
    font-size: 8.5pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
.right{
    text-align:right;
    font:bold 10pt;
    font-family: monospace;
}
-->
</style>
</head>
<body>
    <center>
<?php echo $menu->out_title_border() ?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?= $current_script ?>'>
                <tr>
                    <td align='center' class='pt11b'>
                        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
                            <tr><td> <!----------- ダミー(デザイン用) ------------>
                        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
                            <th>コード</th> <th nowrap>内外費</th> <th nowrap>直接部門名</th> <th>備  考</th> <th>事業部</th> <th>部門別</th> <th>表示順</th>
                            <tr>
                                <td align='center'>
                                    <input type='text' class='right' name='item_no' size='4' maxlength='4' value='<?= $tmp_item_no ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='right' name='intext' size='1' maxlength='1' value='<?= $intext ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='pt10' name='item' size='10' maxlength='20' value='<?= $tmp_item ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='pt10' name='note' size='40' maxlength='30' value='<?= $tmp_note ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='right' name='div' size='1' maxlength='1' value='<?= $div ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='right' name='section' size='1' maxlength='1' value='<?= $section ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='right' name='order_no' size='4' maxlength='5' value='<?= $order_no ?>'>
                                </td>
                            </tr>
                        </table>
                            </td></tr>
                        </table> <!----------------- ダミーEnd ------------------>
                    </td>
                    <td width='65' align='center'>
                        <table align='center' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='add' value='追加'>
                            </td>
                        </table>
                    </td>
                    <td width='60' align='center'>
                        <table align='center' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='del' value='削除'>
                            </td>
                        </table>
                    </td>
                    <td width='60' align='center'>
                        <table align='center' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='chg' value='変更'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <br>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <caption>
            <font class='pt10'>
                内外間接費：　内作間接費(工場間接費)＝１　外作間接費(調達部門費)＝２　
                部門別：H=標準品　B=Ｌバイモル部門　S=Ｃ特注部門
            </font>
        </caption>
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th width='10' bgcolor='yellow'>No.</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                    echo "<th bgcolor='yellow'>{$field[$i]}</th>\n";
                }
                ?>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
            <form name='page_form' method='post' action='<?= $current_script ?>'>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    echo "<tr>\n";
                        printf("<td class='pt10b' align='right'><input class='pt10' type='submit' name='cpy' value='%d'></td>\n", $r + 1);    // 行番号の表示
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        echo "<!--  bgcolor='#ffffc6' 薄い黄色 -->\n";
                        if ($i == 3) {          // 備考
                            echo "<td nowrap align='left' class='pt10b'>{$res[$r][$i]}</td>\n";
                        } elseif ($i == 6) {    // 順番(ソート順)
                            echo "<td nowrap align='right' class='pt10b'>{$res[$r][$i]}</td>\n";
                        } else {
                            echo "<td nowrap align='center' class='pt10b'>{$res[$r][$i]}</td>\n";
                        }
                        echo "<!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
                    }
                    echo "</tr>\n";
                }
                ?>
            </form>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
