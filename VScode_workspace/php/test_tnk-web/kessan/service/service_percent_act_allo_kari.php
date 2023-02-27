<?php
//////////////////////////////////////////////////////////////////////////////
// サービス割合処理 全体の割合(配賦率)から製造経費の配賦                    //
// Copyright(C) 2003-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/10/29 Created   service_percent_act_allo_kari.php                   //
// 2003/10/29 $per割合の計算結果を小数点以下４桁を５桁に変更100%の対策      //
//            number_format()を３桁から１桁へ変更                           //
// 2003/11/12 group by item_no,item,order_no order by order_no,item_no      //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2004/08/24 仮締め用のサービス割合に変更 service_percent_act_all_kari.php //
// 2007/01/23 製造間接費の配賦金額をテーブルに格納を追加  MenuHeader()対応  //
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

////////////// 認証チェック

//////////// 対象年月のセッションデータ取得
$service_ym = date('Ym');        // セッションデータがない場合の初期値(前月)
if (substr($service_ym,4,2) != 01) {
    $service_ym--;
} else {
    $service_ym = $service_ym - 100;
    $service_ym = $service_ym + 11;   // 前年の12月にセット
}

//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:i:s");
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if (substr($service_ym,6,2) == '32') {
    $view_ym = substr($service_ym,0,6) . '決算';
} else {
    $view_ym = $service_ym;
}
$menu_title = "$view_ym 仮締め サービス割合による製造間接費 配賦金額 処理";
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title($menu_title);
//////////// 表題の設定
$menu->set_caption('製 造 経 費　間 接 費　の　配 賦 金 額　集 計');

///// 前半期末 年月の算出
$yyyy = substr($service_ym, 0,4);
$mm   = substr($service_ym, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
    $zenki_ym = $yyyy . '09';     // 期初年月
} elseif (($mm >= 10) && ($mm <= 12)) {
    $zenki_ym = $yyyy . '09';     // 期初年月
} else {
    $zenki_ym = $yyyy . '03';     // 期初年月
}

$rows_fld1 = 5;
$rows_fld2 = 5;

