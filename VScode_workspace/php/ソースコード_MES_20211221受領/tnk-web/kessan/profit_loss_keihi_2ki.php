<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 月次 経費実績内訳表 ２期比較表                              //
// Copyright(C) 2012-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2012/01/26 Created   profit_loss_keihi_2ki.php                           //
// 2012/04/18 第４四半期のみ表示形式が違っていたのに対応                    //
// 2015/02/20 クレーム対応費追加のため 科目を追加                           //
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

$url_referer     = $_SESSION['pl_referer'];     // 呼出もとの URL を取得

///// 対象当月
$yyyymm = $_SESSION['2ki_ym'];
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
$yyyymm   = $_SESSION['2ki_ym'];
$ki       = Ym_to_tnk($_SESSION['2ki_ym']);
$b_yyyymm = $yyyymm - 100;
$p1_ki    = Ym_to_tnk($b_yyyymm);
///// 期初年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym   = $yyyy . "04";   // 当期 期初年月
$b_str_ym = $str_ym - 100;  // 前期 期初年月

///// 期・半期の取得
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
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
    $menu->set_title("第 {$ki} 期　本決算　経 費 実 績 内 訳 表");
} else {
    $menu->set_title("第 {$ki} 期　第{$hanki}四半期　経 費 実 績 内 訳 表");
}

///// 表示単位を設定取得
if (isset($_POST['keihi_tani'])) {
    $_SESSION['keihi_tani'] = $_POST['keihi_tani'];
    $tani = $_SESSION['keihi_tani'];
} elseif (isset($_SESSION['keihi_tani'])) {
    $tani = $_SESSION['keihi_tani'];
} else {
    $tani = 1000;        // 初期値 表示単位 千円
    $_SESSION['keihi_tani'] = $tani;
}
///// 表示 小数部桁数 設定取得
if (isset($_POST['keihi_keta'])) {
    $_SESSION['keihi_keta'] = $_POST['keihi_keta'];
    $keta = $_SESSION['keihi_keta'];
} elseif (isset($_SESSION['keihi_keta'])) {
    $keta = $_SESSION['keihi_keta'];
} else {
    $keta = 0;          // 初期値 小数点以下桁数
    $_SESSION['keihi_keta'] = $keta;
}

///////// 項目とインデックスの関連付け
$item = array();
$item[0]   = "役員報酬";
$item[1]   = "給料手当";
$item[2]   = "賞与手当";
$item[3]   = "顧問料";
$item[4]   = "法定福利費";
$item[5]   = "厚生福利費";
$item[6]   = "賞与引当金繰入";
$item[7]   = "退職給付費用";
$item[8]   = "人件費計";
$item[9]   = "旅費交通費";
$item[10]  = "海外出張";
$item[11]  = "通信費";
$item[12]  = "会議費";
$item[13]  = "交際接待費";
$item[14]  = "広告宣伝費";
$item[15]  = "求人費";
$item[16]  = "運賃荷造費";
$item[17]  = "図書教育費";
$item[18]  = "業務委託費";
$item[19]  = "事業等";
$item[20]  = "諸税公課";
$item[21]  = "試験研究費";
$item[22]  = "雑費";
$item[23]  = "修繕費";
$item[24]  = "保証修理費";
$item[25]  = "事務用消耗品費";
$item[26]  = "工場消耗品費";
$item[27]  = "車両費";
$item[28]  = "保険料";
$item[29]  = "水道光熱費";
$item[30]  = "諸会費";
$item[31]  = "支払手数料";
$item[32]  = "地代家賃";
$item[33]  = "寄付金";
$item[34]  = "倉敷料";
$item[35]  = "賃借料";
$item[36]  = "減価償却費";
$item[37]  = "クレーム対応費";
$item[38]  = "経費計";
$item[39]  = "合計";

