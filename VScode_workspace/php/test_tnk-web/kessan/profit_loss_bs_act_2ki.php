<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 ２期比較表 貸借対照表                                       //
// Copyright(C) 2012-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2012/01/24 Created   profit_loss_bs_act_2ki.php                          //
// 2012/01/26 Excelの２期比較表にあわせてレイアウトを調整                   //
// 2012/04/04 当期の一部で違うデータが表示されていたのを変更                //
// 2012/04/18 第４四半期のみ表示形式が違っていたのに対応                    //
//            調整を加味するように合計の計算やデータの取得を追加            //
// 2012/10/09 当期純利益の調整がデータに登録(手動直接)されているため        //
//            当期純利益の数字が０にならないので、調整を取り込まないように  //
//            変更                                                          //
// 2017/04/13 新規科目：未収収益追加                                        //
// 2017/08/04 新規科目：前払消費税追加                                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    // 実際の認証はprofit_loss_submit.phpで行っているaccount_group_check()を使用

////////////// サイト設定
// $menu->set_site(10, 7);                     // site_index=10(損益メニュー) site_id=7(月次損益)
//////////// 表題の設定
$menu->set_caption('栃木日東工器(株)');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('抽象化名',   PL . 'address.php');

///// 対象当月
$yyyymm   = 202211;
$ki       = 22;
$b_yyyymm = $yyyymm - 100;
$p1_ki   = 21

///// 前期末 年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // 前期末年月

///// 期・半期の取得
$tuki_chk = 12;
if ($tuki_chk == 3) {
    $hanki = '４';
} elseif ($tuki_chk == 6) {
    $hanki = '１';
} elseif ($tuki_chk == 9) {
    $hanki = '２';
} elseif ($tuki_chk == 12) {
    $hanki = '３';
}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if ($tuki_chk == 3) {
    $menu->set_title("第 {$ki} 期　本決算　貸　借　対　照　表");
} else {
    $menu->set_title("第 {$ki} 期　第{$hanki}四半期　貸　借　対　照　表");
}

///// データ取得
///////// 項目とインデックスの関連付け
    $item = array();
    $item[0]   = "現金及び預金";
    $item[1]   = "売掛金";
    $item[2]   = "棚卸資産";
    $item[3]   = "前払費用";
    $item[4]   = "流動繰延税金資産";
    $item[5]   = "短期貸付金";
    $item[6]   = "未収入金";
    $item[7]   = "未収消費税等";
    $item[8]   = "未収法人税等";
    $item[9]   = "立替金";
    $item[10]  = "仮払消費税等";
    $item[11]  = "仮払金";
    $item[12]  = "その他流動資産";
    $item[13]  = "流動貸倒引当金";
    $item[14]  = "流動資産計";
    $item[15]  = "有形固定資産";
    $item[16]  = "建設仮勘定";
    $item[17]  = "減価償却累計額";
    $item[18]  = "有形固定資産計";
    $item[19]  = "ソフトウェア";
    $item[20]  = "電話加入権";
    $item[21]  = "施設利用権";
    $item[22]  = "無形固定資産計";
    $item[23]  = "固定資産計";
    $item[24]  = "長期貸付金";
    $item[25]  = "長期前払費用";
    $item[26]  = "固定繰延税金資産";
    $item[27]  = "差入敷金保証金";
    $item[28]  = "その他の投資等";
    $item[29]  = "固定貸倒引当金";
    $item[30]  = "投資その他の資産計";
    $item[31]  = "資産の部合計";
    $item[32]  = "支払手形";
    $item[33]  = "買掛金";
    $item[34]  = "短期借入金";
    $item[35]  = "リース債務(短期)";
    $item[36]  = "未払金";
    $item[37]  = "未払消費税";
    $item[38]  = "未払法人税等";
    $item[39]  = "未払費用";
    $item[40]  = "預り金";
    $item[41]  = "仮受消費税等";
    $item[42]  = "その他の流動負債";
    $item[43]  = "賞与引当金";
    $item[44]  = "流動負債計";
    $item[45]  = "長期借入金";
    $item[46]  = "リース債務(長期)";
    $item[47]  = "長期未払金";
    $item[48]  = "退職給付引当金";
    $item[49]  = "その他の固定負債";
    $item[50]  = "固定負債計";
    $item[51]  = "負債の部合計";
    $item[52]  = "資本金";
    $item[53]  = "資本金計";
    $item[54]  = "資本準備金";
    $item[55]  = "その他資本剰余金";
    $item[56]  = "資本剰余金計";
    $item[57]  = "利益準備金";
    $item[58]  = "その他利益剰余金";
    $item[59]  = "繰越利益剰余金";
    $item[60]  = "利益剰余金計";
    $item[61]  = "当期純利益";
    $item[62]  = "純資産の部合計";
    $item[63]  = "負債及び純資産の部";
    $item[64]  = "未収収益";
    $item[65]  = "前払消費税";

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>

