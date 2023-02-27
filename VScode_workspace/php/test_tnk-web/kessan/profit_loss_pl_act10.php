<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 月次 ＣＬ・商品管理・試験修理 損益計算書                    //
// Copyright (C) 2003-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/02/12 Created   profit_loss_pl_act.php                              //
// 2003/02/23 date("Y/m/d H:m:s") → H:i:s のミス修正                       //
// 2003/03/04 文字サイズをブラウザーで変更できなくした title_font 等        //
//            特記事項をカプラ・リニア以外に全体とその他を追加              //
// 2003/03/06 title_font today_font を設定 少数以下の桁数６桁を追加         //
// 2003/03/11 Location: http → Location $url_referer に変更                //
//            メッセージを出力するため site_index site_id をコメントにし    //
//            parent.menu_site.を有効に変更                                 //
// 2003/05/01 工場長からの指示で認証をAccount_groupから通常へ変更           //
// 2003/08/05 $p1_c_srisoku → $p1_l_srisoku になっていたのを修正           //
// 2003/12/15 販管費及び  一般管理費 計 → 一般管理費計 (スペースを削除)    //
// 2004/05/11 左側のサイトメニューのオン・オフ ボタンを追加                 //
// 2005/10/26 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/11/08 $menu->out_action('特記事項入力')を<a href=に追加             //
// 2006/03/07 前回 style='overflow-y:hidden;' をうっかり付けたためコメント  //
// 2007/11/08 前月の仕入割引の表示が$p1_l_swari → $p1_c_swari へ訂正       //
// 2009/08/17 物流の損益表示を追加（暫定）                             大谷 //
// 2009/08/18 試験・修理部門の損益表示を追加（暫定）                   大谷 //
// 2009/08/19 物流を商品管理に名称変更                                 大谷 //
// 2009/08/20 コメントを編集                                           大谷 //
// 2009/08/21 損益をExcelにあわせて200904～200906に調整を入れた        大谷 //
// 2009/10/06 商管の売上高がASに登録されたのでその対応200909より       大谷 //
//            入力画面で調整金額を入力しこっちでマイナスする           大谷 //
// 2009/10/15 売上高・売上総利益・営業利益・経常利益を太字に変更       大谷 //
// 2009/10/29 商管への社員給与按分加算に対応$～_allo_kin               大谷 //
// 2009/11/09 商管の売上調整が前月・前々月に入っていなかったので修正   大谷 //
// 2009/11/12 リニアの調整金額がうまく取込めなかったのを修正           大谷 //
// 2009/12/07 カプラ試験修理の売上高を加味するよう変更                 大谷 //
// 2009/12/10 段落を調整                                               大谷 //
// 2010/01/15 200912度の添田さんの労務費を調整                         大谷 //
// 2010/01/19 200912度の業務委託収入とその他を調整（1月度戻しの分も）  大谷 //
// 2010/02/01 201001度より営業外を人員比率で再計算した値に置き換え     大谷 //
// 2010/02/04 201001度の添田さんの労務費を調整                              //
//            労務費を入力して配賦するようプログラムを作成予定         大谷 //
// 2010/02/08 201001度から配賦した労務費を加味するように変更           大谷 //
// 2010/03/04 201002度営業外収益その他の調整を追加。201003には戻し     大谷 //
// 2010/04/08 $p2_l_kyu_kinの2が抜けていたため訂正                     大谷 //
// 2010/04/12 当月のリニア経常利益で桁区切りがされていなかったのを訂正 大谷 //
// 2010/05/11 201004度リニアの売上(ツール)255,240円を商管に移動             //
//            また、累計が正しくとれていなかった点を修正               大谷 //
// 2010/10/08 グラフ作成用のデータ登録を追加                           大谷 //
// 2011/05/10 商管の売上調整が２重で入っていた為訂正                   大谷 //
// 2011/07/14 データ登録で労務費と経費のデータが同じだったのを修正     大谷 //
// 2011/10/08 経常利益以下(当期純利益等)を追加(データ登録はなし)       大谷 //
// 2012/01/16 経常利益以下のデータ登録を追加(２期比較表用)             大谷 //
// 2012/02/03 エラー発生部を修正（検索失敗時_tが指定されていなかった） 大谷 //
// 2012/02/28 2012年1月 業務委託費 調整 リニア製造経費 +1,156,130円    大谷 //
//             ※ 平出横川派遣料 2月に逆調整を行うこと                      //
// 2012/03/05 2012年1月 業務委託費 調整 リニア製造経費 -1,156,130円 戻 大谷 //
// 2012/07/07 2012年6月 営業外費用の調整をこっちでしようと思ったが          //
//            再計算の方で調整するため変更なし                         大谷 //
// 2013/11/07 2013年10月 商管業務委託費 調整                                //
//            カプラ材料費 -1,245,035円、商管製造経費 +1,245,035円     大谷 //
//             ※ 横川派遣料 11月に逆調整を行うこと                         //
// 2013/11/07 2013年11月 商管業務委託費 調整                                //
//            カプラ材料費 +1,245,035円、商管製造経費 -1,245,035円     大谷 //
// 2014/09/04 商管の製造経費労務費を各セグメント配賦の為調整           大谷 //
// 2016/07/13 機工の照会を追加、商管をｂ_からｎ_へ変更                      //
//            リニアはリニア標準に変更（ｌ_からｌｈ_）                 大谷 //
// 2016/07/25 耐久・修理商品別損益追加に伴う調整                       大谷 //
// 2017/11/08 4～9月の機工材料費訂正。コメント化の為、月毎調整か            //
//            一括調整かの方針が決定次第でどちらかをコメント解除。     大谷 //
// 2017/11/09 機工損益修正を10月一括調整(月毎調整は別メニュー)         大谷 //
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
///// 呼出先のaction名とアドレス設定
//$menu->set_action('特記事項入力',   PL . 'profit_loss_comment_put.php');

