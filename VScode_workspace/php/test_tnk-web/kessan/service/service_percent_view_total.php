<?php
//////////////////////////////////////////////////////////////////////////////
// サービス割合処理 全体の割合(配賦率) 照会                                 //
// Copyright(C) 2003-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/10/24 Created   service_percent_view_total.php                      //
// 2003/10/28 $per割合の計算結果を小数点以下４桁を５桁に変更100%の対策      //
//            number_formatを３桁から1桁へ変更                              //
// 2003/11/12 group by item_no,item,order_no order by order_no,item_no      //
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
$menu->set_RetUrl($_SESSION['service_referer']);    // 分岐処理前に保存されている呼出元をセットする
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

$rows_fld1=5;
$rows_fld2=5;

$menu_title = "$view_ym サービス割合による配賦率 照会";
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title($menu_title);
//////////// 表題の設定
$menu->set_caption('製 造 経 費　間 接 費　の　配 賦 率　集 計');

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
    font:10pt;
}
.pt10b {
    font:bold 10pt;
}
.pt11bR {
    font:bold 11pt;
    color: red;
    font-family: monospace;
}
.pt11b {
    font:bold 9pt;
}
.ok_button {
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
    font:bold 12pt;
    font-family: monospace;
}
-->
</style>
</head>
<body>
    <center>
