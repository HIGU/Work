<?php
//////////////////////////////////////////////////////////////////////////////
// サービス割合 部門別 照会                                                 //
// Copyright(C) 2003-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/10/24 Created   service_percentage_view.php                         //
//            JavaScriptで修正ボタン追加 locattion.replace(xx_input.php)    //
// 2004/04/19 前期の実績をグレーに変更                                      //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2007/01/24 MenuHeaderクラス対応                                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
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
$menu->set_RetUrl($url_referer);        // 上記の結果をセット
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:i:s");

$menu_title = "202211 サービス割合 test1 部門 照会";
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title($menu_title);


/////////// HTML Header を出力してキャッシュを制御
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
<script language="JavaScript">
<!--
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (("0" > c) || (c > "9")) {
            alert("数値以外は入力出来ません。");
            return false;
        }
    }
    return true;
}
// -->
</script>
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
    font-size:  10pt;
}
.pt10b {
    font:bold 10pt;
}
.pt10b {
    font-size:   10pt;
    font-weight: bold;
}
.pt11bR {
    font-size:   11pt;
    font-weight: bold;
    color: red;
    font-family: monospace;
}
.pt11b {
    font-size:   11pt;
    font-weight: bold;
}
.pt12b {
    font-size:   12pt;
    font-weight: bold;
    font-family: monospace;
}
th {
    font-size:   9pt;
    font-weight: bold;
}
.title-font {
    font-size:   13.5pt;
    font-weight: bold;
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
    font:bold 12pt;
    font-family: monospace;
}
.zenki {
    font-size:  10pt;
    color:      gray;
}
-->
</style>
</head>
<body>
    <center>
<?php echo $menu->out_title_border() ?>
        
            <form name='page_form' method='post' action='<?= $url_referer ?>'>
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <tr>
                <!--
                <td align='left'>
                    <table align='left' border='3' cellspacing='0' cellpadding='0'>
                        <td align='left'>
                            <input class='pt10b' type='button' name='backward' value='前頁'>
                        </td>
                    </table>
                </td>
                -->
                <td align='center'>
                    <!-- <?= $menu_title . "　単位：％\n" ?> -->
                    <table align='center' border='3' cellspacing='0' cellpadding='0'>
                        <td align='right' class='pt11b'>
                            <input class='pt11b' type='submit' name='save' value='ＯＫ'>　単位：％　
                            <input class='pt11b' type='button' name='repair' value='修正' 
                            onClick="JavaScript:location.replace('service_percentage_input.php?view=ret')">
                        </td>
                    </table>
                </td>
                <!--
                <td align='right'>
                    <table align='right' border='3' cellspacing='0' cellpadding='0'>
                        <td align='right'>
                            <input class='pt10b' type='button' name='forward' value='次頁'>
                        </td>
                    </table>
                </td>
                -->
            </tr>
        </table>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th width='10' bgcolor='yellow'>No.</th>        <!-- 行ナンバーの表示 -->
                <?php
                $field = array(
                    "コード", "経理部門", "社員番号", "名前"
                );
                for ($i=0; $i<count($field); $i++) {             // フィールド数分繰返し
                    echo "<th bgcolor='yellow' nowrap>{$field[$i]}</th>\n";
                }
                ?>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <?php
                $res = array(
                    [1, 1, 100, "test1"],
                    [2, 2, 200, "test2"],
                    [3, 3, 300, "test3"],
                    [4, 4, 400, "test4"],
                    [5, 5, 500, "test5"],
                    [6, 6, 600, "test6"],
                    [7, 7, 700, "test7"],
                    [8, 8, 800, "test8"],
                    [9, 9, 900, "test9"],
                );
                $percent = array(
                    [10, 1, 100, "test1", "合計"=>100],
                    [20, 2, 200, "test2", "合計"=>200],
                    [30, 3, 300, "test3", "合計"=>300],
                    [40, 4, 400, "test4", "合計"=>400],
                    [50, 5, 500, "test5", "合計"=>500],
                    [60, 6, 600, "test6", "合計"=>600],
                    [70, 7, 700, "test7", "合計"=>700],
                    [80, 8, 800, "test8", "合計"=>800],
                    [90, 9, 900, "test9", "合計"=>900],
                );
                for ($r=0; $r < 9; $r++) {
                    echo "<tr>\n";
                        printf("<td class='pt10b' align='right'>%d</td>\n", $r + 1);    // 行番号の表示
                    for ($i=0; $i<4; $i++) {       // レコード数分繰返し
                        if ($i == (5 - 1) ) {          // 合計なら
                            echo "    <td align='right' class='pt10b'>{$percent[$r]['合計']}</td>\n";
                        } elseif ( $i >= $num ) {           // 入力用フィールド
                            echo "<td align='right' class='pt10b'>{$percent[$r][$i-$num]}</td>\n";
                        } elseif ($res[$r][$i] != "") {     // 項目があれば
                            echo "<td align='center' class='pt10b'>{$res[$r][$i]}</td>\n";
                        } else {                            // 項目が無ければ
                            if ($i == 2) {
                                echo "<td align='center' class='pt10b'>経費のみ</td>\n";
                            } else {
                                echo "<td align='center' class='pt10b'>---</td>\n";
                            }
                        }
                    }
                    echo "</tr>\n";
                    echo "<tr>\n";
                    echo "    <td colspan='5' align='right' class='zenki'>\n";
                    echo "        前期実績202103決算\n";
                    echo "    </td>\n";
                    $zenki = array(
                        [10, "合計"=>10],
                        [20, "合計"=>20],
                        [30, "合計"=>30],
                        [40, "合計"=>40],
                        [50, "合計"=>50],
                        [60, "合計"=>60],
                        [70, "合計"=>70],
                        [80, "合計"=>80],
                        [90, "合計"=>90],
                    );
                    for ($j=0; $j< 1; $j++) {
                        echo "    <td align='right' class='zenki'>{$zenki[$r][$j]}</td>\n";
                    }
                    echo "    <td align='right' class='zenki'>{$zenki[$r]['合計']}</td>\n";
                    echo "</tr>\n";
                }
                ?>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
            </form>
    </center>
</body>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