<style type='text/css'>
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
.pt8 {
    font:normal 8pt;
    font-family: monospace;
}
.pt10 {
    font:normal 10pt;
    font-family: monospace;
}
.pt10b {
    font:bold 10pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
.title_font {
    font:bold 13.5pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.corporate_name {
    font:bold 10pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
.OnOff_font {
    font-size:     8.5pt;
    font-family:   monospace;
}
-->
</style>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <table width='100%' border='1' cellspacing='1' cellpadding='1'>
            <tr>
                <td colspan='3' bgcolor='#d6d3ce' align='center' class='corporate_name'>
                    <?=$menu->out_caption(), "\n"?>
                </td>
                <form method='post' action='<?php echo $menu->out_self() ?>'>
                    <td colspan='15' bgcolor='#d6d3ce' align='right' class='pt10'>
                        単位
                        <select name='taisyaku_tani' class='pt10'>
                        <?php
                            if ($tani == 1000)
                                echo "<option value='1000' selected>　千円</option>\n";
                            else
                                echo "<option value='1000'>　千円</option>\n";
                            if ($tani == 1)
                                echo "<option value='1' selected>　　円</option>\n";
                            else
                                echo "<option value='1'>　　円</option>\n";
                            if ($tani == 1000000)
                                echo "<option value='1000000' selected>百万円</option>\n";
                            else
                                echo "<option value='1000000'>百万円</option>\n";
                            if ($tani == 10000)
                                echo "<option value='10000' selected>　万円</option>\n";
                            else
                                echo "<option value='10000'>　万円</option>\n";
                            if($tani == 100000)
                                echo "<option value='100000' selected>十万円</option>\n";
                            else
                                echo "<option value='100000'>十万円</option>\n";
                        ?>
                        </select>
                        少数桁
                        <select name='keihi_keta' class='pt10'>
                        <?php
                            if ($keta == 0)
                                echo "<option value='0' selected>０桁</option>\n";
                            else
                                echo "<option value='0'>０桁</option>\n";
                            if ($keta == 3)
                                echo "<option value='3' selected>３桁</option>\n";
                            else
                                echo "<option value='3'>３桁</option>\n";
                            if ($keta == 6)
                                echo "<option value='6' selected>６桁</option>\n";
                            else
                                echo "<option value='6'>６桁</option>\n";
                            if ($keta == 1)
                                echo "<option value='1' selected>１桁</option>\n";
                            else
                                echo "<option value='1'>１桁</option>\n";
                            if ($keta == 2)
                                echo "<option value='2' selected>２桁</option>\n";
                            else
                                echo "<option value='2'>２桁</option>\n";
                            if ($keta == 4)
                                echo "<option value='4' selected>４桁</option>\n";
                            else
                                echo "<option value='4'>４桁</option>\n";
                            if ($keta == 5)
                                echo "<option value='5' selected>５桁</option>\n";
                            else
                                echo "<option value='5'>５桁</option>\n";
                        ?>
                        </select>
                        <input class='pt10b' type='submit' name='return' value='単位変更'>
                    </td>
                </form>
            </tr>
        </table>
        <?php
            //  bgcolor='#ceffce' 黄緑
            //  bgcolor='#ffffc6' 薄い黄色
            //  bgcolor='#d6d3ce' Win グレイ
        ?>
    <table width='81%' bgcolor='#d6d3ce' align='left' cellspacing="0" cellpadding="3" border='1'>
        <tr>
        <td>
        <table width='50%' bgcolor='#d6d3ce' align='left' cellspacing="0" cellpadding="3" border='1'>
            <TBODY>
                <tr>
                    <td colspan='3' width='200' align='center' class='pt10b' bgcolor='#ceffce'>科　　　目</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>第<?php echo $p1_ki ?>期</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>第<?php echo $ki ?>期</td>
                    <td nowrap align='center' class='pt8'   bgcolor='#ceffce'>前期比増減</td>
                </tr>
                <tr>
                    <td rowspan='35' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce' style='border-right-style:none;'>資産の部</td>
                    <td rowspan='18' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>流動資産</td>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　現金及び預金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　売　 掛 　金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　棚 卸 資 産</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　前 払 費 用</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　繰延税金資産</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　未 収 収 益</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　短期貸付金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td> <!-- $view_data[5][1] -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　未 収 入 金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　未収消費税等</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td> <!-- 余白 -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　未収法人税等</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　立　 替 　金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　仮払消費税等</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　前払消費税</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　仮 　払　 金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　その他流動資産</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　貸倒引当金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td> <!-- $view_data[13][1] -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td> <!-- 余白 -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>計</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td rowspan='9' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>固定資産</td>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　有形固定資産</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　建設仮勘定</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　減価償却累計額</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>有形固定資産 計</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　ソフトウェア</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　電話加入権</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　施設利用権</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>無形固定資産 計</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>計</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td rowspan='7' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>投資その他の資産</td>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　長期貸付金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　長期前払費用</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　繰延税金資産</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　差入敷金保証金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　その他の投資等</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　貸倒引当金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td> <!-- $view_data[29][1] -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>計</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td colspan='2' align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>資産の部 合計</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo 400 ?></td>
                </tr>
            </TBODY>
        </table>
        </td>
        <td>
        <table width='50%' bgcolor='#d6d3ce' align='right' cellspacing="0" cellpadding="3" border='1'>
            <TBODY>
                <tr>
                    <td colspan='3' width='200' align='center' class='pt10b' bgcolor='#ceffce'>科　　　目</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>第<?php echo $p1_ki ?>期</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>第<?php echo $ki ?>期</td>
                    <td nowrap align='center' class='pt8'   bgcolor='#ceffce'>前期比増減</td>
                </tr>
                <tr>
                    <td rowspan='21' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce' style='border-right-style:none;'>負債の部</td>
                    <td rowspan='14' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>流動負債</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>支　払　手　形</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>買　　掛　　金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>短 期 借 入 金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>リース債務(短期)</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>未　　払　　金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>未 払 消 費 税</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>未払法人税等</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>未　払　費　用</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>預　　り　　金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>仮受消費税等</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>その他の流動負債</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>賞 与 引 当 金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>　</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>計</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>固定負債</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>長 期 借 入 金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>リース債務(長期)</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>長 期 未 払 金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>退職給付引当金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>その他の固定負債</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td> <!-- $view_data[49][1] -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>計</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td colspan='2' align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>負債の部 合計</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td rowspan='13' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce' style='border-right-style:none;'>純資産の部</td><!--資本の部-->
                    <td rowspan='3'  width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>資本金</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>資　　本　　金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>　</td> <!-- 余白 -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>資本金 計</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td rowspan='4' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>資本剰余金</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>資 本 準 備 金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>その他資本剰余金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>　</td> <!-- 余白 -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>資本剰余金 計</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td rowspan='4' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>利益剰余金</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>利 益 準 備 金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td> <!-- $view_data[57][1] -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>　</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>その他利益剰余金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>繰越利益剰余金</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>利益剰余金 計</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>当 期 純 利 益</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td colspan='2' align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>純資産の部 合計</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo 400 ?></td>
                </tr>
                <tr>
                    <td colspan='3' align='center' class='pt10b' bgcolor='#ceffce'>負債及び純資産の部</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo 400 ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo 400 ?></td>
                </tr>
            </TBODY>
        </table>
        </td>
        </tr>
    </table>
    </center>
</body>
</html>
