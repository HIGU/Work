<?php
//////////////////////////////////////////////////////////////////////////
//  月次損益関係 仕入高の調整入力及び登録                               //
//  2003/02/22   Copyright(C) K.Kobayashi k_kobayashi@tnk.co.jp         //
//  変更経歴                                                            //
//  2003/02/22 新規作成  profit_loss_adjust_shiire.php                  //
//  2003/02/23 登録済みの買掛金・有償支給未収入金を表示(照会)させる     //
//  2003/03/07 合計金額追加 Excel コピー用 仕入高追加 単位変更対応      //
//  2003/03/10 仕入データの照会部分で調整データを加味するロジック追加   //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();       // Script Name は自動取得
$_SESSION["site_index"] = 10;       // 月次損益関係=10 最後のメニューは 99 を使用
$_SESSION["site_id"] = 7;           // 下位メニュー無し (0 <=)
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "あなたは許可されていません｡<br>管理者に連絡して下さい｡";
    header("Location: http:" . WEB_HOST . "menu.php");
    exit();
}
//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:m:s");

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);
///// 対象当月
$yyyymm = $_SESSION['pl_ym'];
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// 表示単位を設定取得
if (isset($_POST['shiire_tani'])) {
    $_SESSION['shiire_tani'] = $_POST['shiire_tani'];
    $tani = $_SESSION['shiire_tani'];
} elseif (isset($_SESSION['shiire_tani'])) {
    $tani = $_SESSION['shiire_tani'];
} else {
    $tani = 1000;        // 初期値 表示単位 千円
    $_SESSION['shiire_tani'] = $tani;
}
///// 表示 小数部桁数 設定取得
if (isset($_POST['shiire_keta'])) {
    $_SESSION['shiire_keta'] = $_POST['shiire_keta'];
    $keta = $_SESSION['shiire_keta'];
} elseif (isset($_SESSION['shiire_keta'])) {
    $keta = $_SESSION['shiire_keta'];
} else {
    $keta = 3;          // 初期値 小数点以下桁数
    $_SESSION['shiire_keta'] = $keta;
}

