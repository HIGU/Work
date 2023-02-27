<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 月次 ＣＬ経費実績表                                         //
// Copyright(C) 2003-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2003/01/29 Created   profit_loss_cl_keihi.php                            //
// 2003/01/30 明細フィールドのデータ計算が終了してから単位調整に変更        //
// 2003/02/12 配賦処理を別プログラムに変更。経歴テーブルからデータ取得      //
// 2003/02/21 font を monospace (等間隔font) へ変更                         //
// 2003/02/23 date("Y/m/d H:m:s") → H:i:s のミス修正                       //
// 2003/03/06 title_font today_font を設定 少数以下の桁数６桁を追加         //
// 2003/03/10 売上高 材料(仕入高) 材料(製造原価) を追加                     //
// 2003/03/11 Location: http → Location $url_referer に変更                //
//            メッセージを出力するため site_index site_id をコメントにし    //
//                                            parent.menu_site.を有効に変更 //
// 2003/05/01 工場長からの指示で認証をAccount_groupから通常へ変更           //
// 2004/05/06 外形標準課税の対応のため事業等の科目追加(7520)B36 $r=35       //
//            下位互換性のため事業等7520を除いてselectし7520のみをselectへ  //
// 2004/05/11 左側のサイトメニューのオン・オフ ボタンを追加                 //
// 2005/10/27 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2009/08/20 商品管理の追加に伴い旧プログラムを_oldとして別メニューへ 大谷 //
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

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 22 期　11 月度　Ｃ Ｌ 経 費 実 績 内 訳 表");

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
                    <td width='10' rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'>区分</td>
                    <td rowspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>勘定科目</td>
                    <td colspan='10' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>当　月　の　製　造　経　費</td>
                    <td colspan='3' rowspan='2' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>販売費及び一般管理費</td>
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>合　　　計</td>
                    <td colspan='3' nowrap align='center' class='pt10b'>直接経費</td>
                    <td colspan='3' nowrap align='center' class='pt10b'>間接経費</td>
                    <td rowspan='2' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>合　計</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>カプラ</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>リニア</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>合計</td>
                    <td nowrap align='center' class='pt10b'>カプラ</td>
                    <td nowrap align='center' class='pt10b'>リニア</td>
                    <td nowrap align='center' class='pt10b'>合計</td>
                    <td nowrap align='center' class='pt10b'>カプラ</td>
                    <td nowrap align='center' class='pt10b'>リニア</td>
                    <td nowrap align='center' class='pt10b'>合計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>カプラ</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>リニア</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>合計</td>
                </tr>
                <tr>
                    <td width='10' rowspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>売上</td>
                    <td nowrap class='pt10'>カプラ</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 200 ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 200 ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'><?php echo 200 ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- リニア -->
                    <td nowrap class='pt10' align='right'><?php echo 200 ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 200 ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap class='pt10'>リニア</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>         <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 300 ?></td>     <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 300 ?></td>     <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- カプラ -->
                    <td nowrap class='pt10' align='right'><?php echo 300 ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right'><?php echo 300 ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 300 ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>売上比</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>     <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>     <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>     <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo 400 ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo 500 ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo 600 ?>  </td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap align='right' class='pt10b' bgcolor='#ffffc6'>売上計</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 200 ?></td>     <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 300 ?></td>     <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 700 ?></td>     <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 200 ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 300 ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 700 ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 700 ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td width='10' rowspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>材料</td>
                    <td nowrap class='pt10'>仕入材料</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'><?php echo 800 ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right'><?php echo 800 ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right'><?php echo 800 ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 800 ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap class='pt10'>製造原価材料</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 800 ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 800 ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 800 ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right'>　</td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right'>　</td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 800 ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'>材料比率</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo 800 ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo 800 ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo 800 ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'>差額</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo 800 ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>材料計</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 800 ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 800 ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 800 ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo 800 ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td width='10' rowspan='<?= 800 ?>' align='center' class='pt10b' bgcolor='#ffffc6'>人件費</td>
                    <TD nowrap class='pt10'>役員報酬</TD>
                    <?php
                        $r = 0;     // 該当レコード 水色 #b4ffff
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <TR>
                    <TD nowrap class='pt10'>給料手当</TD>
                    <?php
                        $r = 1;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>賞与手当</TD>
                    <?php
                        $r = 2;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>顧問料</TD>
                    <?php
                        $r = 3;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>法定福利費</TD>
                    <?php
                        $r = 4;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>厚生福利費</TD>
                    <?php
                        $r = 5;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>賞与引当金繰入</TD>
                    <?php
                        $r = 6;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>退職給付費用</TD>
                    <?php
                        $r = 7;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </TR>
                <TR bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right'>人件費計</TD>
                    <?php
                        for ($c=0;$c<10;$c++) {
                            printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_jin_sum[$c]);
                        }
                    ?>
                </TR>
                <tr>
                    <td width='10' rowspan='<?= 800 ?>' align='center' class='pt10b' bgcolor='#ffffc6'>経費</td>
                    <TD nowrap class='pt10'>旅費交通費</TD>
                    <?php
                        $r = 8;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>海外出張</TD>
                    <?php
                    $r = 9;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>通　信　費</TD>
                    <?php
                    $r = 10;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>会　議　費</TD>
                    <?php
                    $r = 11;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>交際接待費</TD>
                    <?php
                    $r = 12;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>広告宣伝費</TD>
                    <?php
                    $r = 13;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>求　人　費</TD>
                    <?php
                    $r = 14;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>運賃荷造費</TD>
                    <?php
                    $r = 15;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>図書教育費</TD>
                    <?php
                    $r = 16;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>業務委託費</TD>
                    <?php
                    $r = 17;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <td nowrap class='pt10'>事　業　等</td>
                    <?php
                    $r = 35;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>諸税公課</TD>
                    <?php
                    $r = 18;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>試験研究費</TD>
                    <?php
                    $r = 19;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>雑　　　費</TD>
                    <?php
                    $r = 20;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>修　繕　費</TD>
                    <?php
                    $r = 21;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>保証修理費</TD>
                    <?php
                    $r = 22;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>事務用消耗品費</TD>
                    <?php
                    $r = 23;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>工場消耗品費</TD>
                    <?php
                    $r = 24;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>車　両　費</TD>
                    <?php
                    $r = 25;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>保　険　料</TD>
                    <?php
                    $r = 26;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>水道光熱費</TD>
                    <?php
                    $r = 27;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>諸　会　費</TD>
                    <?php
                    $r = 28;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>支払手数料</TD>
                    <?php
                    $r = 29;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>地代家賃</TD>
                    <?php
                    $r = 30;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>寄　付　金</TD>
                    <?php
                    $r = 31;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>倉　敷　料</TD>
                    <?php
                    $r = 32;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>賃　借　料</TD>
                    <?php
                    $r = 33;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>減価償却費</TD>
                    <?php
                    $r = 34;     // 該当レコード
                        for ($c=0;$c<10;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",900);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",900);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",900);
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right'>経費計</TD>
                    <?php
                        for ($c=0;$c<10;$c++) {
                            printf("<td nowrap class='pt10' align='right'>%s</td>\n",100);
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD colspan='2' nowrap class='pt10b' align='right'>合　計</TD>
                    <?php
                        for ($c=0;$c<10;$c++) {
                            printf("<td nowrap class='pt10' align='right'>%s</td>\n",100);
                        }
                    ?>
                </tr>
            </TBODY>
        </table>
    </center>
</body>
</html>
