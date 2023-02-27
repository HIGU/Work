<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係の原価率計算表(材料比率重視 推移監視)                        //
// Copyright (C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/03/12 Created   profit_loss_cost_rate.php                           //
// 2003/03/13 StyleSheetを<link に設定 リンクファイルのコメントは           //
//                                      /* ... */ の１行にのみ対応(NN6.1)   //
// 2003/03/27 前半期・次半期のロジック変更 前期売上高比平均をロジック化     //
// 2004/05/11 左側のサイトメニューのオン・オフ ボタンを追加                 //
// 2004/09/07 合計の材料費・労務費経費をExcelの計算法方法に合わせるため変更 //
// 2005/06/15 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/08/29 set_focus()の中身を全てコメントアウト MenuHeaderに移行のため  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
//session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
//access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
// $menu->set_site(10, 7);                     // site_index=99(システムメニュー) site_id=60(テンプレート)
                                            // 月次・中間・決算 = 10 最後のメニュー = 99 を使用
                                            // 月次損益関係 = 7  下位メニュー無し <= 0

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 22 期　11 月度　原 価 率 計 算 表 ");

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();

$current_script  = $menu->out_self();       // 現在実行中のスクリプト名を保存
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<script language="JavaScript">
<!--
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
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのため、こちらに変更しNN対応
    // document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
    // document.form_name.element_name.select();
}
// -->
</script>
<link rel='stylesheet' href='account_settlement.css' type='text/css'> <!-- ファイル指定の場合 -->
<style type="text/css">
<!--
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <table width='100%' border='1' cellspacing='0' cellpadding='0'>
            <tr>
                <td colspan='1' width='130' bgcolor='#d6d3ce' align='center' class='corporate_name'>
                    栃木日東工器(株)
                </td>
                <form method='post' action='<?php $current_script ?>'>
                    <td bgcolor='green' width='70'align='center' class='pt10'>
                        <input class='pt10' type='submit' name='backward_ki' value='前半期'>
                    </td>
                    <td bgcolor='green' width='70'align='center' class='pt10'>
                        <input class='pt10' type='submit' name='forward_ki' value='次半期'>
                    </td>
                    <td colspan='7' bgcolor='#d6d3ce' align='right' class='pt10'>
                        単位
                        <select name='costrate_tani' class='pt10'>
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
                        <select name='costrate_keta' class='pt10'>
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
                        <input class='pt10b' type='submit' name='chg_measure' value='単位変更'>
                    </td>
                </form>
            </tr>
        </table>
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tbody>
                <tr>
                    <td rowspan='2' align='center' class='pt11b'>　</td>
                    <td colspan='3' align='center' class='pt11b' bgcolor='#ffffc6'>カ　プ　ラ</td>
                    <td colspan='3' align='center' class='pt11b' bgcolor='#ffffc6'>リ　ニ　ア</td>
                    <td colspan='3' align='center' class='pt11b' bgcolor='#ffffc6'>合　　　計</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>材　料　費<br>(外作費)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>労務費経費<br>(内作費)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>材　料　費<br>(外作費)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>労務費経費<br>(内作費)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>材　料　費<br>(外作費)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>労務費経費<br>(内作費)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>計</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='white'>前期売上高比</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php  echo 10 ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php  echo 10 ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php  echo 10 ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php  echo 10 ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php  echo 10 ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php  echo 10 ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php  echo 10 ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php  echo 10 ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php  echo 10 ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='#ceffce'>期首棚卸高<br>(割合)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo "1000<br>(10" ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo "1000<br>(10" ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo "1000<br>(10" ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo "1000<br>(10" ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo "1000<br>(10" ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo "1000<br>(10" ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo "1000<br>(10" ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo "1000<br>(10" ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo "1000<br>(10" ?>%)</td>
                </tr>
                <?php for ($j = 0; $j < 3; $j++) { ?>
                <tr>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php  echo 1000 ?></td>
                    <td nowrap align='center' class='pt11' bgcolor='white'>売　上　高</td>
                    <td colspan='2' nowrap align='center' class='pt11' bgcolor='white'><?php  echo 1000 ?></td>
                    <td colspan='3' nowrap align='center' class='pt11' bgcolor='white'><?php  echo 1000 ?></td>
                    <td colspan='3' nowrap align='center' class='pt11' bgcolor='white'><?php  echo 1000 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='#ceffce'>期首棚卸高</td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='white'>当月発生高</td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?> </td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?>     </td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?> </td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?>     </td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?> </td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?>     </td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='#ceffce'>期末棚卸高</td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php  echo 1000 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='white'>売上原価</td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php  echo 1000 ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='#ceffce'>売上高比</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo 10 ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo 10 ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo 10 ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo 10 ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo 10 ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo 10 ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo 10 ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo 10 ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php  echo 10 ?>%</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
    </center>
</body>
</html>
