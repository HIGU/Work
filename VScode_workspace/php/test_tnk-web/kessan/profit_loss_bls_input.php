<?php
//////////////////////////////////////////////////////////////////////////////
// 試験修理・バイモルの人員比較計算表の登録・修正及び照会兼用               //
// Copyright (C) 2009-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/08/24 Created   profit_loss_bls_input.php                           //
// 2009/11/02 バイモル・試験修理を分ける前にリニアの販管費の人件費に        //
//            商管への社員按分給与を加味するよう変更                        //
// 2009/12/09 試験修理の労務費を１１分は固定値にするように変更              //
//            サービス割合登録忘れの為                                      //
// 2009/12/10 コメントの整理                                                //
// 2010/03/04 添田さんの給与配賦を加味して労務費を計算するように変更        //
// 2010/06/04 異動に伴い調整人員名の変更                                    //
// 2010/10/06 前月のデータコピーを追加                                      //
// 2011/06/07 2011/04より試験修理部門に581追加                              //
// 2011/06/08 500部門の経費が試験修理部門に配布されていたのを               //
//            2011/06より配布しないように変更                               //
// 2013/01/28 バイモルを液体ポンプへ変更（表示のみデータはバイモルのまま）  //
// 2014/05/07 異動に伴い調整人員名の変更                                    //
// 2014/08/06 一部コメントの追加                                            //
// 2015/06/10 機工の計算を追加                                              //
// 2015/06/15 機工の給与配賦を6月度より変更                                 //
// 2015/11/06 機工の給与配賦を10月度より変更                                //
//                                  → 元に戻す                             //
// 2016/02/02 安田さんの配賦を8：2（試修：リニア）に変更                    //
//            以前は入力した給与の0.5をリニア→試修だったが、入力した給与の //
//            0.2を試修→リニア（×-0.2）に変更                             //
// 2016/04/25 2016/04より機工の給与配賦を変更                               //
// 2016/07/22 修理・耐久損益のための労務費・経費を計算登録                  //
// 2016/10/14 安田さんを安達さんへ変更（配賦割合は要検討$invent[16]）       //
// 2016/10/31 安達さんは100％試験なので、金額を入力しない(2016/10～)        //
// 2016/11/18 一番下に安田係長給与の20％を自動配賦するよう追加              //
//            リニアからマイナス(労務費以外には影響させない)                //
// 2017/05/08 人事異動による名称変更                                        //
// 2017/05/09 2017/05より機工配賦割合変更（千田副部長、石崎課長代理分）     //
// 2018/04/19 2018/04より機工配賦割合変更（千田副部長、安田課長分）         //
// 2018/10/17 コメントを修正                                                //
// 2019/02/05 コメントを修正                                                //
// 2019/05/09 人事異動に伴う名称の変更                                      //
// 2021/03/03 機工終了に伴う配布の終了                                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting',E_ALL);        // E_ALL='2047' debug 用
// ini_set('display_errors','1');           // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class

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
$menu->set_title("第{$ki}期　{$tuki}月度　ＢＬＳ 人員比率計算表");

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
                    <td rowspan='4' align='center' bgcolor='#ccffcc' class='pt11b' colspan='2'>リ　ニ　ア</td>
                    <td align='center' bgcolor='white' class='pt11b'>社員数</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>パート数</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>パート掛率50％</td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo 120 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo 120 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>計</td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo 120 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo 120 ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>液体ポンプ</td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>社員数</td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo 120 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo 120 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>パート数</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>パート掛率50％</td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo 120 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo 120 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>計</td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo 120 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo 120 ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>試験・修理</td>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>社員数</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo 120 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo 120 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>パート数</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>パート掛率50％</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo 120 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo 120 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>計</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo 120 ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo 120 ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='3' align='center' bgcolor='#ffffcc' class='pt11b'>
                    液体ポンプ社員数
                    <br>
                    計算<font color='red'>※１</font>
                    </td>
                    <?php
                    if ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>千田課長</td>
                    <?php
                    } elseif ($yyyymm < 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>石崎課長代理</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>安田課長</td>
                    <?php
                    }
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td rowspan='3' align='center' bgcolor='#ccffff' class='pt11b'>
                    試修社員数
                    <br>
                    計算<font color='red'>※１</font>
                    </td>
                    <td align='center' bgcolor='white' class='pt11b'>安達課員</td>
                    <td align='center' bgcolor='white' class='pt11b'>100%</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm < 201507) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>萩野課長代理</td>
                    <?php
                    } elseif ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>菊地課長</td>
                    <?php
                    } elseif ($yyyymm < 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>安田課長代理</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>萩野課長</td>
                    <?php
                    }
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>給与配賦計算<br>(液体ポンプ)<font color='red'>※２入力しない</font></td>
                    <?php
                    if ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>千田課長</td>
                    <?php
                    } elseif ($yyyymm < 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>石崎課長代理</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>安田課長</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>給与配賦計算<br>(試験・修理)<font color='red'>※２入力しない</font></td>
                    <?php
                    if ($yyyymm < 201507) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>萩野課長代理</td>
                    <?php
                    } elseif ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>菊地課長</td>
                    <?php
                    } elseif ($yyyymm < 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>安田課長代理</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>萩野課長</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td rowspan='7' align='center' bgcolor='#ccffcc' class='pt11b' colspan='2'>試修給与配賦<font color='red'>※３</font><BR>※\\Fs1\総務課専用\人事関係<BR>\ｼｮｰﾄﾊﾟｰﾄ・ｱﾙﾊﾞｲﾄ給与\2019年度 添田<BR>給与＋賞与<BR>※2019年4・5月は特殊</td>
                    <td align='center' bgcolor='white' class='pt11b'>カプラ配賦率</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>カプラ配賦金額</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='c_hai_kin_z' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='c_hai_kin' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>リニア配賦率</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>リニア配賦金額</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='l_hai_kin_z' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='l_hai_kin' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>試験修理配賦率</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>試験修理配賦金額</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='s_hai_kin_z' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='s_hai_kin' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>給与配賦額</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>給与配賦計算<br>(試験・修理)<font color='red'>※４入力しない</font></td>
                    <td align='center' bgcolor='white' class='pt11b'>安達課員</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm >= 201704) {
                    ?>
                    <td rowspan='5' align='center' bgcolor='#ccffff' class='pt11b'>
                    <?php
                    } else {
                    ?>
                    <td rowspan='4' align='center' bgcolor='#ccffff' class='pt11b'>
                    <?php
                    }
                    ?>
                    機工配賦
                    <br>
                    計算<font color='red'>※５</font>
                    </td>
                    <?php
                    if ($yyyymm >= 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201604) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>小森谷副工場長</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201510) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>小森谷部長</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201506) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>小森谷部長</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>大房部長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>50%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm >= 201904) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>入江部長</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201604) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>大房部長</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201510) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>大房部長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>50%</td>
                    <?php
                    } elseif ($yyyymm >= 201506) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>大房部長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>50%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>千田課長</td>
                    <td align='center' bgcolor='white' class='pt11b'>5%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm >= 201904) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>中山部長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>千田副部長</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>千田副部長</td>
                    <td align='center' bgcolor='white' class='pt11b'>80%</td>
                    <?php
                    } elseif ($yyyymm >= 201604) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>千田課長</td>
                    <td align='center' bgcolor='white' class='pt11b'>40%</td>
                    <?php
                    } elseif ($yyyymm >= 201510) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>千田課長</td>
                    <td align='center' bgcolor='white' class='pt11b'>30%</td>
                    <?php
                    } elseif ($yyyymm >= 201506) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>千田課長</td>
                    <td align='center' bgcolor='white' class='pt11b'>30%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>中山課長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>5%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm >= 201904) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>吉成課長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201604) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>中山課長</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201510) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>中山課長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>5%</td>
                    <?php
                    } elseif ($yyyymm >= 201506) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>中山課長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>5%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <?php
                if ($yyyymm >= 201704) {
                ?>
                <tr>
                    <?php
                    if ($yyyymm >= 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>安田課長</td>
                    <td align='center' bgcolor='white' class='pt11b'>80%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>石崎課長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>20%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    機工経費
                    <br>
                    調整<font color='red'>※６</font>
                    </td>
                    <td align='center' bgcolor='white' class='pt11b'>単位：円</td>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>給与配賦計算<br>(試験・修理)<BR><font color='red'>耐久に配賦※７</font></td>
                    <?php
                    if ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>安田係長</td>
                    <?php
                    } elseif ($yyyymm < 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>安田課長代理</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>萩野課長</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo 120 ?>'>
                        <?php echo 120 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 120 ?>' class='right' onChange='return isDigit(value);'>
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
        <b>※２ 給与配賦を行う方の給与の支給項目・支給合計を入力（１０％を自動配賦）</b>
        <br>
        <b>※３ 登録した給与配賦額を試験修理の労務費より各配賦率で配賦</b>
        <br>
        <b>※４ 給与配賦を行う方の給与の支給項目・支給合計を入力（試修8：リニア2で配賦-試修からマイナス）</b>
        <br>
        <b>※５ 給与配賦を行う方の給与の支給項目・支給合計を入力（各割合で自動配賦）</b>
        <br>
        <b>※６ 560部門以外で機工に配賦する製造経費を入力</b>
        <br>
        <b>※７ 給与配賦を行う方の給与の支給項目・支給合計を入力（２０％を<font color='red'>耐久に自動配賦</font>）</b>
        <br>
    </center>
</body>
</html>
