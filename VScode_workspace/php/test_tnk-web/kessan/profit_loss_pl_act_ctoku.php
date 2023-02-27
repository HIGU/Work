<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 月次 カプラ特注・標準 損益計算書                            //
// Copyright (C) 2009-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/08/21 Created   profit_loss_pl_act_ctoku.php                        //
// 2009/10/07 商管の売上調整の際カプラ全体からマイナスするよう変更          //
// 2009/10/15 売上高・売上総利益・営業利益・経常利益を太字に変更            //
// 2009/12/10 段落を調整                                                    //
// 2010/01/15 200912分試修の労務費調整のためこちらにも調整埋め込み          //
// 2010/01/19 200912度の業務委託収入とその他を調整（1月度戻しの分も）       //
// 2010/02/04 2010/01度より営業外に再計算した値を適用                       //
// 2010/02/08 201001度から配賦した労務費を加味するように変更           大谷 //
// 2010/10/08 グラフ作成用のデータ登録を追加                           大谷 //
// 2011/07/14 データ登録で労務費と経費のデータが同じだったのを修正     大谷 //
// 2013/11/07 2013年10月 商管業務委託費 調整                                //
//            カプラ材料費 -1,245,035円、商管製造経費 +1,245,035円     大谷 //
//             ※ 横川派遣料 11月に逆調整を行うこと                         //
// 2013/11/07 2013年11月 商管業務委託費 調整                                //
//            カプラ材料費 +1,245,035円、商管製造経費 -1,245,035円     大谷 //
// 2014/09/04 商管の製造経費労務費を各セグメント配賦の為調整           大谷 //
// 2018/10/10 2018/09固定資産訂正分はすべてカプラ標準なので調整        大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
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
$menu->set_action('特記事項入力',   PL . 'profit_loss_comment_put_ctoku.php');

///// 期・月の取得
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 22 期　11 月度　カプラ特注・標準 商 品 別 損 益 計 算 書");


