<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 ２期比較表 本決算損益表 予算無しVer(Webにデータがない為)    //
// Copyright (C) 2012-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2012/01/17 Created   profit_loss_pl_act_2ki.php                          //
// 2012/01/20 プログラムの完成 チェック済 稼動                              //
// 2012/02/13 第４四半期のみ表示形式が違っていたのに対応                    //
// 2012/04/18 第４四半期のみ表示形式が違っていたのに対応（２回目）          //
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

$menu->set_title("第 11 期　第4 四半期　予　算　実　績　比　較　表");

$tuki_chk = 3;

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
                    <td rowspan='3' colspan='2' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>項　　　目</td>
                    <?php if($tuki_chk==3) { ?>
                        <td colspan='5' rowspan='2' align='center' class='pt10b' bgcolor='#ffffc6'>四　半　期　損　益<BR>（<?php echo 2022 ?>/04～<?php echo 2022 ?>/<?php echo 11 ?>）</td>
                    <?php } else { ?>
                        <td colspan='4' rowspan='2' align='center' class='pt10b' bgcolor='#ffffc6'>第　<?php echo 4 ?>　四　半　期　損　益<BR>（<?php echo 22 ?>/<?php echo 10 ?>～<?php echo 22 ?>/<?php echo 12 ?>）</td>
                    <?php } ?>
                    <?php if($tuki_chk==3) { ?>
                        <td colspan='4' rowspan='2' align='center' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>第　<?php echo 4 ?>　期　損　益　累　計<BR>（<?php echo 22 ?>/04～<?php echo 22 ?>/<?php echo 11 ?>）</td>
                    <?php } else { ?>
                        <td colspan='4' rowspan='2' align='center' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>第<?php echo 4 ?>四半期までの累計<BR>（<?php echo 22 ?>/04～<?php echo 22 ?>/<?php echo 11 ?>）</td>
                    <?php } ?>
                    <td colspan='3' align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>　</td>
                </tr>
                <tr>
                    <?php if($tuki_chk==3) { ?>
                        <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>前期比較（<?php echo 22 ?>/04～<?php echo 22 ?>/<?php echo 11 ?>）</td>
                    <?php } else { ?>
                        <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>前期比較（<?php echo 22 ?>/04～<?php echo 22 ?>/<?php echo 11 ?>）</td>
                    <?php } ?>
                </tr>
                <tr>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>第１四半期</td>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>第２四半期</td>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>第３四半期</td>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>第４四半期</td>
                    <?php } else { ?>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>22/09月</td>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo 22 ?>/<?php echo 10 ?>月</td>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo 22 ?>/<?php echo 11 ?>月</td>
                    <?php } ?>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'>予　　算</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>実　　績</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'>予算差異</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'>達成率</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>前期実績</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>増減額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>増減率</td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　高</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none;'>　</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>　売　上　原　価</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-top-style:none; border-right-style:none;'>　</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>売　上　総　利　益</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none;'>　</td>
                    <td nowrap align='center' class='pt10' bgcolor='white'>販管費及び一般管理費計</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-top-style:none; border-right-style:none;'>　</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>営　業　利　益</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none;'>　</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>　営業外収益 計</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none; border-top-style:none;'>　</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>　営業外費用 計</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-top-style:none; border-right-style:none;'>　</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>経　常　利　益</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none;'>　</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>　特　別　利　益</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='center' class='pt10' bgcolor='white'>―</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none; border-top-style:none;'>　</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>　特　別　損　失</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='center' class='pt10' bgcolor='white'>―</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-top-style:none; border-right-style:none;'>　</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>税引前当期利益金額</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none;'>　</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>　法人税、事業税</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 100 ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-top-style:none; border-right-style:none;'>　</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>当　期　利　益</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 300 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo 100 ?>%</td>
                </tr>
            </TBODY>
        </table>
        </td>
        </tr>
    </table>
    </center>
</body>
</html>
