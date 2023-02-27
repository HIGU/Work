<?php
//////////////////////////////////////////////////////////////////////////////
// 商品管理・試験修理の計算データの登録・修正及び照会兼用                   //
// Copyright (C) 2009-2022 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/08/18 Created   profit_loss_nkb_input.php                           //
// 2009/08/19 物流を商品管理に名称変更                                      //
// 2009/10/06 商管の営業外収益が7月しか入力できないようになっていた修正     //
//            200909より商管を売上高直接入力から調整入力に変更              //
// 2009/11/02 10月より商管に間接部門の給与を配賦する処理を追加              //
//            商管への配賦割合は$allo_nkb_kyuを変更すること                 //
//            カプラ・リニアの減算分もここで計算                            //
// 2009/11/09 商管への給与配賦を$allo_nkb_kyuから$allo_nkb_kyu1と2に変更    //
//            09/11/09時点で$allo_nkb_kyu1=0.09 $allo_nkb_kyu2=0.52         //
// 2009/11/10 $allo_nkb_kyu1=0.20 $allo_nkb_kyu2=1.00に変更                 //
// 2009/12/07 試験修理にカプラ分を追加、調整ではなく売上高入力              //
//            売上比率で労務費・経費をCLに按分                              //
// 2009/12/10 コメントの整理                                                //
// 2010/01/27 全てのデータを登録後売上高比を再計算し、営業外の配賦を再度    //
//            実行する（2009/12は計算したが損益上は未適用）                 //
//            いつから実施するかを確認してif分を変更                        //
// 2010/01/28 前半期の売上高比から対象月までの売上高比に変更                //
// 2010/02/01 201001より商管の営業外収益その他を入力できないようにする      //
// 2014/08/06 2014/07より、商品管理課の配賦給与を工場長0.2(20%)→0.05(5%)   //
//            管理部長1.0(100%)→0.5(50%)へ変更(工場長は前期売上割合)       //
// 2014/08/07 商管の配賦給与を元に戻した。                                  //
//            工場長0.05(5%)→0.2(20%)、管理部長0.5(50%)→1.0(100%)         //
// 2016/07/22 修理・耐久の売上高と売上高比を登録                            //
// 2017/07/06 表示を分かりやすく工場長と、管理部長を追加                    //
// 2022/05/11 商管の配賦給与を以下に変更。                                  //
//            工場長0.2(20%)、管理部長1.0(100%)→0.2(20%)                   //
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
$menu->set_title("第{$ki}期　{$tuki}月度　商品管理・試修 損益の登録");

