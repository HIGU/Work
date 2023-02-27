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

$url_referer     = $_SESSION['pl_referer'];     // 呼出もとの URL を取得

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 {$ki} 期　{$tuki} 月度　Ｃ Ｌ 経 費 実 績 内 訳 表");

///// 対象当月
$yyyymm = $_SESSION['pl_ym'];
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
///// 表示単位を設定取得
if (isset($_POST['keihi_tani'])) {
    $_SESSION['keihi_tani'] = $_POST['keihi_tani'];
    $tani = $_SESSION['keihi_tani'];
} elseif (isset($_SESSION['keihi_tani'])) {
    $tani = $_SESSION['keihi_tani'];
} else {
    $tani = 1000;        // 初期値 表示単位 千円
    $_SESSION['keihi_tani'] = $tani;
}
///// 表示 小数部桁数 設定取得
if (isset($_POST['keihi_keta'])) {
    $_SESSION['keihi_keta'] = $_POST['keihi_keta'];
    $keta = $_SESSION['keihi_keta'];
} elseif (isset($_SESSION['keihi_keta'])) {
    $keta = $_SESSION['keihi_keta'];
} else {
    $keta = 0;          // 初期値 小数点以下桁数
    $_SESSION['keihi_keta'] = $keta;
}
//////////// 人件費・経費のレコード数 フィールド数
$rec_jin =  8;    // 人件費の使用科目数
$rec_kei = 28;    // 経費の使用科目数       外形標準課税対応のため 27→28
$f_mei   = 13;    // 明細(表)のフィールド数

//////////// 勘定科目の配列設定
// 人件費の Start End 科目
$str_jin = 8101;
$end_jin = 8123;
/******
    8101 = 役員報酬
    8102 = 給料手当
    8103 = 賞与手当
    8104 = 顧問料
    8105 = 法定福利費
    8106 = 厚生福利費
    8121 = 賞与引当金繰入
    8123 = 退職給付費用  旧名→退職給与引当金繰入
******/
$jin_act = array(8101,8102,8103,8104,8105,8106,8121,8123);

// 経費の Start End 科目
$str_kei = 7501;
$end_kei = 8000;
/******
    7501 = 旅費交通費
    7502 = 海外出張
    7503 = 通信費
    7504 = 会議費
    7505 = 交際接待費
    7506 = 広告宣伝費
    7508 = 求人費
    7509 = 運賃荷造費
    7510 = 図書教育費
    7512 = 業務委託費
    7520 = 事業等       // 外形標準課税により追加
    7521 = 諸税公課
    7522 = 試験研究費
    7523 = 雑費
    7524 = 修繕費
    7525 = 保証修理費
    7526 = 事務用消耗品費
    7527 = 工場消耗品費
    7528 = 車両費
    7530 = 保険料
    7531 = 水道光熱費
    7532 = 諸会費
    7533 = 支払手数料
    7536 = 地代家賃
    7537 = 寄付金
    7538 = 倉敷料
    7540 = 賃借料
    8000 = 減価償却費
******/
$kei_act = array(7501,7502,7503,7504,7505,7506,7508,7509,7510,7512,7521,7522,7523,7524,7525,7526,7527,7528,7530,7531,7532,7533,7536,7537,7538,7540,8000);
////// 全体の配列
$actcod  = array(8101,8102,8103,8104,8105,8106,8121,8123,7501,7502,7503,7504,7505,7506,7508,7509,7510,7512,7521,7522,7523,7524,7525,7526,7527,7528,7530,7531,7532,7533,7536,7537,7538,7540,8000);

/***** 売    上    高 *****/
$res = array();                     ///// 売上の月次処理で作られたデータを使用
$query = sprintf("select 全体, カプラ, リニア from wrk_uriage where 年月=%d", $yyyymm);
if ((getResult($query,$res)) > 0) {     ///// データ無しのチェック 優先順位の括弧に注意
    $uri   = $res[0]['全体'];
    $uri_c = $res[0]['カプラ'];
    $uri_l = $res[0]['リニア'];
        ///// 調整データの取得
    $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note like '%%売上高調整'", $yyyymm); // 全体
    getUniResult($query, $adjust_all);
    $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note='カプラ売上高調整'", $yyyymm); // カプラ
    getUniResult($query, $adjust_c);
    $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note='リニア売上高調整'", $yyyymm); // リニア
    getUniResult($query, $adjust_l);
        ///// 調整ロジック END
    $uri   = ($uri + ($adjust_all));    // マイナスも考慮して()を使用する
    $uri_c = ($uri_c + ($adjust_c));
    $uri_l = ($uri_l + ($adjust_l));
    $view_uriage   = number_format(($uri / $tani), $keta);
    $view_uriage_c = number_format(($uri_c / $tani), $keta);
    $view_uriage_l = number_format(($uri_l / $tani), $keta);
        ///// 売上比 算出
    $uri_ritu_c = (Uround(($uri_c / $uri), 3)) * 100;
    $uri_ritu_l = (100 - $uri_ritu_c);
    $view_ritu_c = number_format($uri_ritu_c, 1) . '%';
    $view_ritu_l = number_format($uri_ritu_l, 1) . '%';
    $view_ritu   = number_format(($uri_ritu_c + $uri_ritu_l), 1) . '%';
} else {
    $view_uriage   = "未登録";
    $view_uriage_c = "未登録";
    $view_uriage_l = "未登録";
    $view_ritu_c   = "未登録";
    $view_ritu_l   = "未登録";
    $view_ritu     = "未登録";
}

