<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 決算書                                                      //
// Copyright(C) 2018-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2018/06/26 Created   financial_report_view.php                           //
// 2018/07/05 第１四半期決算で一部調整                                      //
// 2018/07/25 可能な部分でAS計算の損益データと比較を追加                    //
//            営業外に関しては、為替差損益の為、同金額の差異がでる          //
// 2018/10/05 デザインのくずれを修正                                        //
// 2018/10/17 19期第2四半期の結果を受けて修正                               //
// 2019/04/09 販管費のクレーム対応費を追加                                  //
// 2019/05/17 日付の取得方法の変更                                          //
// 2020/01/27 減価償却費明細表を追加                                        //
// 2020/04/13 eCA用のデータ抜き出しを追加                                   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
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

///// 対象当月
$ki2_ym   = 202211;
$yyyymm   = 202211;
$ki       = 22;
$b_yyyymm = $yyyymm - 100;
$p1_ki    = 21;

///// 前期末 年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // 前期末年月

///// 期・半期の取得
$tuki_chk = 12;
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //第４四半期
    $hanki = '４';
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //第１四半期
    $hanki = '１';
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //第２四半期
    $hanki = '２';
} elseif ($tuki_chk >= 10) {    //第３四半期
    $hanki = '３';
}

///// 年月範囲の取得
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //第４四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //第１四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //第２四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 10) {    //第３四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
}
///// TNK期 → NK期へ変換
$nk_ki = $ki + 44;

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if ($tuki_chk == 3) {
    $menu->set_title("第 {$ki} 期　本決算　決　算　書");
} else {
    $menu->set_title("第 {$ki} 期　第{$hanki}四半期　決　算　書");
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>
<script type=text/javascript language='JavaScript'>
<!--
function data_input_click(obj) {
    return confirm("当月のデータを登録します。\n既にデータがある場合は上書きされます。");
}
// -->
</script>
<style type='text/css'>
<!--
.pt10b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          black;
}
.pt11b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   #ffffff;
    color:              blue;
    font:bold           12pt;
    font-family:        monospace;
}
-->
</style>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <?php
            //  bgcolor='#ceffce' 黄緑
            //  bgcolor='#ffffc6' 薄い黄色
            //  bgcolor='#d6d3ce' Win グレイ
        ?>
    <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <center>（貸借対照表）</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='3' align='center'>
                        <div class='pt10b'>資産の部</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='3' align='center'>
                        <div class='pt10b'>負債及び純資産の部</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='center'>
                        <div class='pt10b'>科目</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>金額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='center'>
                        <div class='pt10b'>科目</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>金額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>流動資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format(600) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>負債の部</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>現金及び預金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format(600) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>売掛金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>買掛金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>仕掛品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>リース債務</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>原材料及び貯蔵品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>未払金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>前払費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>未払消費税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>未収入金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>未払法人税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>未収消費税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>その他の流動資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>未払費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>預り金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>賞与引当金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>固定資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format(600) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>固定負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= 600 ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>有形固定資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= 600 ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>リース債務</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?>    </div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>建物</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>長期未払金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?>    </div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>機械及び装置</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>退職給付引当金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?>    </div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>車輌運搬具</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>工具器具及び備品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>負債合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?>    </div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>リース資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>建設仮勘定</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>無形固定資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= 600 ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>純資産の部</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>電話加入権</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>株主資本</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>施設利用権</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>資本金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= 600 ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>ソフトウェア</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>資本金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>資本剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= 600 ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>投資その他の資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= 600 ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>資本準備金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>長期貸付金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>その他資本剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>長期前払費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>利益剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= 600 ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>繰延税金資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>その他利益剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>その他の投資等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>繰越利益剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>純資産合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-top:none;border-right:none'>
                        差額
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'>
                        <div class='pt10b'>資産合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'>
                        <div class='pt10b'>負債及び純資産合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                
                
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <center>（損益計算書）</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>Ⅰ．営  業  収  益</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　売  上  高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>Ⅱ．営  業  費  用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>１．売  上  原  価</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　当期製品製造原価</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　売上総利益金額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>２．販売費及び一般管理費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　営 業 利 益 金 額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>Ⅲ．営  業  外  収  益</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>受  取  利  息</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <?php
                if ($kawase_saeki_kin <> 0) {
                ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>為　替　差　益</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>固定資産売却益</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>雑    収    入</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        ※１
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>Ⅳ．営  業  外  費  用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>支　払　利　息</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <?php
                if ($kawase_sason_kin <> 0) {
                ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>為　替　差　損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>固定資産売却損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>固定資産除却損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        ※１と一致
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　経 常 利 益 金 額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　税引前当期純利益金額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　法人税、住民税及び事業税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <!--
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2'>
                        <div class='pt10b'>　過年度法人税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　法人税等調整額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2' style='border-right:none'>
                        <div class='pt10b'>　当期純利益金額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
                
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <center>（製造原価報告書）</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Ⅰ．材    料    費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　　期首材料棚卸高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　　当期材料仕入高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　　合      計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　　期末材料棚卸高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　　合      計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <!--
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>　　棚卸資産評価損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　　他勘定振替高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　　当期材料費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>※</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='4' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Ⅱ．労    務    費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　当期労務費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='4' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Ⅲ．製  造  経  費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　当期製造経費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　当期総製造費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　期首仕掛品棚卸高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　合      計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　期末仕掛品棚卸高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　当期製品製造原価</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='4' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-right:none'>
                        <div class='pt10b'>期末材料棚卸高には、棚卸資産評価損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt10b'>円が含まれております。</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <center>（経費明細書）</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>科目</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>製造費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>販管費及び一般管理費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>（労務費）</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>（人件費）</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>役員報酬</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>給料手当</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>賞与手当</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>顧問料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>厚生福利費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>賞与引当金繰入額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>退職給付費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt10b'>小計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>（製造経費）</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>（経費）</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>旅費交通費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>通信費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>会議費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>交際接待費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>広告宣伝費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>運賃荷造費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>図書教育費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>業務委託費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>諸税公課</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>試験研究費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>修繕費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>事務用消耗品費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>工場用消耗品費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>車輌費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>保険料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>水道光熱費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>地代家賃</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>寄付金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>賃借料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>雑費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>クレーム対応費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>減価償却費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt10b'>小計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <center>（株主資本等変動計算書）</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>（株主資本）</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　【資本金】</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期変動額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>　【資本剰余金】</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　資本準備金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期変動額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　その他資本剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期変動額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>【資本剰余金】合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期変動額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>　【利益剰余金】</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　利益準備金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高及び当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　その他利益剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期変動額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　繰越利益剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期変動額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>（当期純利益金額）</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>【利益剰余金】合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期変動額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>《純資産合計》</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期変動額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-right:none'>
                        <div class='pt10b'>当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= 600 ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <form method='post' action='<?php echo $menu->out_self() ?>'>
            <input class='pt10b' type='submit' name='input_data' value='登録' onClick='return data_input_click(this)'>
        </form>
    </center>
</body>
</html>
