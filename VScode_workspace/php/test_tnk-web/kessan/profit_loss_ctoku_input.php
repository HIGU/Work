<?php
//////////////////////////////////////////////////////////////////////////////
// カプラ特注・標準の人員比較計算表の登録・修正及び照会兼用                 //
// Copyright (C) 2009-2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/08/24 Created   profit_loss_ctoku_input.php                         //
// 2009/11/02 特注・標準を分ける前にカプラの販管費の人件費に                //
//            商管への社員按分給与を加味するよう変更                        //
// 2009/12/09 一部表示がうまくいってなかった点を修正                        //
// 2009/12/10 コメントの整理                                                //
// 2010/06/04 異動に伴い調整人員名の変更                                    //
// 2010/10/06 前月のデータコピーを追加                                      //
// 2012/06/05 特注の人員名を変更                                            //
// 2013/04/12 特注の人員名を変更                                            //
// 2013/06/05 特注組立の人員に小口課員を追加                                //
// 2014/05/07 異動に伴い調整人員名の変更                                    //
// 2014/07/01 特注組立の人員に薄井課員と佐藤課員を追加                      //
// 2015/05/08 異動に伴い人員名の変更                                        //
// 2016/03/03 異動に伴い人員名の変更                                        //
// 2016/04/21 異動に伴い人員名の変更                                        //
// 2017/05/08 異動に伴い人員名の変更                                        //
// 2017/07/06 異動に伴い人員名の変更                                        //
// 2017/11/13 標準→特注の仕入高配賦を追加                                  //
// 2018/10/10 2018/09固定資産訂正分はすべてカプラ標準なので調整        大谷 //
// 2019/05/09 人事異動に伴う名称変更                                   大谷 //
// 2019/11/11 買掛でマイナス分を調整                                   大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting',E_ALL);        // E_ALL='2047' debug 用
// ini_set('display_errors','1');           // Error 表示 ON debug 用 リリース後コメント
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
// $menu->set_caption('栃木日東工器(株)');
///// 呼出先のaction名とアドレス設定
// $menu->set_action('抽象化名',   PL . 'address.php');

///// タイトルの日付・時間設定
$today = date("Y/m/d H:i:s");

///// 期・月の取得
$ki = 22;
$tuki = 11;

///// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第{$ki}期　{$tuki}月度　カプラ特注・標準 人員比率計算表");

///// 対象当月
$yyyymm = 202211;
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// yymm形式
$ym4 = substr($yyyymm, 2, 4);

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
        if (c == ".") {
            return true;
        }
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
    document.invent.invent_1.focus();
    document.invent.invent_1.select();
}
function data_copy_click(obj) {
    return confirm("前月のデータをコピーします。\n既にデータがある場合は上書きされます。");
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
.pt11 {
    font-size:11pt;
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
th {
    font:bold 11pt;
    font-family: monospace;
}
.title_font {
    font:bold 14pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.right{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
}
.rightb{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#e6e6e6';
}
.rightbg{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#ccffcc';
}
.rightby{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#ffffcc';
}
.rightbb{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#ccffff';
}
.margin0 {
    margin:0%;
}
-->
</style>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <form name='invent' action='<?php echo $menu->out_self() ?>' method='post'>
        <table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
            <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='2' border='1'>
                <th colspan='3' bgcolor='#ccffcc' width='110'>　</th><th bgcolor='#ccffcc' width='110'><?php echo $p1_ym ?></th><th bgcolor='#ccffcc' width='110'><?php echo $yyyymm ?></th>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ccffcc' class='pt11b' colspan='2'>製　造　課</td>
                    <td align='center' bgcolor='white' class='pt11b'>社員数</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 130 ?>'>
                        <?php echo 130 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 130 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>パート数</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 130 ?>'>
                        <?php echo 130 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 130 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>パート掛率50％</td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo 130 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo 130 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>計</td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo 130 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo 130 ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>特注製造</td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>社員数</td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo 130 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo 130 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>パート数</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 130 ?>'>
                        <?php echo 130 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 130 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>パート掛率50％</td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo 130 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo 130 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>計</td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo 130 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo 130 ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ccffcc' class='pt11b' colspan='2'>組立担当</td>
                    <td align='center' bgcolor='white' class='pt11b'>社員数</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 130 ?>'>
                        <?php echo 130 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 130 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>パート数</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 130 ?>'>
                        <?php echo 130 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 130 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>パート掛率50％</td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo 130 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo 130 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>計</td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo 130 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo 130 ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>特注組立</td>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>社員数</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo 130 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo 130 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>パート数</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 130 ?>'>
                        <?php echo 130 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 130 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>パート掛率50％</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo 130 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo 130 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>計</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo 130 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo 130 ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='3' align='center' bgcolor='#ffffcc' class='pt11b'>特注製造<br>社員数計算<font color='red'>※１</font></td>
                    <?php
                    if ($yyyymm < 201706) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>増山課長・名畑目係長</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>阿久津課長代理・名畑目係長</td>
                    <?php
                    }
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>20%</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 130 ?>'>
                        <?php echo 130 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 130 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm < 201706) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>手塚・目澤・入間川・続田</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>556部門社員</td>
                    <?php
                    }
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>100%</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 130 ?>'>
                        <?php echo 130 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 130 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 130 ?>'>
                        <?php echo 130 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 130 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td rowspan='3' align='center' bgcolor='#ccffff' class='pt11b'>特注組立<br>社員数計算<font color='red'>※１</font></td>
                    <?php
                    if ($yyyymm >= 201904) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>八木沢課員</td>
                    <td align='center' bgcolor='white' class='pt11b'>20%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>八木沢課長</td>
                    <td align='center' bgcolor='white' class='pt11b'>20%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 130 ?>'>
                        <?php echo 130 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 130 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm >= 201904) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>小山課長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>80%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>小山係長</td>
                    <td align='center' bgcolor='white' class='pt11b'>100%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 130 ?>'>
                        <?php echo 130 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 130 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>薄井・佐藤・小川課員</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>係長以外525部門社員</td>
                    <?php
                    }
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>100%</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 130 ?>'>
                        <?php echo 130 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 130 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='3'>横川カプラ支払額<font color='red'>※２</font></td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 130 ?>'>
                        <?php echo 130 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 130 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td colspan='5' align='center'>
                        <input type='submit' name='entry' value='実行' >
                        &nbsp;&nbsp;&nbsp;
                        <input type='submit' name='copy' value='前月データコピー' onClick='return data_copy_click(this)'>
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
        <br>
        <b>※１ 各部門に携わる社員数を入力してください</b>
        <br>
        <b>
        ※２ AS経理メニュー 26→20→20 外注＃：<font color='red'>01298</font>
        <br>
        　　　　　年月を入力しリスト印刷<font color='red'>Ｙ</font>でリストを印刷し最下部
        <br>
        　　　　　　　　　当月支払予定の金額を入力（50%を特注仕入高に配賦）
        <br>
        </b>
    </center>
</body>
</html>