/********** 材料費(仕入高) **********/
$res = array();
$query = sprintf("select kin, allo from act_pl_history where pl_bs_ym=%d and note='全体仕入高'", $yyyymm);
if (getResult($query, $res) > 0) {
    $shiire      = $res[0]['kin'];
    $shiire_ritu = (Uround($res[0]['allo'], 3) * 100);
    $view_shiire = number_format(($shiire / $tani), $keta);
    $view_shiire_ritu = number_format($shiire_ritu, 1) . '%';
} else {
    $view_shiire = "未計算";
    $view_shiire_ritu = "未計算";
}
$query = sprintf("select kin, allo from act_pl_history where pl_bs_ym=%d and note='カプラ仕入高'", $yyyymm);
if (getResult($query, $res) > 0) {
    $shiire_c      = $res[0]['kin'];
    $shiire_ritu_c = (Uround($res[0]['allo'], 3) * 100);
    $view_shiire_c = number_format(($shiire_c / $tani), $keta);
    $view_shiire_ritu_c = number_format($shiire_ritu_c, 1) . '%';
} else {
    $view_shiire_c = "未計算";
    $view_shiire_ritu_c = "未計算";
}
$query = sprintf("select kin, allo from act_pl_history where pl_bs_ym=%d and note='リニア仕入高'", $yyyymm);
if (getResult($query, $res) > 0) {
    $shiire_l      = $res[0]['kin'];
    $shiire_ritu_l = (100 - $shiire_ritu_c);        // 合計を合わせるため 100 から カプラを引いた値にする
    $view_shiire_l = number_format(($shiire_l / $tani), $keta);
    $view_shiire_ritu_l = number_format($shiire_ritu_l, 1) . '%';
} else {
    $view_shiire_l = "未計算";
    $view_shiire_ritu_l = "未計算";
}

/********** 材料費(製造原価) **********/
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体材料費'", $yyyymm);
if (getUniResult($query, $material) < 1) {
    $view_material   = "未計算";     // 検索失敗
    $view_material_c = "未計算";
    $view_material_l = "未計算";
    $view_barance    = "-----";
} else {
    $view_material = number_format(($material / $tani), $keta);
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ材料費'", $yyyymm);
    if (getUniResult($query, $material_c) < 1) {
        $view_material_c = "未計算";     // 検索失敗
        $view_material_l = "未計算";
        $view_barance    = "-----";
    } else {
        $view_material_c = number_format(($material_c / $tani), $keta);
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア材料費'", $yyyymm);
        if (getUniResult($query, $material_l) < 1) {
            $view_material_l = "未計算";     // 検索失敗
            $view_barance    = "-----";
        } else {
            $view_material_l = number_format(($material_l / $tani), $keta);
                ///// 材料比 算出
            $mate_ritu_c = (Uround(($material_c / $material), 3)) * 100;
            $mate_ritu_l = (100 - $mate_ritu_c);
            $view_mate_ritu_c = number_format($mate_ritu_c, 1) . '%';
            $view_mate_ritu_l = number_format($mate_ritu_l, 1) . '%';
            $view_mate_ritu   = number_format(($mate_ritu_c + $mate_ritu_l), 1) . '%';
            $balance = ($shiire - $material);
            $view_barance = number_format(($balance / $tani), $keta);
        }
    }
}

