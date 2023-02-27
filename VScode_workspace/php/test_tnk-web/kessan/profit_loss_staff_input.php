<?php
//////////////////////////////////////////////////////////////////////////////
// 全社人員比の計算データの登録・修正及び照会兼用                           //
// 人員比で営業外損益部分を再計算する                                       //
// Copyright (C) 2010-2016 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/02/01 Created   profit_loss_staff_input.php                         //
// 2010/03/04 201002度営業外収益その他の調整を追加。201003には戻し          //
// 2010/10/06 前月のデータコピーを追加                                      //
// 2011/04/06 商管の営業外費用その他の登録部のミスを訂正                    //
// 2012/07/07 2012年6月の営業外費用その他はすべてリニア標準の為             //
//            手動入力                                                      //
// 2012/09/05 2012年8月の営業外収益その他の固定資産売却益はすべてカプラ標準 //
//            の為手動入力                                                  //
// 2012/10/09 2012年9月の営業外収益その他の固定資産売却益 訂正分はすべて    //
//            カプラ標準の為手動入力                                        //
// 2013/01/28 バイモルを液体ポンプへ変更（表示のみデータはバイモルのまま）  //
// 2013/06/06 NKIT有償支給為替差損益の入力を追加                            //
// 2013/07/05 為替差損益の差損と差益が両方収益だったのを費用に配分          //
// 2014/03/05 2014年2月の営業外収益その他の固定資産売却益はすべてカプラ標準 //
//            の為手動入力                                                  //
// 2014/04/03 2014年3月の営業外収益その他の雑収入(PCカプラ金型管理費)は     //
//            すべてカプラ標準の為手動入力                                  //
// 2014/04/04 2014年4月の営業外収益その他の雑収入(PCカプラ金型管理費の戻し) //
//            はすべてカプラ標準の為手動入力                                //
// 2014/08/07 2014年7月の営業外収益その他の雑収入(八下田からの入金分)       //
//            はすべて商管の為手動入力(1,754,636円)                         //
// 2014/09/03 2014年8月の営業外収益その他の雑収入(7月の戻し分)              //
//            はすべて商管の為手動入力(-1,754,636円)                        //
// 2014/10/01 営業外収益その他と営業外費用その他の入力画面を作成し          //
//            調整を行えるようにした。                                      //
// 2016/07/22 修理・耐久損益用に営業外を計算                                //
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
$menu->set_title("第{$ki}期　{$tuki}月度　全社 人員の登録");

///// 対象当月
$yyyymm = 202211;
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
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
.rightbo{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#ffcc99';
}
.rightbg{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#ffffcc';
}
.rightbgr{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#d6d3ce';
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
        <form name='jin' action='<?php echo $menu->out_self() ?>' method='post'>
        <table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
            <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='2' border='1'>
                <th colspan='2' rowspan='2' bgcolor='#ccffcc'>事業部</th>
                <th bgcolor='#d6d3ce' colspan='2'><?php echo $p1_ym ?></th>
                <th bgcolor='#ccffcc' colspan='2'><?php echo $yyyymm ?></th>
                <tr>
                    <th bgcolor='#d6d3ce'>人員</th>
                    <th bgcolor='#d6d3ce'>人員比</th>
                    <th bgcolor='#ccffcc'>人員</th>
                    <th bgcolor='#ccffcc'>人員比</th>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>
                    カプラ
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>　</td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='jin[]' value='<?php echo 140 ?>'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='allo[]' value='<?php echo 140 ?>'>
                        <?php echo 140 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>　</td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>
                    カプラ特注
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo 140 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='allo[]' value='<?php echo 140 ?>'>
                        <?php echo 140 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>　</td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>
                    カプラ標準
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo 140 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='allo[]' value='<?php echo 140 ?>'>
                        <?php echo 140 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    リニア
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>　</td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='jin[]' value='<?php echo 140 ?>'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='allo[]' value='<?php echo 140 ?>'>
                        <?php echo 140 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>　</td>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    液体ポンプ
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo 140 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='allo[]' value='<?php echo 140 ?>'>
                        <?php echo 140 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>　</td>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    リニア標準
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo 140 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='allo[]' value='<?php echo 140 ?>'>
                        <?php echo 140 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>
                    試験修理
                    </td>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>　</td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='hidden' name='jin[]' value='<?php echo 140 ?>'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='hidden' name='allo[]' value='<?php echo 140 ?>'>
                        <?php echo 140 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>　</td>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>
                    カプラ試修
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo 140 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='hidden' name='allo[]' value='<?php echo 140 ?>'>
                        <?php echo 140 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>　</td>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>
                    リニア試修
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo 140 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='hidden' name='allo[]' value='<?php echo 140 ?>'>
                        <?php echo 140 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>
                    商品管理
                    </td>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>　</td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo 140 ?>
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo 140 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='allo[]' value='<?php echo 140 ?>'>
                        <?php echo 140 ?>
                    </td>
                </tr>
                <tr>
                    <th align='center' bgcolor='#ccffcc' colspan='6' rowspan='1' class='pt11b'>
                        NKIT有償支給
                    </th>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>
                    カプラ標準
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' >為替差益</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>為替差損</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>
                    カプラ特注
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' >為替差益</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>為替差損</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    リニア標準
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' >為替差益</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>為替差損</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    液体ポンプ
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' >為替差益</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>為替差損</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <th align='center' bgcolor='#ccffcc' colspan='6' rowspan='1' class='pt11b'>
                        営業外収益その他調整
                    </th>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>
                    カプラ標準
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>カプラ特注</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>
                    リニア標準
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>液体ポンプ</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffcc99' class='pt11b' colspan='2'>
                    試験・修理
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffcc99' class='pt11b' colspan='2'>商品管理</td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <th align='center' bgcolor='#ccffcc' colspan='6' rowspan='1' class='pt11b'>
                        営業外費用その他調整
                    </th>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>
                    カプラ標準
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>カプラ特注</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>
                    リニア標準
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>液体ポンプ</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffcc99' class='pt11b' colspan='2'>
                    試験・修理
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffcc99' class='pt11b' colspan='2'>商品管理</td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' value='<?php echo 140 ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td colspan='6' align='center'>
                        <input type='submit' name='entry' value='実行' >
                        &nbsp;&nbsp;&nbsp;
                        <input type='submit' name='copy' value='前月データコピー' onClick='return data_copy_click(this)'>
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
    </center>
</body>
</html>
