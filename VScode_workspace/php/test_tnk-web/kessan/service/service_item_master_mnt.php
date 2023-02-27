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

//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:i:s");
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu_title = "サービス割合 マスターメンテナンス";
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title($menu_title);

$service_ym = date('Ym');        // セッションデータがない場合の初期値(前月)
if (substr($service_ym,4,2) != 01) {
    $service_ym--;
} else {
    $service_ym = $service_ym - 100;
    $service_ym = $service_ym + 11;   // 前年の12月にセット
}

$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
                                    <input type='text' class='right' name='item_no' size='4' maxlength='4' value='<?= 1 ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='right' name='intext' size='1' maxlength='1' value='<?= 100 ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='pt10' name='item' size='10' maxlength='20' value='<?= "dummy" ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='pt10' name='note' size='40' maxlength='30' value='<?= "test" ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='right' name='div' size='1' maxlength='1' value='<?= "div" ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='right' name='section' size='1' maxlength='1' value='<?= "section" ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='right' name='order_no' size='4' maxlength='5' value='<?= "order_no" ?>'>
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
                $field = array(
                    "コード", "内外間接費", "直接部門", "備考",
                    "事業部", "部門別", "表示順",
                    "初回登録", "更新日"
                );
                for ($i=0; $i<count($field); $i++) {             // フィールド数分繰返し
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
                    $res = array(
                        [1, 100, "test1", "note", "div1", "section1", "view1", 202211, 202211],
                        [2, 200, "test2", "note", "div2", "section2", "view2", 202211, 202211],
                        [3, 300, "test3", "note", "div3", "section3", "view3", 202211, 202211],
                        [4, 400, "test4", "note", "div4", "section4", "view4", 202211, 202211],
                        [5, 500, "test5", "note", "div5", "section5", "view5", 202211, 202211],
                    );

                for ($r=0; $r<5; $r++) {
                    echo "<tr>\n";
                        printf("<td class='pt10b' align='right'><input class='pt10' type='submit' name='cpy' value='%d'></td>\n", $r + 1);    // 行番号の表示
                    for ($i=0; $i<count($res[0]); $i++) {         // レコード数分繰返し
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