////// 経歴テーブルよりデータ取り込み
$res_jin = array();     /*** 当月のデータ取得 ***/
$query = sprintf("select kin00, kin01, kin02, kin03, kin04, kin05, kin06, kin07, kin08, kin09, kin10, kin11, kin12 from act_cl_history where pl_bs_ym=%d and (actcod>=%d and actcod<=%d) order by actcod ASC", $yyyymm, $str_jin, $end_jin);
if (($rows_jin = getResult2($query,$res_jin)) > 0) {     ///// データ無しのチェック 優先順位の括弧に注意
    $res_kei = array();                                             // 互換性のため actcod=7520 を最初は除外する
    $query = sprintf("select kin00, kin01, kin02, kin03, kin04, kin05, kin06, kin07, kin08, kin09, kin10, kin11, kin12 from act_cl_history where pl_bs_ym=%d and (actcod>=%d and actcod<=%d) and actcod!=7520 order by actcod ASC", $yyyymm, $str_kei, $end_kei);
    if (($rows_kei = getResult2($query,$res_kei)) > 0) {     ///// データ無しのチェック 優先順位の括弧に注意
        ///// 人件費と経費の明細部
        $data      = array();       // 計算用変数 配列で初期化
        $view_data = array();       // 表示用変数 配列で初期化
        ///////// 表示用データの生成 (画面の表データイメージ)
        ///// 明細の 単位調整
        $r = 0;
        $c = 0;
        foreach ($res_jin as $row) {    // 人件費
            foreach ($row as $col) {
                $data[$r][$c] = $col / $tani;
                $view_data[$r][$c] = number_format($data[$r][$c],$keta);
                $c++;
            }
            $r++;
            $c = 0;
        }
        foreach ($res_kei as $row) {    // 経費
            foreach ($row as $col) {
                $data[$r][$c] = $col / $tani;
                $view_data[$r][$c] = number_format($data[$r][$c],$keta);
                $c++;
            }
            $r++;
            $c = 0;
        }
        ///// 外形標準課税の事業等 追加分
        $res_gai = array();
        $query = sprintf("select kin00, kin01, kin02, kin03, kin04, kin05, kin06, kin07, kin08, kin09, kin10, kin11, kin12 from act_cl_history where pl_bs_ym=%d and actcod=7520", $yyyymm);
        if (($rows_gai = getResult2($query,$res_gai)) > 0) {     // データ無しのチェック 優先順位の括弧に注意
            for ($c = 0; $c < $f_mei; $c++) {
                $data[35][$c]      = $res_gai[0][$c] / $tani;
                $view_data[35][$c] = number_format($data[35][$c], $keta);
            }
        } else {
            for ($c = 0; $c < $f_mei; $c++) {   // 事業等(7520)が無ければ0で初期化
                $data[35][$c]      = 0;
                $view_data[35][$c] = 0;
            }
        }
        ///// その他(9999)の科目があるかチェック
        $query = sprintf("select (kin00+kin01+kin02+kin03+kin04+kin05+kin06+kin07+kin08+kin09+kin10+kin11+kin12) as other from act_cl_history where pl_bs_ym=%d and actcod=9999", $yyyymm);
        if (getUniResult($query, $res_oth) > 0) {
            if ($res_oth > 0) {
                $_SESSION['s_sysmsg'] = sprintf("その他に金額があります！<br>第%d期%d月：%d", $ki, $tuki, $res_oth);
            }
        }
        
        ///// 小計の計算 人件費
        $jin_sum = array();
        for ($c=0; $c < $f_mei; $c++) {
            $jin_sum[$c] = 0;       // 以下で += を使うため初期化
        }
        for ($r=0; $r < $rec_jin; $r++) {
            for ($c=0; $c < $f_mei; $c++) {
                $jin_sum[$c] += $data[$r][$c];
            }
        }
        ///// 小計の計算 経費
        $kei_sum = array();
        for ($c=0; $c < $f_mei; $c++) {
            $kei_sum[$c] = 0;       // 以下で += を使うため初期化
        }
        for ($r=0; $r<$rec_kei; $r++) {
            for ($c=0; $c < $f_mei; $c++) {
                $kei_sum[$c] += $data[$r+8][$c];
            }
        }
        ///// 合計の計算   ///// 小計・合計の表示用データ生成
        $all_sum = array();
        $view_jin_sum = array();
        $view_kei_sum = array();
        $view_all_sum = array();
        for ($c=0;$c<$f_mei;$c++) {
            $all_sum[$c]  = $jin_sum[$c] + $kei_sum[$c];             // 合計の計算
            $view_jin_sum[$c] = number_format($jin_sum[$c],$keta);   // 表示用 人件費計
            $view_kei_sum[$c] = number_format($kei_sum[$c],$keta);   // 表示用 経費計
            $view_all_sum[$c] = number_format($all_sum[$c],$keta);   // 表示用 合　計
        }
    } else {
        $_SESSION['s_sysmsg'] = sprintf("経費の対象データがありません！<br>第%d期%d月",$ki,$tuki);
        header("Location: $url_referer");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] = sprintf("対象データがありません！<br>第%d期%d月",$ki,$tuki);
    header("Location: $url_referer");
    exit();
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
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
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'><?php echo $view_uriage_c ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- リニア -->
                    <td nowrap class='pt10' align='right'><?php echo $view_uriage_c ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap class='pt10'>リニア</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>         <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>     <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>     <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- カプラ -->
                    <td nowrap class='pt10' align='right'><?php echo $view_uriage_l ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right'><?php echo $view_uriage_l ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>売上比</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>     <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>     <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>     <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_ritu_c ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_ritu_l ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_ritu ?>  </td>   <!-- 合計 -->
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
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>     <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>     <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage ?></td>     <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage ?></td>   <!-- 合計 -->
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
                    <td nowrap class='pt10' align='right'><?php echo $view_shiire_c ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right'><?php echo $view_shiire_l ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right'><?php echo $view_shiire ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_shiire ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap class='pt10'>製造原価材料</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material_c ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material_l ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right'>　</td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right'>　</td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'>材料比率</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_shiire_ritu_c ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_shiire_ritu_l ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_shiire_ritu ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'>差額</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_barance ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>材料計</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material_c ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material_l ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td width='10' rowspan='<?= $rec_jin+1 ?>' align='center' class='pt10b' bgcolor='#ffffc6'>人件費</td>
                    <TD nowrap class='pt10'>役員報酬</TD>
                    <?php
                        $r = 0;     // 該当レコード 水色 #b4ffff
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <TR>
                    <TD nowrap class='pt10'>給料手当</TD>
                    <?php
                        $r = 1;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>賞与手当</TD>
                    <?php
                        $r = 2;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>顧問料</TD>
                    <?php
                        $r = 3;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>法定福利費</TD>
                    <?php
                        $r = 4;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>厚生福利費</TD>
                    <?php
                        $r = 5;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>賞与引当金繰入</TD>
                    <?php
                        $r = 6;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>退職給付費用</TD>
                    <?php
                        $r = 7;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right'>人件費計</TD>
                    <?php
                        for ($c=0;$c<$f_mei;$c++) {
                            printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_jin_sum[$c]);
                        }
                    ?>
                </TR>
                <tr>
                    <td width='10' rowspan='<?= $rec_kei+1 ?>' align='center' class='pt10b' bgcolor='#ffffc6'>経費</td>
                    <TD nowrap class='pt10'>旅費交通費</TD>
                    <?php
                        $r = 8;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>海外出張</TD>
                    <?php
                    $r = 9;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>通　信　費</TD>
                    <?php
                    $r = 10;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>会　議　費</TD>
                    <?php
                    $r = 11;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>交際接待費</TD>
                    <?php
                    $r = 12;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>広告宣伝費</TD>
                    <?php
                    $r = 13;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>求　人　費</TD>
                    <?php
                    $r = 14;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>運賃荷造費</TD>
                    <?php
                    $r = 15;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>図書教育費</TD>
                    <?php
                    $r = 16;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>業務委託費</TD>
                    <?php
                    $r = 17;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <td nowrap class='pt10'>事　業　等</td>
                    <?php
                    $r = 35;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>諸税公課</TD>
                    <?php
                    $r = 18;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>試験研究費</TD>
                    <?php
                    $r = 19;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>雑　　　費</TD>
                    <?php
                    $r = 20;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>修　繕　費</TD>
                    <?php
                    $r = 21;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>保証修理費</TD>
                    <?php
                    $r = 22;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>事務用消耗品費</TD>
                    <?php
                    $r = 23;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>工場消耗品費</TD>
                    <?php
                    $r = 24;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>車　両　費</TD>
                    <?php
                    $r = 25;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>保　険　料</TD>
                    <?php
                    $r = 26;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>水道光熱費</TD>
                    <?php
                    $r = 27;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>諸　会　費</TD>
                    <?php
                    $r = 28;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>支払手数料</TD>
                    <?php
                    $r = 29;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>地代家賃</TD>
                    <?php
                    $r = 30;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>寄　付　金</TD>
                    <?php
                    $r = 31;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>倉　敷　料</TD>
                    <?php
                    $r = 32;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>賃　借　料</TD>
                    <?php
                    $r = 33;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>減価償却費</TD>
                    <?php
                    $r = 34;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // 販管費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right'>経費計</TD>
                    <?php
                        for ($c=0;$c<$f_mei;$c++) {
                            printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_kei_sum[$c]);
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD colspan='2' nowrap class='pt10b' align='right'>合　計</TD>
                    <?php
                        for ($c=0;$c<$f_mei;$c++) {
                            printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_all_sum[$c]);
                        }
                    ?>
                </tr>
            </TBODY>
        </table>
    </center>
</body>
</html>