for ($i = 0; $i < 40; $i++) {
    $head  = "製造経費";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select sum(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $b_str_ym, $b_yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][1]) < 1) {
        $res_in[$i][1] = 0;                 // 検索失敗
    }
    $query = sprintf("select sum(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][2]) < 1) {
        $res_in[$i][2] = 0;                 // 検索失敗
    }
    $res_in[$i][3] = $res_in[$i][2] - $res_in[$i][1];
    
    $head  = "販管費";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select sum(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $b_str_ym, $b_yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][4]) < 1) {
        $res_in[$i][4] = 0;                 // 検索失敗
    }
    $query = sprintf("select sum(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][5]) < 1) {
        $res_in[$i][5] = 0;                 // 検索失敗
    }
    $res_in[$i][6] = $res_in[$i][5] - $res_in[$i][4];
    
    $head  = "合計";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select sum(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $b_str_ym, $b_yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][7]) < 1) {
        $res_in[$i][7] = 0;                 // 検索失敗
    }
    $query = sprintf("select sum(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][8]) < 1) {
        $res_in[$i][8] = 0;                 // 検索失敗
    }
    $res_in[$i][9] = $res_in[$i][8] - $res_in[$i][7];
    
    $view_data[$i][1] = number_format(($res_in[$i][1] / $tani), $keta);
    $view_data[$i][2] = number_format(($res_in[$i][2] / $tani), $keta);
    $view_data[$i][3] = number_format(($res_in[$i][3] / $tani), $keta);
    $view_data[$i][4] = number_format(($res_in[$i][4] / $tani), $keta);
    $view_data[$i][5] = number_format(($res_in[$i][5] / $tani), $keta);
    $view_data[$i][6] = number_format(($res_in[$i][6] / $tani), $keta);
    $view_data[$i][7] = number_format(($res_in[$i][7] / $tani), $keta);
    $view_data[$i][8] = number_format(($res_in[$i][8] / $tani), $keta);
    $view_data[$i][9] = number_format(($res_in[$i][9] / $tani), $keta);
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
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
.pt9 {
    font:normal 9pt;
    font-family: monospace;
}
.pt10 {
    font:normal 10pt;
    font-family: monospace;
}
.pt8b {
    font:bold 8pt;
    font-family: monospace;
}
.pt9b {
    font:bold 9pt;
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
                <td colspan='2' bgcolor='#d6d3ce' align='center' class='corporate_name'>
                    <?=$menu->out_caption(), "\n"?>
                </td>
                <form method='post' action='<?php echo $menu->out_self() ?>'>
                    <td colspan='13' bgcolor='#d6d3ce' align='right' class='pt10'>
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
            <TBODY>
                <TR>
                    <TD rowspan="2" align="center" width='10' class='pt10b' bgcolor='#ceffce'>区分</TD>
                    <TD rowspan="2" align="center" nowrap class='pt10b' bgcolor='#ceffce'>勘定科目</TD>
                    <TD colspan="3" align="center" height="20" class='pt10b' bgcolor='#ceffce'>製 造 費</TD>
                    <TD colspan="3" align="center" height="20" class='pt10b' bgcolor='#ceffce'>販 管 費</TD>
                    <TD colspan="3" align="center" height="20" class='pt10b' bgcolor='#ceffce'>合　　計</TD>
                </TR>
                <TR>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ceffce'>第<?php echo $p1_ki ?>期</TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ceffce'>第<?php echo $ki ?>期</TD>
                    <TD align="center" nowrap height="20" class='pt9b' bgcolor='#ceffce'>前期比増減</TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ceffce'>第<?php echo $p1_ki ?>期</TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ceffce'>第<?php echo $ki ?>期</TD>
                    <TD align="center" nowrap height="20" class='pt9b' bgcolor='#ceffce'>前期比増減</TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ceffce'>第<?php echo $p1_ki ?>期</TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ceffce'>第<?php echo $ki ?>期</TD>
                    <TD align="center" nowrap height="20" class='pt9b' bgcolor='#ceffce'>前期比増減</TD>
                </TR>
                <TR>
                    <TD rowspan="9" align="center" width='10' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>人件費</TD>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>役員報酬</TD>
                    <?php
                        $r = 0;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>給料手当</TD>
                    <?php
                        $r = 1;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>賞与手当</TD>
                    <?php
                        $r = 2;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>顧問料</TD>
                    <?php
                        $r = 3;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>法定福利費</TD>
                    <?php
                        $r = 4;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>厚生福利費</TD>
                    <?php
                        $r = 5;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>賞与引当金繰入</TD>
                    <?php
                        $r = 6;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>退職給付費用</TD>
                    <?php
                        $r = 7;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right' style='border-left-style:none;'>人件費計</TD>
                    <?php
                        $r = 8;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <tr>
                    <TD rowspan="30" align="center" width='10' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>経費</TD>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>旅費交通費</TD>
                    <?php
                        $r = 9;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>海外出張</TD>
                    <?php
                        $r = 10;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>通　信　費</TD>
                    <?php
                        $r = 11;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>会　議　費</TD>
                    <?php
                        $r = 12;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>交際接待費</TD>
                    <?php
                        $r = 13;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>広告宣伝費</TD>
                    <?php
                        $r = 14;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>求　人　費</TD>
                    <?php
                        $r = 15;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>運賃荷造費</TD>
                    <?php
                        $r = 16;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>図書教育費</TD>
                    <?php
                        $r = 17;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>業務委託費</TD>
                    <?php
                        $r = 18;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>事　業　等</TD>
                    <?php
                        $r = 19;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>諸税公課</TD>
                    <?php
                        $r = 20;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>試験研究費</TD>
                    <?php
                        $r = 21;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>雑　　　費</TD>
                    <?php
                        $r = 22;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>修　繕　費</TD>
                    <?php
                        $r = 23;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>保証修理費</TD>
                    <?php
                        $r = 24;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>事務用消耗品費</TD>
                    <?php
                        $r = 25;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>工場消耗品費</TD>
                    <?php
                        $r = 26;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>車　両　費</TD>
                    <?php
                        $r = 27;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>保　険　料</TD>
                    <?php
                        $r = 28;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>水道光熱費</TD>
                    <?php
                        $r = 29;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>諸　会　費</TD>
                    <?php
                        $r = 30;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>支払手数料</TD>
                    <?php
                        $r = 31;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>地代家賃</TD>
                    <?php
                        $r = 32;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>寄　付　金</TD>
                    <?php
                        $r = 33;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>倉　敷　料</TD>
                    <?php
                        $r = 34;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>賃　借　料</TD>
                    <?php
                        $r = 35;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>減価償却費</TD>
                    <?php
                        $r = 36;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>クレーム対応費</TD>
                    <?php
                        $r = 37;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // 増減
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right' style='border-left-style:none;'>経費計</TD>
                    <?php
                        $r = 38;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD colspan='2' nowrap class='pt10b' align='right'>合　計</TD>
                    <?php
                        $r = 39;     // 該当レコード
                        for ($c=1;$c<10;$c++) {
                            printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
            </TBODY>
        </table>
    </center>
</body>
</html>