if (!isset($_POST['touroku'])) {     // データ入力
    ////////// 登録済みならば調整金額・調整理由 取得
    $query = sprintf("select kin, reason from act_adjust_history where pl_bs_ym=%d and note='カプラ仕入高調整'", $yyyymm);
    $res = array();
    if (getResult($query,$res) > 0) {
        $c_kin    = $res[0]['kin'];
        $reason_c = $res[0]['reason'];
        if ($c_kin > 0) {
            $c_kin = ('+' . $c_kin);
        }
    } else {
        $c_kin    = "";
        $reason_c = "";
    }
    $query = sprintf("select kin, reason from act_adjust_history where pl_bs_ym=%d and note='リニア仕入高調整'", $yyyymm);
    $res = array();
    if (getResult($query,$res) > 0) {
        $l_kin    = $res[0]['kin'];
        $reason_l = $res[0]['reason'];
        if ($l_kin > 0) {
            $l_kin = ('+' . $l_kin);
        }
    } else {
        $l_kin    = "";
        $reason_l = "";
    }
} else {                            // 登録処理
    $query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='カプラ仕入高調整'", $yyyymm);
    if (getUniResult($query,$c_kin) <= 0) {
        $query = sprintf("insert into act_adjust_history (pl_bs_ym, kin, note, reason) values (%d, %d, 'カプラ仕入高調整', '%s')", $yyyymm, $_POST['adjust_c'], $_POST['reason_c']);
        query_affected($query);
    } else {
        $query = sprintf("update act_adjust_history set kin=%d, reason='%s' where pl_bs_ym=%d and note='カプラ仕入高調整'", $_POST['adjust_c'], $_POST['reason_c'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='リニア仕入高調整'", $yyyymm);
    if (getUniResult($query,$c_kin) <= 0) {
        $query = sprintf("insert into act_adjust_history (pl_bs_ym, kin, note, reason) values (%d, %d, 'リニア仕入高調整', '%s')", $yyyymm, $_POST['adjust_l'], $_POST['reason_l']);
        query_affected($query);
    } else {
        $query = sprintf("update act_adjust_history set kin=%d, reason='%s' where pl_bs_ym=%d and note='リニア仕入高調整'", $_POST['adjust_l'], $_POST['reason_l'], $yyyymm);
        query_affected($query);
    }
    $_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>仕入高 調整入力完了<br>第 %d期 %d月</font>",$ki,$tuki);
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
///// 要素別買掛表の計算(仕入高でＣＬ比率を決める) 照会用データ取得
$query = sprintf("select kin1 from pl_bs_summary where t_id='E' and pl_bs_ym=%d order by t_row ASC", $yyyymm);
$res = array();
if ((getResult($query, $res)) > 0) {
    $kai_c    = number_format($res[0][0]);                      // 買掛金１〜５カプラ
    $kai_l    = number_format($res[1][0]);                      // 買掛金１〜５リニア
    $kai      = number_format(($res[0][0]) + ($res[1][0]));     // 買掛金１〜５合計
    $misyu_c  = number_format($res[2][0]);                      // 有償支給未収入金１〜５カプラ
    $misyu_l  = number_format($res[3][0]);                      // 有償支給未収入金１〜５リニア
    $misyu    = number_format(($res[2][0]) + ($res[3][0]));     // 有償支給未収入金１〜５合計
    $shiire_c = number_format(($res[0][0] - $res[2][0]));       // 買掛金１〜５ − 有償支給未収入金１〜５ カプラ
    $data_c   = ($res[0][0] - $res[2][0]);                      // 仕入データカプラ
    $shiire_l = number_format(($res[1][0] - $res[3][0]));       // 買掛金１〜５ − 有償支給未収入金１〜５ リニア
    $data_l   = ($res[1][0] - $res[3][0]);                      // 仕入データリニア
    $data_all = ($data_c + $data_l);                            // 仕入データ合計
    $shiire   = number_format($data_all);                       // 買掛金１〜５ − 有償支給未収入金１〜５ 合計
        ///// 調整データの取得
    $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note like '%%仕入高調整'", $yyyymm); // 全体
    getUniResult($query, $adjust_all);
    $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note='カプラ仕入高調整'", $yyyymm); // カプラ
    getUniResult($query, $adjust_c);
    $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note='リニア仕入高調整'", $yyyymm); // リニア
    getUniResult($query, $adjust_l);
        ///// 調整ロジック END
    $view_data_c = number_format((($data_c + ($adjust_c)) / $tani), $keta);     // マイナスも考慮して()を使用
    $view_data_l = number_format((($data_l + ($adjust_l)) / $tani), $keta);     // $adjust = 調整金額
} else {
    $kai_c    = "未登録";
    $kai_l    = "未登録";
    $kai      = "------";
    $misyu_c  = "未登録";
    $misyu_l  = "未登録";
    $misyu    = "------";
    $shiire_c = "------";
    $shiire_l = "------";
    $shiire   = "------";
    $view_data_c = "------";
    $view_data_l = "------";
}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // 日付が過去
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // 常に修正されている
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>月次仕入高調整入力</TITLE>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';

/* 入力文字頭１桁の '+' '-' '0' チェック */
function isPlusMinus(str) {
    var c = str.charAt(0);
    if ((c == '+') || (c == '-') || (c == '0')) {
        return true;
    }
    return false;
}
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
function adjust_input(obj){
    if(!obj.adjust_c.value.length){
        alert("カプラの調整額が空白です。");
        obj.adjust_c.focus();
        obj.adjust_c.select();
        return false;
    }
    if(!isPlusMinus(obj.adjust_c.value)){
        alert("頭に＋−の符号を付けて下さい｡\n調整しない場合は０を入れて下さい｡");
        obj.adjust_c.focus();
        obj.adjust_c.select();
        return false;
    }
    if(isDigit(obj.adjust_c.value)){
        alert("数値以外は入力出来ません｡");
        obj.adjust_c.focus();
        obj.adjust_c.select();
        return false;
    }
    if(!obj.adjust_l.value.length){
        alert("リニアの調整額が空白です。");
        obj.adjust_l.focus();
        obj.adjust_l.select();
        return false;
    }
    if(!isPlusMinus(obj.adjust_l.value)){
        alert("頭に＋−の符号を付けて下さい｡\n調整しない場合は０を入れて下さい｡");
        obj.adjust_l.focus();
        obj.adjust_l.select();
        return false;
    }
    if(isDigit(obj.adjust_l.value)){
        alert("数値以外は入力出来ません｡");
        obj.adjust_l.focus();
        obj.adjust_l.select();
        return false;
    }
    return true;
}
function set_focus(){
    document.adjust.adjust_c.focus();
    document.adjust.adjust_c.select();
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
.pt9 {
    font-size: 9pt;
    font-family: monospace;
}
.pt10 {
    font:normal 10pt;
    font-family: monospace;
}
.pt11 {
    font-size: 11pt;
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
.title-font {
    font:bold 16.5pt;
    font-family: monospace;
}
.today-font {
    font-size: 10.5pt;
    font-family: monospace;
}
.right{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
-->
</style>
</HEAD>
<BODY class='margin0' onLoad="set_focus()">
    <center>
        <table width='100%' border='1' cellspacing='1' cellpadding='1'>
            <tr>
                <form method='post' action='profit_loss_select.php'>
                    <td width='60' bgcolor='blue' align='center' valign='center'>
                        <input class='pt12b' type='submit' name='return' value='戻る'>
                    </td>
                </form>
                <td bgcolor='#d6d3ce' align='center' class='title-font'>
                    <?php
                        printf("第%d期　%d月度　仕入高(要素別買掛金額) 調整額の入力\n",$ki,$tuki);
                    ?>
                </td>
                <td bgcolor='#d6d3ce' align='center' width='140' class='today-font'>
                    <?php echo $today ?>
                </td>
            </tr>
        </table>
        <form name='adjust' action='profit_loss_adjust_shiire.php' method='post' onSubmit='return adjust_input(this)'>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td colspan='3' align='center' class='pt11'>
                        調整金額は頭に＋−をつけて入力して下さい｡
                    </td>
                </tr>
                <tr>
                    <td colspan='3' align='center' class='pt11'>
                        調整しない場合は０を入力して下さい｡
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffa4a4' class='pt12b'>
                        カプラ仕入高調整額<input type='text' name='adjust_c' size='15' maxlength='11' value='<?php echo $c_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='pt12b'>
                        リニア仕入高調整額<input type='text' name='adjust_l' size='15' maxlength='11' value='<?php echo $l_kin ?>' class='right'>
                    </td>
                    <td align='center'>　</td> <!-- 余白 -->
                </tr>
                <tr>
                    <td align='left' class='pt11'>
                        Ｃ理由<input type='text' name='reason_c' size='50' maxlength='50' value='<?php echo $reason_c ?>' class='pt9'>
                    </td>
                    <td align='left' class='pt11'>
                        Ｌ理由<input type='text' name='reason_l' size='50' maxlength='50' value='<?php echo $reason_l ?>' class='pt9'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4'>
                        <input type='submit' name='touroku' value='実行' class='pt11b'>
                    </td>
                </tr>
            </table>
        </form>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <caption class='pt12b'>要素別買掛表(１〜５主要材料)の仕入高 照会</caption>
            <th bgcolor='#ffff94' width='160' class='pt11b'>項　　目</th> <!-- 薄い黄色 -->
            <th bgcolor='#ffff94' width='160' class='pt11b'>カプラ金額</th>
            <th bgcolor='#ffff94' width='160' class='pt11b'>リニア金額</th>
            <th bgcolor='#ffff94' width='160' class='pt11b'>合計金額</th>
            <tr>
                <td align='center' class='pt11b'>買　掛　金</td>
                <td align='right' class='pt11b'><?php echo $kai_c ?></td>
                <td align='right' class='pt11b'><?php echo $kai_l ?></td>
                <td align='right' class='pt11b'><?php echo $kai ?></td>
            </tr>
            <tr>
                <td align='center' class='pt11b'>有償未収入金</td>
                <td align='right' class='pt11b'><?php echo $misyu_c ?></td>
                <td align='right' class='pt11b'><?php echo $misyu_l ?></td>
                <td align='right' class='pt11b'><?php echo $misyu ?></td>
            </tr>
            <tr>
                <td align='center' class='pt11b'>仕　入　高</td>
                <td align='right' class='pt11b'><?php echo $shiire_c ?></td>
                <td align='right' class='pt11b'><?php echo $shiire_l ?></td>
                <td align='right' class='pt11b'><?php echo $shiire ?></td>
            </tr>
        </table>
        <form method='post' action='profit_loss_adjust_shiire.php'>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <caption class='pt12b'>仕入高 Excel コピー用 (調整済)</caption>
                <th bgcolor='#ffff94' width='160' class='pt11b'>項　　目</th> <!-- 薄い黄色 -->
                <th bgcolor='#ffff94' width='160' class='pt11b'>カプラ金額</th>
                <th bgcolor='#ffff94' width='160' class='pt11b'>リニア金額</th>
                <th bgcolor='#ffff94' class='pt11b'>単位 桁数 変更</th>
                <tr>
                    <td align='center' class='pt11b'>仕入高(コピー用)</td>
                    <td align='right' class='pt11b'><?php echo $view_data_c ?></td>
                    <td align='right' class='pt11b'><?php echo $view_data_l ?></td>
                    <td colspan='13' nowrap bgcolor='#d6d3ce' align='right' class='pt10'>
                        単位
                        <select name='shiire_tani' class='pt10'>
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
                        <select name='shiire_keta' class='pt10'>
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
                </tr>
            </table>
        </form>
    </center>
</BODY>
</HTML>