<?php echo $menu->out_title_border() ?>
        
        <form name='page_form' method='post' action='<?php echo $menu->out_retUrl() ?>'>
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <tr>
                <td align='center'>
                    <!-- <?php echo $menu->out_caption() . "　単位：％\n" ?> -->
                    <table align='center' border='3' cellspacing='0' cellpadding='0'>
                        <td align='right' class='ok_button'>
                            <input class='ok_button' type='submit' name='save' value=' ＯＫ '>　単位：％　
                        </td>
                    </table>
                </td>
            </tr>
        </table>
        
        <!--------------- ここから全体の配賦率を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr align='center' bgcolor='#beffbe'>
                    <td colspan='<?php echo $rows_fld1+$rows_fld2+7 ?>' class='pt11b'> <!-- colspanを過去20にしていた -->
                        <?php echo $menu->out_caption(), "\n" ?>
                    </td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td rowspan='3' width='10' align='center' class='pt10' bgcolor='#ffcf9c'>工場間接費</td>
                    <td width='10' align='center' class='pt10' bgcolor='#ffcf9c'>直接</td>
                    <?php
                        $field1 = array(
                            "test1",
                            "test2",
                            "test3",
                            "test4",
                            "test5",
                        );
                        for ($i=0; $i<$rows_fld1; $i++) {
                    ?>
                        <td align='center' class='pt11b' bgcolor='#ffcf9c' nowrap>
                            <?php echo $field1[$i] ?>
                        </td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ffcf9c' nowrap>小　計</td>
                    <td rowspan='3' width='10' align='center' class='pt10' bgcolor='#ceceff'>調達部門費</td>
                    <td width='10' align='center' class='pt10' bgcolor='#ceceff'>直接</td>
                    <?php
                        $field2 = array(
                            "dummy1",
                            "dummy2",
                            "dummy3",
                            "dummy4",
                            "dummy5",
                        );
                        for ($i=0; $i<$rows_fld2; $i++) {
                    ?>
                        <td align='center' class='pt11b' bgcolor='#ceceff' nowrap>
                        <?php echo $field2[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ceceff' nowrap>小　計</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe' nowrap>合　計</td>
                </tr>
                <tr>
                    <td width='10' align='center' class='pt10' bgcolor='#ffcf9c'>率</td>
                    <?php
                        $per1_f = array(
                            1,
                            2,
                            3,
                            4,
                            5,
                            '小計'=>15
                        );
                        for ($i=0; $i<$rows_fld1; $i++) {
                    ?>
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $per1_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $per1_f['小計'] ?></td>
                    <td width='10' align='center' class='pt10' bgcolor='#ceceff'>率</td>
                    <?php
                        $per2_f = array(
                            6,
                            7,
                            8,
                            9,
                            10,
                            '小計'=>31
                        );
                        $per_sum_f = 46;
                        for ($i=0; $i<$rows_fld2; $i++) {
                    ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $per2_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $per2_f['小計'] ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo $per_sum_f ?></td>
                </tr>
                <tr>
                    <td width='10' align='center' class='pt10' bgcolor='#ffcf9c'>集計</td>
                    <?php
                        $point1_f = array(
                            1,
                            2,
                            3,
                            4,
                            5,
                            '小計'=>15
                        );
                        for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $point1_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $point1_f['小計'] ?></td>
                    <td width='10' align='center' class='pt10' bgcolor='#ceceff'>集計</td>
                    <?php
                        $point2_f = array(
                            6,
                            7,
                            8,
                            9,
                            10,
                            '小計'=>31
                        );
                        $point_sum_f = 46;
                        for ($i=0; $i<$rows_fld2; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $point2_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $point2_f['小計'] ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo $point_sum_f ?></td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <br>
        
        <!--------------- ここから経理部門コード毎の明細を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr align='center' bgcolor='#beffbe'>
                    <td colspan='<?php echo $rows_fld1+$rows_fld2+7 ?>' class='pt11b'> <!-- colspanを過去20にしていた -->
                        製 造 経 費　間 接 費　の　配 賦 率 集 計　　明 細
                    </td>
                </tr>
                <tr>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>No</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>コード</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>部門名</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>　</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='center' class='pt11b' bgcolor='#ffcf9c'><?php echo $field1[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ffcf9c'>小　計</td>
                    <?php for ($i=0; $i<$rows_fld2; $i++) { ?>
                        <td align='center' class='pt11b' bgcolor='#ceceff'><?php echo $field2[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ceceff'>小　計</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>合　計</td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- フッターは合計を表示 -->
                <tr>
                    <td colspan='4' align='right' class='pt10' bgcolor='#ffffbe'>率　計</td>
                    <td colspan='<?php echo $rows_fld1+1 ?>' align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo 13 ?></td>
                    <td colspan='<?php echo $rows_fld2+1 ?>' align='right' class='pt11b' bgcolor='#ceceff'><?php echo 14 ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo 27 ?></td>
                </tr>
                <tr>
                    <td colspan='4' align='right' class='pt10' bgcolor='#ffffbe'>合　計</td>
                    <td colspan='<?php echo $rows_fld1+1 ?>' align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo 15 ?></td>
                    <td colspan='<?php echo $rows_fld2+1 ?>' align='right' class='pt11b' bgcolor='#ceceff'><?php echo 16 ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo 31 ?></td>
                </tr>
            </TFOOT>
            <TBODY>
                <?php 
                    $rows_mei = 5;
                    $act_id = array(1, 2, 3, 4, 5);
                    $act_name = array(
                        "test1",
                        "test2",
                        "test3",
                        "test4",
                        "test5",
                    );
                    for ($r=0; $r<$rows_mei; $r++) { ?>
                <tr>
                    <td rowspan='2' nowrap align='center' class='pt10' bgcolor='#ffffbe'><?php echo ($r+1) ?></td>
                    <td rowspan='2' nowrap align='center' class='pt10' bgcolor='#ffffbe'><?php echo $act_id[$r] ?></td>
                    <td rowspan='2' nowrap align='center' class='pt10' bgcolor='#ffffbe'><?php echo $act_name[$r] ?></td>
                    <td width='10' align='center' class='pt10' bgcolor='#ffffbe'>率</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo 5 ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo 25 ?></td>
                    <?php for ($i=$rows_fld1; $i<($rows_fld1+$rows_fld2); $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo 6 ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo 36 ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo 61 ?></td>
                </tr>
                <tr>
                    <td width='10' align='center' class='pt10' bgcolor='#ffffbe'>計</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo 7 ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo 35 ?></td>
                    <?php for ($i=$rows_fld1; $i<($rows_fld1+$rows_fld2); $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo 8 ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo 40 ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo 75 ?></td>
                </tr>
                <?php } ?>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        </form>
    </center>
</body>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
