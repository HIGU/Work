<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 月次 ＣＬ経費 比較表                                        //
// Copyright(C) 2008-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2008/10/07 Created                                                       //
//            profit_loss_cl_keihi_compare.php(profit_loss_cl_keihi.phpより)//
// 2010/01/20 経費合計の差額比較を追加                                      //
// 2010/02/08 商管の差額比較を追加、試修の人件費調整をマスターから加味      //
// 2012/02/08 2012年1月 業務委託費 調整 リニア製造経費 +1,156,130円    大谷 //
//             ※ 平出横川派遣料 2月に逆調整を行うこと                      //
// 2012/02/13 データ取得を都度計算ではなく、履歴から取得に変更         大谷 //
//             ※ 上記逆調整は必要なし                                      //
// 2015/04/10 クレーム対応費の追加に対応                               大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 
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

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 {$ki} 期　{$tuki} 月度　Ｃ Ｌ 経 費 差 額 比 較 表");

///// 対象当月
$yyyymm = $_SESSION['pl_ym'];
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
    $head  = "カプラ製造経費";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][1]) < 1) {
        $res_in[$i][1] = 0;                 // 検索失敗
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][2]) < 1) {
        $res_in[$i][2] = 0;                 // 検索失敗
    }
    $res_in[$i][3] = $res_in[$i][2] - $res_in[$i][1];
    
    $head  = "リニア製造経費";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][4]) < 1) {
        $res_in[$i][4] = 0;                 // 検索失敗
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][5]) < 1) {
        $res_in[$i][5] = 0;                 // 検索失敗
    }
    $res_in[$i][6] = $res_in[$i][5] - $res_in[$i][4];
    
    $head  = "商管製造経費";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][7]) < 1) {
        $res_in[$i][7] = 0;                 // 検索失敗
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][8]) < 1) {
        $res_in[$i][8] = 0;                 // 検索失敗
    }
    $res_in[$i][9] = $res_in[$i][8] - $res_in[$i][7];
    
    // 製造経費合計計算
    $res_in[$i][10] = $res_in[$i][1] + $res_in[$i][4] + $res_in[$i][7];
    $res_in[$i][11] = $res_in[$i][2] + $res_in[$i][5] + $res_in[$i][8];
    $res_in[$i][12] = $res_in[$i][3] + $res_in[$i][6] + $res_in[$i][9];
    
    $head  = "カプラ販管費";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][13]) < 1) {
        $res_in[$i][13] = 0;                 // 検索失敗
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][14]) < 1) {
        $res_in[$i][14] = 0;                 // 検索失敗
    }
    $res_in[$i][15] = $res_in[$i][14] - $res_in[$i][13];
    
    $head  = "リニア販管費";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][16]) < 1) {
        $res_in[$i][16] = 0;                 // 検索失敗
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][17]) < 1) {
        $res_in[$i][17] = 0;                 // 検索失敗
    }
    $res_in[$i][18] = $res_in[$i][17] - $res_in[$i][16];
    
    $head  = "商管販管費";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][19]) < 1) {
        $res_in[$i][19] = 0;                 // 検索失敗
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][20]) < 1) {
        $res_in[$i][20] = 0;                 // 検索失敗
    }
    $res_in[$i][21] = $res_in[$i][20] - $res_in[$i][19];
    
    // 販管費合計計算
    $res_in[$i][22] = $res_in[$i][13] + $res_in[$i][16] + $res_in[$i][19];
    $res_in[$i][23] = $res_in[$i][14] + $res_in[$i][17] + $res_in[$i][20];
    $res_in[$i][24] = $res_in[$i][15] + $res_in[$i][18] + $res_in[$i][21];
    
    // カプラ経費合計計算
    $res_in[$i][25] = $res_in[$i][1] + $res_in[$i][13];
    $res_in[$i][26] = $res_in[$i][2] + $res_in[$i][14];
    $res_in[$i][27] = $res_in[$i][3] + $res_in[$i][15];
    
    // リニア経費合計計算
    $res_in[$i][28] = $res_in[$i][4] + $res_in[$i][16];
    $res_in[$i][29] = $res_in[$i][5] + $res_in[$i][17];
    $res_in[$i][30] = $res_in[$i][6] + $res_in[$i][18];
    
    // 商管経費合計計算
    $res_in[$i][31] = $res_in[$i][7] + $res_in[$i][19];
    $res_in[$i][32] = $res_in[$i][8] + $res_in[$i][20];
    $res_in[$i][33] = $res_in[$i][9] + $res_in[$i][21];
    
    // 経費総合計計算
    $res_in[$i][34] = $res_in[$i][25] + $res_in[$i][28] + $res_in[$i][31];
    $res_in[$i][35] = $res_in[$i][26] + $res_in[$i][29] + $res_in[$i][32];
    $res_in[$i][36] = $res_in[$i][27] + $res_in[$i][30] + $res_in[$i][33];
    
    $view_data[$i][1]  = number_format(($res_in[$i][1] / $tani), $keta);
    $view_data[$i][2]  = number_format(($res_in[$i][2] / $tani), $keta);
    $view_data[$i][3]  = number_format(($res_in[$i][3] / $tani), $keta);
    $view_data[$i][4]  = number_format(($res_in[$i][4] / $tani), $keta);
    $view_data[$i][5]  = number_format(($res_in[$i][5] / $tani), $keta);
    $view_data[$i][6]  = number_format(($res_in[$i][6] / $tani), $keta);
    $view_data[$i][7]  = number_format(($res_in[$i][7] / $tani), $keta);
    $view_data[$i][8]  = number_format(($res_in[$i][8] / $tani), $keta);
    $view_data[$i][9]  = number_format(($res_in[$i][9] / $tani), $keta);
    $view_data[$i][10] = number_format(($res_in[$i][10] / $tani), $keta);
    $view_data[$i][11] = number_format(($res_in[$i][11] / $tani), $keta);
    $view_data[$i][12] = number_format(($res_in[$i][12] / $tani), $keta);
    $view_data[$i][13] = number_format(($res_in[$i][13] / $tani), $keta);
    $view_data[$i][14] = number_format(($res_in[$i][14] / $tani), $keta);
    $view_data[$i][15] = number_format(($res_in[$i][15] / $tani), $keta);
    $view_data[$i][16] = number_format(($res_in[$i][16] / $tani), $keta);
    $view_data[$i][17] = number_format(($res_in[$i][17] / $tani), $keta);
    $view_data[$i][18] = number_format(($res_in[$i][18] / $tani), $keta);
    $view_data[$i][19] = number_format(($res_in[$i][19] / $tani), $keta);
    $view_data[$i][20] = number_format(($res_in[$i][20] / $tani), $keta);
    $view_data[$i][21] = number_format(($res_in[$i][21] / $tani), $keta);
    $view_data[$i][22] = number_format(($res_in[$i][22] / $tani), $keta);
    $view_data[$i][23] = number_format(($res_in[$i][23] / $tani), $keta);
    $view_data[$i][24] = number_format(($res_in[$i][24] / $tani), $keta);
    $view_data[$i][25] = number_format(($res_in[$i][25] / $tani), $keta);
    $view_data[$i][26] = number_format(($res_in[$i][26] / $tani), $keta);
    $view_data[$i][27] = number_format(($res_in[$i][27] / $tani), $keta);
    $view_data[$i][28] = number_format(($res_in[$i][28] / $tani), $keta);
    $view_data[$i][29] = number_format(($res_in[$i][29] / $tani), $keta);
    $view_data[$i][30] = number_format(($res_in[$i][30] / $tani), $keta);
    $view_data[$i][31] = number_format(($res_in[$i][31] / $tani), $keta);
    $view_data[$i][32] = number_format(($res_in[$i][32] / $tani), $keta);
    $view_data[$i][33] = number_format(($res_in[$i][33] / $tani), $keta);
    $view_data[$i][34] = number_format(($res_in[$i][34] / $tani), $keta);
    $view_data[$i][35] = number_format(($res_in[$i][35] / $tani), $keta);
    $view_data[$i][36] = number_format(($res_in[$i][36] / $tani), $keta);
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
        <!-- win_gray='#d6d3ce' -->
        <table width='100%' bgcolor='white' align='center' cellspacing="0" cellpadding="3" border='1'>
            <TBODY>
                <tr>
                    <td width='10' rowspan='3' align='center' class='pt10' bgcolor='#ccffff'>区分</td>
                    <td rowspan='3' nowrap align='center' class='pt10b' bgcolor='#ccffff'>勘定科目</td>
                    <td colspan='12' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>製　造　経　費</td>
                    <td colspan='12' nowrap align='center' class='pt10b' bgcolor='#ceffce'>販売費及び一般管理費</td>
                    <td colspan='12' nowrap align='center' class='pt10b' bgcolor='#ccffff'>経　費　合　計</td>
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>カプラ</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>リニア</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>商管</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>合計</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>カプラ</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>リニア</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>商管</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>合計</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ccffff'>カプラ</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ccffff'>リニア</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ccffff'>商管</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ccffff'>合計</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>前月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>当月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>差額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>前月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>当月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>差額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>前月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>当月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>差額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>前月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>当月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>差額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>前月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>当月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>差額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>前月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>当月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>差額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>前月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>当月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>差額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>前月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>当月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>差額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>前月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>当月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>差額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>前月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>当月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>差額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>前月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>当月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>差額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>前月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>当月</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>差額</td>
                </tr>
                <tr>
                    <td width='10' rowspan='9' align='center' class='pt10b' bgcolor='#ccffff'>人件費</td>
                    <TD nowrap class='pt10'>役員報酬</TD>
                    <?php
                        $r = 0;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <TR>
                    <TD nowrap class='pt10'>給料手当</TD>
                    <?php
                        $r = 1;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>賞与手当</TD>
                    <?php
                        $r = 2;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>顧問料</TD>
                    <?php
                        $r = 3;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>法定福利費</TD>
                    <?php
                        $r = 4;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>厚生福利費</TD>
                    <?php
                        $r = 5;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>賞与引当金繰入</TD>
                    <?php
                        $r = 6;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>退職給付費用</TD>
                    <?php
                        $r = 7;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR bgcolor='#ccffff'>
                    <TD nowrap class='pt10b' align='right'>人件費計</TD>
                    <?php
                        $r = 8;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <tr>
                    <td width='10' rowspan='30' align='center' class='pt10b' bgcolor='#ccffff'>経費</td>
                    <TD nowrap class='pt10'>旅費交通費</TD>
                    <?php
                        $r = 9;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>海外出張</TD>
                    <?php
                        $r = 10;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>通　信　費</TD>
                    <?php
                        $r = 11;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>会　議　費</TD>
                    <?php
                        $r = 12;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>交際接待費</TD>
                    <?php
                        $r = 13;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>広告宣伝費</TD>
                    <?php
                        $r = 14;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>求　人　費</TD>
                    <?php
                        $r = 15;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>運賃荷造費</TD>
                    <?php
                        $r = 16;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>図書教育費</TD>
                    <?php
                        $r = 17;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>業務委託費</TD>
                    <?php
                        $r = 18;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <td nowrap class='pt10'>事　業　等</td>
                    <?php
                        $r = 19;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>諸税公課</TD>
                    <?php
                        $r = 20;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>試験研究費</TD>
                    <?php
                        $r = 21;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>雑　　　費</TD>
                    <?php
                        $r = 22;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>修　繕　費</TD>
                    <?php
                        $r = 23;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>保証修理費</TD>
                    <?php
                        $r = 24;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>事務用消耗品費</TD>
                    <?php
                        $r = 25;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>工場消耗品費</TD>
                    <?php
                        $r = 26;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>車　両　費</TD>
                    <?php
                        $r = 27;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>保　険　料</TD>
                    <?php
                        $r = 28;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>水道光熱費</TD>
                    <?php
                        $r = 29;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>諸　会　費</TD>
                    <?php
                        $r = 30;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>支払手数料</TD>
                    <?php
                        $r = 31;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>地代家賃</TD>
                    <?php
                        $r = 32;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>寄　付　金</TD>
                    <?php
                        $r = 33;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>倉　敷　料</TD>
                    <?php
                        $r = 34;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>賃　借　料</TD>
                    <?php
                        $r = 35;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>減価償却費</TD>
                    <?php
                        $r = 36;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>クレーム対応費</TD>
                    <?php
                        $r = 37;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // 製造経費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // 販管費増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // 経費合計増減
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr bgcolor='#ccffff'>
                    <TD nowrap class='pt10b' align='right'>経費計</TD>
                    <?php
                        $r = 38;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr bgcolor='#ccffff'>
                    <TD colspan='2' nowrap class='pt10b' align='right'>合　計</TD>
                    <?php
                        $r = 39;     // 該当レコード
                        for ($c=1;$c<37;$c++) {
                            printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
            </TBODY>
        </table>
    </center>
</body>
</html>
