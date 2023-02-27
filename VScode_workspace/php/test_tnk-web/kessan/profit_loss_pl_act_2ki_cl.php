<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 ２期比較表 ＣＬＴ・商品管理・試験修理 損益計算書            //
// Copyright (C) 2012-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2012/01/16 Created   profit_loss_pl_act_2ki_cl.php                       //
// 2012/01/17 プログラムの完成 チェック済 稼動                              //
// 2012/01/20 プログラムの桁数を揃えた                                      //
// 2012/01/26 コメントの整理                                                //
// 2012/01/26 Excelの２期比較表にあわせて色を調整した                       //
// 2012/02/13 商管の11期で０割エラー発生の為対応                            //
// 2012/04/18 第４四半期のみ表示形式が違っていたのに対応                    //
// 2015/09/28 機工損益追加                                                  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);    // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);       // E_ALL='2047' debug 用
// ini_set('display_errors', '1');          // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
   // 実際の認証はprofit_loss_submit.phpで行っているaccount_group_check()を使用

///// サイト設定
// $menu->set_site(10, 7);                  // site_index=10(損益メニュー) site_id=7(月次損益)
///// 表題の設定
$menu->set_caption('栃木日東工器(株)');

///// 対象当月
$yyyymm = 202211;
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// 対象前々月
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}

///// 対象当月
$yyyymm   = 202211;
$ki       = 21;
$b_yyyymm = $yyyymm - 100;
$p1_ki   = 21
///// 期初年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym   = $yyyy . "04";   // 当期 期初年月
$b_str_ym = $str_ym - 100;  // 前期 期初年月

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
///// タイトル名(ソースのタイトル名とフォームのタイトル名)
if ($tuki_chk == 3) {
    $menu->set_title("第 {$ki} 期　本決算　ＣＬ商品別損益 前期比較表");
} else {
    $menu->set_title("第 {$ki} 期　第{$hanki}四半期　ＣＬ商品別損益 前期比較表");
}


/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>
<script type=text/javascript language='JavaScript'>
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
function isDigitcho(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((i == 0) && (c == "-")) {
            return true;
        }
        if (("0" > c) || (c > "9")) {
            alert("数値以外は入力出来ません。");
            return false;
        }
    }
    return true;
}
/* 初期入力エレメントへフォーカスさせる */
function set_focus(){
    document.jin.jin_1.focus();
    document.jin.jin_1.select();
}
function data_input_click(obj) {
    return confirm("当月のデータを登録します。\n既にデータがある場合は上書きされます。");
}
// -->
</script>
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
    font: normal 10pt;
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
ol {
    line-height: normal;
}
pre {
    font-size: 10.0pt;
    font-family: monospace;
}
.OnOff_font {
    font-size:     8.5pt;
    font-family:   monospace;
}
-->
</style>
</head>
<!--  style='overflow-y:hidden;' -->
<body>
    <center>
<?= $menu->out_title_border() ?>
        <table width='100%' border='1' cellspacing='1' cellpadding='1'>
            <tr>
                <td colspan='2' bgcolor='#d6d3ce' align='center' class='corporate_name'>
                    <?=$menu->out_caption(), "\n"?>
                </td>
                <form method='post' action='<?php echo $menu->out_self() ?>'>
                    <td colspan='14' bgcolor='#d6d3ce' align='right' class='pt10'>
                        単位
                        <select name='keihi_tani' class='pt10'>
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
    <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
        <tr>
        <td>
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <TBODY>
                <tr>
                    <td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='white'>項　　　目</td>
                    <td colspan='5' align='center' class='pt10b' bgcolor='white'>カ　プ　ラ</td>
                    <td colspan='5' align='center' class='pt10b' bgcolor='white'>リ　ニ　ア</td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td colspan='5' align='center' class='pt10b' bgcolor='white'>ツ　ー　ル</td>
                    <?php 
                    }
                    ?>
                    <td colspan='5' align='center' class='pt10b' bgcolor='white'>試験・修理</td>
                    <td colspan='5' align='center' class='pt10b' bgcolor='white'>商品管理</td>
                    <td colspan='5' align='center' class='pt10b' bgcolor='white'>合　　　計</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>第<?php echo $p1_ki ?>期</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>構成比</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>第<?php echo $ki ?>期</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>構成比</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>前期比増減</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>第<?php echo $p1_ki ?>期</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>構成比</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>第<?php echo $ki ?>期</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>構成比</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>前期比増減</td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>第<?php echo $p1_ki ?>期</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>構成比</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>第<?php echo $ki ?>期</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>構成比</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>前期比増減</td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>第<?php echo $p1_ki ?>期</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>構成比</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>第<?php echo $ki ?>期</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>構成比</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>前期比増減</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>第<?php echo $p1_ki ?>期</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>構成比</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>第<?php echo $ki ?>期</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>構成比</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>前期比増減</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>第<?php echo $p1_ki ?>期</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>構成比</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>第<?php echo $ki ?>期</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>構成比</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>前期比増減</td>
                </tr>
                <tr>
                    <td rowspan='11' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営　業　損　益</td>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　高</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffff96' style='border-right-style:none;'>売上原価</td> <!-- 売上原価 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　期首材料仕掛品棚卸高</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　材料費(仕入高)</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　労　　務　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　経　　　　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　期末材料仕掛品棚卸高</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffff96' style='border-left-style:none;'>　売　上　原　価</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　総　利　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffff96' style='border-right-style:none;'></td> <!-- 販管費 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　人　　件　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　経　　　　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffff96' style='border-left-style:none;'>販管費及び一般管理費計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>営　　業　　利　　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営業外損益</td>
                    <td rowspan='4' align='center' class='pt10' bgcolor='#ffff96' style='border-right-style:none;'></td> <!-- 余白 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　業務委託収入</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　仕　入　割　引</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffff96' style='border-left-style:none;'>　営業外収益 計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td rowspan='3' align='center' class='pt10' bgcolor='#ffff96' style='border-right-style:none;'></td> <!-- 余白 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　支　払　利　息</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffff96' style='border-left-style:none;'>　営業外費用 計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffff96'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>経　　常　　利　　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <?php 
                    if ($yyyymm >= 201504) {
                    ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <?php 
                    }
                    ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td rowspan='2' colspan='2' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>特別損益</td>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　特　別　利　益</td>
                    <td colspan='23' rowspan='2' bgcolor='white' nowrap align='center' class='pt10b'>　</td>
                    <td colspan='2' bgcolor='white' nowrap align='right' class='pt10b'>特別利益</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　特　別　損　失</td>
                    <td colspan='2' bgcolor='white' nowrap align='right' class='pt10b'>特別損失</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 500 ?></td>
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>税引前当期利益金額</td>
                    <td colspan='23' nowrap align='center' class='pt10b' bgcolor='#ceffce'>　</td>
                    <td colspan='2' bgcolor='#ceffce' nowrap align='right' class='pt10b'>税引前当期利益金額</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 500 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>
                    </td>
                </tr>
            </TBODY>
        </table>
        </td>
        </tr>
    </table>
    </center>
</body>
</html>