if (isset($_POST['input_data'])) {                        // 当月データの登録
    ///////// 項目とインデックスの関連付け
    $item = array();
    $item[0]   = "売上高";
    $item[1]   = "期首材料仕掛品棚卸高";
    $item[2]   = "材料費(仕入高)";
    $item[3]   = "労務費";
    $item[4]   = "製造経費";
    $item[5]   = "期末材料仕掛品棚卸高";
    $item[6]   = "売上原価";
    $item[7]   = "売上総利益";
    $item[8]   = "人件費";
    $item[9]   = "経費";
    $item[10]  = "販管費及び一般管理費計";
    $item[11]  = "営業利益";
    $item[12]  = "業務委託収入";
    $item[13]  = "仕入割引";
    $item[14]  = "営業外収益その他";
    $item[15]  = "営業外収益計";
    $item[16]  = "支払利息";
    $item[17]  = "営業外費用その他";
    $item[18]  = "営業外費用計";
    $item[19]  = "経常利益";
    ///////// 各データの保管 カプラ特注=0 カプラ標準=1
    $input_data = array();
    for ($i = 0; $i < 20; $i++) {
        switch ($i) {
                case  0:                                            // 売上高
                    $input_data[$i][0] = $ctoku_uri;                // カプラ特注
                    $input_data[$i][1] = $ch_uri;                   // カプラ標準
                break;
                case  1:                                            // 期首材料仕掛品棚卸高
                    $input_data[$i][0] = $ctoku_invent;             // カプラ特注
                    $input_data[$i][1] = $ch_invent;                // カプラ標準
                break;
                case  2:                                            // 材料費(仕入高)
                    $input_data[$i][0] = $ctoku_metarial;           // カプラ特注
                    $input_data[$i][1] = $ch_metarial;              // カプラ標準
                break;
                case  3:                                            // 労務費
                    $input_data[$i][0] = $ctoku_roumu;              // カプラ特注
                    $input_data[$i][1] = $ch_roumu;                 // カプラ標準
                break;
                case  4:                                            // 製造経費
                    $input_data[$i][0] = $ctoku_expense;            // カプラ特注
                    $input_data[$i][1] = $ch_expense;               // カプラ標準
                break;
                case  5:                                            // 期末材料仕掛品棚卸高
                    $input_data[$i][0] = $ctoku_endinv;             // カプラ特注
                    $input_data[$i][1] = $ch_endinv;                // カプラ標準
                break;
                case  6:                                            // 売上原価
                    $input_data[$i][0] = $ctoku_urigen;             // カプラ特注
                    $input_data[$i][1] = $ch_urigen;                // カプラ標準
                break;
                case  7:                                            // 売上総利益
                    $input_data[$i][0] = $ctoku_gross_profit;       // カプラ特注
                    $input_data[$i][1] = $ch_gross_profit;          // カプラ標準
                break;
                case  8:                                            // 人件費
                    $input_data[$i][0] = $ctoku_han_jin;            // カプラ特注
                    $input_data[$i][1] = $ch_han_jin;               // カプラ標準
                break;
                case  9:                                            // 経費
                    $input_data[$i][0] = $ctoku_han_kei;            // カプラ特注
                    $input_data[$i][1] = $ch_han_kei;               // カプラ標準
                break;
                case 10:                                            // 販管費及び一般管理費計
                    $input_data[$i][0] = $ctoku_han_all;            // カプラ特注
                    $input_data[$i][1] = $ch_han_all;               // カプラ標準
                break;
                case 11:                                            // 営業利益
                    $input_data[$i][0] = $ctoku_ope_profit;         // カプラ特注
                    $input_data[$i][1] = $ch_ope_profit;            // カプラ標準
                break;
                case 12:                                            // 業務委託収入
                    $input_data[$i][0] = $ctoku_gyoumu;             // カプラ特注
                    $input_data[$i][1] = $ch_gyoumu;                // カプラ標準
                break;
                case 13:                                            // 仕入割引
                    $input_data[$i][0] = $ctoku_swari;              // カプラ特注
                    $input_data[$i][1] = $ch_swari;                 // カプラ標準
                break;
                case 14:                                            // 営業外収益その他
                    $input_data[$i][0] = $ctoku_pother;             // カプラ特注
                    $input_data[$i][1] = $ch_pother;                // カプラ標準
                break;
                case 15:                                            // 営業外収益計
                    $input_data[$i][0] = $ctoku_nonope_profit_sum;  // カプラ特注
                    $input_data[$i][1] = $ch_nonope_profit_sum;     // カプラ標準
                break;
                case 16:                                            // 支払利息
                    $input_data[$i][0] = $ctoku_srisoku;            // カプラ特注
                    $input_data[$i][1] = $ch_srisoku;               // カプラ標準
                break;
                case 17:                                            // 営業外費用その他
                    $input_data[$i][0] = $ctoku_lother;             // カプラ特注
                    $input_data[$i][1] = $ch_lother;                // カプラ標準
                break;
                case 18:                                            // 営業外費用計
                    $input_data[$i][0] = $ctoku_nonope_loss_sum;    // カプラ特注
                    $input_data[$i][1] = $ch_nonope_loss_sum;       // カプラ標準
                break;
                case 19:                                            // 経常利益
                    $input_data[$i][0] = $ctoku_current_profit;     // カプラ特注
                    $input_data[$i][1] = $ch_current_profit;        // カプラ標準
                break;
                default:
                break;
            }
    }
    // カプラ特注登録
    $head  = "カプラ特注";
    $sec   = 0;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
    // カプラ標準登録
    $head  = "カプラ標準";
    $sec   = 1;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
}
function insert_date($head,$item,$yyyymm,$input_data,$sec) 
{
    for ($i = 0; $i < 20; $i++) {
        $item_in     = array();
        $item_in[$i] = $head . $item[$i];
        $input_data[$i][$sec] = str_replace(',','',$input_data[$i][$sec]);
        $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
        $res_in = array();
        if (getResult2($query,$res_in) <= 0) {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            ////////// Insert Start
            $query = sprintf("insert into profit_loss_pl_history (pl_bs_ym, kin, note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $yyyymm, $input_data[$i][$sec], $item_in[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sの新規登録に失敗<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 損益データ 新規 登録完了</font>",$yyyymm);
        } else {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update profit_loss_pl_history set kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where pl_bs_ym=%d and note='%s'", $input_data[$i][$sec], $_SESSION['User_ID'], $yyyymm, $item_in[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sのUPDATEに失敗<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 損益データ 変更 完了</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "当月のデータを登録しました。";
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
                <form method='post' action='600'>
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
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>特　　　注</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>標　　　準</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>合　　　計</td>
                    <td rowspan='2' width='400' align='left' class='pt10b' bgcolor='#ffffc6'>製造間接経費・販管費の配賦基準</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo 202209 ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo 202210 ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo 202211 ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>累　計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo 202209 ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo 202210 ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo 202211 ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>累　計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo 202209 ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo 202210 ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo 202211 ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>累　計</td>
                </tr>
                <tr>
                    <td rowspan='11' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営　業　損　益</td>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　高</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600  </td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>実際売上高</td>
                </tr>
                <tr>
                    <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>売上原価</td> <!-- 売上原価 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　期首材料仕掛品棚卸高</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='left'  class='pt10'>標準原価による棚卸高</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　材料費(仕入高)</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='left'  class='pt10'>買掛購入高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　労　　務　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='left'  class='pt10'>サービス割合比及び前半期売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　経　　　　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='left'  class='pt10'>サービス割合比及び前半期売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　期末材料仕掛品棚卸高</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='left'  class='pt10'>標準原価による棚卸高</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　売　上　原　価</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='left'  class='pt10'>　</td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　総　利　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- 販管費 -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　人　　件　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='left'  class='pt10'>部門人員比率</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　経　　　　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='left'  class='pt10'>部門人員比率</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>販管費及び一般管理費計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600</td>
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>営　　業　　利　　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600</td>
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営業外損益</td>
                    <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　業務委託収入</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='left'  class='pt10'>前半期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　仕　入　割　引</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='left'  class='pt10'>前半期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='left'  class='pt10'>前半期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外収益 計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600  </td>
                    <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　支　払　利　息</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>600</td>
                    <td nowrap align='left'  class='pt10'>前半期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'>600</td>
                    <td nowrap align='left'  class='pt10'>前半期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外費用 計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>600  </td>
                    <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>経　　常　　利　　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>600  </td>
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
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
                </tr>
            </tbody>
        </table>
        </td>
        </tr>
    </table>
    </center>
</body>
</html>
