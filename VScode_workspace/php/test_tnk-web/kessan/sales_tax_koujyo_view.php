<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 消費税申告書 控除税額計算表                                 //
// Copyright(C) 2021-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2021/04/23 Created   sales_tax_koujyo_view.php                           //
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
$ki = 22;
$b_yyyymm = $yyyymm - 100;
$p1_ki   = 21;

///// 前期末 年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // 前期末年月

///// 期・半期の取得
$tuki_chk   =12;
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
$nk_ki   = $ki + 44;
$nk_p1ki = $p1_ki + 44;

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if ($tuki_chk == 3) {
    $menu->set_title("第 {$ki} 期　本決算　控　除　税　額　計　算　表");
} else {
    $menu->set_title("第 {$ki} 期　第{$hanki}四半期　控　除　税　額　計　算　表");
}

$cost_ym = array();
$tuki_chk   =12;
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //第４四半期
    $hanki = '４';
    $yyyy_tou = $yyyy + 1;
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cost_ym[9]  = $yyyy_tou . '01';
    $cost_ym[10] = $yyyy_tou . '02';
    $cost_ym[11] = $yyyy_tou . '03';
    $cnum        = 12;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //第１四半期
    $hanki = '１';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cnum        = 3;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //第２四半期
    $hanki = '２';
    $cost_ym[0] = $yyyy . '04';
    $cost_ym[1] = $yyyy . '05';
    $cost_ym[2] = $yyyy . '06';
    $cost_ym[3] = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cnum        = 6;
} elseif ($tuki_chk >= 10) {    //第３四半期
    $hanki = '３';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cnum        = 9;
}

// 翌期4月分
$cost_ym_next = $yyyy + 1 . '04';

// 日東工器譲受資産関係
if ($nk_ki == 65) {
    $nk_kotei             = 76600469;
    $nk_kotei_kei         = 598519;
    $nk_kotei_zei         = floor($nk_kotei * 0.1*pow(10,0))/pow(10,0);
    $nk_kotei_kei_zei     = floor($nk_kotei_kei * 0.1*pow(10,0))/pow(10,0);
    $nk_kotei_zei_edp     = 7660047;
    $nk_kotei_kei_zei_edp = 59852;
}

// 別メニューで作成したデータの取得

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
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>内訳</th>
                    <th class='winbox' nowrap>税込金額</th>
                    <th class='winbox' nowrap colspan='3'>税抜金額（課税対象）</th>
                    <th class='winbox' nowrap colspan='2'>消費税８％</th>
                    <th class='winbox' nowrap colspan='2'>消費税軽８％</th>
                    <th class='winbox' nowrap colspan='2'>消費税１０％</th>
                    <th class='winbox' nowrap colspan='2'>EDP消費税計上額</th>
                    <th class='winbox' nowrap>消費税４％</th>
                    <th class='winbox' nowrap>税込金額</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
            <?php
            // ＥＤＰ買掛金計上仕入額 1行目表示なし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center' rowspan='3'><div class='pt10b'>ＥＤＰ買掛金計上仕入額</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='3'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // ＥＤＰ買掛金計上仕入額 2行目表示なし
            echo "<tr>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='3'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // ＥＤＰ買掛金計上仕入額 3行目数字あり
            echo "<tr>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>①</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>10%</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>②</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // ＥＤＰ未払金計上仕入額 1行目
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center' rowspan='3'><div class='pt10b'>ＥＤＰ未払金計上仕入額</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>③</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>10%</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>④</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>Ａ</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // ＥＤＰ未払金計上仕入額 2行目
            echo "<tr>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>8%軽</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>④</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>Ｂ</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // ＥＤＰ未払金計上仕入額 3行目
            echo "<tr>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑤</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>10%</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑥</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>Ｃ</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 仕訳伝票仕入高 1行目
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center' rowspan='3'><div class='pt10b'>仕訳伝票仕入高</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑫</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>10%</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑩</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 仕訳伝票仕入高 2行目
            echo "<tr>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑬</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>8%軽</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑩</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 仕訳伝票仕入高 3行目
            echo "<tr>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑭</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>8%</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑩</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 仕入割引
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>仕入割引</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>0</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>0</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>0</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>0</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 輸入取引に係る消費税等
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>輸入取引に係る消費税等</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='3'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑦</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 日東工器譲受資産関係
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>日東工器譲受資産関係</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='3'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>(400)</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>イ</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 固定資産
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　固定資産</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>ａ</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>ｄ</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>ｄ</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>ｄ</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 固定資産経費分
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　　　固定資産経費分</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>ｂ</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>ｅ</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>ｅ</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>ｅ</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 棚卸資産
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　棚卸資産</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>ｃ</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>ｆ</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>ｆ</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>ｆ</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 中間納付計上額
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>中間納付計上額</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='3'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑧</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // （中間納付額）
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>（中間納付額）</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='3'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>(400)</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>(400)</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 計
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>計</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑯</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑨</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑨</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑨</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑮＋⑨＝試算表残高</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑱</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>10%</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑲</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>8%軽</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>⑳</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>8%</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 調整
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center' rowspan='6'><div class='pt10b'>調整</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right' rowspan='2'><span class='pt9'>仮受消費税より振替<BR>（誤仕訳）</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='3'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 調整
            echo "<tr>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='3'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 調整
            echo "<tr>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right' rowspan='4'><span class='pt9'>その他調整</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='3'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>買掛金計算額差異</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 調整
            echo "<tr>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='3'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>10%</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right' rowspan='2'><span class='pt9'>未払金消費税調整</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 調整
            echo "<tr>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='3'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>8%軽</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 調整
            echo "<tr>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='3'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>10%</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>固定資産調整</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 前期調整分
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center' rowspan='2'><div class='pt10b'>前期調整分</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='3'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 前期調整分
            echo "<tr>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>5%</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>0</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>0</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>0</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>0</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='3'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center' rowspan='3'><div class='pt10b'>　</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right' rowspan='3'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>10%</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>8%軽</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>8%</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            echo "</tr>\n";
            
            // 合計
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>計</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            echo "</tr>\n";
            
            /*
            // 合計表示
            
            echo "<tr>\n";
            // 年月
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>合計</div></td>\n";
            // 購入額(軽8％)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税額(軽8％)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 購入額(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 消費税額(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 税抜購入(軽8％)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 税抜購入(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 税抜計上済(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 課税対象外
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 仮払消費税(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 自動計算額(軽8％)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            // 自動計算額(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>400</span></td>\n";
            echo "</tr>\n";
            */
            ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <form method='post' action='<?php echo $menu->out_self() ?>'>
            <input class='pt10b' type='submit' name='input_data' value='登録' onClick='return data_input_click(this)'>
        </form>
    </center>
</body>
</html>