$field1 = array(
    "test1",
    "test2",
    "test3",
    "test4",
    "test5",
);
$field2 = array(
    "dummy1",
    "dummy2",
    "dummy3",
    "dummy4",
    "dummy5",
);


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
                        <td align='center' valign='middle' class='ok_button'>
                            <input class='ok_button' type='submit' name='save' value=' ＯＫ '>
                            単位：率=％,金=円
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
                    <td rowspan='4' width='10' align='center' class='pt10' bgcolor='#ffcf9c'>工場間接費</td>
                    <td width='10' align='center' class='pt10' bgcolor='#ffcf9c'>直接</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='center' class='pt11b' bgcolor='#ffcf9c' nowrap><?php echo $field1[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ffcf9c' nowrap>小　計</td>
                    <td rowspan='4' width='10' align='center' class='pt10' bgcolor='#ceceff'>調達部門費</td>
                    <td width='10' align='center' class='pt10' bgcolor='#ceceff'>直接</td>
                    <?php for ($i=0; $i<$rows_fld2; $i++) { ?>
                        <td align='center' class='pt11b' bgcolor='#ceceff' nowrap><?php echo $field2[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ceceff' nowrap>小　計</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe' nowrap>合　計</td>
                </tr>
                <tr>
                    <td width='10' align='center' class='pt10' bgcolor='#ffcf9c'>率</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'>5</td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'>25</td>
                    <td width='10' align='center' class='pt10' bgcolor='#ceceff'>率</td>
                    <?php for ($i=$rows_fld1; $i<($rows_fld1+$rows_fld2); $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'>5</td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'>25</td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'>50</td>
                </tr>
                <tr>
                    <td width='10' align='center' class='pt10' bgcolor='#ffcf9c'>集計</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'>5</td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'>25</td>
                    <td width='10' align='center' class='pt10' bgcolor='#ceceff'>集計</td>
                    <?php for ($i=0; $i<$rows_fld2; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'>5</td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'>25</td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'>50</td>
                </tr>
                <tr>
                    <td width='10' align='center' class='pt10' bgcolor='#ffcf9c'>金額</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'>5</td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'>25</td>
                    <td width='10' align='center' class='pt10' bgcolor='#ceceff'>金額</td>
                    <?php for ($i=$rows_fld1; $i<($rows_fld1+$rows_fld2); $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'>5</td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'>25</td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'>50</td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <hr>
        
        <!--------------- ここから経理部門コード毎の明細を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr align='center' bgcolor='#beffbe'>
                    <td colspan='<?php echo $rows_fld1+$rows_fld2+8 ?>' class='pt11b'> <!-- colspanを過去20にしていた -->
                        製 造 経 費　間 接 費　の　配 賦 金 額　　明 細
                    </td>
                </tr>
                <tr>
                    <td align='center' class='pt10' bgcolor='#ffffbe' nowrap>No</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe' nowrap>コード</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe' nowrap>部門名</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>　</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='center' class='pt11b' bgcolor='#ffcf9c' nowrap><?php echo $field1[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ffcf9c' nowrap>小　計</td>
                    <?php for ($i=0; $i<$rows_fld2; $i++) { ?>
                        <td align='center' class='pt11b' bgcolor='#ceceff' nowrap><?php echo $field2[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ceceff' nowrap>小　計</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe' nowrap>合　計</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe' nowrap>部門経費</td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- フッターは合計を表示 -->
                <tr>
                    <td colspan='4' align='right' class='pt10' bgcolor='#ffffbe'>率　計</td>
                    <td colspan='<?php echo $rows_fld1+1 ?>' align='right' class='pt10' bgcolor='#ffcf9c'>25</td>
                    <td colspan='<?php echo $rows_fld2+1 ?>' align='right' class='pt10' bgcolor='#ceceff'>25</td>
                    <td align='right' class='pt10' bgcolor='#ffffbe'>50</td>
                    <td rowspan='2' align='right' class='pt10' bgcolor='#ffffbe'>50</td>
                </tr>
                <tr>
                    <td colspan='4' align='right' class='pt11b' bgcolor='#ffffbe'>合　計</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt10' bgcolor='#ffcf9c'>5</td>
                    <?php } ?>
                    <td align='right' class='pt10' bgcolor='#ffcf9c'>25</td>
                    <?php for ($i=$rows_fld1; $i<($rows_fld1+$rows_fld2); $i++) { ?>
                        <td align='right' class='pt10' bgcolor='#ceceff'>5</td>
                    <?php } ?>
                    <td align='right' class='pt10' bgcolor='#ceceff'>25</td>
                    <td align='right' class='pt10' bgcolor='#ffffbe'>50</td>
                </tr>
            </TFOOT>
            <TBODY>
                <?php
                    $rows_mei = 5;
                    for ($r=0; $r<$rows_mei; $r++) { ?>
                <tr>
                    <td rowspan='2' nowrap align='center' class='pt10' bgcolor='#ffffbe'><?php echo ($r+1) ?></td>
                    <td rowspan='2' nowrap align='center' class='pt10' bgcolor='#ffffbe'>5</td>
                    <td rowspan='2' nowrap align='center' class='pt10' bgcolor='#ffffbe'>5</td>
                    <td width='10' align='center' class='pt10' bgcolor='#ffffbe'>率</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt10' bgcolor='#ffcf9c'>5</td>
                    <?php } ?>
                    <td align='right' class='pt10' bgcolor='#ffcf9c'>25</td>
                    <?php for ($i=$rows_fld1; $i<($rows_fld1+$rows_fld2); $i++) { ?>
                        <td align='right' class='pt10' bgcolor='#ceceff'>5</td>
                    <?php } ?>
                    <td align='right' class='pt10' bgcolor='#ceceff'>25</td>
                    <td align='right' class='pt10' bgcolor='#ffffbe'>50</td>
                    <td rowspan='2' align='right' class='pt10' bgcolor='#ffffbe'>50</td>
                </tr>
                <tr>
                    <td width='10' align='center' class='pt10' bgcolor='#ffffbe'>金</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt10' bgcolor='#ffcf9c'>5</td>
                    <?php } ?>
                    <td align='right' class='pt10' bgcolor='#ffcf9c'>25</td>
                    <?php for ($i=$rows_fld1; $i<($rows_fld1+$rows_fld2); $i++) { ?>
                        <td align='right' class='pt10' bgcolor='#ceceff'>5</td>
                    <?php } ?>
                    <td align='right' class='pt10' bgcolor='#ceceff'>25</td>
                    <td align='right' class='pt10' bgcolor='#ffffbe'>50</td>
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