///// 対象当月
$yyyymm = 202211;
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
//対象年月日
$ymd_str = $yyyymm . "01";
$ymd_end = $yyyymm . "99";

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
    document.invent.invent_1.focus();
    document.invent.invent_1.select();
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
                <th colspan='1' bgcolor='#ccffcc' width='110'>　</th>
                <th bgcolor='#ffffcc' width='110'>商品管理</th>
                <th bgcolor='#ccffff' width='110'>リニア試修</th>
                <th bgcolor='#ccffff' width='110'>カプラ試修</th>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>
                    <?php if ($yyyymm >= 200909) { ?>
                    売上高調整額
                    <font color='red'>※１</font>
                    <?php } else { ?>
                    売上高
                    <?php } ?>
                    </td>
                    </td>
                    <?php if ($yyyymm >= 200907) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 110 ?>' class='right'>
                        </td>
                    <?php } else { ?>
                        <td align='center' class='rightby'>
                            <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                            <?php echo 110 ?>
                        </td>
                    <?php } ?>
                    <?php if ($yyyymm >= 200911) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 110 ?>' class='right'>
                        </td>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 110 ?>' class='right'>
                        </td>
                    <?php } elseif ($yyyymm >= 200909) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 110 ?>' class='right'>
                        </td>
                        <td align='center' class='rightbb'>
                            <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                            <?php echo 110 ?>
                        </td>
                    <?php } else { ?>
                        <td align='center' class='rightbb'>
                            <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                            <?php echo 110 ?>
                        </td>
                        <td align='center' class='rightbb'>
                            <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                            <?php echo 110 ?>
                        </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>期首棚卸高</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <!-- <?php echo 110 ?> -->
                        　
                    </td>
                    <td align='center' class='rightbb'>　</td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>材料費</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <?php echo 110 ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 110 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <?php if ($yyyymm >= 200911) { ?>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 110 ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <?php } else { ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <?php echo 110 ?>
                    </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>
                    労務費
                    <?php if ($yyyymm == 200907) { ?>
                    <font color='red'>※１</font>
                    <?php } ?>
                    </td>
                    <?php if ($yyyymm == 200907) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 110 ?>' class='right' onChange='return isDigitcho(value);'>
                        </td>
                    <?php } else { ?>
                        <td align='center' class='rightby'>
                            <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                            <?php echo 110 ?>
                        </td>
                    <?php } ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <!-- <?php echo 110 ?> -->
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <?php echo 110 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>製造経費</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <!-- <?php echo 110 ?> -->
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo 110 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>期末棚卸高</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <!-- <?php echo 110 ?> -->
                        　
                    </td>
                    <td align='center' class='rightbb'>　</td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>
                    人件費
                    <?php if ($yyyymm == 200907) { ?>
                    <font color='red'>※１</font>
                    <?php } ?>
                    </td>
                    <?php if ($yyyymm == 200907) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 110 ?>' class='right' onChange='return isDigitcho(value);'>
                        </td>
                    <?php } else { ?>
                        <td align='center' class='rightby'>
                            <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                            <?php echo 110 ?>
                        </td>
                    <?php } ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <!-- <?php echo 110 ?> -->
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo 110 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>販管費経費</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <!-- <?php echo 110 ?> -->
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo 110 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>業務委託収入</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <!-- <?php echo 110 ?> -->
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo 110 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>仕入割引</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <!-- <?php echo 110 ?> -->
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo 110 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>営業外収益その他</td>
                    <?php if ($yyyymm >= 201001) { ?>
                        <td align='center' class='rightby'>
                            <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                            <?php echo 110 ?>
                        </td>
                    <?php } elseif ($yyyymm >= 200907) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 110 ?>' class='right' onChange='return isDigit(value);'>
                        </td>
                    <?php } else { ?>
                        <td align='center' class='rightby'>
                            <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                            <?php echo 110 ?>
                        </td>
                    <?php } ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <!-- <?php echo 110 ?> -->
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo 110 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>支払利息</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <!-- <?php echo 110 ?> -->
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo 110 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>営業外費用その他</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo 110 ?>'>
                        <!-- <?php echo 110 ?> -->
                        <?php echo 110 ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo 110 ?>
                    </td>
                </tr>
                <?php if ($yyyymm >= 200910) { ?>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>商管社員給与按分１(20% 工場長)<font color='red'>※２</font></td>
                    <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 110 ?>' class='right' onChange='return isDigit(value);'>
                        </td>
                    <td align='center' class='rightbb'>　</td>
                    <td align='center' class='rightbb'>　</td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>商管社員給与按分２(20% 管理部長)<font color='red'>※２</font></td>
                    <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo 110 ?>' class='right' onChange='return isDigit(value);'>
                        </td>
                    <td align='center' class='rightbb'>　</td>
                    <td align='center' class='rightbb'>　</td>
                </tr>
                <?php } ?>
                <tr>
                    <td colspan='4' align='center'>
                        <input type='submit' name='entry' value='実行' >
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
        <?php if ($yyyymm == 200907) { ?>
            <br>
            <b>※１ 商品管理の労務費・人件費は調整金額を入力</b>
        <?php } ?>
        <?php if ($yyyymm >= 200911) { ?>
            <br>
            <b>※１ 商品管理・リニア試修の売上高は調整金額を入力</b>
            <br>
            <b>カプラ試修の売上高は売上高を入力</b>
        <?php } elseif ($yyyymm >= 200909) { ?>
            <br>
            <b>※１ 商品管理・試修の売上高は調整金額を入力</b>
        <?php } ?>
        <?php if ($yyyymm >= 200910) { ?>
            <br><br>
            <b>※２ 給与配賦を行う方の給与の支給項目・支給合計を入力（それぞれの％で自動配賦）</b>
        <?php } ?>
    </center>
</body>
</html>