///// 期・月の取得
$ki = 22;
$tuki = 11;

///// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 {$ki} 期　{$tuki} 月度　Ｃ Ｌ Ｔ 試修 商管 商 品 別 損 益 計 算 書");

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
///// 期初年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym = $yyyy . "04";     // 期初年月


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
                        &nbsp;
                            <input class='pt10b' type='submit' name='input_data' value='当月データ登録' onClick='return data_input_click(this)'>
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
                    <td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>項　　　目</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>カ　プ　ラ</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>リ　ニ　ア</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>ツ　ー　ル</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>試験・修理</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>商品管理</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>合　　　計</td>
                    <td rowspan='2' width='400' align='left' class='pt10b' bgcolor='#ffffc6'>製造間接経費・販管費の配賦基準</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>累　計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>累　計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>累　計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>累　計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>累　計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>累　計</td>
                </tr>
                <tr>
                    <td rowspan='11' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営　業　損　益</td>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　高</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>    </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>実際売上高</td>
                </tr>
                <tr>
                    <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>売上原価</td> <!-- 売上原価 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　期首材料仕掛品棚卸高</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='left'  class='pt10'>総平均単価による棚卸高</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　材料費(仕入高)</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='left'  class='pt10'>買掛購入高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　労　　務　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='left'  class='pt10'>ＣＬサービス割合比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　経　　　　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='left'  class='pt10'>ＣＬ直接経費合計比率</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　期末材料仕掛品棚卸高</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='left'  class='pt10'>総平均単価による棚卸高</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　売　上　原　価</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='left'  class='pt10'>ＣＬ直接経費合計比率</td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　総　利　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- 販管費 -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　人　　件　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='left'  class='pt10'>ＣＬ直接給料比率</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　経　　　　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='left'  class='pt10'>ＣＬ占有面積比・ＣＬ直接経費合計比率他</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>販管費及び一般管理費計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>営　　業　　利　　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営業外損益</td>
                    <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　業務委託収入</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>当月の人員比</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>前期実績の売上高比</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　仕　入　割　引</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>当月の人員比</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>前期実績の売上高比</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>当月の人員比</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>前期実績の売上高比</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外収益 計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>    </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　支　払　利　息</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo 90 ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>当月の人員比</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>前期実績の売上高比</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo 90 ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>当月の人員比</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>前期実績の売上高比</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外費用 計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?>    </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo 90 ?></td>
                    <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>経　　常　　利　　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?>    </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo 90 ?></td>
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td colspan='23' rowspan='5' bgcolor='white' nowrap align='center' class='pt10b'>　</td>
                    <td colspan='3' bgcolor='white' nowrap align='right' class='pt10b'>特別利益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='white'><?php echo 90 ?></td>
                    <td rowspan='5' bgcolor='white' nowrap align='center' class='pt10b'>　</td>
                </tr>
                <tr>
                    <td colspan='3' bgcolor='white' nowrap align='right' class='pt10b'>特別損失</td>
                    <td nowrap align='right' class='pt10b' bgcolor='white'><?php echo 90 ?></td>
                </tr>
                <tr>
                    <td colspan='3' bgcolor='white' nowrap align='right' class='pt10b'>税引前純利益金額</td>
                    <td nowrap align='right' class='pt10b' bgcolor='white'><?php echo 90 ?></td>
                </tr>
                <tr>
                    <td colspan='3' bgcolor='white' nowrap align='right' class='pt10b'>法人税等</td>
                    <td nowrap align='right' class='pt10b' bgcolor='white'><?php echo 90 ?></td>
                </tr>
                <tr>
                    <td colspan='3' bgcolor='white' nowrap align='right' class='pt10b'>当期純利益金額</td>
                    <td nowrap align='right' class='pt10b' bgcolor='white'><?php echo 90 ?></td>
                </tr>
            </TBODY>
        </table>
        </td>
        </tr>
        <tr>
        <td>
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
        <tbody>
                <tr>
                    <td colspan='20' bgcolor='white' align='left' class='pt10b'><a href='D:\nitto_koki\00_MES解析\00_repos\test_tnk-web\profit_loss_comment_put_lt.php' style='text-decoration:none; color:black;'>※ 月次損益特記事項</a></td>
                </tr>
                <tr>
                    <td colspan='20' bgcolor='white' class='pt10'>
                        <ol>
                        </ol>
                    </td>
                </tr>
            </tbody>
        </table>
        </td>
        </tr>
    </table>
    </center>
</body>
</html>
